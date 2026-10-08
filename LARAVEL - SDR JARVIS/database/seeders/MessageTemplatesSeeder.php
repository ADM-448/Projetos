<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MessageTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            return;
        }

        $templates = [
            // Abordagem
            [
                'title' => '🎯 WhatsApp: Gancho de Dor & Apresentação Curta',
                'category' => 'approach',
                'channel' => 'whatsapp',
                'is_favorite' => true,
                'content' => "Olá {{decisor}}, tudo bem? Aqui é o {{sdr}} da equipe de soluções B2B.\n\nAcompanho a expansão da {{empresa}} no segmento {{setor}}. Temos auxiliado gestores na sua posição a reduzirem o ciclo comercial e aumentarem o volume de reuniões qualificadas.\n\nFaria sentido 5 minutos na próxima terça para trocarmos uma ideia rápida sobre isso?",
            ],
            [
                'title' => '✉️ E-mail: Apresentação Fria + Caso de Sucesso',
                'category' => 'approach',
                'channel' => 'email',
                'is_favorite' => true,
                'content' => "Assunto: Oportunidade estratégica para {{empresa}}\n\nOlá {{decisor}}, tudo bem?\n\nNotei sua liderança como {{cargo}} na {{empresa}}. Recentemente auxiliamos uma empresa de {{setor}} a reduzir o Custo de Aquisição (CAC) em 30% já no primeiro mês de implementação comercial.\n\nGostaria de compartilhar brevemente este estudo de caso. Faria sentido uma breve call de 15 minutos nesta quinta-feira?",
            ],

            // Quebra de Objeções
            [
                'title' => '🛡️ Quebra de Objeção: "Manda apresentação por e-mail"',
                'category' => 'objection',
                'channel' => 'whatsapp',
                'is_favorite' => true,
                'content' => "Com certeza {{decisor}}! Posso te enviar nosso material sim.\n\nMas para não te mandar uma apresentação genérica de 30 páginas que não agrega à sua rotina, me diga uma coisa: hoje o principal foco da {{empresa}} é volume de novas oportunidades ou qualificação dos leads que já chegam?",
            ],
            [
                'title' => '💰 Quebra de Objeção: "Sem orçamento / Não é o momento"',
                'category' => 'objection',
                'channel' => 'whatsapp',
                'is_favorite' => false,
                'content' => "Entendo perfeitamente {{decisor}}. Justamente por sabermos que todo investimento precisa ser cirúrgico, nossos clientes em {{setor}} nos procuram para aumentar a eficiência comercial e faturar mais antes de expandirem a equipe.\n\nFaria sentido apenas batermos um papo rápido de benchmarking de 10 minutos sem qualquer compromisso de contratação?",
            ],
            [
                'title' => '🤝 Quebra de Objeção: "Já temos equipe / fornecedor"',
                'category' => 'objection',
                'channel' => 'whatsapp',
                'is_favorite' => false,
                'content' => "Excelente {{decisor}}, isso demonstra que a {{empresa}} já tem maturidade nessa frente!\n\nNossa intenção não é substituir nada do que já funciona bem na sua operação, e sim atuar como um acelerador complementar de tração. Vale 10 minutos na semana que vem para você avaliar se nossa tecnologia pode somar ao que vocês já executam?",
            ],

            // Follow-up & Reativação
            [
                'title' => '⚡ WhatsApp: Follow-up Rápido ("Check-in de Agenda")',
                'category' => 'followup',
                'channel' => 'whatsapp',
                'is_favorite' => true,
                'content' => "Oi {{decisor}}, tudo bem por aí?\n\nPassando só para checar se conseguiu dar uma olhada na mensagem anterior sobre a {{empresa}}. Conseguimos falar 5 minutinhos hoje à tarde ou prefere amanhã pela manhã?",
            ],
            [
                'title' => '🚪 E-mail: Break-Up ("Devo encerrar minhas tentativas?")',
                'category' => 'followup',
                'channel' => 'email',
                'is_favorite' => true,
                'content' => "Assunto: Devo encerrar minhas tentativas por aqui, {{decisor}}?\n\nOlá {{decisor}},\n\nTentei contato nos últimos dias mas imagino que a rotina como {{cargo}} na {{empresa}} esteja muito corrida ou a prioridade seja outra no momento.\n\nVou encerrar meus contatos por agora para não encher sua caixa. Se fizer sentido no futuro, fico à inteira disposição!",
            ],

            // Reunião
            [
                'title' => '📅 WhatsApp: Confirmação de Reunião no dia anterior',
                'category' => 'meeting',
                'channel' => 'whatsapp',
                'is_favorite' => true,
                'content' => "Olá {{decisor}}, tudo bem?\n\nPassando para confirmar nosso alinhamento agendado para amanhã às 14h sobre os planos de crescimento da {{empresa}}.\n\nTudo certo por aí ou precisa de algum ajuste na agenda?",
            ],
        ];

        foreach ($templates as $t) {
            MessageTemplate::updateOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $t['title']],
                $t
            );
        }
    }
}
