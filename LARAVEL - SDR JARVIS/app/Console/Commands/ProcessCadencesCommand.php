<?php

namespace App\Console\Commands;

use App\Services\CadenceExecutionService;
use Illuminate\Console\Command;

class ProcessCadencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cadence:process {--dry-run : Apenas simular sem enviar mensagens ou avançar etapas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Varre e dispara automaticamente tarefas de cadências ativas cujo prazo já venceu';

    /**
     * Execute the console command.
     */
    public function handle(CadenceExecutionService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info("⚡ [JARVIS] Iniciando motor de cadências automáticas..." . ($dryRun ? ' (Modo Simulação)' : ''));

        $result = $service->processPendingCadences($dryRun);

        $totalPending = $result['total_pending'];
        $executed = count($result['executed']);
        $skipped = count($result['skipped']);
        $errors = count($result['errors']);

        $this->line("Tarefas pendentes encontradas: <comment>{$totalPending}</comment>");
        $this->line("Disparadas/Executadas: <info>{$executed}</info>");
        $this->line("Aguardando ação manual do SDR: <comment>{$skipped}</comment>");

        if ($errors > 0) {
            $this->error("Erros encontrados: {$errors}");
            foreach ($result['errors'] as $err) {
                $this->error("- Tarefa {$err['task_id']}: {$err['error']}");
            }
        }

        if ($executed > 0) {
            $this->newLine();
            $this->table(
                ['ID', 'Tipo', 'Empresa', 'Lead', 'Título'],
                array_map(function ($item) {
                    return [
                        $item['task_id'],
                        strtoupper($item['type']),
                        $item['company'] ?? '-',
                        $item['lead'] ?? '-',
                        $item['title'] ?? '-',
                    ];
                }, $result['executed'])
            );
        }

        $this->info(" Concluído com sucesso às {$result['processed_at']}.");

        return Command::SUCCESS;
    }
}
