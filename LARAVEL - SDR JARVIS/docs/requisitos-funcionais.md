# Requisitos Funcionais da Plataforma (SDR JARVIS)

Este documento descreve detalhadamente todos os **Requisitos Funcionais (RF)** da plataforma **SDR JARVIS**, categorizados por módulos de negócio.

---

## 👥 Atores do Sistema

* **SDR (Sales Development Representative)**: Usuário operacional responsável por prospectar, executar cadências, enviar mensagens via WhatsApp, disparar e-mails, realizar ligações telefônicas, registrar desfechos e agendar reuniões com decisores.
* **Gestor Comercial / Head de Vendas**: Usuário com visão estratégica sobre a produtividade do time, definição de pipelines, criação de cadências oficiais, monitoramento de metas e análise de conversões.
* **Administrador do Tenant**: Responsável pela configuração das instâncias de WhatsApp, conexão com contas corporativas do Google Workspace, gestão de usuários e importação inicial de bases de leads.
* **Sistema (JARVIS Engine)**: Robô/Scheduler responsável por avançar passos de cadência, gerar tarefas de follow-up, calcular atrasos e consolidar métricas analíticas.

---

## 🏢 Módulo 1: Autenticação, Multi-Tenancy e Perfis de Acesso

### [RF-001] Autenticação e Sessão de Usuários (Login e Logout)
* **Descrição**: O sistema possui autenticação segura via credenciais (E-mail e Senha) com proteção de rotas via middleware `auth`.
* **Regras de Negócio**:
  * Usuários não autenticados são automaticamente redirecionados para a tela `/login`;
  * A tela de login segue a identidade visual **DESIGN.md** (Deep Ink `#130e30`, Hi-Yellow `#ffe228`) e disponibiliza cartões de acesso rápido em 1 clique para as contas demonstrativas de teste;
  * Usuários inativos (`is_active = false`) têm o acesso bloqueado com mensagem informativa;
  * Ao fazer login, a sessão inicializa o CRM padrão (`active_tenant_id`) associado ao usuário;
  * Botão de logout no rodapé da sidebar encerra a sessão com invalidação de token e redirecionamento para `/login`.

### [RF-002] Perfis de Acesso e Matriz de Permissões
O sistema opera com 3 papéis fundamentais de acesso:
1. **💼 SDR (Operador de Prospecção)**:
   * **Escopo**: Foco 100% no CRM da sua base.
   * **Permissões**: Acessa Funil Kanban, Empresas/Contas, Cadências, Templates de Scripts, Tarefas e Lembretes, Chat WhatsApp e E-mail.
   * **Restrições**: Não possui acesso a abas de gestão de equipe nem de administração de tenants. Não altera outros usuários.
2. **👔 Gestor Comercial (Manager da Base)**:
   * **Escopo**: Acessa o CRM da sua base e gerencia a equipe de colaboradores daquele tenant.
   * **Permissões**: Tudo que o SDR acessa + menu lateral exclusivo `👥 Minha Equipe`. Pode cadastrar novos colaboradores (SDRs e Gestores) na sua própria base, editar seus dados e senhas, e remover ou desativar colaboradores da equipe dele.
   * **Restrições**: Não tem acesso a dados de outros CRMs/tenants. Não pode criar novos CRMs nem alternar de tenant globalmente. Não pode criar ou excluir Super Admins.
3. **👑 Super Admin Global (Você)**:
   * **Escopo**: Acesso irrestrito a todos os CRMs da plataforma.
   * **Permissões**: Acessa o Seletor de Tenants (Dropdown) no topo da aplicação; cria novos perfis de CRM autônomos com IAs dedicadas (Nome, Persona, Prompt, Modelo GPT/Gemini/Claude e API Key); cadastra, delega e gerencia qualquer usuário em qualquer tenant do sistema; concede privilégio de Super Admin.

### [RF-003] Painel de Criação e Gestão de Perfis de CRM (Tenants & IA Dedicada)
* **Descrição**: Painel exclusivo para o Super Admin criar e configurar novas operações comerciais.
* **Regras de Negócio**:
  * Ao criar um novo Perfil de CRM, o sistema inicializa automaticamente o Pipeline Comercial padrão com 5 etapas Kanban ("Base Fria", "1º Toque", "Em Conversa", "Reunião Agendada", "Fechado / Ganho").
  * Cada CRM possui configurações dedicadas de IA: Nome do Agente SDR, Modelo de Linguagem (`gpt-4o-mini`, `gpt-4o`, `gemini-1.5-flash`, `claude-3-5-sonnet`), Persona Comercial, Prompt Inicial de Vendas e Chave de API customizada opcional.

### [RF-004] Alternância Dinâmica de Perfil de CRM (Tenant Switcher em 1 Clique)
* **Descrição**: Seletor no topo do sistema que permite ao Super Admin alternar instantaneamente entre qualquer CRM da plataforma. Para Gestores e SDRs, é exibida apenas uma pílula fixa informativa com a base comercial ativa.

