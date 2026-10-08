<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Garante que o usuário autenticado seja um Super Administrador Global.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acesso restrito ao Super Administrador Global.',
                ], 403);
            }

            return redirect()->back()->with('error', 'Acesso negado: funcionalidade restrita ao Super Administrador Global.');
        }

        return $next($request);
    }
}
