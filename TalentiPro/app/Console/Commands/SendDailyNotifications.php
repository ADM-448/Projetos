<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Atividade;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\ClimateSurvey;
use App\Models\ClimateSurveyParticipation;
use App\Models\ReuniaoAgendamento;
use App\Enums\StatusAvaliacao;
use App\Services\WhatsAppService;
use App\Mail\AtividadesPendentesMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDailyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um resumo diário (via WhatsApp e E-mail) para usuários com atividades pendentes.';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsappService)
    {
        $hoje = Carbon::today();

        // 1. Busca todos os usuários que possuem atividades não lidas
        $usersWithPending = User::whereHas('atividades', function ($query) {
            $query->where('lida', false);
        })->with(['atividades' => function ($query) {
            $query->where('lida', false)->orderBy('created_at', 'desc');
        }])->get();

        $this->info("Iniciando disparo diário. Usuários com pendências registradas: " . $usersWithPending->count());

        foreach ($usersWithPending as $user) {
            // Filtra e valida cada atividade para garantir que ainda é uma pendência real não respondida
            $atividadesValidas = $user->atividades->filter(function ($ativ) use ($user, $hoje) {
                if ($this->isAtividadePendenteValida($ativ, $user, $hoje)) {
                    return true;
                }

                // Se não for mais pendente (ex: já respondeu ou prazo encerrou), marca como lida no banco
                $ativ->update([
                    'lida' => true,
                    'read_at' => Carbon::now(),
                ]);
                return false;
            });

            // SE NÃO SOBROU NENHUMA ATIVIDADE REALMENTE PENDENTE, PULA O USUÁRIO (NÃO ENVIA NADA)
            if ($atividadesValidas->isEmpty()) {
                $this->info("Usuário {$user->name} não possui pendências ativas reais. Nenhuma notificação enviada.");
                continue;
            }

            $count = $atividadesValidas->count();

            // ---------- 1. DISPARO DE WHATSAPP ----------
            try {
                // Monta a string resumida pro WhatsApp com links individuais diretos e limpos
                $lista = "";
                foreach ($atividadesValidas as $ativ) {
                    $itemUrl = !empty($ativ->url) ? $ativ->url : route('dashboard');
                    $lista .= "🔹 *{$ativ->titulo}*\n🔗 {$itemUrl}\n\n";
                }

                $mensagemFormatada = "Olá {$user->name}! 👋\n\n"
                    . "Você possui *{$count}* atividade(s) aguardando sua atenção no TalentiPro:\n\n"
                    . $lista
                    . "Acesse os links acima para resolver, ou veja mais detalhes no seu E-mail.";

                $empresaId = $user->empresa_id;
                
                if ($empresaId) {
                    $whatsappService->setEmpresaId($empresaId)->notificar($user, $mensagemFormatada);
                    $this->info("WhatsApp enviado para {$user->name} ({$count} pendência(s))");
                }
            } catch (\Throwable $e) {
                Log::warning('SendDailyNotifications: erro ao enviar WhatsApp', [
                    'userId' => $user->id,
                    'erro'   => $e->getMessage()
                ]);
            }

            // ---------- 2. DISPARO DE E-MAIL ----------
            try {
                if ($user->email) {
                    Mail::to($user->email)->send(new AtividadesPendentesMail($user, $atividadesValidas));
                    $this->info("E-mail enviado para {$user->name}");
                }
            } catch (\Throwable $e) {
                Log::warning('SendDailyNotifications: erro ao enviar E-mail', [
                    'userId' => $user->id,
                    'erro'   => $e->getMessage()
                ]);
            }
        }

        $this->info("Disparo diário concluído com sucesso.");
    }

    /**
     * Valida se a atividade ainda corresponde a uma pendência real não respondida.
     */
    private function isAtividadePendenteValida(Atividade $ativ, User $user, Carbon $hoje): bool
    {
        $url = $ativ->url ?? '';

        // 1. Atividade de Avaliação específica (/avaliacoes/{id})
        if (preg_match('#/avaliacoes/(\d+)#', $url, $matches)) {
            $avaliacaoId = (int)$matches[1];
            $avaliacao = Avaliacao::find($avaliacaoId);

            // Se avaliação não existe, foi cancelada/concluída ou prazo expirou
            if (!$avaliacao || $avaliacao->status !== StatusAvaliacao::ANDAMENTO) {
                return false;
            }
            if ($avaliacao->data_fim && Carbon::parse($avaliacao->data_fim)->endOfDay()->isPast()) {
                return false;
            }

            // Checa se o usuário ainda possui participações pendentes nesta avaliação
            return AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)
                ->where('avaliador_id', $user->id)
                ->where('status', 'pendente')
                ->exists();
        }

        // 2. Atividade de Autoavaliação ou Minhas Avaliações (/minhas-avaliacoes)
        if (str_contains($url, 'minhas-avaliacoes')) {
            // Tenta localizar a avaliação pelo título da atividade
            $avaliacao = Avaliacao::where('titulo', $ativ->titulo)
                ->where(function ($q) use ($user) {
                    if ($user->empresa_id) {
                        $q->where('empresa_id', $user->empresa_id);
                    }
                })
                ->where('status', StatusAvaliacao::ANDAMENTO)
                ->first();

            if ($avaliacao) {
                if ($avaliacao->data_fim && Carbon::parse($avaliacao->data_fim)->endOfDay()->isPast()) {
                    return false;
                }

                return AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                    ->where('avaliador_id', $user->id)
                    ->where('status', 'pendente')
                    ->exists();
            }

            // Fallback genérico: se não encontrou pelo título, verifica se o usuário tem QUALQUER participação pendente em avaliações ativas
            return AvaliacaoParticipante::where('avaliador_id', $user->id)
                ->where('status', 'pendente')
                ->whereHas('avaliacao', function ($q) use ($hoje) {
                    $q->where('status', StatusAvaliacao::ANDAMENTO)
                      ->where(function ($sq) use ($hoje) {
                          $sq->whereNull('data_fim')->orWhereDate('data_fim', '>=', $hoje);
                      });
                })
                ->exists();
        }

        // 3. Atividade de Pesquisa de Clima (/clima/responder/{id})
        if (preg_match('#clima/responder/(\d+)#', $url, $matches)) {
            $surveyId = (int)$matches[1];
            $survey = ClimateSurvey::find($surveyId);

            if (!$survey || $survey->status !== 'ativa') {
                return false;
            }
            if ($survey->data_fim && Carbon::parse($survey->data_fim)->endOfDay()->isPast()) {
                return false;
            }

            return ClimateSurveyParticipation::where('climate_survey_id', $surveyId)
                ->where('user_id', $user->id)
                ->where('status', 'pendente')
                ->exists();
        }

        if (str_contains($url, 'clima/minhas') || str_contains(strtolower($ativ->titulo), 'pesquisa de clima')) {
            return ClimateSurveyParticipation::where('user_id', $user->id)
                ->where('status', 'pendente')
                ->whereHas('survey', function ($q) use ($hoje) {
                    $q->where('status', 'ativa')
                      ->where(function ($sq) use ($hoje) {
                          $sq->whereNull('data_fim')->orWhereDate('data_fim', '>=', $hoje);
                      });
                })
                ->exists();
        }

        // 4. Atividade de Reunião 1:1 (/reunioes/{id} ou /reunioes/{id}/sala)
        if (preg_match('#/reunioes/(\d+)#', $url, $matches)) {
            $reuniaoId = (int)$matches[1];
            $reuniao = ReuniaoAgendamento::find($reuniaoId);

            // Se a reunião não existe, foi concluída ou cancelada, não é mais uma pendência
            if (!$reuniao || in_array($reuniao->status, ['concluido', 'cancelado'])) {
                return false;
            }

            // Se a data agendada já passou há mais de 1 dia, encerra a cobrança diária
            if ($reuniao->data_hora && Carbon::parse($reuniao->data_hora)->endOfDay()->isPast()) {
                return false;
            }

            return $reuniao->status === 'agendado';
        }

        // Para outras atividades não vinculadas a avaliações/pesquisas/reuniões, mantém como válida
        return true;
    }
}

