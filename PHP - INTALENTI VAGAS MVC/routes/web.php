<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VagaController;
use App\Http\Controllers\CurriculoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrefeituraController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmpresaController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', [VagaController::class, 'index'])->name('home');
Route::get('/vagas', [VagaController::class, 'index'])->name('vagas.index');
Route::get('/vagas/{id}', [VagaController::class, 'show'])->name('vagas.show')->whereNumber('id');
Route::post('/vagas/{id}/salvar', [VagaController::class, 'toggleSalvar'])->name('vagas.salvar')->whereNumber('id');


// Autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Endpoint Assíncrono de CEP
Route::get('/api/cep/{cep}', [CurriculoController::class, 'buscaCep'])->name('api.cep');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas: Perfil Candidato
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:candidato'])->group(function () {
    Route::get('/curriculo', [CurriculoController::class, 'edit'])->name('curriculo.edit');
    Route::post('/curriculo', [CurriculoController::class, 'update'])->name('curriculo.update');
    Route::post('/vagas/{id}/aplicar', [VagaController::class, 'aplicar'])->name('vagas.aplicar')->whereNumber('id');
    Route::post('/candidaturas/{id}/cancelar', [VagaController::class, 'cancelarCandidatura'])->name('candidaturas.cancelar')->whereNumber('id');
    Route::get('/minhas-candidaturas', [DashboardController::class, 'minhasCandidaturas'])->name('minhas.candidaturas');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas: Perfil Empresa / RH / Recrutador
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:empresa,admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/vagas/nova', [VagaController::class, 'create'])->name('vagas.create');
    Route::post('/vagas', [VagaController::class, 'store'])->name('vagas.store');
    Route::get('/vagas/{id}/editar', [VagaController::class, 'edit'])->name('vagas.edit')->whereNumber('id');
    Route::put('/vagas/{id}', [VagaController::class, 'update'])->name('vagas.update')->whereNumber('id');
    Route::get('/vagas/{id}/candidatos', [VagaController::class, 'candidatos'])->name('vagas.candidatos')->whereNumber('id');
    Route::get('/candidato/perfil/{userId}', [VagaController::class, 'perfilCandidato'])->name('candidatos.perfil')->whereNumber('userId');
    Route::post('/candidaturas/{id}/status', [VagaController::class, 'atualizarStatusCandidatura'])->name('candidaturas.status')->whereNumber('id');

    // Gestão de Equipe da Empresa
    Route::get('/empresa/equipe', [EmpresaController::class, 'equipe'])->name('empresa.equipe');
    Route::post('/empresa/equipe/adicionar', [EmpresaController::class, 'adicionarUsuario'])->name('empresa.equipe.adicionar');
    Route::post('/empresa/equipe/{userId}/funcao', [EmpresaController::class, 'atualizarFuncao'])->name('empresa.equipe.funcao')->whereNumber('userId');
    Route::post('/empresa/equipe/{userId}/status', [EmpresaController::class, 'toggleStatusMembro'])->name('empresa.equipe.status')->whereNumber('userId');
    Route::delete('/empresa/equipe/{userId}', [EmpresaController::class, 'removerMembro'])->name('empresa.equipe.remover')->whereNumber('userId');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas: Observatório do Trabalho (Prefeitura / Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/prefeitura/dashboard', [PrefeituraController::class, 'dashboard'])->name('prefeitura.dashboard');

    // Chat compartilhado entre Recrutador e Candidato
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show')->whereNumber('id');
    Route::post('/chat/{id}/enviar', [ChatController::class, 'enviarMensagem'])->name('chat.enviar')->whereNumber('id');
    Route::get('/chat/{id}/mensagens', [ChatController::class, 'mensagensJson'])->name('chat.mensagens')->whereNumber('id');

    // Notificações
    Route::get('/notificacoes/{id}/abrir', [\App\Http\Controllers\NotificacaoController::class, 'abrir'])->name('notificacoes.abrir')->whereNumber('id');
    Route::post('/notificacoes/marcar-todas-lidas', [\App\Http\Controllers\NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.marcarTodasLidas');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas: Painel do Administrador Geral (Super Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/empresas', [AdminController::class, 'empresas'])->name('empresas');
    Route::get('/dashboard', fn() => redirect()->route('admin.empresas'))->name('dashboard');
    Route::post('/empresas/{id}/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggleStatus')->whereNumber('id');
    Route::post('/empresas/{id}/dominio', [AdminController::class, 'salvarDominio'])->name('salvarDominio')->whereNumber('id');
    Route::delete('/empresas/{id}', [AdminController::class, 'destroy'])->name('destroy')->whereNumber('id');
});

