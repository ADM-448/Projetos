<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Models\User;

class AuthController extends Controller
{

    public function login()
    {
        // Se já estiver logado, manda pro Dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }

        // Renderiza a tela de login
        $this->render('Auth/login');
    }

    public function loginAction()
    {
        // Recebe os dados do POST
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');

        if ($email && $password) {
            $userModel = new User();
            $user = $userModel->findByEmail($email);

            // Verifica se usuário existe E se a senha bate com o Hash
            if ($user && password_verify($password, $user['password'])) {

                // --- VERIFICAÇÃO DE STATUS (NOVO) ---
                // Se for empresa/prefeitura (tipo 2), verifica se a empresa está ativa
                if ($user['tipo_usuario'] == 2) {
                    $empresaModel = new \src\Models\Empresa();
                    // Assumindo que o usuário tem vínculo com alguma empresa.
                    // Precisamos pegar a empresa vinculada.
                    $empresas = $empresaModel->getByUser($user['id']);

                    if (!empty($empresas)) {
                        // Verifica a primeira empresa (ou a principal).
                        // Se todas estiverem inativas, bloqueia.
                        $algumaAtiva = false;
                        foreach ($empresas as $emp) {
                            if (($emp['status'] ?? 1) == 1) {
                                $algumaAtiva = true;
                                break;
                            }
                        }

                        if (!$algumaAtiva) {
                            $_SESSION['flash'] = 'Acesso bloqueado. Sua empresa/conta está inativa. Contate o administrador.';
                            header('Location: ' . BASE_URL . 'auth/login');
                            exit;
                        }
                    }
                }

                // --- MULTI-TENANCY: ISOLAMENTO DE PORTAL ---
                // Verifica se o usuário pertence ao Portal Atual
                // Ignora para Admin (3) se quisermos que ele logue em qualquer lugar ou se for superadmin.
                if ($user['tipo_usuario'] != 3) {
                    $portalAtual = defined('PORTAL_ATUAL') ? PORTAL_ATUAL : 1;
                    if ($user['portal_id'] != $portalAtual) {
                        $_SESSION['flash'] = 'Cadastro não encontrado neste portal. Verifique se está na cidade correta.';
                        header('Location: ' . BASE_URL . 'auth/login');
                        exit;
                    }
                }

                // --- SUCESSO ---
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['tipo_usuario'] = $user['tipo_usuario']; // 1=Candidato, 2=Empresa, 3=Admin
                $_SESSION['portal_id'] = $user['portal_id'];

                if ($_SESSION['tipo_usuario'] == 1) { // Candidato
                    header('Location: ' . BASE_URL . 'vagas');
                } elseif ($_SESSION['tipo_usuario'] == 2) { // RH / Empresa
                    header('Location: ' . BASE_URL . 'dashboard');
                } else { // Admin
                    header('Location: ' . BASE_URL . 'admin/dashboard');
                }
                exit;
            } else {
                $_SESSION['flash'] = 'E-mail ou senha incorretos.';
            }
        } else {
            $_SESSION['flash'] = 'Preencha todos os campos.';
        }

        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    public function register()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        $this->render('Auth/register');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/register');
            exit;
        }

        $data = $_POST;
        $tipo_cadastro = $data['tipo_cadastro'] ?? '';
        $db = \src\Config\Database::getInstance();

        try {
            if ($tipo_cadastro === 'candidato') {
                $this->processCandidato($data, $db);
            } elseif ($tipo_cadastro === 'empregador') {
                $this->processEmpregador($data, $db);
            } else {
                $_SESSION['flash_error'] = "Tipo de cadastro inválido.";
                header('Location: ' . BASE_URL . 'auth/register');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erro no cadastro: " . $e->getMessage();
            header('Location: ' . BASE_URL . 'auth/register');
            exit;
        }
    }

