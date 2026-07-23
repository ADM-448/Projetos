<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\AvaliacaoResposta;
use App\Models\Departamento;
use App\Models\Cargo;
use App\Models\User;
use App\Models\Competencia;
use App\Models\AvaliacaoFechamento;
use App\Models\Notificacao; // Added Notificacao import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AvaliacaoWebController extends Controller
{
    // =========================================================================
    // PARTE 1: GESTÃO (Master/RH cria as provas)
    // =========================================================================

    public function index()
    {
        $user = auth()->user();
        if (in_array($user->perfil_acesso, ['admin', 'master'])) {
            $avaliacoes = Avaliacao::with('participantes')->orderBy('created_at', 'desc')->get();
        } else {
            $avaliacoes = Avaliacao::with('participantes')
                ->where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('participantes', function($q) use ($user) {
                              $q->where('avaliador_id', $user->id)->where('tipo', 'gestor');
                          });
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return view('avaliacoes.index', compact('avaliacoes'));
    }

    public function getDetalhes($id)
    {
        $avaliacao = Avaliacao::with('competencias.skills')->findOrFail($id);
        
        // NOTA: A auto-correção que convertia mediadores master/admin para gestor foi removida
        // pois um usuário master pode ser mediador legitimamente de uma avaliação.

        // Carregamento explícito dos participantes para garantir que o relacionamento venha preenchido
        $participantes = AvaliacaoParticipante::where('avaliacao_id', $id)
            ->with(['avaliado', 'avaliador'])
            ->get();
            
        // Anexa os participantes ao objeto avaliação
        $avaliacao->setRelation('participantes', $participantes);
        
        return response()->json($avaliacao);
    }

    public function create()
    {
        $user = auth()->user();
        
        if ($user->perfil_acesso === 'gestor') {
            $departamentos = Departamento::where('id', $user->departamento_id)->get();
            $cargos = Cargo::where('departamento_id', $user->departamento_id)->get();
            // Users filter: only from my department
            // Users filter: my department OR my direct subordinates
            $users = User::with(['cargo', 'departamento'])
                ->where(function($query) use ($user) {
                    $query->where('departamento_id', $user->departamento_id)
                          ->orWhere('gestor_id', $user->id);
                })
                ->get();
        } else {
            // Master/Admin/RH/Mediador vê tudo
            $departamentos = Departamento::all();
            $cargos = Cargo::all();
            $users = User::with(['cargo', 'departamento'])->get();
        }
        
        // Buscando especificamente gestores e masters para a lista de avaliadores
        $gestoresMasters = User::whereIn('perfil_acesso', ['master', 'gestor', 'admin'])->get();
        
        $mediadoresUsers = User::whereIn('perfil_acesso', ['master', 'mediador'])->get();
        $competencias = Competencia::all();

        return view('avaliacoes.create', compact('departamentos', 'cargos', 'users', 'gestoresMasters', 'mediadoresUsers', 'competencias'));
    }

    public function store(Request $request)
    {
        $request->validate(['titulo' => 'required', 'data_inicio' => 'required', 'data_fim' => 'required']);

        // Persiste filtros para recuperação na edição
        $filtros = [
            'depts' => $request->depts ?? [],
            'cargos' => $request->cargos ?? []
        ];

        $avaliacao = Avaliacao::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'forma_avaliacao' => $request->forma_avaliacao,
            'senioridade' => $request->senioridade,
            'data_admissao' => $request->data_admissao,
            'status' => 'rascunho',
            'created_by' => auth()->id(), // Track who created the evaluation
            'filtros' => $filtros // Salva JSON
        ]);

        if ($request->has('comps')) {
            $avaliacao->competencias()->sync($request->comps);
        }

        $this->processarParticipantes($avaliacao, $request);

        return redirect()->route('avaliacoes.index')->with('success', 'Avaliação criada!');
    }

    public function edit($id)
    {
        $avaliacao = Avaliacao::with(['competencias', 'participantes'])->findOrFail($id);
        
        $user = auth()->user();

        if ($user->perfil_acesso === 'gestor') {
            $departamentos = Departamento::where('id', $user->departamento_id)->get();
            $cargos = Cargo::where('departamento_id', $user->departamento_id)->get();
            $users = User::with(['cargo', 'departamento'])
                ->where(function($query) use ($user) {
                    $query->where('departamento_id', $user->departamento_id)
                          ->orWhere('gestor_id', $user->id);
                })
                ->get();
        } else {
            $departamentos = Departamento::all();
            $cargos = Cargo::all();
            $users = User::with(['cargo', 'departamento'])->get();
        }
        
        $gestoresMasters = User::whereIn('perfil_acesso', ['master', 'gestor', 'admin'])->get();
        $mediadoresUsers = User::whereIn('perfil_acesso', ['master', 'mediador'])->get();
        $competencias = Competencia::all();

        return view('avaliacoes.create', compact('avaliacao', 'departamentos', 'cargos', 'users', 'gestoresMasters', 'mediadoresUsers', 'competencias'));
    }

    public function update(Request $request, $id)
    {
        $avaliacao = Avaliacao::findOrFail($id);
        
        $filtros = [
            'depts' => $request->depts ?? [],
            'cargos' => $request->cargos ?? []
        ];

        $avaliacao->update($request->only(['titulo', 'descricao', 'data_inicio', 'data_fim', 'forma_avaliacao', 'senioridade', 'data_admissao']) + ['filtros' => $filtros]);

        if ($request->has('comps')) {
            $changes = $avaliacao->competencias()->sync($request->comps);
            $detachedIds = $changes['detached'] ?? [];
        } else {
            // Se não veio nada, tudo que estava antes será removido
            $detachedIds = $avaliacao->competencias()->pluck('competencias.id')->toArray();
            $avaliacao->competencias()->detach();
        }

        // --- CLEANUP: Remover respostas de competências desvinculadas ---
        if (!empty($detachedIds)) {
            // 1. Buscas Skills vinculadas a essas competências removidas
            $skillIds = \App\Models\Skill::whereIn('competencia_id', $detachedIds)->pluck('id');

            if ($skillIds->isNotEmpty()) {
                // 2. Busca Participantes desta avaliação
                $participanteIds = \App\Models\AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)->pluck('id');

                // 3. Apaga as respostas
                if ($participanteIds->isNotEmpty()) {
                    AvaliacaoResposta::whereIn('avaliacao_participante_id', $participanteIds)
                        ->whereIn('skill_id', $skillIds)
                        ->delete();
                }
            }
        }
        // ----------------------------------------------------------------

        $this->processarParticipantes($avaliacao, $request, true);

        return redirect()->route('avaliacoes.index')->with('success', 'Avaliação atualizada!');
    }

    public function destroy($id)
    {
        $avaliacao = Avaliacao::findOrFail($id);
        $avaliacao->delete();
        return redirect()->route('avaliacoes.index')->with('success', 'Avaliação excluída.');
    }

    // =========================================================================
    // PARTE 2: EXECUÇÃO (Onde o Master dá a nota)
    // =========================================================================

    public function pendentes()
    {
        $user = auth()->user();
        $userId = $user->id;
        $hoje = now()->format('Y-m-d');

        // Base query - "Minhas Avaliações" deve ser estritamente PESSOAL (Autoavaliação, Pares).
        // Tarefas de Gestão e Mediação foram para os menus "Gestão de Equipe".
        // Portanto, filtramos fora 'gestor' e 'mediador' para TODOS os usuários, inclusive Master.
        $query = AvaliacaoParticipante::where('avaliador_id', $userId)
            ->whereHas('avaliacao', function($q) use ($hoje){
                $q->whereDate('data_inicio', '<=', $hoje);
            })
            ->whereNotIn('tipo', ['gestor', 'mediador']); // Filtro universal para separar contexts
            
        $contexto = 'personal';

        $participacoes = $query->with(['avaliacao', 'avaliado.cargo', 'avaliado.departamento'])
            ->get()
            ->unique('avaliacao_id');

        return view('avaliacoes.minhas_avaliacoes', compact('participacoes', 'contexto'));
    }

    // NOVA FUNÇÃO: Gerenciar Avaliações (Visão do Gestor para sua Equipe)
    public function gerenciarAvaliacoes()
    {
        $userId = auth()->id();
        $hoje = now()->format('Y-m-d');

        // Busca avaliações onde o usuário logado é um avaliador (tipo='gestor')
        // Diferente de 'pendentes', aqui podemos focar nas avaliações que ele precisa gerenciar nota
        $participacoes = AvaliacaoParticipante::where('avaliador_id', $userId)
            ->where('tipo', 'gestor') // Foco em gestão
            ->whereHas('avaliacao', function($q) use ($hoje){
                $q->whereDate('data_inicio', '<=', $hoje);
            })
            ->with(['avaliacao', 'avaliado.cargo', 'avaliado.departamento'])
            ->get()
            ->unique('avaliacao_id'); // Reusa a lógica de lista única de avaliações, onde o modal carrega os alvos

        $contexto = 'manager';

        return view('avaliacoes.minhas_avaliacoes', compact('participacoes', 'contexto'));
    }

    // NOVA FUNÇÃO: Mediar Avaliações (Visão do Mediador)
    public function mediarAvaliacoes()
    {
        $userId = auth()->id();
        $hoje = now()->format('Y-m-d');

        // Busca avaliações onde o usuário é MEDIADOR
        $participacoes = AvaliacaoParticipante::where('avaliador_id', $userId)
            ->where('tipo', 'mediador') 
            ->whereHas('avaliacao', function($q) use ($hoje){
                $q->whereDate('data_inicio', '<=', $hoje);
            })
            ->with(['avaliacao', 'avaliado.cargo', 'avaliado.departamento'])
            ->get()
            ->unique('avaliacao_id');

        $contexto = 'mediator';

        return view('avaliacoes.minhas_avaliacoes', compact('participacoes', 'contexto'));
    }

    public function getMeusAlvos(Request $request, $avaliacaoId)
    {
        $userId = auth()->id();
        $avaliacao = Avaliacao::with('competencias.skills')->findOrFail($avaliacaoId);
        
        // Traz todos, inclusive os já avaliados (para poder editar)
        // E inclui relacionamentos necessários
        $query = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)
            ->where('avaliador_id', $userId)
            ->with('avaliado');

        // Filtra APENAS se o contexto for explicitamente definido
        $contexto = $request->input('context');
        
        if ($contexto === 'manager') {
             $query->where('tipo', 'gestor');
        } elseif ($contexto === 'mediator') {
             $query->where('tipo', 'mediador');
        } elseif ($contexto === 'personal') {
             // Se quisermos filtrar o pessoal tb, mas por precaução, se não vier nada, traz tudo
             $query->whereNotIn('tipo', ['gestor', 'mediador']);
        }

        $alvos = $query->get();

        return response()->json(['avaliacao' => $avaliacao, 'alvos' => $alvos]);
    }

    // Novo método para carregar respostas ao editar
    public function getRespostas($participanteId)
    {
        $participante = AvaliacaoParticipante::findOrFail($participanteId);
        $respostas = AvaliacaoResposta::where('avaliacao_participante_id', $participanteId)->get();
        return response()->json([
            'respostas' => $respostas,
            'observacao_avaliado' => $participante->observacao_avaliado,
        ]);
    }

    public function responder(Request $request)
    {
        $participacao = AvaliacaoParticipante::findOrFail($request->participante_id);

        if ($participacao->status === 'realizada') {
            if (!in_array(auth()->user()->perfil_acesso, ['admin', 'master'])) {
                return response()->json(['success' => false, 'message' => 'Avaliação já concluída. Solicite reabertura ao RH.'], 403);
            }
        }

        if ($request->has('notas')) {
            $comentarios = $request->input('comentarios', []);
            foreach ($request->notas as $skillId => $nota) {
                $comentario = $comentarios[$skillId] ?? null;
                
                if ($nota === null || $nota === '') {
                    if ($comentario === null || $comentario === '') {
                        continue;
                    }
                    $nota = 0.0;
                } else {
                    $nota = (float) str_replace(',', '.', $nota);
                }

                AvaliacaoResposta::updateOrCreate(
                    [
                        'avaliacao_participante_id' => $participacao->id,
                        'skill_id' => $skillId
                    ],
                    [
                        'nota' => $nota,
                        'comentario' => $comentario
                    ]
                );
            }
        }

        // Salva a observação geral do avaliado
        $updateData = ['status' => 'realizada'];
        if ($request->has('observacao_avaliado')) {
            $updateData['observacao_avaliado'] = $request->observacao_avaliado;
        }
        $participacao->update($updateData);
        
        // Retorna JSON para o AJAX
        return response()->json(['success' => true, 'message' => 'Avaliação salva com sucesso!']);
    }

    // =========================================================================
    // PARTE 3: RELATÓRIOS INDIVIDUAIS
    // =========================================================================

    public function relatoriosIndividuais()
    {
        $avaliacoes = Avaliacao::orderBy('created_at', 'desc')->get();
        return view('avaliacoes.relatorios.individuais', compact('avaliacoes'));
    }

    public function meusRelatorios()
    {
        $userId = auth()->id();
        
        // Buscamos todas as avaliações onde o usuário foi o ALVO (avaliado)
        $avaliacoes = Avaliacao::whereHas('participantes', function($q) use ($userId) {
            $q->where('avaliado_id', $userId);
        })->orderBy('created_at', 'desc')->get();

        return view('avaliacoes.relatorios.meus_relatorios', compact('avaliacoes'));
    }

    // NOVA FUNÇÃO: Relatórios de Mediação (Lista avaliações onde sou mediador)
    public function relatoriosMediacao()
    {
        $userId = auth()->id();
        
        // Buscamos todas as avaliações onde o usuário é MEDIADOR
        $avaliacoes = Avaliacao::whereHas('participantes', function($q) use ($userId) {
            $q->where('avaliador_id', $userId)
              ->where('tipo', 'mediador');
        })->orderBy('created_at', 'desc')->get();

        // Reutilizamos a view de seleção de relatórios individuais, pois a estrutura é a mesma (Lista Avaliações -> Seleciona Colaborador)
        return view('avaliacoes.relatorios.individuais', compact('avaliacoes'));
    }

    public function getDadosRelatorio(Request $request)
    {
        $avaliacaoId = $request->avaliacao_id;
        $colaboradorId = $request->colaborador_id;
        
        if(!$avaliacaoId || !$colaboradorId) return response()->json(['error' => 'Parâmetros inválidos'], 400);

        // 1. Dados do Colaborador
        $colaborador = User::with(['departamento', 'cargo', 'gestor'])->findOrFail($colaboradorId);
        $avaliacao = Avaliacao::findOrFail($avaliacaoId);

        // 2. Participações deste colaborador nesta avaliação (como ALVO)
        // Isso inclui Autoavaliação e Avaliações de Mediadores
        $participacoes = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)
            ->where('avaliado_id', $colaboradorId)
            ->with(['avaliador', 'respostas.skill.competencia']) // Carrega quem avaliou e o quê
            ->get();

        // 3. Fechamento (Se houver)
        $fechamento = AvaliacaoFechamento::where('avaliacao_id', $avaliacaoId)
            ->where('avaliado_id', $colaborador->id)
            ->first();

        // === CÁLCULOS ===

        // A. Média Individual (Geral recebida)
        // Pega todas as respostas recebidas por ele (excluindo auto? ou incluindo? Geralmente inclui tudo ou só externos. Vamos incluir TUDO por enquanto para simplificar "Individual Score")
        // Mas o pedido pede "Média Colaboradores" (que eu assumo ser self/peers) e "Avaliares".
        // Vamos calcular a média aritmética simples de TODAS as notas recebidas.
        
        $todasRespostas = $participacoes->pluck('respostas')->flatten();
        $mediaIndividual = $todasRespostas->avg('nota') ?? 0;

        // B. Médias de Comparação (Cargo, Setor, Empresa) na mesma avaliação
        // Preciso das médias de OUTRAS pessoas. É pesado calcular on-the-fly.
        // O ideal seria cache, mas vamos fazer query crua.
        
        // Helper interno para média geral de um grupo de users
        $calcularMediaDeUsers = function($userIds) use ($avaliacaoId) {
             return DB::table('avaliacao_respostas')
                ->join('avaliacao_participantes', 'avaliacao_respostas.avaliacao_participante_id', '=', 'avaliacao_participantes.id')
                ->where('avaliacao_participantes.avaliacao_id', $avaliacaoId)
                ->whereIn('avaliacao_participantes.avaliado_id', $userIds)
                ->avg('avaliacao_respostas.nota') ?? 0;
        };

        // Média Cargo
        $usersSameCargo = User::where('cargo_id', $colaborador->cargo_id)->pluck('id');
        $mediaCargo = $calcularMediaDeUsers($usersSameCargo);

        // Média Setor
        $usersSameDept = User::where('departamento_id', $colaborador->departamento_id)->pluck('id');
        $mediaSetor = $calcularMediaDeUsers($usersSameDept);

        // Média Empresa
        $mediaEmpresa = DB::table('avaliacao_respostas')
            ->join('avaliacao_participantes', 'avaliacao_respostas.avaliacao_participante_id', '=', 'avaliacao_participantes.id')
            ->where('avaliacao_participantes.avaliacao_id', $avaliacaoId)
            ->avg('avaliacao_respostas.nota') ?? 0;


        // C. Detalhamento (Colaboradores vs Avaliadores vs Mediação)
        
        // Grupo 1: "Colaboradores" -> Vamos assumir AUTOAVALIAÇÃO
        $partAuto = $participacoes->where('tipo', 'autoavaliacao')->first();
        $mediaColab = $partAuto ? $partAuto->respostas->avg('nota') : 0;
        $detalhesColab = $partAuto ? $partAuto->respostas->map(function($r){
            return [
                'competencia' => $r->skill->competencia->nome ?? 'Geral',
                'pergunta' => $r->skill->nome, 
                'nota' => $r->nota
            ];
        }) : [];

        // Grupo 2: "Avaliadores" -> Gestores/Pares/Subordinados (Excluindo Mediador e Auto)
        $partsAval = $participacoes->whereIn('tipo', ['gestor', 'par', 'subordinado']); // Array de participações
        $respostasAval = $partsAval->pluck('respostas')->flatten();
        $mediaAval = $respostasAval->avg('nota') ?? 0;
        
        // Detalhes Avaliadores: Agrupar por Skill (Média por pergunta) + Comentários
        // Precisamos agrupar respostas pelo ID da skill
        $detalhesAval = [];
        $respostasPorSkill = $respostasAval->groupBy('skill_id');
        
        foreach($respostasPorSkill as $skillId => $resps) {
            $nomeSkill = $resps->first()->skill->nome ?? 'Pergunta';
            $avgSkill = $resps->avg('nota');
            // Coletar comentários não nulos
            $coments = $partsAval->pluck('respostas')->flatten()->where('skill_id', $skillId)->pluck('comentario')->filter()->values();
            
            $detalhesAval[] = [
                'competencia' => $resps->first()->skill->competencia->nome ?? 'Geral',
                'pergunta' => $nomeSkill,
                'nota' => number_format($avgSkill, 2),
                'comentarios' => $coments
            ];
        }

        // Grupo 3: "Mediação" -> Do fechamento manual OU Média Ponderada
        // Se houver fechamento, usa a nota do fechamento.
        // Se não, usa a média das notas de Gestores e Mediadores (excluindo Pares/Auto) como prévia.
        if ($fechamento && isset($fechamento->nota_final)) {
            $mediaMediacao = $fechamento->nota_final;
        } else {
            // Fallback: Calcula média apenas dos Mediadores
            $partsDecisores = $participacoes->whereIn('tipo', ['mediador']);
            $respostasDecisores = $partsDecisores->pluck('respostas')->flatten();
            $mediaMediacao = $respostasDecisores->avg('nota') ?? 0;
        }
        // Detalhes? Se salvarmos por competência no futuro. Por enquanto retorna vazio ou nota geral.
        
        // D. Minha Nota de Mediação (Se eu for o mediador visualizando)
        $minhaPart = $participacoes->where('avaliador_id', auth()->id())->where('tipo', 'mediador')->first();
        $minhaNotaMediacao = $minhaPart ? ($minhaPart->respostas->avg('nota') ?? 0) : null;

        return response()->json([
            'colaborador' => $colaborador,
            'charts' => [
                'individual' => round($mediaIndividual, 2),
                'cargo' => round($mediaCargo, 2),
                'setor' => round($mediaSetor, 2),
                'empresa' => round($mediaEmpresa, 2),
            ],
            'scores' => [
                'colab' => [ 'media' => number_format($mediaColab, 2), 'detalhes' => $detalhesColab ],
                'aval'  => [ 'media' => number_format($mediaAval, 2), 'detalhes' => $detalhesAval ],
                'med'   => [ 'media' => number_format($mediaMediacao, 2), 'id_fechamento' => $fechamento?->id, 'comentarios' => $fechamento?->comentarios ],
                'minha_mediacao' => $minhaNotaMediacao !== null ? number_format($minhaNotaMediacao, 2) : null,
                'observacao_avaliado' => $partAuto?->observacao_avaliado,
            ]
        ]);
    }
    
    // Lista de Alvos para o dropdown de filtro (quem participou daquela avaliação)
    public function getAlvosDaAvaliacao($avaliacaoId) {
        // Retorna users que foram avaliados nesta avaliação (distinct)
        $currentUser = auth()->user();
        $ids = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)->pluck('avaliado_id')->unique();
        
        $query = User::whereIn('id', $ids);

        if ($currentUser->perfil_acesso === 'gestor') {
            $query->where(function($q) use ($currentUser) {
                $q->where('departamento_id', $currentUser->departamento_id)
                  ->orWhere('gestor_id', $currentUser->id);
            });
            $query->where('id', '!=', $currentUser->id);
        }

        $users = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($users);
    }
    
    // Rota auxiliar para salvar o fechamento
    public function salvarFechamento(Request $request) {
        $data = $request->validate([
            'avaliacao_id' => 'required',
            'avaliado_id' => 'required',
            'nota_final' => 'nullable|numeric',
            'comentarios' => 'nullable'
        ]);
        
        $fechamento = AvaliacaoFechamento::firstOrNew(['avaliacao_id' => $data['avaliacao_id'], 'avaliado_id' => $data['avaliado_id']]);
        
        $fechamento->comentarios = $data['comentarios'];
        $fechamento->responsavel_id = auth()->id();
        
        if(isset($data['nota_final'])) {
            $fechamento->nota_final = $data['nota_final'];
        }
        
        $fechamento->save();
        
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // CÉREBRO DA DISTRIBUIÇÃO (MASTER INCLUDED)
    // =========================================================================

    private function processarParticipantes($avaliacao, $request, $isUpdate = false)
    {
        // 1. Quem será avaliado? (Colaboradores)
        $targetIds = collect();
        if ($request->has('avaliados')) $targetIds = collect($request->avaliados);
        
        // Se houver filtros (Dept, Cargo ou Senioridade)
        if ($request->has('depts') || $request->has('cargos') || $request->filled('senioridade')) {
            $query = User::query(); 
            
            if ($request->has('depts') || $request->has('cargos')) {
                $query->where(function($q) use ($request) {
                    if ($request->has('depts')) {
                        $q->orWhereIn('departamento_id', $request->depts);
                    }
                    if ($request->has('cargos')) {
                        $q->orWhereIn('cargo_id', $request->cargos);
                    }
                });
            }

             if ($request->filled('senioridade')) {
                $termo = $request->senioridade; 
                $query->whereHas('cargo', function($q) use ($termo) {
                    $q->where('nome', 'LIKE', "%{$termo}%");
                });
            }
            
            $targetIds = $targetIds->merge($query->pluck('id'))->unique();
        }

        // Limpeza (Edição)
        if ($isUpdate) {
            // Remove participantes que NÃO estão na nova lista de alvos
            if ($targetIds->isEmpty()) {
                AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                    ->whereNotIn('tipo', ['gestor', 'mediador']) // Mantém os avaliadores até o próximo passo
                    ->where('status', 'pendente')
                    ->delete();
            } else {
                AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                    ->whereNotIn('avaliado_id', $targetIds->toArray())
                    ->where('status', 'pendente')
                    ->delete();
            }
        }

        // 2. AVALIADORES (GESTORES)
        // Agora recebemos 'gestores' do formulário
        $gestoresIds = collect($request->gestores ?? []);
        $mediadoresIds = collect($request->mediadores ?? []);

        // --- SYNC / LIMPEZA DE DESMARCADOS (Edição) ---
        if ($isUpdate) {
            // Remove Gestores que não estão mais na lista enviada
            $queryGestores = AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                ->where('tipo', 'gestor')
                ->where('status', 'pendente');
                
            if ($gestoresIds->isEmpty()) {
                $queryGestores->delete();
            } else {
                $queryGestores->whereNotIn('avaliador_id', $gestoresIds)->delete();
            }

            // Remove Mediadores que não estão mais na lista enviada
            $queryMediadores = AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                ->where('tipo', 'mediador')
                ->where('status', 'pendente');
                
            if ($mediadoresIds->isEmpty()) {
                $queryMediadores->delete();
            } else {
                $queryMediadores->whereNotIn('avaliador_id', $mediadoresIds)->delete();
            }
        }
        // ------------------------------------------------

        // 3. Criar os Vínculos no Banco
        foreach ($targetIds as $userId) {
            
            // A. Autoavaliação
            if ($request->forma_avaliacao != '90') {
                $existsAuto = AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                    ->where('avaliado_id', $userId)
                    ->where('tipo', 'autoavaliacao')->exists();

                if (!$existsAuto) {
                    AvaliacaoParticipante::create([
                        'avaliacao_id' => $avaliacao->id,
                        'avaliado_id' => $userId,
                        'avaliador_id' => $userId,
                        'tipo' => 'autoavaliacao',
                        'status' => 'pendente'
                    ]);
                    
                    if ($userId != auth()->id()) {
                        Notificacao::create([
                            'empresa_id' => auth()->user()->empresa_id ?? 1,
                            'destinatario_id' => $userId,
                            'remetente_id' => auth()->id(),
                            'tipo' => 'avaliacao',
                            'mensagem' => 'Nova Auto-Avaliação Disponível: ' . $avaliacao->titulo,
                            'link' => route('avaliacoes.pendentes')
                        ]);
                    }
                }
            }

            // B. Gestores (Avaliadores)
            foreach ($gestoresIds as $gestorId) {
                if ($gestorId == $userId) continue; 

                $exists = AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                    ->where('avaliado_id', $userId)
                    ->where('avaliador_id', $gestorId)
                    ->where('tipo', 'gestor')
                    ->exists();

                if (!$exists) {
                    AvaliacaoParticipante::create([
                        'avaliacao_id' => $avaliacao->id,
                        'avaliado_id' => $userId,
                        'avaliador_id' => $gestorId,
                        'tipo' => 'gestor', // Agora salva corretamente como GESTOR
                        'status' => 'pendente'
                    ]);
                    
                    // Notificar Gestor
                    if ($gestorId != auth()->id()) {
                        Notificacao::create([
                            'empresa_id' => auth()->user()->empresa_id ?? 1,
                            'destinatario_id' => $gestorId,
                            'remetente_id' => auth()->id(),
                            'tipo' => 'avaliacao',
                            'mensagem' => 'Você foi selecionado como Avaliador: ' . $avaliacao->titulo,
                            'link' => route('avaliacoes.pendentes')
                        ]);
                    }
                }
            }

            // C. Mediadores (RH/Externo)
            foreach ($mediadoresIds as $mediadorId) {
                // Removido 'if ($mediadorId == $userId) continue;' para permitir que o mediador medie a própria avaliação

                $exists = AvaliacaoParticipante::where('avaliacao_id', $avaliacao->id)
                    ->where('avaliado_id', $userId)
                    ->where('avaliador_id', $mediadorId)
                    ->where('tipo', 'mediador')
                    ->exists();

                if (!$exists) {
                    AvaliacaoParticipante::create([
                        'avaliacao_id' => $avaliacao->id,
                        'avaliado_id' => $userId,
                        'avaliador_id' => $mediadorId,
                        'tipo' => 'mediador', // Salva como MEDIADOR
                        'status' => 'pendente'
                    ]);
                    
                    // Notificar Mediador
                     if ($mediadorId != auth()->id()) {
                        Notificacao::create([
                            'empresa_id' => auth()->user()->empresa_id ?? 1,
                            'destinatario_id' => $mediadorId,
                            'remetente_id' => auth()->id(),
                            'tipo' => 'avaliacao',
                            'mensagem' => 'Você foi selecionado como Mediador: ' . $avaliacao->titulo,
                            'link' => route('avaliacoes.mediar')
                        ]);
                    }
                }
            }
        }
    }
    public function reabrirAvaliacao(Request $request)
    {
        AvaliacaoParticipante::where('avaliacao_id', $request->avaliacao_id)
            ->where('avaliador_id', auth()->id())
            ->update(['status' => 'pendente']);

        return response()->json(['success' => true]);
    }

    public function exportarPdf(Request $request)
    {
        if (!in_array(auth()->user()->perfil_acesso, ['master', 'gestor', 'rh'])) {
            abort(403, 'Acesso não autorizado');
        }

        // Reutiliza a lógica de buscar os dados para o relatório AJAX
        $response = $this->getDadosRelatorio($request);
        
        if ($response->getStatusCode() !== 200) {
            return back()->with('error', 'Não foi possível carregar os dados para exportação.');
        }

        $data = json_decode($response->getContent(), true);

        if (isset($data['error'])) {
            return back()->with('error', $data['error']);
        }

        $avaliacao = Avaliacao::findOrFail($request->avaliacao_id);
        $colaborador = User::with(['departamento', 'cargo', 'gestor'])->findOrFail($request->colaborador_id);

        $pdf = Pdf::loadView('avaliacoes.relatorios.pdf', [
            'avaliacao' => $avaliacao,
            'colaborador' => $colaborador,
            'charts' => $data['charts'],
            'scores' => $data['scores']
        ]);

        $filename = 'Relatorio_Avaliacao_' . str_replace(' ', '_', $colaborador->name) . '.pdf';
        
        return $pdf->download($filename);
    }
}