### [RF-005] Delegação e Gestão de Colaboradores (Equipe da Base vs. Global)
* **Descrição**: Modal e formulário de gestão de membros da plataforma com escopo adaptativo:
  * Para o **Super Admin**: permite escolher qualquer CRM e atribuir qualquer papel (SDR, Gestor, Admin, Super Admin).
  * Para o **Gestor**: o campo de CRM é fixo na base dele e as opções de papel limitam-se a SDR ou Gestor. Não permite alterar usuários de outros tenants.

---

## 🏢 Módulo 2: Gestão de Contas (Empresas B2B) e Decisores

### [RF-004] Cadastro Completo de Contas B2B (Empresas)
* **Descrição**: O sistema deve armazenar os dados cadastrais da empresa prospectada.
* **Campos Suportados**:
  * Razão Social / Nome Fantasia (`name`);
  * CNPJ (`cnpj`);
  * Website / Domínio (`domain`);
  * Setor / Segmento de Atuação (`segment`);
  * Faixa de Funcionários (`employee_range`);
  * Faturamento Estimado (`estimated_revenue`);
  * Status da Negociação (`status`: `open`, `won`, `lost`, `unqualified`);
  * Motivo de Perda / Desqualificação (`lost_reason`);
  * SDR Responsável (`assigned_user_id`);
  * Etapa do Pipeline (`pipeline_stage_id`);
  * Data e Hora da Reunião Marcada (`meeting_at`).

### [RF-005] Vínculo de Múltiplos Decisores por Conta (Contatos)
* **Descrição**: Uma mesma empresa B2B pode conter múltiplos contatos e decisores vinculados (ex: Diretor de TI, Gerente de Compras, CEO).
* **Campos por Decisor**:
  * Nome Completo (`name`);
  * Cargo / Função (`job_title`);
  * Telefone / WhatsApp (`phone`);
  * E-mail Corporativo (`email`);
  * Perfil no LinkedIn (`linkedin_url`);
  * Flag de Decisor Primário (`is_primary`).

### [RF-006] Linha do Tempo Unificada de Atividades (Timeline da Conta)
* **Descrição**: Cada conta possui uma timeline cronológica reversa que consolida todas as interações do time de vendas.
* **Eventos Registrados**:
  * Mensagens enviadas e recebidas via WhatsApp;
  * E-mails enviados via Gmail;
  * Ligações telefônicas (com desfecho e notas);
  * Tarefas criadas, concluídas ou reagendadas;
  * Matrícula ou avanço em passos de cadências;
  * Alterações de etapa no pipeline Kanban;
  * Reuniões agendadas ou remarcadas.

---

## 📋 Módulo 3: Funil de Vendas (Kanban B2B) e Smart Views

### [RF-007] Visualização em Colunas Kanban
* **Descrição**: O SDR visualiza as contas organizadas em colunas que representam as etapas do pipeline de prospecção.
* **Regra de Negócio**: As etapas possuem ordem, cor de identificação e contagem automática de cards e valores.

### [RF-008] Movimentação Drag & Drop com Atualização Reativa
* **Descrição**: O usuário pode arrastar cards entre colunas para atualizar a etapa do lead no funil.
* **Regra de Negócio**: Ao soltar o card, o sistema atualiza via requisição assíncrona (`axios/Inertia`) e gera uma atividade de mudança de fase na timeline.

### [RF-009] Barra de Visões Rápidas em 1 Clique (Smart Views)
* **Descrição**: Barra de pílulas no topo do Kanban com contadores dinâmicos calculados em tempo real:
  1. `🔥 Todas as Contas`: exibe todas as contas ativas do tenant;
  2. `⚡ Em Cadência Ativa`: filtra contas matriculadas em réguas de prospecção ativas;
  3. `⚠️ Sem Contato > 3 Dias`: filtra contas sem mensagens, ligações ou notas registradas há mais de 72 horas;
  4. `📅 Reunião Agendada`: filtra contas que possuem data de reunião confirmada;
  5. `📌 Com Tarefas`: filtra contas com tarefas pendentes de execução;
  6. `👤 Minhas Contas`: filtra contas atribuídas exclusivamente ao SDR logado.

### [RF-010] Filtros Combinados e Busca Universal
* **Descrição**: O Kanban suporta filtros simultâneos por:
  * SDR Responsável (com opção "Sem responsável");
  * Segmento de Mercado (extraído dinamicamente das empresas cadastradas);
  * Status da Negociação (`Em Aberto`, `Ganho`, `Perdido`, `Desqualificado`);
  * Campo de busca universal (filtra em tempo real por razão social, nome do decisor, cargo ou telefone).

---

## 📌 Módulo 4: Gestão de Tarefas e Lembretes Operacionais

### [RF-011] Criação de Tarefas Vinculadas à Conta
* **Descrição**: O SDR pode agendar tarefas operacionais diretamente no card da empresa ou no painel geral.
* **Atributos da Tarefa**:
  * Título / Descrição da ação;
  * Tipo de Tarefa: Ligação Telefônica (`call`), WhatsApp (`whatsapp`), E-mail (`email`), Pesquisa / Social Selling (`research`), Reunião (`meeting`);
  * Prioridade: Baixa (`low`), Média (`medium`), Alta (`high`), Urgente (`urgent`);
  * Data e Hora de Vencimento (`due_date`);
  * SDR Responsável (`assigned_user_id`).

