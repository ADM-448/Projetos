<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\Vaga;
use src\Config\Database;

class VagasController extends Controller
{
    public function index()
    {
        // 1. Busca Cidades Disponíveis (para o filtro)
        $db = Database::getInstance();
        $portalId = defined('PORTAL_ATUAL') ? PORTAL_ATUAL : 1;


        // Select distinct locais apenas de vagas ativas
        $locaisQuery = "SELECT DISTINCT localizacao 
                        FROM vagas 
                        WHERE portal_id = :pid 
                        AND localizacao IS NOT NULL 
                        AND localizacao != '' 
                        ORDER BY localizacao ASC";

        $locaisDisponiveis = $db->query($locaisQuery, ['pid' => $portalId])->fetchAll(\PDO::FETCH_COLUMN);

        // 2. Busca Vagas com Filtros
        $busca = $_GET['busca'] ?? '';
        $localizacao = $_GET['localizacao'] ?? '';
        $ramo = $_GET['ramo'] ?? '';

        $params = [':portal_id' => $portalId];
        $conditions = ["v.portal_id = :portal_id"]; // Removido status pois coluna não existe

        if (!empty($busca)) {
            // Refatorado para Full-Text Search (Escalabilidade) e Boolean Mode
            // MATCH evita Full Table Scan que derrubaria o banco com muitos usuários

            /* 
               --- JEITO VELHO (Lento com muitos dados) ---
               O banco tinha que ler linha por linha para achar o texto no meio.
               $conditions[] = "(
                   v.titulo LIKE :busca 
                   OR v.descricao LIKE :busca 
                   OR (e.nome_empresa LIKE :busca AND v.confidencial = 0)
               )";
               $params[':busca'] = '%' . $busca . '%';
            */

            // --- JEITO NOVO (Alta Performance) ---
            // Usa Índice Invertido para ir direto ao resultado.
            $conditions[] = "(
                MATCH(v.titulo, v.descricao) AGAINST(:busca IN BOOLEAN MODE) 
                OR (MATCH(e.nome_empresa) AGAINST(:busca IN BOOLEAN MODE) AND v.confidencial = 0)
            )";

            // Adiciona * para permitir busca parcial (ex: 'program' encontra 'programador')
            // Tratamento simples: substitui espaços por * também para buscar todas as palavras
            $termo = trim($busca);
            // Remove caracteres que podem quebrar a sintaxe booleana do MySQL se não tratados
            $termo = preg_replace('/[+\-><()~\"@]+/', ' ', $termo);
            $params[':busca'] = str_replace(' ', '* ', $termo) . '*';
        }

        if (!empty($localizacao)) {
            $conditions[] = "v.localizacao LIKE :localizacao";
            $params[':localizacao'] = '%' . $localizacao . '%';
        }

        if (!empty($ramo)) {
            $conditions[] = "v.ramo_atividade LIKE :ramo";
            $params[':ramo'] = '%' . $ramo . '%';
        }

        $whereClause = implode(" AND ", $conditions);

        // LEFT JOIN para permitir vagas sem empresa (se houver essa regra) ou INNER JOIN se obrigatório
        // No legado é JOIN normal
        $query = "SELECT v.*, e.nome_empresa, e.logo_path, e.tipo as empresa_tipo
                  FROM vagas v
                  JOIN empresas e ON v.empresa_id = e.id
                  WHERE {$whereClause}
                  ORDER BY v.created_at DESC";

        $vagas = $db->query($query, $params)->fetchAll();

        // 3. Ramos de Atividade (Estático conforme legado)
        $ramos = [
            'Tecnologia, TI, Informática',
            'Saúde e Medicina',
            'Educação e Ensino',
            'Engenharia (Civil, Elétrica, Mecânica, etc.)',
            'Administração e Finanças',
            'Recursos Humanos e Gestão de Pessoas',
            'Marketing e Comunicação',
            'Varejo e Comércio',
            'Logística e Transportes',
            'Indústria e Manufatura',
            'Agricultura e Agronegócio',
            'Serviços Gerais e Manutenção',
            'Outros'
        ];
        sort($ramos);

