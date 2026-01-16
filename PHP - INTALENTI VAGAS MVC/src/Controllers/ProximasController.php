<?php

namespace src\Controllers;

use src\Core\Controller;
use src\Config\Database;

class ProximasController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['tipo_usuario'] ?? 0) != 1) {
            $_SESSION['msg'] = "Acesso negado. Faça login como candidato.";
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index()
    {
        $db = Database::getInstance();
        $user_id = $_SESSION['user_id'];
        $portal_id_atual = $_SESSION['portal_id'] ?? 1;

        // Se o usuário clicou em "Recarregar", limpa as vagas ignoradas na sessão
        if (!empty($_GET['reset'])) {
            unset($_SESSION['vagas_ignoradas']);
            // Redireciona para limpar o parâmetro da URL
            header('Location: ' . BASE_URL . 'proximas');
            exit;
        }

        // Busca ID do Candidato e Coordenadas
        $candidato = $db->selectOne("SELECT id, latitude, longitude, cidade, estado FROM candidatos WHERE user_id = :uid", ['uid' => $user_id]);

        // VERIFICAÇÃO
        if (!$candidato || empty($candidato['latitude']) || empty($candidato['longitude'])) {
            $_SESSION['msg_error'] = "Por favor, preencha seu endereço com CEP no 'Meu Currículo' para encontrar vagas próximas.";
            header('Location: ' . BASE_URL . 'curriculo');
            exit;
        }

        $candidato_id = $candidato['id'];
        $cand_lat = $candidato['latitude'];
        $cand_lng = $candidato['longitude'];
        $raio_km = 150;
        $vagas_proximas = [];

        // OTIMIZAÇÃO: Bounding Box
        // Pegamos um quadrado ao redor do usuário para filtrar grosso usando INDEX.
        // A matemática pesada (Haversine) roda só nos candidatos pré-selecionados.
        $box = \src\Utils\GeoUtils::getBoundingBox($cand_lat, $cand_lng, $raio_km);

        // --- LÓGICA DE "PULAR" (SWIPE LEFT) ---
        $ids_ignorados_sql = "";
        if (!empty($_SESSION['vagas_ignoradas'])) {
            $lista_ids = implode(',', array_map('intval', $_SESSION['vagas_ignoradas']));
            $ids_ignorados_sql = "AND v.id NOT IN ($lista_ids)";
        }

        // QUERY DE BUSCA
        $sql = "SELECT v.id, v.titulo, v.latitude, v.longitude, v.confidencial,
               v.faixa_salarial_min, v.localizacao, v.modalidade,
               e.nome_empresa, e.logo_path,
            (
                6371 * acos(
                    LEAST(1.0, GREATEST(-1.0, 
                        cos(radians(:cand_lat)) * cos(radians(v.latitude)) *
                        cos(radians(v.longitude) - radians(:cand_lng)) +
                        sin(radians(:cand_lat)) * sin(radians(v.latitude))
                    ))
                )
            ) AS distancia_km
        FROM vagas v
        LEFT JOIN empresas e ON v.empresa_id = e.id
        WHERE 
            v.portal_id = :portal_id 
            
            -- OTIMIZAÇÃO: Pré-filtro (Usa Index)
            AND v.latitude BETWEEN :min_lat AND :max_lat
            AND v.longitude BETWEEN :min_lng AND :max_lng
            
            -- Filtra vagas que o candidato JÁ aplicou
            AND v.id NOT IN (SELECT vaga_id FROM candidaturas WHERE candidato_id = :cid)
            
            -- Filtra vagas que o candidato PULOU
            $ids_ignorados_sql

        HAVING 
            distancia_km IS NOT NULL    
            AND distancia_km < :raio      
        ORDER BY 
            distancia_km ASC              
        LIMIT 50";

        try {
            $vagas_proximas = $db->select($sql, [
                'cand_lat' => $cand_lat,
                'cand_lng' => $cand_lng,
                'min_lat' => $box['min_lat'],
                'max_lat' => $box['max_lat'],
                'min_lng' => $box['min_lng'],
                'max_lng' => $box['max_lng'],
                'portal_id' => $portal_id_atual,
                'cid' => $candidato_id,
                'raio' => $raio_km
            ]);
        } catch (\PDOException $e) {
            // Registra o erro para debugging no log do servidor
            error_log('[ProximasController] Erro de SQL ao buscar vagas proximas: ' . $e->getMessage());
            $_SESSION['msg_error'] = 'Ocorreu um erro ao buscar as vagas próximas. Tente novamente em instantes.';
        }

        $this->renderTemplate('Vagas/proximas', [
            'vagas_proximas' => $vagas_proximas,
            'vagas_json' => json_encode($vagas_proximas, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
            'candidato' => $candidato,
            'raio_km' => $raio_km,
            'title' => 'Vagas Próximas'
        ]);
    }

    public function ignorar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error']);
            exit;
        }

        // Simula o POST se não vier direto (as vezes o framework PHP purga $_POST em PUT/JSON)
        // Mas aqui é form-data padrão
        $vaga_id = filter_input(INPUT_POST, 'vaga_id', FILTER_VALIDATE_INT);
        if ($vaga_id) {
            if (!isset($_SESSION['vagas_ignoradas'])) {
                $_SESSION['vagas_ignoradas'] = [];
            }
            $_SESSION['vagas_ignoradas'][] = $vaga_id;
            echo json_encode(['status' => 'ignored', 'id' => $vaga_id]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'ID invalido no POST']);
        }
        exit;
    }
}
