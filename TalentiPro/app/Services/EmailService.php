<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendEmailNotificationJob;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Dispara um e-mail assíncrono para o usuário via Job na fila.
     *
     * @param User|string|int $recipient Pode ser o objeto User, ID do User (int) ou o endereço de e-mail (string)
     * @param string $assunto Assunto do e-mail
     * @param string $mensagem Corpo da mensagem
     * @param string|null $link Link opcional para um botão (ex: acessar a avaliação)
     * @param string|null $textoBotao Texto do botão (ex: 'Acessar TalentiPro')
     * @return void
     */
    public function notificar($recipient, string $assunto, string $mensagem, ?string $link = null, ?string $textoBotao = null): void
    {
        try {
            if (is_numeric($recipient)) {
                $recipient = User::find($recipient);
            }

            $email = $recipient instanceof User ? $recipient->email : $recipient;
            $nome = $recipient instanceof User ? $recipient->name : null;

            if (empty($email)) {
                Log::warning('EmailService: Tentativa de envio para destinatário sem e-mail.', [
                    'recipient' => $recipient instanceof User ? $recipient->id : $recipient
                ]);
                return;
            }

            // Converter links internos para deep links de login
            if ($link) {
                $appUrl = config('app.url');
                if (str_starts_with($link, $appUrl)) {
                    $parsed = parse_url($link);
                    $path = $parsed['path'] ?? '';
                    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
                    $relativeUrl = ltrim($path . $query, '/');
                    
                    if ($relativeUrl !== 'login') {
                        $link = route('login') . '?redirect_to=/' . urlencode($relativeUrl);
                    }
                }
            }

            // Despacha para a fila (background)
            SendEmailNotificationJob::dispatch($email, $nome, $assunto, $mensagem, $link, $textoBotao);

        } catch (\Throwable $e) {
            Log::error('EmailService: Erro ao colocar e-mail na fila', [
                'recipient' => $recipient instanceof User ? $recipient->id : $recipient,
                'erro' => $e->getMessage()
            ]);
        }
    }
}
