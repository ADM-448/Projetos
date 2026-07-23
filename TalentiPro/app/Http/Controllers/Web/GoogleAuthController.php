<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    private function getClient()
    {
        $client = new GoogleClient();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope('https://www.googleapis.com/auth/userinfo.email');
        $client->addScope('https://www.googleapis.com/auth/userinfo.profile');
        $client->addScope('https://www.googleapis.com/auth/drive.file');
        $client->addScope('https://www.googleapis.com/auth/calendar.events');
        
        // Offline access para receber o refresh_token
        $client->setAccessType('offline');
        // Força a tela de consentimento para garantir que o refresh_token sempre venha
        $client->setPrompt('consent');

        return $client;
    }

    public function redirect(Request $request)
    {
        $client = $this->getClient();
        
        if ($request->has('tipo') && $request->tipo == 'empresa') {
            $client->setState('empresa');
        } else {
            $client->setState('user');
        }
        
        $authUrl = $client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('dashboard')->with('error', 'Autorização cancelada.');
        }

        $client = $this->getClient();
        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return redirect()->route('dashboard')->with('error', 'Erro ao obter token do Google: ' . $token['error_description']);
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para vincular a conta.');
        }

        $state = $request->state;

        if ($state === 'empresa') {
            $config = \App\Models\ConfiguracaoGoogle::firstOrCreate(
                ['empresa_id' => $user->empresa_id ?? 1]
            );
            $config->access_token = $token['access_token'];
            if (isset($token['refresh_token'])) {
                $config->refresh_token = $token['refresh_token'];
            }
            if (isset($token['expires_in'])) {
                $config->token_expires_at = Carbon::now()->addSeconds($token['expires_in']);
            }
            $config->save();
            return redirect()->route('configuracoes.google')->with('success', 'Conta do Google vinculada para a empresa com sucesso!');
        }

        // Salvar os tokens do usuário individual
        $user->google_access_token = $token['access_token'];
        
        // O refresh_token só vem na primeira vez ou quando prompt=consent
        if (isset($token['refresh_token'])) {
            $user->google_refresh_token = $token['refresh_token'];
        }

        // Calcular tempo de expiração
        if (isset($token['expires_in'])) {
            $user->google_token_expires_at = Carbon::now()->addSeconds($token['expires_in']);
        }

        $user->save();

        return redirect()->route('dashboard')->with('success', 'Sua conta do Google foi vinculada com sucesso!');
    }

    public function unlink()
    {
        $user = Auth::user();
        $user->google_access_token = null;
        $user->google_refresh_token = null;
        $user->google_token_expires_at = null;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Sua conta do Google foi desvinculada.');
    }

    public function unlinkEmpresa()
    {
        $user = Auth::user();
        
        if (!in_array($user->perfil_acesso, ['admin', 'master'])) {
            abort(403);
        }

        $config = \App\Models\ConfiguracaoGoogle::where('empresa_id', $user->empresa_id ?? 1)->first();
        if ($config) {
            $config->access_token = null;
            $config->refresh_token = null;
            $config->token_expires_at = null;
            $config->save();
        }

        return redirect()->route('configuracoes.google')->with('success', 'Conta Google da empresa desvinculada com sucesso.');
    }
}
