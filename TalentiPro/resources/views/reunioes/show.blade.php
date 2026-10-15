@extends('layouts.app')

@section('title', 'Resumo da Reunião - ' . $reuniao->titulo)
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
    :root {
        --color-primary: #4f46e5;
        --color-primary-hover: #3730a3;
        --color-accent: #6d28d9;
        --color-slate-50: #f8fafc;
        --color-slate-100: #f1f5f9;
        --color-slate-500: #64748b;
        --color-slate-900: #0f172a;
        --radius-card: 16px;
        --radius-element: 10px;
        --radius-pill: 99px;
        --shadow-card: 0 4px 16px rgba(0,0,0,.08);
        --border-soft: 1px solid #e9eaf0;
        --font-main: 'Inter', system-ui, sans-serif;
    }

    .reuniao-page { font-family: var(--font-main); background: var(--color-slate-50); min-height: 100vh; padding: 24px; }
    
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 32px; }
    
    .card-panel { background: #fff; border-radius: var(--radius-card); padding: 28px 32px; box-shadow: var(--shadow-card); border: var(--border-soft); margin-bottom: 24px; }
    
    .panel-title { font-size: 1.1rem; font-weight: 700; color: var(--color-slate-900); margin: 0 0 20px 0; padding-bottom: 14px; border-bottom: var(--border-soft); display: flex; align-items: center; gap: 10px; }
    
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: var(--radius-pill); font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; text-decoration: none; border: none; font-family: var(--font-main); }
    .btn-primary { background: var(--color-primary); color: #fff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
    .btn-primary:hover { background: var(--color-primary-hover); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4); }
    .btn-secondary { background: var(--color-slate-50); color: var(--color-slate-900); border: var(--border-soft); }
    .btn-secondary:hover { background: var(--color-slate-100); transform: translateY(-1px); }
    .btn-danger { background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.18); }
    .btn-success { background: #10b981; color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .btn-success:hover { background: #059669; transform: translateY(-2px); }

    .form-control { width: 100%; padding: 12px 16px; border-radius: var(--radius-element); border: var(--border-soft); background: #fff; font-size: 0.95rem; color: var(--color-slate-900); font-family: var(--font-main); transition: all 0.2s; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .form-label { font-size: 0.85rem; font-weight: 700; color: var(--color-slate-900); margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.02em;}

    .pdi-card-item { background: var(--color-slate-50); border: var(--border-soft); border-radius: var(--radius-element); padding: 20px; position: relative; margin-bottom: 16px; transition: all 0.2s; }
    .pdi-card-item:hover { border-color: var(--color-primary); }

    @media (max-width: 768px) {
        .reuniao-page { padding: 16px; }
        .page-header { flex-direction: column; align-items: stretch; }
        .card-panel { padding: 20px; }
        .grid-layout { grid-template-columns: 1fr !important; }
    }
</style>

<div class="reuniao-page">
    <div class="page-header">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-slate-900); margin: 0 0 4px 0; letter-spacing: -0.02em;">
                <i class="fa-solid fa-file-lines" style="color: var(--color-primary);"></i> Relatório da Reunião {{ $reuniao->feedback->audio_path ? '(IA)' : '(Manual)' }}
            </h1>
            <p style="color: var(--color-slate-500); font-size: 0.95rem; margin: 0;">
                Reunião com: <strong style="color: var(--color-slate-900);">{{ auth()->id() === $reuniao->gestor_id ? $reuniao->participantes->pluck('name')->join(', ') : $reuniao->gestor->name }}</strong>
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if(auth()->id() === $reuniao->gestor_id || in_array(auth()->user()->perfil_acesso->value, ['master', 'admin']))
                <form id="form-excluir-reuniao" action="{{ route('reunioes.destroy', $reuniao) }}" method="POST" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmarExclusaoReuniao()" class="btn btn-danger">
                        <i class="fa-solid fa-trash-can"></i> Excluir Registro
                    </button>
                </form>
            @endif
            @if(auth()->id() === $reuniao->gestor_id)
                <button id="btn-iniciar-edicao" onclick="iniciarEdicao()" class="btn btn-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Editar Registro
                </button>
                <button id="btn-cancelar-edicao" onclick="cancelarEdicao()" class="btn btn-secondary" style="display: none;">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button id="btn-salvar-edicao" onclick="salvarEdicao()" class="btn btn-success" style="display: none;">
                    <i class="fa-solid fa-check"></i> Salvar Alterações
                </button>
            @endif
            <a href="{{ route('reunioes.index') }}" class="btn btn-secondary">Voltar para Reuniões</a>
        </div>
    </div>

    <div class="grid-layout" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Resumo e Transcrição -->
        <div style="display: flex; flex-direction: column; gap: 24px; min-width: 0;">
            <div class="card-panel" style="margin-bottom: 0;">
                <h2 class="panel-title">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--color-primary);"></i> Resumo Executivo
                </h2>
                
                {{-- Resumo Mode Visualização --}}
                <div id="resumo-visualizacao" style="background-color: var(--color-slate-50); padding: 20px; border-radius: var(--radius-element); border-left: 4px solid var(--color-primary); line-height: 1.7; color: var(--color-slate-900); font-size: 0.95rem;">
                    {!! nl2br(e($reuniao->feedback->resumo_ia ?? 'Nenhum resumo gerado.')) !!}
                </div>

                {{-- Resumo Mode Edição (oculto) --}}
                <div id="resumo-edicao" style="display: none;">
                    <textarea id="edit-resumo-texto" rows="12" class="form-control" style="resize: vertical; line-height: 1.6;">{{ $reuniao->feedback->resumo_ia }}</textarea>
                </div>

                @if($reuniao->feedback->resumo_ia_original)
                <div style="margin-top: 24px; background-color: #f1f5f9; padding: 20px; border-radius: var(--radius-element); border-left: 4px solid var(--color-slate-500);">
                    <h4 style="margin: 0 0 10px 0; color: var(--color-slate-900); font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-robot"></i> Resumo Original da IA (Apenas Leitura)
                    </h4>
                    <div style="line-height: 1.6; color: var(--color-slate-500); font-size: 0.9rem;">
                        {!! nl2br(e($reuniao->feedback->resumo_ia_original)) !!}
                    </div>
                </div>
                @endif
            </div>

            <div class="card-panel" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: var(--border-soft); padding-bottom: 14px; margin-bottom: 20px;">
                    <h2 class="panel-title" style="border: none; padding: 0; margin: 0;">
                        <i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Planos de Desenvolvimento (PDI) Estabelecidos
                    </h2>
                    <div id="edicao-pdi-acoes" style="display: none;">
                        <button type="button" onclick="adicionarPdi()" class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.85rem;">
                            <i class="fa-solid fa-plus"></i> Adicionar PDI
                        </button>
                    </div>
                </div>
                
                <div id="pdis-container" style="display: flex; flex-direction: column; gap: 16px;">
                    {{-- Renderizado via JavaScript --}}
                </div>
                <div id="sem-pdis" style="display: none; padding: 40px 20px; text-align: center; color: var(--color-slate-500); background: var(--color-slate-50); border-radius: var(--radius-element); border: 1px dashed #cbd5e1;">
                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                    Nenhum PDI estabelecido nesta reunião.
                </div>
            </div>

            @if($reuniao->feedback->transcricao_completa !== 'Registro Manual')
            <div class="card-panel" style="margin-bottom: 0;">
                <h3 class="panel-title">
                    <i class="fa-solid fa-quote-left" style="color: var(--color-slate-500);"></i> Transcrição (Final)
                </h3>
                <div style="background-color: var(--color-slate-50); padding: 20px; border-radius: var(--radius-element); border: var(--border-soft); max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.9rem; line-height: 1.6; color: var(--color-slate-500);">
                    {!! nl2br(e($reuniao->feedback->transcricao_completa ?? 'Nenhuma transcrição disponível.')) !!}
                </div>
            </div>
            @endif

            @if($reuniao->feedback->transcricao_original && $reuniao->feedback->transcricao_original !== 'Registro Manual')
            <div class="card-panel" style="margin-bottom: 0;">
                <h3 class="panel-title">
                    <i class="fa-solid fa-file-audio" style="color: var(--color-slate-500);"></i> Transcrição Original (Bruta da IA)
                </h3>
                <div style="background-color: #f1f5f9; padding: 20px; border-radius: var(--radius-element); border-left: 4px solid var(--color-slate-500); max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; line-height: 1.6; color: var(--color-slate-500);">
                    {!! nl2br(e($reuniao->feedback->transcricao_original)) !!}
                </div>
            </div>
            @endif
        </div>

        <!-- Metadados (Sidebar da Grid) -->
        <div style="display: flex; flex-direction: column; gap: 24px; min-width: 0; align-self: start;">
            <div class="card-panel" style="margin-bottom: 0;">
                <h3 class="panel-title"><i class="fa-solid fa-circle-info" style="color: var(--color-primary);"></i> Detalhes da Reunião</h3>
                
                <div style="margin-bottom: 24px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-slate-500); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Data da Reunião</span>
                    <strong style="color: var(--color-slate-900); font-size: 1.05rem;">{{ $reuniao->data_hora->format('d/m/Y \à\s H:i') }}</strong>
                </div>

                {{-- Exibe a data de modificação se o relatório foi editado --}}
                <div id="bloco-data-modificacao" style="margin-bottom: 24px; display: {{ $reuniao->feedback->updated_at->diffInSeconds($reuniao->feedback->created_at) > 5 ? 'block' : 'none' }}; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); padding: 14px; border-radius: var(--radius-element); border-left: 4px solid #d97706;">
                    <span style="color: #b45309; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px;"><i class="fa-solid fa-clock-rotate-left"></i> Última Modificação</span>
                    <strong id="data-modificacao-texto" style="color: #b45309;">{{ $reuniao->feedback->updated_at->format('d/m/Y \à\s H:i') }}</strong>
                </div>

                <div style="margin-bottom: 24px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-slate-500); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Sentimento Geral</span>
                    @if($reuniao->feedback->sentimento_ia)
                        @php
                            $sentimento = strtolower($reuniao->feedback->sentimento_ia ?? 'neutro');
                            $cor = 'var(--color-slate-500)'; 
                            $bg = '#f1f5f9';
                            if(str_contains($sentimento, 'positivo')) { $cor = '#059669'; $bg = 'rgba(16, 185, 129, 0.15)'; }
                            if(str_contains($sentimento, 'negativo')) { $cor = '#dc2626'; $bg = 'rgba(239, 68, 68, 0.15)'; }
                        @endphp
                        <span style="background: {{ $bg }}; color: {{ $cor }}; padding: 6px 14px; font-weight: 700; font-size: 0.9rem; border-radius: var(--radius-pill); display: inline-flex; align-items: center; gap: 8px;">
                            @if($cor === '#059669') <i class="fa-regular fa-face-smile"></i>
                            @elseif($cor === '#dc2626') <i class="fa-regular fa-face-frown"></i>
                            @else <i class="fa-regular fa-face-meh"></i>
                            @endif
                            {{ ucfirst($reuniao->feedback->sentimento_ia) }}
                        </span>
                    @else
                        <span style="color: var(--color-slate-500); font-style: italic; font-size: 0.9rem;">Sem análise de IA</span>
                    @endif
                </div>

                @if($reuniao->google_meet_link)
                    <div style="margin-top: 24px; border-top: var(--border-soft); padding-top: 24px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-slate-500); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 10px;">Link da Reunião</span>
                        <a href="{{ $reuniao->google_meet_link }}" target="_blank" class="btn btn-secondary" style="width: 100%; justify-content: center; color: #4285F4; border-color: rgba(66, 133, 244, 0.3);">
                            <i class="fa-solid fa-video"></i> Acessar Sala do Meet
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if(auth()->id() === $reuniao->gestor_id)
@push('scripts')
<script>
    const URL_UPDATE_REGISTRO = '{{ route('reunioes.update_registro', $reuniao->id) }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    
    // Lista de participantes da reunião para o select de novos/editados PDIs
    const PARTICIPANTES_LISTA = <?php echo json_encode($reuniao->feedback->participantes->map(function($p) {
        return ['id' => $p->id, 'name' => $p->name];
    })); ?>;

    // Inicializa a lista de PDIs a partir do banco
    let pdisData = <?php echo json_encode($reuniao->feedback->pdis->map(function($p) {
        return [
            'id' => $p->id,
            'colaborador_id' => $p->colaborador_id,
            'titulo' => $p->titulo,
            'descricao' => $p->descricao,
            'prazo' => $p->data_fim ? $p->data_fim->format('Y-m-d') : '',
            'status' => $p->status->value,
            'acoes' => $p->acoes->map(function($a) {
                return ['id' => $a->id, 'titulo' => $a->titulo, 'descricao' => $a->descricao];
            })->toArray()
        ];
    })); ?>;

    // Clonamos os dados iniciais para restaurar se o usuário cancelar
    const INITIAL_PDIS = JSON.parse(JSON.stringify(pdisData));
    const INITIAL_RESUMO = <?php echo json_encode($reuniao->feedback->resumo_ia); ?>;

    let deletarPdis = [];
    let isEditMode = false;

    function confirmarExclusaoReuniao() {
        Swal.fire({
            title: 'Excluir Reunião?',
            text: 'Tem certeza que deseja excluir esta reunião e todos os registros associados? Esta ação não pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-excluir-reuniao').submit();
            }
        });
    }

    // Inicializa a renderização em modo leitura
    document.addEventListener('DOMContentLoaded', () => {
        renderPdis();
    });

    function iniciarEdicao() {
        isEditMode = true;
        
        // Controla visibilidade de botões do cabeçalho
        document.getElementById('btn-iniciar-edicao').style.display = 'none';
        document.getElementById('btn-cancelar-edicao').style.display = 'inline-flex';
        document.getElementById('btn-salvar-edicao').style.display = 'inline-flex';

        // Alterna entre visualização e textarea do Resumo
        document.getElementById('resumo-visualizacao').style.display = 'none';
        document.getElementById('resumo-edicao').style.display = 'block';

        // Exibe o botão de Adicionar PDI
        document.getElementById('edicao-pdi-acoes').style.display = 'block';

        deletarPdis = [];
        renderPdis();
    }

    function cancelarEdicao() {
        isEditMode = false;

        // Controla visibilidade de botões do cabeçalho
        document.getElementById('btn-iniciar-edicao').style.display = 'inline-flex';
        document.getElementById('btn-cancelar-edicao').style.display = 'none';
        document.getElementById('btn-salvar-edicao').style.display = 'none';

        // Alterna entre visualização e textarea do Resumo
        document.getElementById('resumo-visualizacao').style.display = 'block';
        document.getElementById('resumo-edicao').style.display = 'none';

        // Oculta o botão de Adicionar PDI
        document.getElementById('edicao-pdi-acoes').style.display = 'none';

        // Restaura valores originais
        document.getElementById('edit-resumo-texto').value = INITIAL_RESUMO;
        pdisData = JSON.parse(JSON.stringify(INITIAL_PDIS));
        deletarPdis = [];
        
        renderPdis();
    }

    function renderPdis() {
        const container = document.getElementById('pdis-container');
        const semPdis = document.getElementById('sem-pdis');
        container.innerHTML = '';

        if (!pdisData.length) {
            semPdis.style.display = 'block';
            return;
        }
        semPdis.style.display = 'none';

        pdisData.forEach((pdi, idx) => {
            if (isEditMode) {
                // MODO EDIÇÃO
                container.insertAdjacentHTML('beforeend', `
                    <div class="pdi-card-item" style="border-left: 4px solid var(--color-primary); background: #fff;">
                        <button type="button" onclick="removerPdi(${idx})" style="position: absolute; top: 12px; right: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; width: 30px; height: 30px; border-radius: var(--radius-element); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="Excluir PDI">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label class="form-label">Colaborador</label>
                                <select onchange="updatePdiField(${idx}, 'colaborador_id', this.value)" class="form-control" style="padding: 10px 14px;">
                                    ${PARTICIPANTES_LISTA.map(p => `<option value="${p.id}" ${p.id == pdi.colaborador_id ? 'selected' : ''}>${escapeHtml(p.name)}</option>`).join('')}
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Prazo</label>
                                <input type="date" value="${pdi.prazo || ''}" onchange="updatePdiField(${idx}, 'prazo', this.value)" class="form-control" style="padding: 10px 14px;">
                            </div>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Título do PDI</label>
                            <input type="text" value="${escapeHtml(pdi.titulo || '')}" oninput="updatePdiField(${idx}, 'titulo', this.value)" class="form-control" style="padding: 10px 14px;">
                        </div>
                        <div>
                            <label class="form-label">Descrição</label>
                            <textarea rows="3" oninput="updatePdiField(${idx}, 'descricao', this.value)" class="form-control" style="padding: 10px 14px; resize: vertical;">${escapeHtml(pdi.descricao || '')}</textarea>
                        </div>
                        <div style="margin-top: 20px; border-top: var(--border-soft); padding-top: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label style="font-size: 0.9rem; color: var(--color-slate-900); font-weight: 700;"><i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Metas / Etapas (Ações)</label>
                                <button onclick="adicionarAcaoPdi(${idx})" type="button" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;"><i class="fa-solid fa-plus"></i> Adicionar Etapa</button>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                ${(pdi.acoes || []).map((acao, aIdx) => `
                                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; background: var(--color-slate-50); padding: 14px; border: var(--border-soft); border-radius: var(--radius-element); align-items: start;">
                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <input type="text" value="${escapeHtml(acao.titulo || '')}" oninput="updateAcaoField(${idx}, ${aIdx}, 'titulo', this.value)" class="form-control" placeholder="Título da etapa" style="padding: 8px 12px; font-size: 0.9rem;">
                                            <input type="text" value="${escapeHtml(acao.descricao || '')}" oninput="updateAcaoField(${idx}, ${aIdx}, 'descricao', this.value)" class="form-control" placeholder="Descrição (Opcional)" style="padding: 8px 12px; font-size: 0.9rem;">
                                        </div>
                                        <button onclick="removerAcaoPdi(${idx}, ${aIdx})" type="button" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; width: 30px; height: 30px; border-radius: var(--radius-element); cursor: pointer; display: flex; align-items: center; justify-content: center;" title="Remover etapa"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                `).join('')}
                                ${!(pdi.acoes && pdi.acoes.length) ? '<p style="font-size: 0.85rem; color: var(--color-slate-500); font-style: italic; margin: 0;">Nenhuma etapa cadastrada.</p>' : ''}
                            </div>
                        </div>
                    </div>
                `);
            } else {
                // MODO LEITURA
                const colab = PARTICIPANTES_LISTA.find(p => p.id == pdi.colaborador_id) || { name: 'Desconhecido' };
                const dataFormatada = pdi.prazo ? formatarDataBr(pdi.prazo) : 'Não definido';
                
                let badgeStatus = '';
                let statusLabel = (pdi.status || 'nao_iniciado').replace('_', ' ').toUpperCase();
                if(pdi.status === 'concluido') badgeStatus = 'background: rgba(16, 185, 129, 0.15); color: #065f46;';
                else if(pdi.status === 'em_andamento') badgeStatus = 'background: rgba(245, 158, 11, 0.15); color: #b45309;';
                else badgeStatus = 'background: rgba(100, 116, 139, 0.12); color: #475569;';

                const acoesHtml = (pdi.acoes && pdi.acoes.length > 0) ? `
                    <div style="margin-top: 16px; padding-top: 16px; border-top: var(--border-soft);">
                        <h5 style="margin: 0 0 10px 0; font-size: 0.85rem; font-weight: 700; color: var(--color-slate-900); text-transform: uppercase; letter-spacing: 0.05em;"><i class="fa-solid fa-list-check" style="color: var(--color-primary);"></i> Etapas (${pdi.acoes.length})</h5>
                        <ul style="margin: 0; padding-left: 20px; color: var(--color-slate-500); font-size: 0.9rem; line-height: 1.6;">
                            ${pdi.acoes.map(a => `<li><strong style="color: var(--color-slate-900);">${escapeHtml(a.titulo)}</strong>${a.descricao ? ' - ' + escapeHtml(a.descricao) : ''}</li>`).join('')}
                        </ul>
                    </div>
                ` : '';

                container.insertAdjacentHTML('beforeend', `
                    <div class="pdi-card-item" style="border-left: 4px solid var(--color-primary); background: #fff;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 12px;">
                            <h4 style="margin: 0; color: var(--color-slate-900); font-size: 1.1rem; font-weight: 800;">${escapeHtml(pdi.titulo || 'Sem título')}</h4>
                            <span style="${badgeStatus} padding: 4px 10px; font-size: 0.75rem; font-weight: 700; border-radius: var(--radius-pill); white-space: nowrap;">
                                ${statusLabel}
                            </span>
                        </div>
                        <p style="color: var(--color-slate-500); font-size: 0.95rem; margin: 0 0 16px 0; line-height: 1.6;">${escapeHtml(pdi.descricao || 'Sem descrição.').replace(/\n/g, '<br>')}</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: var(--color-slate-500); border-top: var(--border-soft); padding-top: 12px; flex-wrap: wrap; gap: 10px;">
                            <span><strong style="color: var(--color-slate-900);">Colaborador:</strong> ${escapeHtml(colab.name)}</span>
                            <span><strong style="color: var(--color-slate-900);">Prazo:</strong> ${dataFormatada}</span>
                        </div>
                        ${acoesHtml}
                    </div>
                `);
            }
        });
    }

    function adicionarPdi() {
        const primeiro = PARTICIPANTES_LISTA[0] || {};
        pdisData.push({
            id: '',
            colaborador_id: primeiro.id || '',
            titulo: '',
            descricao: '',
            prazo: '',
            status: 'nao_iniciado',
            acoes: []
        });
        renderPdis();
    }

    function removerPdi(idx) {
        Swal.fire({
            title: 'Excluir PDI?',
            text: 'Tem certeza que deseja excluir este Plano de Desenvolvimento Individual (PDI)? O item será deletado definitivamente ao salvar as alterações.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#a0aec0',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const pdi = pdisData[idx];
                if (pdi && pdi.id) {
                    deletarPdis.push(pdi.id);
                }
                pdisData.splice(idx, 1);
                renderPdis();
            }
        });
    }

    function updatePdiField(idx, field, value) {
        if (pdisData[idx]) {
            pdisData[idx][field] = value;
        }
    }

    function updateAcaoField(pdiIdx, acaoIdx, field, value) {
        if (pdisData[pdiIdx] && pdisData[pdiIdx].acoes && pdisData[pdiIdx].acoes[acaoIdx]) {
            pdisData[pdiIdx].acoes[acaoIdx][field] = value;
        }
    }

    function adicionarAcaoPdi(idx) {
        if (pdisData[idx]) {
            if (!pdisData[idx].acoes) pdisData[idx].acoes = [];
            pdisData[idx].acoes.push({ id: '', titulo: '', descricao: '' });
            renderPdis();
        }
    }

    function removerAcaoPdi(pdiIdx, acaoIdx) {
        if (pdisData[pdiIdx] && pdisData[pdiIdx].acoes) {
            pdisData[pdiIdx].acoes.splice(acaoIdx, 1);
            renderPdis();
        }
    }

    async function salvarEdicao() {
        const btnSalvar = document.getElementById('btn-salvar-edicao');
        const btnCancelar = document.getElementById('btn-cancelar-edicao');
        
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Salvando...';

        const resumoText = document.getElementById('edit-resumo-texto').value.trim();
        if (!resumoText) {
            alert('O resumo não pode ficar em branco.');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
            return;
        }

        // Valida se todos os PDIs possuem título
        for (let i = 0; i < pdisData.length; i++) {
            if (!pdisData[i].titulo.trim()) {
                alert(`Por favor, preencha o título do PDI #${i+1}`);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
                return;
            }
        }

        const body = new FormData();
        body.append('_token', CSRF_TOKEN);
        body.append('resumo', resumoText);
        
        pdisData.forEach((pdi, i) => {
            if (pdi.id) body.append(`pdis[${i}][id]`, pdi.id);
            body.append(`pdis[${i}][colaborador_id]`, pdi.colaborador_id);
            body.append(`pdis[${i}][titulo]`, pdi.titulo);
            body.append(`pdis[${i}][descricao]`, pdi.descricao || '');
            if (pdi.prazo) body.append(`pdis[${i}][prazo]`, pdi.prazo);
            
            if (pdi.acoes) {
                pdi.acoes.filter(a => a.titulo && a.titulo.trim() !== '').forEach((acao, a) => {
                    if (acao.id) body.append(`pdis[${i}][acoes][${a}][id]`, acao.id);
                    body.append(`pdis[${i}][acoes][${a}][titulo]`, acao.titulo);
                    body.append(`pdis[${i}][acoes][${a}][descricao]`, acao.descricao || '');
                });
            }
        });

        deletarPdis.forEach(id => body.append('deletar_pdis[]', id));

        try {
            const resp = await fetch(URL_UPDATE_REGISTRO, { method: 'POST', body });
            const data = await resp.json();

            if (!resp.ok || !data.success) {
                alert(data.error || 'Ocorreu um erro ao atualizar o registro.');
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
                return;
            }

            window.location.reload();

        } catch (e) {
            alert('Erro de conexão ao tentar salvar as alterações.');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Alterações';
        }
    }

    function formatarDataBr(dataIso) {
        if (!dataIso) return '';
        const parts = dataIso.split('-');
        if (parts.length !== 3) return dataIso;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
@endif
