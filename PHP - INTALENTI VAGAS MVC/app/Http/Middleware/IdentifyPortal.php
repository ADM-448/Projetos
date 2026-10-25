<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdentifyPortal
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Em cache por 10 minutos para alta performance
        $portal = Cache::remember("portal_host_{$host}", 600, function () use ($host) {
            return DB::table('empresas')
                ->where('tipo', 'prefeitura')
                ->where('dominio', $host)
                ->where('status', 1)
                ->select('id', 'nome_empresa', 'logo_path')
                ->first();
        });

        if ($portal) {
            config(['portal.current_id' => $portal->id]);
            config(['portal.current_name' => $portal->nome_empresa]);
            view()->share('currentPortal', $portal);
        } else {
            // Em ambiente local ou fallback seguro
            if (in_array($host, ['localhost', '127.0.0.1'])) {
                $defaultId = config('portal.default_id', 1);
                $fallback = DB::table('empresas')->where('id', $defaultId)->first();
                config(['portal.current_id' => $defaultId]);
                config(['portal.current_name' => $fallback->nome_empresa ?? 'Portal de Vagas']);
                view()->share('currentPortal', $fallback);
            }
        }

        return $next($request);
    }
}
