<?php

namespace App\Services\AI\Providers;

use App\Exceptions\AiRateLimitException;
use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenAiProvider implements AiProviderInterface
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY', '');
    }

    public function getName(): string
    {
        return 'OpenAI (ChatGPT/Whisper)';
    }

    public function transcreverAudio(string $audioContent, string $mimeType): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API da OpenAI não configurada.');
        }

        // Extensão baseada no mime type (simplificado)
        $ext = 'webm';
        if (str_contains($mimeType, 'mp4')) $ext = 'mp4';
        elseif (str_contains($mimeType, 'mp3')) $ext = 'mp3';
        elseif (str_contains($mimeType, 'wav')) $ext = 'wav';
        elseif (str_contains($mimeType, 'ogg')) $ext = 'ogg';

        $filename = 'audio_' . Str::random(10) . '.' . $ext;

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])
            ->attach('file', $audioContent, $filename)
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'pt',
            ]);

        if (!$response->successful()) {
            $status = $response->status();
            $erro = $response->body();
            Log::warning("[OpenAiProvider] Falha HTTP {$status}: {$erro}");

            if ($status === 429 || str_contains(strtolower($erro), 'quota')) {
                throw new AiRateLimitException("OpenAI atingiu limite de requisições. Status: {$status}");
            }
            throw new \Exception("Erro na API da OpenAI (Whisper): {$erro}");
        }

        Log::info('[OpenAiProvider] Transcrição de áudio concluída.');
        return $response->json('text', '');
    }

    public function analisarTranscricao(string $transcricao, array $participantes): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API da OpenAI não configurada.');
        }

        if (empty(trim($transcricao))) {
            throw new \Exception('A transcrição está vazia. Não é possível analisar.');
        }

        $nomesParticipantes = implode(', ', $participantes);
        $mapeamentoJson     = json_encode($participantes, JSON_UNESCAPED_UNICODE);

        $prompt = "Você é um assistente especialista em Recursos Humanos. Uma reunião de feedback acabou de ocorrer.\n\n"
                . "TRANSCRIÇÃO DA REUNIÃO:\n\"\"\"\n{$transcricao}\n\"\"\"\n\n"
                . "PARTICIPANTES (colaboradores avaliados): {$nomesParticipantes}\n"
                . "MAPEAMENTO DE IDs: {$mapeamentoJson}\n\n"
                . "Sua tarefa é analisar a transcrição e retornar ESTRITAMENTE um JSON válido no formato abaixo, "
                . "sem markdown, sem texto extra antes ou depois:\n"
                . "{\n"
                . "  \"resumo\": \"Resumo executivo detalhado da reunião, incluindo tópicos discutidos e conclusões.\",\n"
                . "  \"sentimento\": \"Positivo | Neutro | Negativo\",\n"
                . "  \"pdis\": [\n"
                . "    {\n"
                . "      \"colaborador_id\": ID_NUMERICO_DO_COLABORADOR,\n"
                . "      \"colaborador_nome\": \"Nome do Colaborador\",\n"
                . "      \"titulo\": \"Título curto da ação ou objetivo de desenvolvimento geral\",\n"
                . "      \"descricao\": \"Descrição do propósito geral do PDI\",\n"
                . "      \"prazo\": \"YYYY-MM-DD ou null se não mencionado\",\n"
                . "      \"acoes\": [\n"
                . "        {\n"
                . "          \"titulo\": \"Passo específico ou meta menor\",\n"
                . "          \"descricao\": \"Detalhes de como executar este passo\"\n"
                . "        }\n"
                . "      ]\n"
                . "    }\n"
                . "  ]\n"
                . "}\n\n"
                . "Mapeie cada ação para o colaborador correto usando os IDs numéricos do mapeamento acima. "
                . "Se não houver ações claras para algum colaborador, não o inclua no array 'pdis'.";

        $payload = [
            'model' => 'gpt-3.5-turbo', // ou gpt-4o-mini, mais barato e rápido
            'messages' => [
                ['role' => 'system', 'content' => 'Você responde estritamente com JSON válido.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2
        ];

        $response = Http::timeout(60)
            ->withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}",
            ])
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$response->successful()) {
            $status = $response->status();
            $erro = $response->body();
            Log::warning("[OpenAiProvider] Falha HTTP {$status}: {$erro}");

            if ($status === 429 || str_contains(strtolower($erro), 'quota')) {
                throw new AiRateLimitException("OpenAI atingiu limite de requisições. Status: {$status}");
            }
            throw new \Exception("Erro na API da OpenAI: {$erro}");
        }

        $respostaText = $response->json('choices.0.message.content', '');
        
        $conteudo = trim($respostaText);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('A IA (OpenAI) retornou uma resposta em formato inesperado.');
        }

        return [
            'resumo'     => $dados['resumo']     ?? '',
            'sentimento' => $dados['sentimento'] ?? 'Neutro',
            'pdis'       => $dados['pdis']       ?? [],
        ];
    }

    public function gerarTextoJson(string $prompt): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API da OpenAI não configurada.');
        }

        $payload = [
            'model' => 'gpt-3.5-turbo', // ou gpt-4o-mini
            'messages' => [
                ['role' => 'system', 'content' => 'Você responde estritamente com JSON válido.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2
        ];

        $response = Http::timeout(60)
            ->withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}",
            ])
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$response->successful()) {
            $status = $response->status();
            $erro = $response->body();
            Log::warning("[OpenAiProvider] Falha HTTP {$status}: {$erro}");

            if ($status === 429 || str_contains(strtolower($erro), 'quota')) {
                throw new AiRateLimitException("OpenAI atingiu limite de requisições. Status: {$status}");
            }
            throw new \Exception("Erro na API da OpenAI: {$erro}");
        }

        $respostaText = $response->json('choices.0.message.content', '');
        
        $conteudo = trim($respostaText);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('A IA (OpenAI) retornou uma resposta em formato inesperado.');
        }

        return $dados;
    }
}