### [RF-012] Alertas Visuais de Tarefas Atrasadas
* **Descrição**: Tarefas com `due_date < momento_atual` e status pendente devem ser destacadas com badge vermelha e contadores de atraso tanto no card do Kanban quanto no menu lateral.

### [RF-013] Conclusão e Histórico de Tarefas
* **Descrição**: Ao marcar a checkbox de uma tarefa, o sistema altera seu status para `completed`, registra a data/hora de conclusão e adiciona o evento na timeline da empresa.

### [RF-014] Painel Consolidado de Tarefas (`currentView = 'tasks'`)
* **Descrição**: Tela dedicada acessível pela sidebar que lista todas as tarefas do time ou do SDR logado, categorizadas por "Pendentes", "Atrasadas" e "Concluídas", com filtros de tipo e prioridade.

---

## 💬 Módulo 5: Comunicação Omnichannel - WhatsApp

### [RF-015] Gerenciamento de Instâncias WhatsApp (Evolution API / Baileys)
* **Descrição**: Conexão de números de WhatsApp corporativos dos SDRs via leitura de QR Code.
* **Critérios**: Exibição do status de conexão (`connected`, `connecting`, `disconnected`) e sincronização de instâncias por tenant.

### [RF-016] Drawer de Chat em Tempo Real no Kanban
* **Descrição**: Ao clicar no botão de WhatsApp de qualquer decisor, abre-se uma gaveta lateral (drawer) exibindo o histórico completo de mensagens trocadas com o contato.

### [RF-017] Disparo de Mensagens com Simulação Anti-Ban
* **Descrição**: O envio de mensagens via WhatsApp deve respeitar regras de segurança contra bloqueio:
  * Simulação de digitação e presença humana (`composing`);
  * Delay parametrizável antes do disparo;
  * Registro de status (`sent`, `delivered`, `read`).

### [RF-018] Suporte a Spintax e Variáveis Dinâmicas
* **Descrição**: O campo de envio aceita sintaxe Spintax (ex: `{Olá|Oi|Fala}, {nome}!`) para variação algorítmica do texto, além das tags `{nome}` e `{empresa}`.

### [RF-019] Inserção de Scripts Salvos em 1 Clique no Chat
* **Descrição**: Botão `⚡ Scripts Salvos` na gaveta de chat que abre a biblioteca de templates. Ao selecionar um roteiro, o sistema substitui automaticamente `{{decisor}}`, `{{empresa}}`, `{{cargo}}`, `{{setor}}` e preenche o input pronto para envio.

---

## ✉️ Módulo 6: Comunicação Omnichannel - E-mail (Gmail OAuth 2.0)

### [RF-020] Conexão Nativa com Google Workspace / Gmail
* **Descrição**: Fluxo de autenticação OAuth 2.0 padrão Google onde o SDR autoriza o JARVIS a disparar e-mails diretamente pela sua caixa de correio comercial (estilo HubSpot/Salesloft).
* **Segurança**: Armazenamento cifrado de `refresh_token` e renovação automática de tokens expirados.

### [RF-021] Modal de Composição e Envio de Cold Mail
* **Descrição**: Interface para compor e-mails selecionando:
  * Conta remetente (dentre as contas Google conectadas do tenant);
  * Destinatário principal (`to_email`) e cópia;
  * Assunto do e-mail (`subject`);
  * Corpo formatado da mensagem (`body`).

### [RF-022] Registro Automático na Timeline
* **Descrição**: Todo e-mail enviado via API do Gmail é registrado instantaneamente como atividade na timeline da conta com remetente, assunto, data/hora e resumo.

### [RF-023] Inserção de Scripts de E-mail em 1 Clique
* **Descrição**: Botão `⚡ Scripts Salvos` no compositor de e-mail que permite selecionar um modelo. Caso o modelo contenha `Assunto: ...`, o sistema preenche separadamente o campo de assunto e o campo de corpo do e-mail.

---

## ⚡ Módulo 7: Réguas e Cadências de Prospecção Outbound

### [RF-024] Estrutura de Cadências Multicanais
* **Descrição**: O sistema permite criar réguas de contato ordenadas (`Cadence`) compostas por múltiplos passos (`CadenceStep`).
* **Canais Suportados por Passo**:
  * `whatsapp`: Disparo de mensagem no WhatsApp;
  * `email`: Envio de e-mail comercial;
  * `call`: Tarefa de Cold Call para o SDR;
  * `linkedin`: Ação de conexão ou mensagem no LinkedIn;
  * `task`: Tarefa genérica customizada.

### [RF-025] Intervalos Flexíveis entre Passos
* **Descrição**: O intervalo de tempo para execução do próximo passo pode ser configurado em múltiplas grandezas:
  * Segundos (`seconds`);
  * Minutos (`minutes`);
  * Horas (`hours`);
  * Dias (`days`).

### [RF-026] Matrícula de Contas em Cadência (Enrollment)
* **Descrição**: O SDR pode matricular uma conta em uma cadência diretamente do card no Kanban ou da aba de cadências da empresa.
* **Regra de Negócio**: Ao matricular, o primeiro passo é agendado imediatamente ou conforme o delay estipulado, e o status da matrícula passa a ser `active`.

