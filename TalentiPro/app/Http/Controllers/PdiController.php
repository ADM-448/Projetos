<?php

namespace App\Http\Controllers;

use App\Contracts\PdiServiceContract;
use App\Http\Requests\StorePdiRequest;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Models\PdiGrupo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PdiController extends Controller
{
    public function __construct(
        private readonly PdiServiceContract $pdiService
    ) {}

    /**
     * Lista os PDIs do usuário.
     */
    public function index()
    {
        Gate::authorize('viewAny', Pdi::class);

        $user = auth()->user();
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        // 1. Carregar meus PDIs (Onde eu sou o colaborador)
        $meusPdis = Pdi::where('colaborador_id', $user->id)
            ->with(['gestor'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdisEquipe = collect();
        $pdiGrupos = collect();

        // 2. Se for Gestor, Master ou Consultor, carregar PDIs gerenciados/equipe
        if ($perfil === 'master' || $perfil === 'admin' || $perfil === 'consultor') {
            $pdisEquipe = Pdi::where('colaborador_id', '!=', $user->id)
                ->with(['colaborador', 'gestor'])
                ->orderBy('created_at', 'desc')
                ->get();

            $pdiGrupos = PdiGrupo::with(['gestor'])
                ->withCount('pdis')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($perfil === 'gestor') {
            // Colaboradores do mesmo departamento ou sem departamento, exceto eu mesmo
            $colaboradoresIds = User::where(function ($q) use ($user) {
                $q->where('departamento_id', $user->departamento_id)
                  ->orWhereNull('departamento_id');
            })
            ->where('id', '!=', $user->id)
            ->pluck('id');

            $pdisEquipe = Pdi::whereIn('colaborador_id', $colaboradoresIds)
                ->with(['colaborador', 'gestor'])
                ->orderBy('created_at', 'desc')
                ->get();

            $pdiGrupos = PdiGrupo::where('gestor_id', $user->id)
                ->with(['gestor'])
                ->withCount('pdis')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // 3. KPIs Rápidos (Métricas consolidadas)
        // Se for colaborador comum, o KPI mostra apenas seus dados. Caso contrário, traz de todos os liderados/empresa.
        if ($perfil === 'colaborador') {
            $kpiTotal = $meusPdis->count();
            $kpiAndamento = $meusPdis->where('status', \App\Enums\StatusPdi::EM_ANDAMENTO)->count();
            $kpiConcluidos = $meusPdis->where('status', \App\Enums\StatusPdi::CONCLUIDO)->count();
            $kpiNaoIniciados = $meusPdis->where('status', \App\Enums\StatusPdi::NAO_INICIADO)->count();
        } else {
            $kpiTotal = Pdi::count();
            $kpiAndamento = Pdi::where('status', \App\Enums\StatusPdi::EM_ANDAMENTO)->count();
            $kpiConcluidos = Pdi::where('status', \App\Enums\StatusPdi::CONCLUIDO)->count();
            $kpiNaoIniciados = Pdi::where('status', \App\Enums\StatusPdi::NAO_INICIADO)->count();
        }

        return view('pdis.index', compact(
            'meusPdis',
            'pdisEquipe',
            'pdiGrupos',
            'kpiTotal',
            'kpiAndamento',
            'kpiConcluidos',
            'kpiNaoIniciados'
        ));
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        Gate::authorize('create', Pdi::class);

        $user = auth()->user();
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        // Seletor de colaboradores com busca Choices.js
        if ($perfil === 'master' || $perfil === 'admin') {
            $colaboradores = User::where('empresa_id', $user->empresa_id)
                ->orderBy('name')
                ->get();
        } else {
            // Gestor: Colaboradores do mesmo departamento ou sem departamento, exceto eu mesmo
            $colaboradores = User::where('empresa_id', $user->empresa_id)
                ->where(function ($q) use ($user) {
                    $q->where('departamento_id', $user->departamento_id)
                      ->orWhereNull('departamento_id');
                })
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();
        }

        return view('pdis.create', compact('colaboradores'));
    }

    /**
     * Salva o PDI (Individual ou Grupo).
     */
    public function store(StorePdiRequest $request)
    {
        Gate::authorize('create', Pdi::class);

        $user = auth()->user();
        $dados = $request->validated();

        if ($dados['tipo_criacao'] === 'individual') {
            $this->pdiService->criarPdi(
                $dados, 
                $dados['acoes'], 
                $user->empresa_id, 
                $user->id
            );
            $msg = 'PDI individual criado com sucesso!';
        } else {
            $this->pdiService->criarPdiGrupo(
                $dados, 
                $dados['colaboradores'], 
                $dados['acoes'], 
                $user->empresa_id, 
                $user->id
            );
            $msg = 'PDIs criados em grupo com sucesso!';
        }

        return redirect()->route('pdis.index')->with('success', $msg);
    }

    /**
     * Exibe o detalhe do PDI.
     */
    public function show(Pdi $pdi)
    {
        // Carrega o colaborador antes da autorização para que a Policy possa
        // verificar se o usuário logado é o gestor direto do colaborador deste PDI.
        $pdi->loadMissing('colaborador');

        Gate::authorize('view', $pdi);

        $pdi->load(['colaborador', 'gestor', 'acoes', 'comentarios.user']);

        return view('pdis.show', compact('pdi'));
    }

    /**
     * Exibe o formulário de edição de um PDI individual.
     */
    public function edit(Pdi $pdi)
    {
        Gate::authorize('update', $pdi);

        if ($pdi->pdi_grupo_id !== null) {
            return redirect()->route('pdis.index')->with('error', 'Este PDI pertence a um Lote e não pode ser editado individualmente. Edite o Lote.');
        }

        $user = auth()->user();
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil === 'master' || $perfil === 'admin') {
            $colaboradores = User::where('empresa_id', $user->empresa_id)
                ->orderBy('name')
                ->get();
        } else {
            $colaboradores = User::where('empresa_id', $user->empresa_id)
                ->where(function ($q) use ($user) {
                    $q->where('departamento_id', $user->departamento_id)
                      ->orWhereNull('departamento_id');
                })
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();
        }

        $pdi->load('acoes');

        return view('pdis.edit', compact('pdi', 'colaboradores'));
    }

    /**
     * Atualiza um PDI individual.
     */
    public function update(StorePdiRequest $request, Pdi $pdi)
    {
        Gate::authorize('update', $pdi);

        if ($pdi->pdi_grupo_id !== null) {
            return redirect()->route('pdis.index')->with('error', 'Este PDI pertence a um Lote e não pode ser editado individualmente. Edite o Lote.');
        }

        $dados = $request->validated();
        $acoes = $dados['acoes'] ?? [];

        // Atualizar dados básicos
        $pdi->update([
            'colaborador_id' => $dados['colaborador_id'],
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'] ?? null,
            'categoria' => $dados['categoria'] ?? null,
            'data_inicio' => $dados['data_inicio'] ?? null,
            'data_fim' => $dados['data_fim'] ?? null,
        ]);

        // Sincronizar ações
        $acoesProcessadas = [];
        foreach ($acoes as $acaoSubmitted) {
            $acaoId = $acaoSubmitted['id'] ?? null;

            if ($acaoId) {
                // Atualizar ação existente
                $acaoExistente = $pdi->acoes()->find($acaoId);
                if ($acaoExistente) {
                    $acaoExistente->update([
                        'titulo' => $acaoSubmitted['titulo'],
                        'descricao' => $acaoSubmitted['descricao'] ?? null,
                    ]);
                    $acoesProcessadas[] = $acaoExistente->id;
                }
            } else {
                // Criar nova ação
                $nova = $pdi->acoes()->create([
                    'titulo' => $acaoSubmitted['titulo'],
                    'descricao' => $acaoSubmitted['descricao'] ?? null,
                    'status' => 'pendente'
                ]);
                $acoesProcessadas[] = $nova->id;
            }
        }

        // Remover ações que não vieram no form (Opção A: remover incondicionalmente)
        $pdi->acoes()->whereNotIn('id', $acoesProcessadas)->delete();

        // Recalcular progresso (via service)
        $this->pdiService->recalcularProgresso($pdi);

        return redirect()->route('pdis.index')->with('success', 'PDI atualizado com sucesso!');
    }

    /**
     * Exibe o formulário de edição do Lote de PDIs.
     */
    public function editGrupo(PdiGrupo $grupo)
    {
        Gate::authorize('update', $grupo);

        $user = auth()->user();
        $perfil = $user->perfil_acesso->value ?? $user->perfil_acesso;

        if ($perfil === 'master' || $perfil === 'admin') {
            $colaboradores = User::where('empresa_id', $user->empresa_id)
                ->orderBy('name')
                ->get();
        } else {
            $colaboradores = User::where('empresa_id', $user->empresa_id)
                ->where(function ($q) use ($user) {
                    $q->where('departamento_id', $user->departamento_id)
                      ->orWhereNull('departamento_id');
                })
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();
        }

        $colaboradoresSelecionados = $grupo->pdis()->pluck('colaborador_id')->toArray();
        $primeiroPdi = $grupo->pdis()->with('acoes')->first();
        $acoes = $primeiroPdi ? $primeiroPdi->acoes : collect();

        return view('pdis.edit-grupo', compact('grupo', 'colaboradores', 'colaboradoresSelecionados', 'acoes'));
    }

    /**
     * Atualiza o PDI Grupo e sincroniza as alterações com os PDIs individuais.
     */
    public function updateGrupo(StorePdiRequest $request, PdiGrupo $grupo)
    {
        Gate::authorize('update', $grupo);

        $dados = $request->validated();
        $colaboradoresIds = $dados['colaboradores'] ?? [];
        $acoes = $dados['acoes'] ?? [];

        $this->pdiService->atualizarPdiGrupo($grupo, $dados, $colaboradoresIds, $acoes);

        return redirect()->route('pdis.index')->with('success', 'Lote de PDIs atualizado com sucesso!');
    }



    /**
     * Toggle do status da ação do PDI via AJAX/Form.
     */
    public function toggleAcao(Request $request, PdiAcao $acao)
    {
        $pdi = $acao->pdi;
        Gate::authorize('toggleAcao', $pdi);

        // Verifica se o PDI já pode ser iniciado
        if ($pdi->data_inicio && $pdi->data_inicio->startOfDay()->gt(now()->startOfDay())) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'O PDI ainda não iniciou.',
                ], 403);
            }
            return back()->with('error', 'O PDI ainda não iniciou.');
        }

        // Verifica se o PDI passou do prazo
        if ($pdi->data_fim && $pdi->data_fim->endOfDay()->isPast()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'O prazo deste PDI já encerrou.',
                ], 403);
            }
            return back()->with('error', 'O prazo deste PDI já encerrou.');
        }

        $novoStatus = $request->input('status') === 'concluido' ? 'concluido' : 'pendente';
        $pdi = $this->pdiService->atualizarAcaoStatus($acao, $novoStatus);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'progresso' => $pdi->progresso,
                'status_pdi' => $pdi->status->value,
                'status_pdi_label' => ucfirst(str_replace('_', ' ', $pdi->status->value)),
                'data_conclusao' => $pdi->data_conclusao ? $pdi->data_conclusao->format('d/m/Y') : null,
                'acao_status' => $acao->status,
            ]);
        }

        return back()->with('success', 'Status da ação atualizado!');
    }

    /**
     * Adiciona um comentário ao PDI.
     */
    public function storeComentario(Request $request, Pdi $pdi)
    {
        $pdi->loadMissing('colaborador');

        Gate::authorize('comentar', $pdi);

        $request->validate([
            'comentario' => ['required', 'string'],
        ]);

        $this->pdiService->adicionarComentario(
            $pdi,
            $request->input('comentario'),
            auth()->id()
        );

        return back()->with('success', 'Comentário adicionado com sucesso!');
    }

    /**
     * Upload de comprovante de encerramento do PDI.
     */
    public function uploadComprovante(Request $request, Pdi $pdi)
    {
        Gate::authorize('toggleAcao', $pdi);

        // Verifica se o PDI passou do prazo
        if ($pdi->data_fim && $pdi->data_fim->endOfDay()->isPast()) {
            return back()->with('error', 'O prazo deste PDI já encerrou. Não é possível enviar comprovante.');
        }

        $request->validate([
            'comprovante' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,zip', 'max:5120'], // Máximo 5MB
        ]);

        $this->pdiService->salvarComprovante($pdi, $request->file('comprovante'));

        return back()->with('success', 'Comprovante enviado com sucesso!');
    }

    /**
     * Download do comprovante.
     */
    public function downloadComprovante(Pdi $pdi)
    {
        $pdi->loadMissing('colaborador');

        Gate::authorize('view', $pdi);

        if (!$pdi->comprovante || !Storage::disk('public')->exists($pdi->comprovante)) {
            abort(404, 'Comprovante não encontrado.');
        }

        return Storage::disk('public')->download($pdi->comprovante);
    }

    /**
     * Remove um PDI individual.
     */
    public function destroy(Pdi $pdi)
    {
        Gate::authorize('delete', $pdi);

        if ($pdi->comprovante) {
            Storage::disk('public')->delete($pdi->comprovante);
        }

        $pdi->delete();

        return redirect()->route('pdis.index')->with('success', 'PDI excluído com sucesso!');
    }

    /**
     * Remove um PdiGrupo (exclui o lote).
     */
    public function destroyGrupo(PdiGrupo $grupo)
    {
        Gate::authorize('delete', $grupo);

        DB::transaction(function () use ($grupo) {
            // Deleta arquivos de comprovantes dos PDIs associados
            foreach ($grupo->pdis as $pdi) {
                if ($pdi->comprovante) {
                    Storage::disk('public')->delete($pdi->comprovante);
                }
            }
            $grupo->delete(); // Cascade deletará os PDIs filhas se a constraint for configurada, mas faremos manual para garantir
        });

        return redirect()->route('pdis.index')->with('success', 'Grupo de PDIs excluído com sucesso!');
    }
}
