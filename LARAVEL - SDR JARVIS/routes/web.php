<?php

use App\Http\Controllers\AdminTenantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CadenceController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WebChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SDR JARVIS
|--------------------------------------------------------------------------
*/

// Rotas Públicas de Autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Rotas Protegidas por Autenticação
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // CRM Principal (Kanban & Gestão de Contas/Leads)
    Route::get('/', [KanbanController::class, 'index'])->name('kanban.index');
    Route::patch('/leads/{lead}/stage', [KanbanController::class, 'updateStage'])->name('kanban.leads.stage');
    Route::get('/leads/{lead}/messages', [KanbanController::class, 'messages'])->name('kanban.leads.messages');
    Route::post('/leads/{lead}/send-message', [KanbanController::class, 'sendMessage'])->name('kanban.leads.send');
    Route::post('/leads/{lead}/simulate-reply', [KanbanController::class, 'simulateReply'])->name('kanban.leads.simulate-reply');
    Route::post('/leads', [KanbanController::class, 'storeLead'])->name('kanban.leads.store');
    Route::put('/leads/{lead}', [KanbanController::class, 'updateLead'])->name('kanban.leads.update');
    Route::delete('/leads/{lead}', [KanbanController::class, 'destroyLead'])->name('kanban.leads.destroy');
    Route::post('/leads/import', [KanbanController::class, 'importLeads'])->name('kanban.leads.import');

    // WebChat (WhatsApp Web Clone)
    Route::get('/webchat', [WebChatController::class, 'index'])->name('webchat.index');
    Route::post('/webchat/leads/{lead}/sync-history', [WebChatController::class, 'syncHistory'])->name('webchat.leads.sync-history');
    Route::post('/webchat/leads/{lead}/link-company', [WebChatController::class, 'linkCompany'])->name('webchat.leads.link-company');
    Route::post('/webchat/leads/{lead}/create-company', [WebChatController::class, 'createCompanyAndLink'])->name('webchat.leads.create-company');


    // Gestão de Empresas (Accounts) e múltiplos contatos
    Route::patch('/companies/{company}/stage', [KanbanController::class, 'updateCompanyStage'])->name('kanban.companies.stage');
    Route::put('/companies/{company}', [KanbanController::class, 'updateCompany'])->name('kanban.companies.update');
    Route::delete('/companies/{company}', [KanbanController::class, 'destroyCompany'])->name('kanban.companies.destroy');
    Route::post('/companies/{company}/contacts', [KanbanController::class, 'storeCompanyContact'])->name('kanban.companies.contacts.store');
    Route::post('/companies/{company}/activities', [KanbanController::class, 'storeCompanyActivity'])->name('kanban.companies.activities.store');
    Route::post('/companies/{company}/schedule-meeting', [KanbanController::class, 'scheduleCompanyMeeting'])->name('kanban.companies.schedule-meeting');
    Route::delete('/activities/{activity}', [KanbanController::class, 'destroyActivity'])->name('kanban.activities.destroy');

    // Enriquecimento Automático de Contas via CNPJ / Receita Federal
    Route::post('/companies/enrich-cnpj', [\App\Http\Controllers\CompanyEnrichmentController::class, 'enrichByCnpj'])->name('companies.enrich-cnpj');
    Route::post('/companies/{company}/apply-enrichment', [\App\Http\Controllers\CompanyEnrichmentController::class, 'applyEnrichment'])->name('companies.apply-enrichment');

    // Conexão Gmail OAuth 2.0 (estilo HubSpot) e Envio de E-mails
    Route::get('/auth/google/redirect', [GmailOAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GmailOAuthController::class, 'callback'])->name('auth.google.callback');
    Route::post('/auth/google/disconnect/{account}', [GmailOAuthController::class, 'disconnect'])->name('auth.google.disconnect');
    Route::post('/auth/google/credentials', [GmailOAuthController::class, 'saveCredentials'])->name('auth.google.credentials');
    Route::post('/emails/send', [GmailOAuthController::class, 'send'])->name('emails.send');

    // Dashboard de Produtividade & Métricas
    Route::get('/dashboard/metrics', [KanbanController::class, 'dashboardMetrics'])->name('dashboard.metrics');

    // Gestão de Tarefas e Lembretes por Empresa
    Route::post('/companies/{company}/tasks', [TaskController::class, 'store'])->name('companies.tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Cadências e Sequências de Prospecção Outbound
    Route::get('/cadences', [CadenceController::class, 'index'])->name('cadences.index');
    Route::post('/cadences', [CadenceController::class, 'store'])->name('cadences.store');
    Route::delete('/cadences/{cadence}', [CadenceController::class, 'destroy'])->name('cadences.destroy');
    Route::post('/companies/{company}/cadence/enroll', [CadenceController::class, 'enroll'])->name('companies.cadence.enroll');
    Route::post('/companies/{company}/cadence/unenroll', [CadenceController::class, 'unenroll'])->name('companies.cadence.unenroll');
    Route::post('/cadences/process-now', [CadenceController::class, 'processNow'])->name('cadences.process-now');
    Route::post('/companies/{company}/cadence/simulate-reply', [CadenceController::class, 'simulateReply'])->name('companies.cadence.simulate-reply');

    // Biblioteca de Scripts e Modelos de Mensagem (WhatsApp / E-mail)
    Route::get('/templates', [MessageTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [MessageTemplateController::class, 'store'])->name('templates.store');
    Route::put('/templates/{template}', [MessageTemplateController::class, 'update'])->name('templates.update');
    Route::patch('/templates/{template}/favorite', [MessageTemplateController::class, 'toggleFavorite'])->name('templates.favorite');
    Route::delete('/templates/{template}', [MessageTemplateController::class, 'destroy'])->name('templates.destroy');

    // Painel de Gestão Multi-Tenant & Configuração de IAs (Exclusivo Super Admin)
    Route::middleware('superadmin')->group(function () {
        Route::post('/admin/tenants/switch/{tenant}', [AdminTenantController::class, 'switchTenant'])->name('admin.tenants.switch');
        Route::post('/admin/tenants', [AdminTenantController::class, 'storeTenant'])->name('admin.tenants.store');
        Route::put('/admin/tenants/{tenant}', [AdminTenantController::class, 'updateTenant'])->name('admin.tenants.update');
        Route::delete('/admin/tenants/{tenant}', [AdminTenantController::class, 'destroyTenant'])->name('admin.tenants.destroy');
    });

    // Gestão de Colaboradores e Usuários (Super Admin ou Gestor da Base protegido pelo middleware)
    Route::middleware('can.manage.user')->group(function () {
        Route::post('/admin/users', [AdminTenantController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminTenantController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminTenantController::class, 'destroyUser'])->name('admin.users.destroy');
    });

    // Telefonia VoIP FreePBX & Discador WebRTC (Softphone no Navegador)
    Route::get('/voip/credentials', [\App\Http\Controllers\VoipController::class, 'credentials'])->name('voip.credentials');
    Route::post('/voip/originate', [\App\Http\Controllers\VoipController::class, 'originate'])->name('voip.originate');
    Route::post('/voip/log-call', [\App\Http\Controllers\VoipController::class, 'logCall'])->name('voip.log-call');
    Route::post('/voip/calls/{callLog}/analyze', [\App\Http\Controllers\VoipController::class, 'analyzeCall'])->name('voip.calls.analyze');
    Route::post('/admin/voip/settings', [\App\Http\Controllers\VoipController::class, 'saveSettings'])->name('admin.voip.settings');
    Route::post('/admin/voip/test-ami', [\App\Http\Controllers\VoipController::class, 'testAmi'])->name('admin.voip.test-ami');
    Route::post('/admin/users/{user}/sip', [\App\Http\Controllers\VoipController::class, 'saveUserExtension'])->name('admin.users.sip');

    // Agentes Autônomos de IA & Copiloto SDR (JARVIS Operando)
    Route::post('/ai/suggest-replies', [\App\Http\Controllers\AiAgentController::class, 'suggestReplies'])->name('ai.suggest-replies');
    Route::post('/ai/qualify-lead', [\App\Http\Controllers\AiAgentController::class, 'qualifyLead'])->name('ai.qualify-lead');
    Route::post('/ai/improve-draft', [\App\Http\Controllers\AiAgentController::class, 'improveDraft'])->name('ai.improve-draft');
    Route::post('/ai/auto-advance-stage', [\App\Http\Controllers\AiAgentController::class, 'autoAdvanceStage'])->name('ai.auto-advance-stage');
    Route::get('/ai/settings', [\App\Http\Controllers\AiAgentController::class, 'getSettings'])->name('ai.settings');
    Route::post('/ai/settings', [\App\Http\Controllers\AiAgentController::class, 'updateSettings'])->name('ai.settings.update');
    Route::post('/ai/simulate-conversation', [\App\Http\Controllers\AiAgentController::class, 'simulateConversation'])->name('ai.simulate-conversation');
    Route::post('/ai/test-key', [\App\Http\Controllers\AiAgentController::class, 'testApiKey'])->name('ai.test-key');
    Route::post('/ai/leads/{lead}/toggle-autonomous', [\App\Http\Controllers\AiAgentController::class, 'toggleLeadAutonomous'])->name('ai.leads.toggle-autonomous');

    // Conexão WhatsApp & QR Code
    Route::get('/whatsapp/instances', [\App\Http\Controllers\WhatsappInstanceController::class, 'index'])->name('whatsapp.instances.index');
    Route::post('/whatsapp/instances', [\App\Http\Controllers\WhatsappInstanceController::class, 'store'])->name('whatsapp.instances.store');
    Route::get('/whatsapp/instances/{instance}/qr', [\App\Http\Controllers\WhatsappInstanceController::class, 'qrCode'])->name('whatsapp.instances.qr');
    Route::get('/whatsapp/instances/{instance}/status', [\App\Http\Controllers\WhatsappInstanceController::class, 'status'])->name('whatsapp.instances.status');
    Route::post('/whatsapp/instances/{instance}/disconnect', [\App\Http\Controllers\WhatsappInstanceController::class, 'disconnect'])->name('whatsapp.instances.disconnect');
    Route::post('/whatsapp/instances/test-send', [\App\Http\Controllers\WhatsappInstanceController::class, 'testSend'])->name('whatsapp.instances.test-send');
    Route::post('/whatsapp/gateway/ping', [\App\Http\Controllers\WhatsappInstanceController::class, 'pingGateway'])->name('whatsapp.gateway.ping');
});




