<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use App\Models\ConfiguracaoGoogle;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
    }

    /**
     * Configura o cliente com o token da empresa.
     * Atualiza o access_token se estiver expirado usando o refresh_token.
     */
    protected function setTokenForEmpresa(int $empresaId): void
    {
        $config = ConfiguracaoGoogle::where('empresa_id', $empresaId)->first();

        if (!$config || !$config->access_token) {
            throw new \Exception('Conta do Google não vinculada para esta empresa.');
        }

        $this->client->setAccessToken($config->access_token);

        if ($this->client->isAccessTokenExpired()) {
            if (!$config->refresh_token) {
                throw new \Exception('Token expirado e sem Refresh Token. Faça login novamente no Google.');
            }

            $this->client->fetchAccessTokenWithRefreshToken($config->refresh_token);
            $newToken = $this->client->getAccessToken();

            $config->update([
                'access_token' => $newToken['access_token'],
                'token_expires_at' => Carbon::now()->addSeconds($newToken['expires_in']),
            ]);
        }
    }

    /**
     * Cria um evento no Google Calendar e retorna os dados do evento (incluindo Meet Link)
     */
    public function createMeeting(int $empresaId, string $title, string $description, Carbon $startDatetime, int $durationMinutes, array $attendeeEmails): array
    {
        $this->setTokenForEmpresa($empresaId);

        $service = new Calendar($this->client);

        $event = new Event();
        $event->setSummary($title);
        $event->setDescription($description);

        $start = new EventDateTime();
        $start->setDateTime($startDatetime->toRfc3339String());
        $start->setTimeZone(config('app.timezone'));
        $event->setStart($start);

        $end = new EventDateTime();
        $end->setDateTime($startDatetime->copy()->addMinutes($durationMinutes)->toRfc3339String());
        $end->setTimeZone(config('app.timezone'));
        $event->setEnd($end);

        // Adicionar participantes
        $attendees = [];
        foreach ($attendeeEmails as $email) {
            $attendee = new EventAttendee();
            $attendee->setEmail($email);
            $attendees[] = $attendee;
        }
        $event->setAttendees($attendees);

        // Criar link do Google Meet
        $conferenceData = new ConferenceData();
        $conferenceRequest = new CreateConferenceRequest();
        $conferenceRequest->setRequestId(uniqid('meet_'));
        $conferenceSolutionKey = new ConferenceSolutionKey();
        $conferenceSolutionKey->setType('hangoutsMeet');
        $conferenceRequest->setConferenceSolutionKey($conferenceSolutionKey);
        $conferenceData->setCreateRequest($conferenceRequest);
        $event->setConferenceData($conferenceData);

        // Inserir evento no calendário principal
        $optParams = ['conferenceDataVersion' => 1, 'sendUpdates' => 'all'];
        $createdEvent = $service->events->insert('primary', $event, $optParams);

        return [
            'id' => $createdEvent->getId(),
            'meet_link' => $createdEvent->getHangoutLink(),
        ];
    }
}
