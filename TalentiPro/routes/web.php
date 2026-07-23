<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    DashboardController,
    DepartamentoWebController,
    CargoWebController,
    ColaboradorWebController,
    CompetenciaWebController,
    AvaliacaoWebController,
    PdiWebController,
    RelatorioWebController,
    SolicitacaoWebController,
    EmpresaWebController,
    AuthController,
    ReuniaoFeedbackWebController,
    GoogleConfigController,
    WhatsAppController
};

// Redireciona a raiz para o login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de Autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rota de Seleção de Empresa (Login Multi-Empresa)
Route::get('/login/select-company', [AuthController::class, 'showSelectCompany'])->name('auth.select-company');
Route::post('/login/select-company', [AuthController::class, 'selectCompany'])->name('auth.select-company.post');

// GRUPO PROTEGIDO (Só acessa logado)
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pesquisa de Clima Organizacional
    Route::get('/clima', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'index'])->name('clima.index');
    Route::get('/clima/criar', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'create'])->name('clima.create');
    Route::post('/clima', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'store'])->name('clima.store');
    Route::get('/clima/{id}', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'show'])->name('clima.show');
    Route::post('/clima/{id}/responder', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'submit'])->name('clima.submit');
    Route::post('/clima/{id}/abrir', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'abrir'])->name('clima.abrir');
    Route::post('/clima/{id}/encerrar', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'encerrar'])->name('clima.encerrar');
    Route::post('/clima/{id}/toggle-gestor', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'toggleGestor'])->name('clima.toggle-gestor');
    Route::post('/clima/{id}/toggle-colaborador', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'toggleColaborador'])->name('clima.toggle-colaborador');
    Route::get('/clima/{id}/resultados', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'dashboard'])->name('clima.dashboard');
    Route::get('/clima/{id}/auditoria', [\App\Http\Controllers\Web\ClimateSurveyWebController::class, 'auditoria'])->name('clima.auditoria');

    // 2. Cadastros (Departamento, Cargos, Colaboradores)
    Route::resource('departamentos', DepartamentoWebController::class);
    Route::resource('cargos', CargoWebController::class);
    Route::resource('colaboradores', ColaboradorWebController::class);

    Route::post('/colaboradores/importar', [ColaboradorWebController::class, 'importar'])->name('colaboradores.importar');
    // Rota extra para "Contas" - Gerenciar acesso ao sistema
    Route::get('/contas', [ColaboradorWebController::class, 'contas'])->name('contas.index');

    // Rota para "Minha Conta"
    Route::get('/minha-conta', [ColaboradorWebController::class, 'meuPerfil'])->name('perfil.index');

    // Gestão de Empresas (Admin only)
    Route::get('/empresas', [EmpresaWebController::class, 'index'])->name('empresas.index');
    Route::post('/empresas', [EmpresaWebController::class, 'store'])->name('empresas.store');
    Route::put('/empresas/{empresa}', [EmpresaWebController::class, 'update'])->name('empresas.update');
    Route::post('/empresas/{empresa}/toggle', [EmpresaWebController::class, 'toggleStatus'])->name('empresas.toggle');
    Route::delete('/empresas/{empresa}', [EmpresaWebController::class, 'destroy'])->name('empresas.destroy');

    // Configurações do Google (Admin/Master)
    Route::get('/configuracoes/google', [GoogleConfigController::class, 'index'])->name('configuracoes.google');
    Route::post('/configuracoes/google', [GoogleConfigController::class, 'store'])->name('configuracoes.google.store');

    // Configuração WhatsApp (apenas Master)
    Route::get('/configuracoes/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.config');
    Route::post('/configuracoes/whatsapp/teste', [WhatsAppController::class, 'teste'])->name('whatsapp.teste');
    Route::post('/configuracoes/whatsapp/reprocessar', [WhatsAppController::class, 'reprocessar'])->name('whatsapp.reprocessar');

    // 3. Treinamentos (Competências e Avaliações)
    Route::post('/competencias/importar', [CompetenciaWebController::class, 'importar'])->name('competencias.importar');
    Route::resource('competencias', CompetenciaWebController::class);

    
    // Minhas Avaliações (Tela de Responder)
    Route::get('/avaliacoes/gerenciar', [AvaliacaoWebController::class, 'gerenciarAvaliacoes'])->name('avaliacoes.gerenciar');
    Route::get('/avaliacoes/mediar', [AvaliacaoWebController::class, 'mediarAvaliacoes'])->name('avaliacoes.mediar'); // Nova Rota Gestor
    // Relatórios
    Route::get('/avaliacoes/relatorios', [AvaliacaoWebController::class, 'relatoriosIndividuais'])->name('avaliacoes.relatorios');
    Route::get('/avaliacoes/meus-relatorios', [AvaliacaoWebController::class, 'meusRelatorios'])->name('avaliacoes.relatorios.meus');
    Route::get('/avaliacoes/relatorios/mediacao', [AvaliacaoWebController::class, 'relatoriosMediacao'])->name('avaliacoes.relatorios.mediacao'); // Nova Rota // Nova Rota Gestor
    Route::get('/minhas-avaliacoes', [AvaliacaoWebController::class, 'pendentes'])->name('avaliacoes.pendentes');
    Route::post('/avaliacoes/responder', [AvaliacaoWebController::class, 'responder'])->name('avaliacoes.responder');
    Route::post('/avaliacoes/reabrir', [AvaliacaoWebController::class, 'reabrirAvaliacao'])->name('avaliacoes.reabrir');

    Route::put('/avaliacoes/{id}/status/{status}', [AvaliacaoWebController::class, 'alterarStatus'])->name('avaliacoes.status');
    Route::get('/api/avaliacao-detalhes/{id}', [AvaliacaoWebController::class, 'getDetalhes']); // Fixed 'show' to 'getDetalhes' just in case
    Route::resource('avaliacoes', AvaliacaoWebController::class);

    // APIs para os Modais
    Route::get('/api/avaliacao/{id}/detalhes', [AvaliacaoWebController::class, 'getDetalhes']); // Ver Mais (Gestão)
    Route::get('/api/avaliacao/{id}/alvos', [AvaliacaoWebController::class, 'getMeusAlvos']);   // Responder (Execução)
    Route::get('/api/avaliacao-participante/{id}/respostas', [AvaliacaoWebController::class, 'getRespostas']); // Carregar Respostas (Edição)
    Route::get('/api/avaliacao/{id}/participantes', [AvaliacaoWebController::class, 'getAlvosDaAvaliacao']); // Listar Avaliados (Relatório)


    // Rota da API usada no Dashboard/Charts se houver
    Route::get('/api/avaliacao-dados/{id}', [AvaliacaoWebController::class, 'buscarDadosAvaliacao']);
    Route::get('/api/avaliacao-dashboard/{id}', [AvaliacaoWebController::class, 'dadosDashboard']);


    // 4. PDI
    // Rotas customizadas ANTES do resource para não cair no {pdi}
    Route::post('/pdis/update-group', [PdiWebController::class, 'updateGroup'])->name('pdis.update.group');
    Route::delete('/pdis/destroy-group', [PdiWebController::class, 'destroyGroup'])->name('pdis.destroy.group');
    Route::get('/pdis/destroy-group', function() { return redirect()->route('pdis.index'); }); // Fallback for refresh
    Route::get('/meus-pdis', [PdiWebController::class, 'meusPdis'])->name('pdis.meus');
    Route::post('/pdis/{id}/comentar', [PdiWebController::class, 'salvarComentario'])->name('pdis.comentar');
    Route::post('/pdis/acao/{id}/toggle', [PdiWebController::class, 'toggleAcao'])->name('pdis.acao.toggle');
    
    Route::post('/pdis/{id}/comprovante', [PdiWebController::class, 'uploadComprovante'])->name('pdis.upload');
    Route::get('/pdis/{id}/download-comprovante', [PdiWebController::class, 'downloadComprovante'])->name('pdis.download');
    Route::resource('pdis', PdiWebController::class)->except(['show', 'create', 'edit']);

    // 5. Reuniões de Feedback (IA)
    Route::get('/reunioes-feedback', [ReuniaoFeedbackWebController::class, 'index'])->name('reunioes.index');
    Route::post('/reunioes-feedback/agendar', [ReuniaoFeedbackWebController::class, 'storeAgendamento'])->name('reunioes.agendar');
    Route::get('/reunioes-feedback/sala/{id}', [ReuniaoFeedbackWebController::class, 'salaVideo'])->name('reunioes.sala');
    Route::get('/reunioes-feedback/nova', [ReuniaoFeedbackWebController::class, 'create'])->name('reunioes.create');
    Route::post('/reunioes-feedback/processar', [ReuniaoFeedbackWebController::class, 'processar'])->name('reunioes.processar');
    Route::post('/reunioes-feedback/salvar', [ReuniaoFeedbackWebController::class, 'store'])->name('reunioes.store');
    Route::get('/reunioes-feedback/{id}', [ReuniaoFeedbackWebController::class, 'show'])->name('reunioes.show');
    Route::delete('/reunioes-feedback/{id}', [ReuniaoFeedbackWebController::class, 'destroy'])->name('reunioes.destroy');
    Route::delete('/reunioes-feedback/agendamento/{id}', [ReuniaoFeedbackWebController::class, 'destroyAgendamento'])->name('reunioes.agendamento.destroy');
    
    // API interna para transcrição
    Route::post('/api/reunioes/fetch-transcript', [ReuniaoFeedbackWebController::class, 'fetchTranscriptFromDrive'])->name('reunioes.fetch_transcript');
    Route::post('/api/reunioes/upload-audio', [ReuniaoFeedbackWebController::class, 'uploadAudioAndProcess'])->name('reunioes.upload_audio');

    // Integração Google Drive
    Route::get('/auth/google/redirect', [\App\Http\Controllers\Web\GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Web\GoogleAuthController::class, 'callback'])->name('google.callback');
    Route::post('/auth/google/unlink', [\App\Http\Controllers\Web\GoogleAuthController::class, 'unlink'])->name('google.unlink');
    Route::post('/auth/google/unlink-empresa', [\App\Http\Controllers\Web\GoogleAuthController::class, 'unlinkEmpresa'])->name('google.unlink.empresa');

    // 6. Relatórios e Solicitações
    Route::get('/relatorios/individuais', [AvaliacaoWebController::class, 'relatoriosIndividuais'])->name('relatorios.individuais');
    Route::get('/api/relatorio/dados', [AvaliacaoWebController::class, 'getDadosRelatorio'])->name('relatorios.dados');
    Route::get('/relatorios/exportar-pdf', [AvaliacaoWebController::class, 'exportarPdf'])->name('relatorios.exportar.pdf');
    Route::post('/api/relatorio/salvar-fechamento', [AvaliacaoWebController::class, 'salvarFechamento']);
    
    // Solicitações RH
    Route::get('/solicitacoes', [SolicitacaoWebController::class, 'index'])->name('solicitacoes.index');
    Route::post('/solicitacoes/{id}/aprovar', [SolicitacaoWebController::class, 'aprovar'])->name('solicitacoes.aprovar');
    Route::post('/solicitacoes/{id}/recusar', [SolicitacaoWebController::class, 'recusar'])->name('solicitacoes.recusar');
    Route::post('/solicitacoes', [SolicitacaoWebController::class, 'store'])->name('solicitacoes.store');
    // 6. Notificações
    Route::get('/api/notificacoes', [App\Http\Controllers\Web\NotificacaoWebController::class, 'index']);
    Route::get('/api/notificacoes/count', [App\Http\Controllers\Web\NotificacaoWebController::class, 'count']);
    Route::post('/api/notificacoes/{id}/ler', [App\Http\Controllers\Web\NotificacaoWebController::class, 'ler']);
    Route::post('/api/notificacoes/ler-todas', [App\Http\Controllers\Web\NotificacaoWebController::class, 'lerTodas']);

});

// ⚠️ ROTA TEMPORÁRIA - Criar link simbólico do storage. DELETAR após usar!
Route::get('/fix-storage-link', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');

    if (is_link($link)) {
        return '✅ Link simbólico já existe! Pode deletar esta rota.';
    }

    if (file_exists($link) && !is_link($link)) {
        return '📁 A pasta public/storage já existe como diretório real (não é symlink). Mova os arquivos de <b>storage/app/public/pdi_docs/</b> para <b>public/storage/pdi_docs/</b> manualmente pelo FileZilla.';
    }

    if (@symlink($target, $link)) {
        return '✅ Link simbólico criado com sucesso! Agora delete esta rota do web.php.';
    }

    return '❌ Seu servidor não permite symlinks automáticos. Crie manualmente pelo FileZilla a pasta <b>public/storage/</b> e copie os arquivos de <b>storage/app/public/pdi_docs/</b> para <b>public/storage/pdi_docs/</b>.';
});
