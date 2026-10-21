# **🏗️ Laravel SaaS Architecture Showcase — TalentiPro (talentipro.com.br)**

Bem-vindo ao meu **Showcase de Arquitetura de Software**. Este documento detalha a engenharia, os design patterns e as decisões arquiteturais aplicadas no desenvolvimento do **TalentiPro**, uma plataforma corporativa de Gestão Estratégica de Pessoas, Avaliações 360°, Mapeamento Comportamental DISC e Inteligência Artificial.

> **🌐 Projeto em Produção:** Acesse a aplicação ao vivo em **[https://talentipro.com.br](https://talentipro.com.br)**  
> **🔒 Repositório do Core:** O código-fonte integral com versionamento cronológico (releases `v1.0.0` a `v4.0.0`) está hospedado no repositório privado **[ADM-448/TalentiPro](https://github.com/ADM-448/TalentiPro)**.

---

## **📂 1. Padrão Arquitetural: Service Layer & Clean Architecture**

Para assegurar alta manutenibilidade e evitar o anti-pattern *"Fat Controllers"*, o backend do TalentiPro foi desenhado com estrita separação de responsabilidades:

- **Controllers:** Focados estritamente no fluxo HTTP (Clean Controllers) — recebem a requisição, delegam validação para `FormRequests`, acionam o serviço apropriado e devolvem views ou respostas JSON.
- **Service Layer:** Centraliza todas as regras de negócio complexas, cálculos de matriz e integrações externas (`AvaliacaoService`, `CalculateDiscProfileService`, `GeminiAudioService`, `GeminiTextService`, `WhatsAppService`).
- **Events & Asynchronous Jobs:** Desacoplamento de tarefas pesadas (transcrição de reuniões, geração preditiva de PDI por IA, disparo de mensagens e relatórios em PDF) via filas Redis/Database (`ShouldQueue`).

### **🌳 Estrutura Real de Diretórios**

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AvaliacaoController.php          # Gerenciamento de ciclos e conciliação
│   │   ├── DiscAssessmentController.php     # Questionário e motor de cálculo DISC
│   │   ├── ConsultorContextController.php   # Alternância multi-tenant de empresas
│   │   └── ReuniaoFeedbackController.php    # Transcrição e análise de reuniões
│   ├── Requests/                            # Validações profundas e sanitização
│   └── Middleware/
│       ├── EnsureUserBelongsToEmpresa.php   # Isolamento multi-tenant
│       └── CapturaIntendedUrl.php
│
├── Services/
│   ├── AvaliacaoService.php                 # Regras de notas ponderadas e gaps
│   ├── CalculateDiscProfileService.php      # Algoritmo de intensidades DISC
│   ├── GeminiAudioService.php               # Transcrição e análise de áudio (LLM)
│   ├── GeminiTextService.php                # Geração autônoma de PDI com IA
│   └── AI/                                  # Multi-provider de IA (Gemini, DeepSeek, OpenAI)
│
├── Models/
│   ├── Traits/BelongsToEmpresa.php          # Global Scopes de isolamento tenant
│   ├── Avaliacao.php
│   ├── DiscAssessment.php
│   └── CargoQuestaoIa.php                   # Perguntas contextuais geradas por IA
│
└── Jobs/
    ├── GerarPdiAvaliacaoJob.php             # Processamento assíncrono em fila
    └── ProcessAudioTranscriptionJob.php
```

---

## **🎯 2. Destaques Técnicos de Engenharia**

### **A. Algoritmo Comportamental DISC**
- Motor de cálculo em PHP que processa as escolhas mais/menos aderentes do colaborador e calcula as intensidades normalizadas dos fatores **Dominância (D), Influência (I), Estabilidade (S) e Conformidade (C)**.
- Geração de matriz de equipe (*Team Grid*) para análise de liderança e compatibilidade de pares.

### **B. Pipeline de IA com Google Gemini**
- **Transcrição de Feedbacks 1:1:** O áudio capturado é enviado via streaming à API multimodal do Google Gemini (`GeminiAudioService`), gerando transcrição literal, resumo executivo, identificação de sentimentos e plano de ação sugerido.
- **PDI Inteligente:** Análise dos gaps de competência com nota abaixo do esperado para sugestão automática de metas SMART e cursos de capacitação.

### **C. Isolamento Multi-Tenant Estrito**
- Proteção a nível de banco via `BelongsToEmpresa` (trait com `addGlobalScope`), impedindo vazamento de dados entre empresas clientes (*Cross-Tenant Leakage*).
- Suporte a **Consultores Multi-Empresa**, permitindo que especialistas em RH transitem entre múltiplos tenants clientes sem a necessidade de múltiplos logins.

---

Desenvolvido por **ADM-448** • [talentipro.com.br](https://talentipro.com.br)
