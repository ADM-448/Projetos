<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Proteção BruteForce
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login.
     */
    public function showLoginForm(Request $request): View
    {
        if ($request->has('redirect_to')) {
            session()->put('url.intended', $request->query('redirect_to'));
        }
        return view('auth.login');
    }

    /**
     * Processa a tentativa de autenticação.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Gerar chave única
        $throttleKey = Str::transliterate(Str::lower($request->input('login')).'|'.$request->ip());

        // 2. Bloqueio automático se já excedeu 5 erros
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            // APENAS avisa e bloqueia (Sem chamar o 'hit' aqui!)
            return back()->withErrors([
                'login' => "Muitas tentativas de login. Por favor, tente novamente em {$seconds} segundos.",
            ])->onlyInput('login');
        }

        // 3. Validação básica de entrada
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($request->input('login'));

        // Determina se é e-mail ou cpf
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'cpf';

        // Se for cpf, remove pontuações
        if ($field === 'cpf') {
            $loginInput = preg_replace('/\D/', '', $loginInput);
        }

        $credentials = [
            $field => $loginInput,
            'password' => $request->input('password'),
        ];

        // 4. Tentativa de login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Sucesso! Limpa o contador de erros imediatamente
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            $user = Auth::user();

            // Verifica se é Admin e manda pro painel especial.
            if ($user->perfil_acesso === UserRole::ADMIN) {
                return redirect()->to('/admin/dashboard')
                    ->with('success', 'Bem-vindo ao Painel de Administração Global!');
            }

            // Verifica se é Consultor e direciona para a seleção de empresa
            if ($user->isConsultor()) {
                return redirect()->route('consultor.empresas.hub')
                    ->with('success', 'Bem-vindo, Consultor! Selecione a empresa que deseja acessar.');
            }

            // Se existir parâmetro redirect_to ou redirect (ex: vindo de notificação de e-mail/whatsapp)
            $redirectTo = $request->input('redirect_to') ?? $request->input('redirect');
            if ($redirectTo) {
                $redirectTo = '/' . ltrim(urldecode($redirectTo), '/');
                if (!str_starts_with($redirectTo, '//') && $redirectTo !== '/' && $redirectTo !== '/login') {
                    return redirect()->to($redirectTo)
                        ->with('success', 'Login realizado com sucesso!');
                }
            }

            // Para os outros (Master, Gestor, Colaborador), manda pro dashboard padrão
            // O middleware 'empresa.ativa' garante que a empresa existe antes de chegar aqui.
            return redirect()->intended('/dashboard')
                ->with('success', 'Login realizado com sucesso!');

        }

        // 5. Se o código chegou AQUI, significa que o login FALHOU!
        // Incrementa o contador de erros
        RateLimiter::hit($throttleKey, 60);

        // Retorna com erro genérico
        return back()->withErrors([
            'login' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('login');
    }

    /**
     * Processa o encerramento da sessão (Logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Limpa e invalida a sessão no servidor
        $request->session()->invalidate();

        // Regenera o token CSRF para prevenir reutilizações
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Você saiu do sistema com segurança!');
    }
}
