<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessarFilaWhatsApp implements ShouldQueue
{
    use Queueable;

    /**
     * Processa a fila de mensagens WhatsApp pendentes (reenvios).
     * Executado automaticamente a cada 10 minutos pelo scheduler do Laravel.
     */
    public function handle(\App\Services\WhatsAppService $wpp): void
    {
        // Sem mensagens pendentes? Sai sem fazer nada
        $totalPendentes = \App\Models\WhatsappQueue::pendentes()->count();
        if ($totalPendentes === 0) {
            return;
        }

        \Illuminate\Support\Facades\Log::info("WhatsApp Job: {$totalPendentes} mensagens pendentes encontradas.");

        // Checa se a instância está online antes de tentar reenviar
        if (!$wpp->estaConectado()) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp Job: instância desconectada. Fila não será processada.');
            // Removido o alerta a cada 10 minutos conforme solicitado
            return;
        }

        // Processa a fila
        [$enviadas, $falhadas] = $wpp->processarFila();

        // (Removido o alerta aos admins de mensagens falhadas a pedido do utilizador)


        if ($enviadas > 0) {
            \Illuminate\Support\Facades\Log::info("WhatsApp Job: {$enviadas} mensagem(ns) reenviadas com sucesso.");
        }
    }

    /**
     * Notifica todos os usuários com perfil 'master' ou 'admin' via Atividade.
     */
    private function alertarAdmins(string $titulo, string $mensagem): void
    {
        try {
            $masters = \App\Models\User::whereIn('perfil_acesso', ['master', 'admin'])->get();

            foreach ($masters as $master) {
                if (!$master->empresa_id) {
                    continue; // Pula admins globais sem empresa vinculada para não quebrar a chave estrangeira
                }

                app(\App\Services\AtividadeService::class)->registrar(
                    $master->empresa_id,
                    $master->id,
                    $titulo,
                    $mensagem,
                    'fa-solid fa-triangle-exclamation',
                    '#e53e3e',
                    '/configuracoes/whatsapp'
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Job: erro ao alertar admins', ['erro' => $e->getMessage()]);
        }
    }
}
