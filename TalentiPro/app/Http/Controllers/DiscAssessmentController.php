<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiscQuestion;
use App\Models\DiscAssessment;
use App\Models\DiscResponse;
use App\Models\User;
use App\Models\Departamento;
use App\Services\CalculateDiscProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiscAssessmentController extends Controller
{
    public function create()
    {
        // Check if user already took the assessment
        $assessment = DiscAssessment::where('user_id', Auth::id())->first();
        if ($assessment) {
            return redirect()->route('disc.result', $assessment->id)->with('info', 'Você já realizou o Teste DISC.');
        }

        $questions = DiscQuestion::with('options')->orderBy('block_number')->get();
        return view('disc.take', compact('questions'));
    }

    public function retake()
    {
        $assessment = DiscAssessment::where('user_id', Auth::id())->first();
        if ($assessment) {
            $assessment->delete(); // Cascade deletes responses
        }
        return redirect()->route('disc.take');
    }

    public function store(Request $request, CalculateDiscProfileService $service)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.*' => 'required|integer|min:1|max:4', // responses[question_id][option_id] = score
        ]);

        DB::beginTransaction();
        try {
            $assessment = DiscAssessment::create([
                'user_id' => Auth::id()
            ]);

            foreach ($request->input('responses') as $questionId => $options) {
                // Ensure unique scores per question block if you want backend validation, though Alpine handles UI
                $scores = array_values($options);
                if (count(array_unique($scores)) !== 4) {
                    throw new \Exception('As notas 1, 2, 3 e 4 devem ser distribuídas sem repetição em cada bloco.');
                }

                foreach ($options as $optionId => $score) {
                    DiscResponse::create([
                        'disc_assessment_id' => $assessment->id,
                        'disc_option_id' => $optionId,
                        'score' => $score,
                    ]);
                }
            }

            $service->execute($assessment);
            DB::commit();

            return redirect()->route('disc.result', $assessment->id)->with('success', 'Teste DISC finalizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar o teste: ' . $e->getMessage());
        }
    }

    public function show(DiscAssessment $assessment)
    {
        // Policy check could go here if implemented, for now, let's ensure it's the owner or admin/gestor
        $role = Auth::user()->perfil_acesso->value ?? Auth::user()->perfil_acesso;
        if ($assessment->user_id !== Auth::id() && !in_array($role, ['master', 'gestor', 'admin'])) {
            abort(403);
        }

        return view('disc.result', compact('assessment'));
    }

    public function equipe(Request $request)
    {
        $user = Auth::user();
        $role = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if (!in_array($role, ['master', 'gestor', 'admin'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = User::with(['discAssessment', 'departamento', 'cargo'])
            ->where('empresa_id', $user->empresa_id);

        if ($role === 'gestor') {
            $query->where(function ($q) use ($user) {
                $q->where('departamento_id', $user->departamento_id)
                  ->orWhereNull('departamento_id');
            })->where('id', '!=', $user->id);
        }

        // Filtro por Nome ou Email
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por Departamento
        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        $colaboradoresRaw = $query->orderBy('name')->get();

        // Filtro por Perfil DISC (Dominante, Influente, Estável, Cauteloso)
        if ($request->filled('perfil')) {
            $perfilFiltro = strtoupper($request->perfil);
            $colaboradoresRaw = $colaboradoresRaw->filter(function($u) use ($perfilFiltro) {
                return $u->discAssessment && strtoupper($u->discAssessment->primary_profile) === $perfilFiltro;
            });
        }

        // Filtro por Status (concluido / pendente)
        if ($request->filled('status')) {
            $statusFiltro = $request->status;
            $colaboradoresRaw = $colaboradoresRaw->filter(function($u) use ($statusFiltro) {
                if ($statusFiltro === 'concluido') {
                    return !is_null($u->discAssessment);
                } elseif ($statusFiltro === 'pendente') {
                    return is_null($u->discAssessment);
                }
                return true;
            });
        }

        $colaboradores = $colaboradoresRaw;

        // Dados estatísticos da empresa / equipe
        $baseUsersQuery = User::with('discAssessment')->where('empresa_id', $user->empresa_id);
        if ($role === 'gestor') {
            $baseUsersQuery->where(function ($q) use ($user) {
                $q->where('departamento_id', $user->departamento_id)
                  ->orWhereNull('departamento_id');
            })->where('id', '!=', $user->id);
        }
        $allTeamUsers = $baseUsersQuery->get();

        $totalColaboradores = $allTeamUsers->count();
        $totalConcluidos = $allTeamUsers->filter(fn($u) => !is_null($u->discAssessment))->count();
        $totalPendentes = $totalColaboradores - $totalConcluidos;

        $statsPerfis = [
            'DOMINANCE'  => 0,
            'INFLUENCE'  => 0,
            'STEADINESS' => 0,
            'COMPLIANCE' => 0,
        ];

        foreach ($allTeamUsers as $u) {
            if ($u->discAssessment && isset($statsPerfis[$u->discAssessment->primary_profile])) {
                $statsPerfis[$u->discAssessment->primary_profile]++;
            }
        }

        $departamentos = Departamento::where('empresa_id', $user->empresa_id)->orderBy('nome')->get();

        return view('disc.equipe', compact(
            'colaboradores',
            'departamentos',
            'totalColaboradores',
            'totalConcluidos',
            'totalPendentes',
            'statsPerfis'
        ));
    }
}
