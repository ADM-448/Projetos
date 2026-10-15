<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    /**
     * Retorna o nome do provedor para logs (ex: 'gemini', 'openai', 'deepseek').
     */
    public function getName(): string;

    /**
     * Realiza a transcrição de um arquivo de áudio.
     * @param string $audioContent Conteúdo do arquivo em bytes/base64
     * @param string $mimeType Ex: 'audio/webm'
     * @return string O texto transcrito
     * @throws \App\Exceptions\AiRateLimitException
     * @throws \App\Exceptions\AiNotSupportedException Se o provedor não suportar áudio
     * @throws \Exception
     */
    public function transcreverAudio(string $audioContent, string $mimeType): string;

    /**
     * Analisa a transcrição e retorna um array contendo resumo, sentimento e PDIs sugeridos.
     * @param string $transcricao Texto da reunião
     * @param array $participantes Array ['id' => 'Nome']
     * @return array Formato: ['resumo' => '...', 'sentimento' => '...', 'pdis' => [...]]
     * @throws \App\Exceptions\AiRateLimitException
     * @throws \Exception
     */
    public function analisarTranscricao(string $transcricao, array $participantes): array;

    /**
     * Envia um prompt genérico para a IA e espera um JSON de resposta.
     * Usado para gerar PDIs de Avaliações e de Clima.
     * @param string $prompt
     * @return array Array associativo parseado do JSON retornado pela IA
     * @throws \App\Exceptions\AiRateLimitException
     * @throws \Exception
     */
    public function gerarTextoJson(string $prompt): array;
}
