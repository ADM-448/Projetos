# Documentação do SDR JARVIS

Bem-vindo à documentação oficial do **SDR JARVIS**, a plataforma de prospecção ativa B2B (Sales Development Representative) orientada a cadências multicanais, engajamento automatizado e produtividade comercial.

---

## 📑 Índice da Documentação

1. [Requisitos Funcionais da Plataforma (RF)](requisitos-funcionais.md)
   * Especificação exaustiva de todos os requisitos funcionais categorizados por módulo (RF-001 a RF-060).
   * Atores do sistema, regras de negócio associadas e critérios de aceitação.
2. [Arquitetura de Dados & Entidades](arquitetura-e-dados.md)
   * Modelo conceitual das tabelas e relacionamentos.
   * Regras de isolamento multi-tenant e integridade relacional.
3. [Plano de Implementação: Telefonia VoIP com FreePBX](plano-integracao-freepbx.md)
   * Integração com PABX IP open-source para ligações gratuitas pelo PC via WebRTC, gravação automática, Click-to-Call e análise por IA.
4. [Guia de Deploy & Configuração do FreePBX](guia-deploy-freepbx.md)
   * Passo a passo completo de instalação em VPS/Cloud, ativação de SSL Let's Encrypt, criação de ramais WebRTC PJSIP, configuração de AMI e conexão com o SDR JARVIS.

---

## 🎯 Visão Geral da Plataforma

O **SDR JARVIS** foi concebido para resolver o gargalo de tração e conversão de times de prospecção outbound B2B. A plataforma unifica em uma interface única de alta velocidade:
* **Funil de Contas (Kanban Inteligente)** com drag & drop e Smart Views em 1 clique;
* **Engajamento Omnichannel** direto (WhatsApp via Evolution API e E-mail nativo via Google Workspace / Gmail OAuth 2.0);
* **Cadências & Sequências Automatizadas** multicanais com intervalos flexíveis (segundos, minutos, horas, dias);
* **Biblioteca de Scripts & Roteiros de Vendas** com tags dinâmicas (`{{decisor}}`, `{{empresa}}`, `{{cargo}}`, etc.) e injeção em 1 clique;
* **Gestão de Tarefas e Lembretes de Follow-up** por conta;
* **Registro Estruturado de Cold Calls** com desfechos de conexão e objeções;
* **Painel de Produtividade em Tempo Real** com ranking de SDRs e taxas de conversão.
