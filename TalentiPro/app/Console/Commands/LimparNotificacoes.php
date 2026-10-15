<?php

namespace App\Console\Commands;

use App\Models\Atividade;
use Illuminate\Console\Command;

class LimparNotificacoes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificacoes:limpar {--dias=3 : Remove notificações criadas há mais de N dias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove automaticamente as notificações (atividades) com mais de N dias (padrão: 3)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dias = (int) $this->option('dias');

        if ($dias < 1) {
            $this->error('O valor de --dias deve ser maior ou igual a 1.');
            return self::FAILURE;
        }

        $removidas = Atividade::where('created_at', '<', now()->subDays($dias))->delete();

        $this->info("{$removidas} notificação(ões) com mais de {$dias} dia(s) removida(s).");

        return self::SUCCESS;
    }
}
