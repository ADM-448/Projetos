<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - Talenti-Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #334155;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #f8fafc;
        }

        .container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        h1 {
            color: #6956e5;
            margin-bottom: 30px;
            font-size: 2rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }

        h2 {
            color: #0f172a;
            margin-top: 30px;
            font-size: 1.25rem;
        }

        p,
        li {
            margin-bottom: 15px;
        }

        ul {
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Termos de Uso</h1>
        <p><strong>Última atualização:</strong> {{ now()->format('d/m/Y') }}</p>

        <p>Bem-vindo à Talenti-Pro. Ao acessar e utilizar o nosso sistema, você concorda com os Termos de Serviço
            descritos abaixo. Se você não concorda com estes termos, por favor não utilize a nossa plataforma.</p>

        <h2>1. Descrição do Serviço</h2>
        <p>A Talenti-Pro é uma plataforma SaaS (Software as a Service) para gestão de Recursos Humanos, focada em
            Avaliações de Desempenho, PDIs (Plano de Desenvolvimento Individual) e ferramentas assistidas por IA para
            reuniões 1:1 de feedback.</p>

        <h2>2. Conta de Usuário e Responsabilidades</h2>
        <ul>
            <li>Você é responsável por manter a confidencialidade das credenciais da sua conta.</li>
            <li>Você se compromete a usar a plataforma de forma ética, não enviando áudios ou informações de terceiros
                sem consentimento explícito.</li>
            <li>A responsabilidade pelas avaliações e relatórios gerados a partir do sistema (mesmo aqueles auxiliados
                por Inteligência Artificial) recai integralmente sobre os gestores e a empresa contratante.</li>
        </ul>

        <h2>3. Integração com Serviços de Terceiros</h2>
        <p>A Talenti-Pro fornece uma funcionalidade opcional de integração com o Google Calendar. Ao utilizar esse
            recurso:</p>
        <ul>
            <li>Você concorda em conceder à Talenti-Pro acesso para visualizar e gerenciar (criar) eventos na sua
                agenda.</li>
            <li>Você reconhece que a disponibilidade deste recurso depende das APIs do Google e pode ser suspensa caso o
                Google altere seus Termos de Serviço.</li>
        </ul>

        <h2>4. Inteligência Artificial (Gemini)</h2>
        <p>Nossa funcionalidade de transcrição e resumo de reuniões utiliza a tecnologia de Inteligência Artificial
            Generativa. Você entende que:</p>
        <ul>
            <li>A IA pode ocasionalmente gerar resumos imprecisos ou "alucinações". Os relatórios gerados servem como
                rascunho executivo e devem ser sempre revisados pelo gestor responsável.</li>
            <li>A análise de sentimento (Positivo/Neutro/Negativo) é uma inferência de máquina e não substitui o bom
                senso humano e as práticas empáticas de RH.</li>
        </ul>

        <h2>5. Cancelamento e Rescisão</h2>
        <p>Podemos encerrar ou suspender o seu acesso à plataforma, sem aviso prévio, se houver violação destes Termos.
            O cliente pode cancelar a sua assinatura a qualquer momento entrando em contato com a equipe comercial.</p>

        <h2>6. Contato</h2>
        <p>Para dúvidas sobre estes termos de uso, entre em contato através do e-mail:
            <strong>financeiro@intalenti.com.br</strong></p>
    </div>
</body>

</html>