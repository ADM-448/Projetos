# 🤖 SDR JARVIS - Plataforma de Prospecção Ativa, Enriquecimento B2B e IA Omnichannel

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![AI Agent](https://img.shields.io/badge/AI%20Agent-JARVIS-blueviolet?style=for-the-badge)](#)
[![Multi-Tenant](https://img.shields.io/badge/Multi--Tenant-SaaS-orange?style=for-the-badge)](#)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

O **SDR JARVIS** é uma plataforma SaaS empresarial de **Sales Development Representative (SDR) Autônomo e Assistido por IA**. Desenvolvida sobre o ecossistema Laravel 11, a solução automatiza cadências de prospecção outbound, enriquecimento de leads e empresas via dados corporativos (Receita/CNPJ), atendimento omnichannel em tempo real (WhatsApp, Webchat, E-mail com OAuth Google e VoIP integrado) e gestão visual do funil de vendas via Kanban.

---

## 🚀 Principais Módulos & Recursos

### 1. Agente Inteligente SDR (JARVIS AI)
- Motor de conversação e qualificação automatizada de leads baseado em Large Language Models (LLM).
- Geração de respostas contextuais, agendamento de reuniões e roteamento dinâmico de oportunidades quentes para executivos de vendas.

### 2. Enriquecimento de Dados B2B (Company Enrichment)
- Consulta automatizada e enriquecimento cadastral de empresas com base em CNPJ/domínio corporativo.
- Mapeamento de porte, faturamento estimado, quadro societário e decisores chave para abordagem personalizada.

### 3. Cadências Outbound & Templates Dinâmicos
- Criação de sequências estruturadas de contato em múltiplos canais (E-mail, WhatsApp, Ligação/VoIP).
- Gestão centralizada de templates de mensagens com variáveis dinâmicas e testes de engajamento (`MessageTemplateController`).

### 4. Gestão Omnichannel (WhatsApp, Gmail OAuth & VoIP)
- **WhatsApp Multi-Instâncias:** Conexão e envio/recebimento de mensagens em massa com fila controlada.
- **Integração Gmail via OAuth:** Sincronização direta de caixas de entrada de vendedores para disparo seguro e monitoramento de aberturas/respostas.
- **Telefonia VoIP:** Disparo e registro de chamadas de prospecção diretamente pela plataforma (`VoipController`).

### 5. CRM Kanban & Gestão Multi-Tenant
- Quadro Kanban interativo com drag-and-drop para acompanhamento das fases do lead (Prospecção, Qualificação, Apresentação, Negociação, Ganho/Perdido).
- Arquitetura Multi-Tenant isolando workspaces por organização com painel de administração corporativa (`AdminTenantController`).

---

## 🛠️ Stack Tecnológica

| Componente | Tecnologia |
| :--- | :--- |
| **Backend** | PHP 8.2+, Laravel 11.x, Eloquent ORM, Jobs & Queues |
| **Frontend** | Blade Templates, TailwindCSS, Alpine.js, Drag & Drop Kanban |
| **Banco de Dados** | MySQL 8.0+ / PostgreSQL |
| **Integrações** | Google OAuth API (Gmail), WhatsApp Gateways, VoIP Providers |
| **IA / LLM** | Provedores Generativos de IA para automação de conversas |

---

## ⚙️ Guia de Instalação e Execução Local

### Pré-requisitos
- PHP 8.2+ com extensões `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `openssl` ativas.
- Composer 2.x
- Node.js 18+ e NPM
- MySQL ativo

### Passo a Passo

1. **Acessar a pasta do projeto:**
   ```bash
   cd "LARAVEL - SDR JARVIS"
   ```

2. **Instalar dependências do PHP e Node:**
   ```bash
   composer install
   npm install
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
   DB_DATABASE=sdr_jarvis
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Executar Migrations e Seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Compilar os Assets de Frontend:**
   ```bash
   npm run build # ou npm run dev para desenvolvimento
   ```

7. **Iniciar o Servidor:**
   ```bash
   php artisan serve
   ```
   Acesse a aplicação no navegador em `http://localhost:8000`.

---

Desenvolvido por **ADM-448**.