### [RF-027] Motor Automático de Disparo de Cadências (Cron & Queue Engine)
* **Descrição**: O sistema possui um motor autônomo agendado (`cadence:process` rodando via scheduler a cada minuto) e assíncrono (`SendCadenceStepJob`).
* **Regras de Negócio**:
  * O motor varre continuamente todas as matrículas ativas (`status = 'active'`) cujas tarefas de cadência atingiram o prazo de vencimento (`due_at <= now()`);
  * **Passos Automáticos (`whatsapp`, `email`)**: O motor dispara a mensagem diretamente para o canal configurado (utilizando a instância ativa de WhatsApp ou a conta corporativa de e-mail conectada via Gmail OAuth), marca a tarefa como `completed`, registra a atividade na timeline e avança a empresa para o próximo passo (`CadenceController::advanceNextStep`) respeitando o intervalo configurado (`delay_value`, `delay_unit`);
  * **Passos Manuais (`call`, `meeting`, `followup`)**: O motor mantém as tarefas pendentes destacando prazo para a intervenção do operador SDR;
  * **Execução Sob Demanda**: Disponibilizado botão "⚡ Executar Motor Agora" na tela de cadências e rota `POST /cadences/process-now` para processamento imediato sem esperar o cron.

### [RF-028] Detector de Resposta do Lead e Pausa Automática (Reply Detection & Auto-Stop)
* **Descrição**: Mecanismo inteligente de proteção que impede que mensagens frias de prospecção continuem sendo disparadas para leads que já engajaram na conversa.
* **Canais de Detecção**:
  * Mensagens inbound recebidas via webhook da API de WhatsApp;
  * Respostas de e-mail recebidas;
  * Ação manual do SDR ou botão "🎯 Simular Resposta" no CRM (`POST /companies/{company}/cadence/simulate-reply`).
* **Comportamento do Auto-Stop**:
  1. O status da matrícula ativa (`CompanyCadence`) é alterado imediatamente para `replied`;
  2. Todas as tarefas pendentes futuras geradas pela cadência são canceladas (`status = 'cancelled'`);
  3. É gravada uma nota automática na timeline: *"🎯 Lead respondeu! Cadência interrompida para atendimento humanizado"*;
  4. O lead e a empresa são movidos automaticamente para a coluna de resposta no Kanban ("3. Respondeu / Conversa");
  5. O card da empresa passa a exibir um badge de destaque esmeralda informando o engajamento.

---

## 📝 Módulo 8: Biblioteca de Scripts e Modelos de Mensagem

### [RF-028] Categorização de Scripts Comerciais
* **Descrição**: Armazenamento e classificação de roteiros de comunicação pelas seguintes categorias:
  * `approach`: Abordagem Inicial / Gancho de Dor;
  * `objection`: Quebra de Objeções (ex: "Já temos fornecedor", "Sem orçamento");
  * `followup`: Follow-ups de reengajamento;
  * `meeting`: Confirmação e lembrete de reuniões;
  * `closing`: Fechamento e proposta.

### [RF-029] Filtro por Canal de Atendimento
* **Descrição**: Os scripts podem ser direcionados para `whatsapp`, `email` ou `all` (omnichannel).

### [RF-030] Sistema de Favoritos e Cópia Rápida
* **Descrição**: Os SDRs podem fixar seus scripts mais usados no topo da lista através de uma estrela de favorito e copiá-los para a área de transferência com 1 clique.

### [RF-031] Motor de Substituição de Tags Dinâmicas
* **Descrição**: O motor deve substituir em tempo de execução:
  * `{{decisor}}`: Primeiro nome do contato ativo;
  * `{{empresa}}`: Razão Social ou Nome Fantasia da conta;
  * `{{cargo}}`: Cargo do decisor (ex: Diretor Comercial);
  * `{{setor}}`: Segmento de atuação da empresa;
  * `{{sdr}}`: Nome do SDR responsável autenticado.

---

## 📞 Módulo 9: Registro de Cold Calls e Desfechos

### [RF-032] Modal de Registro Rápido de Ligação
* **Descrição**: Botão de chamada no card ou no lead que abre a tela de logging da ligação.

### [RF-033] Classificação Obrigatória do Desfecho (Call Outcomes)
* **Descrição**: O SDR deve categorizar o resultado da tentativa de contato:
  * `connected`: Conectou e conversou com o decisor;
  * `meeting_scheduled`: Reunião agendada na chamada;
  * `no_answer`: Não atendeu / Ocupado;
  * `gatekeeper`: Barrou na secretária / recepção;
  * `voicemail`: Caixa postal / Mensagem gravada;
  * `wrong_number`: Número errado / Inexistente.

### [RF-034] Registro de Notas e Próximo Passo
* **Descrição**: Campo de anotações sobre as dores e objeções citadas, com opção de criar imediatamente a próxima tarefa de acompanhamento.

---

## 📅 Módulo 10: Agendamento de Reuniões e Demonstrações

### [RF-035] Agendamento de Reunião com Decisor
* **Descrição**: Formulário para agendar reunião comercial registrando data, horário, pauta, duração estimada e link da sala virtual (Google Meet / Zoom / Teams).

