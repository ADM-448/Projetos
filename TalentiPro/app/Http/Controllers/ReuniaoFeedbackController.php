<?php

namespace App\Http\Controllers;

use App\Models\Pdi;
use App\Models\ReuniaoAgendamento;
use App\Models\ReuniaoFeedback;
use App\Models\User;
use App\Services\GeminiAudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReuniaoFeedbackController extends Controller
{
    public function __construct(protected GeminiAudioService $geminiService)
    {
    }

    // =========================================================================
    // Listagem e Agendamento
    // =========================================================================

    public function index(): View
    {
        $user = auth()->user();
        $isAcessoAdmin = isset($user->perfil_acesso) && in_array($user->perfil_acesso->value, ['master', 'admin']);

        $agendamentos = ReuniaoAgendamento::with(['gestor', 'participantes', 'feedback'])
            ->where(function ($q) use ($user, $isAcessoAdmin) {
                if ($isAcessoAdmin) {
                    // Admins e Masters visualizam todas as reuniões da mesma empresa
                    $q->whereHas('gestor', function ($query) use ($user) {
                        $query->where('empresa_id', $user->empresa_id);
                    });
                } else {
                    $q->where('gestor_id', $user->id)
                      ->orWhereHas('participantes', function ($pQuery) use ($user) {
                          $pQuery->where('users.id', $user->id);
                      });
                }
            })
            ->orderBy('data_hora', 'desc')
            ->get();

        return view('reunioes.index', compact('agendamentos'));
    }

    public function create(): View
    {
        $colaboradores = User::where('empresa_id', auth()->user()->empresa_id)
                             ->where('id', '!=', auth()->id())
                             ->get();

        return view('reunioes.create', compact('colaboradores'));
    }

    public function store(Request $request, \App\Services\GoogleCalendarService $googleService): RedirectResponse
    {
        $request->validate([
            'titulo'          => 'required|string|max:255',
            'participantes'   => 'required|array|min:1',
            'participantes.*' => 'exists:users,id',
            'data_hora'       => 'required|date|after:now',
            'tipo_reuniao'    => 'required|in:local,google_meet',
        ]);

        $user = auth()->user();
        $empresaId = $user->empresa_id;
        $googleEventId = null;
        $googleMeetLink = null;
        
        $colaboradores = User::whereIn('id', $request->participantes)->get();
        $emails = $colaboradores->pluck('email')->toArray();

        // Tenta criar o evento no Google Agenda se a empresa tiver vínculo
        if ($request->tipo_reuniao === 'google_meet') {
            try {
                $meetData = $googleService->createMeeting(
                    $empresaId,
                    $request->titulo,
                    'Reunião gerada pela plataforma Talenti-Pro.',
                    \Carbon\Carbon::parse($request->data_hora),
                    60, // 60 minutos default
                    $emails
                );

                $googleEventId = $meetData['id'];
                $googleMeetLink = $meetData['meet_link'];
            } catch (\Exception $e) {
                // Pode não ter configurado o Google ou token estar expirado.
                // Apenas registra o log e segue sem o meet_link.
                Log::info('Não foi possível gerar link do Google Meet: ' . $e->getMessage());
            }
        }

        $agendamento = ReuniaoAgendamento::create([
            'gestor_id'        => $user->id,
            'titulo'           => $request->titulo,
            'data_hora'        => $request->data_hora,
            'status'           => 'agendado',
            'google_event_id'  => $googleEventId,
            'google_meet_link' => $googleMeetLink,
        ]);

        // Sincroniza participantes na tabela pivot
        $agendamento->participantes()->sync($request->participantes);

        foreach ($request->participantes as $colaboradorId) {
            app(\App\Services\AtividadeService::class)->registrar(
                $empresaId,
                $colaboradorId,
                $agendamento->titulo,
                "Você foi convidado para a reunião: {$agendamento->titulo}.",
                'fa-solid fa-calendar-check',
                '#059669',
                route('reunioes.sala', $agendamento->id),
                'Reunião 1:1',
                'participar'
            );

            // Dispara E-mail
            app(\App\Services\EmailService::class)->notificar(
                $colaboradorId,
                "Convite para Reunião: {$agendamento->titulo}",
                "Você foi convidado para uma nova reunião agendada para " . \Carbon\Carbon::parse($agendamento->data_hora)->format('d/m/Y \à\s H:i') . ".\n\nAcesse a plataforma para visualizar os detalhes" . ($googleMeetLink ? ' e o link do Google Meet.' : '.'),
                route('reunioes.sala', $agendamento->id),
                'Acessar Reunião'
            );
        }

        if ($googleMeetLink) {
            return redirect()->route('reunioes.index')->with('success', 'Reunião agendada com sucesso com link do Google Meet!');
        }

        return redirect()->route('reunioes.index')->with('success', 'Reunião agendada com sucesso! (Sem link automático do Meet)');
    }

    // =========================================================================
    // Sala de Reunião
    // =========================================================================

    public function sala(ReuniaoAgendamento $reuniao): View|RedirectResponse
    {
        $this->autorizarParticipante($reuniao);

        $empresa    = auth()->user()->empresa;
        $podeUsarIa = $empresa->ia_ativa
                   && ($empresa->ia_transcricoes_utilizadas < $empresa->ia_limite_transcricoes_mes);

        // Carregar participantes se não carregados
        $reuniao->loadMissing('participantes');
        
        $participantesIds = $reuniao->participantes->pluck('id')->toArray();
        $participantesIds[] = $reuniao->gestor_id;
        
        // Participantes disponíveis para seleção no formulário de análise
        $participantes = User::whereIn('id', $participantesIds)->get();

        return view('reunioes.sala', compact('reuniao', 'podeUsarIa', 'participantes'));
    }

    // =========================================================================
    // ETAPA 1 DA IA: Upload e Transcrição do Áudio
    // =========================================================================

    /**
     * Recebe o arquivo de áudio, salva no storage local e envia para o Gemini transcrever.
     * Retorna apenas a transcrição (texto) — sem análise de PDI ainda.
     */
    public function uploadAudio(Request $request, ReuniaoAgendamento $reuniao): JsonResponse
    {
        if (auth()->id() !== $reuniao->gestor_id) {
            return response()->json(['success' => false, 'error' => 'Apenas o organizador pode enviar o áudio.']);
        }

        $empresa = auth()->user()->empresa;
        if (! $empresa->ia_ativa || $empresa->ia_transcricoes_utilizadas >= $empresa->ia_limite_transcricoes_mes) {
            return response()->json(['success' => false, 'error' => 'Limite de IA excedido ou serviço desabilitado.']);
        }

        $request->validate([
            'audio' => 'required|file|mimetypes:audio/webm,audio/mp4,audio/wav,audio/ogg,video/webm|max:51200',
        ]);

        $file      = $request->file('audio');
        $mimeType  = $file->getMimeType() ?? 'audio/webm';
        $extension = $file->getClientOriginalExtension() ?: 'webm';
        $filename  = 'reuniao_' . $reuniao->id . '_' . time() . '_' . Str::random(5) . '.' . $extension;

        // Salva no disco local (pasta storage/app/reunioes/audios)
        $audioPath = $file->storeAs('reunioes/audios', $filename);

        $reuniao->update(['status' => 'em_andamento']);

        // Lê o conteúdo do arquivo para enviar ao Gemini
        $audioContent = file_get_contents($file->getRealPath());

        $resultado = $this->geminiService->transcreverAudio($audioContent, $mimeType);

        if (! $resultado['success']) {
            // Se a IA falhou, remove o arquivo salvo para não deixar lixo
            Storage::delete($audioPath);
            Log::error('[ReuniaoFeedbackController] Transcrição falhou.', ['reuniao_id' => $reuniao->id]);
            return response()->json(['error' => $resultado['error']], 500);
        }

        // Armazena o path do áudio temporariamente na sessão para recuperar no salvarComIa
        session(["audio_path_reuniao_{$reuniao->id}" => $audioPath]);

        return response()->json([
            'success'    => true,
            'transcricao' => $resultado['transcricao'],
        ]);
    }

    // =========================================================================
    // ETAPA 2 DA IA: Análise da Transcrição → Resumo + PDIs sugeridos
    // =========================================================================

    /**
     * Recebe a transcrição (texto) e a lista de participantes,
     * e retorna o resumo executivo + sugestões de PDI para revisão do gestor.
     */
    public function processarTranscricao(Request $request, ReuniaoAgendamento $reuniao): JsonResponse
    {
        if (auth()->id() !== $reuniao->gestor_id) {
            return response()->json(['success' => false, 'error' => 'Apenas o organizador pode processar a reunião.']);
        }

        $request->validate([
            'transcricao'     => 'required|string|min:10',
            'participantes'   => 'required|array|min:1',
            'participantes.*' => 'exists:users,id',
        ]);

        $participantes = User::whereIn('id', $request->participantes)
                             ->pluck('name', 'id')
                             ->toArray();

        $resultado = $this->geminiService->analisarTranscricao($request->transcricao, $participantes);

        if (! $resultado['success']) {
            return response()->json(['error' => $resultado['error']], 500);
        }

        return response()->json([
            'success'    => true,
            'resumo'     => $resultado['resumo'],
            'sentimento' => $resultado['sentimento'],
            'pdis'       => $resultado['pdis'],
        ]);
    }

    // =========================================================================
    // ETAPA 3 DA IA: Salvar Definitivo (após revisão do gestor)
    // =========================================================================

    /**
     * Recebe o resumo e PDIs revisados/aprovados pelo gestor e persiste tudo.
     * Também debita o crédito de transcrição da empresa.
     */
    public function salvarComIa(Request $request, ReuniaoAgendamento $reuniao): JsonResponse
    {
        if (auth()->id() !== $reuniao->gestor_id) {
            return response()->json(['success' => false, 'error' => 'Apenas o organizador pode salvar.']);
        }

        $request->validate([
            'transcricao'                 => 'required|string',
            'transcricao_original'        => 'nullable|string',
            'resumo'                      => 'required|string|min:10',
            'resumo_original'             => 'required|string',
            'sentimento'                  => 'nullable|string|in:Positivo,Neutro,Negativo',
            'participantes'               => 'required|array|min:1',
            'participantes.*'             => 'exists:users,id',
            'pdis'                        => 'nullable|array',
            'pdis.*.colaborador_id'       => 'required_with:pdis|exists:users,id',
            'pdis.*.titulo'               => 'required_with:pdis|string|max:255',
            'pdis.*.descricao'            => 'nullable|string',
            'pdis.*.prazo'                => 'nullable|date',
            'pdis.*.acoes'                => 'nullable|array',
            'pdis.*.acoes.*.titulo'       => 'required_with:pdis.*.acoes|string|max:255',
            'pdis.*.acoes.*.descricao'    => 'nullable|string',
        ]);

        // Recupera o path do áudio da sessão
        $audioPath = session("audio_path_reuniao_{$reuniao->id}");

        // Cria o registro de Feedback
        $feedback = ReuniaoFeedback::create([
            'gestor_id'           => $reuniao->gestor_id,
            'agendamento_id'      => $reuniao->id,
            'transcricao_completa' => $request->transcricao,
            'transcricao_original' => $request->transcricao_original ?? $request->transcricao,
            'resumo_ia'           => $request->resumo,
            'resumo_ia_original'  => $request->resumo_original,
            'sentimento_ia'       => $request->sentimento,
            'audio_path'          => $audioPath,
        ]);

        $feedback->participantes()->sync($request->participantes);

        // Cria os PDIs aprovados pelo gestor
        if ($request->has('pdis') && is_array($request->pdis)) {
            foreach ($request->pdis as $pdiData) {
                $pdi = Pdi::create([
                    'empresa_id'          => auth()->user()->empresa_id,
                    'colaborador_id'      => $pdiData['colaborador_id'],
                    'gestor_id'           => auth()->id(),
                    'reuniao_feedback_id' => $feedback->id,
                    'titulo'              => $pdiData['titulo'],
                    'descricao'           => $pdiData['descricao'] ?? null,
                    'data_fim'            => empty($pdiData['prazo']) ? null : $pdiData['prazo'],
                    'status'              => 'nao_iniciado',
                    'progresso'           => 0,
                ]);

                if (!empty($pdiData['acoes']) && is_array($pdiData['acoes'])) {
                    foreach ($pdiData['acoes'] as $acaoData) {
                        $pdi->acoes()->create([
                            'titulo'    => $acaoData['titulo'],
                            'descricao' => $acaoData['descricao'] ?? null,
                            'status'    => 'pendente',
                        ]);
                    }
                }
            }
        }

        // Debita crédito de IA da empresa
        auth()->user()->empresa->increment('ia_transcricoes_utilizadas');

        $reuniao->update(['status' => 'concluido']);

        // Limpa sessão
        session()->forget("audio_path_reuniao_{$reuniao->id}");

        return response()->json([
            'success'    => true,
            'redirect'   => route('reunioes.show', $reuniao->id),
            'message'    => 'Reunião salva com sucesso! PDIs criados automaticamente.',
        ]);
    }

    // =========================================================================
    // Modo Manual (sem IA)
    // =========================================================================

    /**
     * Salva o relatório da reunião preenchido manualmente pelo gestor, sem IA.
     */
    public function salvarManual(Request $request, ReuniaoAgendamento $reuniao): RedirectResponse
    {
        if (auth()->id() !== $reuniao->gestor_id) {
            abort(403);
        }

        $request->validate([
            'resumo' => 'required|string|min:10',
        ]);

        $feedback = ReuniaoFeedback::create([
            'gestor_id'            => $reuniao->gestor_id,
            'agendamento_id'       => $reuniao->id,
            'transcricao_completa' => 'Registro Manual',
            'transcricao_original' => 'Registro Manual',
            'resumo_ia'            => $request->resumo,
            'sentimento_ia'        => null,
            'audio_path'           => null,
        ]);

        $ids = $reuniao->participantes->pluck('id')->toArray();
        $ids[] = $reuniao->gestor_id;
        $feedback->participantes()->attach($ids);

        $reuniao->update(['status' => 'concluido']);

        return redirect()
            ->route('reunioes.show', $reuniao->id)
            ->with('success', 'Relatório da reunião salvo com sucesso!');
    }

    // =========================================================================
    // Visualização e Exclusão
    // =========================================================================

    public function show(ReuniaoAgendamento $reuniao): View|RedirectResponse
    {
        $this->autorizarParticipante($reuniao);

        $reuniao->load(['feedback.pdis.colaborador', 'gestor', 'participantes']);

        if (! $reuniao->feedback) {
            return redirect()
                ->route('reunioes.index')
                ->with('error', 'Nenhum relatório encontrado para esta reunião.');
        }

        return view('reunioes.show', compact('reuniao'));
    }

    // =========================================================================
    // Helper Privado
    // =========================================================================

    /**
     * Garante que apenas o gestor, o colaborador da reunião ou perfis administrativos (master/admin) têm acesso.
     */
    protected function autorizarParticipante(ReuniaoAgendamento $reuniao): void
    {
        $user = auth()->user();
        $isAcessoAdmin = isset($user->perfil_acesso) && in_array($user->perfil_acesso->value, ['master', 'admin']);

        $reuniao->loadMissing('participantes');

        if (auth()->id() !== $reuniao->gestor_id && !$reuniao->participantes->contains(auth()->id()) && !$isAcessoAdmin) {
            abort(403, 'Você não tem permissão para acessar esta reunião.');
        }
    }

    /**
     * Exclui a reunião e todo o feedback/PDI associado.
     */
    public function destroy(ReuniaoAgendamento $reuniao): RedirectResponse
    {
        if (auth()->id() !== $reuniao->gestor_id && !in_array(auth()->user()->perfil_acesso->value, ['master', 'admin'])) {
            abort(403, 'Apenas o criador da reunião ou administradores podem excluí-la.');
        }

        if ($reuniao->feedback) {
            // Deleta os PDIs que foram vinculados a este feedback
            Pdi::where('reuniao_feedback_id', $reuniao->feedback->id)->delete();
            
            // Deleta o áudio do storage local
            if ($reuniao->feedback->audio_path) {
                Storage::delete($reuniao->feedback->audio_path);
            }
            
            $reuniao->feedback->delete();
        }

        $reuniao->delete();

        return redirect()->route('reunioes.index')->with('success', 'Reunião e todos os registros associados foram excluídos.');
    }

    /**
     * Atualiza os dados de resumo e PDIs da reunião de forma inline.
     */
    public function updateRegistro(Request $request, ReuniaoAgendamento $reuniao): JsonResponse
    {
        if (auth()->id() !== $reuniao->gestor_id) {
            return response()->json(['success' => false, 'error' => 'Apenas o criador da reunião pode editar este registro.']);
        }

        $request->validate([
            'resumo'                    => 'required|string|min:10',
            'sentimento'                => 'nullable|string|in:Positivo,Neutro,Negativo',
            'pdis'                      => 'nullable|array',
            'pdis.*.id'                 => 'nullable|exists:pdis,id',
            'pdis.*.colaborador_id'     => 'required_with:pdis|exists:users,id',
            'pdis.*.titulo'             => 'required_with:pdis|string|max:255',
            'pdis.*.descricao'          => 'nullable|string',
            'pdis.*.prazo'              => 'nullable|date',
            'pdis.*.acoes'              => 'nullable|array',
            'pdis.*.acoes.*.id'         => 'nullable|exists:pdi_acoes,id',
            'pdis.*.acoes.*.titulo'     => 'required_with:pdis.*.acoes|string|max:255',
            'pdis.*.acoes.*.descricao'  => 'nullable|string',
            'deletar_pdis'              => 'nullable|array',
            'deletar_pdis.*'            => 'exists:pdis,id',
        ]);

        $feedback = $reuniao->feedback;
        if (!$feedback) {
            return response()->json(['error' => 'Relatório não encontrado.'], 404);
        }

        // 1. Atualiza o resumo e sentimento no feedback (forçando o updated_at mudar)
        $feedback->update([
            'resumo_ia'     => $request->resumo,
            'sentimento_ia' => $request->sentimento,
        ]);
        
        // Garante que o timestamp de modificação mude explicitamente
        $feedback->touch();

        // 2. Exclui os PDIs marcados para deleção
        if ($request->has('deletar_pdis') && is_array($request->deletar_pdis)) {
            Pdi::whereIn('id', $request->deletar_pdis)
               ->where('reuniao_feedback_id', $feedback->id)
               ->delete();
        }

        // 3. Atualiza ou insere novos PDIs
        if ($request->has('pdis') && is_array($request->pdis)) {
            foreach ($request->pdis as $pdiData) {
                if (!empty($pdiData['id'])) {
                    // Atualiza PDI existente
                    $pdi = Pdi::where('id', $pdiData['id'])
                        ->where('reuniao_feedback_id', $feedback->id)
                        ->first();
                        
                    if ($pdi) {
                        $pdi->update([
                            'colaborador_id' => $pdiData['colaborador_id'],
                            'titulo'         => $pdiData['titulo'],
                            'descricao'      => $pdiData['descricao'] ?? null,
                            'data_fim'       => empty($pdiData['prazo']) ? null : $pdiData['prazo'],
                        ]);
                    }
                } else {
                    // Cria novo PDI associado
                    $pdi = Pdi::create([
                        'empresa_id'          => auth()->user()->empresa_id,
                        'colaborador_id'      => $pdiData['colaborador_id'],
                        'gestor_id'           => auth()->id(),
                        'reuniao_feedback_id' => $feedback->id,
                        'titulo'              => $pdiData['titulo'],
                        'descricao'           => $pdiData['descricao'] ?? null,
                        'data_fim'            => empty($pdiData['prazo']) ? null : $pdiData['prazo'],
                        'status'              => 'nao_iniciado',
                        'progresso'           => 0,
                    ]);
                }
                
                // Trata as ações do PDI
                if (isset($pdi) && $pdi && isset($pdiData['acoes']) && is_array($pdiData['acoes'])) {
                    $acoesProcessadas = [];
                    foreach ($pdiData['acoes'] as $acaoData) {
                        if (!empty($acaoData['id'])) {
                            // Atualiza ação existente
                            $acao = $pdi->acoes()->find($acaoData['id']);
                            if ($acao) {
                                $acao->update([
                                    'titulo' => $acaoData['titulo'],
                                    'descricao' => $acaoData['descricao'] ?? null,
                                ]);
                                $acoesProcessadas[] = $acao->id;
                            }
                        } else {
                            // Cria nova ação
                            $nova = $pdi->acoes()->create([
                                'titulo' => $acaoData['titulo'],
                                'descricao' => $acaoData['descricao'] ?? null,
                                'status' => 'pendente',
                            ]);
                            $acoesProcessadas[] = $nova->id;
                        }
                    }
                    // Remove ações que não vieram no payload
                    $pdi->acoes()->whereNotIn('id', $acoesProcessadas)->delete();
                    
                    // Recalcula o progresso se for existente
                    if (!empty($pdiData['id'])) {
                        app(\App\Contracts\PdiServiceContract::class)->recalcularProgresso($pdi);
                    }
                } elseif (isset($pdi) && $pdi) {
                    // Se não vieram ações, apaga todas
                    $pdi->acoes()->delete();
                    app(\App\Contracts\PdiServiceContract::class)->recalcularProgresso($pdi);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Relatório e PDIs atualizados com sucesso.',
            'updated_at' => $feedback->updated_at->format('d/m/Y \à\s H:i')
        ]);
    }
}
