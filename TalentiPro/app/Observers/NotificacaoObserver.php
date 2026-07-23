<?php

namespace App\Observers;

use App\Models\Notificacao;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class NotificacaoObserver
{
    /**
     * Disparado automaticamente sempre que uma Notificacao é criada em
     * qualquer parte do sistema (PDI, Avaliação, Solicitação, etc.).
     *
     * Princípio: NUNCA quebrar o sistema. Se o WhatsApp falhar, a
     * notificação interna do sistema já foi salva e o usuário a verá
     * no sino de notificações.
     */
    public function created(Notificacao $notificacao): void
    {
        try {
            // Carrega o destinatário com o telefone cadastrado
            $destinatario = $notificacao->destinatario;

            // Sem telefone = sem WhatsApp. Ignora silenciosamente.
            if (empty($destinatario?->telefone)) {
                return;
            }

            $mensagem = $this->montarMensagem($notificacao, $destinatario->name);

            // Passa o user_id para rastreamento na fila de reenvio
            app(WhatsAppService::class)->enviar(
                $destinatario->telefone,
                $mensagem,
                $destinatario->id
            );

        } catch (\Throwable $e) {
            // Captura qualquer erro (Exception + Error) sem vazar para o usuário
            Log::error('WhatsApp Observer: falha ao enviar notificação', [
                'notificacao_id' => $notificacao->id,
                'tipo'           => $notificacao->tipo,
                'destinatario'   => $notificacao->destinatario_id,
                'erro'           => $e->getMessage(),
            ]);
        }
    }

    /**
     * Monta a mensagem personalizada por tipo de notificação.
     */
    private function montarMensagem(Notificacao $notificacao, string $nomeCompleto): string
    {
        $primeiroNome = explode(' ', trim($nomeCompleto))[0];

        $emoji = match ($notificacao->tipo) {
            'pdi'         => '📋',
            'avaliacao'   => '📊',
            'solicitacao' => '🔔',
            'reuniao'     => '📅',
            default       => '🔔',
        };

        $acao = match ($notificacao->tipo) {
            'pdi'         => 'um novo *PDI* para acompanhar',
            'avaliacao'   => 'uma nova *Avaliação* para responder',
            'solicitacao' => 'uma nova *Solicitação* aguardando sua análise',
            'reuniao'     => 'uma *Reunião* agendada para você',
            default       => 'uma nova notificação',
        };

        return implode("\n", [
            "{$emoji} Olá, *{$primeiroNome}*!",
            '',
            "Você tem {$acao} no *TalentiPro*.",
            '',
            "📌 _{$notificacao->mensagem}_",
            '',
            '👉 Acesse o sistema para ver os detalhes.',
        ]);
    }
}
