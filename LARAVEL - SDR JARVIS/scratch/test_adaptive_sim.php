<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = App\Models\Tenant::find(3);
$instructions = $tenant->settings['instructions'] ?? '';
$companyProp = $tenant->settings['company_value_prop'] ?? '';
$agentName = $tenant->settings['ai_name'] ?? 'João do time de Vendas';

function generateAdaptiveReply(string $input, array $context): string
{
    $agentName = $context['agent_name'] ?? 'Consultor';
    $companyProp = $context['company_value_prop'] ?? 'nossa solução';
    $instructions = $context['instructions'] ?? '';
    $lower = mb_strtolower(trim($input));

    $isHr = str_contains(mb_strtolower($instructions . ' ' . $companyProp), 'rh') || str_contains(mb_strtolower($instructions . ' ' . $companyProp), 'talenti');
    $brand = $isHr ? 'TalentiPro' : (preg_match('/([A-Z][a-zA-Z0-9]+Pro|[A-Z][a-zA-Z0-9]{3,})/u', $companyProp, $m) ? $m[1] : 'nossa empresa');

    // 1. Dúvida de Treinamento / Escopo ("vcs treinam oq?", "o que vcs treinam", "o que fazem")
    if (str_contains($lower, 'treina') || str_contains($lower, 'treinam') || str_contains($lower, 'treinamento') || str_contains($lower, 'oq') || str_contains($lower, 'o que fazem') || str_contains($lower, 'o que é') || str_contains($lower, 'oque fazem')) {
        if ($isHr) {
            return "Opa! Na verdade não atuamos com treinamentos tradicionais. Somos especialistas em People Ops e diagnósticos de RH através da plataforma {$brand}. Nosso foco é centralizar feedbacks, reuniões 1:1, metas e ciclos de avaliação de desempenho (90°, 180° e 360°) para apoiar a liderança. Para eu contextualizar melhor: você atua diretamente na liderança de RH, é gestor de equipe ou fundador/diretor?";
        } else {
            return "Opa! Nós atuamos com {$companyProp}. O nosso propósito é apoiar sua operação a resolver gargalos e acelerar resultados. Para eu contextualizar melhor o seu momento: qual é o principal objetivo ou necessidade da sua empresa hoje?";
        }
    }

    // 2. Objeção / Menção a Planilhas / Processo Manual
    if (str_contains($lower, 'planilha') || str_contains($lower, 'planilhas') || str_contains($lower, 'excel') || str_contains($lower, 'manual')) {
        if (str_contains($instructions, 'Planilhas funcionam bem')) {
            return "Planilhas funcionam bem no início, mas com o crescimento da equipe elas acabam gerando perda de histórico, retrabalho e falta de métricas consolidadas. A {$brand} automatiza esse processo para a liderança focar apenas nas conversas estratégicas. Qual é o tamanho aproximado da equipe hoje?";
        }
        return "Te entendo! Planilhas e processos manuais quebram um galho no início, mas logo viram um gargalo de tempo e controle. Qual é o tamanho aproximado da equipe de vocês hoje?";
    }

    // 3. Objeção de Preço ("quanto custa", "preço", "valor", "tá caro")
    if (str_contains($lower, 'preço') || str_contains($lower, 'preco') || str_contains($lower, 'quanto custa') || str_contains($lower, 'valor') || str_contains($lower, 'investimento')) {
        if ($isHr && str_contains($instructions, 'colaboradores ativos')) {
            return "Os planos são estruturados de acordo com o número de colaboradores ativos na empresa, justamente para ficar acessível tanto para times enxutos quanto operações maiores. Em 15 minutos de demonstração conseguimos ver seu cenário e passar a proposta exata. Quinta ou sexta-feira ficaria melhor para você?";
        }
        return "Pergunta justa! Nossos planos são dimensionados de acordo com o tamanho da sua operação para que o retorno sobre o investimento seja imediato. Em um bate-papo de 15 minutinhos conseguimos entender seu modelo e passar a tabela exata. Quinta ou sexta-feira seria melhor?";
    }

    // 4. Objeção de Tempo / Implantação ("sem tempo", "implantar")
    if (str_contains($lower, 'sem tempo') || str_contains($lower, 'implantar') || str_contains($lower, 'corrido') || str_contains($lower, 'demora')) {
        if ($isHr && str_contains($instructions, 'Google')) {
            return "A plataforma foi pensada para rodar sem atrito: a integração de reuniões 1:1 é direta com a agenda do Google e os ciclos de avaliação 90°, 180° e 360° já possuem templates prontos. Você me daria 15 minutinhos para eu te mostrar na prática?";
        }
        return "Sei bem como é a correria do dia a dia! Por isso mesmo nossa implantação é assistida e pronta em menos de 48 horas, sem travar a rotina da sua equipe. Em 10 minutinhos eu te mostro tudo na tela. Amanhã às 14h ficaria viável?";
    }

    // 5. Identificação de Persona (Lead respondeu o Passo 1: "sou de rh", "sou gestor", "sou diretor")
    if (str_contains($lower, 'rh') || str_contains($lower, 'gestor') || str_contains($lower, 'diretor') || str_contains($lower, 'lider') || str_contains($lower, 'fundador') || str_contains($lower, 'ceo')) {
        if ($isHr) {
            return "Entendido! Ter essa visão faz toda a diferença. Hoje vocês utilizam planilhas para acompanhar metas e feedbacks ou já contam com alguma ferramenta dedicada?";
        }
        return "Perfeito! Muito bom falar com quem está à frente da operação. Hoje qual tem sido o maior gargalo no processo atual de vocês?";
    }

    // 6. Tamanho de equipe / Quantidade de pessoas
    if (preg_match('/\b(\d{1,4})\s*(pessoas|colaboradores|funcionários|membros|no time)?\b/u', $lower, $m) || str_contains($lower, 'pessoas') || str_contains($lower, 'colaboradores')) {
        $count = $m[1] ?? 'seu time';
        if ($isHr) {
            return "Entendi perfeitamente: para uma equipe com essa estrutura, centralizar os históricos de feedback sem sobrecarregar a gestão é fundamental. O próximo passo ideal seria realizarmos uma demonstração prática de 15 a 20 minutos da {$brand} para você ver o fluxo funcionando. Qual seria o melhor dia para alinharmos essa apresentação?";
        }
        return "Excelente dimensão! Com essa equipe, estruturar os processos gera um ganho de escala imediato. O que acha de uma call de 15 minutinhos para vermos o fluxo na prática? Quinta ou sexta ficaria melhor?";
    }

    // 7. Saudações Iniciais ("oi", "ola", "bom dia", "teste", "opa")
    $greetings = ['teste', 'testando', 'oi', 'ola', 'olá', 'opa', 'fala', 'e ai', 'eae', 'bom dia', 'boa tarde', 'boa noite'];
    if (in_array($lower, $greetings) || strlen(trim($input)) <= 6) {
        if ($isHr) {
            return "Olá! Sou o {$agentName}, consultor de relacionamento da InTalenti e da plataforma {$brand}. Tudo bem por aí? Para eu contextualizar melhor: você atua diretamente na liderança de RH, é gestor de equipe ou fundador/diretor?";
        }
        return "Olá! Sou o {$agentName}, da {$companyProp}. Tudo bem por aí? Me conta: como posso te apoiar com os desafios da sua empresa hoje?";
    }

    // 8. Resposta padrão inteligente conectada à proposta de valor do usuário
    return "Com certeza! Nosso propósito na {$companyProp} é justamente facilitar a sua rotina com diagnóstico consultivo e tecnologia. Para eu te direcionar com precisão: qual é o principal desafio que você enfrenta hoje?";
}

$testInputs = [
    'vcs treinam oq?',
    'oi',
    'quanto custa a plataforma?',
    'a gente usa planilhas no excel aqui',
    'sou gestor de equipe',
    'somos em 45 colaboradores',
    'estamos sem tempo para implantar software',
];

$ctx = [
    'agent_name' => $agentName,
    'company_value_prop' => $companyProp,
    'instructions' => $instructions,
];

foreach ($testInputs as $inp) {
    echo "=============================================\n";
    echo "USER: {$inp}\n";
    $reply = generateAdaptiveReply($inp, $ctx);
    echo "REPLY: {$reply}\n";
}
