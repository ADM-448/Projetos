<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MarketSalary;
use App\Jobs\ScrapeMarketSalaryJob;
use Illuminate\Support\Facades\Log;

class UpdateWeeklyMarketSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:update-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza automaticamente no banco a base de salários regionais de mercado mais antigos que 7 dias.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando varredura semanal da base de salários de mercado...');

        // Busca salários de mercado que não foram atualizados nos últimos 7 dias (ou nunca foram)
        $outdatedSalaries = MarketSalary::where('last_scraped_at', '<', now()->subDays(7))
            ->orWhereNull('last_scraped_at')
            ->get();

        if ($outdatedSalaries->isEmpty()) {
            $this->info('Todos os salários de mercado no banco estão atualizados!');
            return 0;
        }

        $count = 0;
        foreach ($outdatedSalaries as $marketSalary) {
            $marketSalary->update(['status' => 'pending']);
            ScrapeMarketSalaryJob::dispatch($marketSalary->id);
            $count++;
        }

        $msg = "{$count} pesquisas salariais de mercado foram enviadas para re-atualização na fila.";
        Log::info("[Scheduler] {$msg}");
        $this->info($msg);

        return 0;
    }
}
