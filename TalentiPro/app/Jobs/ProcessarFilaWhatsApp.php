<?php

namespace App\Jobs;

use App\Models\Notificacao;
use App\Models\User;
use App\Models\WhatsappQueue;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessarFilaWhatsApp implements ShouldQueue
{
    use Queueable;

    /**
     * Processa a fila de mensagens WhatsApp pendentes (reenvios).
     * Executado automaticamente a cada 10 minutos pelo scheduler do Laravel.
     *
     * Fluxo:
     * 1. Verifica se a instância está conectada
     * 2. Se não → notifica admins master e encerra (não adianta tentar enviar)
     * 3. Se sim  → processa todos os itens pendentes
     * 4. Ao final, se houve mensagens definitivamente falhadas → notifica admins
     */
    public function handle(WhatsAppService $wpp): void
    {
        // Sem mensagens pendentes? Sai sem fazer nada
        $totalPendentes = WhatsappQueue::pendentes()->count();
        if ($totalPendentes === 0) {
            return;
        }

        Log::info("WhatsApp Job: {$totalPendentes} mensagens pendentes encontradas.");

        // Checa se a instância está online antes de tentar reenviar
        if (!$wpp->estaConectado()) {
            Log::warning('WhatsApp Job: instância desconectada. Alertando admins.');
            $this->alertarAdmins(
                "⚠️ WhatsApp Desconectado",
                "O WhatsApp do sistema está desconectado. Há {$totalPendentes} mensagem(ns) na fila aguardando reenvio. Acesse Configurações → WhatsApp para reconectar.",
            );
            return;
        }

        // Processa a fila
        [$enviadas, $falhadas] = $wpp->processarFila();

        // Se houver mensagens que esgotaram todas as tentativas → alerta o admin
        if ($falhadas > 0) {
            $this->alertarAdmins(
                "❌ WhatsApp: {$falhadas} mensagem(ns) não puderam ser enviadas",
                "{$falhadas} mensagem(ns) na fila do WhatsApp falharam após 3 tentativas. Acesse Configurações → WhatsApp para verificar o status da conexão.",
            );
        }

        if ($enviadas > 0) {
            Log::info("WhatsApp Job: {$enviadas} mensagem(ns) reenviadas com sucesso.");
        }
    }

    /**
     * Notifica todos os usuários com perfil 'master' via notificação interna.
     * Garantia: mesmo que o WhatsApp esteja fora, o admin recebe pelo sistema.
     */
    private function alertarAdmins(string $titulo, string $mensagem): void
    {
        try {
            $masters = User::where('perfil_acesso', 'master')->get();

            foreach ($masters as $master) {
                Notificacao::create([
                    'empresa_id'     => $master->empresa_id,
                    'destinatario_id'=> $master->id,
                    'remetente_id'   => $master->id,
                    'tipo'           => 'sistema',
                    'mensagem'       => "{$titulo} — {$mensagem}",
                    'link'           => '/configuracoes/whatsapp',
                    'lida'           => false,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp Job: erro ao alertar admins', ['erro' => $e->getMessage()]);
        }
    }
}