### [RF-036] Atualização Automática de Fase do Funil
* **Descrição**: Ao agendar a reunião, o sistema sugere ou move automaticamente a conta para a etapa "Reunião Agendada" do pipeline.

### [RF-037] Disparo de Confirmação Pré-Reunião
* **Descrição**: Opção de disparo de confirmação com 1 clique contendo data, hora e link da reunião para o WhatsApp e E-mail do decisor.

---

## 📊 Módulo 11: Dashboard de Produtividade & Métricas dos SDRs

### [RF-038] KPIs Consolidados em Tempo Real
* **Descrição**: Painel analítico exibindo os principais indicadores de esforço e resultado:
  * Total de Reuniões Agendadas;
  * Total de Ligações Realizadas e Taxa de Conexão (`% connected`);
  * Disparos Omnichannel (Total WhatsApp Outbound + Total E-mails enviados);
  * Taxa de Resposta do Funil (`% responded`);
  * Cota diária de disparos e percentual de atingimento.

### [RF-039] Filtro Dinâmico de Período
* **Descrição**: O dashboard permite alternar a visualização dos dados entre:
  * `today`: Hoje;
  * `week`: Esta Semana (últimos 7 dias);
  * `month`: Este Mês (mês corrente);
  * `quarter`: Este Trimestre.

### [RF-040] Leaderboard / Ranking de Produtividade dos SDRs
* **Descrição**: Tabela comparativa entre os membros do time exibindo:
  * Posição no ranking;
  * Nome do SDR;
  * Volume total de atividades executadas;
  * Ligações telefônicas realizadas;
  * Reuniões agendadas;
  * Taxa individual de conversão.

### [RF-041] Gráfico de Desfechos de Ligações (Call Outcomes Breakdown)
* **Descrição**: Visualização gráfica percentual da assertividade telefônica do time (quantas ligações falaram com decisor vs. secretária vs. não atendeu).

### [RF-042] Distribuição de Contas por Etapa do Pipeline
* **Descrição**: Demonstrativo da quantidade e percentual de contas alocadas em cada fase do funil comercial.

---

## 📥 Módulo 12: Importação e Exportação de Contas/Leads

### [RF-043] Importação em Lote via Planilha CSV
* **Descrição**: O administrador ou SDR pode subir arquivos `.csv` para enriquecimento ou carga de listas de prospecção.
* **Colunas Mapeadas**: `empresa`, `cnpj`, `setor`, `nome`, `cargo`, `telefone`, `email`.

### [RF-044] Download de Planilha Modelo
* **Descrição**: O sistema disponibiliza download de template de planilha `.csv` pré-formatada para orientar a correta formatação dos dados.

### [RF-045] Destinação Inicial no Funil
* **Descrição**: Durante a importação, o usuário seleciona em qual etapa do pipeline as novas contas devem ser criadas (por padrão, na coluna inicial).

---

## 🔒 Módulo 13: Auditoria, Histórico e Regras Não-Funcionais Relacionadas

### [RF-046] Rastreabilidade de Alterações
* **Descrição**: Todas as ações críticas (mudança de fase, exclusão de templates, matrícula em cadências) devem gravar o ID do usuário executor e o carimbo de data/hora (`created_at`).

### [RF-047] Responsividade e Ergonomia Visual
* **Descrição**: A interface deve ser totalmente utilizável em resoluções desktop padrão (1366x768 até 4K) e tablets, respeitando a identidade visual e ergonomia estipuladas no [DESIGN.md](../DESIGN.md).

---

## 📞 Módulo 14: Telefonia VoIP FreePBX & Discador WebRTC (Softphone Embutido)

### [RF-048] Click-to-Call Omnichannel no CRM
* **Descrição**: Qualquer contato ou decisor cadastrado no CRM que possua número de telefone pode ser contatado instantaneamente com 1 clique no botão `📞`.
* **Regras de Negócio**:
  * Ao clicar no botão, o Webphone flutuante é aberto automaticamente com o número preenchido e a chamada é iniciada;
  * O nome do decisor e o nome da empresa são exibidos no topo do discador.

### [RF-049] Webphone WebRTC Embutido (Zero Instalação)
* **Descrição**: O SDR JARVIS possui um softphone WebRTC nativo (`WebphoneWidget.vue`) embutido na aplicação, eliminando a necessidade de instalar softwares externos como MicroSIP ou Zoiper no computador.
* **Recursos do Discador**:
  * Teclado numérico DTMF com dígitos de 0 a 9, `*` e `#` com feedback sonoro suave via Web Audio API;
  * Indicador de status de conexão do ramal (Verde: Online/Pronto, Amarelo: Chamando, Vermelho: Desconectado);
  * Timer de conversação em tempo real (`00:00`);
  * Controles durante a chamada: Mute (Mutar microfone), Hold (Colocar em espera), Teclado DTMF e Encerrar Chamada.

### [RF-050] Desfecho Comercial Pós-Chamada (Call Wrap-Up)
* **Descrição**: Ao desligar a chamada, o Webphone abre imediatamente a tela de wrap-up para o SDR qualificar o desfecho da ligação em 1 clique.
* **Desfechos Padronizados**:
  * `connected`: 📞 Conversou com Decisor;
  * `meeting_scheduled`: 🎯 Reunião Agendada na Chamada;
  * `gatekeeper`: 🛑 Barrou na Secretária / Recepção;
  * `no_answer`: ⏳ Não Atendeu / Tocou até cair;
  * `busy`: 📵 Número Ocupado;
  * `voicemail`: 📼 Caixa Postal.
