<?php

use App\Http\Controllers\SalaryDashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

// Redireciona a raiz direto pro dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard e funcionalidades principais — sem auth
Route::get('/dashboard', [SalaryDashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/market-sync', [SalaryDashboardController::class, 'syncMarketData'])->name('dashboard.market-sync');
Route::post('/dashboard/market-sync-all', [SalaryDashboardController::class, 'syncAllMarketData'])->name('dashboard.market-sync-all');
Route::post('/import', [ImportController::class, 'store'])->name('import');
Route::get('/template/download', [TemplateController::class, 'download'])->name('template.download');
