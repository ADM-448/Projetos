<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToEmpresa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se o usuário não estiver autenticado, deixa o middleware de auth lidar
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Admin (perfil_acesso = 'admin') pode acessar tudo (multi-empresa)
        if ($user->perfil_acesso === 'admin') {
            return $next($request);
        }

        // Para outros perfis (master, gestor, mediador, colaborador):
        // Garante que o usuário tem empresa_id definida
        if (!$user->empresa_id) {
            // Durante desenvolvimento: apenas avisa no log
            \Log::warning("Usuário {$user->email} sem empresa_id definida");
            // Em produção, você pode descomentar a linha abaixo:
            // abort(403, 'Usuário não está associado a nenhuma empresa.');
        }

        // Armazena a empresa_id na sessão para uso nos controllers
        if ($user->empresa_id) {
            session(['current_empresa_id' => $user->empresa_id]);
        }

        return $next($request);
    }
}
