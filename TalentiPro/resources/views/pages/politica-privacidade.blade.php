<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Talenti-Pro</title>
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

        .alert {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Política de Privacidade</h1>
        <p><strong>Última atualização:</strong> {{ now()->format('d/m/Y') }}</p>

        <p>A Talenti-Pro respeita a sua privacidade e tem o compromisso de proteger os dados pessoais e informações
            sensíveis que você compartilha conosco. Esta Política de Privacidade explica como coletamos, usamos,
            compartilhamos e protegemos os seus dados ao utilizar a nossa plataforma, incluindo as nossas integrações
            com serviços de terceiros, como o Google Workspace.</p>

        <h2>1. Dados Coletados</h2>
        <p>Ao utilizar a Talenti-Pro, podemos coletar os seguintes tipos de informações:</p>
        <ul>
            <li><strong>Informações de Cadastro:</strong> Nome completo, endereço de e-mail e cargo.</li>
            <li><strong>Integração com o Google (OAuth):</strong> Caso você opte por conectar sua conta do Google,
                solicitamos acesso ao seu perfil básico (Nome e E-mail) para autenticação, e acesso ao seu Google
                Calendar para o agendamento automatizado de reuniões e criação de links do Google Meet.</li>
            <li><strong>Dados de Uso:</strong> Informações sobre como você interage com a plataforma (logs, cliques,
                avaliações, feedbacks gerados).</li>
            <li><strong>Áudios e Transcrições:</strong> Áudios gravados através da nossa sala de reuniões virtuais (via
                Microfone/WebRTC) e as transcrições geradas pela Inteligência Artificial.</li>
        </ul>

        <h2>2. Uso das Informações</h2>
        <p>Utilizamos os dados coletados exclusivamente para os seguintes propósitos:</p>
        <ul>
            <li>Autenticar o usuário e prover acesso seguro à plataforma.</li>
            <li>Agendar reuniões de "One-on-One" (1:1) de feedback no calendário do Google dos participantes, inserindo
                links de videoconferência de forma autônoma (se a permissão for concedida).</li>
            <li>Processar áudios utilizando Inteligência Artificial (Google Gemini) para gerar atas, resumos e análises
                de sentimento das reuniões (os áudios são enviados de forma segura para processamento).</li>
            <li>Melhorar continuamente os serviços oferecidos.</li>
        </ul>

        <div class="alert">
            <strong>Transparência com Dados do Google:</strong> O uso das informações recebidas das APIs do Google por
            parte da Talenti-Pro e sua transferência para qualquer outro aplicativo estarão de acordo com a <a
                href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank"
                style="color: #6956e5;">Política de Dados do Usuário dos Serviços de API do Google</a>, incluindo os
            requisitos de uso limitado. <strong>Nós não vendemos seus dados.</strong>
        </div>

        <h2>3. Compartilhamento de Dados</h2>
        <p>Nós não vendemos, alugamos ou comercializamos seus dados pessoais. O compartilhamento ocorre apenas:</p>
        <ul>
            <li>Com o Google, através do envio de áudios brutos da reunião exclusivamente para a API do Gemini, a fim de
                gerar as transcrições fechadas solicitadas pelo contratante. Esses dados não são usados pelo Google para
                treinar modelos de inteligência artificial de fundação.</li>
            <li>Com os administradores da sua empresa (RH e Gestores), conforme a hierarquia configurada na plataforma.
            </li>
            <li>Por força de ordem judicial ou obrigação legal.</li>
        </ul>

        <h2>4. Armazenamento e Segurança</h2>
        <p>Seus dados são armazenados de forma criptografada em servidores seguros (como AWS S3 para mídias).
            Implementamos as melhores práticas da indústria (SSL/TLS, criptografia de repouso) para evitar acessos não
            autorizados.</p>

        <h2>5. Direitos do Usuário</h2>
        <p>Você tem o direito de solicitar a exclusão de sua conta, o descadastramento da sua conta Google ou a exclusão
            dos áudios/transcrições de suas reuniões a qualquer momento. Para desconectar o Google, basta utilizar o
            botão "Desconectar Google" diretamente na tela de reuniões do aplicativo.</p>

        <h2>6. Contato</h2>
        <p>Para dúvidas sobre esta política de privacidade, entre em contato através do e-mail:
            <strong>financeiro@intalenti.com.br</strong></p>
    </div>
</body>

</html>