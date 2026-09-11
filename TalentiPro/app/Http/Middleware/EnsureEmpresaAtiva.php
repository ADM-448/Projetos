<?php

namespace App\Http\Middleware;

use App\Enums\StatusEmpresa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaAtiva
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Se não estiver logado, redireciona para o login. // Se não tiver empresa, redireciona para o admin.
        if (! $user || $user->empresa_id === null) {

            return $next($request);
        }

        // Verificar se tem Empresa
        $empresa = $user->empresa;

        // Valida se empresa existe ou está ativa
        if (! $empresa || $empresa->status !== StatusEmpresa::ATIVA) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Acesso suspenso. A assinatura da sua empresa está inativa ou pendente. Entre em contato com o suporte']);
        }

        return $next($request);
    }
}
