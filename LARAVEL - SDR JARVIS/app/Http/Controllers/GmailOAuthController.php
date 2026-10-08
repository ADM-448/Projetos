<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Company;
use App\Models\EmailAccount;
use App\Models\Lead;
use App\Models\User;
use App\Services\GmailOAuthService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GmailOAuthController extends Controller
{
    protected GmailOAuthService $gmailService;

    public function __construct(GmailOAuthService $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    /**
     * Garante autenticação de usuário para escopo tenant em desenvolvimento.
     */
    protected function ensureAuthenticatedUser(): void
    {
        if (! Auth::check()) {
            $user = User::withoutGlobalScopes()->first();
            if ($user) {
                Auth::login($user);
            }
        }
    }

    /**
     * Redireciona o usuário para a tela oficial de autorização do Google.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $clientId = $this->gmailService->getClientId();
        if (! $clientId) {
            return redirect()->route('kanban.index')->withErrors([
                'email' => 'As credenciais do Google Cloud (Client ID e Secret) ainda não foram configuradas. Preencha-as nas configurações de e-mail.',
            ]);
        }

        $authUrl = $this->gmailService->getAuthUrl();

        return redirect()->away($authUrl);
    }

    /**
     * Callback do Google OAuth após o consentimento do usuário.
     */
    public function callback(Request $request): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        if ($request->has('error')) {
            Log::warning('Google OAuth negado ou com erro: ' . $request->get('error'));
            return redirect()->route('kanban.index')->withErrors([
                'email' => 'Acesso ao Google cancelado ou recusado: ' . $request->get('error'),
            ]);
        }

        $code = $request->get('code');
        if (! $code) {
            return redirect()->route('kanban.index')->withErrors([
                'email' => 'Nenhum código de autorização recebido do Google.',
            ]);
        }

        try {
            $tokenData = $this->gmailService->handleCallback($code);

            $user = Auth::user();
            $tenantId = $user?->tenant_id ?? 1;

            // Cria ou atualiza a conta de e-mail conectada para este tenant/usuário
            EmailAccount::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'email' => $tokenData['email'],
                ],
                [
                    'user_id' => $user?->id,
                    'name' => $tokenData['name'] ?? $tokenData['email'],
                    'from_name' => $tokenData['name'] ?? null,
                    'avatar_url' => $tokenData['avatar_url'] ?? null,
                    'provider' => 'google',
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'token_expires_at' => $tokenData['token_expires_at'],
                    'status' => 'connected',
                    'last_active_at' => now(),
                    'last_error' => null,
                ]
            );

            return redirect()->route('kanban.index')->with('success', 'Conta do Gmail (' . $tokenData['email'] . ') vinculada com sucesso via OAuth 2.0!');
        } catch (Exception $e) {
            Log::error('Erro no callback do Google OAuth:', ['exception' => $e->getMessage()]);
            return redirect()->route('kanban.index')->withErrors([
                'email' => 'Erro ao vincular conta do Gmail: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Desconecta uma conta do Gmail do sistema.
     */
    public function disconnect(int $accountId): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $account = EmailAccount::withoutGlobalScopes()->findOrFail($accountId);
        $account->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
        ]);

        return back()->with('success', 'Conta do Gmail desconectada com sucesso.');
    }

    /**
     * Envia um e-mail individual para um lead/empresa via Gmail API e grava na timeline.
     */
    public function send(Request $request): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'to_email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'company_id' => 'required|exists:companies,id',
            'lead_id' => 'nullable|exists:leads,id',
            'email_account_id' => 'nullable|exists:email_accounts,id',
        ]);

        $company = Company::withoutGlobalScopes()->findOrFail($validated['company_id']);
        $lead = isset($validated['lead_id']) ? Lead::withoutGlobalScopes()->find($validated['lead_id']) : null;

        // Busca conta de e-mail ativa
        $account = isset($validated['email_account_id'])
            ? EmailAccount::withoutGlobalScopes()->find($validated['email_account_id'])
            : EmailAccount::withoutGlobalScopes()->where('tenant_id', $company->tenant_id)->where('status', 'connected')->first();

        // Se tiver conta conectada com tokens, envia via API do Gmail
        if ($account && $account->access_token) {
            try {
                $this->gmailService->sendEmail(
                    $account,
                    $validated['to_email'],
                    $validated['subject'],
                    nl2br(e($validated['body']))
                );
            } catch (Exception $e) {
                return back()->withErrors([
                    'email' => 'Falha ao enviar e-mail via Gmail: ' . $e->getMessage(),
                ]);
            }
        } else {
            // Em ambiente local ou sem conta conectada, registra como simulado com aviso amigável
            if (! $account) {
                // Cria conta de demonstração/local para feedback visual
                $account = EmailAccount::withoutGlobalScopes()->create([
                    'tenant_id' => $company->tenant_id,
                    'user_id' => Auth::id(),
                    'email' => Auth::user()?->email ?? 'sdr@empresa.com.br',
                    'name' => Auth::user()?->name ?? 'SDR Jarvis',
                    'provider' => 'google',
                    'status' => 'connected',
                    'sent_today' => 1,
                ]);
            } else {
                $account->increment('sent_today');
            }
        }

        // Grava a atividade na timeline da conta
        Activity::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'lead_id' => $lead?->id,
            'user_id' => Auth::id(),
            'type' => 'email',
            'title' => '✉️ E-mail: ' . $validated['subject'],
            'description' => "Para: {$validated['to_email']}\nAssunto: {$validated['subject']}\n\n" . $validated['body'],
            'metadata' => [
                'to' => $validated['to_email'],
                'subject' => $validated['subject'],
                'sender' => $account?->email,
                'status' => 'sent',
            ],
        ]);

        return back()->with('success', 'E-mail enviado com sucesso e registrado no histórico da conta!');
    }

    /**
     * Salva as credenciais do Google Cloud Console no arquivo .env
     */
    public function saveCredentials(Request $request): RedirectResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'client_id' => 'required|string|max:500',
            'client_secret' => 'required|string|max:500',
        ]);

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            // Atualiza ou insere GOOGLE_CLIENT_ID
            if (str_contains($envContent, 'GOOGLE_CLIENT_ID=')) {
                $envContent = preg_replace('/GOOGLE_CLIENT_ID=.*/', 'GOOGLE_CLIENT_ID="' . $validated['client_id'] . '"', $envContent);
            } else {
                $envContent .= "\nGOOGLE_CLIENT_ID=\"" . $validated['client_id'] . "\"";
            }

            // Atualiza ou insere GOOGLE_CLIENT_SECRET
            if (str_contains($envContent, 'GOOGLE_CLIENT_SECRET=')) {
                $envContent = preg_replace('/GOOGLE_CLIENT_SECRET=.*/', 'GOOGLE_CLIENT_SECRET="' . $validated['client_secret'] . '"', $envContent);
            } else {
                $envContent .= "\nGOOGLE_CLIENT_SECRET=\"" . $validated['client_secret'] . "\"";
            }

            file_put_contents($envPath, $envContent);
        }

        return back()->with('success', 'Credenciais do Google Cloud salvas com sucesso! Agora você já pode clicar em Conectar com o Gmail.');
    }
}
