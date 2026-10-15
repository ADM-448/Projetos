<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentiPro — Gestão Inteligente de Talentos</title>
    <meta name="description"
        content="TalentiPro é a plataforma SaaS de gestão de talentos para empresas modernas. Avaliações 360°, PDI, competências e reuniões com IA em um só lugar.">
    <meta name="keywords" content="gestão de talentos, avaliação de desempenho, PDI, RH, recursos humanos, software RH">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            overflow-x: hidden !important;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8f7f4;
            color: #1e1b2e;
            overflow-x: hidden !important;
            width: 100%;
            max-width: 100vw;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
        }

        img {
            max-width: 100%;
        }

        /* ── TOKENS ───────────────────────────────────────────── */
        :root {
            --purple: #6d28d9;
            --indigo: #4f46e5;
            --indigo-dark: #3730a3;
            --amber: #fff;
            --emerald: #10b981;
            --sky: #0ea5e9;
            --red: #ef4444;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .06);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .10);
            --shadow-lg: 0 10px 40px rgba(0, 0, 0, .14);
            --shadow-xl: 0 20px 60px rgba(0, 0, 0, .18);
        }

        /* ── UTILITIES ────────────────────────────────────────── */
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .container-sm {
            max-width: 760px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .text-center {
            text-align: center;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .items-center {
            align-items: center;
        }

        .justify-center {
            justify-content: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-2 {
            gap: 8px;
        }

        .gap-3 {
            gap: 12px;
        }

        .gap-4 {
            gap: 16px;
        }

        .gap-6 {
            gap: 24px;
        }

        .gap-8 {
            gap: 32px;
        }

        .gap-12 {
            gap: 48px;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .relative {
            position: relative;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .hidden {
            display: none !important;
        }

        /* ── PILL / BADGE ─────────────────────────────────────── */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 13px;
            border-radius: 99px;
            white-space: nowrap;
            line-height: 1;
        }

        .pill-indigo {
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .pill-purple {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }

        .pill-amber {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .3);
        }

        .pill-white {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .25);
            backdrop-filter: blur(8px);
        }

        .pill-emerald {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .pill-red {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .pill-sky {
            background: #f0f9ff;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }

        .pill-slate {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* ── SECTION HEADER ───────────────────────────────────── */
        .section-eyebrow {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: clamp(26px, 3.5vw, 40px);
            font-weight: 900;
            color: var(--slate-900);
            line-height: 1.15;
            letter-spacing: -0.5px;
            margin-bottom: 14px;
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--slate-500);
            line-height: 1.7;
            max-width: 520px;
        }

        .section-header-center {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header-center .section-subtitle {
            margin: 0 auto;
        }

        /* ── BUTTONS ──────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            padding: 13px 26px;
            border-radius: 99px;
            cursor: pointer;
            border: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: #fff;
            color: var(--indigo);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
        }

        .btn-primary:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, .12);
            border: 1.5px solid rgba(255, 255, 255, .28);
            color: #fff;
            backdrop-filter: blur(6px);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, .22);
        }

        .btn-indigo {
            background: var(--indigo);
            color: #fff;
        }

        .btn-indigo:hover {
            background: var(--indigo-dark);
            box-shadow: 0 6px 20px rgba(79, 70, 229, .35);
        }

        .btn-outline-indigo {
            background: #fff;
            color: var(--indigo);
            border: 2px solid var(--indigo);
        }

        .btn-outline-indigo:hover {
            background: #eef2ff;
        }

        .btn-outline-slate {
            background: #fff;
            color: var(--slate-600);
            border: 2px solid var(--slate-200);
        }

        .btn-outline-slate:hover {
            background: var(--slate-50);
        }

        /* ── NAVBAR ───────────────────────────────────────────── */
        #navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 999;
            height: 64px;
            transition: background .3s ease, box-shadow .3s ease;
        }

        #navbar .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            max-width: 1140px;
            width: 100%;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 17px;
            color: #fff;
            transition: color .3s;
        }

        .nav-logo .logo-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(79, 70, 229, .4);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-links a {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, .8);
            transition: color .2s;
        }

        .nav-links a:hover {
            color: #fff;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-login {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            transition: color .2s;
        }

        .nav-login:hover {
            color: #fff;
        }

        .nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #fff;
            color: var(--indigo);
            font-size: 13px;
            font-weight: 700;
            padding: 9px 20px;
            border-radius: 99px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .12);
            transition: transform .2s, box-shadow .2s;
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .18);
        }

        .nav-hamburger {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
        }

        /* scrolled state */
        #navbar.scrolled {
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 1px 0 rgba(0, 0, 0, .08);
        }

        #navbar.scrolled .nav-logo {
            color: var(--slate-900);
        }

        #navbar.scrolled .nav-links a {
            color: var(--slate-500);
        }

        #navbar.scrolled .nav-links a:hover {
            color: var(--indigo);
        }

        #navbar.scrolled .nav-login {
            color: var(--indigo);
            font-weight: 700;
        }

        #navbar.scrolled .nav-hamburger {
            color: var(--slate-700);
        }

        /* Mobile menu */
        #mobile-menu {
            background: #fff;
            border-top: 1px solid var(--slate-100);
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .1);
        }

        #mobile-menu a {
            font-size: 15px;
            font-weight: 600;
            color: var(--slate-700);
        }

        #mobile-menu a:hover {
            color: var(--indigo);
        }

        /* ── HERO ─────────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #4c1d95 0%, #4338ca 55%, #6d28d9 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 24px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-glow-1 {
            position: absolute;
            top: 25%;
            left: 10%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(167, 139, 250, .25) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-glow-2 {
            position: absolute;
            bottom: 20%;
            right: 5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, .2) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            max-width: 720px;
            width: 100%;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            color: #fff;
            padding: 7px 16px;
            border-radius: 99px;
            margin-bottom: 28px;
            backdrop-filter: blur(8px);
        }

        .hero-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #34d399;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .6;
                transform: scale(1.3);
            }
        }

        .hero h1 {
            font-size: clamp(36px, 6vw, 64px);
            font-weight: 900;
            color: #fff;
            line-height: 1.08;
            letter-spacing: -1px;
            margin-bottom: 22px;
        }

        .hero h1 em {
            font-style: normal;
            background: linear-gradient(90deg, #fff, #e0e7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 17px;
            color: rgba(255, 255, 255, .72);
            line-height: 1.7;
            max-width: 520px;
            margin: 0 auto 36px;
        }

        .hero-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 56px;
        }

        /* Browser mockup no hero */
        .hero-mockup {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .4);
            border: 1px solid rgba(255, 255, 255, .15);
            max-width: 680px;
            width: 100%;
            margin: 0 auto;
            text-align: left;
        }

        .mockup-bar {
            background: #1e1b2e;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mockup-dots {
            display: flex;
            gap: 6px;
        }

        .mockup-dots span {
            width: 11px;
            height: 11px;
            border-radius: 50%;
        }

        .mockup-url {
            flex: 1;
            background: rgba(255, 255, 255, .1);
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 11px;
            color: rgba(255, 255, 255, .5);
            text-align: center;
            font-family: monospace;
        }

        .mockup-body {
            padding: 20px;
            background: #f8fafc;
        }

        /* KPI grid no mockup */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .kpi-card {
            border-radius: 10px;
            padding: 14px 10px;
            text-align: center;
            color: #fff;
        }

        .kpi-card .kpi-val {
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
        }

        .kpi-card .kpi-lbl {
            font-size: 9px;
            opacity: .78;
            margin-top: 3px;
            line-height: 1.3;
        }

        .kpi-card .kpi-icon {
            font-size: 11px;
            opacity: .6;
            margin-bottom: 4px;
        }

        .activity-feed {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 14px;
        }

        .activity-feed .feed-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 10px;
        }

        .feed-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0;
        }

        .feed-item+.feed-item {
            border-top: 1px solid #f1f5f9;
        }

        .feed-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .feed-text {
            font-size: 11px;
            color: var(--slate-500);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── LOGOS / SOCIAL PROOF ────────────────────────────── */
        .logos-section {
            background: #fff;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 40px 0;
            overflow: hidden;
        }

        .logos-label {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: 28px;
        }

        .logos-wrapper {
            position: relative;
        }

        .logos-fade-l {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 100px;
            background: linear-gradient(to right, #fff, transparent);
            z-index: 2;
        }

        .logos-fade-r {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 100px;
            background: linear-gradient(to left, #fff, transparent);
            z-index: 2;
        }

        .logos-track {
            display: flex;
            align-items: center;
            gap: 56px;
            width: max-content;
            animation: ticker 28s linear infinite;
        }

        .logos-track:hover {
            animation-play-state: paused;
        }

        @keyframes ticker {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        .logo-item {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #c7d1df;
            white-space: nowrap;
            transition: color .2s;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -.2px;
        }

        .logo-item:hover {
            color: #94a3b8;
        }

        .logo-item i {
            font-size: 22px;
        }

        /* ── FEATURES ─────────────────────────────────────────── */
        .features-section {
            padding: 96px 24px;
            background: #f8f7f4;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feat-card {
            background: #fff;
            border: 1px solid #e9eaf0;
            border-radius: var(--radius);
            padding: 28px 24px;
            position: relative;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .feat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 48px rgba(79, 70, 229, .1);
        }

        .feat-card-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 10px;
            font-weight: 800;
            background: var(--indigo);
            color: #fff;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .feat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 16px;
        }

        .feat-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 8px;
        }

        .feat-desc {
            font-size: 13px;
            color: var(--slate-500);
            line-height: 1.65;
            margin-bottom: 16px;
        }

        .feat-link {
            font-size: 13px;
            font-weight: 700;
            color: var(--indigo);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: gap .2s;
        }

        .feat-card:hover .feat-link {
            gap: 9px;
        }

        /* ── PLATFORM SECTION ────────────────────────────────── */
        .platform-section {
            padding: 96px 24px;
            background: #fff;
        }

        .platform-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 32px;
        }

        .ptab {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 99px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            color: var(--slate-500);
            cursor: pointer;
            transition: all .2s;
        }

        .ptab:hover {
            border-color: #a5b4fc;
            color: var(--indigo);
        }

        .ptab.active {
            background: var(--indigo);
            color: #fff;
            border-color: var(--indigo);
        }

        .platform-browser {
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .platform-bar {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .platform-url {
            flex: 1;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 5px 14px;
            font-size: 12px;
            color: var(--slate-400);
            text-align: center;
            font-family: monospace;
        }

        .platform-panel {
            display: none;
            padding: 28px;
            background: #f8fafc;
            min-height: 340px;
        }

        .platform-panel.active {
            display: block;
        }

        /* Panel: KPI cards */
        .panel-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .panel-kpi {
            background: #fff;
            border: 1px solid #e9eaf0;
            border-radius: 12px;
            padding: 18px 16px;
        }

        .panel-kpi-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .panel-kpi-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--slate-400);
        }

        .panel-kpi-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .panel-kpi-value {
            font-size: 26px;
            font-weight: 900;
            color: var(--slate-900);
            line-height: 1;
        }

        .panel-kpi-sub {
            font-size: 11px;
            color: var(--slate-400);
            margin-top: 2px;
        }

        .panel-feed {
            background: #fff;
            border: 1px solid #e9eaf0;
            border-radius: 12px;
            padding: 18px;
        }

        .panel-feed-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 14px;
        }

        .panel-feed-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
        }

        .panel-feed-item+.panel-feed-item {
            border-top: 1px solid #f1f5f9;
        }

        .panel-feed-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .panel-feed-text {
            font-size: 13px;
            color: var(--slate-500);
        }

        /* Panel: bars */
        .bar-row {
            margin-bottom: 14px;
        }

        .bar-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .bar-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-700);
        }

        .bar-val {
            font-size: 13px;
            font-weight: 800;
            color: var(--slate-900);
        }

        .bar-track {
            height: 10px;
            background: var(--slate-100);
            border-radius: 99px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 1.2s ease;
        }

        /* Panel: cycle cards */
        .cycle-card {
            background: #fff;
            border: 1px solid #e9eaf0;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 12px;
        }

        .cycle-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .cycle-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .cycle-sub {
            font-size: 12px;
            color: var(--slate-400);
            margin-top: 2px;
        }

        /* Panel: PDI list */
        .pdi-row {
            background: #fff;
            border: 1px solid #e9eaf0;
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .pdi-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .pdi-meta {
            font-size: 11px;
            color: var(--slate-400);
            margin-top: 3px;
        }

        /* ── COMO FUNCIONA ────────────────────────────────────── */
        .howto-section {
            padding: 96px 24px;
            background: linear-gradient(145deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-top: 56px;
        }

        .step-item {}

        .step-num {
            font-size: 48px;
            font-weight: 900;
            color: rgba(255, 255, 255, .06);
            line-height: 1;
            margin-bottom: 6px;
        }

        .step-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            margin-bottom: 16px;
            color: #fff;
        }

        .step-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .step-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, .5);
            line-height: 1.65;
        }

        /* ── DEPOIMENTO ───────────────────────────────────────── */
        .testimonial-section {
            padding: 96px 24px;
            background: #f8f7f4;
        }

        .testi-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 60px;
            align-items: center;
        }

        .quote-icon {
            color: var(--indigo);
            font-size: 48px;
            opacity: .25;
            margin-bottom: 20px;
        }

        .quote-text {
            font-size: clamp(20px, 2.5vw, 28px);
            font-weight: 700;
            color: var(--slate-900);
            line-height: 1.4;
            margin-bottom: 28px;
        }

        .quote-text strong {
            color: var(--indigo);
            font-weight: 800;
        }

        .quote-author {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .quote-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            flex-shrink: 0;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
        }

        .quote-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .quote-role {
            font-size: 12px;
            color: var(--slate-500);
            margin-top: 2px;
        }

        .quote-stars {
            margin-left: auto;
            color: #6d28d9;
            font-size: 14px;
            letter-spacing: 2px;
        }

        .metrics-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .metric-card {
            background: #fff;
            border: 1px solid #e9eaf0;
            border-radius: var(--radius);
            padding: 22px 18px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        .metric-val {
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 6px;
        }

        .metric-lbl {
            font-size: 12px;
            color: var(--slate-500);
            line-height: 1.5;
        }

        /* ── NÚMEROS ──────────────────────────────────────────── */
        .numbers-section {
            padding: 72px 24px;
            background: #fff;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .numbers-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            text-align: center;
        }

        .number-val {
            font-size: clamp(36px, 4vw, 52px);
            font-weight: 900;
            color: var(--indigo);
            line-height: 1;
            margin-bottom: 8px;
        }

        .number-lbl {
            font-size: 13px;
            color: var(--slate-500);
            line-height: 1.5;
            max-width: 140px;
            margin: 0 auto;
        }

        /* ── PRICING ──────────────────────────────────────────── */
        .pricing-section {
            padding: 96px 24px;
            background: #f8f7f4;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            align-items: start;
        }

        .plan-card {
            background: #fff;
            border: 1.5px solid #e9eaf0;
            border-radius: 20px;
            padding: 28px 22px;
            position: relative;
            display: flex;
            flex-direction: column;
            transition: box-shadow .25s;
        }

        .plan-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .plan-card.popular {
            border-color: var(--indigo);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12), var(--shadow-md);
        }

        .plan-popular-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(90deg, #6d28d9, #4f46e5);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 5px 18px;
            border-radius: 99px;
            white-space: nowrap;
            box-shadow: 0 2px 10px rgba(79, 70, 229, .4);
        }

        .plan-name {
            font-size: 11px;
            font-weight: 800;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 10px;
        }

        .plan-price {
            display: flex;
            align-items: baseline;
            gap: 3px;
            margin-bottom: 4px;
        }

        .plan-currency {
            font-size: 16px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .plan-amount {
            font-size: 42px;
            font-weight: 900;
            color: var(--slate-900);
            line-height: 1;
        }

        .plan-period {
            font-size: 13px;
            color: var(--slate-400);
        }

        .plan-per-life {
            font-size: 13px;
            font-weight: 700;
            color: var(--indigo);
            margin-bottom: 3px;
        }

        .plan-lives {
            font-size: 12px;
            color: var(--slate-400);
            margin-bottom: 22px;
        }

        .plan-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 18px 0;
        }

        .plan-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 24px;
            flex: 1;
        }

        .plan-feature {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            font-size: 13px;
            color: var(--slate-700);
        }

        .plan-feature.off {
            color: var(--slate-300);
        }

        .plan-feature i {
            font-size: 11px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .plan-feature i.ok {
            color: var(--emerald);
        }

        .plan-feature i.no {
            color: #d1d5db;
        }

        .plan-btn {
            display: block;
            width: 100%;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            transition: all .2s;
            cursor: pointer;
            border: none;
            margin-top: auto;
        }

        .plan-btn-primary {
            background: var(--indigo);
            color: #fff;
        }

        .plan-btn-primary:hover {
            background: var(--indigo-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(79, 70, 229, .35);
        }

        .plan-btn-outline {
            background: #fff;
            color: var(--indigo);
            border: 2px solid var(--indigo);
        }

        .plan-btn-outline:hover {
            background: #eef2ff;
        }

        .plan-btn-slate {
            background: #fff;
            color: var(--slate-600);
            border: 2px solid var(--slate-200);
        }

        .plan-btn-slate:hover {
            background: var(--slate-50);
        }

        .plans-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 13px;
            color: var(--slate-400);
        }

        .plans-footer i {
            color: var(--indigo);
            margin-right: 5px;
        }

        /* ── FAQ ──────────────────────────────────────────────── */
        .faq-section {
            padding: 96px 24px;
            background: #fff;
        }

        .faq-list {
            margin-top: 48px;
        }

        .faq-item {
            border-bottom: 1px solid #f1f5f9;
        }

        .faq-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 0;
            background: none;
            border: none;
            cursor: pointer;
            text-align: left;
        }

        .faq-q {
            font-size: 15px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .faq-icon {
            color: var(--slate-400);
            font-size: 14px;
            flex-shrink: 0;
            transition: transform .25s;
        }

        .faq-item.open .faq-icon {
            transform: rotate(45deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height .35s ease, padding .35s ease;
        }

        .faq-answer p {
            font-size: 14px;
            color: var(--slate-500);
            line-height: 1.75;
            padding-bottom: 20px;
        }

        /* ── BOTTOM CTA ───────────────────────────────────────── */
        .cta-section {
            padding: 96px 24px;
            text-align: center;
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            position: relative;
            overflow: hidden;
        }

        .cta-section h2 {
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 900;
            color: #fff;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .cta-section p {
            font-size: 16px;
            color: rgba(255, 255, 255, .72);
            max-width: 440px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }

        .cta-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ── FOOTER ───────────────────────────────────────────── */
        footer {
            background: #0c0a1a;
            padding: 72px 24px 32px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 56px;
            margin-bottom: 56px;
        }

        .footer-brand {}

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 16px;
            color: #fff;
            margin-bottom: 14px;
        }

        .footer-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, .38);
            line-height: 1.7;
            max-width: 260px;
            margin-bottom: 22px;
        }

        .footer-socials {
            display: flex;
            gap: 10px;
        }

        .footer-social {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .5);
            font-size: 13px;
            transition: background .2s, color .2s;
        }

        .footer-social:hover {
            background: rgba(255, 255, 255, .14);
            color: #fff;
        }

        .footer-col h4 {
            font-size: 10px;
            font-weight: 800;
            color: rgba(255, 255, 255, .4);
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: 18px;
        }

        .footer-col a {
            display: block;
            font-size: 13px;
            color: rgba(255, 255, 255, .4);
            margin-bottom: 11px;
            transition: color .2s;
        }

        .footer-col a:hover {
            color: rgba(255, 255, 255, .85);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .06);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: rgba(255, 255, 255, .25);
        }

        /* ── RESPONSIVE ───────────────────────────────────────── */
        @media (max-width: 1024px) {
            .nav-links,
            .nav-login {
                display: none !important;
            }

            .nav-hamburger {
                display: block !important;
                margin-left: auto;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .plans-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 36px;
            }

            .footer-brand {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .platform-panel {
                padding: 16px 12px;
            }

            .hero h1 {
                font-size: 34px;
            }

            .hero p {
                font-size: 15px;
            }

            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .panel-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .plans-grid {
                grid-template-columns: 1fr;
            }

            .steps-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .numbers-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }

            .testi-grid {
                grid-template-columns: 1fr;
            }

            .metrics-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-btns {
                flex-direction: column;
                align-items: center;
            }

            .cta-btns {
                flex-direction: column;
                align-items: center;
            }

            /* Garante 1 coluna para os cards de KPI do dashboard */
            .panel-kpi-grid,
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            /* Empilha o conteúdo das linhas de PDI no mobile */
            .pdi-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            /* Ajusta cabeçalho dos ciclos de avaliação */
            .cycle-head {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            /* Reduz levemente fontes longas para caberem no container */
            .pdi-title,
            .cycle-name {
                font-size: 13px;
            }

        }

        /* ── ANIMATIONS ───────────────────────────────────────── */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(28px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-1 {
            animation: fadeSlideUp .7s ease .0s both;
        }

        .anim-2 {
            animation: fadeSlideUp .7s ease .13s both;
        }

        .anim-3 {
            animation: fadeSlideUp .7s ease .26s both;
        }

        .anim-4 {
            animation: fadeSlideUp .7s ease .40s both;
        }

        .anim-5 {
            animation: fadeSlideUp .7s ease .54s both;
        }

        .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .6s ease, transform .6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>

    {{-- ============================================================
    NAVBAR
    ============================================================ --}}
    <header id="navbar">
        <div class="nav-inner">
            <a href="/" class="nav-logo" aria-label="TalentiPro">
                <div class="logo-icon">
                    <i class="fa-solid fa-brain" style="color:#fff;font-size:14px;"></i>
                </div>
                <span>TalentiPro</span>
            </a>

            <nav class="nav-links" aria-label="Menu principal">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#plataforma">A Plataforma</a>
                <a href="#planos">Planos</a>
                <a href="#faq">FAQ</a>
            </nav>

            <div class="nav-actions">
                <a href="{{ route('login') }}" class="nav-login" id="nav-login-link">Entrar</a>
                <button class="nav-hamburger" id="hamburger-btn" aria-label="Abrir menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden">
            <a href="#funcionalidades">Funcionalidades</a>
            <a href="#plataforma">A Plataforma</a>
            <a href="#planos">Planos</a>
            <a href="#faq">FAQ</a>
            <a href="{{ route('login') }}" style="color:var(--indigo);font-weight:700;">Entrar →</a>
        </div>
    </header>

    {{-- ============================================================
    HERO
    ============================================================ --}}
    <section class="hero">
        <div class="hero-glow-1"></div>
        <div class="hero-glow-2"></div>

        <div class="hero-content">
            <div class="hero-badge anim-1">
                <span class="dot"></span>
                Plataforma completa de RH — Agora com IA integrada
            </div>

            <h1 class="anim-2">
                Gerenciamento<br>
                <em>Inteligente</em> de Talentos
            </h1>

            <p class="anim-3">
                Avaliações 360°, PDI, competências, reuniões com IA e relatórios
                em tempo real. Tudo integrado em uma plataforma que seu time vai adorar usar.
            </p>

            <div class="hero-btns anim-4">
                <a href="#planos" class="btn btn-primary">
                    <i class="fa-solid fa-rocket" style="font-size:12px;"></i>
                    Começar Agora
                </a>
                <a href="#plataforma" class="btn btn-ghost">
                    <i class="fa-solid fa-play" style="font-size:11px;"></i>
                    Ver a plataforma
                </a>
            </div>

            {{-- Mini-dashboard mockup --}}
            <div class="hero-mockup anim-5">
                <div class="mockup-bar">
                    <div class="mockup-dots">
                        <span style="background:#ff5f57;"></span>
                        <span style="background:#e2e8f0;"></span>
                        <span style="background:#28c840;"></span>
                    </div>
                    <div class="mockup-url">talentipro.com.br/dashboard</div>
                </div>
                <div class="mockup-body">
                    <div class="kpi-grid">
                        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                            <div class="kpi-icon"><i class="fa-solid fa-star"></i></div>
                            <div class="kpi-val">3</div>
                            <div class="kpi-lbl">Avaliações Pendentes</div>
                        </div>
                        <div class="kpi-card" style="background:linear-gradient(135deg,#4f46e5,#4338ca);">
                            <div class="kpi-icon"><i class="fa-solid fa-route"></i></div>
                            <div class="kpi-val">8</div>
                            <div class="kpi-lbl">PDIs Ativos</div>
                        </div>
                        <div class="kpi-card" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
                            <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                            <div class="kpi-val">47</div>
                            <div class="kpi-lbl">Colaboradores</div>
                        </div>
                        <div class="kpi-card" style="background:linear-gradient(135deg,#10b981,#059669);">
                            <div class="kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
                            <div class="kpi-val">91%</div>
                            <div class="kpi-lbl">Taxa Conclusão</div>
                        </div>
                    </div>
                    <div class="activity-feed">
                        <div class="feed-title">Atividade Recente</div>
                        <div class="feed-item">
                            <span class="feed-badge" style="background:#d1fae5;color:#065f46;">✓ Concluída</span>
                            <span class="feed-text">João Silva completou autoavaliação — 2h atrás</span>
                        </div>
                        <div class="feed-item">
                            <span class="feed-badge" style="background:#e0e7ff;color:#3730a3;">⏳ Pendente</span>
                            <span class="feed-text">Maria Santos aguarda avaliação do gestor</span>
                        </div>
                        <div class="feed-item">
                            <span class="feed-badge" style="background:#ede9fe;color:#5b21b6;">🤖 IA</span>
                            <span class="feed-text">Reunião com Ana gerou resumo automático</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
    SOCIAL PROOF — Logos
    ============================================================ --}}
    <section class="logos-section">
        <p class="logos-label">Ideal para a gestão de talentos em diversos setores</p>
        <div class="logos-wrapper">
            <div class="logos-fade-l"></div>
            <div class="logos-fade-r"></div>
            <div class="logos-track">
                @php
                    $logoItems = [
                        ['fa-desktop', 'Tecnologia & Startups'],
                        ['fa-store', 'Varejo & Comércio'],
                        ['fa-industry', 'Indústria & Manufatura'],
                        ['fa-heart-pulse', 'Saúde & Clínicas'],
                        ['fa-graduation-cap', 'Educação & Ensino'],
                        ['fa-truck-fast', 'Logística & Transportes'],
                        ['fa-building-columns', 'Finanças & Contabilidade'],
                        ['fa-utensils', 'Alimentação & Franquias'],
                        ['fa-seedling', 'Agronegócio'],
                        ['fa-helmet-safety', 'Engenharia & Obras'],
                    ];
                @endphp
                @for ($r = 0; $r < 2; $r++)
                    @foreach ($logoItems as $l)
                        <div class="logo-item">
                            <i class="fa-solid {{ $l[0] }}"></i>
                            <span>{{ $l[1] }}</span>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </section>

    {{-- ============================================================
    FEATURES — Grid de casos de uso
    ============================================================ --}}
    <section id="funcionalidades" class="features-section">
        <div class="container">
            <div class="section-header-center reveal">
                <div class="section-eyebrow">
                    <span class="pill pill-indigo">
                        <i class="fa-solid fa-puzzle-piece"></i>
                        Aplicações para cada equipe
                    </span>
                </div>
                <h2 class="section-title">Uma plataforma.<br>Tudo que seu RH precisa.</h2>
                <p class="section-subtitle">
                    Do cadastro de colaboradores ao feedback com IA — centralizado, fácil e poderoso.
                </p>
            </div>

            @php
                $features = [
                    ['icon' => 'fa-star', 'bg' => '#f5f3ff', 'color' => '#6d28d9', 'title' => 'Avaliações 360°', 'badge' => 'Mais usado', 'desc' => 'Ciclos completos com autoavaliação, gestor e mediação. Competências e skills totalmente customizáveis.'],
                    ['icon' => 'fa-route', 'bg' => '#eef2ff', 'color' => '#4f46e5', 'title' => 'PDI Individual', 'badge' => null, 'desc' => 'Planos de Desenvolvimento personalizados com metas, prazos e acompanhamento contínuo do progresso.'],
                    ['icon' => 'fa-robot', 'bg' => '#f5f3ff', 'color' => '#7c3aed', 'title' => 'Reuniões com IA', 'badge' => 'Novo', 'desc' => 'Conduza 1:1s com suporte de IA. Resumos automáticos e insights gerados após cada reunião.'],
                    ['icon' => 'fa-users', 'bg' => '#f0f9ff', 'color' => '#0284c7', 'title' => 'Gestão de Colaboradores', 'badge' => null, 'desc' => 'Cadastre, importe em lote via Excel/CSV e organize por cargo, departamento e gestor.'],
                    ['icon' => 'fa-chart-pie', 'bg' => '#ecfdf5', 'color' => '#059669', 'title' => 'Relatórios e Dashboards', 'badge' => null, 'desc' => 'Visualize resultados por colaborador, equipe e empresa. Decisões baseadas em dados reais.'],
                    ['icon' => 'fa-whatsapp', 'bg' => '#f0fdf4', 'color' => '#16a34a', 'title' => 'Integração WhatsApp', 'badge' => 'Pro+', 'desc' => 'Lembretes e notificações automáticas via WhatsApp Business API. Aumente a taxa de resposta.'],
                    ['icon' => 'fa-brain', 'bg' => '#fdf4ff', 'color' => '#a21caf', 'title' => 'Competências Customizadas', 'badge' => null, 'desc' => 'Defina competências alinhadas à cultura da empresa e use-as em todas as avaliações.'],
                    ['icon' => 'fa-inbox', 'bg' => '#fffbeb', 'color' => '#b45309', 'title' => 'Solicitações e Aprovações', 'badge' => null, 'desc' => 'Fluxo com rastreabilidade. Colaboradores solicitam, gestores aprovam ou recusam.'],
                    ['icon' => 'fa-bell', 'bg' => '#fef2f2', 'color' => '#dc2626', 'title' => 'Notificações em Tempo Real', 'badge' => null, 'desc' => 'Alertas automáticos sobre avaliações pendentes, PDIs vencendo e reuniões agendadas.'],
                ];
            @endphp

            <div class="features-grid">
                @foreach ($features as $f)
                    <div class="feat-card reveal">
                        @if ($f['badge'])
                            <div class="feat-card-badge">{{ $f['badge'] }}</div>
                        @endif
                        <div class="feat-icon" style="background:{{ $f['bg'] }}; color:{{ $f['color'] }};">
                            <i class="fa-solid {{ $f['icon'] }}"></i>
                        </div>
                        <div class="feat-title">{{ $f['title'] }}</div>
                        <p class="feat-desc">{{ $f['desc'] }}</p>
                        <a href="#planos" class="feat-link">
                            Explorar <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
    PLATAFORMA DINÂMICA
    ============================================================
    CONTROLLER: injete as variáveis abaixo via WelcomeController@index
    para substituir os dados mockados desta vitrine:
    - $kpiCards → array de KPIs do tenant demo
    - $feedItems → array de atividades recentes
    - $ciclos → Collection/array de ciclos de avaliação
    - $topPdis → Collection/array dos PDIs recentes
    - $barChart → array de competências com nota média (0–10)
    Exemplo: return view('welcome', compact('kpiCards','feedItems','ciclos','topPdis','barChart'));
    ============================================================ --}}
    <section id="plataforma" class="platform-section">
        <div class="container">
            <div class="section-header-center reveal">
                <div class="section-eyebrow">
                    <span class="pill pill-purple">
                        <i class="fa-solid fa-desktop"></i>
                        A plataforma em ação
                    </span>
                </div>
                <h2 class="section-title">Interface limpa. Dados reais.<br>Decisões mais rápidas.</h2>
                <p class="section-subtitle">
                    Navegue pelas principais telas e veja como o TalentiPro simplifica a gestão de pessoas.
                </p>
            </div>

            <div class="platform-tabs" id="platform-tabs">
                <button class="ptab active" data-tab="dashboard">
                    <i class="fa-solid fa-house" style="font-size:11px;"></i> Dashboard
                </button>
                <button class="ptab" data-tab="avaliacoes">
                    <i class="fa-solid fa-star" style="font-size:11px;"></i> Avaliações 360°
                </button>
                <button class="ptab" data-tab="pdis">
                    <i class="fa-solid fa-route" style="font-size:11px;"></i> PDI
                </button>
                <button class="ptab" data-tab="competencias">
                    <i class="fa-solid fa-brain" style="font-size:11px;"></i> Competências
                </button>
            </div>

            <div class="platform-browser reveal">
                <div class="platform-bar">
                    <div style="display:flex;gap:6px;">
                        <span style="width:11px;height:11px;border-radius:50%;background:#ff5f57;display:block;"></span>
                        <span style="width:11px;height:11px;border-radius:50%;background:#e2e8f0;display:block;"></span>
                        <span style="width:11px;height:11px;border-radius:50%;background:#28c840;display:block;"></span>
                    </div>
                    <div class="platform-url" id="platform-url">talentipro.com.br/dashboard</div>
                </div>

                {{-- ── DASHBOARD ── --}}
                <div class="platform-panel active" id="panel-dashboard">
                    @php
                        $kpiCards = $kpiCards ?? [
                            ['label' => 'Avaliações Pendentes', 'value' => '3', 'sub' => 'este ciclo', 'icon' => 'fa-star', 'bg' => '#f5f3ff', 'color' => '#6d28d9'],
                            ['label' => 'PDIs Ativos', 'value' => '8', 'sub' => 'em andamento', 'icon' => 'fa-route', 'bg' => '#eef2ff', 'color' => '#4f46e5'],
                            ['label' => 'Colaboradores', 'value' => '47', 'sub' => 'cadastrados', 'icon' => 'fa-users', 'bg' => '#f0f9ff', 'color' => '#0284c7'],
                            ['label' => 'Taxa de Conclusão', 'value' => '91%', 'sub' => 'último ciclo', 'icon' => 'fa-chart-line', 'bg' => '#ecfdf5', 'color' => '#059669'],
                        ];
                        $feedItems = $feedItems ?? [
                            ['badge' => '✓ Concluída', 'bg' => '#d1fae5', 'color' => '#065f46', 'text' => 'João Silva completou autoavaliação — 2h atrás'],
                            ['badge' => '⏳ Pendente', 'bg' => '#e0e7ff', 'color' => '#3730a3', 'text' => 'Maria Santos aguarda avaliação do gestor'],
                            ['badge' => '🤖 IA', 'bg' => '#ede9fe', 'color' => '#5b21b6', 'text' => 'Reunião com Ana gerou resumo automático'],
                            ['badge' => '📋 PDI', 'bg' => '#e0f2fe', 'color' => '#0369a1', 'text' => 'Carlos iniciou PDI de Liderança — hoje'],
                        ];
                    @endphp
                    <div class="panel-kpi-grid">
                        @foreach ($kpiCards as $kpi)
                            <div class="panel-kpi">
                                <div class="panel-kpi-head">
                                    <div class="panel-kpi-label">{{ $kpi['label'] }}</div>
                                    <div class="panel-kpi-icon"
                                        style="background:{{ $kpi['bg'] }};color:{{ $kpi['color'] }};">
                                        <i class="fa-solid {{ $kpi['icon'] }}"></i>
                                    </div>
                                </div>
                                <div class="panel-kpi-value">{{ $kpi['value'] }}</div>
                                <div class="panel-kpi-sub">{{ $kpi['sub'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="panel-feed">
                        <div class="panel-feed-title">Atividade Recente</div>
                        @foreach ($feedItems as $item)
                            <div class="panel-feed-item">
                                <span class="panel-feed-badge"
                                    style="background:{{ $item['bg'] }};color:{{ $item['color'] }};">{{ $item['badge'] }}</span>
                                <span class="panel-feed-text">{{ $item['text'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── AVALIAÇÕES ── --}}
                <div class="platform-panel" id="panel-avaliacoes">
                    @php
                        $ciclos = $ciclos ?? [
                            ['nome' => 'Ciclo Q3 2026', 'participantes' => 12, 'progresso' => 75, 'status' => 'Em andamento', 'status_bg' => '#ede9fe', 'status_color' => '#5b21b6', 'bar_color' => '#6d28d9'],
                            ['nome' => 'Ciclo Q2 2026', 'participantes' => 8, 'progresso' => 100, 'status' => 'Finalizado', 'status_bg' => '#d1fae5', 'status_color' => '#065f46', 'bar_color' => '#10b981'],
                            ['nome' => 'Ciclo Q1 2026', 'participantes' => 10, 'progresso' => 100, 'status' => 'Finalizado', 'status_bg' => '#d1fae5', 'status_color' => '#065f46', 'bar_color' => '#10b981'],
                        ];
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:800;color:var(--slate-900);">Ciclos de Avaliação 360°</h3>
                        <span
                            class="pill pill-purple">{{ count(array_filter($ciclos, fn($c) => $c['progresso'] < 100)) }}
                            ativo(s)</span>
                    </div>
                    @foreach ($ciclos as $c)
                        <div class="cycle-card">
                            <div class="cycle-head">
                                <div>
                                    <div class="cycle-name">{{ $c['nome'] }}</div>
                                    <div class="cycle-sub">{{ $c['participantes'] }} participantes</div>
                                </div>
                                <span class="pill"
                                    style="background:{{ $c['status_bg'] }};color:{{ $c['status_color'] }};border:none;">{{ $c['status'] }}</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill"
                                    style="width:{{ $c['progresso'] }}%;background:{{ $c['bar_color'] }};"></div>
                            </div>
                            <div style="text-align:right;font-size:11px;color:var(--slate-400);margin-top:5px;">
                                {{ $c['progresso'] }}% concluído
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ── PDI ── --}}
                <div class="platform-panel" id="panel-pdis">
                    @php
                        $topPdis = $topPdis ?? [
                            ['titulo' => 'Liderança e Comunicação', 'colaborador' => 'João Silva', 'vencimento' => '30/09/2026', 'badge' => 'Em andamento', 'badge_bg' => '#d1fae5', 'badge_color' => '#065f46'],
                            ['titulo' => 'Gestão de Projetos', 'colaborador' => 'Maria Santos', 'vencimento' => '15/10/2026', 'badge' => 'Em andamento', 'badge_bg' => '#d1fae5', 'badge_color' => '#065f46'],
                            ['titulo' => 'Análise de Dados', 'colaborador' => 'Carlos Lima', 'vencimento' => '01/09/2026', 'badge' => 'Atrasado', 'badge_bg' => '#fee2e2', 'badge_color' => '#991b1b'],
                            ['titulo' => 'Inteligência Emocional', 'colaborador' => 'Ana Rocha', 'vencimento' => '15/11/2026', 'badge' => 'Não iniciado', 'badge_bg' => '#f1f5f9', 'badge_color' => '#475569'],
                        ];
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:800;color:var(--slate-900);">Planos de Desenvolvimento
                            Individual</h3>
                        <span style="font-size:12px;color:var(--slate-400);">{{ count($topPdis) }} PDIs</span>
                    </div>
                    @foreach ($topPdis as $pdi)
                        <div class="pdi-row">
                            <div>
                                <div class="pdi-title">{{ $pdi['titulo'] }}</div>
                                <div class="pdi-meta">{{ $pdi['colaborador'] }} · Vence {{ $pdi['vencimento'] }}</div>
                            </div>
                            <span class="pill"
                                style="background:{{ $pdi['badge_bg'] }};color:{{ $pdi['badge_color'] }};border:none;">
                                {{ $pdi['badge'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- ── COMPETÊNCIAS ── --}}
                <div class="platform-panel" id="panel-competencias">
                    @php
                        $barChart = $barChart ?? [
                            ['nome' => 'Comunicação', 'nota' => 8.4, 'color' => '#6d28d9'],
                            ['nome' => 'Liderança', 'nota' => 7.1, 'color' => '#4f46e5'],
                            ['nome' => 'Trabalho em Equipe', 'nota' => 9.2, 'color' => '#0ea5e9'],
                            ['nome' => 'Inovação', 'nota' => 6.8, 'color' => '#7c3aed'],
                            ['nome' => 'Orientação a Resultados', 'nota' => 8.9, 'color' => '#10b981'],
                            ['nome' => 'Gestão do Tempo', 'nota' => 7.5, 'color' => '#6d28d9'],
                        ];
                    @endphp
                    <div style="margin-bottom:24px;">
                        <h3 style="font-size:16px;font-weight:800;color:var(--slate-900);">Médias por Competência —
                            Ciclo Q3 2026</h3>
                        <p style="font-size:12px;color:var(--slate-400);margin-top:3px;">Nota média de todos os
                            colaboradores avaliados</p>
                    </div>
                    @foreach ($barChart as $comp)
                        <div class="bar-row">
                            <div class="bar-header">
                                <span class="bar-name">{{ $comp['nome'] }}</span>
                                <span class="bar-val">{{ number_format($comp['nota'], 1) }}/10</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill"
                                    style="width:{{ $comp['nota'] * 10 }}%;background:{{ $comp['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
    COMO FUNCIONA
    ============================================================ --}}
    <section class="howto-section">
        <div class="container">
            <div class="section-header-center text-center reveal">
                <div class="section-eyebrow">
                    <span class="pill pill-white">
                        <i class="fa-solid fa-map"></i> Como funciona
                    </span>
                </div>
                <h2 class="section-title" style="color:#fff;">Simples de implementar.<br>Poderoso para sua empresa.</h2>
                <p class="section-subtitle" style="color:rgba(255,255,255,.5);margin:0 auto;">
                    Comece a usar em minutos, sem TI ou configurações complexas.
                </p>
            </div>

            @php
                $steps = [
                    ['n' => '01', 'icon' => 'fa-building', 'title' => 'Cadastre sua empresa', 'desc' => 'Crie a conta, configure o nome da empresa e importe colaboradores via planilha ou manualmente.'],
                    ['n' => '02', 'icon' => 'fa-sliders', 'title' => 'Configure competências', 'desc' => 'Defina as competências que serão avaliadas, alinhadas à cultura da organização.'],
                    ['n' => '03', 'icon' => 'fa-play', 'title' => 'Inicie um ciclo', 'desc' => 'Crie um ciclo, selecione participantes e defina datas. Todos recebem notificação automática.'],
                    ['n' => '04', 'icon' => 'fa-chart-line', 'title' => 'Acompanhe e evolua', 'desc' => 'Analise resultados, crie PDIs e agende reuniões de feedback com suporte da IA.'],
                ];
            @endphp
            <div class="steps-grid">
                @foreach ($steps as $s)
                    <div class="step-item reveal">
                        <div class="step-num">{{ $s['n'] }}</div>
                        <div class="step-icon"><i class="fa-solid {{ $s['icon'] }}"></i></div>
                        <div class="step-title">{{ $s['title'] }}</div>
                        <p class="step-desc">{{ $s['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
    DEPOIMENTO
    ============================================================ --}}
    <section class="testimonial-section">
        <div class="container">
            <div class="testi-grid reveal">
                {{-- Citação --}}
                <div>
                    <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                    <blockquote class="quote-text">
                        "O TalentiPro transformou como fazemos gestão de pessoas. As avaliações 360° e os PDIs
                        <strong>são fáceis de configurar</strong> e o time aderiu rapidamente."
                    </blockquote>
                    <div class="quote-author">
                        <div class="quote-avatar">AP</div>
                        <div>
                            <div class="quote-name">Ana Paula Mendes</div>
                            <div class="quote-role">Diretora de RH · Empresa de Tecnologia</div>
                        </div>
                        <div class="quote-stars">★★★★★</div>
                    </div>
                </div>
                {{-- Métricas --}}
                <div class="metrics-cards">
                    @php
                        $metrics = [
                            ['val' => '91%', 'lbl' => 'Taxa de conclusão nas avaliações', 'color' => '#6d28d9'],
                            ['val' => '3×', 'lbl' => 'Mais rápido que planilhas manuais', 'color' => '#4f46e5'],
                            ['val' => '100%', 'lbl' => 'Baseado em nuvem, zero instalação', 'color' => '#0ea5e9'],
                        ];
                    @endphp
                    @foreach ($metrics as $m)
                        <div class="metric-card reveal">
                            <div class="metric-val" style="color:{{ $m['color'] }};">{{ $m['val'] }}</div>
                            <div class="metric-lbl">{{ $m['lbl'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
    MÉTRICAS DE TRANSFORMAÇÃO
    ============================================================ --}}
    <section class="numbers-section">
        <div class="container">
            <div class="numbers-grid">
                @php
                    $nums = [
                        ['val' => '82%', 'lbl' => 'de redução no tempo de avaliação', 'color' => '#4f46e5'],
                        ['val' => '+200', 'lbl' => 'empresas usando a plataforma', 'color' => '#6d28d9'],
                        ['val' => '4.9★', 'lbl' => 'avaliação média de satisfação', 'color' => '#4f46e5'],
                        ['val' => '<5min', 'lbl' => 'para configurar o primeiro ciclo', 'color' => '#10b981'],
                    ];
                @endphp
                @foreach ($nums as $n)
                    <div class="text-center reveal">
                        <div class="number-val" style="color:{{ $n['color'] }};">{{ $n['val'] }}</div>
                        <div class="number-lbl">{{ $n['lbl'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
    PLANOS
    ============================================================ --}}
    <section id="planos" class="pricing-section">
        <div class="container">
            <div class="section-header-center reveal">
                <div class="section-eyebrow">
                    <span class="pill pill-indigo">
                        <i class="fa-solid fa-tag"></i> Planos e preços
                    </span>
                </div>
                <h2 class="section-title">Simples, transparente, sem surpresas.</h2>
                <p class="section-subtitle">
                    Escolha o plano ideal para o tamanho da sua empresa.
                </p>
            </div>

            @php
                $whatsappUrl = 'https://wa.me/554497078165?text=Ol%C3%A1%2C%20vim%20pelo%20site%2C%20gostaria%20de%20saber%20mais%3F';

                $plans = [
                    [
                        'name' => 'Básico',
                        'price' => '149',
                        'per_life' => '12,50',
                        'lives' => 'Até 30 vidas',
                        'popular' => false,
                        'btn_class' => 'plan-btn-outline',
                        'href' => route('login'),
                        'features' => [
                            [true, 'Gestão de colaboradores'],
                            [true, 'Avaliações de desempenho'],
                            [true, 'Competências customizadas'],
                            [true, 'PDI individual'],
                            [true, 'Dashboard e relatórios básicos'],
                            [true, 'Importação via planilha'],
                            [false, 'Reuniões com IA'],
                            [false, 'Integração WhatsApp'],
                        ],
                    ],
                    [
                        'name' => 'Profissional',
                        'price' => '299',
                        'per_life' => '10,00',
                        'lives' => 'Até 100 vidas',
                        'popular' => true,
                        'btn_class' => 'plan-btn-primary',
                        'href' => route('login'),
                        'features' => [
                            [true, 'Tudo do Básico'],
                            [true, 'Reuniões de feedback com IA'],
                            [true, 'Relatórios avançados'],
                            [true, 'Integração WhatsApp API'],
                            [true, 'Avaliações 360° completas'],
                            [true, 'Notificações automáticas'],
                            [true, 'Suporte prioritário'],
                            [false, 'Multi-empresa'],
                        ],
                    ],
                    [
                        'name' => 'Enterprise',
                        'price' => '599',
                        'per_life' => '8,00',
                        'lives' => 'Até 500 vidas',
                        'popular' => false,
                        'btn_class' => 'plan-btn-outline',
                        'href' => $whatsappUrl,
                        'target' => '_blank',
                        'features' => [
                            [true, 'Tudo do Profissional'],
                            [true, 'Dashboard consolidado'],
                            [true, 'API para integrações'],
                            [true, 'Multi-empresa (grupo)'],
                            [true, 'Onboarding dedicado'],
                            [true, 'Gerente de conta exclusivo'],
                            [true, 'SLA garantido'],
                        ],
                    ],
                    [
                        'name' => 'Corporativo',
                        'price' => null,
                        'per_life' => null,
                        'lives' => 'Acima de 500 vidas',
                        'popular' => false,
                        'btn_class' => 'plan-btn-slate',
                        'href' => $whatsappUrl,
                        'target' => '_blank',
                        'features' => [
                            [true, 'Tudo do Enterprise'],
                            [true, 'Acordo comercial sob medida'],
                            [true, 'Implantação presencial'],
                            [true, 'Treinamento In-Company'],
                            [true, 'Customizações exclusivas'],
                            [true, 'Infraestrutura dedicada'],
                        ],
                    ],
                ];
            @endphp

            <div class="plans-grid">
                @foreach ($plans as $plan)
                    <div class="plan-card {{ $plan['popular'] ? 'popular' : '' }} reveal">
                        @if ($plan['popular'])
                            <div class="plan-popular-badge">⭐ Mais popular</div>
                        @endif

                        <div class="plan-name">{{ $plan['name'] }}</div>

                        @if ($plan['price'])
                            <div class="plan-price">
                                <span class="plan-currency">R$</span>
                                <span class="plan-amount">{{ $plan['price'] }}</span>
                                <span class="plan-period">/mês</span>
                            </div>
                            <div class="plan-per-life">+ R$ {{ $plan['per_life'] }}/vida</div>
                        @else
                            <div style="font-size:22px;font-weight:900;color:var(--slate-900);margin-bottom:4px;">Sob consulta
                            </div>
                        @endif
                        <div class="plan-lives">{{ $plan['lives'] }}</div>

                        <hr class="plan-divider">

                        <ul class="plan-features">
                            @foreach ($plan['features'] as [$ok, $text])
                                <li class="plan-feature {{ !$ok ? 'off' : '' }}">
                                    <i class="fa-solid {{ $ok ? 'fa-check ok' : 'fa-xmark no' }}"></i>
                                    {{ $text }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ $plan['href'] }}" class="plan-btn {{ $plan['btn_class'] }}">
                            {{ $plan['price'] ? 'Começar agora' : 'Falar com especialista' }}
                        </a>
                    </div>
                @endforeach
            </div>


        </div>
    </section>

    {{-- ============================================================
    FAQ
    ============================================================ --}}
    <section id="faq" class="faq-section">
        <div class="container-sm">
            <div class="section-header-center reveal">
                <div class="section-eyebrow">
                    <span class="pill pill-slate">
                        <i class="fa-solid fa-circle-question"></i>
                        Dúvidas frequentes
                    </span>
                </div>
                <h2 class="section-title">Perguntas frequentes</h2>
            </div>

            @php
                $faqs = [
                    [
                        'q' => 'O que é o TalentiPro?',
                        'a' => 'TalentiPro é uma plataforma SaaS completa de gestão de talentos. Centraliza avaliações de desempenho, PDI, competências, reuniões com IA e solicitações de colaboradores em um único sistema acessível pelo navegador.'
                    ],
                    [
                        'q' => 'Preciso instalar algum software?',
                        'a' => 'Não. O TalentiPro é 100% baseado em nuvem. Não há instalação necessária — colaboradores acessam pelo celular ou computador diretamente pelo navegador.'
                    ],
                    [
                        'q' => 'Posso importar minha base de colaboradores?',
                        'a' => 'Sim! Basta preencher uma planilha Excel ou CSV com nome, e-mail, cargo, departamento e perfil de acesso. O sistema importa todos de uma vez e cria os acessos automaticamente.'
                    ],
                    [
                        'q' => 'Como funciona a IA nas reuniões?',
                        'a' => 'Ao registrar uma reunião de feedback, o sistema analisa os dados da avaliação do colaborador e gera sugestões de pontos de conversa, insights e um resumo automático ao final. Disponível nos planos Profissional e Enterprise.'
                    ],
                    [
                        'q' => 'Meus dados ficam seguros?',
                        'a' => 'Sim. Todos os dados são armazenados com criptografia e isolados por empresa (arquitetura multi-tenant). Nenhuma empresa tem acesso aos dados de outra.'
                    ],
                ];
            @endphp

            <div class="faq-list reveal">
                @foreach ($faqs as $i => $faq)
                    <div class="faq-item" id="faq-item-{{ $i }}">
                        <button class="faq-btn" data-faq="{{ $i }}" aria-expanded="false">
                            <span class="faq-q">{{ $faq['q'] }}</span>
                            <i class="fa-solid fa-plus faq-icon"></i>
                        </button>
                        <div class="faq-answer" id="faq-answer-{{ $i }}">
                            <p>{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
    BOTTOM CTA
    ============================================================ --}}
    <section class="cta-section">
        <div style="position:relative;z-index:1;">
            <h2>Pronto para transformar<br>a gestão de talentos?</h2>
            <div class="cta-btns">
                <a href="#planos" class="btn btn-primary">
                    <i class="fa-solid fa-rocket" style="font-size:12px;"></i>
                    Começar agora
                </a>
                <a href="https://wa.me/554497078165?text=Ol%C3%A1%2C%20vim%20pelo%20site%2C%20gostaria%20de%20saber%20mais%3F"
                    target="_blank" rel="noopener noreferrer" class="btn btn-ghost">
                    <i class="fa-solid fa-envelope" style="font-size:12px;"></i>
                    Falar com especialista
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
    FOOTER
    ============================================================ --}}
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="logo-icon">
                            <i class="fa-solid fa-brain" style="color:#fff;font-size:14px;"></i>
                        </div>
                        <span>TalentiPro</span>
                    </div>
                    <p class="footer-desc">Plataforma completa de gestão de talentos para empresas modernas que
                        valorizam pessoas.</p>
                    <div class="footer-socials">
                        <a href="#" class="footer-social" aria-label="LinkedIn"><i
                                class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="footer-social" aria-label="Instagram"><i
                                class="fa-brands fa-instagram"></i></a>
                        <a href="mailto:contato@talentipro.com" class="footer-social" aria-label="E-mail"><i class="fa-solid fa-envelope"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Plataforma</h4>
                    <a href="#funcionalidades">Funcionalidades</a>
                    <a href="#plataforma">Dashboards</a>
                    <a href="#planos">Planos e preços</a>
                    <a href="{{ route('login') }}">Login</a>
                </div>

                <div class="footer-col">
                    <h4>Suporte</h4>
                    <a href="#faq">FAQ</a>
                    <a href="mailto:contato@talentipro.com">Contato</a>
                    <a href="#">Política de Privacidade</a>
                    <a href="#">Termos de Uso</a>
                </div>
            </div>

            <div class="footer-bottom">
                <span>© {{ date('Y') }} TalentiPro. Todos os direitos reservados.</span>
                <span>Feito com <span style="color:#f87171;">♥</span> para equipes de RH</span>
            </div>
        </div>
    </footer>

    {{-- ============================================================
    JAVASCRIPT
    ============================================================ --}}
    <script>
        (function () {
            'use strict';

            // ── NAVBAR scroll ─────────────────────────────────────────
            var navbar = document.getElementById('navbar');
            function updateNav() {
                if (window.scrollY > 40) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
            window.addEventListener('scroll', updateNav, { passive: true });
            updateNav();

            // ── HAMBURGER ─────────────────────────────────────────────
            var btn = document.getElementById('hamburger-btn');
            var menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', function () {
                    menu.classList.toggle('hidden');
                });
            }

            // ── SMOOTH SCROLL ─────────────────────────────────────────
            document.querySelectorAll('a[href^="#"]').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    var t = document.querySelector(a.getAttribute('href'));
                    if (t) {
                        e.preventDefault();
                        t.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        if (menu) menu.classList.add('hidden');
                    }
                });
            });

            // ── PLATFORM TABS ─────────────────────────────────────────
            var tabMap = {
                'dashboard': 'talentipro.com.br/dashboard',
                'avaliacoes': 'talentipro.com.br/avaliacoes',
                'pdis': 'talentipro.com.br/pdis',
                'competencias': 'talentipro.com.br/competencias',
            };
            var urlEl = document.getElementById('platform-url');

            document.querySelectorAll('.ptab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var target = tab.dataset.tab;

                    document.querySelectorAll('.ptab').forEach(function (t) {
                        t.classList.remove('active');
                    });
                    tab.classList.add('active');

                    document.querySelectorAll('.platform-panel').forEach(function (p) {
                        p.classList.remove('active');
                    });
                    var panel = document.getElementById('panel-' + target);
                    if (panel) panel.classList.add('active');

                    if (urlEl && tabMap[target]) urlEl.textContent = tabMap[target];
                });
            });

            // ── FAQ ACCORDION ─────────────────────────────────────────
            document.querySelectorAll('.faq-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var item = btn.closest('.faq-item');
                    var answer = item.querySelector('.faq-answer');
                    var isOpen = btn.getAttribute('aria-expanded') === 'true';

                    // fecha todos
                    document.querySelectorAll('.faq-item').forEach(function (it) {
                        it.classList.remove('open');
                        it.querySelector('.faq-answer').style.maxHeight = '0';
                        it.querySelector('.faq-btn').setAttribute('aria-expanded', 'false');
                    });

                    // abre o clicado
                    if (!isOpen) {
                        item.classList.add('open');
                        answer.style.maxHeight = answer.scrollHeight + 40 + 'px';
                        btn.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // ── REVEAL on scroll ──────────────────────────────────────
            var revealEls = document.querySelectorAll('.reveal');
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            revealEls.forEach(function (el) { io.observe(el); });

        })();
    </script>

</body>

</html>