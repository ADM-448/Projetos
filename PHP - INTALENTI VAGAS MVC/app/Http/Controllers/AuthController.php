<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $throttleKey = 'login:' . $request->ip() . '|' . strtolower($request->input('email'));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            // 1. Verificação de status
            if (!$user->status) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'Sua conta está inativa ou aguardando aprovação do Administrador Geral.']);
            }

            // 2. Verificação de Empresa ou Prefeitura inativa
            if ($user->isEmpresa() || ($user->isPrefeitura() && !$user->isSuperAdmin())) {
                $empresa = $user->empresas()->first();
                if ($empresa && !$empresa->status) {
                    Auth::logout();
                    $request->session()->invalidate();
                    return back()->withErrors(['email' => 'O acesso desta instituição está pendente de validação ou inativo. Aguarde a liberação pelo Administrador.']);
                }
            }

            // 3. Multi-Tenancy: Isolamento de portal (exceto Super Admin)
            if (!$user->isAdmin()) {
                $portalAtual = config('portal.current_id');
                if ($portalAtual && $user->portal_id && $user->portal_id != $portalAtual) {
                    Auth::logout();
                    $request->session()->invalidate();
                    return back()->withErrors([
                        'email' => 'Cadastro não encontrado neste portal municipal. Verifique a cidade de acesso.',
                    ]);
                }
            }

            return $this->redirectUserByRole($user);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'E-mail ou senha incorretos.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Você saiu do sistema com segurança.');
    }

    public function registerForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $tipo = (int) $request->input('tipo_usuario', 1);
        $portalId = config('portal.current_id', 1);
        $statusInicial = ($tipo === 3) ? false : true;

        $createdData = DB::transaction(function () use ($request, $tipo, $portalId, $statusInicial) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'cpf' => $tipo === 1 ? preg_replace('/[^0-9]/', '', $request->input('cpf')) : null,
                'tipo_usuario' => $tipo,
                'portal_id' => $portalId,
                'status' => $statusInicial,
            ]);

            $empresa = null;

            if ($tipo === 1) {
                // Candidato
                $candidato = Candidato::create([
                    'user_id' => $user->id,
                    'nome_completo' => $request->input('name'),
                    'data_nascimento' => $request->input('data_nascimento'),
                    'celular' => $request->input('celular'),
                    'telefone' => $request->input('telefone'),
                    'nacionalidade' => $request->input('nacionalidade', 'Brasileiro(a)'),
                    'rne' => $request->input('nacionalidade') === 'Estrangeiro(a)' ? $request->input('rne') : null,
                ]);

                // Notificação amigável para preencher o currículo (Item 306)
                Notificacao::create([
                    'user_id' => $user->id,
                    'titulo' => 'Complete seu Currículo',
                    'mensagem' => 'Seja bem-vindo ao portal! Complete suas informações cadastrais e profissionais para se candidatar às vagas.',
                    'link' => route('curriculo.edit'),
                    'lida' => false,
                ]);
            } else {
                // Empresa ou Prefeitura
                $tipoEmpresa = $tipo === 3 ? 'prefeitura' : 'empresa';
                $nomeEmpresa = trim($request->input('nome_empresa'));
                $cidade = trim($request->input('cidade', ''));

                if ($tipo === 3 && empty($nomeEmpresa) && !empty($cidade)) {
                    $nomeEmpresa = 'Prefeitura Municipal de ' . ucfirst($cidade);
                }

                $empresa = Empresa::create([
                    'portal_id' => $portalId,
                    'nome_empresa' => $nomeEmpresa,
                    'nome_fantasia' => $request->input('nome_fantasia') ?: $nomeEmpresa,
                    'cnpj' => preg_replace('/[^0-9]/', '', $request->input('cnpj')),
                    'tipo' => $tipoEmpresa,
                    'dominio' => $request->input('dominio'),
                    'cidade' => $cidade,
                    'estado' => strtoupper(trim($request->input('estado', 'PR'))),
                    'status' => $statusInicial,
                ]);

                // Vínculo Gestor/Recrutador -> Empresa/Prefeitura
                $empresa->usuarios()->attach($user->id, [
                    'role' => 'admin',
                    'status' => $statusInicial,
                ]);
            }

            return ['user' => $user, 'empresa' => $empresa];
        });

        $user = $createdData['user'];
        $empresa = $createdData['empresa'];

        if ($tipo === 3) {
            // Notifica o Super Admin sobre a nova prefeitura pendente
            $superAdmin = User::where('tipo_usuario', 3)->where('email', 'admin@intalenti.com.br')->first();
            if ($superAdmin && $empresa) {
                Notificacao::create([
                    'user_id' => $superAdmin->id,
                    'titulo' => 'Nova Prefeitura Pendente',
                    'mensagem' => "A prefeitura '{$empresa->nome_empresa}' ({$empresa->cidade}) solicitou cadastro e aguarda sua liberação.",
                    'link' => route('admin.empresas', ['tipo' => 'pendentes']),
                    'lida' => false,
                ]);
            }

            return redirect()->route('login')->with('success', 'Solicitação de cadastro da Prefeitura enviada com sucesso! O acesso passará por análise e liberação do Administrador Geral.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($tipo === 1) {
            return redirect()->route('curriculo.edit')->with('success', 'Cadastro realizado! Por favor, monte o seu currículo para candidatar-se às vagas.');
        }

        return $this->redirectUserByRole($user);
    }

    protected function redirectUserByRole(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.empresas');
        } elseif ($user->isCandidato()) {
            return redirect()->route('vagas.index');
        } elseif ($user->isEmpresa()) {
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('prefeitura.dashboard')->with('success', 'Cadastro realizado com sucesso! Bem-vindo ao Observatório Municipal de Vagas.');
        }
    }
}
