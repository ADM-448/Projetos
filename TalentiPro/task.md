# Tarefas: Módulos DISC e Pesquisa de Clima

Este arquivo rastreia o progresso da implementação dos novos módulos de acordo com o plano aprovado.

## Configurações Iniciais
- `[x]` Instalar e configurar **Alpine.js** e **Chart.js** via NPM (Vite).
# Tarefas: Módulos DISC e Pesquisa de Clima

Este arquivo rastreia o progresso da implementação dos novos módulos de acordo com o plano aprovado.

## Configurações Iniciais
- `[x]` Instalar e configurar **Alpine.js** e **Chart.js** via NPM (Vite).
  - `[x]` `ClimateSurvey` e `ClimateQuestion` (configuração da pesquisa).
  - `[x]` `ClimateResponse` (anônima: `company_id`, `department_id`, `question_id`, notas).
  - `[x]` `ClimateSurveyParticipation` (controle de unicidade: `user_id`, `climate_survey_id`, `has_submitted`).
  - `[x]` `ClimateSurveyAuditLog` (registro auditoria: `company_id`, `survey_id`, `user_id`, `department_id`, timestamp).
- `[x]` **Serviços**: Criar `ClimateSurveyService` para tabulação de dados e eNPS.
- `[x]` **Controladores**: 
  - `[x]` Criar o Job `GerarPdiClimaJob`
  - `[x]` Implementar a lógica de salvar PDI, Ações e Notificações no Job
  - `[x]` Atualizar o controller `ClimateSurveyController` para despachar o Job
  - `[x]` Atualizar o frontend em `relatorio.blade.php` para tratar o feedback de loading assíncrono
- `[x]` **Frontend (Telas)**:
  - `[x]` Tela do colaborador para responder (anônima, design intuitivo).
  - `[x]` Tela do RH consolidada com Gráficos **Chart.js** (Métricas eNPS).
- `[x]` **Testes**: Validação de submissão dupla e garantia de anonimato no `ClimateResponse`.
