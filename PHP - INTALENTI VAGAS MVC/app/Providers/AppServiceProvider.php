<?php

namespace App\Providers;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $notificacoesUsuario = $user->notificacoes()
                    ->orderBy('lida', 'asc')
                    ->orderByDesc('created_at')
                    ->take(15)
                    ->get();
                $notificacoesNaoLidasCount = $user->notificacoes()->where('lida', false)->count();

                $view->with('notificacoesUsuario', $notificacoesUsuario);
                $view->with('notificacoesNaoLidasCount', $notificacoesNaoLidasCount);
            }
        });
    }
}