* **Regra de Negócio**: O registro salva a chamada em `call_logs` e publica automaticamente uma atividade formatada na timeline da empresa com a duração real e as anotações do SDR.

### [RF-051] Integração com PABX IP FreePBX / Asterisk (AMI & CDR Webhook)
* **Descrição**: Conexão com servidores FreePBX locais ou em nuvem para orquestração de chamadas.
* **Canais de Comunicação**:
  * **WSS (WebSocket Seguro na porta 8089)**: Canal de áudio WebRTC nativo para os navegadores dos SDRs;
  * **AMI (Asterisk Manager Interface na porta 5038)**: Protocolo TCP para originação de chamadas (`originateCall`) e testes de conexão com credenciais dedicadas;
  * **Webhook de CDR (`POST /api/webhooks/freepbx/cdr`)**: Receptor de Call Detail Records para associar a gravação de áudio (`recording_url`) ao log da chamada no CRM.

---

## 🤖 Módulo 15: Agentes Autônomos de IA & Copiloto SDR (JARVIS Operando)

### [RF-052] Copiloto de IA Integrado ao Chat de Prospecção (Sugestões Estratégicas)
* **Descrição**: O drawer de conversas no WhatsApp possui um painel retrátil inteligente (`JarvisCopilotChat.vue`) que atua como co-piloto do SDR em tempo real.
* **Comportamento & Recursos**:
  * Analisa o histórico recente de mensagens do contato, dados da empresa (segmento, notas) e as diretrizes de persona do Tenant (`ai_persona`, `ai_prompt`);
  * Apresenta 3 opções estratégicas de resposta com 1 clique de inserção no campo de mensagem:
    1. **📅 Convite Direto / Reunião**: Focado em conversão de agenda e micro-compromisso de 15 minutos;
    2. **🔍 Abordagem Consultiva (Spin Selling)**: Perguntas abertas inteligentes para mapear dores e desafios atuais do lead;
    3. **🛡️ Contorno de Objeção / Quebra de Fricção**: Desarma resistências de preço, falta de tempo ou "me manda por e-mail" sem soar invasivo;
  * Permite ao SDR enviar orientações pontuais para a IA (ex: *"Lead disse que o sócio viajou"* ou *"Pedir e-mail corporativo"*).

### [RF-053] Auto-Qualificação de Leads & Termômetro de Sentimento
* **Descrição**: A IA analisa as mensagens recebidas do contato e emite um diagnóstico objetivo sobre o momento do lead.
* **Métricas Geradas**:
  * **Score de Calor (0 a 100)**: Nível de prontidão e fit comercial do lead;
  * **Sentimento**: `positive` (Alto Interesse 🔥), `objection` (Objeção Mapeada ⚠️), `neutral` (Aberto/Em Aquecimento 💬), `negative` (Desqualificado 🧊);
  * **Mapeamento de Objeções**: Lista de barreiras declaradas pelo cliente (ex: *Orçamento/Preço*, *Momento/Falta de Tempo*, *Já possui solução*);
  * **Resumo Executivo & Próximo Passo Recomendado**: Orientação prática para o SDR conduzir a negociação.

### [RF-054] Magic Polish (Refinador de Tom do SDR)
* **Descrição**: Permite ao SDR digitar qualquer rascunho rápido e pedir para o JARVIS reescrever a mensagem no tom desejado com padrão executivo B2B.
* **Modos de Refinamento**:
  * `⚡ Mais Curto & Direto`: Elimina rodeios e vai direto ao ponto de forma educada;
  * `🎯 Mais Persuasivo`: Enfatiza valor, contraste e urgência comercial;
  * `👔 Mais Executivo`: Tom formal, sóbrio e adequado para C-Levels e Diretores;
  * `💬 Mais Amigável`: Linguagem leve e relacional para contatos menos formais.

### [RF-055] Avanço Automático de Etapa por IA
* **Descrição**: Quando a qualificação da IA identifica que o lead atingiu prontidão (ex: confirmou interesse em call), o sistema exibe o botão `[🚀 Mover Conta para "Reunião Agendada"]`.
* **Regras de Negócio**:
  * Atualiza a etapa da empresa e de seus contatos no pipeline do Kanban;
  * Cria automaticamente uma atividade do tipo `note` na timeline da empresa com o log da decisão de avanço pela IA.

### [RF-056] Termômetro de Calor no Quadro Kanban
* **Descrição**: Os cards do Kanban exibem um selo dinâmico com o Score (`🔥 85/100`, `⚠️ Objeção`, `🧊 Frio`), permitindo ao gestor e aos SDRs visualizarem instantaneamente quais contas da esteira têm maior probabilidade de fechamento.

---

## 🏢 Módulo 16: Enriquecimento Automático de Contas via CNPJ / Receita Federal

