<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Garante que haja usuário autenticado na sessão de desenvolvimento.
     */
    protected function ensureAuthenticatedUser(): void
    {
        if (! Auth::check()) {
            $user = User::withoutGlobalScopes()->first();
            if ($user) {
                Auth::login($user);
            }
        }
    }

    /**
     * Cria uma nova tarefa / lembrete para a empresa.
     */
    public function store(Request $request, Company $company): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:call,email,whatsapp,meeting,followup,other',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_at' => 'nullable|date',
            'lead_id' => 'nullable|exists:leads,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();
        $tenantId = $company->tenant_id ?? ($user?->tenant_id ?? 1);

        $task = Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'lead_id' => $validated['lead_id'] ?? null,
            'user_id' => $validated['user_id'] ?? $user?->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? 'followup',
            'priority' => $validated['priority'] ?? 'medium',
            'due_at' => $validated['due_at'] ?? null,
            'status' => 'pending',
        ]);

        // Registra atividade na timeline da empresa
        Activity::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'lead_id' => $validated['lead_id'] ?? null,
            'user_id' => $user?->id,
            'type' => 'note',
            'description' => "📌 Nova tarefa criada: {$task->title}" . ($task->due_at ? " (Vencimento: {$task->due_at->format('d/m/Y H:i')})" : ''),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->load(['user:id,name', 'lead:id,name']),
                'message' => 'Tarefa criada com sucesso!',
            ]);
        }

        return back()->with('success', 'Tarefa criada com sucesso!');
    }

    /**
     * Alterna o status da tarefa entre concluída e pendente.
     */
    public function toggle(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $isPending = $task->status === 'pending';
        $newStatus = $isPending ? 'completed' : 'pending';
        $completedAt = $isPending ? now() : null;

        $task->update([
            'status' => $newStatus,
            'completed_at' => $completedAt,
        ]);

        $user = Auth::user();
        if ($newStatus === 'completed') {
            Activity::withoutGlobalScopes()->create([
                'tenant_id' => $task->tenant_id,
                'company_id' => $task->company_id,
                'lead_id' => $task->lead_id,
                'user_id' => $user?->id,
                'type' => 'note',
                'description' => "✅ Tarefa concluída: {$task->title}",
            ]);

            // Se for tarefa de uma cadência ativa, avança automaticamente para o próximo passo!
            if ($task->company_cadence_id) {
                $enrollment = \App\Models\CompanyCadence::find($task->company_cadence_id);
                if ($enrollment && $enrollment->status === 'active') {
                    CadenceController::advanceNextStep($enrollment);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh(['user:id,name', 'lead:id,name']),
                'message' => $isPending ? 'Tarefa concluída com sucesso!' : 'Tarefa reaberta!',
            ]);
        }

        return back()->with('success', $isPending ? 'Tarefa concluída com sucesso!' : 'Tarefa reaberta!');
    }

    /**
     * Remove uma tarefa.
     */
    public function destroy(Request $request, Task $task): RedirectResponse|JsonResponse
    {
        $this->ensureAuthenticatedUser();

        $task->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tarefa removida com sucesso!',
            ]);
        }

        return back()->with('success', 'Tarefa removida com sucesso!');
    }
}
