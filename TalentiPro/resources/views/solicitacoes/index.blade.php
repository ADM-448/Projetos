@extends('layouts.app')

@section('title', 'Solicitações de Avaliação')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')

{{-- ── Estilos Responsivos (Tabela -> Cards no Mobile) ────────── --}}
<style>
    .table-solicitacoes {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    
    @media (max-width: 768px) {
        .table-solicitacoes, .table-solicitacoes tbody, .table-solicitacoes tr, .table-solicitacoes td {
            display: block;
            width: 100%;
        }
        .table-solicitacoes thead {
            display: none;
        }
        .table-solicitacoes tr {
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            border-bottom: none !important;
        }
        .table-solicitacoes td {
            border: none !important;
            padding: 10px 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right !important;
        }
        .table-solicitacoes td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            text-align: left;
        }
        .table-solicitacoes td:first-child {
            border-bottom: 1px solid #f1f5f9 !important;
            padding-bottom: 12px !important;
            margin-bottom: 8px;
            justify-content: flex-start;
            flex-direction: column;
            align-items: flex-start;
            text-align: left !important;
        }
        .table-solicitacoes td:first-child::before {
            display: none;
        }
        .table-solicitacoes td:last-child {
            border-top: 1px dashed #e2e8f0 !important;
            margin-top: 8px;
            padding-top: 12px !important;
            justify-content: center;
        }
        .table-solicitacoes td:last-child::before {
            display: none;
        }
    }
</style>

{{-- ── Cabeçalho ────────────────────────────────────────────────── --}}
<div style="margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
        Solicitações de Avaliação
    </h1>
    <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
        Gerencie as solicitações da equipe — aprove ou recuse com justificativa.
    </p>
</div>