### [RF-057] Consulta Cadastral Pública Automatizada (CNPJ)
* **Descrição**: Ao cadastrar uma nova conta ou ao editar uma empresa existente, o SDR pode digitar o CNPJ e clicar em `⚡ Puxar Dados da Receita Federal`.
* **Provedores de Dados**:
  * **BrasilAPI** (`https://brasilapi.com.br/api/cnpj/v1/{cnpj}`) como provedor primário público e de alta velocidade;
  * **ReceitaWS** como provedor de redundância/fallback;
  * Mecanismo de simulação de testes para CNPJs em modo offline.
* **Dados Preenchidos Automaticamente**:
  * Razão Social e Nome Fantasia;
  * CNAE Principal e Segmento de Atuação Comercial;
  * Capital Social formatado em Reais;
  * Situação Cadastral (ex: `ATIVA`);
  * Endereço Completo (Logradouro, Bairro, Cidade, Estado e CEP);
  * Telefone corporativo e E-mail fiscal.

### [RF-058] Extração de Decisores pelo Quadro de Sócios e Administradores (QSA)
* **Descrição**: A consulta à Receita Federal extrai a lista de sócios, diretores e administradores registrados no QSA da empresa.
* **Comportamento no CRM**:
  * No cadastro de **Nova Conta**: Apresenta chips clicáveis com os nomes e cargos dos sócios (ex: *"Sócio-Administrador"*, *"Diretor Executivo"*). Ao clicar em um sócio, o sistema preenche na hora o nome e cargo do lead principal.
  * Na edição de **Conta Existente**: Permite importar qualquer sócio do QSA diretamente para a lista de contatos da empresa com 1 clique.

### [RF-059] Auditoria e Histórico de Enriquecimento
* **Descrição**: Ao enriquecer uma conta existente, o sistema gera uma nota automática na timeline da empresa registrando o sucesso da consulta pública e a quantidade de decisores vinculados.

---

## 🎙️ Módulo 17: Conversation Intelligence & Transcrição de Chamadas por IA

### [RF-060] Speech-to-Text & Transcrição de Voz (Whisper)
* **Descrição**: O sistema analisa as gravações das ligações telefônicas realizadas pelos SDRs através do Webphone FreePBX.
* **Processamento de Áudio**:
  * Utiliza a API Whisper da OpenAI (`whisper-1`) quando houver arquivo de áudio disponível na URL de gravação do PABX;
  * Mecanismo de simulação cognitiva hiper-realista para chamadas de teste em ambiente local ou sem gravação em disco.

### [RF-061] Ata Executiva Automatizada da Ligação
* **Descrição**: A partir da transcrição da conversa, o JARVIS gera automaticamente uma ata estruturada da ligação.
* **Campos da Ata**:
  * **Resumo em Tópicos**: 3 a 4 bullet points destacando os principais pontos acordados com o decisor;
  * **Mapeamento de Objeções**: Identificação de barreiras mencionadas pelo lead (preço, tempo, concorrência);
  * **Score de Engajamento da Chamada (0 a 100)**;
  * **Sentimento da Conversa**: `positive` (Alto Interesse 🔥), `neutral` (Aberto 💬), `objection` (Objeção ⚠️), `negative` (Desqualificado 🧊).

### [RF-062] Coaching de Vendas para o SDR & Próximos Passos
* **Descrição**: O JARVIS gera um feedback educativo instantâneo para o SDR (*Coaching Tip*) sugerindo melhorias na abordagem para a próxima chamada, além de listar as tarefas de follow-up recomendadas.

### [RF-063] Publicação Instantânea na Timeline da Conta
* **Descrição**: A ata, o score e os tópicos da conversa são salvos na tabela `call_logs` e publicados diretamente como uma atividade do tipo `call` na timeline da empresa, permitindo a qualquer gestor ou operador acompanhar o histórico auditável da conta.

---

## 🧪 Módulo 18: Sandbox & Simulador de Resposta do Lead no WhatsApp

### [RF-064] Simulador de Resposta Inbound no Chat Drawer (Sandbox)
* **Descrição**: O Drawer de WhatsApp possui um botão e painel retrátil de Sandbox (**`🧪 Simular Lead`**) permitindo disparar respostas simuladas em nome do lead em ambiente local ou de homologação.
* **Cenários Pré-configurados (1 clique)**:
  * **Interesse / Demonstração**: *"Olá! Vi a sua mensagem e achei muito interessante. Como funciona a demonstração de vocês?"*;
  * **Aceite de Reunião**: *"Perfeito! Pode me ligar na próxima quinta-feira às 14h para conversarmos?"*;
  * **Dúvida / Objeção de Preço**: *"Qual é a faixa de investimento para a contratação da plataforma?"*;
  * **Texto Customizado**: Campo livre para o SDR digitar qualquer fala do lead.

### [RF-065] Reação Automática do CRM ao Engajamento Inbound
* **Descrição**: Ao disparar uma resposta simulada ou receber uma mensagem real via webhook:
  * A mensagem é persistida na tabela `messages` como `inbound` e exibida instantaneamente na bolha branca à esquerda do chat;
  * O motor de cadências (`CadenceExecutionService::handleLeadReply`) interrompe a sequência automática de e-mails/mensagens frias;
  * Tarefas pendentes da cadência são canceladas;
  * É gerada uma nota na timeline registrando o engajamento do cliente;
  * O Lead e a Empresa avançam automaticamente para a etapa de *"Respondeu / Em Conversa"* no Kanban.

