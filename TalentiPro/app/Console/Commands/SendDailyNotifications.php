<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Atividade;
use App\Services\WhatsAppService;
use App\Mail\AtividadesPendentesMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        // 1. Busca todos os usuários que possuem atividades não lidas
        $usersWithPending = User::whereHas('atividades', function ($query) {
            $query->where('lida', false);
        })->with(['atividades' => function ($query) {
            $query->where('lida', false)->orderBy('created_at', 'desc');
        }])->get();

        $this->info("Iniciando disparo diário. Usuários com pendências: " . $usersWithPending->count());

        foreach ($usersWithPending as $user) {
            $atividades = $user->atividades;
            $count = $atividades->count();

            // ---------- 1. DISPARO DE WHATSAPP ----------
            try {
                // Monta a string resumida pro WhatsApp
                $lista = "";
                foreach ($atividades as $ativ) {
                    $lista .= "- {$ativ->titulo}\n";
                }

                $mensagemFormatada = "Olá {$user->name}! 👋\n\n"
                    . "Você possui *{$count}* atividade(s) aguardando sua atenção no TalentiPro:\n\n"
                    . $lista . "\n"
                    . "Veja os detalhes no seu E-mail ou acesse a plataforma:\n"
                    . "🔗 " . config('app.url') . "/login";

                // Se o usuário tiver um empresa_id na model, usa ele. Se não, tenta pegar da pivot se existir.
                // Como User belongsToMany Empresa, se tiver empresa padrão, podemos setar. 
                // Mas o notificar() exige o $user e a $mensagemFormatada. O setEmpresaId() pode ser a primeira empresa dele.
                $empresaId = $user->empresas()->first()->id ?? null;
                
                if ($empresaId) {
                    $whatsappService->setEmpresaId($empresaId)->notificar($user, $mensagemFormatada);
                    $this->info("WhatsApp enviado para {$user->name}");
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
                    Mail::to($user->email)->send(new AtividadesPendentesMail($user, $atividades));
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
}
