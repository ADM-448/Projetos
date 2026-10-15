<?php

use App\Http\Controllers\ConvencaoController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SalaryDashboardController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

// Redireciona a raiz direto pro dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard e funcionalidades principais — com Rate Limiting
Route::middleware(['throttle:30,1'])->group(function () {
    Route::get('/dashboard', [SalaryDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/market-sync', [SalaryDashboardController::class, 'syncMarketData'])->name('dashboard.market-sync');
    Route::post('/dashboard/market-sync-all', [SalaryDashboardController::class, 'syncAllMarketData'])->name('dashboard.market-sync-all');
    Route::post('/import', [ImportController::class, 'store'])->name('import');
    Route::get('/template/download', [TemplateController::class, 'download'])->name('template.download');

    // CCT Routes
    Route::post('/convencoes', [ConvencaoController::class, 'store'])->name('convencoes.store');
    Route::put('/convencoes/{convencao}', [ConvencaoController::class, 'update'])->name('convencoes.update'); // <-- ADICIONE ESTA LINHA
    Route::delete('/convencoes/{convencao}', [ConvencaoController::class, 'destroy'])->name('convencoes.destroy');

    // Módulo Plano de Cargos e Salários & Relatórios Inteligentes
    Route::post('/plano-cargos/pesquisa-cep', [\App\Http\Controllers\PlanoCargosController::class, 'pesquisarMercadoPorCep'])->name('plano-cargos.pesquisa-cep');
    Route::post('/plano-cargos/exportar-word', [\App\Http\Controllers\PlanoCargosController::class, 'exportarDescricaoWord'])->name('plano-cargos.exportar-word');
    Route::get('/plano-cargos/exportar-pdf', [\App\Http\Controllers\PlanoCargosController::class, 'exportarRelatorioPdf'])->name('plano-cargos.exportar-pdf');
});

