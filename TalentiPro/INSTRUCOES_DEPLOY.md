# Guia de Deploy - TalentiPro (Atualização de E-mails e Cron)

Este arquivo contém o resumo exato do que você precisa fazer no painel da **HostGator** para colocar todas as últimas atualizações no ar antes da reunião com seu lead.

---

## 1. Arquivos para Fazer Upload

Vá no **Gerenciador de Arquivos** do cPanel e substitua/crie os seguintes arquivos (atenção às pastas):

### 🆕 NOVOS Arquivos (Você precisa criar as pastas se não existirem)
1. `app/Mail/GenericNotificationMail.php`
2. `resources/views/emails/generic.blade.php`

### 🔄 Arquivos Modificados (Apenas substituir os existentes)
**Views e Controllers:**
1. `resources/views/dashboard/partials/modal_avaliacoes.blade.php` *(Correção do contador 0/3)*
2. `resources/views/reunioes/show.blade.php` *(Remoção do player de áudio)*
3. `app/Http/Controllers/DiscAssessmentController.php` *(Correção de equipe gestor)*
4. `app/Http/Controllers/ReuniaoFeedbackController.php` *(Disparo de E-mail)*
5. `app/Http/Controllers/ClimateSurveyController.php` *(Disparo de E-mail)*
6. `app/Http/Controllers/DiscSolicitacaoController.php` *(Disparo de E-mail)*
7. `app/Http/Controllers/SolicitacaoController.php` *(Disparo de E-mail)*

**Serviços e Rotas:**
8. `app/Services/EmailService.php` *(Atualizado para aceitar ID)*
9. `app/Services/AvaliacaoService.php` *(Disparo de E-mail)*
10. `app/Services/PdiService.php` *(Disparo de E-mail)*
11. `app/Services/WhatsAppService.php` *(Envio Assíncrono)*
12. `routes/console.php` *(Configuração da fila)*

**Jobs (Fila):**
13. `app/Jobs/SendWhatsAppMessageJob.php` *(Novo Job)*

---

## 2. Como Configurar o Cron Job no cPanel (HostGator)

Como você está em hospedagem compartilhada, não podemos rodar o `php artisan queue:work` direto no terminal. O código em `routes/console.php` já foi ajustado para que o Cron Job faça esse trabalho para nós.

Siga os passos exatos abaixo:

1. Acesse o seu **cPanel** da HostGator.
2. Desça a página até a seção **Avançado** e clique em **Trabalhos Cron** (ou *Cron Jobs*).
3. Na seção **Adicionar novo trabalho cron**, preencha os campos da seguinte forma:
   - **Configurações comuns:** Selecione a primeira opção do dropdown: `Uma vez por minuto (* * * * *)`. Isso vai preencher todos os campos abaixo com asteriscos.
   - **Comando:** Cole o código abaixo (verifique se o caminho `intale75` e a pasta `TalentiPro_Refatorado_2` batem exatamente com o caminho do seu servidor):

```bash
/usr/local/bin/php /home2/intale75/public_html/TalentiPro_Refatorado_2/artisan schedule:run >> /dev/null 2>&1
```

*(Se o seu projeto estiver direto na public_html, o caminho será apenas `/home2/intale75/public_html/artisan`)*

4. Clique no botão azul **Adicionar novo trabalho cron**.

### 💡 Como saber se deu certo (Cron)?
- Crie uma nova Reunião, PDI ou Avaliação no sistema de produção.
- Vá na caixa de entrada do e-mail de destino.
- O Cron roda a cada 1 minuto. Assim que virar o minuto, o sistema irá processar a fila e o e-mail (ou WhatsApp) deve chegar!

---

## 3. Configurar o Envio de E-mails (.env)

Para que o e-mail `contato@talentipro.com.br` envie as mensagens de verdade (e não apenas crie logs fictícios), você deve configurar as credenciais SMTP no arquivo `.env` da produção.

1. Se ainda não o fez, acesse o **cPanel** > **Contas de E-mail** e crie a conta `contato@talentipro.com.br` com uma senha forte.
2. Abra o **Gerenciador de Arquivos** no cPanel e edite o arquivo `.env` do projeto (ative "Mostrar arquivos ocultos" nas configurações se não estiver vendo o `.env`).
3. Substitua toda a seção `MAIL_` atual por esta abaixo, colocando a senha que você criou:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.talentipro.com.br
MAIL_PORT=465
MAIL_USERNAME=contato@talentipro.com.br
MAIL_PASSWORD="L@r42zero9"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="contato@talentipro.com.br"
MAIL_FROM_NAME="${APP_NAME}"
```

Salve o arquivo. A partir de agora, todos os e-mails da fila serão enviados de verdade!

---
Boa sorte na apresentação com o lead! Se a tela quebrar em alguma parte, basta voltar o arquivo antigo correspondente.
