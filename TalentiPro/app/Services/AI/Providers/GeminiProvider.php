<?php

namespace App\Services\AI\Providers;

use App\Exceptions\AiRateLimitException;
use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    protected const MODELOS_FALLBACK = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-3.5-flash',
        'gemini-3.5-flash-lite',
        'gemini-flash-latest',
    ];

    protected const MAX_RETRIES_POR_MODELO = 1; // Reduzido pois o fallback do Manager fará a proteção principal
    protected const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
    }

    public function getName(): string
    {
        return 'Gemini (Google)';
    }

    public function transcreverAudio(string $audioContent, string $mimeType): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API do Gemini não configurada.');
        }

        $mimeType = $this->normalizarMimeType($mimeType);
        $base64Audio = base64_encode($audioContent);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => 'Você é um transcritor profissional de reuniões corporativas. '
                                    . 'Ouça o áudio fornecido e gere ESTRITAMENTE E APENAS a transcrição '
                                    . 'em português de tudo o que foi falado, sem comentários ou resumos. '
                                    . 'Se o áudio for inaudível ou não contiver voz humana, responda apenas: '
                                    . '[Áudio inaudível ou sem voz detectada].',
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data'     => $base64Audio,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resposta = $this->chamarGemini($payload, 150);

        Log::info('[GeminiProvider] Transcrição concluída.');

        return trim($resposta);
    }

    public function analisarTranscricao(string $transcricao, array $participantes): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API do Gemini não configurada.');
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
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        $respostaText = $this->chamarGemini($payload, 120);

        $conteudo = trim($respostaText);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('A IA (Gemini) retornou uma resposta em formato inesperado.');
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
            throw new \Exception('Chave da API do Gemini não configurada.');
        }

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        $respostaText = $this->chamarGemini($payload, 120);

        $conteudo = trim($respostaText);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('A IA (Gemini) retornou uma resposta em formato inesperado.');
        }

        return $dados;
    }

    protected function chamarGemini(array $payload, int $timeout = 120): string
    {
        $ultimoErro = '';
        $ultimoStatusCode = 200;

        foreach (self::MODELOS_FALLBACK as $modelo) {
            for ($tentativa = 1; $tentativa <= self::MAX_RETRIES_POR_MODELO; $tentativa++) {
                $url = self::BASE_URL . "/{$modelo}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::timeout($timeout)
                        ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $ultimoErro = "Timeout/Conexão (" . $e->getMessage() . ")";
                    Log::warning("[GeminiProvider] Timeout/Conexão no modelo {$modelo} (tentativa {$tentativa}): {$ultimoErro}. Tentando próximo modelo.");
                    break; // Pula imediatamente para o próximo modelo do fallback
                } catch (\Exception $e) {
                    $ultimoErro = $e->getMessage();
                    Log::warning("[GeminiProvider] Exceção no modelo {$modelo}: {$ultimoErro}. Tentando próximo.");
                    break;
                }

                if ($response->successful()) {
                    return $response->json('candidates.0.content.parts.0.text', '');
                }

                $ultimoStatusCode = $response->status();
                $ultimoErro  = $response->body();

                Log::warning("[GeminiProvider] Erro {$ultimoStatusCode} no modelo {$modelo}: " . substr($ultimoErro, 0, 300));

                if (in_array($ultimoStatusCode, [429, 503]) || str_contains(strtolower($ultimoErro), 'quota')) {
                    // Pula para o próximo modelo, caso a cota do modelo específico estoure
                    break;
                }
            }
        }

        // Se saiu do loop, todos falharam. Lança RateLimitException se for 429 ou cota
        if (in_array($ultimoStatusCode, [429, 503]) || str_contains(strtolower($ultimoErro), 'quota')) {
            throw new AiRateLimitException("Gemini atingiu o limite de cota/rate limit. Status: {$ultimoStatusCode}");
        }

        throw new \Exception("Erro na API do Gemini: {$ultimoErro}");
    }

    protected function normalizarMimeType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'video/')) {
            return str_replace('video/', 'audio/', $mimeType);
        }
        return $mimeType;
    }
}
