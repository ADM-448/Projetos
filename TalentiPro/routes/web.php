<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\AvaliacaoRespostaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\PdiController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\ReuniaoFeedbackController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PÁGINA INICIAL (Pública para o Google)
// ==========================================
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==========================================
// PÁGINAS INSTITUCIONAIS E LEGAIS
// ==========================================
Route::get('/politica-privacidade', function () {
    return view('pages.politica-privacidade');
})->name('politica.privacidade');

Route::get('/termos-uso', function () {
    return view('pages.termos-uso');
})->name('termos.uso');

// Rotas de Autenticação (Públicas / Acessíveis para Visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rotas Protegidas (Exigem Autenticação)
Route::middleware(['auth', 'empresa.ativa'])->group(function () {

    // Rota de Logout (POST obrigatório por segurança)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard do Tenant (Master, Gestor, Colaborador)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('role:master,gestor,colaborador');

    // APIs Internas do Dashboard (Modais)
    Route::middleware('role:master,gestor')->group(function () {
        Route::get('/dashboard/api/avaliacoes-ativas', [DashboardController::class, 'apiAvaliacoesAtivas'])->name('dashboard.api.avaliacoes-ativas');
        Route::get('/dashboard/api/pdis-pendentes', [DashboardController::class, 'apiPdisPendentes'])->name('dashboard.api.pdis-pendentes');
    });

    // Meus Dados (Perfil)
    Route::get('/meus-dados', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/meus-dados', [ProfileController::class, 'update'])->name('profile.update');

    // Painel Administrativo Global (Apenas para o Admin)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminIndex'])->name('dashboard');
        
        // Rotas de gestão de tenants com throttle restritivo para escrita
        Route::middleware('throttle:30,1')->group(function () {
            Route::resource('empresas', EmpresaController::class)->except(['show']);
        });
    });

    // Relatórios de Avaliações (Público autenticado)
    Route::get('avaliacoes/meus-relatorios', [\App\Http\Controllers\AvaliacaoRelatorioController::class, 'meusRelatorios'])->name('avaliacoes.relatorios.meus');
    Route::get('api/relatorios/alvos/{avaliacao_id}', [\App\Http\Controllers\AvaliacaoRelatorioController::class, 'getAlvosDaAvaliacao']);
    Route::get('api/relatorios/dados', [\App\Http\Controllers\AvaliacaoRelatorioController::class, 'getDadosRelatorio'])->name('relatorios.dados');
    Route::get('avaliacoes/relatorios/pdf', [\App\Http\Controllers\AvaliacaoRelatorioController::class, 'exportarPdf'])->name('relatorios.exportar.pdf');

    // Rotas de Gestão de RH (Apenas Master e Gestor)
    Route::middleware(['role:master,gestor'])->group(function () {

        // Rotas de LEITURA — throttle generoso (120 req/min)
        // Protege contra raspagem e crawlers sem prejudicar uso normal
        Route::middleware('throttle:120,1')->group(function () {
            Route::get('departamentos', [DepartamentoController::class, 'index'])->name('departamentos.index');
            Route::get('cargos', [CargoController::class, 'index'])->name('cargos.index');
            Route::get('colaboradores', [ColaboradorController::class, 'index'])->name('colaboradores.index');
        });

        // Rotas de ESCRITA — throttle restritivo (30 req/min)
        // Protege criação, edição e exclusão contra abuso automatizado
        Route::middleware('throttle:30,1')->group(function () {
            Route::resource('departamentos', DepartamentoController::class)->except(['index']);
            Route::resource('cargos', CargoController::class)->except(['index']);
            Route::post('colaboradores/importar', [ColaboradorController::class, 'importar'])->name('colaboradores.importar');
            Route::delete('colaboradores/lote', [ColaboradorController::class, 'destroyLote'])->name('colaboradores.destroyLote');
            Route::resource('colaboradores', ColaboradorController::class)->except(['index']);
        });

        // Competências e Skills
        Route::post('competencias/importar', [CompetenciaController::class, 'importar'])->name('competencias.importar');
        Route::resource('competencias', CompetenciaController::class)->except(['show', 'create', 'edit']);
        Route::get('competencias/{competencia}/skills', [SkillController::class, 'index'])->name('skills.index');
        Route::post('competencias/{competencia}/skills', [SkillController::class, 'store'])->name('skills.store');
        Route::delete('competencias/{competencia}/skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');

        // Relatórios de Avaliações (Equipe)
        Route::get('avaliacoes/relatorios', [\App\Http\Controllers\AvaliacaoRelatorioController::class, 'index'])->name('avaliacoes.relatorios');
        
        // Avaliações (Gestão)
        Route::resource('avaliacoes', AvaliacaoController::class)->parameters([
            'avaliacoes' => 'avaliacao'
        ]);
        Route::get('avaliacoes/{avaliacao}/mediar/{avaliado}', [AvaliacaoController::class, 'mediar'])->name('avaliacoes.mediar');
        Route::post('avaliacoes/{avaliacao}/mediar/{avaliado}', [AvaliacaoController::class, 'salvarFechamento'])->name('avaliacoes.salvar-fechamento');
        
        // IA para Relatórios
        Route::post('avaliacoes/gerar-pdi-ia', [\App\Http\Controllers\AvaliacaoRelatorioController::class, 'gerarPdiIa'])->name('avaliacoes.gerar_pdi_ia');
    });

    // Responder Avaliações (Todos podem, se houver um participante associado)
    Route::get('minhas-avaliacoes', [AvaliacaoRespostaController::class, 'index'])->name('avaliacoes.minhas');
    Route::post('/lembretes', [App\Http\Controllers\LembreteController::class, 'store'])->name('lembretes.store');
    
    Route::get('/avaliacoes', [AvaliacaoController::class, 'index'])->name('avaliacoes.index');
    Route::get('responder-avaliacao/{participante}', [AvaliacaoRespostaController::class, 'create'])->name('avaliacoes.responder');
    Route::post('responder-avaliacao/{participante}', [AvaliacaoRespostaController::class, 'store'])->name('avaliacoes.store-resposta');

    // Módulo de PDI (Planos de Desenvolvimento Individual)
    Route::resource('pdis', PdiController::class)->parameters([
        'pdis' => 'pdi'
    ]);
    Route::post('pdis/{pdi}/comentarios', [PdiController::class, 'storeComentario'])->name('pdis.comentarios.store');
    Route::post('pdi-acoes/{acao}/toggle', [PdiController::class, 'toggleAcao'])->name('pdi-acoes.toggle');
    Route::post('pdis/{pdi}/comprovante', [PdiController::class, 'uploadComprovante'])->name('pdis.comprovante.upload');
    
    // Configurações de Google Workspace / OAuth
    Route::get('/auth/google', [GoogleOAuthController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleOAuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::post('/auth/google/disconnect', [GoogleOAuthController::class, 'disconnect'])->name('google.disconnect');

    // Reuniões de Feedback (Google Meet / IA)
    Route::resource('reunioes', ReuniaoFeedbackController::class)->parameters([
        'reunioes' => 'reuniao'
    ]);
    Route::get('/reunioes/{reuniao}/sala',                  [ReuniaoFeedbackController::class, 'sala'])->name('reunioes.sala');
    Route::post('/reunioes/{reuniao}/upload-audio',         [ReuniaoFeedbackController::class, 'uploadAudio'])->name('reunioes.upload_audio');
    Route::post('/reunioes/{reuniao}/processar-transcricao',[ReuniaoFeedbackController::class, 'processarTranscricao'])->name('reunioes.processar_transcricao');
    Route::post('/reunioes/{reuniao}/salvar-com-ia',        [ReuniaoFeedbackController::class, 'salvarComIa'])->name('reunioes.salvar_com_ia');
    Route::post('/reunioes/{reuniao}/salvar-manual',        [ReuniaoFeedbackController::class, 'salvarManual'])->name('reunioes.salvar_manual');
    Route::post('/reunioes/{reuniao}/update-registro',      [ReuniaoFeedbackController::class, 'updateRegistro'])->name('reunioes.update_registro');
    Route::get('pdis/{pdi}/comprovante/download', [PdiController::class, 'downloadComprovante'])->name('pdis.comprovante.download');
    Route::delete('pdi-grupos/{grupo}', [PdiController::class, 'destroyGrupo'])->name('pdi-grupos.destroy');
    Route::get('pdi-grupos/{grupo}/edit', [PdiController::class, 'editGrupo'])->name('pdi-grupos.edit');
    Route::put('pdi-grupos/{grupo}', [PdiController::class, 'updateGrupo'])->name('pdi-grupos.update');

    // Notificações
    Route::get('notificacoes/{id}/ler', [\App\Http\Controllers\NotificacaoController::class, 'ler'])->name('notificacoes.ler');
    Route::post('notificacoes/ler-todas', [\App\Http\Controllers\NotificacaoController::class, 'lerTodas'])->name('notificacoes.ler-todas');

    // ── Módulo de Solicitações (Reaberturas e Contestações) ──────────────
    // Qualquer usuário autenticado pode ver a listagem (filtrada por perfil no controller)
    Route::get('solicitacoes', [SolicitacaoController::class, 'index'])->name('solicitacoes.index');
    // Apenas colaboradores criam (Policy::create valida)
    Route::post('solicitacoes', [SolicitacaoController::class, 'store'])->name('solicitacoes.store');
    // Apenas master/gestor aprovam ou recusam (Policy::aprovar / Policy::recusar validam)
    Route::post('solicitacoes/{solicitacao}/aprovar', [SolicitacaoController::class, 'aprovar'])->name('solicitacoes.aprovar');
    Route::post('solicitacoes/{solicitacao}/recusar', [SolicitacaoController::class, 'recusar'])->name('solicitacoes.recusar');

    // Integrações & Configurações (Apenas Master e Admin)
    Route::middleware('role:master,admin')->group(function () {
        Route::get('/configuracoes/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.config');
        Route::post('/configuracoes/whatsapp/teste', [WhatsAppController::class, 'teste'])->name('whatsapp.teste');
        Route::post('/configuracoes/whatsapp/reprocessar', [WhatsAppController::class, 'reprocessar'])->name('whatsapp.reprocessar');
    });

    // Módulo DISC
    Route::get('/disc/teste', [\App\Http\Controllers\DiscAssessmentController::class, 'create'])->name('disc.take');
    Route::post('/disc/teste', [\App\Http\Controllers\DiscAssessmentController::class, 'store'])->name('disc.store');
    Route::post('/disc/refazer', [\App\Http\Controllers\DiscAssessmentController::class, 'retake'])->name('disc.retake');
    Route::post('/disc/solicitar-refazer', [\App\Http\Controllers\DiscSolicitacaoController::class, 'store'])->name('disc.solicitar-refazer');
    Route::get('/disc/resultado/{assessment}', [\App\Http\Controllers\DiscAssessmentController::class, 'show'])->name('disc.result');
    Route::get('/disc/equipe', [\App\Http\Controllers\DiscAssessmentController::class, 'equipe'])->name('disc.equipe');

    // Módulo Pesquisa de Clima (Colaborador)
    Route::get('/clima', [\App\Http\Controllers\ClimateSurveyController::class, 'minhas'])->name('clima.minhas');
    Route::get('/clima/responder/{survey}', [\App\Http\Controllers\ClimateSurveyController::class, 'responder'])->name('clima.responder');
    Route::post('/clima/responder/{survey}', [\App\Http\Controllers\ClimateSurveyController::class, 'salvarResposta'])->name('clima.salvar-resposta');

    // Módulo Pesquisa de Clima (Gestão/RH)
    Route::get('/gestao/clima', [\App\Http\Controllers\ClimateSurveyController::class, 'index'])->name('gestao.clima.index');
    Route::get('/gestao/clima/criar', [\App\Http\Controllers\ClimateSurveyController::class, 'create'])->name('gestao.clima.create');
    Route::post('/gestao/clima', [\App\Http\Controllers\ClimateSurveyController::class, 'store'])->name('gestao.clima.store');
    Route::get('/gestao/clima/{survey}/editar', [\App\Http\Controllers\ClimateSurveyController::class, 'edit'])->name('gestao.clima.edit');
    Route::put('/gestao/clima/{survey}', [\App\Http\Controllers\ClimateSurveyController::class, 'update'])->name('gestao.clima.update');
    Route::get('/gestao/clima/{survey}/relatorio', [\App\Http\Controllers\ClimateSurveyController::class, 'relatorio'])->name('gestao.clima.relatorio');
    Route::post('/gestao/clima/{survey}/encerrar', [\App\Http\Controllers\ClimateSurveyController::class, 'encerrar'])->name('gestao.clima.encerrar');
    Route::post('/gestao/clima/{survey}/gerar-pdi-ia', [\App\Http\Controllers\ClimateSurveyController::class, 'gerarPdiClimaIa'])->name('gestao.clima.gerar_pdi_ia');
    Route::delete('/gestao/clima/{survey}', [\App\Http\Controllers\ClimateSurveyController::class, 'destroy'])->name('gestao.clima.destroy');
    Route::delete('/gestao/clima/templates/{template}', [\App\Http\Controllers\ClimateSurveyController::class, 'destroyTemplate'])->name('gestao.clima.templates.destroy');
});
