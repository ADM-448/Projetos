<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleOAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = $this->getGoogleClient();
        $authUrl = $client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('dashboard')->with('error', 'Falha na autenticação com o Google.');
        }

        $client = $this->getGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return redirect()->route('dashboard')->with('error', 'Erro ao obter token do Google: ' . $token['error']);
        }

        $empresa = auth()->user()->empresa;

        // Salvar as configurações (se já existe, atualiza)
        \App\Models\ConfiguracaoGoogle::updateOrCreate(
            ['empresa_id' => $empresa->id],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => \Carbon\Carbon::now()->addSeconds($token['expires_in']),
            ]
        );

        return redirect()->route('dashboard')->with('success', 'Conta Google vinculada com sucesso para sua Empresa!');
    }

    public function disconnect()
    {
        $empresa = auth()->user()->empresa;
        if ($empresa->configuracaoGoogle) {
            $empresa->configuracaoGoogle->delete();
        }
        return redirect()->route('dashboard')->with('success', 'Conta Google desvinculada.');
    }

    private function getGoogleClient()
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope(\Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        return $client;
    }
}
