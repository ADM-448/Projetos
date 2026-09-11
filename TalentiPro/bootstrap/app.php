<?php

use App\Http\Middleware\EnsureEmpresaAtiva;
use App\Http\Middleware\EnsurePerfilAcesso;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'empresa.ativa' => EnsureEmpresaAtiva::class,
            'role' => EnsurePerfilAcesso::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user && $user->perfil_acesso === \App\Enums\UserRole::ADMIN) {
                return '/admin/dashboard';
            }
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->withErrors(['email' => 'Sua sessão expirou por inatividade. Por favor, tente novamente.']);
        });
    })
    ->create();
