<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessarLembretes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lembretes:processar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa e envia e-mails para os lembretes pendentes cuja data chegou';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lembretes = \App\Models\Lembrete::where('status', 'pendente')
                        ->where('data_agendada', '<=', now())
                        ->get();

        if ($lembretes->isEmpty()) {
            $this->info('Nenhum lembrete pendente para hoje.');
            return;
        }

        foreach ($lembretes as $lembrete) {
            $user = $lembrete->user;
            
            // Cria a atividade de notificação local no sistema
            \App\Services\AtividadeService::registrar(
                $user->empresa_id,
                'Lembrete: ' . $lembrete->mensagem,
                'O seu lembrete agendado chegou. Verifique as suas pendências.',
                'fa-solid fa-bell',
                '#3b82f6',
                $user->id
            );

            // Envia o e-mail de notificação
            app(\App\Services\EmailService::class)->notificar(
                $user,
                'Notificação de Lembrete',
                "Você tem um lembrete pendente: {$lembrete->mensagem}. Acesse o sistema para verificar suas pendências.",
                url('/dashboard'),
                'Acessar Dashboard'
            );

            $lembrete->update(['status' => 'enviado']);
        }

        $this->info("Processados {$lembretes->count()} lembretes.");
    }
}
