# **🏗️ Laravel SaaS Architecture Showcase (Plataforma de Gestão de RH)**

Bem-vindo ao meu repositório de demonstração de arquitetura\! Este documento serve como um **Showcase** das minhas habilidades avançadas no ecossistema **Laravel**, com foco na estruturação de projetos escaláveis, design patterns e processamento em background (Assincronicidade).

> **⚠️ Nota de Confidencialidade:** O código original que inspirou esta arquitetura pertence a um SaaS corporativo privado (TalentiPro). Por questões de sigilo e segurança (NDAs), todas as lógicas de negócio proprietárias, chaves de API e nomes de entidades específicas foram estritamente anonimizadas e generalizadas neste repositório conceitual. O produto final pode ser visualizado em funcionamento em [talentipro.com.br](https://talentipro.com.br/).

## **📂 1\. Padrão de Arquitetura: Service Layer**

Para evitar o anti-pattern *"Fat Controller"* e facilitar a escalabilidade da plataforma, o projeto foi desenhado sob o padrão **Service Layer**.

* **Controllers:** Mantidos extremamente magros. A única responsabilidade deles é receber a requisição HTTP (Request), acionar a validação (via FormRequests), invocar o serviço correspondente e retornar a resposta formatada (JSON/API Resources).  
* **Services:** Detêm todo o "core" (regras de negócio) da aplicação. A lógica complexa de manipulação de dados, chamadas a APIs externas (IA e Mensageria) e integrações vive isolada aqui.

### **🌳 Árvore de Diretórios (Visão Desacoplada)**

app/  
├── Http/Controllers/  
│   ├── NotificationController.php    \# Magro, focado apenas em roteamento HTTP  
│   └── ReportController.php  
├── Services/  
│   ├── AudioProcessingService.php    \# Lógica de integração com IAs de Transcrição  
│   ├── ExternalMessagingService.php  \# Lógica da API do WhatsApp  
│   └── (Classes isoladas contendo as regras de negócio)  
├── Jobs/  
│   ├── ProcessAudioQueueJob.php      \# Trabalhadores assíncronos (ShouldQueue)  
│   └── ProcessMessageQueueJob.php  
└── Models/  
    ├── Tenant.php                    \# Controle de isolamento de dados (Multi-tenant)  
    └── Employee.php                  \# Regras de Eloquent e relacionamentos

## **⚙️ 2\. Processamento Assíncrono (Filas e Background Jobs)**

A aplicação lida com processos de alto custo computacional, como a **transcrição de áudio via Inteligência Artificial** e o disparo automatizado de notificações via **WhatsApp API**. Executar isso de forma síncrona bloquearia a navegação do usuário (Timeouts).

Para contornar isso, implementei um sistema robusto de filas do Laravel (**Queue Worker** & **Task Scheduling**).

### **A Engenharia do Fluxo Assíncrono:**

1. **Desacoplamento Imediato:** Quando o usuário faz o upload de uma reunião (áudio), o sistema salva o arquivo no Storage, registra o status pendente no banco e despacha um *Job* para a fila. A resposta 202 Accepted é entregue ao cliente em milissegundos.  
2. **Workers em Background:** Processos isolados consomem a fila silenciosamente. Os *Jobs* injetam os *Services* necessários via Injeção de Dependência nativa.  
3. **Resiliência (Circuit Breaker):** Jobs de comunicação externa executam um *Health Check* antes do envio. Em caso de queda da API do WhatsApp, a tarefa não é perdida; o Laravel a reintegra à fila com uma política de retentativas (*retry delay*).

## **💻 3\. Snippets de Código de Arquitetura**

Abaixo estão dois exemplos conceituais de como a separação de responsabilidades (SOLID) foi aplicada.

### **Snippet A: Controller Limpo com Injeção de Dependência**

O Controller não conhece os detalhes de como o WhatsApp funciona. Ele apenas injeta o serviço e delega a ação, garantindo facilidade para testes unitários.

\<?php

namespace App\\Http\\Controllers;

use App\\Services\\ExternalMessagingService;  
use Illuminate\\Http\\Request;  
use Illuminate\\Http\\RedirectResponse;

class NotificationController extends Controller  
{  
    /\*\*  
     \* Dispara notificação via Service Layer (Controller Magro).  
     \*/  
    public function sendTest(Request $request, ExternalMessagingService $apiService): RedirectResponse  
    {  
        $user \= $request-\>user();

        // Configura o contexto do tenant (multi-tenancy)  
        $apiService-\>setTenantId($user-\>tenant\_id);

        $payload \= \[  
            'to'      \=\> $user-\>phone,  
            'message' \=\> "Sua avaliação 360º está disponível.",  
            'user\_id' \=\> $user-\>id  
        \];

        // Delega o processamento externo para o Service  
        $isDispatched \= $apiService-\>sendMessage($payload);

        if ($isDispatched) {  
            return back()-\>with('success', 'Mensagem enviada com sucesso para a fila de processamento\!');  
        }

        return back()-\>with('error', 'Instabilidade detectada na API Externa. Ação movida para retentativa.');  
    }  
}

### **Snippet B: Background Job Resiliente (Tratamento de Falhas)**

Este Job processa filas de forma segura. Ele implementa verificações de integridade para não desperdiçar processamento do servidor caso a API terceira caia.

\<?php

namespace App\\Jobs;

use App\\Services\\ExternalMessagingService;  
use Illuminate\\Contracts\\Queue\\ShouldQueue;  
use Illuminate\\Foundation\\Queue\\Queueable;  
use Illuminate\\Support\\Facades\\Log;

class ProcessMessageQueueJob implements ShouldQueue  
{  
    use Queueable;

    /\*\*  
     \* Processa tarefas pendentes em background.  
     \*/  
    public function handle(ExternalMessagingService $apiService): void  
    {  
        $pendingTasks \= $this-\>countPendingTasks();

        if ($pendingTasks \=== 0\) {  
            return;  
        }

        // Circuit Breaker: Checa a conexão antes de iniciar o loop de disparos  
        if (\!$apiService-\>isExternalServiceConnected()) {  
            Log::warning('Queue Worker: API externa offline. Interrompendo worker.');  
            $this-\>notifyAdmins("Falha na API", "Serviço indisponível com {$pendingTasks} tarefas na fila.");  
            return; // Mantém as tarefas seguras no banco para a próxima rodada  
        }

        // Delega o processamento em lote para o Service  
        \[$successCount, $failedCount\] \= $apiService-\>processPendingQueue();

        if ($failedCount \> 0\) {  
            Log::error("Queue Worker: {$failedCount} envios falharam em definitivo.");  
        }  
    }

    private function countPendingTasks(): int  
    {  
        return 15; // Exemplo abstraído  
    }

    private function notifyAdmins(string $title, string $message): void  
    {  
        // Lógica abstraída de envio de slack/email interno  
    }  
}  
