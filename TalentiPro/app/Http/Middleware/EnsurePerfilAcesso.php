<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePerfilAcesso
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        $user = $request->user();

        // 1. Se não estiver logado, bloqueia
        if (! $user) {
            return redirect()->route('login');
        }

        // As vezes o Laravel passa 'admin,master' como uma única string no array.
        // Vamos separar tudo por vírgula para garantir
        $allowedProfiles = [];
        foreach ($perfis as $p) {
            $allowedProfiles = array_merge($allowedProfiles, explode(',', $p));
        }
        $allowedProfiles = array_map('trim', $allowedProfiles);

        $userProfileValue = $user->perfil_acesso instanceof \BackedEnum 
            ? $user->perfil_acesso->value 
            : $user->perfil_acesso;

        if (! in_array($userProfileValue, $allowedProfiles)) {
            abort(403, 'Acesso não autorizado para o seu perfil.');
        }

        return $next($request);
    }
}