---

## 🧠 Módulo 19: Laboratório de Treinamento, Estresse & Instrução da IA (JARVIS Lab)

### [RF-066] Arena de Simulação & Testes de Estresse da IA
* **Descrição**: Painel interativo de laboratório acessível pelo menu lateral (`Treinamento da IA`), onde gestores e SDRs conversam diretamente com o Agente de IA para validar suas respostas, limites éticos e comportamento sob pressão comercial.
* **Recursos da Arena**:
  * **Diálogo Livre em Tempo Real**: O usuário assume o papel do lead ou cliente difícil e a IA responde aplicando as regras do negócio;
  * **Ataques Rápidos de Estresse (1 clique)**:
    * *Objeção Severa de Preço & Concorrência*;
    * *Falta de Tempo / Despiste Temporal*;
    * *Cliente Irritado / Agressividade Direta*;
    * *Tentativa de Jailbreak / Prompt Injection*;
    * *Ceticismo Máximo*.
  * **Telemetria & Raio-X Cognitivo em Tempo Real**:
    * Nível de estresse suportado com barra de intensidade (0% a 100%);
    * Sentimento detectado no input do usuário;
    * Intenção do usuário e estratégia de vendas aplicada pelo agente;
    * Monitoramento de Guardrails (bloqueio de injeção de prompt e proteção de marca).

### [RF-067] Estúdio de Instrução & Prompt Tuning do Agente
* **Descrição**: Aba dedicada para o gestor instruir a IA e definir as regras do jogo do seu negócio sem necessidade de mexer em código.
* **Parâmetros Customizáveis por Tenant**:
  * **Identidade do Agente**: Nome (ex: *JARVIS Closer Pro*) e Cargo/Persona (*Especialista em Novos Negócios*);
  * **Proposta de Valor da Empresa**: Descrição do produto, ticket, diferenciais e dores que a empresa resolve;
  * **Diretrizes e Instruções de Vendas**: Lista estruturada de comandos de comportamento;
  * **Regras Inegociáveis (Guardrails)**: O que a IA NUNCA pode falar (não prometer descontos, não criticar concorrentes, não enviar arquivos pesados);
  * **Tom de Voz**: `Consultivo & Empático`, `Direto & Objetivo`, `Amigável & Caloroso`, `Corporativo Formal`.

### [RF-068] Base de Conhecimento & FAQ Oficial da Empresa
* **Descrição**: Gerenciador dinâmico de Perguntas e Respostas oficiais da empresa. Quando o lead fizer perguntas técnicas, de implantação ou de preço, a IA utiliza com prioridade as respostas homologadas pela diretoria.
* **Ação Imediata**: Botão *"Salvar e Ir para a Arena"* que aplica as novas instruções instantaneamente na simulação de estresse.

### [RF-069] Modo Piloto Automático (SDR 100% Autônomo via WhatsApp)
* **Descrição**: Capacidade do Agente de IA operar de forma totalmente autônoma no WhatsApp, monitorando webhooks de mensagens recebidas (inbound) e respondendo instantaneamente em nome da empresa sem necessidade de clique ou intervenção humana.
* **Recursos do Piloto Automático**:
  * **Master Switch de Autonomia**: Ativa/desativa globalmente no tenant o atendimento autônomo;
  * **Tempo de Digitação Humanizada**: Delay configurável (5s a 30s) simulando digitação com envio de presença `composing` no WhatsApp;
  * **Avanço Automático de Etapa no Kanban**: Quando o lead confirma interesse ou aceita data/horário de reunião, a IA avança automaticamente o lead e a conta para a etapa *"Reunião Agendada"* e registra uma nota de atividade;
  * **Desqualificação Automática**: Se o lead expressar formalmente recusa ("não tenho interesse", "retire meu número"), o SDR JARVIS encerra o contato cordialmente e move o status para desqualificado;
  * **Restrição de Horário Comercial**: Configuração opcional para operar apenas em horário comercial (ex: 08:00 às 18:30).

### [RF-070] Transbordo Humano Inteligente (Handoff) & Controle por Lead
* **Descrição**: Sistema de segurança que impede que a IA continue respondendo sozinhas em cenários que exigem intervenção de um vendedor humano.
* **Gatilhos de Transbordo**:
  * **Pedido Expresso do Lead**: Se o contato solicitar falar com uma pessoa real ("quero falar com atendente", "humano por favor");
  * **Detecção de Estresse / Hostilidade Severa**: Se a mensagem contiver termos ofensivos ou agressivos;
  * **Ação do Sistema**: A IA envia mensagem de transição educada (*"Com certeza! Estou passando seu atendimento para o nosso especialista humano da equipe..."*), pausa o robô exclusivamente para aquele lead e registra alerta no CRM.
* **Controle Manual pelo Vendedor no Chat**:
  * No topo da conversa de WhatsApp de cada lead no Kanban, há um botão de alternância: `🤖 SDR Autônomo Ativo • Pausar` e `👤 Atendimento Humano (IA Pausada) • Reativar`. Permite que qualquer vendedor assuma ou devolva a conversa ao robô a qualquer instante.



