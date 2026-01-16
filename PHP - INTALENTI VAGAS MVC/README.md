# 💼 InTalenti Vagas - Portal Multi-Tenant de Vagas e Oportunidades (PHP MVC)

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Architecture](https://img.shields.io/badge/Architecture-MVC%20Multi--Tenant-blue?style=for-the-badge)](#)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

O **InTalenti Vagas** é uma plataforma web completa em **PHP Nativo com Arquitetura MVC (Model-View-Controller)** projetada para gestão, divulgação e candidatura a vagas de emprego com suporte nativo a **Multi-tenancy** (múltiplas cidades, prefeituras ou parceiros operando de forma isolada e personalizada).

---

## 🚀 Principais Módulos & Recursos

### 1. Multi-Tenancy Dinâmico (Cidades / Portais)
- Isolamento contextual de vagas e candidatos por portal/cidade contratante.
- Customização visual de identidade e logotipo por prefeitura/entidade parceira.

### 2. Gestão de Oportunidades & Candidaturas
- Cadastro, publicação e expiração de vagas por categorias profissionais, senioridade e modelo de trabalho (Presencial, Híbrido, Remoto).
- Formulário intuitivo de candidatura com upload seguro de currículos e dados de contato.
- Painel do Candidato e área de triagem de currículos para recrutadores.

### 3. Painel Administrativo com Analytics
- Dashboard analítico integrado com **Chart.js** exibindo métricas de acessos, vagas abertas, candidaturas recebidas e taxa de conversão.
- Mapeamento geográfico de oportunidades e polos de emprego utilizando **Leaflet.js**.

### 4. Arquitetura e Engenharia de Software
- **Padrão MVC Estruturado:** Separação estrita de responsabilidades (`Controllers`, `Models`, `Views`, `Middlewares`).
- **Autoload PSR-4:** Resolução de namespaces padronizada via Composer.
- **Conexão Segura:** Singleton com PDO, Prepared Statements e proteção ativa contra SQL Injection e CSRF.

---

## 🛠️ Stack Tecnológica

| Componente | Tecnologia |
| :--- | :--- |
| **Linguagem Principal** | PHP 7.4+ / PHP 8.x (Sem dependência de frameworks pesados) |
| **Padrão de Projeto** | MVC (Model-View-Controller) com Autoload PSR-4 |
| **Banco de Dados** | MySQL 5.7+ / MariaDB 10.x+ (PDO) |
| **Frontend** | HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.x |
| **Gráficos & Mapas** | Chart.js, Leaflet.js |
| **Servidor Web** | Apache (com `.htaccess` / `mod_rewrite`) ou PHP Built-in Server |

---

## ⚙️ Guia de Instalação e Execução Local

### Pré-requisitos
- PHP 7.4 ou superior com extensão `pdo_mysql` habilitada.
- Composer instalado.
- Banco de dados MySQL / MariaDB ativo.

### Passo a Passo

1. **Acessar a pasta do projeto:**
   ```bash
   cd "PHP - INTALENTI VAGAS MVC"
   ```

2. **Instalar dependências (Autoloader Composer):**
   ```bash
   composer install
   ```

3. **Configurar as Variáveis de Ambiente:**
   ```bash
   cp .env.example .env
   ```
   Edite o arquivo `.env` com suas credenciais do MySQL:
   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=intalenti_vagas
   DB_USER=root
   DB_PASS=
   ```

4. **Executar as Migrations / Estrutura do Banco:**
   Importe as tabelas presentes na pasta `migrations/` no seu banco de dados MySQL:
   ```bash
   mysql -u root -p intalenti_vagas < migrations/schema.sql
   ```
   *(Ou execute os scripts SQL disponibilizados no diretório `migrations/`)*

5. **Iniciar a Aplicação:**
   - **Via Apache (XAMPP):** Acesse diretamente `http://localhost/Projetos/PHP - INTALENTI VAGAS MVC/public`
   - **Via PHP Built-in Server:**
     ```bash
     php -S localhost:8000 server.php
     ```
     Acesse no navegador: `http://localhost:8000`.

---

Desenvolvido por **ADM-448**.
