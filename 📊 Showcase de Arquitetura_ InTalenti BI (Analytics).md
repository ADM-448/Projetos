# **📊 Laravel BI Architecture Showcase — InTalenti BI (Analytics & Remuneração)**

Bem-vindo ao **Showcase de Arquitetura de Software** do **InTalenti BI**, uma plataforma corporativa desenvolvida em **Laravel 11** para Inteligência Salarial, Benchmarking de Mercado em Tempo Real e Gestão Estratégica de Planos de Cargos e Salários.

> **📂 Código no Repositório:** Disponível neste monorepo na pasta **[`INTALENTI_BI_LARAVEL/`](./INTALENTI_BI_LARAVEL)**.

---

## **📂 1. Engenharia de Dados & Arquitetura de Remuneração**

O **InTalenti BI** resolve o desafio de normalizar, cruzar e analisar grandes volumes de dados salariais dispersos em planilhas legadas de RH:

- **Motor de Ingestão em Lote (Excel/CSV):** Leitura de planilhas complexas com sanitização cambial (`BrazilianCurrencyParser`) e normalização fonética/textual de títulos de cargos (`CargoNormalizerService`, `StringNormalizer`).
- **Coleta de Mercado Assíncrona (Scraping & Queues):** Jobs agendados (`ScrapeMarketSalaryJob`, `UpdateWeeklyMarketSalaries`) que coletam e agregam faixas salariais reais do mercado de trabalho por região e nível de senioridade.
- **Simulação de Planos de Carreira:** Módulo dedicado (`PlanoCargosController`) que projeta o impacto financeiro de reajustes na folha e enquadramento em tabelas de níveis salariais.
- **Auditoria de Convenções Coletivas:** Controle de pisos sindicais e dissídios com validação automática de conformidade jurídica (`ConvencaoController`).

### **🌳 Estrutura Real de Diretórios**

```
INTALENTI_BI_LARAVEL/
├── app/
│   ├── Http/Controllers/
│   │   ├── SalaryDashboardController.php # Métricas centrais, quartis e dispersão
│   │   ├── BenchmarkController.php       # Comparativo interno vs mercado
│   │   ├── PlanoCargosController.php     # Projeções de planos e carreiras
│   │   ├── ConvencaoController.php       # Regras e pisos de acordos coletivos
│   │   └── ImportController.php          # Pipeline de importação de planilhas
│   │
│   ├── Services/
│   │   ├── MarketSalary/                 # Provedores de mercado (Salario.com.br, APIs)
│   │   ├── BrazilianCurrencyParser.php   # Parser robusto de moeda BRL/USD
│   │   ├── CargoNormalizerService.php    # Normalização semântica de cargos
│   │   └── SpreadsheetParserService.php  # Leitura e validação de dados em lote
│   │
│   └── Jobs/
│       └── ScrapeMarketSalaryJob.php     # Worker em fila para atualização de salários
│
└── resources/views/
    ├── dashboard.blade.php               # Dashboard executivo com Chart.js
    ├── partials/dashboard/tabs/          # Módulos: Financeiro, Mercado, Cenários, Dissídios
    └── reports/                          # Relatórios em PDF executivos para diretoria
```

---

Desenvolvido por **ADM-448**.
