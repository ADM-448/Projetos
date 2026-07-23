<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pdi;
use App\Models\ReuniaoFeedback;
use App\Models\ReuniaoAgendamento;
use App\Services\GoogleIntegrationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ReuniaoFeedbackWebController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!in_array($user->perfil_acesso, ['master', 'gestor'])) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        $reunioes = ReuniaoFeedback::with(['participantes', 'gestor'])
            ->where('gestor_id', $user->id)
            ->orWhereHas('participantes', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                if ($user->perfil_acesso === 'master') {
                    $q->whereNotNull('id'); // Master vê todas
                }
            })
            ->get();

        $agendamentos = ReuniaoAgendamento::with(['gestor', 'colaborador'])
            ->where('gestor_id', $user->id)
            ->orWhere('colaborador_id', $user->id)
            ->orWhere(function ($q) use ($user) {
                if ($user->perfil_acesso === 'master') {
                    $q->whereNotNull('id');
                }
            })
            ->get();

        $unified = collect();

        foreach ($agendamentos as $a) {
            $unified->push((object)[
                'tipo' => 'agendamento',
                'id' => $a->id,
                'data_hora' => $a->data_hora,
                'titulo' => $a->titulo,
                'gestor_nome' => $a->gestor->name ?? 'N/A',
                'com_quem' => $a->colaborador->name ?? 'N/A',
                'status' => $a->status,
                'link_meet' => $a->google_meet_link,
                'gestor_id' => $a->gestor_id,
                'feedback_id' => null
            ]);
        }

        foreach ($reunioes as $r) {
            if ($r->agendamento_id) {
                $item = $unified->firstWhere('id', $r->agendamento_id);
                if ($item && $item->tipo === 'agendamento') {
                    $item->feedback_id = $r->id;
                    $item->status = 'concluido';
                    continue;
                }
            }
            
            $unified->push((object)[
                'tipo' => 'avulsa',
                'id' => $r->id,
                'data_hora' => $r->created_at,
                'titulo' => $r->titulo,
                'gestor_nome' => $r->gestor->name ?? 'N/A',
                'com_quem' => $r->participantes->pluck('name')->join(', '),
                'status' => 'concluido',
                'link_meet' => null,
                'gestor_id' => $r->gestor_id,
                'feedback_id' => $r->id
            ]);
        }

        $lista = $unified->sortByDesc('data_hora');

        // Para popular o form de novo agendamento
        if ($user->perfil_acesso === 'gestor') {
            $users = User::where('departamento_id', $user->departamento_id)
                ->orWhere('gestor_id', $user->id)
                ->get();
        } else {
            $users = User::all();
        }

        return view('reunioes.index', compact('lista', 'users'));
    }

    public function storeAgendamento(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'colaborador_id' => 'required|exists:users,id',
            'data_hora' => 'required|date',
        ]);

        $colaborador = User::find($request->colaborador_id);
        
        $googleEventId = null;
        $meetLink = null;

        try {
            $googleService = new GoogleIntegrationService();
            // Cria o evento no Google Agenda e gera o link do Meet
            $googleEvent = $googleService->createMeetEvent(
                $request->titulo, 
                $request->data_hora, 
                [$colaborador->email, auth()->user()->email]
            );
            $googleEventId = $googleEvent['event_id'];
            $meetLink = $googleEvent['meet_link'];
        } catch (\Throwable $e) {
            $googleEventId = null;
            $meetLink = null;
            $erroGoogle = 'A reunião foi agendada apenas no sistema. A integração com o Google Agenda falhou ou não está configurada (' . $e->getMessage() . ').';
        }

        ReuniaoAgendamento::create([
            'gestor_id' => auth()->id(),
            'colaborador_id' => $request->colaborador_id,
            'titulo' => $request->titulo,
            'data_hora' => $request->data_hora,
            'google_event_id' => $googleEventId,
            'google_meet_link' => $meetLink,
            'status' => 'agendado'
        ]);

        // Cria a notificação de sistema para o colaborador, o que dispara automaticamente o WhatsApp
        \App\Models\Notificacao::create([
            'empresa_id' => auth()->user()->empresa_id ?? 1,
            'destinatario_id' => $request->colaborador_id,
            'remetente_id' => auth()->id(),
            'tipo' => 'reuniao',
            'mensagem' => 'Nova reunião agendada: "' . $request->titulo . '" para o dia ' . \Carbon\Carbon::parse($request->data_hora)->format('d/m/Y \à\s H:i') . ($meetLink ? ' (Google Meet disponível)' : ''),
            'link' => route('reunioes.index')
        ]);

        if (isset($erroGoogle)) {
            return redirect()->back()->with('warning', $erroGoogle);
        }

        return redirect()->back()->with('success', 'Reunião agendada com sucesso no sistema e no Google Agenda!');
    }

    public function fetchTranscriptFromDrive(Request $request)
    {
        $request->validate([
            'drive_url' => 'required|string'
        ]);

        try {
            $googleService = new GoogleIntegrationService();
            $texto = $googleService->fetchTranscriptText($request->drive_url);
            return response()->json(['success' => true, 'texto' => $texto]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
    public function uploadAudioAndProcess(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,mp3,mp4,wav,ogg,m4a',
            'titulo' => 'nullable|string'
        ]);

        $user = auth()->user();
        if (!$user->google_access_token) {
            return response()->json(['success' => false, 'error' => 'Google Drive não está vinculado.']);
        }

        try {
            $audioFile = $request->file('audio');
            $audioContent = file_get_contents($audioFile->getRealPath());
            $mimeType = $audioFile->getMimeType();

            // Ajusta tipo MIME de vídeo para áudio (ex: video/webm -> audio/webm) para o Gemini não esperar frames de vídeo
            if ($mimeType && strpos($mimeType, 'video/') === 0) {
                $mimeType = str_replace('video/', 'audio/', $mimeType);
            }

            // 1. Upload para o Google Drive
            $client = new \Google\Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $client->setAccessToken($user->google_access_token);

            if ($client->isAccessTokenExpired()) {
                if ($user->google_refresh_token) {
                    $newAccessToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    $user->google_access_token = $newAccessToken['access_token'];
                    if(isset($newAccessToken['expires_in'])) {
                        $user->google_token_expires_at = \Carbon\Carbon::now()->addSeconds($newAccessToken['expires_in']);
                    }
                    $user->save();
                } else {
                    return response()->json(['success' => false, 'error' => 'Token do Google expirado. Por favor, desvincule e vincule novamente.']);
                }
            }

            $driveService = new \Google\Service\Drive($client);
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => ($request->titulo ?: 'Reuniao_TalentiPro') . '_' . date('Ymd_His') . '.' . $audioFile->getClientOriginalExtension()
            ]);

            $driveFile = $driveService->files->create($fileMetadata, [
                'data' => $audioContent,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            // 2. Enviar para o Gemini para Transcrição
            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) {
                return response()->json(['success' => false, 'error' => 'Chave da API do Gemini não configurada.']);
            }

            $base64Audio = base64_encode($audioContent);

            // Lista de modelos para tentar em ordem de preferência (fallback automático)
            $modelos = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-2.0-flash-lite'];
            $maxRetries = 2; // tentativas por modelo
            $response = null;
            $lastError = '';

            foreach ($modelos as $modelo) {
                for ($tentativa = 1; $tentativa <= $maxRetries; $tentativa++) {
                    \Log::info("Gemini Transcrição: Tentando modelo {$modelo} (tentativa {$tentativa}/{$maxRetries})");

                    $response = \Illuminate\Support\Facades\Http::timeout(120)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key=" . $apiKey, [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => 'Você é um transcritor profissional. Por favor, ouça o áudio desta reunião e gere ESTRITAMENTE E APENAS a transcrição em texto de tudo o que foi falado. Não adicione comentários, não resuma, não invente informações. Se o áudio for inaudível ou não tiver vozes, responda apenas: [Áudio inaudível ou sem voz].'],
                                        [
                                            'inlineData' => [
                                                'mimeType' => $mimeType,
                                                'data' => $base64Audio
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]);

                    if ($response->successful()) {
                        $transcricao = $response->json('candidates.0.content.parts.0.text');
                        \Log::info("Gemini Transcrição: Sucesso com modelo {$modelo}");
                        return response()->json(['success' => true, 'transcricao' => trim($transcricao), 'drive_file_id' => $driveFile->id]);
                    }

                    $lastError = $response->body();
                    $statusCode = $response->status();
                    \Log::warning("Gemini Transcrição: Erro {$statusCode} com modelo {$modelo} (tentativa {$tentativa}): {$lastError}");

                    // Se for 503 (sobrecarregado) ou 429 (quota/rate limit), espera antes de tentar
                    if ($statusCode === 503 || $statusCode === 429) {
                        sleep(3 * $tentativa); // espera progressiva: 3s, 6s
                        continue;
                    }

                    // Para outros erros (400, 404, etc.), pula direto para o próximo modelo
                    break;
                }
            }

            // Mensagem amigável para o usuário quando a cota é excedida
            if (str_contains($lastError, '429') || str_contains($lastError, 'quota')) {
                return response()->json(['success' => false, 'error' => 'A cota gratuita da API de IA foi atingida temporariamente. Por favor, tente novamente em alguns minutos ou amanhã. Se o problema persistir, o administrador precisa verificar o plano de faturamento da API Gemini no Google Cloud.']);
            }

            return response()->json(['success' => false, 'error' => 'Erro na API do Gemini (todos os modelos falharam): ' . $lastError]);

        } catch (\Throwable $e) {
            \Log::error('Erro no uploadAudioAndProcess: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            $errorMsg = $e->getMessage();
            
            // Tratamento amigável para falta de espaço no Drive
            if (str_contains(strtolower($errorMsg), 'quota') || str_contains(strtolower($errorMsg), 'storagequotaexceeded') || str_contains(strtolower($errorMsg), 'insufficient space')) {
                return response()->json(['success' => false, 'error' => 'O seu Google Drive está lotado (sem espaço livre). Por favor, libere espaço apagando arquivos antigos ou assine um plano do Google One para poder salvar mais reuniões.']);
            }
            
            if (str_contains(strtolower($errorMsg), 'invalid credentials') || str_contains(strtolower($errorMsg), 'unauthorized')) {
                return response()->json(['success' => false, 'error' => 'Sua conexão com o Google expirou ou foi revogada. Por favor, desvincule o Google Drive no painel e vincule novamente.']);
            }
            
            return response()->json(['success' => false, 'error' => 'Erro ao salvar no Drive ou processar o áudio: ' . $errorMsg]);
        }
    }

    public function create()
    {
        $user = auth()->user();
        if (!in_array($user->perfil_acesso, ['master', 'gestor'])) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        // Se gestor, lista apenas pessoas do seu departamento ou subodinados
        if ($user->perfil_acesso === 'gestor') {
            $users = User::where('departamento_id', $user->departamento_id)
                ->orWhere('gestor_id', $user->id)
                ->get();
        } else {
            $users = User::all();
        }

        $agendamentoId = request()->query('agendamento_id');
        $agendamento = null;
        $transcricao = '';

        if ($agendamentoId) {
            $agendamento = ReuniaoAgendamento::findOrFail($agendamentoId);
            $transcricao = trim($agendamento->transcricao_temporaria);
            $agendamento->update(['status' => 'concluido']);
        }

        return view('reunioes.create', compact('users', 'agendamento', 'transcricao'));
    }

    public function processar(Request $request)
    {
        $request->validate([
            'participantes' => 'required|array'
        ]);

        // Se transcrição não for obrigatória na validação inicial, podemos dar bypass ou retornar algo mockado
        $transcricao = $request->transcricao ?? '';

        $participantes = User::whereIn('id', $request->participantes)->pluck('name', 'id')->toArray();
        $nomesParticipantes = implode(", ", $participantes);

        $prompt = "Você é um assistente de RH. Uma reunião de feedback acabou de ocorrer. 
Aqui está a transcrição da reunião:
\"" . $request->transcricao . "\"

Os participantes avaliados (colaboradores) mencionados ou presentes são: " . $nomesParticipantes . ".

Sua tarefa é analisar a transcrição e retornar ESTRITAMENTE um JSON no seguinte formato, sem formatação markdown ou texto extra:
{
    \"resumo\": \"Resumo detalhado da reunião...\",
    \"pdis\": [
        {
            \"colaborador_id\": ID_DO_COLABORADOR,
            \"titulo\": \"Título curto da tarefa ou objetivo\",
            \"descricao\": \"Descrição detalhada do que deve ser feito\",
            \"prazo\": \"YYYY-MM-DD\" (seja flexível mas inferido a partir da data de hoje ou coloque nulo)
        }
    ]
}

Importante: Mapeie as tarefas para os colaboradores corretos usando os IDs (" . json_encode($participantes) . ").";

        $apiKey = env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            return response()->json(['error' => 'Chave da API do Gemini não configurada no .env (GEMINI_API_KEY)'], 500);
        }

        try {
            $modelos = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-2.0-flash-lite'];
            $maxRetries = 2;
            $lastError = '';

            foreach ($modelos as $modelo) {
                for ($tentativa = 1; $tentativa <= $maxRetries; $tentativa++) {
                    \Log::info("Gemini Análise: Tentando modelo {$modelo} (tentativa {$tentativa}/{$maxRetries})");

                    $response = Http::timeout(60)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key=" . $apiKey, [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $prompt]
                                    ]
                                ]
                            ],
                            'generationConfig' => [
                                'responseMimeType' => 'application/json'
                            ]
                        ]);

                    if ($response->successful()) {
                        $content = $response->json('candidates.0.content.parts.0.text');
                        // Gemini as vezes retorna markdown code blocks, vamos limpar
                        $content = str_replace(['```json', '```'], '', $content);
                        $dados = json_decode(trim($content), true);
                        
                        // Mapear nomes para a visualização
                        if (isset($dados['pdis'])) {
                            foreach ($dados['pdis'] as &$pdi) {
                                $pdi['colaborador_nome'] = $participantes[$pdi['colaborador_id']] ?? 'Desconhecido';
                            }
                        }
                        
                        \Log::info("Gemini Análise: Sucesso com modelo {$modelo}");
                        return response()->json($dados);
                    }

                    $lastError = $response->body();
                    $statusCode = $response->status();
                    \Log::warning("Gemini Análise: Erro {$statusCode} com modelo {$modelo} (tentativa {$tentativa}): {$lastError}");

                    if ($statusCode === 503 || $statusCode === 429) {
                        sleep(3 * $tentativa);
                        continue;
                    }
                    break;
                }
            }

            if (str_contains($lastError, '429') || str_contains($lastError, 'quota')) {
                return response()->json(['error' => 'A cota gratuita da API de IA foi atingida temporariamente. Por favor, tente novamente em alguns minutos ou amanhã.'], 429);
            }

            return response()->json(['error' => 'Erro na API do Gemini (todos os modelos falharam): ' . $lastError], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao processar IA: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'resumo' => 'required|string',
            'transcricao' => 'nullable|string',
            'participantes' => 'required|array',
        ]);

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $filename = 'reuniao_' . time() . '_' . Str::random(5) . '.webm';
            $audioPath = $file->storeAs('reunioes_audio', $filename, 'public');
        }

        $reuniao = ReuniaoFeedback::create([
            'titulo' => $request->titulo,
            'gestor_id' => auth()->id(),
            'agendamento_id' => $request->agendamento_id,
            'transcricao' => $request->transcricao ?? 'Transcrição não registrada.',
            'resumo' => $request->resumo,
            'audio_path' => $audioPath,
            'status' => 'aprovada',
        ]);

        $reuniao->participantes()->sync($request->participantes);

        if ($request->has('pdis') && is_array($request->pdis)) {
            foreach ($request->pdis as $pdiData) {
                // Criar PDI
                Pdi::create([
                    'empresa_id' => auth()->user()->empresa_id ?? 1,
                    'colaborador_id' => $pdiData['colaborador_id'],
                    'gestor_id' => auth()->id(),
                    'titulo' => $pdiData['titulo'],
                    'descricao' => $pdiData['descricao'],
                    'prazo' => empty($pdiData['prazo']) ? null : $pdiData['prazo'],
                    'status' => 'nao_iniciado',
                    'progresso' => 0
                ]);
            }
        }

        session()->flash('success', 'Reunião salva com sucesso!');
        return response()->json(['success' => true, 'redirect' => route('reunioes.index')]);
    }

    public function show($id)
    {
        $user = auth()->user();
        if (!in_array($user->perfil_acesso, ['master', 'gestor'])) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        $reuniao = ReuniaoFeedback::with('participantes', 'gestor')->findOrFail($id);
        
        return view('reunioes.show', compact('reuniao'));
    }

    public function destroy($id)
    {
        $reuniao = ReuniaoFeedback::findOrFail($id);
        if ($reuniao->gestor_id !== auth()->id() && !in_array(auth()->user()->perfil_acesso, ['admin', 'master'])) {
            return redirect()->back()->with('error', 'Sem permissão para deletar esta reunião.');
        }
        $reuniao->delete();
        return redirect()->back()->with('success', 'Reunião deletada com sucesso!');
    }

    public function destroyAgendamento($id)
    {
        $agendamento = ReuniaoAgendamento::findOrFail($id);
        if ($agendamento->gestor_id !== auth()->id() && !in_array(auth()->user()->perfil_acesso, ['admin', 'master'])) {
            return redirect()->back()->with('error', 'Sem permissão para deletar este agendamento.');
        }
        $agendamento->delete();
        return redirect()->back()->with('success', 'Agendamento deletado com sucesso!');
    }
}
