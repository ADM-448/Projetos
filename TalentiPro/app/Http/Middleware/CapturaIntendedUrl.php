<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CapturaIntendedUrl
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Captura o parâmetro 'redirect_to' ou 'redirect'
        $redirectTo = $request->input('redirect_to') ?? $request->input('redirect');

        if ($redirectTo) {
            $redirectTo = '/' . ltrim(urldecode($redirectTo), '/');

            // Valida se é um caminho relativo válido
            if (!str_starts_with($redirectTo, '//') && $redirectTo !== '/' && $redirectTo !== '/login') {
                // Se já estiver logado, manda ele direto pra tela da tarefa
                if (auth()->check()) {
                    return redirect($redirectTo);
                }
                
                // Se não estiver logado, salva na sessão pra usar após o login
                session()->put('url.intended', url($redirectTo));
            }
        }

        return $next($request);
    }
}
