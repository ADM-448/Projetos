<?php

namespace App\Services\AI\Providers;

use App\Exceptions\AiNotSupportedException;
use App\Exceptions\AiRateLimitException;
use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekProvider implements AiProviderInterface
{
    protected const BASE_URL = 'https://api.deepseek.com/v1/chat/completions';

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.api_key') ?: env('DEEPSEEK_API_KEY', '');
    }

    public function getName(): string
    {
        return 'DeepSeek';
    }

    public function transcreverAudio(string $audioContent, string $mimeType): string
    {
        // DeepSeek não possui endpoint de transcrição de áudio aberto atualmente no plano gratuito/padrão.
        // Lançamos a exceção para que o Manager pule para o próximo (OpenAI Whisper).
        Log::info('[DeepSeekProvider] Transcrição de áudio solicitada, mas não é suportada nativamente. Pulando...');
        throw new AiNotSupportedException('DeepSeek não suporta transcrição de áudio (Speech-to-Text).');
    }

    public function analisarTranscricao(string $transcricao, array $participantes): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API do DeepSeek não configurada.');
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
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'Você responde estritamente com JSON válido.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2
        ];

        $respostaText = $this->chamarDeepSeekComRotacao($payload);
        
        $conteudo = trim($respostaText);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('A IA (DeepSeek) retornou uma resposta em formato inesperado.');
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
            throw new \Exception('Chave da API do DeepSeek não configurada.');
        }

        $payload = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'Você responde estritamente com JSON válido.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.2
        ];

        $respostaText = $this->chamarDeepSeekComRotacao($payload);
        
        $conteudo = trim($respostaText);
        $conteudo = preg_replace('/^```json\s*/i', '', $conteudo);
        $conteudo = preg_replace('/\s*```$/', '', $conteudo);

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('A IA (DeepSeek) retornou uma resposta em formato inesperado.');
        }

        return $dados;
    }

    /**
     * Tenta fazer a requisição rotacionando as chaves (separadas por vírgula)
     */
    protected function chamarDeepSeekComRotacao(array $payload): string
    {
        $keys = array_map('trim', explode(',', $this->apiKey));
        $ultimoErro = '';
        $ultimoStatus = 0;

        foreach ($keys as $index => $key) {
            if (empty($key)) continue;

            $numeroChave = $index + 1;
            Log::info("[DeepSeekProvider] Tentando requisição com a chave #{$numeroChave}");

            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type'  => 'application/json',
                    'Authorization' => "Bearer {$key}",
                ])
                ->post(self::BASE_URL, $payload);

            if ($response->successful()) {
                Log::info("[DeepSeekProvider] Sucesso com a chave #{$numeroChave}");
                return $response->json('choices.0.message.content', '');
            }

            $ultimoStatus = $response->status();
            $ultimoErro = $response->body();
            Log::warning("[DeepSeekProvider] Falha HTTP {$ultimoStatus} com chave #{$numeroChave}: {$ultimoErro}");

            // Se for limite de uso (429) ou falta de fundos (402), tenta a próxima chave.
            if ($ultimoStatus === 429 || $ultimoStatus === 402) {
                continue; 
            }

            // Se for outro erro (ex: 401 token inválido), também pula pra próxima
            if ($ultimoStatus === 401) {
                continue;
            }

            // Se for um erro do sistema da DeepSeek (500), tenta a próxima por garantia
        }

        // Se saiu do loop, TODAS as chaves falharam.
        if ($ultimoStatus === 429 || $ultimoStatus === 402) {
            throw new AiRateLimitException("DeepSeek atingiu limite de requisições ou fundos em TODAS as chaves disponíveis. Último Status: {$ultimoStatus}");
        }
        
        throw new \Exception("Erro na API do DeepSeek em todas as chaves: {$ultimoErro}");
    }
}
