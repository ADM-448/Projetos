@extends('layouts.app')

@section('title', 'Teste DISC da Equipe')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.disc-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.disc-stat-card {
    background: white;
    padding: 22px 25px;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 18px;
    transition: all 0.25s ease;
}
.disc-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}

.disc-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.disc-filter-card {
    background: white;
    border-radius: 16px;
    padding: 20px 25px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    margin-bottom: 25px;
}

.disc-table-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    overflow: hidden;
}

.badge-profile {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.3px;
}
.badge-dominante { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
.badge-influente { background: #fffff0; color: #b7791f; border: 1px solid #fefcbf; }
.badge-estavel   { background: #f0fff4; color: #276749; border: 1px solid #c6f6d5; }
.badge-cauteloso { background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; }

.disc-score-group {
    display: inline-flex;
    gap: 4px;
    align-items: center;
    background: #f8fafc;
    padding: 4px;
    border-radius: 8px;
    border: 1px solid #edf2f7;
}

.disc-score-chip {
    font-size: 0.76rem;
    font-weight: 800;
    padding: 4px 8px;
    border-radius: 6px;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.btn-view-report {
    background: #6a4c7c;
    color: white !important;
    padding: 9px 18px;
    font-size: 0.83rem;
    border-radius: 10px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 12px rgba(106, 76, 124, 0.3);
    transition: all 0.2s ease;
}
.btn-view-report:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(106, 76, 124, 0.45);
    background: #5b3f6c;
}

.user-avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6a4c7c 0%, #8b5cf6 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.88rem;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(106, 76, 124, 0.25);
}

.custom-input-group {
    position: relative;
    width: 100%;
}
.custom-input-group i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    font-size: 0.9rem;
}
.custom-input-group input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    border: 1px solid #cbd5e0;
    border-radius: 10px;
    font-size: 0.9rem;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.custom-input-group input:focus {
    border-color: #6a4c7c;
    box-shadow: 0 0 0 3px rgba(106, 76, 124, 0.15);
}

.custom-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e0;
    border-radius: 10px;
    font-size: 0.9rem;
    outline: none;
    background: white;
    color: #2d3748;
    font-weight: 500;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.custom-select:focus {
    border-color: #6a4c7c;
    box-shadow: 0 0 0 3px rgba(106, 76, 124, 0.15);
}

/* CONTROLE DE EXIBIÇÃO DESKTOP x MOBILE */
.disc-mobile-cards {
    display: none;
}

@media (max-width: 768px) {
    .disc-desktop-table {
        display: none !important;
    }
    .disc-mobile-cards {
        display: flex !important;
        flex-direction: column;
        gap: 15px;
    }
    .disc-filter-form {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .disc-filter-form > * {
        width: 100% !important;
    }
    .mobile-emp-card {
        background: white;
        border-radius: 16px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
}
</style>

<div class="header-actions" style="margin-bottom: 25px;">
    <div>
        <h1 class="page-title" style="margin-bottom: 8px;">
            <i class="fa-solid fa-users-viewfinder" style="color: #6a4c7c;"></i> Teste DISC da Equipe
        </h1>
        <p style="color: #64748b; font-size: 0.95rem;">
            Mapeamento comportamental completo da sua equipe. Acompanhe perfis, estatísticas e relatórios individuais.
        </p>
    </div>
</div>

{{-- CARDS DE ESTATÍSTICAS --}}
<div class="disc-stats-grid">
    <div class="disc-stat-card">
        <div class="disc-stat-icon" style="background: rgba(106, 76, 124, 0.1); color: #6a4c7c;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Total da Equipe</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2d3748; margin-top: 2px;">{{ $totalColaboradores }}</div>
        </div>
    </div>

    <div class="disc-stat-card">
        <div class="disc-stat-icon" style="background: #f0fff4; color: #38a169;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Testes Concluídos</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2d3748; margin-top: 2px;">
                {{ $totalConcluidos }} 
                <span style="font-size: 0.85rem; font-weight: 700; color: #38a169;">
                    ({{ $totalColaboradores > 0 ? round(($totalConcluidos / $totalColaboradores) * 100) : 0 }}%)
                </span>
            </div>
        </div>
    </div>

    <div class="disc-stat-card">
        <div class="disc-stat-icon" style="background: #fffbeb; color: #d97706;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Pendentes</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2d3748; margin-top: 2px;">{{ $totalPendentes }}</div>
        </div>
    </div>

    @php
        $maiorPerfilKey = !empty($statsPerfis) ? array_search(max($statsPerfis), $statsPerfis) : null;
        $mapPerfisNomes = [
            'DOMINANCE'  => ['nome' => 'Dominante', 'cor' => '#e53e3e', 'icon' => '🔴'],
            'INFLUENCE'  => ['nome' => 'Influente', 'cor' => '#d69e2e', 'icon' => '🟡'],
            'STEADINESS' => ['nome' => 'Estável',   'cor' => '#38a169', 'icon' => '🟢'],
            'COMPLIANCE' => ['nome' => 'Cauteloso', 'cor' => '#3182ce', 'icon' => '🔵'],
        ];
        $predominante = ($maiorPerfilKey && $statsPerfis[$maiorPerfilKey] > 0) 
            ? $mapPerfisNomes[$maiorPerfilKey] 
            : ['nome' => 'Nenhum', 'cor' => '#718096', 'icon' => '⚪'];
    @endphp

    <div class="disc-stat-card">
        <div class="disc-stat-icon" style="background: rgba(49, 130, 206, 0.1); color: {{ $predominante['cor'] }};">
            <i class="fa-solid fa-chart-pie"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Predominante</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: {{ $predominante['cor'] }}; margin-top: 2px;">
                {{ $predominante['nome'] }}
            </div>
        </div>
    </div>
</div>

{{-- BARRA DE DISTRIBUIÇÃO DOS PERFIS NA EQUIPE --}}
<div style="background: white; border-radius: 16px; padding: 22px 25px; border: 1px solid #f0f2f5; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 25px;">
    <h4 style="font-size: 0.95rem; font-weight: 700; color: #2d3748; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-chart-bar" style="color: #6a4c7c;"></i> Distribuição Geral dos Perfis na Equipe
    </h4>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        @foreach([
            'DOMINANCE'  => ['label' => 'Dominante (D)', 'cor' => '#e53e3e', 'bg' => '#fff5f5'],
            'INFLUENCE'  => ['label' => 'Influente (I)', 'cor' => '#d69e2e', 'bg' => '#fffff0'],
            'STEADINESS' => ['label' => 'Estável (S)',   'cor' => '#38a169', 'bg' => '#f0fff4'],
            'COMPLIANCE' => ['label' => 'Cauteloso (C)', 'cor' => '#3182ce', 'bg' => '#ebf8ff'],
        ] as $key => $pInfo)
            @php
                $qtd = $statsPerfis[$key] ?? 0;
                $pct = $totalConcluidos > 0 ? round(($qtd / $totalConcluidos) * 100) : 0;
            @endphp
            <div style="background: {{ $pInfo['bg'] }}; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.04);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 0.88rem; font-weight: 700; color: {{ $pInfo['cor'] }};">{{ $pInfo['label'] }}</span>
                    <span style="font-size: 0.88rem; font-weight: 800; color: #2d3748;">{{ $qtd }} <small style="color: #718096; font-size: 0.78rem;">({{ $pct }}%)</small></span>
                </div>
                <div style="width: 100%; height: 7px; background: rgba(0,0,0,0.06); border-radius: 999px; overflow: hidden;">
                    <div style="width: {{ $pct }}%; height: 100%; background: {{ $pInfo['cor'] }}; border-radius: 999px; transition: width 0.4s;"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- FILTROS --}}
<div class="disc-filter-card">
    <form action="{{ route('disc.equipe') }}" method="GET" class="disc-filter-form" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 200px;">
            <div class="custom-input-group">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Buscar por nome ou e-mail...">
            </div>
        </div>

        <div style="flex: 1; min-width: 160px;">
            <select name="departamento_id" class="custom-select">
                <option value="">-- Todos Departamentos --</option>
                @foreach($departamentos as $dep)
                    <option value="{{ $dep->id }}" {{ request('departamento_id') == $dep->id ? 'selected' : '' }}>
                        {{ $dep->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <select name="perfil" class="custom-select">
                <option value="">-- Todos os Perfis --</option>
                <option value="DOMINANCE" {{ request('perfil') == 'DOMINANCE' ? 'selected' : '' }}>Dominante</option>
                <option value="INFLUENCE" {{ request('perfil') == 'INFLUENCE' ? 'selected' : '' }}>Influente</option>
                <option value="STEADINESS" {{ request('perfil') == 'STEADINESS' ? 'selected' : '' }}>Estável</option>
                <option value="COMPLIANCE" {{ request('perfil') == 'COMPLIANCE' ? 'selected' : '' }}>Cauteloso</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 140px;">
            <select name="status" class="custom-select">
                <option value="">-- Status --</option>
                <option value="concluido" {{ request('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn-primary" style="padding: 10px 22px; border-radius: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(106, 76, 124, 0.3);">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            @if(request()->hasAny(['search', 'departamento_id', 'perfil', 'status']))
                <a href="{{ route('disc.equipe') }}" style="padding: 10px 16px; border-radius: 10px; background: #edf2f7; color: #4a5568; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-xmark"></i> Limpar
                </a>
            @endif
        </div>
    </form>
</div>

@php
    $clsMap = [
        'Dominante' => 'badge-dominante',
        'Influente' => 'badge-influente',
        'Estável'   => 'badge-estavel',
        'Cauteloso' => 'badge-cauteloso',
    ];
@endphp

{{-- VISUALIZAÇÃO DESKTOP: TABELA TRADICIONAL --}}
<div class="disc-table-card disc-desktop-table">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #edf2f7; color: #475569; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.6px;">
                    <th style="padding: 18px 24px;">Colaborador</th>
                    <th style="padding: 18px 16px;">Departamento / Cargo</th>
                    <th style="padding: 18px 16px; text-align: center;">Status</th>
                    <th style="padding: 18px 16px;">Perfil Primário</th>
                    <th style="padding: 18px 16px;">Perfil Secundário</th>
                    <th style="padding: 18px 16px; text-align: center;">Pontuações (D-I-S-C)</th>
                    <th style="padding: 18px 24px; text-align: center;">Ação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colaboradores as $u)
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 16px 24px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div class="user-avatar-circle">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ $u->name }}</div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 1px;">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>

                    <td style="padding: 16px;">
                        <div style="font-weight: 700; color: #334155; font-size: 0.9rem;">{{ $u->departamento->nome ?? 'Sem Dept.' }}</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 1px;">{{ $u->cargo->nome ?? 'Sem Cargo' }}</div>
                    </td>

                    <td style="padding: 16px; text-align: center;">
                        @if($u->discAssessment)
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 9999px; font-size: 0.76rem; font-weight: 700; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                                <i class="fa-solid fa-check"></i> Concluído
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 9999px; font-size: 0.76rem; font-weight: 700; background: #fffbeb; color: #92400e; border: 1px solid #fef3c7;">
                                <i class="fa-solid fa-clock"></i> Pendente
                            </span>
                        @endif
                    </td>

                    <td style="padding: 16px;">
                        @if($u->discAssessment)
                            @php
                                $pBr = $u->discAssessment->primary_profile_br;
                                $cCls = $clsMap[$pBr] ?? 'badge-cauteloso';
                            @endphp
                            <span class="badge-profile {{ $cCls }}">
                                {{ mb_strtoupper($pBr) }}
                            </span>
                        @else
                            <span style="color: #cbd5e0; font-size: 0.9rem; font-weight: bold;">—</span>
                        @endif
                    </td>

                    <td style="padding: 16px;">
                        @if($u->discAssessment && $u->discAssessment->secondary_profile_br)
                            @php
                                $p2Br = $u->discAssessment->secondary_profile_br;
                                $c2Cls = $clsMap[$p2Br] ?? 'badge-cauteloso';
                            @endphp
                            <span class="badge-profile {{ $c2Cls }}" style="opacity: 0.9;">
                                {{ mb_strtoupper($p2Br) }}
                            </span>
                        @else
                            <span style="color: #cbd5e0; font-size: 0.9rem; font-weight: bold;">—</span>
                        @endif
                    </td>

                    <td style="padding: 16px; text-align: center;">
                        @if($u->discAssessment)
                            <div class="disc-score-group">
                                <span class="disc-score-chip" style="background: #e53e3e;" title="Dominância (D)">D: {{ $u->discAssessment->score_d }}</span>
                                <span class="disc-score-chip" style="background: #d69e2e;" title="Influência (I)">I: {{ $u->discAssessment->score_i }}</span>
                                <span class="disc-score-chip" style="background: #38a169;" title="Estabilidade (S)">S: {{ $u->discAssessment->score_s }}</span>
                                <span class="disc-score-chip" style="background: #3182ce;" title="Conformidade (C)">C: {{ $u->discAssessment->score_c }}</span>
                            </div>
                        @else
                            <span style="color: #cbd5e0; font-size: 0.9rem; font-weight: bold;">—</span>
                        @endif
                    </td>

                    <td style="padding: 16px 24px; text-align: center;">
                        @if($u->discAssessment)
                            <a href="{{ route('disc.result', $u->discAssessment->id) }}" class="btn-view-report">
                                <i class="fa-solid fa-chart-radar"></i> Ver Relatório
                            </a>
                        @else
                            <span style="font-size: 0.8rem; color: #a0aec0; font-weight: 600; font-style: italic;">
                                Aguardando preenchimento
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 50px 20px; text-align: center; color: #a0aec0;">
                        <i class="fa-solid fa-folder-open" style="font-size: 2.2rem; margin-bottom: 12px; display: block; color: #cbd5e0;"></i>
                        <div style="font-weight: 700; color: #64748b; font-size: 1rem;">Nenhum colaborador encontrado</div>
                        <div style="font-size: 0.85rem; color: #a0aec0; margin-top: 4px;">Tente alterar os filtros selecionados acima.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- VISUALIZAÇÃO MOBILE: CARDS NATIVOS E COMPACTOS --}}
<div class="disc-mobile-cards">
    @forelse($colaboradores as $u)
        <div class="mobile-emp-card">
            {{-- Cabeçalho do Card --}}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr($u->name, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; line-height: 1.3;">{{ $u->name }}</div>
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                            {{ $u->departamento->nome ?? 'Sem Dept.' }} • {{ $u->cargo->nome ?? 'Sem Cargo' }}
                        </div>
                    </div>
                </div>

                <div style="flex-shrink: 0;">
                    @if($u->discAssessment)
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                            <i class="fa-solid fa-check"></i> Concluído
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; background: #fffbeb; color: #92400e; border: 1px solid #fef3c7;">
                            <i class="fa-solid fa-clock"></i> Pendente
                        </span>
                    @endif
                </div>
            </div>

            @if($u->discAssessment)
                {{-- Conteúdo do Teste Concluído --}}
                <div style="background: #f8fafc; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 10px; border: 1px solid #edf2f7;">
                    @php
                        $pBr = $u->discAssessment->primary_profile_br;
                        $cCls = $clsMap[$pBr] ?? 'badge-cauteloso';
                    @endphp

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Perfis:</span>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <span class="badge-profile {{ $cCls }}">
                                {{ mb_strtoupper($pBr) }}
                            </span>

                            @if($u->discAssessment->secondary_profile_br)
                                @php
                                    $p2Br = $u->discAssessment->secondary_profile_br;
                                    $c2Cls = $clsMap[$p2Br] ?? 'badge-cauteloso';
                                @endphp
                                <span class="badge-profile {{ $c2Cls }}" style="opacity: 0.9;">
                                    {{ mb_strtoupper($p2Br) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                        <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Notas:</span>
                        <div class="disc-score-group">
                            <span class="disc-score-chip" style="background: #e53e3e;">D: {{ $u->discAssessment->score_d }}</span>
                            <span class="disc-score-chip" style="background: #d69e2e;">I: {{ $u->discAssessment->score_i }}</span>
                            <span class="disc-score-chip" style="background: #38a169;">S: {{ $u->discAssessment->score_s }}</span>
                            <span class="disc-score-chip" style="background: #3182ce;">C: {{ $u->discAssessment->score_c }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('disc.result', $u->discAssessment->id) }}" class="btn-view-report" style="width: 100%; justify-content: center; box-sizing: border-box;">
                    <i class="fa-solid fa-chart-radar"></i> Ver Relatório Completo
                </a>
            @endif
        </div>
    @empty
        <div style="background: white; border-radius: 16px; padding: 40px 20px; text-align: center; color: #a0aec0; border: 1px solid #f0f2f5;">
            <i class="fa-solid fa-folder-open" style="font-size: 2.2rem; margin-bottom: 10px; display: block; color: #cbd5e0;"></i>
            <div style="font-weight: 700; color: #64748b; font-size: 1rem;">Nenhum colaborador encontrado</div>
        </div>
    @endforelse
</div>
@endsection
