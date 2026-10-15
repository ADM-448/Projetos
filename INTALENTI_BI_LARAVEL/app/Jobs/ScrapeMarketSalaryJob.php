<?php

namespace App\Jobs;

use App\Models\MarketSalary;
use App\Services\MarketSalary\MarketSalaryAggregator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeMarketSalaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public $marketSalaryId)
    {
    }

    public function handle(MarketSalaryAggregator $aggregator): void
    {
        $marketSalary = MarketSalary::find($this->marketSalaryId);

        if (! $marketSalary) {
            return;
        }

        try {
            $resolved = $aggregator->resolve($marketSalary);

            $marketSalary->update([
                'status' => 'completed',
                'mediana' => $resolved['mediana'],
                'piso' => $resolved['piso'],
                'teto' => $resolved['teto'],
                'last_scraped_at' => now(),
                'raw_data' => $resolved['raw_data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no ScrapeMarketSalaryJob: '.$e->getMessage(), [
                'market_salary_id' => $this->marketSalaryId,
            ]);

            $marketSalary->update([
                'status' => 'failed',
                'raw_data' => ['error' => $e->getMessage()],
            ]);

            $this->release(10);
        }
    }
}
