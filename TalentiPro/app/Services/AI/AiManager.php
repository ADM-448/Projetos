<?php

namespace App\Services\AI;

use App\Exceptions\AiNotSupportedException;
use App\Exceptions\AiRateLimitException;
use Exception;
use Illuminate\Support\Facades\Log;

class AiManager
{
    /**
     * @var AiProviderInterface[]
     */
    protected array $providers = [];

    /**
     * Construtor injeta as estratégias de IA.
     * @param AiProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Tenta transcrever o áudio usando a fila de provedores.
     */
    public function transcreverAudio(string $audioContent, string $mimeType): array
    {
        $ultimoErro = '';

        foreach ($this->providers as $provider) {
            $nome = $provider->getName();
            Log::info("[AiManager] Tentando transcrever áudio com: {$nome}");

            try {
                $transcricao = $provider->transcreverAudio($audioContent, $mimeType);
                Log::info("[AiManager] Transcrição concluída com sucesso usando: {$nome}");
                return ['success' => true, 'transcricao' => $transcricao, 'provider' => $nome];
            } catch (AiNotSupportedException $e) {
                // Se o provedor não suporta áudio (ex: DeepSeek), loga e pula para o próximo
                Log::info("[AiManager] Provedor {$nome} não suporta a operação de áudio. Tentando o próximo.");
                continue;
            } catch (AiRateLimitException $e) {
                // Rate limit: tenta o próximo
                $ultimoErro = $e->getMessage();
                Log::warning("[AiManager] Fallback ativado: {$nome} estourou o limite de requisições. Mensagem: {$ultimoErro}");
                continue;
            } catch (Exception $e) {
                // Outros erros genéricos: também tenta o próximo para garantir resiliência
                $ultimoErro = $e->getMessage();
                Log::error("[AiManager] Erro no provedor {$nome}: {$ultimoErro}. Tentando o próximo.");
                continue;
            }
        }

        // Se chegou aqui, todos os provedores falharam ou não suportaram
        Log::error('[AiManager] Falha geral: Todos os provedores de IA falharam na transcrição de áudio.');
        return [
            'success' => false,
            'error'   => 'Não foi possível transcrever o áudio no momento devido a falhas ou indisponibilidade em nossos servidores de Inteligência Artificial. Tente novamente mais tarde.',
            'last_error' => $ultimoErro
        ];
    }

    /**
     * Tenta analisar a transcrição usando a fila de provedores.
     */
    public function analisarTranscricao(string $transcricao, array $participantes): array
    {
        $ultimoErro = '';

        foreach ($this->providers as $provider) {
            $nome = $provider->getName();
            Log::info("[AiManager] Tentando analisar transcrição com: {$nome}");

            try {
                $dados = $provider->analisarTranscricao($transcricao, $participantes);
                $dados['success'] = true;
                $dados['provider'] = $nome;
                Log::info("[AiManager] Análise de transcrição concluída com sucesso usando: {$nome}");
                return $dados;
            } catch (AiRateLimitException $e) {
                // Rate limit: tenta o próximo
                $ultimoErro = $e->getMessage();
                Log::warning("[AiManager] Fallback ativado: {$nome} estourou o limite de requisições. Mensagem: {$ultimoErro}");
                continue;
            } catch (Exception $e) {
                // Outros erros genéricos: tenta o próximo
                $ultimoErro = $e->getMessage();
                Log::error("[AiManager] Erro no provedor {$nome}: {$ultimoErro}. Tentando o próximo.");
                continue;
            }
        }

        Log::error('[AiManager] Falha geral: Todos os provedores de IA falharam na análise da transcrição.');
        return [
            'success' => false,
            'error'   => 'Não foi possível processar a transcrição no momento devido à indisponibilidade de nossos servidores de Inteligência Artificial. Tente novamente mais tarde.',
            'last_error' => $ultimoErro
        ];
    }

    /**
     * Tenta gerar PDI de avaliação usando a fila de provedores.
     */
    public function gerarPdiAvaliacao(string $dadosCompilados, string $nomeColaborador): array
    {
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

        return $this->executarGeracaoTextoJson($prompt, 'PDI Avaliacao');
    }

    /**
     * Tenta gerar PDI Organizacional (Clima) usando a fila de provedores.
     */
    public function gerarPdiClima(string $dadosCompilados): array
    {
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

        return $this->executarGeracaoTextoJson($prompt, 'PDI Clima');
    }

    /**
     * Helper privado para rodar a fila de provedores enviando um prompt JSON.
     */
    protected function executarGeracaoTextoJson(string $prompt, string $contexto): array
    {
        $ultimoErro = '';

        foreach ($this->providers as $provider) {
            $nome = $provider->getName();
            Log::info("[AiManager - {$contexto}] Tentando gerar texto JSON com: {$nome}");

            try {
                $dados = $provider->gerarTextoJson($prompt);
                Log::info("[AiManager - {$contexto}] Geração concluída com sucesso usando: {$nome}");
                return [
                    'success' => true,
                    'dados'   => $dados,
                    'provider' => $nome
                ];
            } catch (AiRateLimitException $e) {
                // Rate limit: tenta o próximo
                $ultimoErro = $e->getMessage();
                Log::warning("[AiManager - {$contexto}] Fallback ativado: {$nome} estourou o limite de requisições. Mensagem: {$ultimoErro}");
                continue;
            } catch (Exception $e) {
                // Outros erros genéricos: tenta o próximo
                $ultimoErro = $e->getMessage();
                Log::error("[AiManager - {$contexto}] Erro no provedor {$nome}: {$ultimoErro}. Tentando o próximo.");
                continue;
            }
        }

        Log::error("[AiManager - {$contexto}] Falha geral: Todos os provedores de IA falharam na geração de JSON.");
        return [
            'success' => false,
            'error'   => 'Não foi possível processar a solicitação no momento devido à indisponibilidade de nossos servidores de Inteligência Artificial. Tente novamente mais tarde.',
            'last_error' => $ultimoErro
        ];
    }
}
