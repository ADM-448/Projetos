<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  (ex: 'candidato', 'empresa', 'admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Faça login para acessar esta página.');
        }

        $userRoleMap = [
            1 => 'candidato',
            2 => 'empresa',
            3 => 'admin',
        ];

        $userRoleName = $userRoleMap[$user->tipo_usuario] ?? null;

        // Admin tem acesso concedido para rotas de empresa/prefeitura
        if ($user->isAdmin() && (in_array('empresa', $roles) || in_array('admin', $roles))) {
            return $next($request);
        }

        if (!$userRoleName || !in_array($userRoleName, $roles)) {
            // Redirecionamento amigável baseado no perfil real do usuário
            if ($user->isCandidato()) {
                return redirect()->route('vagas.index')
                    ->with('error', 'Acesso restrito. Sua conta possui perfil de Candidato.');
            } elseif ($user->isEmpresa()) {
                return redirect()->route('dashboard')
                    ->with('error', 'Acesso restrito. Sua conta possui perfil de Empresa/Recrutador.');
            } else {
                return redirect()->route('prefeitura.dashboard')
                    ->with('error', 'Acesso restrito ao Observatório/Administração.');
            }
        }

        return $next($request);
    }
}
