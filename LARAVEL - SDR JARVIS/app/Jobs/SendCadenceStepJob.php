<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\CadenceExecutionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCadenceStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    protected int $taskId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * Execute the job.
     */
    public function handle(CadenceExecutionService $service): void
    {
        $task = Task::withoutGlobalScopes()->find($this->taskId);

        if (!$task) {
            Log::warning("[SendCadenceStepJob] Tarefa {$this->taskId} não encontrada.");
            return;
        }

        if ($task->status !== 'pending') {
            Log::info("[SendCadenceStepJob] Tarefa {$this->taskId} já processada (status: {$task->status}).");
            return;
        }

        try {
            $service->executeTask($task);
        } catch (Exception $e) {
            Log::error("[SendCadenceStepJob] Erro ao executar tarefa {$this->taskId}: " . $e->getMessage());
            throw $e;
        }
    }
}
