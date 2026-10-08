<?php

namespace App\Services;

use App\Models\EmailAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailOAuthService
{
    /**
     * Retorna o Client ID configurado (do .env ou config).
     */
    public function getClientId(): ?string
    {
        return config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
    }

    /**
     * Retorna o Client Secret configurado.
     */
    public function getClientSecret(): ?string
    {
        return config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');
    }

    /**
     * Retorna a Redirect URI configurada.
     */
    public function getRedirectUri(): string
    {
        $configured = config('services.google.redirect') ?: env('GOOGLE_REDIRECT_URI');
        if ($configured) {
            return $configured;
        }

        return url('/auth/google/callback');
    }

    /**
     * Gera a URL oficial de consentimento do Google OAuth2.
     */
    public function getAuthUrl(?string $state = null): string
    {
        $clientId = $this->getClientId();
        $redirectUri = $this->getRedirectUri();

        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/gmail.readonly',
        ];

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state ?? csrf_token(),
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Troca o authorization code pelo access token e refresh token do Google.
     */
    public function handleCallback(string $code): array
    {
        $clientId = $this->getClientId();
        $clientSecret = $this->getClientSecret();
        $redirectUri = $this->getRedirectUri();

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            Log::error('Erro ao trocar code do Google por tokens:', $response->json());
            throw new Exception('Falha ao autenticar com o Google: ' . ($response->json('error_description') ?? $response->body()));
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];
        $refreshToken = $tokenData['refresh_token'] ?? null;
        $expiresIn = $tokenData['expires_in'] ?? 3600;

        // Busca o perfil da conta Google conectada
        $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
        if (! $profileResponse->successful()) {
            throw new Exception('Falha ao buscar dados do perfil da conta Google.');
        }

        $profile = $profileResponse->json();

        return [
            'email' => $profile['email'],
            'name' => $profile['name'] ?? null,
            'avatar_url' => $profile['picture'] ?? null,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_expires_at' => Carbon::now()->addSeconds($expiresIn - 60),
        ];
    }

    /**
     * Garante que o access token esteja válido. Se expirou, renova com o refresh token.
     */
    public function ensureValidToken(EmailAccount $account): string
    {
        if ($account->token_expires_at && $account->token_expires_at->isFuture() && $account->access_token) {
            return $account->access_token;
        }

        if (! $account->refresh_token) {
            throw new Exception('Token de acesso expirado e nenhum refresh token disponível. Por favor, reconecte a conta do Gmail.');
        }

        $clientId = $this->getClientId();
        $clientSecret = $this->getClientSecret();

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $account->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            $account->update([
                'status' => 'error',
                'last_error' => 'Falha ao renovar token com o Google: ' . $response->body(),
            ]);
            throw new Exception('Não foi possível renovar o acesso ao Gmail. Reconecte a conta.');
        }

        $data = $response->json();
        $newAccessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;

        $account->update([
            'access_token' => $newAccessToken,
            'token_expires_at' => Carbon::now()->addSeconds($expiresIn - 60),
            'status' => 'connected',
            'last_error' => null,
        ]);

        return $newAccessToken;
    }

    /**
     * Envia um e-mail através da API REST oficial do Gmail (users.messages.send).
     */
    public function sendEmail(EmailAccount $account, string $to, string $subject, string $htmlBody): array
    {
        $accessToken = $this->ensureValidToken($account);

        $fromName = $account->from_name ?: $account->name ?: $account->email;
        $fromHeader = "=?utf-8?B?" . base64_encode($fromName) . "?= <{$account->email}>";
        $subjectHeader = "=?utf-8?B?" . base64_encode($subject) . "?=";

        // Monta o e-mail em formato MIME RFC 2822
        $mime = "From: {$fromHeader}\r\n";
        $mime .= "To: <{$to}>\r\n";
        $mime .= "Subject: {$subjectHeader}\r\n";
        $mime .= "MIME-Version: 1.0\r\n";
        $mime .= "Content-Type: text/html; charset=utf-8\r\n";
        $mime .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $mime .= chunk_split(base64_encode($htmlBody));

        // Codifica no formato base64url que a API do Gmail requer
        $rawMessage = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');

        $response = Http::withToken($accessToken)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $rawMessage,
            ]);

        if (! $response->successful()) {
            Log::error('Erro ao enviar e-mail via Gmail API:', $response->json());
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception('Erro retornado pela API do Gmail: ' . $errorMsg);
        }

        $account->increment('sent_today');
        $account->update([
            'last_active_at' => now(),
            'status' => 'connected',
            'last_error' => null,
        ]);

        return $response->json();
    }
}