    private function processCandidato($data, $db)
    {
        $name = filter_var($data['cand_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($data['cand_email'], FILTER_VALIDATE_EMAIL);
        $cpf = preg_replace('/[^0-9]/', '', $data['cpf']);
        $password = $data['cand_password'];
        $confirm_password = $data['cand_confirm_password'];

        if (!$this->validaCPF($cpf)) {
            throw new \Exception("CPF inválido.");
        }
        if ($password !== $confirm_password) {
            throw new \Exception("As senhas não conferem.");
        }

        // Verifica CPF duplicado (Raw e Formatted para legacy)
        $cpfFormatted = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf);
        $dupCpf = $db->selectOne("SELECT id FROM users WHERE cpf = :cpf OR cpf = :cpf_fmt", ['cpf' => $cpf, 'cpf_fmt' => $cpfFormatted]);
        if ($dupCpf) {
            throw new \Exception("Este CPF já está cadastrado.");
        }

        // Verifica Email duplicado
        $dupEmail = $db->selectOne("SELECT id FROM users WHERE email = :email", ['email' => $email]);
        if ($dupEmail) {
            throw new \Exception("Este e-mail já está cadastrado.");
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $userId = $db->insert('users', [
            'name' => $name,
            'email' => $email,
            'cpf' => $cpf,
            'password' => $password_hash,
            'tipo_usuario' => 1, // Candidato
            'portal_id' => defined('PORTAL_ATUAL') ? PORTAL_ATUAL : 1
        ]);

        // Cria registro na tabela candidatos
        $db->insert('candidatos', [
            'user_id' => $userId,
            'nome_completo' => $name, // Opcional, mantendo consistência
            'email' => $email,
            'cpf' => $cpf,
            'data_nascimento' => $data['cand_data_nascimento'] ?? null,
            'telefone' => $data['cand_telefone'] ?? '',
            'whatsapp' => $data['cand_whatsapp'] ?? ''
        ]);

        // TODO: Salvar idiomas e nacionalidade se necessário no futuro

        // Notificação de Boas-vindas / Preenchimento de Currículo
        $notificacaoModel = new \src\Models\Notificacao();
        $notificacaoModel->criar($userId, 'Bem-vindo! Para se candidatar, você precisa completar seu currículo.', BASE_URL . 'curriculo');

        $_SESSION['flash_success'] = "Cadastro realizado! Faça login."; // Precisa implementar flash success na view de login
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    private function processEmpregador($data, $db)
    {
        $codigo_convite = preg_replace('/[\s\t\n\r\0\x0B\xA0]/', '', $data['codigo_convite'] ?? '');
        $temConvite = !empty($codigo_convite);

        $rh_name = filter_var($data['rh_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $rh_email = filter_var($data['rh_email'], FILTER_VALIDATE_EMAIL);
        $rh_password = $data['rh_password'];
        $rh_confirm_password = $data['rh_confirm_password'];

        if ($rh_password !== $rh_confirm_password) {
            throw new \Exception("As senhas não conferem.");
        }

        // Duplicidade Email
        $dupEmail = $db->selectOne("SELECT id FROM users WHERE email = :email", ['email' => $rh_email]);
        if ($dupEmail) {
            throw new \Exception("Este e-mail já está em uso.");
        }

        try {
            $db->beginTransaction();

            $empresaId = null;
            $roleFinal = 'membro';
            $tipo_empresa = null; // Inicializa para evitar undefined variable quando usar convite
            $portalId = defined('PORTAL_ATUAL') ? PORTAL_ATUAL : 1; // Pega do contexto

            if ($temConvite) {
                // --- Fluxo COM Convite (Entra na equipe de alguém) ---
                $empresaModel = new \src\Models\Empresa();
                $invite = $empresaModel->getValidInvite($codigo_convite);

                if (!$invite) {
                    throw new \Exception("Código de convite inválido ou expirado.");
                }

                $empresaId = $invite['empresa_id'];
                $roleFinal = 'membro'; // Entra como membro

                // Queima convite único
                if ($invite['tipo'] == 'unico') {
                    $empresaModel->markInviteUsed($invite['id']);
                }
            } else {
                // --- Fluxo SEM Convite (Cria nova empresa) ---
                $cnpj = preg_replace('/[^0-9]/', '', $data['emp_cnpj']);
                $nome_empresa = filter_var($data['emp_nome_empresa'], FILTER_SANITIZE_SPECIAL_CHARS);
                // Novo campo Nome Fantasia
                $nome_fantasia = !empty($data['emp_nome_fantasia']) ? filter_var($data['emp_nome_fantasia'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
                $tipo_empresa = $data['emp_tipo'];

                if (!$this->validaCNPJ($cnpj)) {
                    throw new \Exception("CNPJ inválido.");
                }

                // Duplicidade CNPJ
                $cnpjFormatted = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cnpj);
                $dupCnpj = $db->selectOne("SELECT id FROM empresas WHERE cnpj = :cnpj OR cnpj = :cnpj_fmt", ['cnpj' => $cnpj, 'cnpj_fmt' => $cnpjFormatted]);
                if ($dupCnpj) {
                    throw new \Exception("Este CNPJ já está cadastrado.");
                }

                // Upload Logo (Opcional)
                $logoPath = null;
                if (isset($_FILES['emp_logo']) && $_FILES['emp_logo']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    $ext = strtolower(pathinfo($_FILES['emp_logo']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $uploadDir = 'uploads/logos/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                        $fileName = $cnpj . '.' . $ext;
                        if (move_uploaded_file($_FILES['emp_logo']['tmp_name'], $uploadDir . $fileName)) {
                            $logoPath = $fileName;
                        }
                    }
                }

                // Endereço (Opcional, busca via CEP no frontend ou controller)
                $cep = preg_replace('/[^0-9]/', '', $data['emp_cep'] ?? '');
                // (Implementar GeoUtils/Busca CEP aqui se quisesse backend, mas vamos salvar direto por agora)

                $empresaId = $db->insert('empresas', [
                    'nome_empresa' => $nome_empresa,
                    'nome_fantasia' => $nome_fantasia, // Salvando
                    'cnpj' => $cnpj,
                    'tipo' => $tipo_empresa,
                    'logo_path' => $logoPath,
                    'portal_id' => $portalId,
                    'cep' => $cep,
                    'logradouro' => $data['emp_logradouro'] ?? '',
                    'numero' => $data['emp_numero'] ?? '',
                    'complemento' => $data['emp_complemento'] ?? '',
                    'bairro' => $data['emp_bairro'] ?? '',
                    'cidade' => $data['emp_cidade'] ?? '',
                    'estado' => $data['emp_estado'] ?? '',
                    'status' => ($tipo_empresa === 'prefeitura') ? 0 : 1 // Prefeituras nascem inativas para validação
                ]);

                $roleFinal = 'admin'; // Criador é admin
            }

            // 2. Cria Usuário RH
            $userId = $db->insert('users', [
                'name' => $rh_name,
                'email' => $rh_email,
                'password' => password_hash($rh_password, PASSWORD_DEFAULT),
                'tipo_usuario' => 2, // RH
                'portal_id' => $portalId
            ]);

            $db->insert('empresa_user', [
                'user_id' => $userId,
                'empresa_id' => $empresaId,
                'role' => $roleFinal
            ]);

            $db->commit();

            if (!empty($tipo_empresa) && $tipo_empresa === 'prefeitura') {
                $_SESSION['flash_success'] = "Solicitação enviada! O cadastro de Prefeitura passará por análise de validação antes da liberação.";
            } else {
                $_SESSION['flash_success'] = "Cadastro realizado com sucesso! Faça login.";
            }
            $_SESSION['flash_force_login'] = true; // Flag opcional para UI

            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function forgot()
    {
        $this->render('Auth/forgot');
    }

    public function sendResetLink()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $_SESSION['flash_error'] = "E-mail inválido.";
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }

        $db = \src\Config\Database::getInstance();
        $user = $db->selectOne("SELECT id, name FROM users WHERE email = :email", ['email' => $email]);

        if (!$user) {
            $_SESSION['flash_error'] = "E-mail não encontrado.";
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }

        // Gera token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        try {
            // Salva token
            $db->insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'expires_at' => $expires
            ]);

            // Envia Email
            $link = BASE_URL . "auth/reset?token=" . $token;
            $assunto = "Recuperação de Senha - IntalenTI";
            $html = "<p>Olá, {$user['name']}.</p>
                     <p>Você solicitou a redefinição de sua senha.</p>
                     <p>Clique no link abaixo para criar uma nova senha (válido por 1 hora):</p>
                     <p><a href='$link' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Redefinir Senha</a></p>
                     <p>Se você não solicitou, ignore este e-mail.</p>";

            if (\src\Utils\Mailer::enviar($email, $user['name'], $assunto, $html)) {
                $_SESSION['flash_success'] = "Link de recuperação enviado para seu e-mail.";
            } else {
                $_SESSION['flash_error'] = "Erro ao enviar e-mail. Tente novamente mais tarde.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Erro ao processar solicitação.";
        }

        header('Location: ' . BASE_URL . 'auth/forgot');
        exit;
    }

    public function reset()
    {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            $_SESSION['flash_error'] = "Link inválido.";
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Verifica validade do token
        $db = \src\Config\Database::getInstance();
        $reset = $db->selectOne("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1", ['token' => $token]);

        if (!$reset) {
            $_SESSION['flash_error'] = "Link expirado ou inválido.";
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $this->render('Auth/reset', ['token' => $token]);
    }

    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $_SESSION['flash_error'] = "As senhas não conferem.";
            header('Location: ' . BASE_URL . 'auth/reset?token=' . $token);
            exit;
        }

        $db = \src\Config\Database::getInstance();

        // Revalida token
        $reset = $db->selectOne("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1", ['token' => $token]);

        if (!$reset) {
            $_SESSION['flash_error'] = "Token inválido ou expirado.";
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Atualiza senha
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->update('users', ['password' => $hash], 'email = :email', ['email' => $reset['email']]);

        // Remove tokens usados/antigos deste email
        $db->delete('password_resets', 'email = :email', ['email' => $reset['email']]);

        $_SESSION['flash_success'] = "Senha atualizada com sucesso! Faça login.";
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    private function validaCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    private function validaCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
