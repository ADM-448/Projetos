@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))
@section('title', 'Avaliações')
@section('content')

@push('styles')
<style>

    
    /* Tabela */
    .list-container { background: white; border-radius: 10px; padding: 20px; border: 1px solid #e0e0e0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .eval-list { width: 100%; border-collapse: collapse; }
    .eval-list th { text-align: left; padding: 15px; border-bottom: 2px solid #eee; }
    .eval-list td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
    
    /* Ações */
    .btn-view { background: #3b5998; color: white; border: none; padding: 6px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; margin-right: 10px; }
    .icon-action { color: #555; font-size: 1.1em; margin-left: 10px; cursor: pointer; }
    .icon-action:hover { color: #3b5998; }
    .icon-trash:hover { color: #c0392b; }

    /* Modal Ver Mais */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
    .modal-content { background: white; padding: 40px; border-radius: 10px; width: 800px; max-height: 90vh; overflow-y: auto; position:relative; }
    .preview-table { width: 100%; border-collapse: collapse; margin-top:20px; border:1px solid #ccc; }
    .preview-table th { background: #eef2f7; padding: 10px; border: 1px solid #ccc; }
    .preview-table td { padding: 10px; border: 1px solid #ccc; }
</style>
@endpush


    <div class="header-actions">
        <h1 class="page-title">Avaliações</h1>
        <a href="{{ route('avaliacoes.create') }}" class="btn-primary"><i class="fa-solid fa-plus"></i> Criar Avaliação</a>
    </div>



    <div class="list-container">
        <table class="eval-list">
            <thead>
                <tr>
                    <th style="width: 50%;">Avaliação</th>
                    <th style="width: 20%;">Criada em</th>
                    <th style="width: 30%; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avaliacoes as $av)
                <tr>
                    <td data-label="Avaliação">
                        <div class="mobile-td-content">
                            <strong>{{ $av->titulo }}</strong>
                            <div style="font-size:12px; color:#777;">{{ $av->descricao }}</div>
                        </div>
                    </td>
                    <td data-label="Criada em">{{ $av->created_at->format('d/m/Y') }}</td>
                    <td data-label="Ações" style="text-align: center;">
                        <div style="display: flex; gap: 15px; align-items: center; justify-content: center;">
                            {{-- Ver Mais --}}
                            <button class="btn-view" style="margin: 0;" onclick="abrirModalPreview({{ $av->id }})">Ver Mais</button>
                            
                            @php
                                $user = auth()->user();
                                $podeEditar = false;
                                
                                if (in_array($user->perfil_acesso, ['admin', 'master'])) {
                                    $podeEditar = true;
                                } elseif ($user->perfil_acesso === 'gestor') {
                                    // Gestor pode editar se:
                                    // 1. Ele criou a avaliação (created_by)
                                    // 2. OU ele é um dos avaliadores (tipo='gestor')
                                    $criouAvaliacao = ($av->created_by == $user->id);
                                    $eAvaliador = $av->participantes
                                        ->where('avaliador_id', $user->id)
                                        ->where('tipo', 'gestor')
                                        ->isNotEmpty();
                                    
                                    $podeEditar = $criouAvaliacao || $eAvaliador;
                                }
                            @endphp

                            @if($podeEditar)
                                {{-- Editar --}}
                                <a href="{{ route('avaliacoes.edit', $av->id) }}" class="icon-action" style="margin: 0;" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                {{-- Excluir --}}
                                <form action="{{ route('avaliacoes.destroy', $av->id) }}" method="POST" style="margin: 0; display: flex; align-items: center;" class="confirm-delete" data-confirm-message="Tem certeza que deseja excluir a avaliação '{{ $av->titulo }}'?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none; border:none; padding:0; display: flex; align-items: center;">
                                        <i class="fa-solid fa-trash icon-action icon-trash" title="Excluir" style="margin: 0;"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center; padding:30px;">Nenhuma avaliação encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>


<div id="modalPreview" class="modal-overlay">
    <div class="modal-content">
        <button onclick="document.getElementById('modalPreview').style.display='none'" style="position:absolute; right:20px; top:20px; border:none; background:#3b5998; color:white; padding:5px 10px; border-radius:5px; cursor:pointer;">Fechar</button>
        <h2 id="previewTitulo" style="text-align:center;">Carregando...</h2>
        <div id="previewContent"></div>
    </div>
</div>

@push('scripts')
<script>
    async function abrirModalPreview(id) {
        document.getElementById('modalPreview').style.display = 'flex';
        document.getElementById('previewContent').innerHTML = '<p style="text-align:center">Carregando...</p>';
        
        try {
            const res = await fetch('{{ url("/api/avaliacao") }}/' + id + '/detalhes');
            const data = await res.json();

            document.getElementById('previewTitulo').innerText = data.titulo;
            
            let html = `
                <div style="display:flex; gap:10px; margin-bottom:20px; border-bottom:1px solid #ccc; padding-bottom:10px;">
                    <button onclick="switchTab('tabSkills')" class="btn-tab active" id="btn-tabSkills" style="background:none; border:none; border-bottom:2px solid #3b5998; font-weight:bold; cursor:pointer; color:#3b5998;">Perguntas</button>
                    <button onclick="switchTab('tabParticipants')" class="btn-tab" id="btn-tabParticipants" style="background:none; border:none; font-weight:bold; cursor:pointer; color:#777;">Participantes</button>
                </div>
            `;

            // ABA 1: PERGUNTAS
            html += `<div id="tabSkills">`;
            if(data.competencias.length === 0) html += '<p>Nenhuma competência cadastrada.</p>';
            data.competencias.forEach(comp => {
                html += `<h4 style="margin-top:20px; color:#333; background:#eee; padding:5px;">${comp.nome}</h4>
                         <table class="preview-table">
                            <thead><tr><th width="30%">Pergunta</th><th>Explicação</th></tr></thead>
                            <tbody>`;
                comp.skills.forEach(skill => {
                    html += `<tr><td><b>${skill.nome}</b></td><td>${skill.descricao || ''}</td></tr>`;
                });
                html += `</tbody></table>`;
            });
            html += `</div>`;

            // ABA 2: PARTICIPANTES
            html += `<div id="tabParticipants" style="display:none;">`;
            if(!data.participantes || data.participantes.length === 0) {
                html += '<p>Nenhum participante vinculado ainda.</p>';
            } else {
                
                // Agrupar por Avaliado
                const grouped = {};
                data.participantes.forEach(p => {
                    const avaliadoName = p.avaliado ? p.avaliado.name : 'Desconhecido';
                    if(!grouped[avaliadoName]) grouped[avaliadoName] = [];
                    grouped[avaliadoName].push(p);
                });

                html += `<div style="display:flex; flex-direction:column; gap:15px;">`;

                for (const [avaliado, parts] of Object.entries(grouped)) {
                    html += `
                        <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                            <div style="background: #f4f6f8; padding: 10px 15px; font-weight: bold; color: #333; border-bottom: 1px solid #ddd; display:flex; justify-content:space-between; align-items:center;">
                                <span><i class="fa-solid fa-user" style="color:#555; margin-right:8px;"></i> ${avaliado}</span>
                                <span style="font-size:12px; color:#777; font-weight:normal;">${parts.length} Avaliações</span>
                            </div>
                            <div style="padding: 10px;">
                                <table style="width:100%; font-size:13px; border-collapse: collapse;">
                                    <tbody>
                    `;

                    parts.forEach(p => {
                        let tipoLabel = '';
                        if(p.tipo === 'autoavaliacao') tipoLabel = 'Autoavaliação';
                        else if(p.tipo === 'gestor') tipoLabel = 'Avaliador'; // Changed label from 'Gestor' to 'Avaliador' to match user perception
                        else if(p.tipo === 'mediador') tipoLabel = 'Mediador';
                        else tipoLabel = 'Outro'; // Fallback
                        
                        let statusColor = p.status === 'realizada' ? '#27ae60' : '#f39c12';
                        let statusIcon = p.status === 'realizada' ? '<i class="fa-solid fa-check-circle"></i>' : '<i class="fa-solid fa-clock"></i>';
                        let avaliadorName = p.avaliador ? p.avaliador.name : 'N/A';
                        
                        // Destaque visual se for autoavaliação
                        let rowBg = p.tipo === 'autoavaliacao' ? 'background-color:#fffbea;' : '';

                        html += `
                                <tr style="border-bottom: 1px solid #eee; ${rowBg}">
                                    <td style="padding: 8px; width: 40%;">
                                        <span style="font-weight:600; color:#555;">${avaliadorName}</span>
                                    </td>
                                    <td style="padding: 8px; width: 30%; color:#777;">
                                        ${tipoLabel}
                                    </td>
                                    <td style="padding: 8px; width: 30%; text-align:right; color:${statusColor}; font-weight:bold;">
                                        ${statusIcon} <span style="font-size:11px;">${p.status.toUpperCase()}</span>
                                    </td>
                                </tr>
                        `;
                    });

                    html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
                html += `</div>`;
            }
            html += `</div>`;

            document.getElementById('previewContent').innerHTML = html;

        } catch (e) {
            document.getElementById('previewContent').innerHTML = '<p style="color:red; text-align:center">Erro ao carregar detalhes.</p>';
        }
    }

    function switchTab(tabId) {
        document.getElementById('tabSkills').style.display = 'none';
        document.getElementById('tabParticipants').style.display = 'none';
        
        document.getElementById('btn-tabSkills').style.borderBottom = 'none';
        document.getElementById('btn-tabSkills').style.color = '#777';
        document.getElementById('btn-tabParticipants').style.borderBottom = 'none';
        document.getElementById('btn-tabParticipants').style.color = '#777';

        document.getElementById(tabId).style.display = 'block';
        document.getElementById('btn-' + tabId).style.borderBottom = '2px solid #3b5998';
        document.getElementById('btn-' + tabId).style.color = '#3b5998';
    }
</script>
@endpush
@endsection