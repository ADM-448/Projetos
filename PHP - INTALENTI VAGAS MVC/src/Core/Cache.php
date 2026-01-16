<?php

namespace src\Core;

/**
 * CLASSE CACHE (Padrão: File System Cache)
 * 
 * O objetivo desta classe é ser um "Armário Temporário".
 * Ela NÃO sabe o que é banco de dados, nem quem é o usuário.
 * Ela apenas recebe um dado, guarda num arquivo, e devolve se a validade ainda estiver boa.
 * 
 * CONCEITO TÉCNICO DE PLENO:
 * - I/O de Disco (Cache em Arquivo) é mais lento que RAM (Redis/Memcached), mas
 *   MUITO mais rápido que uma conexão de rede com o Banco de Dados MySQL.
 * - Serialização: Transformar um Array/Objeto do PHP em texto puro para poder salvar em arquivo.
 */
class Cache
{
    // Define onde os arquivos de cache serão salvos (pasta 'cache' na raiz)
    // Ajustado para apontar corretamente da pasta src/Core para cache na raiz do projeto MVC
    private static $cacheFolder = __DIR__ . '/../../cache/';

    /**
     * Salva um valor no cache.
     * 
     * @param string $key   O nome da chave (ex: 'home_posts_page_1')
     * @param mixed  $data  O dado a ser salvo (pode ser array, string, int...)
     * @param int    $time  Tempo de vida em segundos (TTL - Time To Live)
     */
    public static function set($key, $data, $time = 60)
    {
        // 1. Garante que a pasta cache existe. Se não, cria.
        if (!is_dir(self::$cacheFolder)) {
            mkdir(self::$cacheFolder, 0777, true);
        }

        // 2. Define o nome do arquivo. Usamos md5 para garantir que caracteres especiais
        // na chave não quebrem o sistema de arquivos (ex: 'post/1' viraria pasta sem o md5).
        $filename = self::$cacheFolder . md5($key) . '.txt';

        // 3. Prepara o conteúdo.
        // Precisamos guardar DUAS coisas:
        // A) O dado em si.
        // B) Quando ele expira (Agora + Tempo de vida).
        $content = [
            'expires_at' => time() + $time,
            'payload'    => $data
        ];

        // 4. Serialização (serialize ou json_encode)
        // O PHP não consegue salvar um Array diretamente num arquivo de texto.
        // O 'serialize' transforma o array numa string especial que o PHP entende depois.
        $serializedContent = serialize($content);

        // 5. Escreve no disco.
        // file_put_contents é atômico na maioria dos casos (evita corrupção se dois tentarem escrever).
        file_put_contents($filename, $serializedContent);
    }

    /**
     * Tenta recuperar um valor do cache.
     * 
     * @param string $key O nome da chave
     * @return mixed|bool Retorna o dado se válido, ou FALSE se não existir/vencido.
     */
    public static function get($key)
    {
        $filename = self::$cacheFolder . md5($key) . '.txt';

        // 1. Verifica se o arquivo físico existe no HD.
        if (!file_exists($filename)) {
            return false; // Cache Miss (Não achou)
        }

        // 2. Lê o conteúdo do arquivo.
        // Essa operação é o gargalo de I/O, mas ainda é ms mais rápida que um SELECT complexo.
        $serializedContent = file_get_contents($filename);

        // 3. Deserializa (transforma texto de volta em Array PHP).
        $content = unserialize($serializedContent);

        // 4. Verifica a validade (TTL).
        // Se o tempo atual (time()) for maior que o tempo de expiração guardado...
        if (time() > $content['expires_at']) {
            // ...o cache venceu! Apagamos o arquivo para limpar sujeira e retornamos false.
            unlink($filename);
            return false; // Cache Expired
        }

        // 5. Se chegou aqui, o cache é fresco (Cache Hit). Retorna o dado original.
        return $content['payload'];
    }
}
