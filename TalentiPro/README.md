<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

# 🚀 TalentiPro - Plataforma de Gestão de Talentos e Desempenho

O **TalentiPro** é uma plataforma inovadora e completa focada na Gestão de Talentos, Mapeamento de Competências e Avaliação de Desempenho para empresas que buscam modernizar e profissionalizar seu setor de Recursos Humanos.

## 🌟 Principais Módulos e Funcionalidades

*   🏢 **Gestão Organizacional Completa:**
    *   Estruturação de **Departamentos**, **Cargos** e **Colaboradores**.
    *   Mapeamento inteligente de **Hard Skills** e **Soft Skills** (Competências) atreladas a cada cargo.
    *   Suporte a importação em massa para onboarding acelerado de grandes volumes de colaboradores e competências.
*   📊 **Avaliação de Desempenho Inteligente:**
    *   Fluxos dinâmicos de avaliação (Autoavaliação, Avaliação Múltipla/360 graus).
    *   Papel do Gestor como "Mediador" (fechamento, calibração de notas).
    *   Dashboards interativos e emissão de **Relatórios Individuais** e **Relatórios de Mediação**, evidenciando lacunas (gaps) de desempenho em relação ao cargo.
    *   Alimentação responsiva via API (para modais interativos e respostas rápidas).
*   📈 **PDI (Plano de Desenvolvimento Individual):**
    *   Criação de planos de ação personalizados para o desenvolvimento contínuo pós-avaliação.
    *   Sistema de acompanhamento em comentários entre a liderança e o liderado.
    *   Upload e anexação de **comprovantes** (certificados, cursos e documentos) atrelados às ações do PDI.
*   📞 **Canal de Solicitações de RH:**
    *   Central de atendimento na qual o colaborador pode solicitar férias, enviar atestados médicos, entre outros, e o RH monitora, aprova ou recusa as demandas em tempo real.
*   🏢 **Multi-empresa (Multi-tenant):**
    *   Ambiente preparado para **holdings** ou infraestruturas corporais de grande escala, permitindo o intercâmbio centralizado entre filiais e empresas no próprio painel (`/login/select-company`).

## 🛠️ Stack Tecnológico

O TalentiPro foi construído visando estabilidade, segurança e performance utilizando o estado da arte do PHP contendo padrões consolidados de engenharia de software:

- **Linguagem Back-end:** PHP 8.2+
- **Framework Core:** Laravel 12.0 (MVC tradicional com modulação em Controllers e Middlewares)
- **Frontend / UI:** Motor assíncrono Blade + Vite (JavaScript, ES6+) e CSS Utilitário modernoizado (TailwindCSS).
- **Banco de Dados:** MySQL
- **Módulo de Autenticação:** Scaffold robusto customizado em cima do Laravel Breeze.

## 🚀 Instalação e Ambiente de Desenvolvimento

Siga as etapas rigorosamente para configurar o projeto limpo com integridade no seu setup local:

1. **Clone do Repositório**
   ```bash
   git clone [URL_DO_REPOSITORIO]
   cd TalentiPro
   ```

2. **Dependências Back-end (Composer)**
   ```bash
   composer install
   ```

3. **Arquivo de Variáveis de Ambiente (.Env)**
   Copie o modelo de ambiente `.env.example` para gerar o seu `.env` contendo as variáveis únicas do projeto (como Banco de Dados).
   ```bash
   # Utilizando Windows (CMD/PowerShell)
   copy .env.example .env
   # Ou Linux / Mac / WSL
   cp .env.example .env
   ```
   Logo depois, gere a chave única de APP:
   ```bash
   php artisan key:generate
   ```

4. **Dependências Front-end (NPM / Vite)**
   Instale os node_modules e faça a pre-compilação dos assets otimizados da aplicação.
   ```bash
   npm install
   npm run build
   ```
   *Se for codar com "Hot Reload", após o install deixe rodando `npm run dev` no terminal aberto.*

5. **Banco de Dados (Migrations e Seeds)**
   A aplicação subirá as tabelas da infraestrutura base ativando logo em seguida as Seeds padrões (como permissões super admin, `UserSeeder.php` etc.).
   ```bash
   php artisan migrate --seed
   ```

6. **Acionar Engine Local**
   ```bash
   php artisan serve
   ```
   *Painel acessível via browser no IP dinâmico emitido (Geralmente http://localhost:8000).*

## 📦 Deploy para Produção (VPS / cPanel / Hostgator)

As regras de roteamento na raiz (`/public`) foram padronizadas e empacotadas. Todo o plano rigoroso de deploy (incluindo permissões de chmod e configuração de servidor com Subpastas) consta no guia corporativo oficial de homologação.
👉 [**Guia de Deploy (README_DEPLOY.md)**](./README_DEPLOY.md)

---
**Engenharia de Software & Produto by _Seu Time / Intalenti_**
> Projetos robustos demandam manutenção constante e clean code. Contate os Devs em caso de ampliações modulares futuras (Mobile ou Restful API escalável).
