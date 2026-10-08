# Guia Completo de Deploy & Configuração do FreePBX (VoIP SDR JARVIS)

Este guia prático foi criado para orientar a instalação, configuração e homologação do **[FreePBX](https://www.freepbx.org/)** (Asterisk) integrado ao **SDR JARVIS**.

---

## 💰 1. O FreePBX é Realmente Gratuito?

**SIM! O FreePBX e o Asterisk são 100% gratuitos e de código aberto (Open-Source sob licença GPL).**

### O que você NÃO paga:
* ❌ **Zero taxa de licença de software**: você nunca pagará mensalidade por usuário ou por ramal (plataformas de CRM de mercado como Meetime, Exact Sales e Twilio cobram entre R$ 150 e R$ 400 mensais por SDR só pela licença do discador).
* ❌ **Zero custo para ligações internas**: falar de ramal para ramal, transferir chamadas ou conferências entre sua equipe custa **R$ 0,00**.
* ❌ **Gravações de chamadas ilimitadas**: o FreePBX grava todas as ligações nativamente sem cobrar armazenamento extra por áudio.

### O que tem custo no mundo real (Apenas consumo e infraestrutura):
| Item | Descrição | Custo Estimado |
| :--- | :--- | :--- |
| **Software FreePBX / Asterisk** | Plataforma completa de PABX IP | **R$ 0,00 (Gratuito)** |
| **Servidor / Hospedagem** | Onde o PABX fica ligado | **R$ 0,00** (se rodar em PC/VM local) ou **~R$ 35 a R$ 50/mês** (VPS Cloud na Hetzner, Contabo ou DigitalOcean) |
| **Ligações Externas (PSTN)** | Fazer tocar no celular ou fixo de um cliente no Brasil | **Centavos por minuto** (ex: R$ 0,06 a R$ 0,12/min) ou **Planos Ilimitados** (a partir de R$ 39/mês por linha) contratados direto com qualquer operadora VoIP |

> [!TIP]
> **Resumo**: O software é livre e perpétuo. Você só paga a infraestrutura da máquina onde ele roda e os minutos de ligação consumidos da operadora VoIP que você escolher (sem fidelidade e sem intermediários).

---

## 🛠️ 2. Arquitetura da Conexão com o SDR JARVIS

```
+---------------------------------------------------------------------------------+
|                                 SDR JARVIS                                      |
|                                                                                 |
|   +------------------------------------+   +--------------------------------+   |
|   |         Webphone WebRTC            |   |         Backend Laravel        |   |
|   |   (Navegador do SDR no PC)         |   |      (SDR JARVIS API)          |   |
|   +-----------------+------------------+   +---------------+----------------+   |
+---------------------|--------------------------------------|--------------------+
                      | WSS (Porta 8089 - Áudio WebRTC)      | TCP (Porta 5038 - AMI)
                      |                                      |
                      v                                      v
+---------------------------------------------------------------------------------+
|                            SERVIDOR FREEPBX / ASTERISK                          |
|                                                                                 |
|  * Módulo WebRTC (PJSIP WSS com Certificado SSL)                                |
|  * Asterisk Manager Interface (AMI na porta 5038)                               |
|  * Gravação de Chamadas Automática (.wav / .mp3)                                |
|  * Tronco SIP conectado à sua Operadora VoIP                                    |
+---------------------------------------------------------------------------------+
                                      |
                                      v (Operadora VoIP)
                        📱 Celular / Telefone do Lead
```

---

## 🚀 3. Passo a Passo de Instalação do Servidor FreePBX

### Opção A: Em Nuvem / VPS (Recomendada para Produção)
Requisitos recomendados:
* **VPS Linux**: 2 vCPUs, 2 GB a 4 GB RAM, 40 GB SSD.
* **Sistema**: Debian 12 ou Rocky Linux 9 (ou Ubuntu 22.04 LTS).
* **Provedores acessíveis**: Hetzner (Cloud ~€4.50/mês), Contabo (~€5.50/mês) ou DigitalOcean ($12/mês).

#### Instalação rápida via Script Automatizado Oficial:
Conecte-se via SSH na VPS e execute:
```bash
# 1. Atualizar o sistema
sudo apt update && sudo apt upgrade -y

# 2. Baixar o instalador do FreePBX 17
wget https://github.com/FreePBX/sng_freepbx_debian_install/raw/master/sng_freepbx_debian_install.sh

# 3. Dar permissão e executar
chmod +x sng_freepbx_debian_install.sh
./sng_freepbx_debian_install.sh
```
Ao final da instalação, acesse pelo navegador: `http://ip-do-servidor/admin` e crie a senha do usuário `admin`.

---

## ⚙️ 4. Configuração Essencial no FreePBX para o SDR JARVIS

### 4.1. Habilitar Certificado SSL (Obrigatório para WebRTC)
O microfone do navegador **só funciona via HTTPS/WSS**. É obrigatório ter um certificado SSL válido no FreePBX.
1. No painel do FreePBX, vá em: **Admin ➔ Certificate Management**.
2. Clique em **Generate Let's Encrypt Certificate**.
3. Preencha seu subdomínio (ex: `pabx.suaempresa.com.br`) apontado para o IP da VPS.
4. Defina este certificado como o padrão do sistema.

---

### 4.2. Habilitar o Transporte WebRTC (WebSocket WSS)
1. Vá em: **Settings ➔ Asterisk SIP Settings**.
2. Na aba **General**, localize **RTP Settings** e configure os limites de portas RTP (`10000` a `20000`).
3. Na aba **SIP Settings [chan_pjsip]**:
   * Habilite o transporte: **`wss - 0.0.0.0 - All`** (Porta padrão: `8089`).
   * No campo de certificado SSL, selecione o certificado Let's Encrypt gerado.
4. Clique em **Submit** e depois no botão vermelho **Apply Config** no topo da tela.

---

### 4.3. Criar os Ramais dos SDRs (PJSIP WebRTC)
Para cada SDR (ex: Ramal `1001`, `1002`, etc.):
1. Vá em: **Applications ➔ Extensions**.
2. Clique em **Add Extension ➔ Add New PJSIP Extension**.
3. Preencha:
   * **User Extension**: `1001`
   * **Display Name**: `Lucas SDR`
   * **Secret**: `Defina_Uma_Senha_Segura` (ex: `JarvisSip2026!`)
4. Na aba **Advanced**:
   * **DTLS Enable**: `Yes`
   * **DTLS Verify**: `Fingerprint`
   * **DTLS Setup**: `Act/Pass`
   * **DTLS Certificate**: Selecione o certificado Let's Encrypt
   * **Media Encryption**: `DTLS-SRTP`
   * **Rewrite Contact**: `Yes`
   * **RTP Symmetric**: `Yes`
5. Na aba **Other**:
   * **Media Transport**: Escolha `transport-wss`.
6. Salve e clique em **Apply Config**.

---

### 4.4. Criar o Usuário AMI (Asterisk Manager Interface)
O AMI é o canal que o Laravel usa para testar conexões e disparar ligações automáticas.
1. Vá em: **Settings ➔ Asterisk Manager Users**.
2. Clique em **Add Manager**.
3. Preencha:
   * **Manager Name**: `jarvis_ami`
   * **Manager Secret**: `senha_super_secreta_ami`
4. Na aba **Permissions**, marque as permissões de leitura e escrita:
   * `system`, `call`, `originate`, `all`.
5. Salve e clique em **Apply Config**.

---

### 4.5. Conectar sua Operadora VoIP (Tronco de Saída)
Para poder ligar para celulares de leads reais:
1. Contrate uma conta em uma operadora VoIP nacional (ex: *Directcall*, *FalaHub*, *TotalVoice*, *VoipMundo*, etc.).
2. No FreePBX, vá em: **Connectivity ➔ Trunks ➔ Add PJSIP Trunk**.
3. Preencha os dados fornecidos pela operadora (Servidor SIP, Usuário e Senha).
4. Em **Connectivity ➔ Outbound Routes**, crie uma rota de saída permitindo discar qualquer número de 10 ou 11 dígitos enviando pelo tronco VoIP.

---

### 4.6. Configurar Gravação Automática de Chamadas
1. Vá em: **Settings ➔ Advanced Settings**.
2. Localize **Call Recording Format** e selecione `wav` ou `mp3`.
3. Nos ramais dos SDRs (aba **Advanced**), marque:
   * **Inbound External Calls**: `Force` (Sempre gravar)
   * **Outbound External Calls**: `Force` (Sempre gravar)

---

## 🔗 5. Conectando o FreePBX ao SDR JARVIS

No CRM do **SDR JARVIS**:
1. Faça login como **Super Admin** ou **Gestor**.
2. Abra a aba administrativa do CRM e insira as credenciais geradas:
   * **Servidor FreePBX / Host**: `pabx.suaempresa.com.br`
   * **URL WebSocket WebRTC**: `wss://pabx.suaempresa.com.br:8089/ws`
   * **Host AMI**: `pabx.suaempresa.com.br` (ou `127.0.0.1` se estiver no mesmo servidor)
   * **Porta AMI**: `5038`
   * **Usuário AMI**: `jarvis_ami`
   * **Senha AMI**: `senha_super_secreta_ami`
3. Nos colaboradores do CRM, abra a edição do usuário e preencha:
   * **Ramal FreePBX (SIP)**: `1001`
   * **Senha do Ramal**: `JarvisSip2026!`

---

## ✅ 6. Checklist de Teste em Produção

- [ ] Abrir o SDR JARVIS no navegador (Chrome, Edge ou Firefox).
- [ ] O botão do Webphone no canto inferior direito deve exibir badge verde (**Ramal 1001 • Pronto**).
- [ ] No Kanban, clicar no botão **📞** de um decisor.
- [ ] O Webphone abre, disca para o número via WebRTC e o celular do cliente toca.
- [ ] O áudio da conversa é transmitido em alta fidelidade com microfone e fone de ouvido.
- [ ] Ao desligar, a janela de **Desfecho Comercial (Wrap-up)** abre automaticamente.
- [ ] Ao salvar, a ligação, a gravação e as anotações aparecem imediatamente na **Timeline da Empresa**.
