<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Exibe a tela de login da plataforma.
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('kanban.index');
        }

        return Inertia::render('Auth/Login', [
            'demoAccounts' => [
                [
                    'role_label' => '👑 Super Admin Global',
                    'email' => 'admin@jarvis.com',
                    'description' => 'Acesso total: gerencia múltiplos CRMs, cria perfis e delega acessos.',
                ],
                [
                    'role_label' => '👔 Gestora Comercial (Manager)',
                    'email' => 'mariana.sdr@jarvis.com',
                    'description' => 'Acessa seu CRM e gerencia a equipe de SDRs da base dela.',
                ],
                [
                    'role_label' => '💼 SDR Operador',
                    'email' => 'lucas.sdr@jarvis.com',
                    'description' => 'Foco 100% no CRM: Kanban, cadências, chat WhatsApp e e-mail.',
                ],
            ],
        ]);
    }

    /**
     * Processa a autenticação de credenciais.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::withoutGlobalScopes()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'As credenciais informadas não correspondem aos nossos registros.',
            ])->onlyInput('email');
        }

        if ($user->is_active === false) {
            return back()->withErrors([
                'email' => 'Seu acesso foi desativado pelo administrador da operação.',
            ])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Inicializa o tenant ativo da sessão com o tenant vinculado ao usuário
        if ($user->tenant_id) {
            session(['active_tenant_id' => $user->tenant_id]);
        }

        return redirect()->intended(route('kanban.index'))
            ->with('success', "Bem-vindo de volta, {$user->name}!");
    }

    /**
     * Encerra a sessão do usuário.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sessão encerrada com sucesso.');
    }
}
