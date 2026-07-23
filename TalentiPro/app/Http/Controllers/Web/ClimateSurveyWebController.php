<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\OpenClimateSurveyAction;
use App\Actions\SubmitClimateSurveyAction;
use App\Data\SubmitSurveyData;
use App\Exceptions\DuplicateParticipationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitSurveyRequest;
use App\Models\ClimateCategory;
use App\Models\ClimateResponse;
use App\Models\ClimateSurvey;
use App\Models\ClimateSurveyParticipation;
use App\Services\CalculateClimateMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClimateSurveyWebController extends Controller
{
    public function __construct(
        private readonly CalculateClimateMetricsService $metricsService,
        private readonly SubmitClimateSurveyAction $submitAction,
        private readonly OpenClimateSurveyAction $openAction,
    ) {}

    public function index(): View
    {
        $surveys = ClimateSurvey::orderByDesc('created_at')->get();
        $user = auth()->user();

        // Mapeia quais pesquisas o usuário logado já respondeu
        $participations = ClimateSurveyParticipation::where('user_id', $user->id)
            ->pluck('survey_id')
            ->toArray();

        return view('clima.index', compact('surveys', 'participations', 'user'));
    }

    public function create(): View|RedirectResponse
    {
        if (auth()->user()->perfil_acesso !== 'master') {
            abort(403, 'Apenas o master pode criar pesquisas.');
        }

        $categories = ClimateCategory::orderBy('nome')->get();
        return view('clima.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()->perfil_acesso !== 'master') {
            abort(403);
        }

        $validated = $request->validate([
            'titulo'                 => ['required', 'string', 'max:255'],
            'descricao'              => ['nullable', 'string'],
            'data_inicio'            => ['required', 'date'],
            'data_fim'               => ['required', 'date', 'after_or_equal:data_inicio'],
            'questions'              => ['required', 'array', 'min:1'],
            'questions.*.texto'      => ['required', 'string'],
            'questions.*.tipo'       => ['required', 'in:nota,texto'],
            'questions.*.category_id'=> ['nullable', 'exists:climate_categories,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $survey = ClimateSurvey::create([
                'empresa_id'  => auth()->user()->empresa_id,
                'titulo'      => $validated['titulo'],
                'descricao'   => $validated['descricao'],
                'data_inicio' => $validated['data_inicio'],
                'data_fim'    => $validated['data_fim'],
                'status'      => 'rascunho',
                'created_by'  => auth()->id(),
            ]);

            foreach ($validated['questions'] as $index => $q) {
                $survey->questions()->create([
                    'texto'       => $q['texto'],
                    'tipo'        => $q['tipo'],
                    'category_id' => $q['category_id'],
                    'ordem'       => $index,
                    'obrigatoria' => true,
                ]);
            }
        });

        return redirect()->route('clima.index')->with('success', 'Pesquisa criada com sucesso!');
    }

    public function show(int $id): View|RedirectResponse
    {
        $survey = ClimateSurvey::with('questions.category')->findOrFail($id);
        $user = auth()->user();

        $alreadyParticipated = ClimateSurveyParticipation::where('survey_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        return view('clima.show', compact('survey', 'alreadyParticipated'));
    }

    public function submit(SubmitSurveyRequest $request, int $id): RedirectResponse
    {
        $survey = ClimateSurvey::findOrFail($id);

        if (!$survey->isAtiva()) {
            return back()->with('error', 'Esta pesquisa não está ativa para respostas.');
        }

        $user = auth()->user();

        $data = new SubmitSurveyData(
            surveyId: $survey->id,
            userId: $user->id,
            empresaId: $user->empresa_id,
            departmentId: $user->departamento_id,
            respostas: $request->validated('respostas')
        );

        try {
            $this->submitAction->handle($data);
            return redirect()->route('clima.index')->with('success', 'Obrigado por participar! Suas respostas foram salvas anonimamente.');
        } catch (DuplicateParticipationException $e) {
            return redirect()->route('clima.index')->with('error', $e->getMessage());
        }
    }

    public function abrir(int $id): RedirectResponse
    {
        if (auth()->user()->perfil_acesso !== 'master') {
            abort(403);
        }

        $survey = ClimateSurvey::findOrFail($id);
        $this->openAction->handle($survey);

        return back()->with('success', 'Pesquisa aberta! Notificações enviadas para a equipe.');
    }

    public function encerrar(int $id): RedirectResponse
    {
        if (auth()->user()->perfil_acesso !== 'master') {
            abort(403);
        }

        $survey = ClimateSurvey::findOrFail($id);
        $survey->update(['status' => 'encerrada']);

        return back()->with('success', 'Pesquisa encerrada com sucesso.');
    }

    public function toggleGestor(int $id, Request $request): RedirectResponse
    {
        if (auth()->user()->perfil_acesso !== 'master') {
            abort(403);
        }

        $survey = ClimateSurvey::findOrFail($id);
        $novoValor = $survey->visibilidade_gestor === 'geral' ? 'apenas_departamento' : 'geral';
        $survey->update(['visibilidade_gestor' => $novoValor]);

        return back()->with('success', 'Visibilidade dos gestores atualizada.');
    }

    public function toggleColaborador(int $id, Request $request): RedirectResponse
    {
        if (auth()->user()->perfil_acesso !== 'master') {
            abort(403);
        }

        $survey = ClimateSurvey::findOrFail($id);
        $survey->update(['resultado_liberado_colaboradores' => !$survey->resultado_liberado_colaboradores]);

        return back()->with('success', 'Liberação para colaboradores atualizada.');
    }

    public function dashboard(int $id, Request $request): View
    {
        $user = auth()->user();
        $survey = ClimateSurvey::findOrFail($id);

        // Resolução de visibilidade baseada no plano
        if ($user->perfil_acesso === 'colaborador') {
            abort_unless($survey->resultado_liberado_colaboradores, 403, 'Os resultados ainda não foram liberados.');
            $deptId = null; // colaborador sempre vê geral quando liberado
        } elseif ($user->perfil_acesso === 'gestor') {
            $deptId = $survey->visibilidade_gestor === 'apenas_departamento' ? $user->departamento_id : null;
        } else {
            // Master / RH
            $deptId = $request->department_id ?? null; // Podem filtrar pela URL se quiserem
        }

        $metricas = $this->metricsService->getMetricsBySurvey($survey->id, $user->empresa_id, $deptId);
        $departamentos = \App\Models\Departamento::orderBy('nome')->get(); // Para o filtro

        return view('clima.dashboard', compact('metricas', 'user', 'survey', 'deptId', 'departamentos'));
    }

    public function auditoria(int $id): View
    {
        // 🚨 Proteção Máxima: Apenas o ADMIN da plataforma (não o master da empresa)
        if (auth()->user()->perfil_acesso !== 'admin') {
            abort(403, 'Acesso restrito à auditoria.');
        }

        $survey = ClimateSurvey::withoutGlobalScope('empresa')->findOrFail($id);
        $respostasRaw = ClimateResponse::withoutGlobalScope('empresa')
            ->where('survey_id', $id)
            ->with(['question', 'departamento'])
            ->get();

        $respostas = [];
        foreach ($respostasRaw as $r) {
            $userId = Crypt::decryptString($r->encrypted_user_id);
            // Busca o usuário apenas para exibir o nome na auditoria
            $userAudit = \App\Models\User::withoutGlobalScope('empresa')->find($userId);
            
            $respostas[] = [
                'usuario'      => $userAudit ? $userAudit->name : 'Desconhecido',
                'email'        => $userAudit ? $userAudit->email : 'N/A',
                'pergunta'     => $r->question->texto,
                'departamento' => $r->departamento ? $r->departamento->nome : 'N/A',
                'resposta'     => $r->nota ?? $r->texto_resposta,
                'data'         => $r->created_at->format('d/m/Y H:i:s'),
            ];
        }

        return view('clima.auditoria', compact('respostas', 'survey'));
    }
}
