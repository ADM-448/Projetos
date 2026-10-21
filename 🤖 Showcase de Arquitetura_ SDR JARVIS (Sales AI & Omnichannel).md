# **🤖 Laravel AI Architecture Showcase — SDR JARVIS (Sales AI & Omnichannel)**

Bem-vindo ao **Showcase de Arquitetura de Software** do **SDR JARVIS**, uma plataforma corporativa desenvolvida em **Laravel 11** para Prospecção Ativa B2B, Qualificação Autônoma de Leads com IA Generativa e Gestão Omnichannel de Contatos em Tempo Real.

> **📂 Código no Repositório:** Disponível neste monorepo na pasta **[`LARAVEL - SDR JARVIS/`](./LARAVEL%20-%20SDR%20JARVIS)**.

---

## **📂 1. Engenharia do Agente Autônomo & Comunicação Multicanal**

O **SDR JARVIS** opera como um copiloto e agente inteligente de vendas, combinando modelos de linguagem com automação de infraestrutura:

- **Motor de Conversação Autônomo com IA:** Respostas contextuais em tempo real, quebra de objeções e agendamento de reuniões com executivos de vendas (`AiAgentController`, `JarvisCopilotChat.vue`).
- **Enriquecimento Corporativo de Dados (B2B):** Consulta automática de dados tributários e corporativos por CNPJ/domínio corporativo via `CompanyEnrichmentController`.
- **Motor de Cadências Outbound:** Sequências de prospecção com múltiplos pontos de contato e intervalos parametrizados (`CadenceController`).
- **Hub Omnichannel Completo:**
  - **WhatsApp Multi-Instâncias:** Conexão de instâncias com controle de fila (`WhatsappInstanceController`, `WhatsappClientService`).
  - **Gmail OAuth Integrado:** Disparo e rastreamento de e-mails corporativos via OAuth 2.0 (`GmailOAuthController`).
  - **VoIP Integrado:** Disparo de chamadas e gravação via protocolo SIP/Asterisk com softphone web embutido (`VoipController`, `WebphoneWidget.vue`).
- **Arquitetura Multi-Tenant com Kanban:** CRM visual em tempo real com separação estrita de tenants (`BelongsToTenant`, `AdminTenantController`).

### **🌳 Estrutura Real de Diretórios**

```
LARAVEL - SDR JARVIS/
├── app/
│   ├── Http/Controllers/
│   │   ├── AiAgentController.php           # Motor do agente autônomo JARVIS
│   │   ├── KanbanController.php            # Funil de vendas e movimentação de cards
│   │   ├── CadenceController.php           # Sequenciador de réguas outbound
│   │   ├── CompanyEnrichmentController.php # Enriquecimento de dados de empresas
│   │   ├── VoipController.php              # Chamadas SIP e log de ligações
│   │   └── WhatsappInstanceController.php  # Gestão de conexões WhatsApp
│   │
│   ├── Services/
│   │   ├── Ai/                             # Agente LLM, prompts e parser de contexto
│   │   ├── Voip/AsteriskAmiService.php     # Integração com central telefônica PBX
│   │   ├── WhatsappClientService.php       # Gateway de mensageria
│   │   └── EnrichmentService.php           # Consulta de bases de CNPJ
│   │
│   └── Traits/
│       └── BelongsToTenant.php             # Isolamento seguro multi-inquilino
│
└── resources/js/
    ├── Components/
    │   ├── Ai/JarvisCopilotChat.vue        # Interface de chat com IA
    │   ├── Voip/WebphoneWidget.vue         # Softphone VoIP no navegador
    │   └── Whatsapp/                       # Conexão QR Code multi-instância
    └── Pages/Kanban/Index.vue              # Quadro Kanban de oportunidades
```

---

Desenvolvido por **ADM-448**.