{{-- ── Cards de Status ─────────────────────────────────────────── --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px;">
    @php
        $pendentes = $solicitacoes->where('status','pendente')->count();
        $aprovadas = $solicitacoes->where('status','aprovada')->count();
        $recusadas = $solicitacoes->where('status','recusada')->count();
    @endphp
    
    <!-- Card Pendentes -->
    <div style="background: white; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef9c3; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Pendentes</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; margin-top: 4px;">{{ $pendentes }}</div>
        </div>
    </div>

    <!-- Card Aprovadas -->
    <div style="background: white; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-check"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Aprovadas</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; margin-top: 4px;">{{ $aprovadas }}</div>
        </div>
    </div>

    <!-- Card Recusadas -->
    <div style="background: white; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Recusadas</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; margin-top: 4px;">{{ $recusadas }}</div>
        </div>
    </div>
</div>

{{-- ── Tabela de Solicitações ───────────────────────────────────── --}}
<div class="unified-card" style="padding: 0; overflow: hidden;">
    @if($solicitacoes->isEmpty())
        <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
            <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 16px; display: block; color: #e2e8f0;"></i>
            <p style="font-size: 1.1rem; font-weight: 600; color: #475569;">Nenhuma solicitação encontrada.</p>
            <p style="font-size: 0.9rem; margin-top: 4px;">
                Quando colaboradores enviarem solicitações, elas aparecerão aqui.
            </p>
        </div>
    @else
        <div style="overflow-x: hidden; padding: 20px; background: transparent; border: none; box-shadow: none;" class="table-container-mobile">
            <table class="table-solicitacoes">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 16px; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Colaborador</th>
                        <th style="padding: 16px; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Avaliação</th>
                        <th style="padding: 16px; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Tipo</th>
                        <th style="padding: 16px; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Status</th>
                        <th style="padding: 16px; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Data</th>
                        <th style="padding: 16px; font-weight: 600; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitacoes as $s)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td data-label="Colaborador" style="padding: 16px;">
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ $s->user->name }}</div>
                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">{{ $s->user->departamento->nome ?? 'Sem dept.' }}</div>
                        </td>
                        <td data-label="Avaliação" style="padding: 16px; color: #334155; font-weight: 500; font-size: 0.95rem;">
                            @if($s->tipo === 'reabertura_disc')
                                <span style="display:inline-flex;align-items:center;gap:6px;color:#6a4c7c;font-weight:700;">
                                    <i class="fa-solid fa-puzzle-piece"></i> Teste DISC
                                </span>
                            @else
                                {{ optional($s->avaliacao)->titulo ?? '—' }}
                            @endif
                        </td>
                        <td data-label="Tipo" style="padding: 16px; text-align: center;">
                            @php
                                $tipoLabel = match($s->tipo) {
                                    'reabertura'      => ['label' => 'Reabertura',    'bg' => '#e0f2fe', 'color' => '#0284c7'],
                                    'contestacao'     => ['label' => 'Contestação',   'bg' => '#fef3c7', 'color' => '#d97706'],
                                    'reabertura_disc' => ['label' => 'Refazer DISC',  'bg' => '#f3e8ff', 'color' => '#7c3aed'],
                                    default           => ['label' => ucfirst($s->tipo), 'bg' => '#f1f5f9', 'color' => '#475569'],
                                };
                            @endphp
                            <span style="display: inline-flex; align-items: center; justify-content: center; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: {{ $tipoLabel['bg'] }}; color: {{ $tipoLabel['color'] }};">
                                {{ $tipoLabel['label'] }}
                            </span>
                        </td>
                        <td data-label="Status" style="padding: 16px; text-align: center;">
                            @if($s->status === 'pendente')
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: #fef9c3; color: #a16207;">
                                    <i class="fa-solid fa-hourglass-half"></i> Pendente
                                </span>
                            @elseif($s->status === 'aprovada')
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: #dcfce7; color: #166534;">
                                    <i class="fa-solid fa-check"></i> Aprovada
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: #fee2e2; color: #991b1b;">
                                    <i class="fa-solid fa-ban"></i> Recusada
                                </span>
                                @if($s->resposta_rh)
                                    <div style="font-size: 0.7rem; color: #ef4444; margin-top: 6px;" title="{{ $s->resposta_rh }}">
                                        <i class="fa-solid fa-circle-info"></i> {{ Str::limit($s->resposta_rh, 30) }}
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td data-label="Data" style="padding: 16px; text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 500;">
                            {{ $s->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td data-label="Ações" style="padding: 16px; text-align: center;">
                            <div style="display: inline-flex; gap: 8px; justify-content: center; align-items: center;">
                                {{-- Ver justificativa --}}
                                <button onclick="abrirDetalhes('{{ addslashes($s->user->name) }}', '{{ $s->tipo === 'reabertura_disc' ? 'Teste DISC' : addslashes(optional($s->avaliacao)->titulo ?? '—') }}', '{{ addslashes($s->justificativa) }}', '{{ $s->resposta_rh ? addslashes($s->resposta_rh) : '' }}')"
                                        style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; color: #475569; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                        onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'"
                                        onmouseout="this.style.background='#f1f5f9'; this.style.color='#475569'"
                                        title="Ver detalhes">
                                    <i class="fa-solid fa-eye" style="font-size: 0.85rem;"></i>
                                </button>

                                @if($s->status === 'pendente')
                                    @can('aprovar', $s)
                                        <form action="{{ route('solicitacoes.aprovar', $s->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" 
                                                    style="background: #dcfce7; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; color: #166534; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                                    onmouseover="this.style.background='#bbf7d0'; this.style.color='#14532d'"
                                                    onmouseout="this.style.background='#dcfce7'; this.style.color='#166534'"
                                                    title="Aprovar e reabrir">
                                                <i class="fa-solid fa-check" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </form>
                                        
                                        <button onclick="abrirModalRecusa({{ $s->id }}, '{{ addslashes($s->user->name) }}')"
                                                style="background: #fee2e2; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; color: #991b1b; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"
                                                onmouseover="this.style.background='#fecaca'; this.style.color='#7f1d1d'"
                                                onmouseout="this.style.background='#fee2e2'; this.style.color='#991b1b'"
                                                title="Recusar">
                                            <i class="fa-solid fa-ban" style="font-size: 0.85rem;"></i>
                                        </button>
                                    @else
                                        <span style="font-size: 0.75rem; color: #94a3b8; background: #f8fafc; padding: 4px 8px; border-radius: 6px;">
                                            <i class="fa-solid fa-eye"></i> Somente Leitura
                                        </span>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ──────────────────────────────────────────────────────────────
     MODAL: Recusa com Motivo (master/gestor)
──────────────────────────────────────────────────────────────── --}}
<div id="modal-recusa" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
    <div class="card" style="width:100%; max-width:500px; margin:20px; animation:slideUp 0.3s ease; background:white; border-radius:12px; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.2rem; font-weight:700; color:#991b1b; margin:0;">
                <i class="fa-solid fa-ban"></i> Recusar Solicitação
            </h2>
            <button onclick="document.getElementById('modal-recusa').style.display = 'none'"
                    style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#64748b;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <p id="recusa-nome-colaborador" style="color:#64748b; margin-bottom:16px; font-size:0.9rem;"></p>

        <form id="form-recusa" method="POST">
            @csrf
            <div style="margin-bottom:20px;">
                <label class="form-label" style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Motivo da Recusa <span style="color:#ef4444;">*</span></label>
                <textarea name="resposta_rh" class="form-control" rows="4" required minlength="10"
                    style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px;"
                    placeholder="Informe o motivo da recusa. Este texto será enviado ao colaborador (mínimo 10 caracteres)..."></textarea>
                <p style="font-size:0.75rem; color:#94a3b8; margin-top:6px;">
                    <i class="fa-solid fa-circle-info"></i> O colaborador verá este motivo na sua lista de solicitações.
                </p>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-recusa').style.display = 'none'"
                        class="btn" style="background:#f1f5f9; color:#475569; padding:8px 16px; border-radius:8px; border:none; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn" style="background:#ef4444; color:white; padding:8px 16px; border-radius:8px; border:none; cursor:pointer;">
                    <i class="fa-solid fa-ban"></i> Confirmar Recusa
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ──────────────────────────────────────────────────────────────
     MODAL: Ver Detalhes da Justificativa
