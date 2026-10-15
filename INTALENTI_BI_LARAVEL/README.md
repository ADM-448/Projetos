# 📊 InTalenti BI - Plataforma de Business Intelligence Salarial, Benchmarking & Planos de Cargos

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Chart.js](https://img.shields.io/badge/Chart.js-Analytics-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)](https://www.chartjs.org/)

O **InTalenti BI** é uma solução corporativa de **Inteligência de Mercado e Gestão da Remuneração** construída sobre o ecossistema Laravel 11. O sistema permite que analistas de RH e gestores estratégicos importem bases salariais internas, realizem auditoria de equidade salarial por cargo/gênero/senioridade, executem **benchmarking automatizado contra salários de mercado** e elaborem Planos Estruturados de Cargos e Salários e Convenções Coletivas.

---

## 🚀 Principais Funcionalidades

### 1. Dashboard Executivo & Analytics de Remuneração
- Visão holística da folha salarial: folha total, salário médio, mediana salarial e dispersão por departamento/nível.
- Análise de equidade e gap salarial (gênero, tempo de casa e faixas de remuneração).
- Gráficos analíticos interativos de quartis salariais (Q1, Mediana, Q3).

### 2. Motor de Importação e Normalização de Dados (Excel / CSV)
- Importação flexível de grandes planilhas salariais via `ImportController` com validação de consistência e mapeamento de colunas.
- Template oficial de importação pré-formatado (`Modelo_Importacao_BI.xlsx`).

### 3. Benchmarking de Salários de Mercado & Web Scraping
- Cruzamento automatizado do salário interno com faixas médias praticadas pelo mercado (`MarketSalary`).
- Jobs em segundo plano e comandos agendados (`UpdateWeeklyMarketSalaries`, `ScrapeMarketSalaryJob`) para monitoramento contínuo de oscilações salariais no mercado de trabalho.

### 4. Módulo de Planos de Cargos & Convenções Coletivas
- Simulação de planos de carreira com tabelas de níveis e progressão salarial (`PlanoCargosController`).
- Mapeamento e controle de pisos e reajustes sindicais por convenção coletiva (`ConvencaoController`).

---

## 📈 Histórico de Evolução (Changelog)

| Versão | Marco | Entregas Técnicas |
| :---: | :---: | :--- |
| **`v1.0.0`** | Agosto/2026 | **Lançamento Inicial da Plataforma:**<br>• Dashboard de remuneração e gráficos analíticos de dispersão.<br>• Motor de importação em lote de planilhas salariais (Excel/CSV).<br>• Comandos agendados e Jobs para coleta/cálculo de média salarial de mercado. |
| **`v2.0.0`** | Outubro/2026 | **Arquitetura Refatorada & Planos de Cargos:**<br>• Introdução do módulo de Planos de Cargos e Salários (`PlanoCargosController`).<br>• Gestão de Convenções Coletivas de Trabalho (`ConvencaoController`).<br>• Refatoração completa da camada de relatórios e exportações.<br>• Interface moderna redesenhada com TailwindCSS e componentes Blade modulares. |

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8.2+, Laravel 11.x, Eloquent ORM, Jobs & Queues (Queue Workers)
- **Frontend:** Blade Templates, TailwindCSS, Chart.js, Alpine.js
- **Banco de Dados:** MySQL 8.0+ / MariaDB
- **Manipulação de Planilhas:** PhpSpreadsheet / Maatwebsite Excel

---

## ⚙️ Guia de Instalação e Execução Local

### Pré-requisitos
- PHP 8.2+ com extensões `pdo_mysql`, `fileinfo`, `gd`, `zip` habilitadas.
- Composer 2.x
- Node.js 18+ e NPM
- MySQL ativo

### Passo a Passo

1. **Acessar a pasta do projeto:**
   ```bash
   cd INTALENTI_BI_LARAVEL
   ```

2. **Instalar dependências PHP e JS:**
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
   DB_DATABASE=intalenti_bi
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Executar Migrations e Seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Compilar os Assets de Frontend:**
   ```bash
   npm run build # ou npm run dev
   ```

7. **Iniciar o Servidor:**
   ```bash
   php artisan serve
   ```
   Acesse no navegador: `http://localhost:8000`.

---

Desenvolvido por **ADM-448**.
