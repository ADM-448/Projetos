<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VagaController;
use Illuminate\Support\Facades\Route;

// Redirecionamento da rota raiz
Route::get('/', function () {
    return redirect()->route('vagas.index');
});

// Rotas públicas (Visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Rotas protegidas (Usuários Autenticados)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('vagas', VagaController::class);
});
