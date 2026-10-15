<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiTextService
 *
 * Responsável por requisições de IA textuais ao Gemini (Avaliações, Clima, etc.).
 * Retorna JSONs estruturados prontos para virarem PDIs.
 */
class GeminiTextService
{
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

    /**
     * Gera um PDI para um colaborador com base nas notas (<=3) de sua Avaliação.
     */
    public function gerarPdiAvaliacao(string $dadosCompilados, string $nomeColaborador): array
    {
        if (empty($this->apiKey)) {
            Log::error('[GeminiTextService] GEMINI_API_KEY não configurada no .env.');
            return ['success' => false, 'error' => 'Chave da API não configurada.'];
        }

        $dataAtual = now()->format('d/m/Y');
        $prompt = "Você é um consultor especialista de RH e Desenvolvimento Humano.\n\n"
                . "O colaborador {$nomeColaborador} recebeu notas baixas (<=3) em algumas competências na última avaliação.\n"
                . "COMPETÊNCIAS A MELHORAR E COMENTÁRIOS:\n\"\"\"\n{$dadosCompilados}\n\"\"\"\n\n"
                . "Sua tarefa é criar um Plano de Desenvolvimento Individual (PDI) focado exatamente nestes gaps.\n"
                . "Considere que a data atual é: {$dataAtual}.\n"
                . "Retorne ESTRITAMENTE um JSON válido no formato abaixo, sem markdown ou textos extras:\n"
                . "{\n"
                . "  \"titulo\": \"Título do PDI\",\n"
                . "  \"descricao\": \"Propósito geral deste PDI justificando a importância da melhoria\",\n"
                . "  \"prazo\": \"YYYY-MM-DD\" (calcule um prazo realista entre 30 a 90 dias a partir da data atual),\n"
                . "  \"acoes\": [\n"
                . "    {\n"
                . "      \"titulo\": \"Ação ou meta específica\",\n"
                . "      \"descricao\": \"Detalhes de como executar\"\n"
                . "    }\n"
                . "  ]\n"
                . "}";

        return $this->enviarPromptParaJson($prompt, 'PDI Avaliacao');
    }

    /**
     * Gera um PDI Organizacional com base nos resultados da Pesquisa de Clima.
     */
    public function gerarPdiClima(string $dadosCompilados): array
    {
        if (empty($this->apiKey)) {
            Log::error('[GeminiTextService] GEMINI_API_KEY não configurada no .env.');
            return ['success' => false, 'error' => 'Chave da API não configurada.'];
        }

        $dataAtual = now()->format('d/m/Y');
        $prompt = "Você é um Consultor Estratégico de RH.\n\n"
                . "Uma Pesquisa de Clima acabou de ser finalizada. Abaixo estão as médias das dimensões e alguns comentários anônimos.\n"
                . "RESULTADOS DA PESQUISA:\n\"\"\"\n{$dadosCompilados}\n\"\"\"\n\n"
                . "Sua tarefa é analisar os principais problemas (notas baixas ou reclamações recorrentes) e criar um "
                . "Plano de Ação Organizacional estruturado como um PDI voltado para a Empresa (RH/Gestão).\n"
                . "Considere que a data atual é: {$dataAtual}.\n"
                . "Retorne ESTRITAMENTE um JSON válido no formato abaixo, sem markdown ou textos extras:\n"
                . "{\n"
                . "  \"titulo\": \"Título do Plano de Ação Organizacional\",\n"
                . "  \"descricao\": \"Diagnóstico geral e propósito deste plano\",\n"
                . "  \"prazo\": \"YYYY-MM-DD\" (calcule um prazo realista entre 60 a 120 dias a partir da data atual),\n"
                . "  \"acoes\": [\n"
                . "    {\n"
                . "      \"titulo\": \"Iniciativa Organizacional (ex: Novo programa de feedbacks)\",\n"
                . "      \"descricao\": \"Passos práticos de implementação para a gestão\"\n"
                . "    }\n"
                . "  ]\n"
                . "}";

        return $this->enviarPromptParaJson($prompt, 'PDI Clima');
    }

    /**
     * Reutiliza a lógica de envio e parse do JSON.
     */
    protected function enviarPromptParaJson(string $prompt, string $contexto): array
    {
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

        $conteudo = trim($resposta['text']);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("[GeminiTextService - {$contexto}] Falha ao parsear JSON: " . $conteudo);
            return ['success' => false, 'error' => 'A IA retornou uma resposta em formato inesperado.'];
        }

        return [
            'success' => true,
            'dados'   => $dados,
        ];
    }

    /**
     * Executa a chamada HTTP ao Gemini com fallback de modelos e retry.
     */
    protected function chamarGemini(array $payload, int $timeout = 120): array
    {
        $ultimoErro = '';

        foreach (self::MODELOS_FALLBACK as $modelo) {
            for ($tentativa = 1; $tentativa <= self::MAX_RETRIES_POR_MODELO; $tentativa++) {
                Log::info("[GeminiTextService] Modelo: {$modelo} | Tentativa: {$tentativa}/" . self::MAX_RETRIES_POR_MODELO);

                $url = self::BASE_URL . "/{$modelo}:generateContent?key={$this->apiKey}";

                try {
                    $response = Http::timeout($timeout)
                        ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $ultimoErro = "Timeout/Conexão (" . $e->getMessage() . ")";
                    Log::warning("[GeminiTextService] Timeout/Conexão no modelo {$modelo} (tentativa {$tentativa}): {$ultimoErro}. Tentando próximo modelo.");
                    break;
                } catch (\Exception $e) {
                    $ultimoErro = $e->getMessage();
                    Log::warning("[GeminiTextService] Exceção no modelo {$modelo}: {$ultimoErro}. Tentando próximo.");
                    break;
                }

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text', '');
                    Log::info("[GeminiTextService] Sucesso com modelo: {$modelo}");
                    return ['success' => true, 'text' => $text];
                }

                $statusCode  = $response->status();
                $ultimoErro  = $response->body();

                Log::warning("[GeminiTextService] Erro {$statusCode} no modelo {$modelo} (tentativa {$tentativa}): " . substr($ultimoErro, 0, 500));

                if (in_array($statusCode, [429, 503])) {
                    $espera = 2 * $tentativa;
                    sleep($espera);
                    continue;
                }

                break;
            }
        }

        if (str_contains($ultimoErro, '429') || str_contains(strtolower($ultimoErro), 'quota')) {
            return [
                'success' => false,
                'error'   => 'A cota gratuita da API de IA foi atingida. Aguarde e tente novamente.',
            ];
        }

        Log::error('[GeminiTextService] Todos os modelos falharam. Erro: ' . $ultimoErro);

        return [
            'success' => false,
            'error'   => 'O serviço de IA está temporariamente indisponível.',
        ];
    }
}
