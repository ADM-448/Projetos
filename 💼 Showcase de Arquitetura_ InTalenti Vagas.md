# **💼 Plataforma de Recrutamento & Gestão de Vagas — InTalenti Vagas (PHP MVC Multi-Tenant)**

Bem-vindo ao **Showcase de Engenharia de Software** do **InTalenti Vagas**, uma plataforma corporativa multi-tenant para divulgação de vagas, triagem de candidatos e gestão de empregabilidade regional desenvolvida em **PHP Puro com Arquitetura MVC (Model-View-Controller)**.

> **🌐 Projeto em Produção:** Acesse o portal em **[https://intalenti.com.br/vagas/](https://intalenti.com.br/vagas/)**  
> **📂 Código no Repositório:** Disponível neste monorepo na pasta **[`PHP - INTALENTI VAGAS MVC/`](./PHP%20-%20INTALENTI%20VAGAS%20MVC)**.

---

## **🏛️ 1. Filosofia de Arquitetura: MVC Puro & Domínio Profundo de PHP**

Diferente de sistemas construídos sobre frameworks prontos, o **InTalenti Vagas** demonstra a capacidade de construir uma aplicação escalável, modular e robusta utilizando os **fundamentos da engenharia de software em PHP**:

- **Roteamento & Dispatcher Proprietário:** Sistema central de rotas (`src/Core/Core.php`) que intercepta URLs amigáveis, mapeia parâmetros via regex e despacha para o controlador correspondente.
- **Autoloading PSR-4:** Estrutura orientada a namespaces gerenciada pelo Composer.
- **Camada de Dados Segura (Singleton PDO):** Conexão única via design pattern Singleton (`src/Config/Database.php`), consultas 100% preparadas (*Prepared Statements*) e sanitização contra SQL Injection e XSS.
- **Camada de Apresentação Modular:** Views em PHP nativo desacopladas com layouts mestres reutilizáveis (`src/Views/layouts/master.php`).

### **🌳 Estrutura Real de Diretórios**

```
PHP - INTALENTI VAGAS MVC/
├── public/
│   ├── index.php                  # Front Controller único da aplicação
│   ├── assets/ (css, js, img)
│   └── uploads/                   # Upload seguro de currículos (PDF) e logos
│
├── src/
│   ├── Config/
│   │   └── Database.php           # Singleton PDO com conexão persistente
│   ├── Core/
│   │   ├── Core.php               # Motor de roteamento e dispatching
│   │   ├── Controller.php         # Base Controller com helpers de renderização
│   │   └── Cache.php              # Cache local de queries frequentes
│   │
│   ├── Controllers/
│   │   ├── VagasController.php    # Gestão de vagas, filtros e detalhes
│   │   ├── CandidaturaController.php # Fluxo de inscrição e upload de CV
│   │   ├── DashboardController.php   # Métricas de recrutadores com Chart.js
│   │   └── PrefeituraController.php  # Portal regional da cidade/tenant
│   │
│   ├── Models/
│   │   ├── Vaga.php               # Consultas com índices Full-Text
│   │   ├── CandidatoProfile.php
│   │   └── Candidatura.php
│   │
│   └── Utils/
│       ├── GeoUtils.php           # Cálculo de proximidade geográfica
│       ├── Validator.php          # Motor de validação e sanitização
│       └── Mailer.php             # Disparador de notificações
│
└── migrations/                    # Scripts SQL de schema e índices
```

---

## **🎯 2. Diferenciais Técnicos de Destaque**

### **A. Multi-Tenancy para Prefeituras e Parceiros**
- Capacidade de segmentar vagas, candidatos e identidades visuais por município contratante.
- Cada prefeitura ou entidade possui seu subdomínio ou portal exclusivo, garantindo personalização de marca e isolamento institucional.

### **B. Mapas com Leaflet.js & Busca por Geolocalização**
- Cálculo de distância e mapeamento visual de polos de emprego na cidade utilizando a biblioteca open-source **Leaflet.js** e o módulo utilitário `GeoUtils.php`.

### **C. Painel de Analytics com Chart.js**
- Visualização em tempo real de acessos, taxas de conversão de vagas e distribuição de candidatos por senioridade e área de atuação.

---

Desenvolvido por **ADM-448** • [intalenti.com.br/vagas](https://intalenti.com.br/vagas/)
