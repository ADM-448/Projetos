<?php

namespace App\Services;

use App\Models\ConfiguracaoGoogle;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Drive;
use Exception;

class GoogleIntegrationService
{
    protected $client;
    protected $empresaId;

    public function __construct($empresaId = null)
    {
        $this->empresaId = $empresaId ?? (auth()->user()->empresa_id ?? 1);
        $this->initializeClient();
    }

    protected function initializeClient()
    {
        $this->client = new Client();
        $config = ConfiguracaoGoogle::where('empresa_id', $this->empresaId)->first();

        // 1. Tenta usar OAuth Corporativo (Nível Empresa - Centralizado e Mais Fácil)
        if ($config && $config->access_token) {
            $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
            $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $this->client->setAccessToken($config->access_token);

            if ($this->client->isAccessTokenExpired()) {
                if ($config->refresh_token) {
                    $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($config->refresh_token);
                    $config->access_token = $newAccessToken['access_token'];
                    if (isset($newAccessToken['expires_in'])) {
                        $config->token_expires_at = \Carbon\Carbon::now()->addSeconds($newAccessToken['expires_in']);
                    }
                    $config->save();
                    $this->client->setAccessToken($newAccessToken);
                }
            }
            // Se chegou aqui e não expirou ou renovou com sucesso, estamos prontos.
            if (!$this->client->isAccessTokenExpired()) {
                return;
            }
        }

        // 2. Tenta usar Service Account (Nível Empresa - Avançado)
        if ($config && !empty($config->service_account_json)) {
            $jsonCredentials = json_decode($config->service_account_json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->client->setAuthConfig($jsonCredentials);
                $this->client->setScopes([
                    Calendar::CALENDAR,
                    Drive::DRIVE_READONLY
                ]);
                $this->client->setSubject($jsonCredentials['client_email']);
                return;
            }
        }

        // 3. Tenta usar OAuth do Usuário Logado (Nível Usuário)
        $user = auth()->user();
        if ($user && $user->google_access_token) {
            $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
            $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $this->client->setAccessToken($user->google_access_token);

            if ($this->client->isAccessTokenExpired()) {
                if ($user->google_refresh_token) {
                    $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    $user->google_access_token = $newAccessToken['access_token'];
                    if (isset($newAccessToken['expires_in'])) {
                        $user->google_token_expires_at = \Carbon\Carbon::now()->addSeconds($newAccessToken['expires_in']);
                    }
                    $user->save();
                    $this->client->setAccessToken($newAccessToken);
                } else {
                    throw new Exception("Sua conexão com o Google expirou. Desvincule e vincule novamente no painel.");
                }
            }
            return;
        }

        throw new Exception("Nenhuma configuração do Google foi encontrada. Peça ao administrador para vincular a conta da empresa.");
    }

    /**
     * Cria um evento no Google Agenda e gera o link do Meet
     */
    public function createMeetEvent($titulo, $dataHoraInicio, $participantesEmails)
    {
        $service = new Calendar($this->client);

        $event = new Event([
            'summary' => 'Reunião: ' . $titulo,
            'description' => 'Reunião de Feedback gerada via TalentiPro.',
            'start' => [
                'dateTime' => date('c', strtotime($dataHoraInicio)),
                'timeZone' => 'America/Sao_Paulo',
            ],
            'end' => [
                'dateTime' => date('c', strtotime($dataHoraInicio . ' +1 hour')),
                'timeZone' => 'America/Sao_Paulo',
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid('talenti_', true),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                ]
            ]
        ]);

        $createdEvent = $service->events->insert('primary', $event, ['conferenceDataVersion' => 1]);

        return [
            'event_id' => $createdEvent->getId(),
            'meet_link' => $createdEvent->getHangoutLink() ?? 'https://meet.google.com/new'
        ];
    }

    /**
     * Extrai o File ID de uma URL do Google Docs
     */
    public function extractFileIdFromUrl($url)
    {
        // Exemplo: https://docs.google.com/document/d/1XyZ.../edit
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Busca o texto de um documento no Google Drive (Transcrição)
     */
    public function fetchTranscriptText($fileIdOrUrl)
    {
        $service = new Drive($this->client);
        
        $fileId = $this->extractFileIdFromUrl($fileIdOrUrl) ?: $fileIdOrUrl;

        try {
            // Exporta o Google Doc como plain text
            $response = $service->files->export($fileId, 'text/plain', ['alt' => 'media']);
            return $response->getBody()->getContents();
        } catch (\Throwable $e) {
            throw new \Exception("Erro ao ler transcrição do Google Drive. Verifique se o arquivo existe e se a Service Account tem permissão de leitura. ID: {$fileId}. Erro: " . $e->getMessage());
        }
    }
}
