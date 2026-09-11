# Plano de Implementação: Melhorias de Fluxo e Agendamentos

Este documento descreve a arquitetura e o plano de ação para implementar as novas funcionalidades solicitadas, com foco em **boas práticas de manutenção, escalabilidade e integração** com a base de código atual do TalentiPro.

## 1. Banco de Perguntas (Pesquisa de Clima)
**Objetivo:** Permitir salvar perguntas para reutilização rápida na criação de novas pesquisas, disponibilizando um pacote de perguntas globais e permitindo bancos personalizados por empresa.
**Abordagem:** 
- Criar a tabela `climate_question_templates` (id, empresa_id, dimensao, texto_pergunta, tipo_resposta, is_global).
- `empresa_id` será `nullable`. Se `is_global = true` e `empresa_id = null`, a pergunta aparece para todas as empresas (padrão do sistema).
- **Cuidado no CRUD:** Garantir nas rotas que o RH de uma empresa só possa criar, editar e excluir perguntas onde `empresa_id` seja o dele. Perguntas globais só podem ser inseridas via banco/seeder ou por um super admin.
- Na tela de criação de pesquisa de clima, adicionar botão "Importar do Banco" (trazendo as globais + as da empresa logada).
- **Justificativa:** A arquitetura garante flexibilidade. O sistema já vem com templates prontos (agregando valor rápido) e as empresas não ficam engessadas.

## 2. Salvar e Editar Pesquisa de Clima como Rascunho
**Objetivo:** Permitir que o RH inicie a criação de uma pesquisa, salve como rascunho e volte para editar/publicar depois.
**Abordagem:**
- O status `rascunho` já existe no banco de dados.
- Criar os métodos `edit` e `update` no `ClimateSurveyController`.
- Permitir adicionar/remover perguntas de uma pesquisa enquanto ela estiver no status `rascunho`.
- **Justificativa:** Evita a perda de dados durante o planejamento. A restrição de edição apenas para rascunhos garante que uma pesquisa `ativa` não sofra mutações que invalidem as respostas.

## 3. Agendamento de Avaliações (Notificações no Futuro)
**Objetivo:** Agendar uma avaliação para uma data futura (ex: daqui a 15 dias) e só enviar os alertas/e-mails na data programada.
**Abordagem:**
- Adicionar o status `agendada` nas Avaliações (e Pesquisas de Clima).
- Ao salvar com uma `data_inicio` futura, o status será `agendada` e as notificações/atividades **não** serão geradas imediatamente.
- Criar um **Console Command / Cron Job** (ex: `php artisan avaliacoes:processar-agendamentos`) que rodará diariamente.
- O Job buscará todas as avaliações `agendadas` onde a `data_inicio` é hoje, mudará o status para `ativa` e disparará a criação das `Atividades` e os `Emails` para os participantes.
- **Justificativa:** Essa abordagem assíncrona (via Cron) é o padrão ouro para sistemas escaláveis. Remove a carga de processamento do momento do clique do usuário e garante que e-mails só cheguem no momento exato em que a avaliação precisa ser respondida.

## 4. Sistema de Lembretes (Snooze e Tarefas Recorrentes)
**Objetivo:** Permitir que colaboradores adiem uma avaliação ("lembrar em X dias") e que o RH crie lembretes de ciclo ("lembrar próxima avaliação em 6 meses").
**Abordagem:**
- Criar uma tabela genérica (Polimórfica) `lembretes` (id, user_id, lembreteable_type, lembreteable_id, data_agendada, mensagem, status).
- **Escopo Inicial:** Implementado apenas nas Avaliações.
- **Para o Colaborador:** Na tela da avaliação, um botão "Lembrar-me depois" (Amanhã, Em 3 dias).
- **Para o Gestor/RH:** Ao encerrar uma avaliação, uma opção "Agendar lembrete para o próximo ciclo".
- O Cron Job diário vai ler a tabela `lembretes` e disparar a notificação (`Atividade` e E-mail).
- **Justificativa:** A tabela polimórfica (`lembreteable_type`) garante que o código está **totalmente preparado para o futuro**. Quando for necessário ativar o lembrete para o PDI, basta passar o Model do PDI na criação do lembrete, sem precisar alterar a estrutura do banco ou o *Cron Job*.
