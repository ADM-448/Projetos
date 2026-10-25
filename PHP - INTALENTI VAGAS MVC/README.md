# 💼 InTalenti Vagas Enterprise — Plataforma Multi-Tenant de Recrutamento & Empregabilidade

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Framework](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Architecture](https://img.shields.io/badge/Architecture-Clean%20MVC%20%7C%20Multi--Tenant-blue?style=for-the-badge)](#)
[![UI Design](https://img.shields.io/badge/Design%20System-Peerlist%20Inspired-00aa45?style=for-the-badge)](#)
[![Tests](https://img.shields.io/badge/Tests-35%20Passed%20%7C%20177%20Assertions-success?style=for-the-badge&logo=githubactions&logoColor=white)](#)
[![Database](https://img.shields.io/badge/MySQL-8.x%20%7C%20MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

O **InTalenti Vagas Enterprise** é uma plataforma corporativa de alta performance para recrutamento, seleção e observatório de empregabilidade regional. Desenvolvido sob a arquitetura **Laravel 12** e seguindo os padrões **PSR-12**, **Clean Architecture** e **Design System inspirado no Peerlist**, o sistema une **Multi-Tenancy contextual** (atendendo tanto prefeituras públicas quanto ecossistemas privados) com isolamento rigoroso de dados e testes automatizados de ponta a ponta.

---

## 🏛️ Destaques da Engenharia & Arquitetura

### 1. Refatoração e Modernização Arquitetural
O sistema foi completamente modernizado a partir de uma base legada procedural em PHP puro para um ecossistema corporativo robusto:
- **Camada de Dados:** Migração de queries manuais e PDO cru para **Eloquent ORM**, migrations versionadas, seeders determinísticos e **Global Scopes**.
- **Geolocalização Resiliente:** Substituição de chamadas procedurais por **`App\Services\GeoService`** com fallback em camadas (BrasilAPI ➔ ViaCEP ➔ Nominatim OSM), cálculo de distância via fórmula de Haversine e cache inteligente de 30 dias por CEP.
- **Autenticação & Segurança:** Autenticação nativa com `RateLimiter` contra ataques de força bruta, proteção CSRF automática, unicidade estrita de CPF/CNPJ com algoritmos de validação oficiais e sanitização preventiva de XSS.

### 2. Multi-Tenancy Dinâmico (Prefeituras & Corporativo)
- **Portais Municipais Independentes:** Isolamento automático por domínio/subdomínio via middleware `IdentifyPortal`.
- **Observatório Municipal da Prefeitura:** Painel dedicado com mapa de vagas e métricas de contratação locais, permitindo aos gestores públicos analisar demandas profissionais e indicadores de empregabilidade da cidade.
- **Isolamento Multitenant Rigoroso:** Empresas e recrutadores visualizam exclusivamente suas próprias vagas, pipeline de candidatos e dados de triagem, bloqueando qualquer acesso cruzado indevido.

### 3. Módulo de Gestão de Equipe & Recrutadores
- **Hierarquia Corporativa:** Perfis de **Administrador da Empresa** e **Recrutador/RH** com granularidade de permissões.
- **Controle de Membros:** Adição de novos usuários por e-mail, alternância imediata de funções (*Promover Admin / Rebaixar RH*) e suspensão/reativação em 1 clique.
- **Transferência Segura de Vagas:** Ao remover um colaborador, o sistema oferece transferência automática de todas as vagas publicadas para o administrador, garantindo 100% de integridade referencial.

### 4. Geolocalização & Mapa de Distribuição (LGPD Compliant)
- **Filtro de Vagas por Proximidade (KM):** O candidato pode ordenar oportunidades pela proximidade física de sua residência e filtrar por raio geográfico configurável.
- **Mapa Espacial Interativo (Leaflet.js):** O painel da empresa plota a sede corporativa (pin azul) e a distribuição dos candidatos inscritos (pins verdes) a partir de CEPs aproximados, respeitando rigorosamente a privacidade e as diretrizes da LGPD.

### 5. Central de Notificações em Tempo Real (Sininho)
- Sininho interativo na barra de navegação com contador dinâmico de não lidas.
- Disparo automático de notificações para:
  - Novas mensagens no chat interno entre recrutador e candidato.
  - Atualizações de status da candidatura (*Em Análise*, *Aprovado*, *Finalizado*).
  - Novas inscrições recebidas pela empresa.
  - Vínculo de novos membros à equipe corporativa.
- Ações rápidas assíncronas (*Marcar todas como lidas*) via Fetch API e endpoints dedicados.

### 6. Design System Peerlist & Responsividade Mobile
- **Design Tokens Customizados:** Paleta inspirada no visual moderno do Peerlist (tons sutis de cinza, verde vibrante `#00aa45`, microinterações e cards com sombras suaves).
- **Apresentação Dual Responsiva:** Tabelas densas de desktop alternam suavemente para cartões compactos (*touch-friendly cards*) em smartphones (360px a 480px).
- **Menu Hambúrguer Offcanvas:** Navegação móvel fluida com barra de busca, perfil do usuário e atalhos completos para todas as áreas do sistema.
- **Tipografia Justificada:** Textos descritivos de vagas e qualificações justificados com hifenização suave (`hyphens: auto; text-justify: inter-word`).

---

## 📂 Estrutura de Diretórios

```
PHP - INTALENTI VAGAS MVC/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # VagaController, EmpresaController, CurriculoController, ChatController...
│   │   ├── Middleware/        # IdentifyPortal, CheckRole, CheckCandidatoProfile...
│   │   └── Requests/          # VagaRequest, CurriculoRequest, EmpresaRequest...
│   ├── Models/                # User, Vaga, Empresa, Candidatura, Candidato, ChatMessage, Notificacao...
│   ├── Scopes/                # PortalScope (Isolamento Multi-tenant no Eloquent)
│   └── Services/              # GeoService (BrasilAPI + Nominatim + Haversine + Cache)
│
├── database/
│   ├── migrations/            # Migrations versionadas estruturadas e normalizadas
│   ├── seeders/               # DatabaseSeeder com dados realistas para testes imediatos
│   └── factories/             # Model Factories para testes unitários e de integração
│
├── resources/
│   ├── views/                 # Blade templates organizados (layouts, partials, dashboard, vagas, empresa...)
│   ├── css/ & js/             # Assets e scripts integrados via Vite
│
├── routes/
│   ├── web.php                # Rotas da aplicação organizadas por grupos e middlewares de perfil
│   └── console.php            # Comandos artisan e automações de manutenção
│
├── tests/
│   ├── Feature/               # SecurityIntegrationTest, VagasFlowTest (35 testes / 177 asserções)
│   └── Unit/                  # SecurityValidationTest, ExampleTest
│
├── artisan                    # CLI do ecossistema Laravel
├── composer.json              # Dependências e autoload PSR-4
└── phpunit.xml                # Configurações do ambiente de testes automatizados
```

---

## 🧪 Qualidade de Código & Suíte de Testes Automatizados

A integridade do sistema é garantida por uma suíte completa de **35 testes automatizados** (177 asserções) cobrindo regras de negócio críticas e camadas de segurança:

```bash
php artisan test
```

```
   PASS  Tests\Unit\SecurityValidationTest
  ✓ user primeiro nome attribute                                                               0.10s
  ✓ user role helper methods                                                                   0.01s
  ✓ cpf validation algorithm                                                                   0.01s
  ✓ cnpj validation algorithm                                                                  0.01s
  ✓ prepare for validation sanitization                                                        0.01s

   PASS  Tests\Feature\SecurityIntegrationTest
  ✓ database enforces unique cpf constraint                                                    0.02s
  ✓ database enforces unique cnpj constraint                                                   0.01s
  ✓ database allows multiple null values                                                       0.02s
  ✓ company isolation on vacancies index                                                       0.03s
  ✓ company cannot edit or update other company vacancy                                        0.03s
  ✓ company cannot access or screen candidacies of other companies                             0.02s
  ✓ prefecture isolation by municipality                                                       0.07s
  ✓ candidate sees all active vacancies                                                        0.02s
  ✓ candidate cannot access company management endpoints                                       0.02s
  ✓ chat message creates notification for interlocutor                                         0.02s
  ✓ notification bell can mark single and all as read                                          0.02s
  ✓ candidate can sort vacancies by proximity and filter by radius                             0.03s
  ✓ company dashboard renders candidate distribution map with approximate cep                  0.09s
  ✓ prefeitura registration flow and dashboard isolation                                       0.10s
  ✓ company team management module                                                             0.08s

   PASS  Tests\Feature\VagasFlowTest
  ✓ can view vagas list                                                                        0.02s
  ✓ candidate registration creates profile                                                     0.02s
  ✓ cep api returns json                                                                       0.16s
  ✓ eliminatory question blocks application with message                                       0.02s
  ✓ creating vaga sanitizes salary values properly                                             0.02s
  ✓ candidate cannot start chat before recruiter                                               0.02s
  ✓ recruiter can view candidate profile and update status                                     0.19s
  ✓ registration rejects duplicate cpf                                                         0.02s
  ✓ registration rejects duplicate cnpj                                                        0.02s
  ✓ empresa only sees its own vacancies on index                                               0.02s
  ✓ candidato sees all active vacancies on index                                               0.02s
  ✓ prefeitura only sees vacancies of its city on index                                        0.02s
  ✓ empresa cannot view candidate profile not applied to its vacancies                         0.02s

  Tests:    35 passed (177 assertions)
  Duration: 1.79s
```

---

## 🚀 Instalação e Execução Local

### Pré-requisitos
- **PHP 8.2+** com extensões ativas: `pdo_mysql`, `mbstring`, `openssl`, `curl`.
- **Composer 2.x**.
- **MySQL 8.x** ou **MariaDB 10.x**.

### Passo a Passo

1. **Clonar o Repositório e Acessar o Diretório:**
   ```bash
   git clone https://github.com/ADM-448/Projetos.git
   cd "Projetos/PHP - INTALENTI VAGAS MVC"
   ```

2. **Instalar Dependências via Composer:**
   ```bash
   composer install
   ```

3. **Configurar o Ambiente (`.env`):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Ajuste as credenciais do MySQL no arquivo `.env` gerado:*
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=vagas_laravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Executar as Migrations e Alimentar Dados (Seed):**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Criar Symlink de Storage:**
   ```bash
   php artisan storage:link
   ```

6. **Iniciar o Servidor Local:**
   ```bash
   php artisan serve
   ```
   Acesse a aplicação no navegador em: **`http://localhost:8000`**

---

## 🔑 Credenciais de Teste Disponíveis (DatabaseSeeder)

| Perfil | E-mail | Senha Padrão | Escopo de Acesso |
| :--- | :--- | :--- | :--- |
| **Candidato** | `candidato@intalenti.com.br` | `password` | Vagas, Filtro por KM, Currículo, Candidaturas, Chat |
| **Empresa (RH / Admin)** | `rh@techsolutions.com.br` | `password` | Dashboard Empresa, Mapa de Candidatos, Vagas, Gestão de Equipe |
| **Empresa (Recrutador)** | `recrutador@techsolutions.com.br` | `password` | Publicação de Vagas, Triagem e Chat com Inscritos |
| **Prefeitura (Gestor Público)** | `prefeitura@maringa.pr.gov.br` | `password` | Observatório Municipal da Cidade de Maringá |
| **Super Administrador** | `admin@intalenti.com.br` | `password` | Gestão Global de Portais e Empresas Aprovadas |

---

## 👨‍💻 Autoria e Licença

Desenvolvido por **[ADM-448](https://github.com/ADM-448)**  
*Engenharia de Software, Arquitetura Multi-Tenant & Soluções Escaláveis em PHP / Laravel.*
