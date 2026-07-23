<?php

namespace App\Providers;

use App\Models\Notificacao;
use App\Observers\NotificacaoObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registra o observer de WhatsApp.
        // A partir daqui, toda Notificacao::create() do sistema
        // disparará automaticamente a mensagem no WhatsApp do destinatário
        // (se ele tiver telefone cadastrado e WHATSAPP_ENABLED=true no .env).
        Notificacao::observe(NotificacaoObserver::class);
    }
}
