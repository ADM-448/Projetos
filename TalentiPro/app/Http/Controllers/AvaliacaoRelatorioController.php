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
        
        // Regra do index: Admin, Master e Consultor veem todas as avaliações (da empresa ativa ou todas se admin). 
        // Gestor vê as avaliações que ele criou ou onde ele é o gestor responsável.
        // Mediador vê onde ele é o mediador.
        if (in_array($user->perfil_acesso->value, ['admin', 'master', 'consultor'])) {
            $avaliacoes = Avaliacao::where('is_template', false)->orderBy('created_at', 'desc')->get();
        } else {
            // Se for gestor, filtra as que importam para ele
            $avaliacoes = Avaliacao::where('is_template', false)
                ->where(function($query) use ($user) {
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
        
        // Colaborador tem acesso apenas à ÚLTIMA versão da avaliação em que foi avaliado
        $avaliacoes = Avaliacao::where('is_template', false)
            ->whereHas('participantes', function($q) use ($userId) {
                $q->where('avaliado_id', $userId);
            })->orderBy('created_at', 'desc')->take(1)->get();

        return view('avaliacoes.relatorios.meus_relatorios', compact('avaliacoes'));
    }

    public function getAlvosDaAvaliacao($avaliacaoId) 
    {
        $currentUser = auth()->user();
        $avaliacao = Avaliacao::findOrFail($avaliacaoId);
        $ids = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)->pluck('avaliado_id')->unique();
        
        $query = User::whereIn('id', $ids);

        if ($currentUser->perfil_acesso->value === 'gestor') {
            // Se o gestor foi quem criou a campanha, ele pode ver o relatório de todos os alvos dela
            if ($avaliacao->created_by !== $currentUser->id) {
                // Se não criou, ele vê seu departamento, seus subordinados ou pessoas que ele avaliou diretamente
                $idsAvaliadosPeloGestor = AvaliacaoParticipante::where('avaliacao_id', $avaliacaoId)
                    ->where('avaliador_id', $currentUser->id)
                    ->pluck('avaliado_id')->toArray();

                $query->where(function($q) use ($currentUser, $idsAvaliadosPeloGestor) {
                    $q->where('departamento_id', $currentUser->departamento_id)
                      ->orWhere('gestor_id', $currentUser->id)
                      ->orWhereIn('id', $idsAvaliadosPeloGestor);
                });
            }
            
            // Oculta o próprio gestor da lista (pois ele vê sua autoavaliação em "Meus Relatórios")
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
        $avaliacao->load('competencias');
        $pesosCompetenciasMap = $avaliacao->competencias->pluck('pivot.peso', 'nome')->toArray();
        $pesosPorIdMap = $avaliacao->competencias->pluck('pivot.peso', 'id')->toArray();

        // Função para calcular média ponderada por competência
        $calcularMediaPonderadaRespostas = function($respostasColecao) use ($pesosPorIdMap) {
            if ($respostasColecao->isEmpty()) return 0;

            $porCompetencia = $respostasColecao->groupBy(function($r) {
                return $r->skill?->competencia_id ?? 0;
            });

            $somaPonderada = 0;
            $somaPesos = 0;

            foreach ($porCompetencia as $compId => $respsComp) {
                $mediaComp = $respsComp->avg('nota') ?? 0;
                $peso = $pesosPorIdMap[$compId] ?? 1;

                $somaPonderada += ($mediaComp * $peso);
                $somaPesos += $peso;
            }

            return $somaPesos > 0 ? ($somaPonderada / $somaPesos) : 0;
        };

        // A. Média Individual (Ponderada pelas competências)
        $todasRespostas = $participacoes->pluck('respostas')->flatten();
        $mediaIndividual = $calcularMediaPonderadaRespostas($todasRespostas);

        // B. Médias de Comparação (Cargo, Setor, Empresa)
        $empresaId = $avaliacao->empresa_id ?? $colaborador->empresa_id;

        // Localiza todas as avaliações da mesma empresa baseadas no mesmo modelo/template
        $campanhasQuery = Avaliacao::where('empresa_id', $empresaId);

        if ($avaliacao->template_id) {
            $campanhasQuery->where(function($q) use ($avaliacao) {
                $q->where('template_id', $avaliacao->template_id)
                  ->orWhere('id', $avaliacao->template_id)
                  ->orWhere('id', $avaliacao->id);
            });
        } elseif ($avaliacao->is_template) {
            $campanhasQuery->where(function($q) use ($avaliacao) {
                $q->where('template_id', $avaliacao->id)
                  ->orWhere('id', $avaliacao->id);
            });
        } else {
            $campanhasQuery->where(function($q) use ($avaliacao) {
                $q->where('id', $avaliacao->id)
                  ->orWhere('titulo', $avaliacao->titulo);
            });
        }

        $campanhasMesmoModelo = $campanhasQuery->pluck('id')->toArray();
        if (empty($campanhasMesmoModelo)) {
            $campanhasMesmoModelo = [$avaliacaoId];
        }

        $calcularMediaDeUsers = function($userIds) use ($campanhasMesmoModelo) {
            if (empty($userIds) || (is_countable($userIds) && count($userIds) === 0)) {
                return 0;
            }
            return DB::table('avaliacao_respostas')
                ->join('avaliacao_participantes', 'avaliacao_respostas.avaliacao_participante_id', '=', 'avaliacao_participantes.id')
                ->whereIn('avaliacao_participantes.avaliacao_id', $campanhasMesmoModelo)
                ->whereIn('avaliacao_participantes.avaliado_id', $userIds)
                ->avg('avaliacao_respostas.nota') ?? 0;
        };

        // Média Cargo
        $mediaCargo = 0;
        if ($colaborador->cargo_id) {
            $usersSameCargo = User::where('empresa_id', $empresaId)
                ->where('cargo_id', $colaborador->cargo_id)
                ->pluck('id');
            $mediaCargo = $calcularMediaDeUsers($usersSameCargo);
        }

        // Média Setor
        $mediaSetor = 0;
        if ($colaborador->departamento_id) {
            $usersSameDept = User::where('empresa_id', $empresaId)
                ->where('departamento_id', $colaborador->departamento_id)
                ->pluck('id');
            $mediaSetor = $calcularMediaDeUsers($usersSameDept);
        }

        // Média Empresa (Todas as notas das avaliações do mesmo modelo nesta empresa)
        $mediaEmpresa = DB::table('avaliacao_respostas')
            ->join('avaliacao_participantes', 'avaliacao_respostas.avaliacao_participante_id', '=', 'avaliacao_participantes.id')
            ->whereIn('avaliacao_participantes.avaliacao_id', $campanhasMesmoModelo)
            ->avg('avaliacao_respostas.nota') ?? 0;

        // Dados da Empresa Cliente para exibição no Relatório e Conformidade LGPD
        $empresa = \App\Models\Empresa::find($empresaId);
        $cnpjFormatado = null;
        if ($empresa && $empresa->cnpj) {
            $digits = preg_replace('/\D/', '', $empresa->cnpj);
            $cnpjFormatado = (strlen($digits) === 14)
                ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits)
                : $empresa->cnpj;
        }

        $logoUrl = null;
        $logoPath = null;
        if ($empresa && $empresa->logo) {
            if (filter_var($empresa->logo, FILTER_VALIDATE_URL)) {
                $logoUrl = $empresa->logo;
            } else {
                $logoUrl = asset('storage/' . ltrim($empresa->logo, '/'));
                $physicalPath = public_path('storage/' . ltrim($empresa->logo, '/'));
                if (file_exists($physicalPath)) {
                    $logoPath = $physicalPath;
                } else {
                    $storagePublic = storage_path('app/public/' . ltrim($empresa->logo, '/'));
                    if (file_exists($storagePublic)) {
                        $logoPath = $storagePublic;
                    }
                }
            }
        }

        $dadosEmpresa = [
            'id' => $empresa?->id,
            'nome' => $empresa?->nome ?? 'Empresa Cliente',
            'razao_social' => $empresa?->razao_social,
            'cnpj' => $cnpjFormatado,
            'logo_url' => $logoUrl,
            'logo_path' => $logoPath,
            'cor_primaria' => $empresa?->cor_primaria ?? '#4f46e5',
            'cor_secundaria' => $empresa?->cor_secundaria ?? '#7c3aed',
            'cor_terciaria' => $empresa?->cor_terciaria ?? '#1e1b4b',
        ];

        // Carrega competências vinculadas com seus respectivos pesos configurados no modelo
        $avaliacao->load('competencias');
        $pesosCompetenciasMap = $avaliacao->competencias->pluck('pivot.peso', 'nome')->toArray();

        // C. Detalhamento
        
        // Grupo 1: "Colaboradores" -> AUTOAVALIAÇÃO
        $partAuto = $participacoes->where('tipo', \App\Enums\TipoAvaliacao::AUTOAVALIACAO)->first();
        $mediaColab = $partAuto ? $calcularMediaPonderadaRespostas($partAuto->respostas) : 0;
        $detalhesColab = $partAuto ? $partAuto->respostas->map(function($r) use ($pesosCompetenciasMap) {
            $compNome = $r->skill->competencia->nome ?? 'Geral';
            return [
                'competencia' => $compNome,
                'tipo_competencia' => $r->skill->competencia->tipo ?? 'comportamental',
                'peso' => $pesosCompetenciasMap[$compNome] ?? 1,
                'pergunta' => $r->skill->nome, 
                'nota' => $r->nota,
                'evidencia' => $r->evidencia ?? null
            ];
        }) : [];

        // Grupo 2: "Avaliadores" -> Gestores/Pares/Subordinados (Excluindo Mediador e Auto)
        $partsAval = $participacoes->whereIn('tipo', [
            \App\Enums\TipoAvaliacao::GESTOR,
            \App\Enums\TipoAvaliacao::PARES,
            \App\Enums\TipoAvaliacao::SUBORDINADO
        ]); 
        $respostasAval = $partsAval->pluck('respostas')->flatten();
        $mediaAval = $calcularMediaPonderadaRespostas($respostasAval);
        $isOwnReport = (auth()->id() == $colaboradorId);

        $detalhesAval = [];
        $respostasPorSkill = $respostasAval->groupBy('skill_id');
        
        foreach($respostasPorSkill as $skillId => $resps) {
            $primeiraResp = $resps->first();
            $nomeSkill = $primeiraResp->skill->nome ?? 'Pergunta';
            $tipoComp = $primeiraResp->skill->competencia->tipo ?? 'comportamental';
            $compNome = $primeiraResp->skill->competencia->nome ?? 'Geral';
            $avgSkill = $resps->avg('nota');
            
            $breakdown = [];
            $evidenciasList = [];
            foreach($resps as $resp) {
                if (!$isOwnReport) {
                    $breakdown[] = [
                        'nome' => $resp->participante->avaliador->name ?? 'Avaliador',
                        'nota' => $resp->nota
                    ];
                }
                if (!empty($resp->evidencia)) {
                    $evidenciasList[] = [
                        'avaliador' => (!$isOwnReport) ? ($resp->participante->avaliador->name ?? 'Avaliador') : 'Avaliador',
                        'texto' => $resp->evidencia
                    ];
                }
            }
            
            $detalhesAval[] = [
                'competencia' => $compNome,
                'tipo_competencia' => $tipoComp,
                'peso' => $pesosCompetenciasMap[$compNome] ?? 1,
                'pergunta' => $nomeSkill,
                'nota' => number_format($avgSkill, 2),
                'breakdown' => $breakdown,
                'evidencias' => $evidenciasList
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

        // 4. Histórico de Avaliações deste Colaborador (Exclusivo para Gestores, Masters, Admins e Consultores)
        $historico = [];
        if (!$isOwnReport && in_array(auth()->user()->perfil_acesso->value, ['admin', 'master', 'gestor', 'consultor'])) {
            $outrasAvaliacoes = Avaliacao::where('is_template', false)
                ->whereHas('participantes', function($q) use ($colaboradorId) {
                    $q->where('avaliado_id', $colaboradorId);
                })
                ->orderBy('data_inicio', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($outrasAvaliacoes as $outraAv) {
                $partsOutra = AvaliacaoParticipante::where('avaliacao_id', $outraAv->id)
                    ->where('avaliado_id', $colaboradorId)
                    ->with('respostas')
                    ->get();
                
                $respsOutra = $partsOutra->pluck('respostas')->flatten();
                $mediaGeralOutra = $respsOutra->count() > 0 ? round($respsOutra->avg('nota'), 2) : 0;
                
                $autoOutra = $partsOutra->where('tipo', \App\Enums\TipoAvaliacao::AUTOAVALIACAO)->first();
                $mediaAutoOutra = ($autoOutra && $autoOutra->respostas->count() > 0) ? round($autoOutra->respostas->avg('nota'), 2) : null;
                
                $avalOutra = $partsOutra->whereIn('tipo', [\App\Enums\TipoAvaliacao::GESTOR, \App\Enums\TipoAvaliacao::PARES, \App\Enums\TipoAvaliacao::SUBORDINADO]);
                $respsAvalOutra = $avalOutra->pluck('respostas')->flatten();
                $mediaAvalOutra = $respsAvalOutra->count() > 0 ? round($respsAvalOutra->avg('nota'), 2) : null;

                $historico[] = [
                    'avaliacao_id' => $outraAv->id,
                    'titulo' => $outraAv->titulo,
                    'data_inicio' => $outraAv->data_inicio ? $outraAv->data_inicio->format('d/m/Y') : $outraAv->created_at->format('d/m/Y'),
                    'media_geral' => $mediaGeralOutra,
                    'media_auto' => $mediaAutoOutra,
                    'media_avaliadores' => $mediaAvalOutra,
                    'status' => ucfirst($outraAv->status?->value) ?? 'Em Andamento',
                    'is_atual' => ($outraAv->id == $avaliacaoId)
                ];
            }
        }

        // 5. PDI Recente do Colaborador (Para imprimir junto no relatório)
        $pdiRecente = \App\Models\Pdi::where('colaborador_id', $colaboradorId)
            ->with('acoes')
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'colaborador' => $colaborador,
            'avaliacao' => $avaliacao,
            'empresa' => $dadosEmpresa,
            'charts' => [
                'individual' => round($mediaIndividual, 2),
                'cargo' => round($mediaCargo, 2),
                'setor' => round($mediaSetor, 2),
                'empresa' => round($mediaEmpresa, 2),
            ],
            'is_own_report' => $isOwnReport,
            'avaliadores_breakdown' => $avaliadoresBreakdown,
            'historico' => $historico,
            'scores' => [
                'colab' => [ 'media' => number_format($mediaColab, 2), 'detalhes' => $detalhesColab ],
                'aval'  => [ 'media' => number_format($mediaAval, 2), 'detalhes' => $detalhesAval ],
                'med'   => [ 'media' => $mediaMediacao !== null ? number_format((float)$mediaMediacao, 2) : null, 'id_fechamento' => $fechamento?->id, 'comentarios' => $fechamento?->comentarios ],
                'minha_mediacao' => $minhaNotaMediacao !== null ? number_format($minhaNotaMediacao, 2) : null,
                'observacao_avaliado' => $partAuto?->observacao,
                'observacoes_avaliadores' => $partsAval->whereNotNull('observacao')->filter(fn($p) => trim($p->observacao) !== '')->map(function($p) {
                    return [
                        'nome' => $p->avaliador->name ?? 'Avaliador',
                        'texto' => $p->observacao
                    ];
                })->values(),
            ],
            'pdi_recente' => $pdiRecente
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

        // 2. Compilar o prompt: se houver gaps (notas <= 3.0 ou menores), foca neles; caso contrário, foca no aprimoramento contínuo
        $dadosCompilados = "";
        $hasLowScores = false;
        $todasCompetenciasTexto = "";

        foreach ($request->competencias as $compName => $dados) {
            foreach ($dados['skills'] as $skillName => $skillData) {
                $nota = isset($skillData['aval']['nota']) && $skillData['aval']['nota'] !== null ? floatval($skillData['aval']['nota']) : null;
                
                $infoLinha = "Competência Geral: {$compName}\n- Critério: {$skillName}\n";
                if ($nota !== null) {
                    $infoLinha .= "- Nota dos Avaliadores: {$nota}\n";
                }
                
                if (isset($skillData['aval']['comentarios']) && is_array($skillData['aval']['comentarios']) && count($skillData['aval']['comentarios']) > 0) {
                    $infoLinha .= "- Comentários/Feedbacks recebidos:\n";
                    foreach ($skillData['aval']['comentarios'] as $c) {
                        $infoLinha .= "  > \"{$c}\"\n";
                    }
                }
                $infoLinha .= "\n";

                $todasCompetenciasTexto .= $infoLinha;

                if ($nota !== null && $nota <= 3.0) {
                    $hasLowScores = true;
                    $dadosCompilados .= $infoLinha;
                }
            }
        }

        // Se o colaborador não teve notas baixas (<= 3.0), usamos todas as competências para focar em evolução contínua
        if (!$hasLowScores || empty(trim($dadosCompilados))) {
            $dadosCompilados = "O colaborador obteve bom desempenho geral. Analise as competências abaixo e sugira um plano de aprimoramento contínuo e liderança/alta performance:\n\n" . $todasCompetenciasTexto;
        }

        // 3. Execução Direta com a IA (Rápida e sem depender de worker de fila do servidor)
        try {
            /** @var \App\Services\AI\AiManager $iaService */
            $iaService = app(\App\Services\AI\AiManager::class);
            $resultado = $iaService->gerarPdiAvaliacao($dadosCompilados, $request->colaborador_nome);

            if ($resultado['success'] && !empty($resultado['dados'])) {
                // Incrementa contador de IA na empresa
                if ($user->empresa_id) {
                    \App\Models\Empresa::where('id', $user->empresa_id)->increment('ia_transcricoes_utilizadas');
                }

                return response()->json([
                    'success' => true,
                    'dados' => $resultado['dados'],
                    'message' => 'PDI gerado com sucesso pela Inteligência Artificial!'
                ]);
            }

            // Se o resultado não tiver sucesso, loga o erro retornado
            \Illuminate\Support\Facades\Log::warning("[AvaliacaoRelatorioController] Falha na IA direta: " . ($resultado['error'] ?? 'Erro desconhecido'));
            
            // Fallback: Tenta despachar na fila se a chamada direta tiver falhado
            \App\Jobs\GerarPdiAvaliacaoJob::dispatch(
                $dadosCompilados, 
                $request->colaborador_nome, 
                $colabId, 
                $user->id, 
                $user->empresa_id ?? 1
            );

            return response()->json([
                'success' => true,
                'queued' => true,
                'message' => 'A IA está finalizando a geração do PDI em segundo plano. Você será notificado!'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[AvaliacaoRelatorioController] Exceção ao gerar PDI com IA: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Não foi possível gerar o PDI com IA no momento: ' . $e->getMessage()
            ], 500);
        }
    }
}
