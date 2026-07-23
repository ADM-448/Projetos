<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // On some Windows environments with spaces in paths, discovery might fail.
        // We use view()->file() as a robust fallback if auth.login is not found.
        if (view()->exists('auth.login')) {
            return view('auth.login');
        }

        $fallbackPath = resource_path('views/auth/login.blade.php');
        if (file_exists($fallbackPath)) {
            return view()->file($fallbackPath);
        }

        throw new \Exception("View [auth.login] not found at expected path: $fallbackPath");
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $login = trim($request->input('login'));

        // Detecta automaticamente se é e-mail ou CPF
        if (str_contains($login, '@')) {
            // É um e-mail
            $campo = 'email';
            $valor = $login;
        } else {
            // É CPF — normaliza e gera versões com e sem máscara
            $campo = 'cpf';
            $rawValue = preg_replace('/[^0-9]/', '', $login);
            
            $formattedValue = $rawValue;
            if (strlen($rawValue) === 11) {
                $formattedValue = substr($rawValue, 0, 3) . '.' .
                                  substr($rawValue, 3, 3) . '.' .
                                  substr($rawValue, 6, 3) . '-' .
                                  substr($rawValue, 9, 2);
            }
            
            $valor = array_unique([$rawValue, $formattedValue, $login]);
        }

        // Busca todos os usuários com essa credencial, ignorando o filtro de empresa
        $query = User::withoutGlobalScope('empresa');
        if ($campo === 'email') {
            $query->where('email', $valor);
        } else {
            $query->whereIn('cpf', $valor);
        }
        $users = $query->with(['empresa', 'departamento'])->get();

        // Filtra apenas os usuários onde a senha bate
        $validUsers = $users->filter(function ($user) use ($request) {
            return Hash::check($request->input('password'), $user->password);
        });

        if ($validUsers->isEmpty()) {
            return back()->withErrors([
                'login' => 'As credenciais fornecidas não conferem com nossos registros.',
            ])->onlyInput('login');
        }

        // Se encontrou apenas 1 usuário válido, loga direto
        if ($validUsers->count() === 1) {
            Auth::login($validUsers->first());
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        // SE encontrou MAIS de 1 usuário válido (mesmo CPF/email em empresas diferentes)
        // Salva os IDs permitidos na sessão e redireciona para a tela de escolha
        $allowedIds = $validUsers->pluck('id')->toArray();
        $request->session()->put('auth_candidate_ids', $allowedIds);

        return redirect()->route('auth.select-company');
    }

    public function showSelectCompany(Request $request)
    {
        $allowedIds = $request->session()->get('auth_candidate_ids');

        if (!$allowedIds) {
            return redirect()->route('login');
        }

        $users = User::withoutGlobalScope('empresa')
            ->with(['empresa', 'departamento'])
            ->whereIn('id', $allowedIds)
            ->get();

        $accounts = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'empresa_nome' => $user->empresa ? $user->empresa->nome : 'N/A', 
                'perfil' => $user->perfil_acesso,
                'departamento' => $user->departamento ? $user->departamento->nome : null,
            ];
        });

        return view('auth.select-company', compact('accounts'));
    }

    public function selectCompany(Request $request)
    {
        $allowedIds = $request->session()->get('auth_candidate_ids');

        if (!$allowedIds) {
            return redirect()->route('login');
        }

        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $userId = $request->input('user_id');

        // Segurança: Garante que o ID selecionado estava na lista de permitidos (autenticados previamente)
        if (!in_array($userId, $allowedIds)) {
            return back()->with('error', 'Seleção inválida.');
        }

        // Loga o usuário específico
        Auth::loginUsingId($userId);
        $request->session()->regenerate();
        
        // Limpa a sessão temporária
        $request->session()->forget('auth_candidate_ids');

        return redirect()->intended('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
