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

        // Cria a atividade
        Atividade::create([
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
        // Usa orderBy('id', 'desc') para garantir ordem correta mesmo quando
        // multiple inserts acontecem com o mesmo timestamp (ex: ambiente de teste)
        $atividadesUser = Atividade::where('user_id', $userId)->orderBy('id', 'desc')->get();
        if ($atividadesUser->count() > 10) {
            // slice(10) = pega do índice 10 em diante = as mais antigas (maior ID = mais recente)
            $idsParaExcluir = $atividadesUser->slice(10)->pluck('id');
            Atividade::whereIn('id', $idsParaExcluir)->delete();
        }

        // O envio de WhatsApp imediato foi desativado.
        // As notificações agora são agrupadas e enviadas uma vez por dia
        // através do comando SendDailyNotifications (Cron Job).
    }
}
