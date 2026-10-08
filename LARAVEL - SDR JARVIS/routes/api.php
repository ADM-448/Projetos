<?php

use App\Http\Controllers\Api\WhatsappWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui são registradas as rotas de API do SDR JARVIS.
|
*/

// Webhook da Evolution API (suporta receber com ou sem o nome da instância na URL)
Route::post('/webhooks/whatsapp/{instance?}', [WhatsappWebhookController::class, 'handle'])->name('webhooks.whatsapp');

// Webhook do FreePBX / Asterisk CDR (Call Detail Records e Gravações de Áudio)
Route::post('/webhooks/freepbx/cdr', [\App\Http\Controllers\VoipController::class, 'cdrWebhook'])->name('webhooks.freepbx.cdr');

