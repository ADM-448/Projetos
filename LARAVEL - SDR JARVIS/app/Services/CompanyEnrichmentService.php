<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompanyEnrichmentService
{
    /**
     * Remove caracteres não numéricos do CNPJ.
     */
    public function sanitizeCnpj(string $cnpj): string
    {
        return preg_replace('/\D/', '', $cnpj);
    }

    /**
     * Formata um CNPJ limpo para 00.000.000/0000-00.
     */
    public function formatCnpj(string $cnpj): string
    {
        $clean = $this->sanitizeCnpj($cnpj);
        if (strlen($clean) !== 14) {
            return $cnpj;
        }

        return preg_replace(
            '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $clean
        );
    }

    /**
     * Consulta os dados cadastrais da empresa na Receita Federal via BrasilAPI ou ReceitaWS.
     */
    public function fetchByCnpj(string $rawCnpj): array
    {
        $cnpj = $this->sanitizeCnpj($rawCnpj);

        if (strlen($cnpj) !== 14) {
            return [
                'success' => false,
                'message' => 'O CNPJ informado deve conter exatamente 14 dígitos numéricos.',
            ];
        }

        // 1. Tenta consulta primária na BrasilAPI (rápida, gratuita e pública)
        try {
            $brasilApiUrl = "https://brasilapi.com.br/api/cnpj/v1/{$cnpj}";
            $response = Http::timeout(8)->get($brasilApiUrl);

            if ($response->successful()) {
                return $this->formatBrasilApiResponse($response->json(), $cnpj);
            }
        } catch (\Throwable $e) {
            Log::warning("CompanyEnrichmentService: Falha na BrasilAPI para CNPJ {$cnpj}, tentando ReceitaWS.", [
                'error' => $e->getMessage()
            ]);
        }

        // 2. Fallback secundário na ReceitaWS
        try {
            $receitaWsUrl = "https://receitaws.com.br/v1/cnpj/{$cnpj}";
            $response = Http::timeout(8)->get($receitaWsUrl);

            if ($response->successful() && ($response->json()['status'] ?? '') !== 'ERROR') {
                return $this->formatReceitaWsResponse($response->json(), $cnpj);
            }
        } catch (\Throwable $e) {
            Log::warning("CompanyEnrichmentService: Falha na ReceitaWS para CNPJ {$cnpj}.", [
                'error' => $e->getMessage()
            ]);
        }

        // 3. Fallback inteligente para CNPJs de demonstração / testes locais
        if ($this->isDemoOrTestCnpj($cnpj)) {
            return $this->generateMockEnrichmentData($cnpj);
        }

        return [
            'success' => false,
            'message' => 'Não foi possível localizar os dados deste CNPJ na base pública da Receita Federal. Verifique o número digitado.',
        ];
    }

    /**
     * Normaliza os dados retornados pela BrasilAPI.
     */
    protected function formatBrasilApiResponse(array $data, string $cnpj): array
    {
        $tradeName = !empty($data['nome_fantasia']) ? trim($data['nome_fantasia']) : null;
        $legalName = !empty($data['razao_social']) ? trim($data['razao_social']) : 'Empresa';
        $displayName = $tradeName ?: $legalName;

        // Processar Sócios e Administradores (QSA)
        $partners = [];
        $rawQsa = $data['qsa'] ?? [];
        foreach ($rawQsa as $item) {
            $partnerName = ucwords(mb_strtolower($item['nome_socio'] ?? $item['nome'] ?? ''));
            $partnerRole = $item['qualificacao_socio'] ?? $item['qualificacao_representante_legal'] ?? 'Sócio / Decisor';

            if ($partnerName) {
                $partners[] = [
                    'name' => $partnerName,
                    'job_title' => $this->cleanJobTitle($partnerRole),
                    'document' => $item['cnpj_cpf_do_socio'] ?? null,
                ];
            }
        }

        // Telefone
        $phone = null;
        if (!empty($data['ddd_telefone_1'])) {
            $phone = preg_replace('/\D/', '', $data['ddd_telefone_1']);
        }

        // Endereço formatado
        $address = [
            'street' => trim(($data['descricao_tipo_de_logradouro'] ?? '') . ' ' . ($data['logradouro'] ?? '')),
            'number' => $data['numero'] ?? 'S/N',
            'complement' => $data['complemento'] ?? '',
            'neighborhood' => $data['bairro'] ?? '',
            'city' => $data['municipio'] ?? '',
            'state' => $data['uf'] ?? '',
            'zip' => $data['cep'] ?? '',
        ];

        $fullAddress = trim("{$address['street']}, {$address['number']} " . ($address['complement'] ? "- {$address['complement']} " : '') . "- {$address['neighborhood']}, {$address['city']}/{$address['state']} - CEP {$address['zip']}", ' ,-');

        // Capital Social
        $capital = (float) ($data['capital_social'] ?? 0);
        $capitalFormatted = 'R$ ' . number_format($capital, 2, ',', '.');

        // Segmento / Atividade Principal
        $segment = $data['cnae_fiscal_descricao'] ?? 'Comércio / Serviços';

        return [
            'success' => true,
            'source' => 'BrasilAPI',
            'cnpj' => $this->formatCnpj($cnpj),
            'cnpj_raw' => $cnpj,
            'name' => $displayName,
            'trade_name' => $tradeName,
            'legal_name' => $legalName,
            'segment' => $segment,
            'status' => $data['descricao_situacao_cadastral'] ?? 'ATIVA',
            'opening_date' => $data['data_inicio_atividade'] ?? null,
            'capital_social' => $capital,
            'capital_social_formatted' => $capitalFormatted,
            'phone' => $phone,
            'email' => strtolower($data['email'] ?? ''),
            'address' => $address,
            'full_address' => $fullAddress,
            'partners' => $partners,
        ];
    }

    /**
     * Normaliza os dados retornados pela ReceitaWS.
     */
    protected function formatReceitaWsResponse(array $data, string $cnpj): array
    {
        $tradeName = !empty($data['fantasia']) ? trim($data['fantasia']) : null;
        $legalName = !empty($data['nome']) ? trim($data['nome']) : 'Empresa';
        $displayName = $tradeName ?: $legalName;

        $partners = [];
        $rawQsa = $data['qsa'] ?? [];
        foreach ($rawQsa as $item) {
            $partnerName = ucwords(mb_strtolower($item['nome'] ?? ''));
            $partnerRole = $item['qual'] ?? 'Sócio / Administrador';

            if ($partnerName) {
                $partners[] = [
                    'name' => $partnerName,
                    'job_title' => $this->cleanJobTitle($partnerRole),
                ];
            }
        }

        $phone = !empty($data['telefone']) ? preg_replace('/\D/', '', explode('/', $data['telefone'])[0]) : null;

        $address = [
            'street' => $data['logradouro'] ?? '',
            'number' => $data['numero'] ?? 'S/N',
            'complement' => $data['complemento'] ?? '',
            'neighborhood' => $data['bairro'] ?? '',
            'city' => $data['municipio'] ?? '',
            'state' => $data['uf'] ?? '',
            'zip' => $data['cep'] ?? '',
        ];

        $fullAddress = trim("{$address['street']}, {$address['number']} - {$address['neighborhood']}, {$address['city']}/{$address['state']} - CEP {$address['zip']}", ' ,-');

        $capital = (float) str_replace(['.', ','], ['', '.'], $data['capital_social'] ?? '0');
        $capitalFormatted = 'R$ ' . number_format($capital, 2, ',', '.');

        $primaryActivity = ($data['atividade_principal'][0]['text'] ?? null) ?: 'Comércio / Serviços';

        return [
            'success' => true,
            'source' => 'ReceitaWS',
            'cnpj' => $this->formatCnpj($cnpj),
            'cnpj_raw' => $cnpj,
            'name' => $displayName,
            'trade_name' => $tradeName,
            'legal_name' => $legalName,
            'segment' => $primaryActivity,
            'status' => $data['situacao'] ?? 'ATIVA',
            'opening_date' => $data['abertura'] ?? null,
            'capital_social' => $capital,
            'capital_social_formatted' => $capitalFormatted,
            'phone' => $phone,
            'email' => strtolower($data['email'] ?? ''),
            'address' => $address,
            'full_address' => $fullAddress,
            'partners' => $partners,
        ];
    }

    /**
     * Limpa o texto do cargo da Receita Federal para uma nomenclatura comercial elegante.
     */
    protected function cleanJobTitle(string $rawTitle): string
    {
        $raw = mb_strtolower(trim($rawTitle));

        if (str_contains($raw, 'administrador') || str_contains($raw, 'admin')) {
            return 'Sócio-Administrador';
        }
        if (str_contains($raw, 'diretor')) {
            return 'Diretor Executivo';
        }
        if (str_contains($raw, 'presidente')) {
            return 'Presidente / CEO';
        }
        if (str_contains($raw, 'gerente')) {
            return 'Gerente Geral';
        }
        if (str_contains($raw, 'sócio') || str_contains($raw, 'socio')) {
            return 'Sócio';
        }

        return ucwords($rawTitle);
    }

    /**
     * Verifica se é um CNPJ conhecido de demonstração ou fictício para testes locais.
     */
    protected function isDemoOrTestCnpj(string $cnpj): bool
    {
        $clean = $this->sanitizeCnpj($cnpj);
        return in_array($clean, [
            '00000000000191', // Banco do Brasil
            '33000167000101', // Petrobras
            '11111111111111',
            '12345678000199',
            '00000000000000',
        ]);
    }

    /**
     * Gera dados simulados caso a conexão externa falhe ou seja um CNPJ de teste.
     */
    protected function generateMockEnrichmentData(string $cnpj): array
    {
        return [
            'success' => true,
            'source' => 'Simulação Cadastral (Modo Teste)',
            'cnpj' => $this->formatCnpj($cnpj),
            'cnpj_raw' => $cnpj,
            'name' => 'Empresa Exemplo Inovação B2B',
            'trade_name' => 'Inovação Tech',
            'legal_name' => 'Inovação e Tecnologia em Logística S.A.',
            'segment' => 'Desenvolvimento de Softwares e Soluções Tecnológicas',
            'status' => 'ATIVA',
            'opening_date' => '15/03/2019',
            'capital_social' => 500000.00,
            'capital_social_formatted' => 'R$ 500.000,00',
            'phone' => '1130005000',
            'email' => 'contato@inovacaotech.com.br',
            'address' => [
                'street' => 'Avenida Paulista',
                'number' => '1000',
                'complement' => 'Andar 15',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01310-100',
            ],
            'full_address' => 'Avenida Paulista, 1000 - Andar 15 - Bela Vista, São Paulo/SP - CEP 01310-100',
            'partners' => [
                [
                    'name' => 'Renato Alcantara',
                    'job_title' => 'Sócio-Administrador',
                ],
                [
                    'name' => 'Beatriz Vasconcelos',
                    'job_title' => 'Diretora Executiva',
                ]
            ],
        ];
    }
}
