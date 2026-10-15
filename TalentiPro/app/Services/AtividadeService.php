<?php

namespace App\Services;

use App\Models\Atividade;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AtividadeService
{
    /**
     * Registra uma nova atividade na timeline e notifica via WhatsApp (síncrono).
     * Limita o histórico a 10 atividades por usuário.
     *
     * @param string $tipoNotificacao Tipo legível da notificação: 'Avaliação', 'PDI', 'Reunião 1:1', etc.
     * @param string $acaoNotificacao Verbo de ação: 'responder', 'avaliar', 'acompanhar', 'participar'
     *
     * Nota: O envio é feito diretamente (sem Job/fila) pois o ambiente de produção
     * (Hostgator) não possui worker de fila configurado continuamente.
     */
    public function registrar(
        int $empresaId,
        int $userId,
        string $titulo,
        string $descricao,
        string $icone = 'fa-solid fa-bell',
        string $cor = '#6a4c7c',
        ?string $url = null,
        string $tipoNotificacao = 'Atividade',
        string $acaoNotificacao = 'responder'
    ) {
        // Se a atividade for gerada pelo próprio usuário para ele mesmo, já marca como lida
        $isSelf = (auth()->check() && auth()->id() == $userId);

        $atividade = Atividade::create([
            'empresa_id' => $empresaId,
            'user_id'    => $userId,
            'icone'      => $icone,
            'cor'        => $cor,
            'titulo'     => $titulo,
            'descricao'  => $descricao,
            'url'        => $url,
            'lida'       => $isSelf,
            'read_at'    => $isSelf ? Carbon::now() : null,
        ]);

        // Otimização de Banco: Capping de 10 atividades por usuário
        $atividadesUser = Atividade::where('user_id', $userId)->orderBy('id', 'desc')->get();
        if ($atividadesUser->count() > 10) {
            $idsParaExcluir = $atividadesUser->slice(10)->pluck('id');
            Atividade::whereIn('id', $idsParaExcluir)->delete();
        }

        // Disparo Imediato via Fila
        if (!$isSelf) {
            $user = User::find($userId);
            if ($user) {
                // Formata URL de destino direta e limpa para WhatsApp e E-mail
                $urlDestino = $url ?: route('dashboard');

                // 1. WhatsApp
                $msgWhatsApp = "Olá {$user->name}! 👋\n\n"
                    . "Uma nova atividade foi atribuída a você no TalentiPro:\n"
                    . "📋 *{$titulo}*\n\n"
                    . "Acesse pelo link abaixo para resolver:\n"
                    . "🔗 {$urlDestino}";
                
                \App\Jobs\EnviarNotificacaoWhatsappJob::dispatch($user, $msgWhatsApp);

                // 2. E-mail
                if (!empty($user->email)) {
                    \App\Jobs\SendEmailNotificationJob::dispatch(
                        $user->email,
                        $user->name,
                        "Nova Atividade: {$titulo}",
                        $descricao,
                        $urlDestino,
                        "Acessar Atividade"
                    );
                }
            }
        }
    }
}