        // 4. (NOVO) Se for candidato, marcar quais vagas já se candidatou
        if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] == 1 && isset($_SESSION['user_id'])) {
            $candidato = $db->selectOne("SELECT id FROM candidatos WHERE user_id = :uid", ['uid' => $_SESSION['user_id']]);
            if ($candidato) {
                $candidaturas = $db->query("SELECT vaga_id, data_candidatura FROM candidaturas WHERE candidato_id = :cid", ['cid' => $candidato['id']])->fetchAll();

                $mapCandidaturas = [];
                foreach ($candidaturas as $c) {
                    $mapCandidaturas[$c['vaga_id']] = $c['data_candidatura'];
                }

                foreach ($vagas as &$v) {
                    if (isset($mapCandidaturas[$v['id']])) {
                        $v['data_candidatura'] = $mapCandidaturas[$v['id']];
                    }
                }
            }
        }

        $this->renderTemplate('Vagas/index', [
            'vagas' => $vagas,
            'locais_disponiveis' => $locaisDisponiveis,
            'ramos_de_atividade' => $ramos,
            'busca_realizada' => (!empty($busca) || !empty($localizacao) || !empty($ramo))
        ]);
    }

    public function show($id)
    {
        $db = Database::getInstance();
        $vaga = $db->selectOne("
            SELECT v.*, e.nome_empresa, e.logo_path, e.tipo as empresa_tipo, e.id as empresa_id
            FROM vagas v
            JOIN empresas e ON v.empresa_id = e.id
            WHERE v.id = :id
        ", ['id' => $id]);

        if (!$vaga) {
            $_SESSION['msg'] = "Vaga não encontrada.";
            header('Location: ' . BASE_URL . 'vagas');
            exit;
        }

        // Busca Perguntas
        $perguntas = $db->select("SELECT id, texto_pergunta, eliminatoria FROM perguntas_vaga WHERE vaga_id = :id", ['id' => $id]);

        // Verifica se já candidatou
        $ja_candidatado = false;

        // Verifica Sessão e Tipo
        $userId = $_SESSION['user_id'] ?? null;
        $userType = $_SESSION['tipo_usuario'] ?? $_SESSION['user_type'] ?? null;

        if ($userId && $userType == 1) { // 1 = Candidato
            $candidato = $db->selectOne("SELECT id FROM candidatos WHERE user_id = :uid", ['uid' => $userId]);

            if ($candidato) {
                // Correct column name is vaga_id
                $check = $db->selectOne("SELECT id FROM candidaturas WHERE vaga_id = :vid AND candidato_id = :cid", [
                    'vid' => $id,
                    'cid' => $candidato['id']
                ]);

                if ($check) {
                    $ja_candidatado = true;
                    $candidaturaId = $check['id'];

                    // Verificar Chat existente
                    $chat = $db->selectOne("SELECT id FROM chats WHERE candidatura_id = :cid", ['cid' => $candidaturaId]);
                    if ($chat) {
                        $chatId = $chat['id'];
                        // Verifica se tem mensagens para regra de bloqueio
                        $msgCount = $db->selectOne("SELECT COUNT(id) as total FROM mensagens_chat WHERE chat_id = :cid", ['cid' => $chatId]);
                        $chat_tem_mensagens = ($msgCount['total'] > 0);
                    }
                }
            }
        }

        // --- Verificação de Perfil Completo (Regra de Negócio) ---
        $perfil_completo = false;
        if ($userId && $userType == 1 && isset($candidato)) {
            // Lista de campos obrigatórios (Até 'sobre_mim' + dados pessoais/contato)
            // Nota: 'nome_completo' e 'cpf' podem estar na tabela users ou candidatos, dependendo da implementação. 
            // Assumindo que $candidato vem de SELECT * FROM candidatos.
            // Precisamos garantir que pegamos os dados atualizados.

            $candidatoFull = $db->selectOne("SELECT * FROM candidatos WHERE id = :id", ['id' => $candidato['id']]);

            // Buscar nome do usuario tambem se necessario
            // $user = $db->selectOne("SELECT name, email FROM users WHERE id = :uid", ['uid' => $userId]);

            $camposObrigatorios = [
                'data_nascimento',
                'telefone',
                'cep',
                'cidade',
                'estado',
                'sobre_mim'
            ];

            $perfil_completo = true;
            foreach ($camposObrigatorios as $campo) {
                if (empty($candidatoFull[$campo])) {
                    $perfil_completo = false;
                    break;
                }
            }
        } elseif ($userId && $userType != 1) {
            // Se não for candidato, tecnicamente "não se aplica", mas para a view não quebrar:
            $perfil_completo = true;
        }

        $this->renderTemplate('Vagas/detalhe', [
            'vaga' => $vaga,
            'perguntas' => $perguntas,
            'ja_candidatado' => $ja_candidatado,
            'candidatura_id' => $candidaturaId ?? null,
            'chat_id' => $chatId ?? null,
            'chat_tem_mensagens' => $chat_tem_mensagens ?? false,
            'msg' => $_SESSION['msg'] ?? null,
            'msg_error' => $_SESSION['msg_error'] ?? null,
            'perfil_completo' => $perfil_completo ?? false,
            'title' => $vaga['titulo']
        ]);

        // Limpar mensagens após exibir
        if (isset($_SESSION['msg'])) unset($_SESSION['msg']);
        if (isset($_SESSION['msg_error'])) unset($_SESSION['msg_error']);
    }

    public function candidatos($id)
    {
        // 1. Verificação de Acesso
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 2) {
            $_SESSION['msg_error'] = "Acesso restrito a recrutadores.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        $vagaId = filter_var($id, FILTER_VALIDATE_INT);
        if (!$vagaId) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $db = Database::getInstance();

        // 2. Verificar se a vaga pertence ao recrutador logado ou se é admin da empresa
        $vaga = $db->selectOne("SELECT id, titulo, recrutador_id, empresa_id, localizacao, latitude, longitude FROM vagas WHERE id = :id", ['id' => $vagaId]);

        if (!$vaga) {
            $_SESSION['msg_error'] = "Vaga não encontrada.";
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        $empresaModel = new \src\Models\Empresa();
        $isOwner = ($vaga['recrutador_id'] == $_SESSION['user_id']);
        $isAdmin = false;

        if (!empty($vaga['empresa_id'])) {
            $isAdmin = ($empresaModel->getUserRole($_SESSION['user_id'], $vaga['empresa_id']) === 'admin');
        }

        if (!$isOwner && !$isAdmin) {
            $_SESSION['msg_error'] = "Acesso não autorizado.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // 3. Buscar Candidatos com Ordenação
        $ordem = $_GET['ordem'] ?? 'recentes';

        $candidaturaModel = new \src\Models\Candidatura();

        // Passa lat/long e ordem
        $candidatos = $candidaturaModel->obterPorVaga(
            $vagaId,
            $ordem,
            $vaga['latitude'],
            $vaga['longitude']
        );

        // 4. Render
        $this->renderTemplate('Vagas/candidatos', [
            'vaga' => $vaga,
            'candidatos' => $candidatos,
            'ordem_atual' => $ordem,
            'title' => 'Candidatos - ' . $vaga['titulo']
        ]);
    }
    public function create()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 2) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $db = Database::getInstance();

        // Empresas do usuário
        $empresas = $db->select(
            "SELECT e.id, e.nome_empresa 
             FROM empresas e 
             JOIN empresa_user eu ON e.id = eu.empresa_id 
             WHERE eu.user_id = :uid",
            ['uid' => $_SESSION['user_id']]
        );

        // Se não tiver empresa cadastrada, avisa mas permite acessar (o form vai estar vazio no select)
        // Ou idealmente redireciona. Vou manter simples.

        $ramos_de_atividade = [
            'Tecnologia, TI, Informática',
            'Saúde e Medicina',
            'Educação e Ensino',
            'Engenharia (Civil, Elétrica, Mecânica, etc.)',
            'Administração e Finanças',
            'Recursos Humanos e Gestão de Pessoas',
            'Marketing e Comunicação',
            'Varejo e Comércio',
            'Logística e Transportes',
            'Indústria e Manufatura',
            'Agricultura e Agronegócio',
            'Serviços Gerais e Manutenção',
            'Outros'
        ];
        sort($ramos_de_atividade);

        // Atualizado conforme solicitação (Lista mesclada com imagem + Jovem Aprendiz + 1º Emprego)
        $tipos_contrato = [
            'Efetivo' => 'Efetivo (CLT)',
            'PJ' => 'Pessoa Jurídica (PJ)',
            'Jovem Aprendiz' => 'Jovem Aprendiz',
            'Estágio' => 'Estágio',
            '1º Emprego' => '1º Emprego',
            'Trainee' => 'Trainee',
            'Temporário' => 'Temporário',
            'Freelancer' => 'Freelancer',
            'Terceiro' => 'Terceiro',
            'Banco de Talentos' => 'Banco de Talentos',
            'Voluntário' => 'Voluntário',
            'Sócio' => 'Sócio',
            'Summer job' => 'Summer job',
            'Intermitente' => 'Intermitente'
        ];


        $this->renderTemplate('Vagas/create', [
            'empresas' => $empresas,
            'ramos_de_atividade' => $ramos_de_atividade,
            'tipos_contrato' => $tipos_contrato, // Novo
            'title' => 'Criar Nova Vaga'
        ]);
    }

    public function store()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 2) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance();

            // Validação básica
            if (empty($_POST['titulo']) || empty($_POST['empresa_id'])) {
                $_SESSION['msg_error'] = "Preencha os campos obrigatórios.";
                header('Location: ' . BASE_URL . 'vagas/create');
                exit;
            }

            // Verifica se empresa pertence ao usuário
            $empresaUser = $db->selectOne(
                "SELECT id FROM empresa_user WHERE user_id = :uid AND empresa_id = :eid",
                ['uid' => $_SESSION['user_id'], 'eid' => $_POST['empresa_id']]
            );

            if (!$empresaUser) {
                $_SESSION['msg_error'] = "Empresa inválida.";
                header('Location: ' . BASE_URL . 'vagas/create');
                exit;
            }

            $tipoVaga = isset($_POST['tipo_vaga']) ? implode(',', $_POST['tipo_vaga']) : '';
            $latitude = null;
            $longitude = null;

            // GeoUtils
            if (!empty($_POST['vaga_cep'])) {
                $geo = \src\Utils\GeoUtils::validateCEP($_POST['vaga_cep']);
                if ($geo && !empty($geo['latitude'])) {
                    $latitude = $geo['latitude'];
                    $longitude = $geo['longitude'];
                }
            }

            $dados = [
                'recrutador_id' => $_SESSION['user_id'],
                'empresa_id' => $_POST['empresa_id'],
                'portal_id' => defined('PORTAL_ATUAL') ? PORTAL_ATUAL : 1,
                'titulo' => $_POST['titulo'],
                'descricao' => $_POST['descricao'],
                'requisitos' => $_POST['requisitos'] ?? '',
                'beneficios' => $_POST['beneficios'] ?? '',
                'experiencia' => $_POST['experiencia'] ?? 'indiferente',
                'faixa_salarial_min' => $this->formatarMoedaParaBanco($_POST['faixa_salarial_min'] ?? ''),
                'faixa_salarial_max' => $this->formatarMoedaParaBanco($_POST['faixa_salarial_max'] ?? ''),
                'salario_a_combinar' => isset($_POST['salario_a_combinar']) ? 1 : 0,
                'tipo_contrato' => isset($_POST['tipo_contrato']) ? implode(',', (array)$_POST['tipo_contrato']) : '',
                'modalidade' => $_POST['modalidade'] ?? 'Presencial',
                'tipo_vaga' => '', // Campo depreciado
                'localizacao' => $_POST['localizacao'],
                'cep' => $_POST['vaga_cep'] ?? '',
                'ramo_atividade' => $_POST['ramo_atividade'] ?? '',
                'confidencial' => !empty($_POST['confidencial']) ? 1 : 0,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert Vaga
            if ($db->insert('vagas', $dados)) {
                $vagaId = $db->lastInsertId();

                // Insert Perguntas
                if (isset($_POST['perguntas']) && is_array($_POST['perguntas'])) {
                    foreach ($_POST['perguntas'] as $pData) {
                        if (is_array($pData)) {
                            $texto = $pData['texto'] ?? '';
                            $eliminatoria = !empty($pData['eliminatoria']) ? 1 : 0;
                        } else {
                            $texto = $pData;
                            $eliminatoria = 0;
                        }

                        if (!empty($texto)) {
                            $db->insert('perguntas_vaga', [
                                'vaga_id' => $vagaId,
                                'texto_pergunta' => $texto,
                                'eliminatoria' => $eliminatoria
                            ]);
                        }
                    }
                }

                $_SESSION['msg'] = "Vaga criada com sucesso!";
                header('Location: ' . BASE_URL . 'dashboard/vagas');
                exit;
            } else {
                $_SESSION['msg_error'] = "Erro ao criar vaga.";
                header('Location: ' . BASE_URL . 'vagas/create');
                exit;
            }
        }
    }

    public function delete($id = null)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 2) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Fallback robusto para POST
        if (!$id) {
            $id = $_POST['id'] ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        }

        if (!$id) {
            $_SESSION['msg_error'] = "ID da vaga não fornecido.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        $db = Database::getInstance();
        $vaga = $db->selectOne("SELECT id, recrutador_id, empresa_id FROM vagas WHERE id = :id", ['id' => $id]);

        if (!$vaga) {
            $_SESSION['msg_error'] = "Vaga não encontrada.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // Verifica permissão (Dono ou Admin da Empresa)
        $empresaModel = new \src\Models\Empresa();
        $isOwner = ($vaga['recrutador_id'] == $_SESSION['user_id']);
        $isAdmin = false;

        if (!empty($vaga['empresa_id'])) {
            $isAdmin = ($empresaModel->getUserRole($_SESSION['user_id'], $vaga['empresa_id']) === 'admin');
        }

        if (!$isOwner && !$isAdmin) {
            $_SESSION['msg_error'] = "Vaga não encontrada ou acesso negado.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // DELEÇÃO COM TRANSAÇÃO PARA DEPENDÊNCIAS
        try {
            $pdo = $db->getConnection();
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
            }

            // 1. Deletar Perguntas
            $pdo->prepare("DELETE FROM perguntas_vaga WHERE vaga_id = :id")->execute([':id' => $id]);

            // 2. Deletar Mensagens e Chats associados às candidaturas desta vaga
            $pdo->prepare("DELETE FROM mensagens_chat WHERE chat_id IN (SELECT id FROM chats WHERE candidatura_id IN (SELECT id FROM candidaturas WHERE vaga_id = :id))")->execute([':id' => $id]);
            $pdo->prepare("DELETE FROM chats WHERE candidatura_id IN (SELECT id FROM candidaturas WHERE vaga_id = :id)")->execute([':id' => $id]);

            // 3. Deletar Candidaturas
            $pdo->prepare("DELETE FROM candidaturas WHERE vaga_id = :id")->execute([':id' => $id]);

            // 4. Deletar a Vaga
            $pdo->prepare("DELETE FROM vagas WHERE id = :id")->execute([':id' => $id]);

            $pdo->commit();

            $_SESSION['msg'] = "Vaga excluída com sucesso!";
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Log real
            file_put_contents(__DIR__ . '/../../debug_vaga_delete.log', date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['msg_error'] = "Erro ao excluir vaga (existem dados vinculados).";
        }

        header('Location: ' . BASE_URL . 'dashboard/vagas');
        exit;
    }

    public function edit($id = null)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 2) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if (!$id) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        $db = Database::getInstance();
        $vaga = $db->selectOne("SELECT * FROM vagas WHERE id = :id", ['id' => $id]);

        if (!$vaga) {
            $_SESSION['msg_error'] = "Vaga não encontrada.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // Verifica permissão (Dono ou Admin da Empresa)
        $empresaModel = new \src\Models\Empresa();
        $isOwner = ($vaga['recrutador_id'] == $_SESSION['user_id']);
        $isAdmin = false;

        if (!empty($vaga['empresa_id'])) {
            $isAdmin = ($empresaModel->getUserRole($_SESSION['user_id'], $vaga['empresa_id']) === 'admin');
        }

        if (!$isOwner && !$isAdmin) {
            $_SESSION['msg_error'] = "Acesso negado.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
            exit;
        }

        // Dados Auxiliares para a View
        // Corrigido para buscar via tabela de relacionamento empresa_user
        $empresas = $db->select(
            "SELECT e.id, e.nome_empresa 
                                 FROM empresas e 
                                 JOIN empresa_user eu ON e.id = eu.empresa_id 
                                 WHERE eu.user_id = :uid",
            ['uid' => $_SESSION['user_id']]
        );

        $ramos_de_atividade = [
            'Tecnologia, TI, Informática',
            'Saúde e Medicina',
            'Educação e Ensino',
            'Engenharia (Civil, Elétrica, Mecânica, etc.)',
            'Administração e Finanças',
            'Recursos Humanos e Gestão de Pessoas',
            'Marketing e Comunicação',
            'Varejo e Comércio',
            'Logística e Transportes',
            'Indústria e Manufatura',
            'Agricultura e Agronegócio',
            'Serviços Gerais e Manutenção',
            'Outros'
        ];
        sort($ramos_de_atividade);

        $tipos_contrato = [
            'Efetivo' => 'Efetivo (CLT)',
            'PJ' => 'Pessoa Jurídica (PJ)',
            'Jovem Aprendiz' => 'Jovem Aprendiz',
            'Estágio' => 'Estágio',
            '1º Emprego' => '1º Emprego',
            'Trainee' => 'Trainee',
            'Temporário' => 'Temporário',
            'Freelancer' => 'Freelancer',
            'Terceiro' => 'Terceiro',
            'Banco de Talentos' => 'Banco de Talentos',
            'Voluntário' => 'Voluntário',
            'Sócio' => 'Sócio',
            'Summer job' => 'Summer job',
            'Intermitente' => 'Intermitente'
        ];

        $perguntas = $db->select("SELECT * FROM perguntas_vaga WHERE vaga_id = :id", ['id' => $id]);

        $this->renderTemplate('Vagas/edit', [
            'vaga' => $vaga,
            'perguntas' => $perguntas,
            'empresas' => $empresas,
            'ramos_de_atividade' => $ramos_de_atividade,
            'tipos_contrato' => $tipos_contrato,
            'title' => 'Editar Vaga'
        ]);
    }

    public function update($id = null)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? $_SESSION['user_type']) != 2) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Fallback para POST ID
            if (!$id && isset($_POST['id'])) {
                $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            }

            if (!$id) {
                $_SESSION['msg_error'] = "ID da vaga não fornecido.";
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
                exit;
            }

            $db = Database::getInstance();

            // Verifica permissão
            $vaga = $db->selectOne("SELECT id, recrutador_id, empresa_id FROM vagas WHERE id = :id", ['id' => $id]);
            if (!$vaga) {
                $_SESSION['msg_error'] = "Vaga não encontrada.";
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
                exit;
            }

            $empresaModel = new \src\Models\Empresa();
            $isOwner = ($vaga['recrutador_id'] == $_SESSION['user_id']);
            $isAdmin = false;

            if (!empty($vaga['empresa_id'])) {
                $isAdmin = ($empresaModel->getUserRole($_SESSION['user_id'], $vaga['empresa_id']) === 'admin');
            }

            if (!$isOwner && !$isAdmin) {
                $_SESSION['msg_error'] = "Acesso negado. Você não tem permissão para editar esta vaga.";
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'dashboard'));
                exit;
            }

            // Campos básicos
            $tipoVaga = isset($_POST['tipo_vaga']) ? implode(',', $_POST['tipo_vaga']) : '';

            $dados = [
                'empresa_id' => $_POST['empresa_id'] ?? null,
                'titulo' => $_POST['titulo'],
                'descricao' => $_POST['descricao'],
                'requisitos' => $_POST['requisitos'] ?? '',
                'beneficios' => $_POST['beneficios'] ?? '',
                'experiencia' => $_POST['experiencia'] ?? 'indiferente',
                'experiencia' => $_POST['experiencia'] ?? 'indiferente',
                'faixa_salarial_min' => $this->formatarMoedaParaBanco($_POST['faixa_salarial_min'] ?? ''),
                'faixa_salarial_max' => $this->formatarMoedaParaBanco($_POST['faixa_salarial_max'] ?? ''),
                'salario_a_combinar' => isset($_POST['salario_a_combinar']) ? 1 : 0,
                'tipo_contrato' => isset($_POST['tipo_contrato']) ? implode(',', (array)$_POST['tipo_contrato']) : '',
                'modalidade' => $_POST['modalidade'] ?? 'Presencial',
                'tipo_vaga' => '', // Campo depreciado
                'localizacao' => $_POST['localizacao'],
                'cep' => $_POST['vaga_cep'] ?? '',
                'ramo_atividade' => $_POST['ramo_atividade'] ?? '',
                'confidencial' => !empty($_POST['confidencial']) ? 1 : 0
            ];

            // GeoCoding (Se mudou localizacao/cep, validar)
            if (!empty($dados['cep'])) {
                $geo = \src\Utils\GeoUtils::validateCEP($dados['cep']);
                if ($geo && !empty($geo['latitude'])) {
                    $dados['latitude'] = $geo['latitude'];
                    $dados['longitude'] = $geo['longitude'];
                }
            }

            $db->update('vagas', $dados, 'id = :id', ['id' => $id]);

            // Perguntas: SEMPRE deleta as antigas primeiro (cobre o caso de remover todas)
            $db->delete('perguntas_vaga', 'vaga_id = :vid', ['vid' => $id]);

            // Só recria se houver perguntas enviadas no POST
            if (isset($_POST['perguntas']) && is_array($_POST['perguntas'])) {
                foreach ($_POST['perguntas'] as $pData) {
                    // Se for array (formato novo [texto=>..., eliminatoria=>...])
                    if (is_array($pData)) {
                        $texto = $pData['texto'] ?? '';
                        $eliminatoria = !empty($pData['eliminatoria']) ? 1 : 0;
                    } else {
                        // Formato antigo/simples (apenas texto)
                        $texto = $pData;
                        $eliminatoria = 0;
                    }

                    if (!empty($texto)) {
                        $db->insert('perguntas_vaga', [
                            'vaga_id' => $id,
                            'texto_pergunta' => $texto,
                            'eliminatoria' => $eliminatoria
                        ]);
                    }
                }
            }

            $_SESSION['msg'] = "Vaga atualizada com sucesso!";
            header('Location: ' . BASE_URL . 'dashboard/vagas');
            exit;
        }
    }

    private function formatarMoedaParaBanco($valor)
    {
        if (empty($valor)) return 0;
        // Remove R$, espaços e pontos de milhar
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        // Troca vírgula decimal por ponto
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }
}
