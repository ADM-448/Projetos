<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiAudioService
 *
 * Responsável por toda a comunicação com a API do Gemini (Google AI).
 *
 * Estratégia:
 *  - Envia o áudio como inlineData (base64) — sem depender de File API.
 *  - Usa lista de fallback de modelos com retry automático.
 *  - Separa transcrição de áudio e análise de texto em métodos distintos
 *    para facilitar testes, manutenção e reutilização.
 */
class GeminiAudioService
{
    /**
     * Modelos tentados em ordem de preferência.
     * Caso o primeiro esteja sobrecarregado (503/429), tenta o próximo automaticamente.
     */
    protected const MODELOS_FALLBACK = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-3.5-flash',
        'gemini-3.5-flash-lite',
        'gemini-flash-latest',
    ];

    protected const MAX_RETRIES_POR_MODELO = 1;

    protected const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
    }

    // =========================================================================
    // MÉTODO PÚBLICO 1: Transcrição de Áudio
    // =========================================================================

    /**
     * Envia o conteúdo do áudio (bytes) para o Gemini e retorna a transcrição em texto.
     *
     * @param  string  $audioContent  Bytes do arquivo de áudio
     * @param  string  $mimeType      Ex: 'audio/webm', 'audio/mp4'
     * @return array  ['success' => true, 'transcricao' => '...'] ou ['success' => false, 'error' => '...']
     */
    public function transcreverAudio(string $audioContent, string $mimeType): array
    {
        if (empty($this->apiKey)) {
            Log::error('[GeminiAudioService] GEMINI_API_KEY não configurada no .env.');
            return ['success' => false, 'error' => 'Chave da API do Gemini não configurada. Contate o administrador.'];
        }

        // Ajuste: navegadores às vezes reportam video/webm mesmo para gravações de microfone
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

        $resposta = $this->chamarGemini($payload, timeout: 120);

        if (!$resposta['success']) {
            return $resposta;
        }

        $transcricao = trim($resposta['text']);

        Log::info('[GeminiAudioService] Transcrição concluída. Caracteres: ' . strlen($transcricao));

        return ['success' => true, 'transcricao' => $transcricao];
    }

    // =========================================================================
    // MÉTODO PÚBLICO 2: Análise da Transcrição → Resumo + PDIs sugeridos
    // =========================================================================

    /**
     * Recebe o texto transcrito da reunião e os participantes,
     * e retorna um resumo executivo + sugestões de PDI para cada participante.
     *
     * @param  string  $transcricao         Texto completo da reunião
     * @param  array   $participantes       Array ['id' => 'Nome Completo']
     * @return array   ['success' => true, 'resumo' => '...', 'pdis' => [...]] ou ['success' => false, 'error' => '...']
     */
    public function analisarTranscricao(string $transcricao, array $participantes): array
    {
        if (empty($this->apiKey)) {
            Log::error('[GeminiAudioService] GEMINI_API_KEY não configurada no .env.');
            return ['success' => false, 'error' => 'Chave da API do Gemini não configurada. Contate o administrador.'];
        }

        if (empty(trim($transcricao))) {
            return ['success' => false, 'error' => 'A transcrição está vazia. Não é possível analisar.'];
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

        $resposta = $this->chamarGemini($payload, timeout: 60);

        if (!$resposta['success']) {
            return $resposta;
        }

        // Limpa possíveis blocos de markdown que a IA às vezes inclui
        $conteudo = trim($resposta['text']);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('[GeminiAudioService] Falha ao parsear JSON de análise: ' . $conteudo);
            return ['success' => false, 'error' => 'A IA retornou uma resposta em formato inesperado. Tente novamente.'];
        }

        Log::info('[GeminiAudioService] Análise concluída. PDIs sugeridos: ' . count($dados['pdis'] ?? []));

        return [
            'success'    => true,
            'resumo'     => $dados['resumo']     ?? '',
            'sentimento' => $dados['sentimento'] ?? 'Neutro',
            'pdis'       => $dados['pdis']       ?? [],
        ];
    }

    // =========================================================================
    // MÉTODO PRIVADO: Executor HTTP com fallback de modelos e retry
    // =========================================================================

    /**
     * Executa a chamada HTTP ao Gemini com fallback de modelos e retry automático.
     *
     * @param  array  $payload  Corpo da requisição JSON
     * @param  int    $timeout  Timeout em segundos
     * @return array  ['success' => true, 'text' => '...'] ou ['success' => false, 'error' => '...']
     */
    protected function chamarGemini(array $payload, int $timeout = 150): array
    {
        $ultimoErro = '';

        foreach (self::MODELOS_FALLBACK as $modelo) {
            for ($tentativa = 1; $tentativa <= self::MAX_RETRIES_POR_MODELO; $tentativa++) {
                Log::info("[GeminiAudioService] Modelo: {$modelo} | Tentativa: {$tentativa}/" . self::MAX_RETRIES_POR_MODELO);

                $url = self::BASE_URL . "/{$modelo}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::timeout($timeout)
                        ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $ultimoErro = "Timeout/Conexão (" . $e->getMessage() . ")";
                    Log::warning("[GeminiAudioService] Timeout/Conexão no modelo {$modelo} (tentativa {$tentativa}): {$ultimoErro}. Tentando próximo modelo.");
                    break;
                } catch (\Exception $e) {
                    $ultimoErro = $e->getMessage();
                    Log::warning("[GeminiAudioService] Exceção no modelo {$modelo}: {$ultimoErro}. Tentando próximo.");
                    break;
                }

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text', '');
                    Log::info("[GeminiAudioService] Sucesso com modelo: {$modelo}");
                    return ['success' => true, 'text' => $text];
                }

                $statusCode  = $response->status();
                $ultimoErro  = $response->body();

                Log::warning("[GeminiAudioService] Erro {$statusCode} no modelo {$modelo} (tentativa {$tentativa}): " . substr($ultimoErro, 0, 500));

                // Para 503 (sobrecarga) e 429 (rate limit), aguarda e tenta novamente
                if (in_array($statusCode, [429, 503])) {
                    $espera = 3 * $tentativa;
                    Log::info("[GeminiAudioService] Aguardando {$espera}s antes de tentar novamente...");
                    sleep($espera);
                    continue;
                }

                // Para outros erros (400, 404, etc.), pula para o próximo modelo
                break;
            }
        }

        // Mensagem amigável para cota excedida
        if (str_contains($ultimoErro, '429') || str_contains(strtolower($ultimoErro), 'quota')) {
            return [
                'success' => false,
                'error'   => 'A cota gratuita da API de IA foi atingida temporariamente. '
                           . 'Por favor, aguarde alguns minutos e tente novamente. '
                           . 'Se o problema persistir, o administrador deve verificar o plano de faturamento no Google Cloud.',
            ];
        }

        Log::error('[GeminiAudioService] Todos os modelos falharam. Último erro: ' . $ultimoErro);

        return [
            'success' => false,
            'error'   => 'O serviço de IA está temporariamente indisponível. Todos os modelos falharam. Tente novamente em alguns minutos.',
        ];
    }

    // =========================================================================
    // MÉTODO PRIVADO: Normalização de MIME Type
    // =========================================================================

    /**
     * Garante que o mimeType enviado ao Gemini seja 'audio/*' e não 'video/*',
     * pois gravações de microfone via MediaRecorder podem chegar como video/webm.
     */
    protected function normalizarMimeType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'video/')) {
            return str_replace('video/', 'audio/', $mimeType);
        }

        return $mimeType;
    }
}
