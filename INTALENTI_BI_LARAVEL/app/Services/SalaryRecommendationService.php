<?php

namespace App\Services;

class SalaryRecommendationService
{
    public const SITUACAO_CONFORME = 'conforme';
    public const SITUACAO_ABAIXO = 'abaixo_tabela';
    public const SITUACAO_ACIMA = 'acima_teto';
    public const SITUACAO_SEM_DADOS = 'sem_dados';

    /**
     * Dicionário base de normalização de cargos informais/fantasia para CBO Oficial.
     */
    protected array $cboDictionary = [
        'faz tudo comercial' => [
            'cbo' => '3541-25',
            'cargo_real' => 'Assistente de Vendas',
            'descricao' => 'Presta suporte ao setor comercial, emissão de propostas, cadastro de clientes e atendimento a pedidos.',
        ],
        'analista ninja' => [
            'cbo' => '3171-10',
            'cargo_real' => 'Analista de Sistemas / Desenvolvedor',
            'descricao' => 'Desenvolve, analisa e implanta sistemas informatizados dimensionando requisitos e funcionalidades.',
        ],
        'atendente multiuso' => [
            'cbo' => '4221-05',
            'cargo_real' => 'Recepcionista Geral',
            'descricao' => 'Recepciona e presta serviços de apoio a clientes, atende chamadas telefônicas e gerencia fluxo de correspondências.',
        ],
        'faz tudo' => [
            'cbo' => '5143-20',
            'cargo_real' => 'Auxiliar de Serviços Gerais',
            'descricao' => 'Executa serviços de limpeza, conservação e apoio operacional em instalações prediais e comerciais.',
        ],
        'encarregado geral' => [
            'cbo' => '4101-05',
            'cargo_real' => 'Supervisor de Operações / Administrativo',
            'descricao' => 'Supervisiona a rotina de equipes administrativas e operacionais, acompanhando prazos e metas da unidade.',
        ],
    ];

    /**
     * Analisa a situação salarial do colaborador em relação à tabela de mercado ou convenção (CCT).
     *
     * @param float $salarioAtual
     * @param float|null $pisoOuMinimo
     * @param float|null $tetoOuMaximo
     * @param float|null $mediana
     * @return array
     */
    public function analisarSituacao(
        float $salarioAtual,
        ?float $pisoOuMinimo,
        ?float $tetoOuMaximo,
        ?float $mediana = null
    ): array {
        if ($pisoOuMinimo === null && $tetoOuMaximo === null && $mediana === null) {
            return [
                'status' => self::SITUACAO_SEM_DADOS,
                'status_label' => 'Sem Parâmetro de Mercado',
                'badge_color' => 'gray',
                'recomendacao' => 'Cadastrar convenção coletiva ou realizar pesquisa de mercado para esta função.',
                'diretriz_diretoria' => 'Coletar referências regionais antes de qualquer alteração salarial.',
                'diferenca' => 0,
                'percentual_desvio' => 0,
            ];
        }

        // Se houver apenas piso definido (ex: convenção sindical com piso mínimo)
        $piso = $pisoOuMinimo ?? ($mediana ? $mediana * 0.85 : $salarioAtual);
        $teto = $tetoOuMaximo ?? ($mediana ? $mediana * 1.35 : $piso * 1.50);

        // Caso 1: Abaixo da Tabela / Piso
        if ($salarioAtual < $piso) {
            $dif = $piso - $salarioAtual;
            $perc = ($piso > 0) ? round(($dif / $piso) * 100, 1) : 0;

            return [
                'status' => self::SITUACAO_ABAIXO,
                'status_label' => 'Abaixo da Tabela',
                'badge_color' => 'red',
                'recomendacao' => 'Recomenda-se o reajuste salarial no momento da revisão sindical (dissídio coletivo) da categoria para mitigar passivos trabalhistas.',
                'diretriz_diretoria' => 'Planejar provisionamento orçamentário para adequação na próxima data-base do sindicato.',
                'diferenca' => -$dif,
                'percentual_desvio' => -$perc,
            ];
        }

        // Caso 2: Acima do Teto Salarial (Anomalia)
        if ($salarioAtual > $teto) {
            $dif = $salarioAtual - $teto;
            $perc = ($teto > 0) ? round(($dif / $teto) * 100, 1) : 0;

            return [
                'status' => self::SITUACAO_ACIMA,
                'status_label' => 'Acima do Teto (Anomalia)',
                'badge_color' => 'amber',
                'recomendacao' => 'Salário significativamente acima do teto de mercado. Orientação à diretoria: congelar aumentos nos próximos ciclos e estruturar transição para uma nova função/cargo de maior senioridade e responsabilidade compatível com a remuneração.',
                'diretriz_diretoria' => 'Não efetuar aumentos nos meses seguintes. Desenvolver plano de carreira/transição de função para o profissional.',
                'diferenca' => $dif,
                'percentual_desvio' => $perc,
            ];
        }

        // Caso 3: Em Conformidade
        return [
            'status' => self::SITUACAO_CONFORME,
            'status_label' => 'Em Conformidade',
            'badge_color' => 'emerald',
            'recomendacao' => 'Ordenado compatível e alinhado com as práticas de mercado. Não se recomenda qualquer alteração neste momento.',
            'diretriz_diretoria' => 'Manter política salarial estável.',
            'diferenca' => 0,
            'percentual_desvio' => 0,
        ];
    }

    /**
     * Identifica e normaliza nomenclaturas informais ou incorretas do cliente para refletir o cargo real e CBO.
     *
     * @param string $cargoInformado
     * @return array
     */
    public function normalizarCargoECBO(string $cargoInformado): array
    {
        $cargoLimpo = mb_strtolower(trim($cargoInformado));
        
        // Verifica no dicionário de cargos informais conhecidos
        if (isset($this->cboDictionary[$cargoLimpo])) {
            $info = $this->cboDictionary[$cargoLimpo];
            return [
                'cargo_informado' => $cargoInformado,
                'cargo_real' => $info['cargo_real'],
                'cbo' => $info['cbo'],
                'descricao' => $info['descricao'],
                'foi_corrigido' => true,
                'aviso_ajuste' => "Nomenclatura interna ajustada para refletir a CBO oficial ({$info['cbo']}) e evitar desvio funcional.",
            ];
        }

        // Caso não haja match exato informal, retorna o cargo original formatado
        return [
            'cargo_informado' => $cargoInformado,
            'cargo_real' => mb_convert_case($cargoInformado, MB_CASE_TITLE, 'UTF-8'),
            'cbo' => 'Sob Análise CBO',
            'descricao' => 'Atividades técnicas e operacionais inerentes à função desempenhada na estrutura da empresa.',
            'foi_corrigido' => false,
            'aviso_ajuste' => null,
        ];
    }

    /**
     * Retorna o texto legal obrigatório para proteção contra passivos trabalhistas.
     */
    public static function getAvisoLegalObrigatorio(): string
    {
        return 'Recomendamos a contratação de um consultor específico para evitar problemas jurídicos e laborais com desvio de função ou atividades que não condizem com a CBO.';
    }
}
