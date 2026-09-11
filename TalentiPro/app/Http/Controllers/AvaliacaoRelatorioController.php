<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\AvaliacaoParticipante;
use App\Models\AvaliacaoFechamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pdi;
use App\Models\PdiAcao;
use App\Services\GeminiTextService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AvaliacaoRelatorioController extends Controller
{
    // =========================================================================
    // PARTE 1: VISUALIZAÇÃO PARA GESTORES, MASTER, ADMIN (Relatórios Individuais)
    // =========================================================================

    public function index()
    {
        $user = auth()->user();
        
        // Regra do index: Admin e Master veem tudo. 
        // Gestor vê as avaliações que ele criou ou onde ele é o gestor responsável.
        // Mediador vê onde ele é o mediador.
        // A lógica do sistema antigo trazia TODAS as avaliações para o dropdown, vamos manter.
        if (in_array($user->perfil_acesso->value, ['admin', 'master'])) {
            $avaliacoes = Avaliacao::orderBy('created_at', 'desc')->get();
        } else {
            // Se for gestor, filtra as que importam para ele
            $avaliacoes = Avaliacao::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('participantes', function($q) use ($user) {
                          $q->where('avaliador_id', $user->id)->whereIn('tipo', ['gestor', 'mediador']);
                      });
            })->orderBy('created_at', 'desc')->get();
        }

        return view('avaliacoes.relatorios.index', compact('avaliacoes'));
    }

    // =========================================================================
    // PARTE 2: VISÃO DO COLABORADOR AVALIADO (Meus Relatórios)
    // =========================================================================

    public function meusRelatorios()
    {
        $userId = auth()->id();
        
        // Buscamos todas as avaliações onde o usuário foi o ALVO (avaliado)
        $avaliacoes = Avaliacao::whereHas('participantes', function($q) use ($userId) {
            $q->where('avaliado_id', $userId);
        })->orderBy('created_at', 'desc')->get();

        return view('avaliacoes.relatorios.meus_relatorios', compact('avaliacoes'));
    }

    // Retorna a lista de usuários alvos para popular o dropdown de "Colaborador" após selecionar a Campanha.
    public function getAlvosDaAvaliacao($avaliacaoId) 
    {
        $currentUser = auth()->user();
        $ids = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)->pluck('avaliado_id')->unique();
        
        $query = User::whereIn('id', $ids);

        if ($currentUser->perfil_acesso->value === 'gestor') {
            $query->where(function($q) use ($currentUser) {
                $q->where('departamento_id', $currentUser->departamento_id)
                  ->orWhere('gestor_id', $currentUser->id);
            });
            // Oculta o próprio gestor da lista se ele estiver avaliando subordinados
            $query->where('id', '!=', $currentUser->id);
        }

        $users = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($users);
    }

    // =========================================================================
    // PARTE 3: ENDPOINT DE DADOS JSON PARA OS GRÁFICOS (API AJAX)
    // =========================================================================

    public function getDadosRelatorio(Request $request)
    {
        $avaliacaoId = $request->avaliacao_id;
        $colaboradorId = $request->colaborador_id;
        
        if(!$avaliacaoId || !$colaboradorId) return response()->json(['error' => 'Parâmetros inválidos'], 400);

        // 1. Dados do Colaborador
        $colaborador = User::with(['departamento', 'cargo', 'gestor'])->findOrFail($colaboradorId);
        $avaliacao = Avaliacao::findOrFail($avaliacaoId);

        // 2. Participações deste colaborador nesta avaliação (como ALVO)
        $participacoes = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)
            ->where('avaliado_id', $colaboradorId)
            ->with(['avaliador', 'respostas.skill.competencia']) 
            ->get();

        // 3. Fechamento (Mediação manual, se houver)
        $fechamento = AvaliacaoFechamento::where('avaliacao_id', $avaliacaoId)
            ->where('avaliado_id', $colaborador->id)
            ->first();

        // === CÁLCULOS ===

        // A. Média Individual (Geral recebida por todos)
        $todasRespostas = $participacoes->pluck('respostas')->flatten();
        $mediaIndividual = $todasRespostas->avg('nota') ?? 0;

        // B. Médias de Comparação (Cargo, Setor, Empresa)
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

        // C. Detalhamento
        
        // Grupo 1: "Colaboradores" -> AUTOAVALIAÇÃO
        $partAuto = $participacoes->where('tipo', \App\Enums\TipoAvaliacao::AUTOAVALIACAO)->first();
        $mediaColab = $partAuto ? $partAuto->respostas->avg('nota') : 0;
        $detalhesColab = $partAuto ? $partAuto->respostas->map(function($r){
            return [
                'competencia' => $r->skill->competencia->nome ?? 'Geral',
                'pergunta' => $r->skill->nome, 
                'nota' => $r->nota
            ];
        }) : [];

        // Grupo 2: "Avaliadores" -> Gestores/Pares/Subordinados (Excluindo Mediador e Auto)
        $partsAval = $participacoes->whereIn('tipo', [
            \App\Enums\TipoAvaliacao::GESTOR,
            \App\Enums\TipoAvaliacao::PARES,
            \App\Enums\TipoAvaliacao::SUBORDINADO
        ]); 
        $respostasAval = $partsAval->pluck('respostas')->flatten();
        $mediaAval = $respostasAval->avg('nota') ?? 0;
        $isOwnReport = (auth()->id() == $colaboradorId);

        $detalhesAval = [];
        $respostasPorSkill = $respostasAval->groupBy('skill_id');
        
        foreach($respostasPorSkill as $skillId => $resps) {
            $nomeSkill = $resps->first()->skill->nome ?? 'Pergunta';
            $avgSkill = $resps->avg('nota');
            
            $breakdown = [];
            if (!$isOwnReport) {
                foreach($resps as $resp) {
                    $breakdown[] = [
                        'nome' => $resp->participante->avaliador->name ?? 'Avaliador',
                        'nota' => $resp->nota
                    ];
                }
            }
            
            $detalhesAval[] = [
                'competencia' => $resps->first()->skill->competencia->nome ?? 'Geral',
                'pergunta' => $nomeSkill,
                'nota' => number_format($avgSkill, 2),
                'breakdown' => $breakdown
            ];
        }

        // Breakdown por Avaliador (para RH)
        $avaliadoresBreakdown = [];
        
        if (!$isOwnReport) {
            foreach ($partsAval as $part) {
                $avgAvaliador = $part->respostas->avg('nota');
                if ($avgAvaliador !== null) {
                    $avaliadoresBreakdown[] = [
                        'nome' => $part->avaliador->name ?? 'Avaliador',
                        'media' => number_format($avgAvaliador, 2)
                    ];
                }
            }
        }

        // Grupo 3: "Mediação / Final"
        $mediaMediacao = null;
        if ($fechamento && $fechamento->nota_final !== null) {
            $mediaMediacao = $fechamento->nota_final;
        } else {
            // Média Apenas dos Mediadores
            $partsDecisores = $participacoes->where('tipo', \App\Enums\TipoAvaliacao::MEDIADOR);
            if ($partsDecisores->count() > 0) {
                $respostasDecisores = $partsDecisores->pluck('respostas')->flatten();
                $mediaMediacao = $respostasDecisores->count() > 0 ? $respostasDecisores->avg('nota') : null;
            }
        }
        
        $minhaPart = $participacoes->where('avaliador_id', auth()->id())->where('tipo', \App\Enums\TipoAvaliacao::MEDIADOR)->first();
        $minhaNotaMediacao = $minhaPart ? ($minhaPart->respostas->avg('nota') ?? 0) : null;

        return response()->json([
            'colaborador' => $colaborador,
            'avaliacao' => $avaliacao,
            'charts' => [
                'individual' => round($mediaIndividual, 2),
                'cargo' => round($mediaCargo, 2),
                'setor' => round($mediaSetor, 2),
                'empresa' => round($mediaEmpresa, 2),
            ],
            'is_own_report' => $isOwnReport,
            'avaliadores_breakdown' => $avaliadoresBreakdown,
            'scores' => [
                'colab' => [ 'media' => number_format($mediaColab, 2), 'detalhes' => $detalhesColab ],
                'aval'  => [ 'media' => number_format($mediaAval, 2), 'detalhes' => $detalhesAval ],
                'med'   => [ 'media' => $mediaMediacao !== null ? number_format((float)$mediaMediacao, 2) : null, 'id_fechamento' => $fechamento?->id, 'comentarios' => $fechamento?->comentarios ],
                'minha_mediacao' => $minhaNotaMediacao !== null ? number_format($minhaNotaMediacao, 2) : null,
                'observacao_avaliado' => $partAuto?->observacao_avaliado,
            ]
        ]);
    }

    // =========================================================================
    // PARTE 4: EXPORTAÇÃO PDF
    // =========================================================================

    public function exportarPdf(Request $request)
    {
        $avaliacaoId = $request->avaliacao_id;
        $colaboradorId = $request->colaborador_id;

        if(!$avaliacaoId || !$colaboradorId) abort(400, 'Parâmetros inválidos');

        // Chama internamente a função que mastiga os dados
        $requestData = new Request(['avaliacao_id' => $avaliacaoId, 'colaborador_id' => $colaboradorId]);
        $dados = $this->getDadosRelatorio($requestData)->getData(true); // get as array

        if(isset($dados['error'])) abort(400, $dados['error']);

        $pdf = Pdf::loadView('avaliacoes.relatorios.pdf', ['data' => $dados]);
        
        // DomPDF options for better rendering
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('Relatorio_Avaliacao_' . $dados['colaborador']['name'] . '.pdf');
    }

    // =========================================================================
    // PARTE 5: GERAÇÃO DE PDI VIA INTELIGÊNCIA ARTIFICIAL
    // =========================================================================

    public function gerarPdiIa(Request $request)
    {
        $request->validate([
            'avaliacao_id' => 'required|integer',
            'colaborador_id' => 'required|integer',
            'colaborador_nome' => 'required|string',
            'competencias' => 'required|array'
        ]);

        $colabId = $request->colaborador_id;
        $avId = $request->avaliacao_id;
        $user = auth()->user();

        // 1. Evitar duplicidade: Verifica se já existe um PDI gerado por IA para esta avaliação
        // Como não há um campo especifico de 'avaliacao_id' no PDI, usaremos o título ou algo que referencie
        // Para simplificar e permitir que o gestor possa gerar mais de um (caso tenha excluído), 
        // prosseguiremos com a criação e o Master gerencia isso.

        // 2. Compilar o prompt focado APENAS nas notas <= 3.0
        $dadosCompilados = "";
        $hasLowScores = false;

        foreach ($request->competencias as $compName => $dados) {
            foreach ($dados['skills'] as $skillName => $skillData) {
                $nota = isset($skillData['aval']['nota']) ? floatval($skillData['aval']['nota']) : null;
                
                if ($nota !== null && $nota <= 3.0) {
                    $hasLowScores = true;
                    $dadosCompilados .= "Competência Geral: {$compName}\n";
                    $dadosCompilados .= "- Pergunta/Critério: {$skillName}\n";
                    $dadosCompilados .= "- Nota dos Avaliadores: {$nota}\n";
                    
                    if (isset($skillData['aval']['comentarios']) && is_array($skillData['aval']['comentarios']) && count($skillData['aval']['comentarios']) > 0) {
                        $dadosCompilados .= "- Comentários/Feedbacks recebidos:\n";
                        foreach ($skillData['aval']['comentarios'] as $c) {
                            $dadosCompilados .= "  > \"{$c}\"\n";
                        }
                    }
                    $dadosCompilados .= "\n";
                }
            }
        }

        if (!$hasLowScores) {
            return response()->json(['success' => false, 'error' => 'Nenhuma competência com nota 3.0 ou inferior foi encontrada para gerar o PDI.']);
        }

        // 3. Despachar para a fila (Job)
        \App\Jobs\GerarPdiAvaliacaoJob::dispatch(
            $dadosCompilados, 
            $request->colaborador_nome, 
            $colabId, 
            $user->id, 
            $user->empresa_id
        );

        return response()->json([
            'success' => true,
            'queued' => true,
            'message' => 'O PDI está sendo gerado em segundo plano. Você será notificado quando estiver pronto!'
        ]);
    }
}
