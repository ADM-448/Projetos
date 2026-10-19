# 🏢 TalentiPro - SaaS de Gestão Estratégica de Talentos, DISC & Avaliação 360° com IA

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Google Gemini](https://img.shields.io/badge/Google%20Gemini-AI%20Powered-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://ai.google.dev/)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-Proprietary-blue?style=for-the-badge)](#)

O **TalentiPro** é uma plataforma SaaS empresarial de alta performance projetada para revolucionar a Gestão Estratégica de Recursos Humanos e Desenvolvimento Organizacional. O sistema centraliza Avaliações de Desempenho (90°, 180°, 360° e Competências com Pesos Customizados), Mapeamento Comportamental DISC, Planos de Desenvolvimento Individual (PDI) automatizados com IA, Gestão de Feedbacks/Reuniões com Transcrição de Áudio Inteligente e Pesquisa de Clima Organizacional anônima e auditada.

---

## 🚀 Arquitetura & Principais Funcionalidades

### 1. Avaliações de Desempenho & Competências
- **Modelos Flexíveis:** Suporte a autoavaliação, avaliação de líderes, pares e liderados com cálculo ponderado automático por tipo de competência (Técnicas, Comportamentais, Liderança).
- **Templates Reutilizáveis & Recorrência:** Criação de matrizes globais de avaliação com agendamento automático de ciclos periódicos.
- **Evidências Comportamentais:** Registro de fatos observados e justificativas com upload e histórico durante o processo de resposta.
- **Relatórios Analíticos:** Gráficos radar, gap analysis comparativo (Autoavaliação vs Gestor), cálculo de aderência a cargo e exportação em PDF executivo.

### 2. Mapeamento de Perfil Comportamental DISC
- **Questionário Parametrizado:** Algoritmo proprietário para identificação de perfis D (Dominância), I (Influência), S (Estabilidade) e C (Conformidade).
- **Links Externos & Convites:** Disparo automatizado de testes por e-mail/WhatsApp para candidatos externos ou colaboradores sem necessidade de login prévio.
- **Mapa de Equipe (Team Grid):** Visão macro da distribuição comportamental do departamento para tomada de decisão estratégica de liderança.

### 3. Inteligência Artificial com Google Gemini
- **Transcrição de Áudios de Reunião:** Gravação ou upload direto de áudios de 1:1 e reuniões de feedback com transcrição e sumarização de tópicos chave via `GeminiAudioService`.
- **Geração de PDI Inteligente:** Análise preditiva dos gaps da avaliação de desempenho para sugestão automática de ações práticas de desenvolvimento, prazos e metas via `GeminiTextService`.
- **Perguntas Situacionais:** Geração contextual de questões comportamentais baseadas na descrição do cargo (`CargoQuestaoIa`).

### 4. Gestão Multi-Tenant & Painel do Consultor
- **Multi-Tenant Seguro:** Isolamento estrito por `empresa_id` em queries via Traits e Global Scopes Laravel, com suporte a White-label (cores customizadas e logotipo da empresa contratante).
- **Portal de Consultoria:** Usuários com perfil `consultor` podem alternar dinamicamente entre múltiplas empresas clientes sem trocar de conta.

### 5. Pesquisa de Clima Organizacional
- Questionários anônimos por categoria com controle antifraude via hash criptográfico de participação e auditoria de respostas agregadas por departamento.

---

## 📈 Histórico de Evolução do Projeto (Changelog)

O projeto foi construído e versionado de maneira incremental, refletindo cada marco de maturidade técnica e entrega de valor:

| Release | Marco Cronológico | Principais Entregas Técnicas |
| :---: | :---: | :--- |
| **`v1.0.0`** | Julho/2026 | **MVP Completo da Plataforma:**<br>• Gestão de Usuários, Cargos, Departamentos e Competências.<br>• Avaliações de Desempenho estruturadas e conciliação de notas.<br>• Módulo inicial de PDI (Planos de Ação) e Reuniões de Feedback.<br>• Módulo de Pesquisa de Clima Organizacional e notificações WhatsApp. |
| **`v2.0.0`** | Setembro/2026 | **Módulo DISC & Refatoração de Controladores:**<br>• Implementação completa da Avaliação Comportamental DISC (Matriz e Algoritmo de cálculo).<br>• Mapeamento de Skills associadas a competências organizacionais.<br>• Refatoração e unificação dos controladores monolíticos.<br>• Introdução de sistema de Lembretes agendados e Telescope para telemetria. |
| **`v3.0.0`** | Outubro/2026 | **Módulo Consultor Multi-Tenant & Templates de Avaliação:**<br>• Arquitetura Multi-Tenant para Consultores de RH alternarem entre diferentes empresas.<br>• Suporte a Templates reutilizáveis de avaliação com motor de agendamento (`AvaliacaoSchedule`).<br>• Módulo de gravação de reuniões em segundo plano com widget flutuante.<br>• Integração com deep providers de IA (`AiManager`, Gemini, OpenAI, DeepSeek). |
| **`v4.0.0`** | Outubro/2026 | **IA Gemini, Modern UI/UX e Pronto para Produção:**<br>• Transcrição de áudio via `GeminiAudioService` e análise semântica de feedbacks.<br>• Geração automática de planos PDI a partir de deficiências de competências via IA.<br>• Ponderação de notas por peso em competências e inclusão de evidências anexadas.<br>• Redesign responsivo com TailwindCSS, Blade Components e White-label completo de marcas. |

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8.2+, Laravel 11.x, Eloquent ORM, Gates/Policies, Queues & Cron Jobs
- **Frontend:** Blade Templates, TailwindCSS, Alpine.js / Vanilla JS, Chart.js
- **Banco de Dados:** MySQL 8.0+ / MariaDB / PostgreSQL
- **Inteligência Artificial:** Google Gemini Pro & Gemini Flash (Text & Audio APIs)
- **Integrações Externas:** Google Calendar API, WhatsApp API Gateway, SMTP Mailers

---

## ⚙️ Guia de Instalação e Execução Local

### Pré-requisitos
- PHP 8.2 ou superior com extensões `pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `fileinfo` ativas.
- Composer 2.x
- Node.js 18+ e NPM
- MySQL / MariaDB em execução

### Passo a Passo

1. **Acessar a pasta do projeto:**
   ```bash
   cd TalentiPro
   ```

2. **Instalar dependências do PHP:**
   ```bash
   composer install
   ```

3. **Configurar as Variáveis de Ambiente:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar o banco de dados no `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=talentipro
   DB_USERNAME=root
   DB_PASSWORD=
   
   # Configurações de IA (Google Gemini)
   GEMINI_API_KEY=sua_chave_gemini_aqui
   ```

5. **Executar Migrations e Seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Criar link simbólico do storage:**
   ```bash
   php artisan storage:link
   ```

7. **Instalar e compilar os assets de frontend:**
   ```bash
   npm install
   npm run build # ou npm run dev para desenvolvimento
   ```

8. **Iniciar o servidor de desenvolvimento:**
   ```bash
   php artisan serve
   ```
   Acesse a aplicação em `http://localhost:8000`.

---

## 🔐 Segurança e Boas Práticas
- **Tenant Isolation:** Validação rigorosa em todas as rotas e queries garantindo que nenhum usuário acesse dados de outra organização.
- **RBAC (Role Based Access Control):** Papéis bem definidos para Administrador SaaS, Gestor de Empresa, Consultor Externo e Colaborador.
- **Sanitização de Dados:** Proteção nativa contra CSRF, XSS e SQL Injection em todos os formulários e rotas da API.

---

Desenvolvido por **ADM-448**.
