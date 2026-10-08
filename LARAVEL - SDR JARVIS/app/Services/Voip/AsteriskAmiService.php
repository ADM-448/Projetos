<?php

namespace App\Services\Voip;

use App\Models\Tenant;
use Exception;
use Illuminate\Support\Facades\Log;

class AsteriskAmiService
{
    /**
     * Testa a conexão com o servidor AMI do Asterisk / FreePBX.
     */
    public function testConnection(string $host, int $port, string $user, string $secret): array
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 5);

        if (!$socket) {
            return [
                'success' => false,
                'message' => "Não foi possível conectar ao servidor AMI ({$host}:{$port}): {$errstr} ({$errno})",
            ];
        }

        stream_set_timeout($socket, 5);
        $banner = fgets($socket); // Lê banner inicial do Asterisk Call Manager

        // Envia ação de Login
        $loginCommand = "Action: Login\r\nUsername: {$user}\r\nSecret: {$secret}\r\nActionID: 1001\r\n\r\n";
        fputs($socket, $loginCommand);

        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket);
            $response .= $line;
            if (trim($line) === '' && str_contains($response, 'Response:')) {
                break;
            }
        }

        // Faz Logoff
        fputs($socket, "Action: Logoff\r\n\r\n");
        fclose($socket);

        if (str_contains($response, 'Response: Success')) {
            return [
                'success' => true,
                'message' => 'Conexão com FreePBX AMI autenticada com sucesso!',
                'banner' => trim($banner ?: ''),
            ];
        }

        return [
            'success' => false,
            'message' => 'Falha de autenticação AMI: usuário ou senha incorretos.',
            'raw_response' => trim($response),
        ];
    }

    /**
     * Dispara uma chamada via AMI Originate (faz o ramal tocar e, ao atender, disca para o destino).
     */
    public function originateCall(Tenant $tenant, string $extension, string $toNumber, array $variables = []): array
    {
        $host = $tenant->voip_ami_host ?: $tenant->voip_server_host;
        $port = $tenant->voip_ami_port ?: 5038;
        $user = $tenant->voip_ami_user;
        $secret = $tenant->voip_ami_secret;

        if (!$host || !$user || !$secret) {
            return [
                'success' => false,
                'message' => 'Configurações de AMI não preenchidas no tenant.',
            ];
        }

        // Limpa número de telefone
        $cleanNumber = preg_replace('/[^0-9]/', '', $toNumber);

        $socket = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$socket) {
            Log::error("[Asterisk AMI] Erro ao conectar em {$host}:{$port}: {$errstr}");
            return [
                'success' => false,
                'message' => "Falha na conexão AMI: {$errstr}",
            ];
        }

        stream_set_timeout($socket, 5);
        fgets($socket); // Ignora banner inicial

        // 1. Login
        fputs($socket, "Action: Login\r\nUsername: {$user}\r\nSecret: {$secret}\r\n\r\n");
        $loginRes = '';
        while (!feof($socket)) {
            $line = fgets($socket);
            $loginRes .= $line;
            if (trim($line) === '' && str_contains($loginRes, 'Response:')) break;
        }

        if (!str_contains($loginRes, 'Response: Success')) {
            fclose($socket);
            return ['success' => false, 'message' => 'Autenticação AMI recusada pelo Asterisk.'];
        }

        // 2. Originate
        // Discagem para canal PJSIP do ramal
        $channel = "PJSIP/{$extension}";
        $context = 'from-internal';
        $priority = 1;
        $callerId = "SDR JARVIS <{$extension}>";

        $originateCmd = "Action: Originate\r\n";
        $originateCmd .= "Channel: {$channel}\r\n";
        $originateCmd .= "Exten: {$cleanNumber}\r\n";
        $originateCmd .= "Context: {$context}\r\n";
        $originateCmd .= "Priority: {$priority}\r\n";
        $originateCmd .= "CallerID: {$callerId}\r\n";
        $originateCmd .= "Timeout: 30000\r\n";
        $originateCmd .= "Async: true\r\n";

        if (!empty($variables)) {
            foreach ($variables as $k => $v) {
                $originateCmd .= "Variable: {$k}={$v}\r\n";
            }
        }

        $originateCmd .= "\r\n";
        fputs($socket, $originateCmd);

        $originateRes = '';
        while (!feof($socket)) {
            $line = fgets($socket);
            $originateRes .= $line;
            if (trim($line) === '' && str_contains($originateRes, 'Response:')) break;
        }

        // 3. Logoff
        fputs($socket, "Action: Logoff\r\n\r\n");
        fclose($socket);

        $success = str_contains($originateRes, 'Response: Success');

        Log::info("[Asterisk AMI] Originate executado para ramal {$extension} -> {$cleanNumber}", [
            'success' => $success,
            'response' => trim($originateRes),
        ]);

        return [
            'success' => $success,
            'message' => $success ? "Chamada disparada! Seu ramal {$extension} irá tocar." : "Asterisk rejeitou o Originate.",
            'raw_response' => trim($originateRes),
        ];
    }
}
