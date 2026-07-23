<?php

use App\Jobs\ProcessarFilaWhatsApp;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Temporarily disabled for debugging
        // $middleware->web(append: [
        //     \App\Http\Middleware\EnsureUserBelongsToEmpresa::class,
        // ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Processa a fila de mensagens WhatsApp pendentes a cada 10 minutos.
        // Reenvio automático quando o WhatsApp cai e volta a funcionar.
        $schedule->job(new ProcessarFilaWhatsApp)->everyTenMinutes()
                 ->withoutOverlapping()
                 ->name('whatsapp-queue');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

