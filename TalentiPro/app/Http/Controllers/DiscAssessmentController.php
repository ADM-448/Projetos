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
        $existing = DiscAssessment::where('user_id', Auth::id())->latest()->first();
        if ($existing) {
            return redirect()->route('disc.result', $existing->id)->with('info', 'Você já respondeu ao Teste DISC.');
        }

        $questions = DiscQuestion::with('options')->orderBy('block_number')->get();
        return view('disc.take', compact('questions'));
    }

    public function retake()
    {
        // Deleta a avaliação atual do utilizador para permitir fazer uma nova
        $existing = DiscAssessment::where('user_id', Auth::id())->latest()->first();
        if ($existing) {
            $existing->delete();
        }
        
        return redirect()->route('disc.take');
    }

    public function liberar(DiscAssessment $assessment)
    {
        $user = Auth::user();
        $role = $user->perfil_acesso->value ?? $user->perfil_acesso;
        
        if (!in_array($role, ['master', 'admin', 'gestor'])) {
            abort(403);
        }

        $colaboradorId = $assessment->user_id;
        $assessment->delete();

        // Notifica o colaborador se ele existir (não for link externo)
        if ($colaboradorId) {
            \App\Models\Atividade::create([
                'user_id'    => $colaboradorId,
                'empresa_id' => $user->empresa_id,
                'icone'      => 'fa-solid fa-rotate-right',
                'cor'        => '#10b981',
                'titulo'     => 'Teste DISC Liberado',
                'descricao'  => 'O gestor/RH liberou para você refazer o Teste DISC.',
                'url'        => route('disc.take'),
                'lida'       => false,
            ]);
        }

        return redirect()->route('disc.equipe')->with('success', 'Teste excluído e liberado para o colaborador refazer.');
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
                'user_id' => Auth::id(),
                'empresa_id' => Auth::user()->empresa_id,
                'origin' => 'internal'
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

            // Notificar os masters/admins da empresa
            $masters = User::where('empresa_id', Auth::user()->empresa_id)
                ->whereIn('perfil_acesso', ['master', 'admin'])
                ->get();
            
            $whatsappService = app(\App\Services\WhatsAppService::class);

            foreach ($masters as $master) {
                \App\Models\Atividade::create([
                    'user_id'    => $master->id,
                    'empresa_id' => $master->empresa_id,
                    'icone'      => 'fa-solid fa-address-card',
                    'cor'        => '#8b5cf6',
                    'titulo'     => 'Novo Teste DISC',
                    'descricao'  => Auth::user()->name . ' finalizou o Teste DISC.',
                    'url'        => route('disc.result', $assessment->id),
                    'lida'       => false,
                ]);

                if (!empty($master->telefone)) {
                    $mensagem = "Olá {$master->name}! 🌟\n\n*". Auth::user()->name ."* acabou de finalizar o preenchimento do *Teste DISC* na plataforma.\n\nAcesse a plataforma para conferir o resultado!";
                    $whatsappService->enviar($master->telefone, $mensagem, $master->id);
                }
            }

            return redirect()->route('disc.result', $assessment->id)->with('success', 'Teste DISC finalizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar o teste: ' . $e->getMessage());
        }
    }

    public function show(DiscAssessment $assessment)
    {
        $user = Auth::user();
        $role = $user->perfil_acesso->value ?? $user->perfil_acesso;

        // Se for gestor/master e o teste pertence à mesma empresa, permitir acesso
        // Se for o dono do teste, permitir acesso
        if ($assessment->user_id !== $user->id) {
            if (!in_array($role, ['master', 'gestor', 'admin', 'consultor'])) {
                abort(403);
            }
            
            $testEmpresaId = $assessment->empresa_id;
            if (!$testEmpresaId && $assessment->user) {
                $testEmpresaId = $assessment->user->empresa_id;
            }
            
            if ($testEmpresaId !== $user->empresa_id) {
                abort(403);
            }
        }

        // Carregar histórico do dono deste teste (se houver dono)
        $historico = collect();
        if ($assessment->user_id) {
            $historico = DiscAssessment::where('user_id', $assessment->user_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('disc.result', compact('assessment', 'historico'));
    }

    public function createExternal($token_ou_id)
    {
        // Validar empresa
        $empresa = \App\Models\Empresa::find($token_ou_id);
        if (!$empresa) {
            abort(404, 'Empresa não encontrada.');
        }

        $questions = DiscQuestion::with('options')->orderBy('block_number')->get();
        return view('disc.external_take', compact('questions', 'empresa'));
    }

    public function storeExternal(Request $request, $token_ou_id, CalculateDiscProfileService $service)
    {
        $empresa = \App\Models\Empresa::findOrFail($token_ou_id);

        $request->validate([
            'respondent_name' => 'required|string|max:255',
            'respondent_email' => 'required|email|max:255',
            'responses' => 'required|array',
            'responses.*.*' => 'required|integer|min:1|max:4',
        ]);

        DB::beginTransaction();
        try {
            // Verifica se já existe um usuário com esse email na empresa
            $user = User::where('empresa_id', $empresa->id)->where('email', $request->respondent_email)->first();

            $assessment = DiscAssessment::create([
                'empresa_id' => $empresa->id,
                'user_id' => $user ? $user->id : null,
                'respondent_name' => $request->respondent_name,
                'respondent_email' => $request->respondent_email,
                'origin' => 'external_form',
                'public_token' => (string) \Illuminate\Support\Str::uuid()
            ]);

            foreach ($request->input('responses') as $questionId => $options) {
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

            // Notificar os masters/admins da empresa
            $masters = User::where('empresa_id', $empresa->id)
                ->whereIn('perfil_acesso', ['master', 'admin'])
                ->get();
            
            $whatsappService = app(\App\Services\WhatsAppService::class);

            foreach ($masters as $master) {
                \App\Models\Atividade::create([
                    'user_id'    => $master->id,
                    'empresa_id' => $master->empresa_id,
                    'icone'      => 'fa-solid fa-address-card',
                    'cor'        => '#8b5cf6',
                    'titulo'     => 'Novo Teste DISC (Externo)',
                    'descricao'  => $request->respondent_name . ' finalizou o Teste DISC externo.',
                    'url'        => route('disc.result', $assessment->id),
                    'lida'       => false,
                ]);

                if (!empty($master->telefone)) {
                    $mensagem = "Olá {$master->name}! 🌟\n\nUm candidato externo (*{$request->respondent_name}*) acabou de finalizar o preenchimento do *Teste DISC* através do link público.\n\nAcesse a plataforma para conferir o resultado!";
                    $whatsappService->enviar($master->telefone, $mensagem, $master->id);
                }
            }

            return redirect()->route('disc.thanks');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar o teste: ' . $e->getMessage())->withInput();
        }
    }

    public function thanksExternal()
    {
        return view('disc.external_thanks');
    }

    public function equipe(Request $request)
    {
        $user = Auth::user();
        $role = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if (!in_array($role, ['master', 'gestor', 'admin', 'consultor'])) {
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

        // Buscar avaliações externas que ainda não têm usuário
        $externalAssessments = DiscAssessment::where('empresa_id', $user->empresa_id)
            ->whereNull('user_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Para os externos, os filtros de busca e perfil também se aplicam
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $externalAssessments = $externalAssessments->filter(function($a) use ($search) {
                return str_contains(strtolower($a->respondent_name), $search) || str_contains(strtolower($a->respondent_email), $search);
            });
        }
        if ($request->filled('perfil')) {
            $perfilFiltro = strtoupper($request->perfil);
            $externalAssessments = $externalAssessments->filter(function($a) use ($perfilFiltro) {
                return strtoupper($a->primary_profile) === $perfilFiltro;
            });
        }
        if ($request->filled('status') && $request->status === 'pendente') {
            $externalAssessments = collect(); // Externos por definição já responderam
        }

        $colaboradores = $colaboradoresRaw;
        // Não vamos misturar os modelos de User e DiscAssessment no mesmo array de forma bruta
        // Vamos passar os externos separados ou transformá-los num wrapper. Para manter simples, passaremos separados.

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

        // Somar os perfis dos externos nas estatísticas
        $todasExternas = DiscAssessment::where('empresa_id', $user->empresa_id)->whereNull('user_id')->get();
        foreach ($todasExternas as $ext) {
            if (isset($statsPerfis[$ext->primary_profile])) {
                $statsPerfis[$ext->primary_profile]++;
            }
        }
        $totalConcluidos += $todasExternas->count();
        // O total de colaboradores não inclui externos na porcentagem pendente, pois não sabemos quem são.
        // Ou você pode somar ao totalColaboradores.
        $totalColaboradores += $todasExternas->count();

        $departamentos = Departamento::where('empresa_id', $user->empresa_id)->orderBy('nome')->get();

        return view('disc.equipe', compact(
            'colaboradores',
            'externalAssessments',
            'departamentos',
            'totalColaboradores',
            'totalConcluidos',
            'totalPendentes',
            'statsPerfis'
        ));
    }
}
