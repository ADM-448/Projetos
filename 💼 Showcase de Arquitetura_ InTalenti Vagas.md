# **💼 Plataforma de Recrutamento & Gestão de Vagas — InTalenti Vagas (Enterprise Multi-Tenant)**

Bem-vindo ao **Showcase de Engenharia de Software** do **InTalenti Vagas**, uma plataforma corporativa multi-tenant para divulgação de vagas, triagem de candidatos, observatório municipal de empregabilidade e gestão estratégica de processos seletivos desenvolvida sob **PHP 8.2+ e Arquitetura Laravel 12 Enterprise**.

> **🌐 Projeto em Produção:** Acesse o portal em **[https://intalenti.com.br/vagas/](https://intalenti.com.br/vagas/)**  
> **📂 Código no Repositório:** Disponível neste monorepo na pasta **[`PHP - INTALENTI VAGAS MVC/`](./PHP%20-%20INTALENTI%20VAGAS%20MVC)**.

---

## **🏛️ 1. Filosofia de Arquitetura & Modernização**

O **InTalenti Vagas** exemplifica a maturidade de engenharia de software ao demonstrar tanto o domínio profundo de arquitetura web em PHP puro quanto a transição para um ecossistema corporativo completo em **Laravel 12**, implementando as melhores práticas da indústria (**PSR-12**, **Clean Architecture**, **Eloquent ORM**, **Service Pattern**, **Form Requests**, **Blade Components**, **Multi-Tenancy** e **Testes Automatizados com 100% de aprovação**):

- **Isolamento Multi-Tenant por Domínio/Subdomínio:** Middleware dedicado `IdentifyPortal` com cache em memória e **Global Scopes** do Eloquent (`PortalScope`), garantindo que dados de portais municipais e empresas parceiras sejam mutuamente isolados sem risco de vazamento de dados.
- **Camada de Serviços & Geolocalização Resiliente:** `App\Services\GeoService` com fallback triplo (BrasilAPI -> ViaCEP -> Nominatim OSM), cálculo de distância por fórmula de Haversine e cache automático de 30 dias por CEP.
- **Segurança & Conformidade:** Validação estrita de unicidade e algoritmo para CPF e CNPJ, sanitização monetária, bloqueio preventivo de candidaturas com perfil incompleto e perguntas eliminatórias que barram imediatamente respostas em desacordo com os requisitos obrigatórios.

### **🌳 Estrutura Real de Diretórios**

```
PHP - INTALENTI VAGAS MVC/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # VagaController, EmpresaController, CurriculoController, ChatController...
│   │   ├── Middleware/        # IdentifyPortal, CheckRole, CheckCandidatoProfile...
│   │   └── Requests/          # Validações fortemente tipadas via Form Requests
│   ├── Models/                # User, Vaga, Empresa, Candidatura, ChatMessage, Notificacao...
│   ├── Scopes/                # PortalScope (Global Scope do Eloquent)
│   └── Services/              # GeoService (BrasilAPI + Nominatim + Haversine + Cache)
│
├── database/
│   ├── migrations/            # Migrations versionadas estruturadas e normalizadas
│   ├── seeders/               # DatabaseSeeder com perfis e dados prontos para homologação
│   └── factories/             # Model Factories para suíte de testes automatizados
│
├── resources/
│   ├── views/                 # Blade templates responsivos com Design System estilo Peerlist
│   └── css/ & js/             # Tokens de design e scripts integrados via Vite
│
├── routes/
│   ├── web.php                # Rotas organizadas por domínios e middlewares de autorização
│   └── console.php            # Comandos artisan e automações de manutenção
│
└── tests/
    ├── Feature/               # Testes de isolamento multitenant, regras de RH e segurança
    └── Unit/                  # Validação unitária de CPF/CNPJ, helpers e atributos
```

---

## **🎯 2. Diferenciais Técnicos de Destaque**

### **A. Multi-Tenancy para Prefeituras e Parceiros Privados**
- Capacidade de segmentar vagas, candidatos e identidades visuais por município contratante.
- Cada prefeitura ou entidade possui seu subdomínio ou portal exclusivo com **Observatório Municipal**, permitindo aos gestores públicos analisar demandas profissionais e indicadores de empregabilidade da cidade em tempo real.

### **B. Gestão Completa de Equipe da Empresa & Transferência de Vagas**
- Sistema de controle de acesso hierárquico (*Administrador da Empresa* vs *Recrutador/RH*).
- Permite adicionar colaboradores por e-mail, promover/rebaixar permissões e suspender/reativar acessos em 1 clique.
- Ao desvincular um colaborador, o sistema oferece transferência automática das vagas para o administrador da conta, prevenindo orfandade de dados e preservando o histórico seletivo.

### **C. Mapas Interativos com Leaflet.js & Busca por Proximidade (LGPD Compliant)**
- Cálculo de proximidade em tempo real (distância em KM) entre o endereço do candidato e o local da vaga.
- Painel corporativo com mapa de calor e distribuição espacial dos inscritos plotados por CEP aproximado, preservando o sigilo do logradouro exato em conformidade com as normas da LGPD.

### **D. Central de Notificações em Tempo Real (Sininho)**
- Notificações ativas para mensagens de chat, atualizações de status de candidatura, novas inscrições recebidas e movimentações de equipe.
- Leitura assíncrona com atualização em lote via Fetch API.

### **E. Design System Peerlist & Responsividade Mobile Total**
- Layout profissional com tipografia justificada e hifenização suave (`hyphens: auto; text-justify: inter-word`).
- Apresentação dual: tabelas completas para desktop e cartões táteis compactos (*touch-friendly cards*) para dispositivos móveis de 360px a 480px.
- Menu hambúrguer offcanvas moderno com busca integrada e controle de perfil.

---

## **🧪 3. Qualidade & Testes Automatizados (100% Passed)**

O projeto conta com **35 testes automatizados (177 asserções)** executados via PHPUnit cobrindo:
1. **Segurança de Dados:** Restrições de unicidade de CPF e CNPJ no banco de dados e validação algorítmica.
2. **Isolamento Multitenant:** Garantia de que empresas só acessam suas próprias vagas e candidatos, e prefeituras apenas as vagas de seu município.
3. **Fluxos de Recrutamento:** Bloqueio por perguntas eliminatórias, sanitização de salários, bloqueio de chat iniciado por candidato e integridade de transferências de equipe.

---

Desenvolvido por **[ADM-448](https://github.com/ADM-448)** • [intalenti.com.br/vagas](https://intalenti.com.br/vagas/)
