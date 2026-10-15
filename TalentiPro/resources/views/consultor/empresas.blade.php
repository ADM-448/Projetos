@extends('layouts.app')

@section('title', 'Portal do Consultor — Selecionar Empresa')

@section('content')
<div style="min-height: calc(100vh - 80px); background: #f8fafc; padding: 40px 20px;">
    <div style="max-width: 1100px; margin: 0 auto;">
        
        {{-- Cabeçalho do Hub --}}
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 16px; color: white; font-size: 1.8rem; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3); margin-bottom: 16px;">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0 0 10px 0; letter-spacing: -0.02em;">
                Portal de Consultoria Analítica
            </h1>
            <p style="font-size: 1.05rem; color: #64748b; margin: 0 auto; max-width: 600px;">
                Olá, <strong>{{ $user->name }}</strong>! Selecione a empresa cliente que você deseja analisar no momento.
            </p>
        </div>

        {{-- Alertas de Sucesso / Erro --}}
        @if(session('success'))
            <div style="background-color: #d1fae5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Grid de Empresas Vinculadas --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 40px;">
            @forelse($empresas as $empresa)
                @php
                    $isAtivaAtual = ($empresa->id == $user->empresa_id);
                @endphp
                <div style="background: white; border-radius: 16px; border: {{ $isAtivaAtual ? '2px solid #3b82f6' : '1px solid #e2e8f0' }}; box-shadow: {{ $isAtivaAtual ? '0 10px 30px rgba(59, 130, 246, 0.15)' : '0 4px 16px rgba(0,0,0,0.05)' }}; padding: 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 35px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='{{ $isAtivaAtual ? '0 10px 30px rgba(59, 130, 246, 0.15)' : '0 4px 16px rgba(0,0,0,0.05)' }}';">
                    
                    @if($isAtivaAtual)
                        <div style="position: absolute; top: -11px; right: 20px; background: #3b82f6; color: white; font-size: 0.72rem; font-weight: 700; padding: 3px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.4);">
                            <i class="fa-solid fa-check"></i> Empresa Ativa
                        </div>
                    @endif

                    <div>
                        {{-- Topo do Card --}}
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $isAtivaAtual ? '#eff6ff' : '#f1f5f9' }}; color: {{ $isAtivaAtual ? '#2563eb' : '#475569' }}; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b; margin: 0 0 3px 0; line-height: 1.2;">
                                    {{ $empresa->nome }}
                                </h3>
                                <div style="font-size: 0.8rem; color: #64748b;">
                                    CNPJ: {{ $empresa->cnpj ?? 'Não informado' }}
                                </div>
                            </div>
                        </div>

                        {{-- Detalhes da Empresa --}}
                        <div style="background: #f8fafc; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; border: 1px solid #f1f5f9;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 0.85rem; color: #64748b;">Colaboradores Ativos:</span>
                                <span style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                                    {{ $empresa->total_colaboradores }}
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.85rem; color: #64748b;">Status da Empresa:</span>
                                @php
                                    $statusEmpresaValor = $empresa->status instanceof \BackedEnum ? $empresa->status->value : ($empresa->status ?? 'ativa');
                                @endphp
                                <span style="font-weight: 600; font-size: 0.8rem; color: {{ $statusEmpresaValor === 'ativa' ? '#16a34a' : '#dc2626' }};">
                                    <i class="fa-solid fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> 
                                    {{ ucfirst($statusEmpresaValor) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Formulário de Acesso --}}
                    <form action="{{ route('consultor.selecionar-empresa') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                        
                        <button type="submit" style="width: 100%; padding: 12px 20px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; {{ $isAtivaAtual ? 'background: #3b82f6; color: white;' : 'background: #0f172a; color: white;' }}" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            @if($isAtivaAtual)
                                <i class="fa-solid fa-chart-line"></i> Acessar Painel Analítico
                            @else
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar nesta Empresa
                            @endif
                        </button>
                    </form>

                </div>
            @empty
                <div style="grid-column: 1 / -1; background: white; border-radius: 16px; padding: 50px 20px; text-align: center; border: 1px dashed #cbd5e1;">
                    <i class="fa-solid fa-building-circle-exclamation" style="font-size: 3rem; color: #94a3b8; margin-bottom: 15px; display: block;"></i>
                    <h3 style="color: #334155; margin-bottom: 8px;">Nenhuma Empresa Vinculada</h3>
                    <p style="color: #64748b; max-width: 450px; margin: 0 auto;">
                        O Administrador do sistema ainda não vinculou nenhuma empresa cliente ao seu perfil de Consultor. Entre em contato com o suporte para liberar seus acessos.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Botão de Logout ou Voltar se já tiver empresa ativa --}}
        <div style="display: flex; justify-content: center; gap: 16px;">
            @if($user->empresa_id)
                <a href="{{ route('dashboard') }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; color: #475569; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel da Empresa Atual
                </a>
            @endif
            
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; color: #e11d48; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
                    <i class="fa-solid fa-right-from-bracket"></i> Sair do Sistema
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
