@extends('layouts.app')
@section('title', 'Configuração WhatsApp')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
@push('styles')
<style>
.wpp-page { max-width: 900px; margin: 0 auto; padding-top: 20px; }

/* Header */
.wpp-header { display: flex; align-items: center; gap: 16px; margin-bottom: 30px; }
.wpp-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: linear-gradient(135deg, #25d366, #128c4e);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; color: white;
    box-shadow: 0 4px 14px rgba(37,211,102,.35);
    flex-shrink: 0;
}
.wpp-header h1 { font-size: 1.6rem; color: #1a1a2e; margin: 0; }
.wpp-header p  { color: #666; margin: 2px 0 0; font-size: .9rem; }

@media(max-width: 768px) {
    .wpp-header {
        flex-direction: column !important;
        text-align: center !important;
        gap: 12px !important;
        margin-bottom: 20px !important;
    }
    .wpp-icon {
        margin: 0 auto !important;
    }
}

/* Seletor de Empresa */
.wpp-company-selector {
    background: white; border-radius: 14px; padding: 18px 24px;
    margin-bottom: 24px; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,.03);
}
.wpp-company-selector select {
    padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1;
    font-size: .9rem; color: #334155; font-weight: 600; width: 300px;
    outline: none; cursor: pointer;
}
.wpp-company-selector label {
    font-weight: 700; color: #1e293b; font-size: .95rem; display: flex; align-items: center; gap: 8px;
}

/* Grid */
.wpp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media(max-width:700px){ .wpp-grid{ grid-template-columns:1fr; } }

.card {
    background: white; border-radius: 14px; padding: 28px;
    box-shadow: 0 4px 18px rgba(0,0,0,.06); border: 1px solid #f0f0f0;
}
.card-title {
    font-size: 1rem; font-weight: 700; color: #333;
    margin: 0 0 18px; display: flex; align-items: center; gap: 8px;
}

/* Status badge */
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; font-size: .8rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px;
}
.status-open    { background:#dcfce7; color:#15803d; }
.status-close   { background:#fee2e2; color:#dc2626; }
.status-disabled{ background:#f1f5f9; color:#64748b; }
.status-dot { width:8px; height:8px; border-radius:50%; background:currentColor; animation:pulse 1.5s infinite; }
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

/* Env rows */
.env-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; border-bottom: 1px solid #f5f5f5; font-size: .88rem;
}
.env-row:last-child { border-bottom: none; }
.env-label { color: #888; }
.env-value { font-family: monospace; color: #333; font-weight: 600; font-size: .82rem; }

/* Fila stats */
.fila-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 16px; }
.fila-stat {
    background: #f9fafb; border-radius: 10px; padding: 14px;
    text-align: center; border: 1px solid #e5e7eb;
}
.fila-stat .num { font-size: 1.8rem; font-weight: 800; line-height: 1; }
.fila-stat .lbl { font-size: .75rem; color: #888; margin-top: 4px; }
.num-pendente { color: #f59e0b; }
.num-enviada  { color: #10b981; }
.num-falhou   { color: #ef4444; }

/* QR Code */
.qr-wrapper {
    display: flex; flex-direction: column; align-items: center; gap: 14px;
    padding: 20px; background: #f9fafb; border-radius: 10px; border: 2px dashed #d1d5db;
}
.qr-wrapper img { width: 200px; height: 200px; border-radius: 8px; }
.qr-steps { font-size: .82rem; color: #555; text-align: left; line-height: 1.7; }

/* Buttons */
.btn-wpp {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 22px; border-radius: 8px; font-weight: 700;
    font-size: .9rem; border: none; cursor: pointer; transition: all .2s;
    text-decoration: none;
}
.btn-green  { background: #25d366; color: white; }
.btn-green:hover { background: #1ebe5d; transform: translateY(-1px); }
.btn-red    { background: #fee2e2; color: #dc2626; }
.btn-red:hover { background: #fecaca; }
.btn-slate  { background: #f1f5f9; color: #475569; }
.btn-slate:hover { background: #e2e8f0; }

/* Notif map */
.notif-list { list-style: none; padding: 0; margin: 0; }
.notif-list li {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid #f5f5f5; font-size: .88rem; color: #444;
}
.notif-list li:last-child { border-bottom: none; }
.notif-icon { font-size: 1.1rem; margin-top: 1px; flex-shrink: 0; }
.notif-text strong { display: block; color: #222; }
.notif-text span   { color: #888; font-size: .82rem; }

/* Alerts */
.alert {
    padding: 14px 18px; border-radius: 10px; margin-bottom: 22px;
    display: flex; align-items: center; gap: 10px; font-size: .9rem; font-weight: 500;
}
.alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.alert-warning { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.alert-info    { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }
</style>
@endpush

<div class="wpp-page">

    {{-- Feedback de sessão --}}
    @if(session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</div>
    @endif

    {{-- Seletor de Empresa para Administrador Global --}}
    @if(auth()->user()->perfil_acesso->value === 'admin' && $empresas->isNotEmpty())
    <div class="wpp-company-selector">
        <div>
            <label for="wpp-empresa-select"><i class="fa-solid fa-building"></i> Configurando para a empresa:</label>
        </div>
        <form action="{{ route('whatsapp.config') }}" method="GET" id="form-empresa-select">
            <select name="empresa_id" id="wpp-empresa-select" onchange="document.getElementById('form-empresa-select').submit()">
                @foreach($empresas as $emp)
                    <option value="{{ $emp->id }}" {{ $empresaId == $emp->id ? 'selected' : '' }}>
                        {{ $emp->nome }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    {{-- Alerta de fila com mensagens falhadas --}}
    @if($filaFalhadas > 0)
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <strong>{{ $filaFalhadas }} mensagem(ns) não puderam ser enviadas após 3 tentativas.</strong>
            O WhatsApp da empresa pode ter ficado offline por um período. Reconecte e use o botão "Reprocessar Falhadas" abaixo.
        </div>
    </div>
    @endif

    {{-- Alerta WhatsApp desconectado com mensagens pendentes --}}
    @if(!$isConectado && $enabled && $filaPendentes > 0)
    <div class="alert alert-error">
        <i class="fa-solid fa-wifi" style="transform:rotate(45deg);"></i>
        <div>
            <strong>WhatsApp desconectado!</strong>
            Há <strong>{{ $filaPendentes }}</strong> mensagem(ns) aguardando reenvio.
            Escaneie o QR Code abaixo para reconectar — o sistema reenviará automaticamente em até 10 minutos.
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="wpp-header">
        <div class="wpp-icon"><i class="fa-brands fa-whatsapp"></i></div>
        <div>
            <h1>Integração WhatsApp</h1>
            <p>Notificações automáticas gratuitas via Evolution API — fila inteligente com reenvio automático</p>
        </div>
    </div>

    @if(!$enabled)
    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>WhatsApp desabilitado.</strong>
            Adicione <code>WHATSAPP_ENABLED=true</code> e configure as demais variáveis no <code>.env</code> do servidor.
        </div>
    </div>
    @endif

    <div class="wpp-grid">

        {{-- Card Status --}}
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-signal"></i> Status da Conexão</div>

            @if(!$enabled)
                <span class="status-badge status-disabled"><span class="status-dot"></span> Desabilitado</span>
            @elseif($isConectado)
                <span class="status-badge status-open"><span class="status-dot"></span> Conectado ✓</span>
            @else
                <span class="status-badge status-close"><span class="status-dot"></span> Desconectado</span>
            @endif

            <div style="margin-top:20px;">
                <div class="env-row">
                    <span class="env-label">Empresa</span>
                    <span class="env-value" style="font-family: inherit; font-size: inherit;">{{ $empresaSelecionada->nome ?? 'N/A' }}</span>
                </div>
                @if(auth()->user()->perfil_acesso->value === 'admin')
                <div class="env-row">
                    <span class="env-label">Instância WhatsApp</span>
                    <span class="env-value">{{ config('whatsapp.instance') }}_{{ $empresaId }}</span>
                </div>
                <div class="env-row">
                    <span class="env-label">Servidor</span>
                    <span class="env-value">{{ config('whatsapp.base_url') }}</span>
                </div>
                @endif
                <div class="env-row">
                    <span class="env-label">Reenvio automático</span>
                    <span class="env-value">A cada 10 minutos</span>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <form action="{{ route('whatsapp.teste') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                    <button type="submit" class="btn-wpp btn-green" {{ (!$enabled || !$isConectado) ? 'disabled style=opacity:.5' : '' }}>
                        <i class="fa-brands fa-whatsapp"></i> Enviar Teste
                    </button>
                </form>
                
                @if($filaFalhadas > 0)
                <form action="{{ route('whatsapp.reprocessar') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="empresa_id" value="{{ $empresaId }}">
                    <button type="submit" class="btn-wpp btn-red">
                        <i class="fa-solid fa-rotate-right"></i> Reprocessar Falhadas ({{ $filaFalhadas }})
                    </button>
                </form>
                @endif
                
                <a href="{{ route('whatsapp.config', ['empresa_id' => $empresaId]) }}" class="btn-wpp btn-slate">
                    <i class="fa-solid fa-rotate"></i> Atualizar
                </a>
            </div>
        </div>

        {{-- Card QR Code --}}
        <div class="card">
            <div class="card-title"><i class="fa-solid fa-qrcode"></i> Conectar WhatsApp</div>

            @if(!$enabled)
                <p style="color:#888; font-size:.88rem;">Ative no <code>.env</code> para ver o QR Code.</p>
            @elseif($isConectado)
                <div style="text-align:center; padding:30px 0; color:#15803d;">
                    <i class="fa-solid fa-circle-check" style="font-size:3rem; margin-bottom:10px;"></i>
                    <p style="font-weight:700; margin:0;">WhatsApp Conectado!</p>
                    <p style="color:#888; font-size:.82rem; margin-top:6px;">Mensagens da empresa <strong>{{ $empresaSelecionada->nome ?? '' }}</strong> estão ativas.</p>
                </div>
            @elseif($qrCode)
                <div class="qr-wrapper">
                    <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code WhatsApp">
                    <ol class="qr-steps">
                        <li>Abra WhatsApp no celular da empresa</li>
                        <li>Vá em <strong>⋮ → Aparelhos conectados</strong></li>
                        <li>Toque <strong>Conectar um aparelho</strong></li>
                        <li>Aponte para o QR Code acima</li>
                    </ol>
                </div>
            @else
                <div class="qr-wrapper">
                    <i class="fa-solid fa-server" style="font-size:2.5rem; color:#ccc;"></i>
                    <p style="color:#888; font-size:.85rem; margin:0; text-align:center;">
                        Evolution API indisponível ou gerando QR Code. Verifique se o servidor está rodando em <code>{{ config('whatsapp.base_url') }}</code>
                    </p>
                </div>
            @endif
        </div>

    </div>

    {{-- Card Fila de Mensagens --}}
    <div class="card" style="margin-top:24px;">
        <div class="card-title"><i class="fa-solid fa-layer-group"></i> Fila de Mensagens (Empresa: {{ $empresaSelecionada->nome ?? 'N/A' }})</div>
        <p style="color:#666; font-size:.88rem; margin:0 0 16px;">
            Mensagens que falharam são reenviadas automaticamente a cada 10 minutos. Após 3 tentativas sem sucesso, ficam como "Falhadas" e o admin é notificado pelo sistema.
        </p>

        <div class="fila-grid">
            <div class="fila-stat">
                <div class="num num-pendente">{{ $filaPendentes }}</div>
                <div class="lbl">⏳ Pendentes / Aguardando reenvio</div>
            </div>
            <div class="fila-stat">
                <div class="num num-enviada">{{ $filaEnviadas }}</div>
                <div class="lbl">✅ Enviadas (últimas 24h)</div>
            </div>
            <div class="fila-stat">
                <div class="num num-falhou">{{ $filaFalhadas }}</div>
                <div class="lbl">❌ Falhadas (esgotaram tentativas)</div>
            </div>
        </div>

        @if($ultimasFalhadas->isNotEmpty())
        <details style="margin-top:10px;">
            <summary style="cursor:pointer; font-size:.88rem; color:#dc2626; font-weight:600; padding:8px 0;">
                Ver detalhes das {{ $filaFalhadas }} mensagem(ns) falhada(s)
            </summary>
            <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:.82rem;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:8px; text-align:left; border-bottom:1px solid #e5e7eb;">Número</th>
                        <th style="padding:8px; text-align:left; border-bottom:1px solid #e5e7eb;">Tentativas</th>
                        <th style="padding:8px; text-align:left; border-bottom:1px solid #e5e7eb;">Último Erro</th>
                        <th style="padding:8px; text-align:left; border-bottom:1px solid #e5e7eb;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimasFalhadas as $item)
                    <tr>
                        <td style="padding:8px; border-bottom:1px solid #f5f5f5; font-family:monospace;">{{ $item->numero }}</td>
                        <td style="padding:8px; border-bottom:1px solid #f5f5f5; text-align:center;">{{ $item->tentativas }}/{{ $item->max_tentativas }}</td>
                        <td style="padding:8px; border-bottom:1px solid #f5f5f5; color:#888; font-size:.78rem; max-width:300px; overflow:hidden; text-overflow:ellipsis;">{{ Str::limit($item->ultimo_erro, 80) }}</td>
                        <td style="padding:8px; border-bottom:1px solid #f5f5f5; white-space:nowrap;">{{ $item->created_at->format('d/m H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </details>
        @endif
    </div>

    {{-- Card Notificações mapeadas --}}
    <div class="card" style="margin-top:24px;">
        <div class="card-title"><i class="fa-solid fa-bell"></i> Notificações Automáticas Configuradas</div>
        <ul class="notif-list">
            <li><span class="notif-icon">📋</span><div class="notif-text"><strong>Nova Avaliação / Auto-Avaliação</strong><span>Quando uma avaliação é atribuída na plataforma.</span></div></li>
            <li><span class="notif-icon">✅</span><div class="notif-text"><strong>Avaliação Concluída</strong><span>Quando o colaborador responde sua avaliação.</span></div></li>
            <li><span class="notif-icon">⚠️</span><div class="notif-text"><strong>Alerta do Sistema</strong><span>Avisos críticos de sistema desconectado.</span></div></li>
        </ul>
    </div>


</div>
@endsection
