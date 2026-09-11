<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentiPro — Plataforma de Gestão de Talentos</title>
    <meta name="description"
        content="TalentiPro é a plataforma completa de gestão de talentos para empresas modernas. Avaliações de desempenho, PDI, competências e reuniões com IA em um só lugar.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6956e5;
            /* Roxo/Azul vibrante do TalentiPro */
            --primary-dark: #5542d1;
            --primary-glow: rgba(105, 86, 229, 0.4);
            --bg-dark: #0f172a;
            /* Slate 900 - Fundo tech mais suave */
            --bg-light: #ffffff;
            --bg-gray: #f8fafc;
            --text-light: #ffffff;
            --text-muted-light: #94a3b8;
            --text-dark: #1e293b;
            --text-muted-dark: #64748b;
            --border-dark: rgba(255, 255, 255, 0.1);
            --border-light: #e2e8f0;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* GLOBALS */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 5%;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 0 30px var(--primary-glow);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.4);
        }

        /* NAV */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: rgba(105, 86, 229, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: 80px;
            display: flex;
            align-items: center;
        }

        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 5%;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 900;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
        }

        .logo i {
            color: white;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: white;
        }

        .nav-cta {
            background: white !important;
            color: var(--primary) !important;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 700 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-cta:hover {
            background: #f8fafc !important;
        }

        /* HERO SECTION */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
            text-align: center;
            background: linear-gradient(135deg, #6956e5 0%, #4a38b4 100%);
            color: white;
        }

        /* Elementos orgânicos / modernos de fundo no hero */
        .hero::before {
            content: '';
            position: absolute;
            top: -15%;
            left: -5%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 5%;
            display: flex;
            justify-content: center;
        }

        .hero-text {
            max-width: 800px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
            padding: 6px 16px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }

        .hero h1 {
            font-size: clamp(2rem, 3.5vw, 3.5rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            color: white;
            letter-spacing: -1px;
        }

        .hero p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            margin-bottom: 35px;
            margin-left: auto;
            margin-right: auto;
            max-width: 700px;
        }

        .hero-btns {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-hero-primary {
            background: white;
            color: var(--primary);
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-hero-primary:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }

        /* LIGHT MODE SECTIONS (IntalenTI Style) */
        .light-section {
            background: var(--bg-light);
            color: var(--text-dark);
            padding: 100px 0;
        }

        .gray-section {
            background: var(--bg-gray);
            color: var(--text-dark);
            padding: 100px 0;
            border-top: 1px solid var(--border-light);
            border-bottom: 1px solid var(--border-light);
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px;
        }

        .section-header h2 {
            font-size: clamp(2rem, 3vw, 2.5rem);
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .section-header p {
            font-size: 1.1rem;
            color: var(--text-muted-dark);
            line-height: 1.6;
        }

        /* FEATURES */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border-color: transparent;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(16, 89, 241, 0.1);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .feature-card p {
            font-size: 0.95rem;
            color: var(--text-muted-dark);
            line-height: 1.6;
        }

        /* ECOSYSTEM (IntalenTI Group) */
        .ecosystem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
            margin-top: 40px;
        }

        .eco-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .eco-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        }

        .eco-header {
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .eco-intalenti .eco-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .eco-investhunter .eco-header {
            background: linear-gradient(135deg, #05050a, #020617);
            border-bottom: 2px solid #1059f1;
        }

        .eco-logo {
            height: 40px;
            margin-bottom: 15px;
        }

        .eco-header h3 {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .eco-header p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 10px;
        }

        .eco-body {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .eco-body p {
            font-size: 1rem;
            color: var(--text-muted-dark);
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .eco-btn {
            margin-top: auto;
            display: block;
            text-align: center;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-intalenti {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-intalenti:hover {
            background: #e2e8f0;
        }

        .btn-investhunter {
            background: rgba(16, 89, 241, 0.1);
            color: #1059f1;
        }

        .btn-investhunter:hover {
            background: rgba(16, 89, 241, 0.2);
        }

        /* PRICING */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            align-items: stretch;
        }

        .price-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .price-popular {
            border: 2px solid var(--primary);
            transform: scale(1.05);
            box-shadow: 0 30px 60px rgba(16, 89, 241, 0.15);
            z-index: 10;
        }

        .popular-badge {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .price-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .price-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 5px;
        }

        .price-value span {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted-dark);
        }

        .price-features {
            list-style: none;
            margin-bottom: 30px;
            flex: 1;
        }

        .price-features li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 12px;
            color: var(--text-muted-dark);
            font-size: 0.9rem;
        }

        .price-features li i {
            color: #10b981;
            font-size: 1rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .btn-price {
            display: block;
            width: 100%;
            text-align: center;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
            border: 2px solid var(--primary);
            margin-top: auto;
        }

        .btn-price-outline {
            background: transparent;
            color: var(--primary);
        }

        .btn-price-outline:hover {
            background: rgba(16, 89, 241, 0.05);
        }

        .btn-price-fill {
            background: var(--primary);
            color: white;
        }

        .btn-price-fill:hover {
            background: var(--primary-dark);
        }

        /* FOOTER */
        footer {
            background: linear-gradient(135deg, #6956e5 0%, #4a38b4 100%);
            padding: 60px 0 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-brand p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            max-width: 300px;
            margin-top: 15px;
            line-height: 1.6;
        }

        .footer-links {
            display: flex;
            gap: 40px;
        }

        .footer-links div {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-links h4 {
            color: white;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 25px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }

        /* MOBILE */
        @media (max-width: 1200px) {
            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .price-popular {
                transform: scale(1);
            }

            .ecosystem-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }

            .mobile-actions {
                display: flex !important;
            }

            .hero-btns {
                flex-direction: column;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav>
        <div class="nav-inner">
            <a href="/" class="logo"><i class="fa-solid fa-brain"></i> TalentiPro</a>

            <div class="nav-links">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#planos">Planos</a>
                <a href="#ecossistema">Ecossistema</a>
                <a href="{{ route('login') }}" style="color: white; font-weight: 600;">Login</a>
                <a href="#planos" class="nav-cta">Assinar Agora</a>
            </div>

            <div class="mobile-actions" style="display: none; align-items: center; gap: 15px;">
                <a href="{{ route('login') }}" style="color: white; font-weight: 600; text-decoration: none;">Login</a>
                <a href="#planos" class="nav-cta" style="padding: 6px 12px; font-size: 0.85rem;">Assinar</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">Plataforma de Gestão Corporativa</div>
                <h1>Transforme seu time em capital de crescimento.</h1>
                <p>O TalentiPro viabiliza a gestão inteligente de pessoas. Avaliações 360°, PDIs estruturados e reuniões
                    de feedback impulsionadas por Inteligência Artificial em um só ecossistema.</p>
                <div class="hero-btns">
                    <a href="#planos" class="btn-hero-primary">Ver Planos e Preços</a>
                    <a href="https://wa.me/554499916992?text=Ol%C3%A1%2C%20gostaria%20de%20falar%20com%20um%20consultor%20sobre%20o%20TalentiPro."
                        target="_blank" class="btn-outline"><i class="fa-brands fa-whatsapp"></i> Falar com
                        Consultor</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="funcionalidades" class="light-section">
        <div class="container">
            <div class="section-header">
                <h2>Uma plataforma, infinitas possibilidades</h2>
                <p>Nós proporcionamos aos nossos clientes ações e planos que atuam diretamente na melhoria e performance
                    das suas equipes.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h3>Avaliações de Desempenho 360°</h3>
                    <p>Crie ciclos de avaliação personalizados com autoavaliação, avaliação do gestor e formulários de
                        competências sob medida para a sua cultura.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-route"></i></div>
                    <h3>Planos de Desenvolvimento (PDI)</h3>
                    <p>Mapeie o futuro dos seus colaboradores criando trilhas claras de aprendizado, prazos definidos e
                        acompanhamento contínuo de metas.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                    <h3>Reuniões com Inteligência Artificial</h3>
                    <p>Chega de perder informações. A nossa IA ouve, transcreve e analisa reuniões 1:1, gerando resumos
                        executivos e sugestões automáticas de PDI.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-users"></i></div>
                    <h3>Gestão Completa de Colaboradores</h3>
                    <p>Organograma inteligente. Cadastre equipes, departamentos e cargos facilmente, e permita
                        solicitações de reabertura via painel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-brands fa-whatsapp"></i></div>
                    <h3>Integração WhatsApp API</h3>
                    <p>Engajamento no canal onde sua equipe já está. Lembretes de avaliações, PDIs e relatórios enviados
                        diretamente no celular do usuário.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-chart-pie"></i></div>
                    <h3>Relatórios e Dashboards</h3>
                    <p>Indicadores em tempo real sobre a saúde do seu time. Saiba exatamente quem são os talentos que
                        precisam de reconhecimento e retenção.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING SECTION -->
    <section id="planos" class="gray-section">
        <div class="container">
            <div class="section-header">
                <h2>Planos transparentes para sua empresa</h2>
                <p>Escolha a opção que melhor se adapta ao estágio de crescimento da sua organização.</p>
            </div>

            <div class="pricing-grid">
                <!-- Bronze -->
                <div class="price-card">
                    <div class="price-name">Bronze</div>
                    <div class="price-value">R$ 149<span>/mês</span></div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 20px;">+ R$
                        12,50 / vida</div>
                    <ul class="price-features">
                        <li><i class="fa-solid fa-check"></i> Até 30 colaboradores</li>
                        <li><i class="fa-solid fa-check"></i> Gestão de Avaliações 360° e PDIs</li>
                        <li><i class="fa-solid fa-check"></i> IA: 8 Reuniões gravadas (4h)</li>
                        <li><i class="fa-solid fa-check"></i> Integração WhatsApp e Google Meet</li>
                    </ul>
                </div>

                <!-- Prata (Destaque) -->
                <div class="price-card price-popular">
                    <div class="popular-badge">MAIS POPULAR</div>
                    <div class="price-name">Prata</div>
                    <div class="price-value">R$ 299<span>/mês</span></div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 20px;">+ R$
                        10,00 / vida</div>
                    <ul class="price-features">
                        <li><i class="fa-solid fa-check"></i> Até 100 colaboradores</li>
                        <li><i class="fa-solid fa-check"></i> IA: 20 Reuniões gravadas (10h)</li>
                        <li><i class="fa-solid fa-check"></i> Dashboard e Relatórios para Exportação</li>
                        <li><i class="fa-solid fa-check"></i> Integração WhatsApp e Google Meet</li>
                    </ul>
                </div>

                <!-- Ouro -->
                <div class="price-card">
                    <div class="price-name">Ouro</div>
                    <div class="price-value">R$ 599<span>/mês</span></div>
                    <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 20px;">+ R$
                        8,00 / vida</div>
                    <ul class="price-features">
                        <li><i class="fa-solid fa-check"></i> Até 500 colaboradores</li>
                        <li><i class="fa-solid fa-check"></i> IA: 60 Reuniões gravadas (30h)</li>
                        <li><i class="fa-solid fa-check"></i> Dashboard e Relatórios para Exportação</li>
                        <li><i class="fa-solid fa-check"></i> Integração WhatsApp e Google Meet</li>
                    </ul>
                </div>

                <!-- Enterprise -->
                <div class="price-card">
                    <div class="price-name">Enterprise</div>
                    <div class="price-value" style="font-size: 1.8rem;">Sob Consulta</div>
                    <div
                        style="font-size: 0.95rem; font-weight: 700; color: var(--text-muted-dark); margin-bottom: 20px;">
                        Acima de 500 vidas</div>
                    <ul class="price-features">
                        <li><i class="fa-solid fa-check"></i> Colaboradores Ilimitados</li>
                        <li><i class="fa-solid fa-check"></i> IA: Franquia Sob Demanda</li>
                        <li><i class="fa-solid fa-check"></i> Dashboard e Relatórios para Exportação</li>
                        <li><i class="fa-solid fa-check"></i> Integração WhatsApp e Google Meet</li>
                    </ul>
                </div>
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="https://wa.me/554499916992?text=Ol%C3%A1%2C%20gostaria%20de%20entrar%20em%20contato%20sobre%20os%20planos%20do%20TalentiPro."
                    target="_blank" class="btn-hero-primary"
                    style="background: var(--primary); color: white; padding: 16px 40px; font-size: 1.1rem;"><i
                        class="fa-brands fa-whatsapp"></i> Entrar em Contato</a>
            </div>
        </div>
    </section>

    <!-- ECOSYSTEM SECTION -->
    <section id="ecossistema" class="light-section">
        <div class="container">
            <div class="section-header">
                <h2>Conheça nosso Ecossistema</h2>
                <p>O TalentiPro faz parte de uma organização dedicada a trazer soluções corporativas integradas de alto
                    impacto.</p>
            </div>

            <div class="ecosystem-grid">
                <!-- IntalenTI Group -->
                <div class="eco-card eco-intalenti">
                    <div class="eco-header">
                        <h3>INTALENTI</h3>
                        <p>Group</p>
                    </div>
                    <div class="eco-body">
                        <p>Uma organização que traz soluções em diversos ramos de atendimento como <strong>consultoria,
                                recrutamento, marketing e pré-vendas.</strong> Proporcionamos aos nossos clientes ações
                            e planos de melhoria e performance.</p>
                        <a href="https://intalenti.com.br" target="_blank" class="eco-btn btn-intalenti">Visitar
                            IntalenTI Group &rarr;</a>
                    </div>
                </div>

                <!-- InvestHunter -->
                <div class="eco-card eco-investhunter">
                    <div class="eco-header">
                        <h3>INVESTHUNTER</h3>
                        <p>Consultoria Estratégica Corporativa</p>
                    </div>
                    <div class="eco-body">
                        <p>Transformamos seu projeto em capital de crescimento. Viabilizamos o acesso a recursos
                            financeiros não reembolsáveis e linhas de crédito subsidiadas dos maiores fundos de fomento
                            do Brasil.</p>
                        <a href="https://investhunter.com.br" target="_blank" class="eco-btn btn-investhunter">Visitar
                            InvestHunter &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo"><i class="fa-solid fa-brain"></i> TalentiPro</a>
                    <p>Uma solução do ecossistema IntalenTI para gestão inteligente de talentos e equipes.</p>
                </div>
                <div class="footer-links">
                    <div>
                        <h4>Plataforma</h4>
                        <a href="#funcionalidades">Funcionalidades</a>
                        <a href="#planos">Planos e Preços</a>
                        <a href="{{ route('login') }}">Acessar Sistema</a>
                    </div>
                    <div>
                        <h4>Empresa</h4>
                        <a href="https://intalenti.com.br" target="_blank">IntalenTI Group</a>
                        <a href="https://investhunter.com.br" target="_blank">InvestHunter</a>
                        <a href="{{ route('termos.uso') }}">Termos de Uso</a>
                        <a href="{{ route('politica.privacidade') }}">Privacidade</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; {{ date('Y') }} TalentiPro. Todos os direitos reservados.
            </div>
        </div>
    </footer>

</body>

</html>