──────────────────────────────────────────────────────────────── --}}
<div id="modal-detalhes" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
    <div class="card" style="width:100%; max-width:500px; margin:20px; animation:slideUp 0.3s ease; background:white; border-radius:12px; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-size:1.1rem; font-weight:700; color:#1e293b; margin:0;">
                <i class="fa-solid fa-file-lines" style="color:#8b5cf6;"></i> Detalhes da Solicitação
            </h2>
            <button onclick="document.getElementById('modal-detalhes').style.display = 'none'"
                    style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#64748b;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div>
            <p style="font-size:0.8rem; color:#94a3b8; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Colaborador</p>
            <p id="det-colaborador" style="color:#1e293b; font-weight:600; margin-bottom:12px;"></p>

            <p style="font-size:0.8rem; color:#94a3b8; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Avaliação</p>
            <p id="det-avaliacao" style="color:#1e293b; margin-bottom:12px;"></p>

            <p style="font-size:0.8rem; color:#94a3b8; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Justificativa do Colaborador</p>
            <div id="det-justificativa" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; color:#1e293b; font-size:0.9rem; margin-bottom:12px;"></div>

            <div id="det-resposta-container" style="display:none; margin-top:16px;">
                <p style="font-size:0.8rem; color:#ef4444; text-transform:uppercase; font-weight:700; margin-bottom:4px;">Motivo da Recusa</p>
                <div id="det-resposta" style="background:#fef2f2; border:1px solid #fee2e2; border-radius:8px; padding:12px; color:#991b1b; font-size:0.9rem;"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function abrirModalRecusa(id, nomeColaborador) {
        document.getElementById('form-recusa').action = '/solicitacoes/' + id + '/recusar';
        document.getElementById('recusa-nome-colaborador').textContent = 'Colaborador: ' + nomeColaborador;
        document.getElementById('modal-recusa').style.display = 'flex';
    }

    function abrirDetalhes(colaborador, avaliacao, justificativa, respostaRh) {
        document.getElementById('det-colaborador').textContent = colaborador;
        document.getElementById('det-avaliacao').textContent = avaliacao;
        document.getElementById('det-justificativa').textContent = justificativa;

        const respostaContainer = document.getElementById('det-resposta-container');
        if (respostaRh) {
            document.getElementById('det-resposta').textContent = respostaRh;
            respostaContainer.style.display = 'block';
        } else {
            respostaContainer.style.display = 'none';
        }
        document.getElementById('modal-detalhes').style.display = 'flex';
    }
</script>
@endpush
