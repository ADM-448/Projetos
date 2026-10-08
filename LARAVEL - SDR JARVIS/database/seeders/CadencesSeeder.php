<?php

namespace Database\Seeders;

use App\Models\Cadence;
use App\Models\CadenceStep;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CadencesSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            return;
        }

        // 1. Cadência Outbound Hunter
        $cadence1 = Cadence::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '🚀 Outbound Hunter B2B (7 Passos / 10 Dias)'],
            [
                'description' => 'Sequência completa e agressiva para prospecção fria multicanal (WhatsApp + E-mail + Cold Call). Máxima taxa de resposta.',
                'target_segment' => 'Prospecção Ativa / Outbound Frio',
                'color' => '#130e30',
                'is_active' => true,
            ]
        );

        $steps1 = [
            [
                'step_number' => 1,
                'day_offset' => 0,
                'task_type' => 'whatsapp',
                'priority' => 'high',
                'title' => '1º Toque WhatsApp: Gancho de Dor & Abordagem Rápida',
                'instructions' => 'Enviar mensagem personalizada focando no problema principal do setor.',
                'template_content' => "Olá {{decisor}}, tudo bem? Aqui é o {{sdr}} da equipe de aceleração B2B.\n\nNotei a expansão recente da {{empresa}} no segmento {{setor}}. Temos auxiliado diretores do seu nicho a reduzirem o ciclo de vendas e aumentarem a qualificação de leads.\n\nVocê teria 5 minutinhos na próxima terça para trocarmos uma ideia rápida a respeito?",
            ],
            [
                'step_number' => 2,
                'day_offset' => 1,
                'task_type' => 'email',
                'priority' => 'medium',
                'title' => '1º E-mail Frio: Apresentação + Caso de Sucesso Real',
                'instructions' => 'Enviar e-mail formal conectando com a mensagem enviada no WhatsApp ontem.',
                'template_content' => "Assunto: Oportunidade estratégica para a {{empresa}}\n\nOlá {{decisor}},\n\nOntem te enviei uma breve mensagem no WhatsApp. Gostaria de compartilhar como ajudamos empresas semelhantes à {{empresa}} a aumentarem a produtividade comercial em mais de 40% nos primeiros 60 dias.\n\nFaria sentido conversarmos na próxima semana por 15 minutos?",
            ],
            [
                'step_number' => 3,
                'day_offset' => 2,
                'task_type' => 'whatsapp',
                'priority' => 'high',
                'title' => '2º Toque WhatsApp: Pergunta de Diagnóstico Direta',
                'instructions' => 'Faça uma pergunta simples de sim/não ou escolha múltipla para facilitar a resposta.',
                'template_content' => "{{decisor}}, uma rápida pergunta: hoje o principal foco da {{empresa}} na aquisição de clientes é volume de novas oportunidades ou conversão e qualificação dos leads?",
            ],
            [
                'step_number' => 4,
                'day_offset' => 1,
                'task_type' => 'call',
                'priority' => 'urgent',
                'title' => 'Cold Call: 3 Minutos de Alinhamento com Decisor',
                'instructions' => 'Ligar para o telefone direto ou celular do decisor. Se cair na recepção, peça transferência direta citando o nome.',
                'template_content' => "Roteiro: 'Oi {{decisor}}, {{sdr}} por aqui. Sei que você deve estar ocupado, prometo ser breve: te mandei um e-mail sobre a {{empresa}}...'",
            ],
            [
                'step_number' => 5,
                'day_offset' => 3,
                'task_type' => 'email',
                'priority' => 'medium',
                'title' => '2º E-mail: Conteúdo de Alto Valor & Prova Social',
                'instructions' => 'Compartilhe um benchmark ou métrica de mercado para gerar autoridade imediata.',
                'template_content' => "Assunto: Dados de mercado para {{empresa}} | Benchmark {{setor}}\n\nOlá {{decisor}},\n\nMapeamos recentemente os principais indicadores de eficiência em empresas de {{setor}} e separei este ponto de reflexão para sua equipe...",
            ],
            [
                'step_number' => 6,
                'day_offset' => 2,
                'task_type' => 'whatsapp',
                'priority' => 'medium',
                'title' => '3º Toque WhatsApp: Áudio Personalizado (25 segundos)',
                'instructions' => 'Grave um áudio espontâneo e amigável chamando o decisor pelo primeiro nome.',
                'template_content' => "Gravar áudio direto pelo WhatsApp: 'Fala {{decisor}}, tudo bom? Aqui é o {{sdr}}...'",
            ],
            [
                'step_number' => 7,
                'day_offset' => 1,
                'task_type' => 'email',
                'priority' => 'low',
                'title' => 'E-mail de Break-Up: Despedida & Porta Aberta',
                'instructions' => 'Mensagem de desengajamento cordial que costuma ter a maior taxa de resposta de decisão.',
                'template_content' => "Assunto: Devo encerrar minhas tentativas por aqui, {{decisor}}?\n\nOlá {{decisor}},\n\nTentei contato nos últimos dias mas imagino que a rotina na {{empresa}} esteja muito corrida ou a prioridade seja outra no momento. Vou encerrar meus contatos por agora para não lotar sua caixa de entrada. Se fizer sentido no futuro, fico à disposição!",
            ],
        ];

        foreach ($steps1 as $stepData) {
            CadenceStep::updateOrCreate(
                ['cadence_id' => $cadence1->id, 'step_number' => $stepData['step_number']],
                $stepData
            );
        }

        // 2. Cadência de Reativação Rápida
        $cadence2 = Cadence::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '⚡ Reativação Rápida (3 Passos / 5 Dias)'],
            [
                'description' => 'Sequência ágil para reaquecer leads e contas que esfriaram após o primeiro contato ou proposta.',
                'target_segment' => 'Leads Parados / Reaquecimento',
                'color' => '#f59e0b',
                'is_active' => true,
            ]
        );

        $steps2 = [
            [
                'step_number' => 1,
                'day_offset' => 0,
                'task_type' => 'whatsapp',
                'priority' => 'high',
                'title' => 'Toque WhatsApp: Retomada com Gancho Específico',
                'instructions' => 'Pergunte se avaliaram internamente o material anterior.',
                'template_content' => "Oi {{decisor}}, tudo bem? Semana passada alinhamos alguns pontos sobre a {{empresa}}. Queria checar se conseguiram validar internamente para darmos o próximo passo.",
            ],
            [
                'step_number' => 2,
                'day_offset' => 2,
                'task_type' => 'call',
                'priority' => 'medium',
                'title' => 'Ligação de Check-in & Quebra de Dúvidas',
                'instructions' => 'Entenda se houve mudança de prioridade ou dúvida orçamentária.',
                'template_content' => "Checar se o decisor precisa de ajustes na proposta ou conversa com o financeiro.",
            ],
            [
                'step_number' => 3,
                'day_offset' => 3,
                'task_type' => 'whatsapp',
                'priority' => 'low',
                'title' => 'Follow-up Final: Manter no Radar?',
                'instructions' => 'Perguntar se é melhor retomar no próximo trimestre.',
                'template_content' => "{{decisor}}, para organizar meu cronograma aqui: faz sentido mantermos essa conversa aberta ou prefere que eu retome no próximo mês?",
            ],
        ];

        foreach ($steps2 as $stepData) {
            CadenceStep::updateOrCreate(
                ['cadence_id' => $cadence2->id, 'step_number' => $stepData['step_number']],
                $stepData
            );
        }

        // 3. Cadência Pós-Reunião Agendada / Proposta
        $cadence3 = Cadence::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '🎯 Pós-Reunião & Fechamento (4 Passos / 7 Dias)'],
            [
                'description' => 'Acompanhamento rigoroso de pós-demonstração comercial para não deixar a oportunidade esfriar.',
                'target_segment' => 'Oportunidades em Negociação',
                'color' => '#10b981',
                'is_active' => true,
            ]
        );

        $steps3 = [
            [
                'step_number' => 1,
                'day_offset' => 0,
                'task_type' => 'email',
                'priority' => 'high',
                'title' => 'Envio de Ata & Próximos Passos por E-mail',
                'instructions' => 'Enviar resumo dos pontos acordados na call e proposta comercial.',
                'template_content' => "Assunto: Resumo da nossa reunião & Proposta comercial para a {{empresa}}\n\nOlá {{decisor}},\n\nObrigado pelo tempo hoje. Conforme combinamos, seguem em anexo a proposta detalhada e os prazos alinhados...",
            ],
            [
                'step_number' => 2,
                'day_offset' => 2,
                'task_type' => 'whatsapp',
                'priority' => 'high',
                'title' => 'WhatsApp: Confirmação de Leitura com Sócios',
                'instructions' => 'Checar se a diretoria teve tempo de revisar o material.',
                'template_content' => "Oi {{decisor}}, tudo bem? Conseguiu apresentar a proposta para o time ou restou alguma dúvida que eu possa ajudar a sanar?",
            ],
            [
                'step_number' => 3,
                'day_offset' => 2,
                'task_type' => 'call',
                'priority' => 'urgent',
                'title' => 'Ligação de Negociação & Alinhamento de Minuta',
                'instructions' => 'Ligar para acertar detalhes de pagamento, início de onboard e contrato.',
                'template_content' => "Ligar para alinhar fechamento e validar data de assinatura.",
            ],
            [
                'step_number' => 4,
                'day_offset' => 3,
                'task_type' => 'whatsapp',
                'priority' => 'high',
                'title' => 'Toque Decisivo de Assinatura & Início',
                'instructions' => 'Incentivar conclusão da contratação no ciclo vigente.',
                'template_content' => "{{decisor}}, estamos finalizando a grade de novos clientes deste ciclo. Conseguimos rodar a assinatura hoje para garantir a condição?",
            ],
        ];

        foreach ($steps3 as $stepData) {
            CadenceStep::updateOrCreate(
                ['cadence_id' => $cadence3->id, 'step_number' => $stepData['step_number']],
                $stepData
            );
        }
    }
}
