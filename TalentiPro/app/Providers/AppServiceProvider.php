<?php

namespace App\Providers;

use App\Contracts\CargoServiceContract;
use App\Contracts\ColaboradorServiceContract;
use App\Contracts\CompetenciaServiceContract;
use App\Contracts\AvaliacaoServiceContract;
use App\Contracts\DepartamentoServiceContract;
use App\Contracts\EmpresaServiceContract;
use App\Contracts\PdiServiceContract;
use App\Models\Cargo;
use App\Models\Competencia;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Pdi;
use App\Models\PdiGrupo;
use App\Models\User;
use App\Policies\CargoPolicy;
use App\Policies\ColaboradorPolicy;
use App\Policies\CompetenciaPolicy;
use App\Policies\DepartamentoPolicy;
use App\Policies\EmpresaPolicy;
use App\Policies\PdiPolicy;
use App\Services\CargoService;
use App\Services\ColaboradorService;
use App\Services\CompetenciaService;
use App\Services\DepartamentoService;
use App\Services\EmpresaService;
use App\Services\AvaliacaoService;
use App\Services\PdiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => ColaboradorPolicy::class,
        Departamento::class => DepartamentoPolicy::class,
        Cargo::class => CargoPolicy::class,
        Empresa::class => EmpresaPolicy::class,
        Competencia::class => CompetenciaPolicy::class,
        Pdi::class => PdiPolicy::class,
        PdiGrupo::class => PdiPolicy::class,
    ];

    /**
     * Registra os bindings de interfaces -> implementações no container do Laravel.
     *
     * Isso é o Princípio D do SOLID (Inversão de Dependência):
     * os Controllers dependem dos Contracts (abstrações),
     * e o container resolve qual implementação concreta injetar.
     */
    public function register(): void
    {
        $this->app->bind(ColaboradorServiceContract::class, ColaboradorService::class);
        $this->app->bind(DepartamentoServiceContract::class, DepartamentoService::class);
        $this->app->bind(CargoServiceContract::class, CargoService::class);
        $this->app->bind(EmpresaServiceContract::class, EmpresaService::class);
        $this->app->bind(CompetenciaServiceContract::class, CompetenciaService::class);
        $this->app->bind(AvaliacaoServiceContract::class, AvaliacaoService::class);
        $this->app->bind(PdiServiceContract::class, PdiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
