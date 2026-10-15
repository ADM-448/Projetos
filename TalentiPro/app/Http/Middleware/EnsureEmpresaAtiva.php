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

        // Tratamento especial para o perfil Consultor (multi-empresa)
        if ($user && method_exists($user, 'isConsultor') && $user->isConsultor()) {
            // Se estiver no hub de seleção ou selecionando empresa, libera a navegação
            if ($request->routeIs('consultor.empresas.hub') || $request->routeIs('consultor.selecionar-empresa')) {
                return $next($request);
            }

            if (!$user->empresa_id || !$user->empresa || $user->empresa->status !== StatusEmpresa::ATIVA) {
                $primeiraAtiva = $user->empresasConsultoria()
                    ->where('status', StatusEmpresa::ATIVA)
                    ->first();

                if ($primeiraAtiva) {
                    $user->update(['empresa_id' => $primeiraAtiva->id]);
                    $user->setRelation('empresa', $primeiraAtiva);
                } else {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors(['login' => 'Nenhuma empresa ativa vinculada ao seu perfil de Consultor. Entre em contato com o suporte.']);
                }
            }

            return $next($request);
        }

        // Se não estiver logado ou for admin global sem empresa fixa
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
