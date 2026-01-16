<?php

namespace src\Config;

use PDO;
use PDOException;

class Database
{
    // Credenciais agora são carregadas do .env via construtor

    // guarda a conexão PDO real
    private $pdo;

    // PATTERN SINGLETON:
    // Esta variável estática vai guardar a ÚNICA instância da classe Database que existirá na memória.
    // Inicialmente é nula.
    private static $instance = null;

    // CONSTRUTOR PRIVADO (Private)
    // Ao mudar para private, bloqueamos o uso de "new Database()" fora desta classe.
    // Isso garante que ninguém crie novas conexões acidentalmente.
    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $username, $password);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Performance e Segurança (usar prepare nativo)
            //produção:             $this->pdo->query("SET SESSION sql_mode=''");
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    // MÉTODO MÁGICO CLONE PRIVADO
    // Impede que alguém faça $db2 = clone $db1; (o que geraria uma cópia da conexão)
    private function __clone() {}

    /**
     * PONTO DE ACESSO GLOBAL (Static)
     * Este é o único jeito de pegar o objeto Database agora.
     * 
     * Como funciona:
     * 1. Verifica se já existe uma instância guardada em self::$instance.
     * 2. Se NÃO existir (primeira vez que chama), ele cria (new self()) e guarda.
     * 3. Se JÁ existir, ele ignora a criação e só retorna a que já estava lá.
     * 
     * Resultado: Só uma conexão com o banco é aberta por requisição, economizando memória e CPU.
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Executa uma query genérica (Prepared Statement)
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Erro na query: " . $e->getMessage());
        }
    }

    /**
     * Alias for query (for backward compatibility or mistake fixing)
     */
    public function execute($sql, $params = [])
    {
        // Alias for query to fix potential legacy calls
        return $this->query($sql, $params);
    }

    /**
     * Seleciona registros
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function select($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Seleciona apenas um registro
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function selectOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Seleciona registros filtrando automaticamente pelo portal atual (Multi-tenancy Safe)
     * @param string $table Nome da tabela
     * @param string|array $where Condições WHERE opcionais (ex: 'ativo = 1')
     * @param array $params Parâmetros para o WHERE
     * @param string $columns Colunas para selecionar
     * @return array
     */
    public function selectByPortal($table, $where = '', $params = [], $columns = '*')
    {
        // Garante que PORTAL_ATUAL esteja definido (segurança)
        if (!defined('PORTAL_ATUAL')) {
            die("Erro Crítico: Tentativa de acesso sem contexto de portal definido.");
        }

        $portalId = PORTAL_ATUAL;

        // Monta a query base
        $sql = "SELECT {$columns} FROM {$table} WHERE portal_id = :portal_id_safe_auto";

        // Adiciona condicional extra se houver
        if (!empty($where)) {
            $sql .= " AND ({$where})";
        }

        // Adiciona o ID do portal aos parâmetros (usando chave única para evitar colisão)
        $params['portal_id_safe_auto'] = $portalId;

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Insere dados em uma tabela
     * @param string $table
     * @param array $data ['coluna' => 'valor']
     * @return string|false ID do último registro inserido
     */
    public function insert($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        // Criar placeholders no formato :coluna
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Atualiza dados de uma tabela
     * @param string $table
     * @param array $data ['coluna' => 'valor']
     * @param string $where Condição WHERE (ex: 'id = :id')
     * @param array $whereParams Parâmetros da condição (ex: ['id' => 1]) - Use chaves diferentes das chaves de $data se houver conflito, ou confie que não haverá.
     * @return int Número de linhas afetadas
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $fields = "";
        foreach ($data as $key => $value) {
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ", ");

        $sql = "UPDATE {$table} SET {$fields} WHERE {$where}";

        $params = array_merge($data, $whereParams);

        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Deleta registros de uma tabela
     * @param string $table
     * @param string $where Condição WHERE (ex: 'id = :id')
     * @param array $params Parâmetros da condição
     * @return int Número de linhas afetadas
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Retorna o ID do último registro inserido
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Executa um UPDATE com SQL bruto (Raw)
     */
    public function updateRaw($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Retorna a conexão PDO real para operações avançadas (transações)
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
}
