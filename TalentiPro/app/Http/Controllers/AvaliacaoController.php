<?php

namespace App\Http\Controllers;

use App\Contracts\AvaliacaoServiceContract;
use App\Http\Requests\StoreAvaliacaoRequest;
use App\Http\Requests\UpdateAvaliacaoRequest;
use App\Http\Requests\StoreFechamentoRequest;
use App\Models\Avaliacao;
use App\Models\Competencia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AvaliacaoController extends Controller
{
    public function __construct(private AvaliacaoServiceContract $avaliacaoService)
    {
    }

    public function index()
    {
        Gate::authorize('viewAny', Avaliacao::class);

        $templates = Avaliacao::where('empresa_id', auth()->user()->empresa_id)
            ->where('is_template', true)
            ->with(['competencias.skills'])
            ->withCount(['campanhas'])
            ->orderBy('titulo')
            ->get();

        $query = Avaliacao::where('empresa_id', auth()->user()->empresa_id)
            ->where('is_template', false)
            ->with(['template'])
            ->withCount(['participantes as avaliados_count' => function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw('count(distinct avaliado_id)'));
            }])
            ->orderBy('created_at', 'desc');

        if (auth()->user()->perfil_acesso->value === 'gestor') {
            $query->where('created_by', auth()->id());
        }

        $avaliacoes = $query->get();

        return view('avaliacoes.index', compact('templates', 'avaliacoes'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Avaliacao::class);

        $templates = Avaliacao::where('empresa_id', auth()->user()->empresa_id)
            ->where('is_template', true)
            ->with('competencias.skills')
            ->get();

        $selectedTemplateId = $request->get('template_id');

        $colaboradoresQuery = User::where('empresa_id', auth()->user()->empresa_id);
        if (auth()->user()->perfil_acesso->value === 'gestor') {
            $colaboradoresQuery->where(function($q) {
                $q->where('departamento_id', auth()->user()->departamento_id)
                  ->orWhereNull('departamento_id');
            });
        }
        $colaboradores = $colaboradoresQuery->get();

        $gestores = User::where('empresa_id', auth()->user()->empresa_id)->get();
        $departamentos = \App\Models\Departamento::where('empresa_id', auth()->user()->empresa_id)->get();
        $cargos = \App\Models\Cargo::where('empresa_id', auth()->user()->empresa_id)->get();

        $avaliacao = new Avaliacao();
        if ($selectedTemplateId) {
            $avaliacao->template_id = $selectedTemplateId;
            $selectedTemplate = $templates->firstWhere('id', $selectedTemplateId);
            if ($selectedTemplate) {
                $avaliacao->titulo = $selectedTemplate->titulo;
                $avaliacao->grau = $selectedTemplate->grau;
                $avaliacao->descricao = $selectedTemplate->descricao;
            }
        }

        return view('avaliacoes.create', compact('templates', 'selectedTemplateId', 'colaboradores', 'gestores', 'departamentos', 'cargos', 'avaliacao'));
    }

    public function store(StoreAvaliacaoRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $this->avaliacaoService->criar(
            $request->validated(), 
            $user->empresa_id, 
            $user->id
        );

        return redirect()->route('avaliacoes.index')->with('success', 'Avaliação criada com sucesso!');
    }

    public function show(Avaliacao $avaliacao)
    {
        Gate::authorize('view', $avaliacao);

        $avaliacao->load(['competencias', 'participantes.avaliado', 'participantes.avaliador']);

        $totalVias = $avaliacao->participantes->count();
        $totalRespondidos = $avaliacao->participantes->where('status', 'respondido')->count();
        $totalPendentes = $totalVias - $totalRespondidos;
        
        $engajamento = $totalVias > 0 
            ? round(($totalRespondidos / $totalVias) * 100) 
            : 0;

        // Calcula os participantes únicos reais (quem avalia + quem é avaliado)
        $totalParticipantesUnicos = collect($avaliacao->participantes->pluck('avaliador_id'))
            ->merge($avaliacao->participantes->pluck('avaliado_id'))
            ->unique()
            ->count();

        return view('avaliacoes.show', compact(
            'avaliacao', 
            'totalParticipantesUnicos',
            'totalVias', 
            'totalRespondidos', 
            'totalPendentes', 
            'engajamento'
        ));
    }

    public function edit(Avaliacao $avaliacao)
    {
        Gate::authorize('update', $avaliacao);

        $templates = Avaliacao::where('empresa_id', auth()->user()->empresa_id)
            ->where('is_template', true)
            ->with('competencias.skills')
            ->get();

        $selectedTemplateId = $avaliacao->template_id;
        
        $colaboradoresQuery = User::where('empresa_id', auth()->user()->empresa_id);
        if (auth()->user()->perfil_acesso->value === 'gestor') {
            $colaboradoresQuery->where(function($q) {
                $q->where('departamento_id', auth()->user()->departamento_id)
                  ->orWhereNull('departamento_id');
            });
        }
        $colaboradores = $colaboradoresQuery->get();

        $gestores = User::where('empresa_id', auth()->user()->empresa_id)->get();
        $departamentos = \App\Models\Departamento::where('empresa_id', auth()->user()->empresa_id)->get();
        $cargos = \App\Models\Cargo::where('empresa_id', auth()->user()->empresa_id)->get();

        // Carrega as dependências necessárias para a view preencher os dados
        $avaliacao->load(['competencias', 'participantes.avaliado', 'participantes.avaliador', 'template']);

        return view('avaliacoes.create', compact('templates', 'selectedTemplateId', 'colaboradores', 'gestores', 'departamentos', 'cargos', 'avaliacao'));
    }

    public function update(UpdateAvaliacaoRequest $request, Avaliacao $avaliacao)
    {
        $this->avaliacaoService->atualizar(
            $avaliacao,
            $request->validated()
        );

        return redirect()->route('avaliacoes.index')->with('success', 'Avaliação atualizada com sucesso!');
    }

    public function destroy(Avaliacao $avaliacao)
    {
        Gate::authorize('delete', $avaliacao);

        $this->avaliacaoService->excluir($avaliacao);

        return redirect()->route('avaliacoes.index', ['tab' => 'campanhas'])->with('success', 'Avaliação excluída com sucesso!');
    }

    public function mediar(Avaliacao $avaliacao, User $avaliado)
    {
        Gate::authorize('mediar', $avaliacao);

        // Garante que o avaliado é da mesma empresa
        if ($avaliado->empresa_id !== $avaliacao->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }

        // Verifica se o prazo encerrou
        if ($avaliacao->data_fim && \Carbon\Carbon::parse($avaliacao->data_fim)->endOfDay()->isPast()) {
            return redirect()->route('avaliacoes.minhas')->with('error', 'O prazo para mediação desta avaliação já foi encerrado.');
        }

        $avaliacao->load('competencias.skills');

        // Buscar respostas já dadas por outros participantes (autoavaliação e gestores)
        $participantesRespostas = \App\Models\AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
            ->where('avaliado_id', $avaliado->id)
            ->where('status', 'respondido')
            ->with(['respostas.skill', 'avaliador'])
            ->get();

        $fechamento = \App\Models\AvaliacaoFechamento::where('avaliacao_id', $avaliacao->id)
            ->where('avaliado_id', $avaliado->id)
            ->first();

        // Buscar o participante mediador logado para carregar respostas salvas anteriormente (se existirem)
        $participanteMediador = \App\Models\AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
            ->where('avaliado_id', $avaliado->id)
            ->where('avaliador_id', auth()->id())
            ->where('tipo', 'mediador')
            ->with('respostas')
            ->first();

        return view('avaliacoes.mediar', compact('avaliacao', 'avaliado', 'participantesRespostas', 'fechamento', 'participanteMediador'));
    }

    public function salvarFechamento(StoreFechamentoRequest $request, Avaliacao $avaliacao, User $avaliado)
    {
        Gate::authorize('mediar', $avaliacao);

        // Garante que o avaliado é da mesma empresa
        if ($avaliado->empresa_id !== $avaliacao->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }

        // Verifica se o prazo encerrou
        if ($avaliacao->data_fim && \Carbon\Carbon::parse($avaliacao->data_fim)->endOfDay()->isPast()) {
            return redirect()->route('avaliacoes.minhas')->with('error', 'O prazo para mediação desta avaliação já foi encerrado.');
        }

        $this->avaliacaoService->fechar(
            $avaliacao,
            $avaliado->id,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('avaliacoes.index')
            ->with('success', 'Avaliação consolidada e fechada com sucesso!');
    }
}
