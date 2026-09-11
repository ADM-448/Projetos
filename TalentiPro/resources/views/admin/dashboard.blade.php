@extends('layouts.app')

@section('title', 'Painel Administrativo SaaS')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title" style="margin-bottom: 5px;">Painel Administrativo Global (SaaS)</h1>
        <p style="color: #666; font-size: 0.95rem;">Controle de assinatura, infraestrutura e monitoramento da plataforma TalentiPro.</p>
    </div>
    
    <div>
        <span style="background-color: #fef9e7; color: #d35400; padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 6px; border: 1px solid #f39c12;">
            <i class="fa-solid fa-server"></i> Status do Sistema: Online
        </span>
    </div>
</div>

{{-- CARDS DE INDICADORES (KPIs) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <!-- Card 1 -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #2980b9;">
        <div style="background: rgba(41, 128, 185, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2980b9; font-size: 1.5rem;">
            <i class="fa-solid fa-building"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Empresas (Tenants)</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $totalEmpresas }}</div>
        </div>
    </div>

    <!-- Card 2 -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #8e44ad;">
        <div style="background: rgba(142, 68, 173, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #8e44ad; font-size: 1.5rem;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Usuários Globais</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">{{ $totalUsuarios }}</div>
        </div>
    </div>

    <!-- Card 3 -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #27ae60;">
        <div style="background: rgba(39, 174, 96, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #27ae60; font-size: 1.5rem;">
            <i class="fa-solid fa-circle-nodes"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Uso de Filas / Redis</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">Ativo</div>
        </div>
    </div>

    <!-- Card 4 -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; border-left: 5px solid #d35400;">
        <div style="background: rgba(211, 84, 0, 0.1); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d35400; font-size: 1.5rem;">
            <i class="fa-solid fa-microchip"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase;">Reqs Gemini IA</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2d3748; line-height: 1.2;">148 / dia</div>
        </div>
    </div>
</div>

{{-- AREA DE CONTEÚDO EM DUAS COLUNAS --}}
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
    
    <!-- Coluna Esquerda: Lista de Tenants (Empresas Cadastradas) -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #2d3748; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-building" style="color: #2980b9;"></i> Empresas Clientes (Tenants)
        </h2>
        
        <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Empresa</th>
                    <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">CNPJ</th>
                    <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Limite Users</th>
                    <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Status</th>
                    <th style="padding: 12px 15px; font-weight: 600; color: #4a5568;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empresas as $empresa)
                <tr style="border-bottom: 1px solid #e2e8f0; hover: background-color: #f8fafc;">
                    <td style="padding: 15px;" data-label="Empresa">
                        <div style="font-weight: bold; color: #2d3748;">{{ $empresa->nome }}</div>
                        <span style="font-size: 0.75rem; color: #718096;">Contratação: {{ $empresa->data_contratacao ? \Carbon\Carbon::parse($empresa->data_contratacao)->format('d/m/Y') : 'N/A' }}</span>
                    </td>
                    <td style="padding: 15px; color: #4a5568; font-size: 0.9rem;" data-label="CNPJ">{{ $empresa->cnpj }}</td>
                    <td style="padding: 15px; color: #4a5568;" data-label="Limite Users">{{ $empresa->max_usuarios }}</td>
                    <td style="padding: 15px;" data-label="Status">
                        @if($empresa->status->value === 'ativa')
                            <span style="background-color: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Ativa</span>
                        @else
                            <span style="background-color: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Suspensa</span>
                        @endif
                    </td>
                    <td style="padding: 15px;" data-label="Ações">
                        <a href="{{ route('admin.empresas.edit', $empresa) }}" style="background: none; border: none; color: #3182ce; text-decoration: none; font-weight: 600; font-size: 0.85rem;" title="Editar Empresa">
                            <i class="fa-solid fa-pen-to-square"></i> Gerenciar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                        Nenhuma empresa cadastrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Coluna Direita: Atalhos SaaS -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 15px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #2d3748; margin-bottom: 10px;">Ações SaaS</h2>
        
        <a href="{{ route('admin.empresas.create') }}" class="btn-primary" style="width: 100%; background: linear-gradient(90deg, #2980b9 0%, #2c3e50 100%); text-align: center; text-decoration: none; display: inline-block;">
            <i class="fa-solid fa-plus"></i> Nova Empresa Cliente
        </a>

        <button class="btn-secondary" style="width: 100%;">
            <i class="fa-solid fa-receipt"></i> Histórico de Assinaturas
        </button>

        <button class="btn-secondary" style="width: 100%;">
            <i class="fa-solid fa-gears"></i> Variáveis de Ambiente IA
        </button>
        
        <div style="margin-top: 15px; padding: 15px; border-radius: 8px; background-color: #fafbfc; border: 1px dashed #ddd;">
            <h4 style="font-size: 0.85rem; font-weight: 700; color: #444; margin-bottom: 5px;"><i class="fa-solid fa-chart-pie"></i> Recursos de Servidor</h4>
            <p style="font-size: 0.8rem; color: #777; margin-bottom: 8px;">Uso de memória e serviços essenciais rodando no container:</p>
            <div style="background: #edf2f7; border-radius: 5px; height: 10px; overflow: hidden; margin-bottom: 5px;">
                <div style="background: #3182ce; width: 45%; height: 100%;"></div>
            </div>
            <span style="font-size: 0.75rem; color: #718096;">RAM: 1.4GB de 4GB em uso</span>
        </div>
    </div>
</div>
@endsection
