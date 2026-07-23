@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('title', 'Solicitações RH')
@section('content')

@push('styles')
<style>
    .requests-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .req-table {
        width: 100%;
        border-collapse: collapse;
    }

    .req-table th {
        text-align: left;
        padding: 15px;
        border-bottom: 2px solid #eee;
        background-color: #f9f9f9;
        color: #555;
    }

    .req-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }

    .justification-box {
        background: #f9f9f9;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #eee;
        font-size: 13px;
        color: #555;
        margin-top: 5px;
        max-width: 400px;
        font-style: italic;
    }

    .btn-approve {
        background: #27ae60;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        font-size: 12px;
    }

    .btn-deny {
        background: #c62828;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        font-size: 12px;
        margin-left: 5px;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        color: white;
        text-transform: uppercase;
    }
    .status-pendente { background-color: #f39c12; }
    .status-aprovada { background-color: #27ae60; }
    .status-recusada { background-color: #c0392b; }

    .info-text {
        color: #666;
        font-size: 13px;
        font-style: italic;
    }
</style>
@endpush

<div class="page-content">
    <h1 class="page-title">Solicitações de Reavaliação</h1>

    <div class="requests-card">
        @if($solicitacoes->isEmpty())
            <p>Nenhuma solicitação encontrada.</p>
        @else
        <table class="req-table">
            <thead>
                <tr>
                    <th>Colaborador / Gestor</th>
                    <th>Avaliação Ref.</th>
                    <th>Justificativa</th>
                    <th>Status</th>
                    <th width="20%">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitacoes as $req)
                @php
                    $user = auth()->user();
                    // Check if user can approve: Master/Admin OR evaluation creator
                    $canApprove = in_array($user->perfil_acesso, ['master', 'admin']) || 
                                  ($req->avaliacao->created_by === $user->id);
                @endphp
                <tr>
                    <td data-label="Colaborador / Gestor">
                        <strong>{{ $req->user->name }}</strong><br>
                        <span style="font-size:12px; color:#888;">Gestor: {{ $req->user->gestor->name ?? 'N/A' }}</span>
                    </td>
                    <td data-label="Avaliação Ref.">{{ $req->avaliacao->titulo }}</td>
                    <td data-label="Justificativa">
                        <div class="justification-box">
                            "{{ $req->justificativa }}"
                        </div>
                    </td>
                    <td data-label="Status">
                        <span class="status-badge status-{{ $req->status }}">{{ $req->status }}</span>
                    </td>
                    <td data-label="Ações">
                        @if($req->status === 'pendente')
                            @if($canApprove)
                                <button type="button" class="btn-approve" onclick="showApprovalModal({{ $req->id }}, '{{ $req->user->name }}', '{{ $req->avaliacao->titulo }}', 'aprovar')">Aprovar</button>
                                <button type="button" class="btn-deny" onclick="showApprovalModal({{ $req->id }}, '{{ $req->user->name }}', '{{ $req->avaliacao->titulo }}', 'negar')">Negar</button>
                            @else
                                <span class="info-text">{{ $req->user->name }} pediu nº {{ $req->id }} para "{{ $req->avaliacao->titulo }}"</span>
                            @endif
                        @else
                           -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Custom Approval Modal --}}
<div id="approvalModal" class="custom-modal-overlay">
    <div class="custom-modal-content">
        <div class="custom-modal-icon">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3 id="approvalModalTitle">Confirmação</h3>
        <p id="approvalModalMessage"></p>
        
        <div class="custom-modal-actions">
            <button class="btn-cancel" onclick="closeApprovalModal()">Cancelar</button>
            <button class="btn-confirm" id="btnConfirmApproval">Confirmar</button>
        </div>
    </div>
</div>

<style>
    .custom-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s;
    }
    
    .custom-modal-content {
        background: white;
        padding: 25px;
        border-radius: 12px;
        width: 450px;
        max-width: 90%;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        transform: scale(0.9);
        animation: scaleUp 0.2s forwards;
    }
    
    @keyframes scaleUp {
        to { transform: scale(1); }
    }
    
    .custom-modal-icon {
        font-size: 40px;
        color: #3b5998;
        margin-bottom: 15px;
    }
    
    .custom-modal-content h3 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 20px;
    }
    
    .custom-modal-content p {
        color: #666;
        margin-bottom: 25px;
        font-size: 15px;
        line-height: 1.5;
    }
    
    .custom-modal-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    
    .btn-cancel, .btn-confirm {
        padding: 10px 24px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
        font-size: 14px;
    }
    
    .btn-cancel {
        background-color: #f1f2f6;
        color: #57606f;
    }
    
    .btn-cancel:hover {
        background-color: #e2e6ea;
    }
    
    .btn-confirm {
        background-color: #3b5998;
        color: white;
    }
    
    .btn-confirm:hover {
        background-color: #2d4373;
        box-shadow: 0 2px 8px rgba(59, 89, 152, 0.3);
    }
</style>

<script>
    let currentAction = null;
    let currentSolicitacaoId = null;

    function showApprovalModal(id, userName, avaliacaoTitle, action) {
        currentSolicitacaoId = id;
        currentAction = action;

        const modal = document.getElementById('approvalModal');
        const title = document.getElementById('approvalModalTitle');
        const message = document.getElementById('approvalModalMessage');
        const btnConfirm = document.getElementById('btnConfirmApproval');

        if (action === 'aprovar') {
            title.innerText = 'Aprovar Solicitação';
            message.innerHTML = `Deseja aprovar a solicitação de <strong>${userName}</strong> para reavaliar "<strong>${avaliacaoTitle}</strong>"?`;
            btnConfirm.style.backgroundColor = '#27ae60';
        } else {
            title.innerText = 'Negar Solicitação';
            message.innerHTML = `Deseja negar a solicitação de <strong>${userName}</strong> para reavaliar "<strong>${avaliacaoTitle}</strong>"?`;
            btnConfirm.style.backgroundColor = '#c62828';
        }

        modal.style.display = 'flex';
    }

    function closeApprovalModal() {
        document.getElementById('approvalModal').style.display = 'none';
        currentAction = null;
        currentSolicitacaoId = null;
    }

    document.getElementById('btnConfirmApproval').addEventListener('click', function() {
        if (currentAction && currentSolicitacaoId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = currentAction === 'aprovar' 
                ? `/solicitacoes/${currentSolicitacaoId}/aprovar`
                : `/solicitacoes/${currentSolicitacaoId}/recusar`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            document.body.appendChild(form);
            form.submit();
        }
    });

    // Close on Esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeApprovalModal();
    });

    // Close on click outside
    document.getElementById('approvalModal').addEventListener('click', function(e) {
        if (e.target === this) closeApprovalModal();
    });
</script>
@endsection
