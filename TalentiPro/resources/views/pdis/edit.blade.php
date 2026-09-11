@extends('layouts.app')

@section('title', 'Editar PDI Individual')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Editar PDI Individual</h1>
        <p style="color: #666; font-size: 0.95rem;">Atualize as informações do plano de desenvolvimento deste colaborador.</p>
    </div>
    <div>
        <a href="{{ route('pdis.index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<form action="{{ route('pdis.update', $pdi->id) }}" method="POST" id="form-pdi">
    @csrf
    @method('PUT')
    
    <input type="hidden" name="tipo_criacao" value="individual">

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        
        <!-- CARD PRINCIPAL: DADOS DO PDI -->
        <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
            <h3 style="margin: 0 0 10px 0; color: #2d3748; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <i class="fa-solid fa-file-invoice" style="color: #6a4c7c;"></i> Informações Gerais
            </h3>

            <!-- Seleção de Colaboradores -->
            <div id="wrapper-individual">
                <label class="form-label" for="colaborador_id">Colaborador <span style="color: red;">*</span></label>
                <select name="colaborador_id" id="colaborador_id" class="form-control" required>
                    <option value="">Selecione o colaborador...</option>
                    @foreach($colaboradores as $colab)
                        <option value="{{ $colab->id }}" {{ old('colaborador_id', $pdi->colaborador_id) == $colab->id ? 'selected' : '' }}>
                            {{ $colab->name }} ({{ $colab->cargo->nome ?? 'Sem cargo' }})
                        </option>
                    @endforeach
                </select>
                @error('colaborador_id')
                    <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <!-- Título e Categoria -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div>
                    <label class="form-label" for="titulo">Título do PDI <span style="color: red;">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="form-control" value="{{ old('titulo', $pdi->titulo) }}" required>
                    @error('titulo')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="form-label" for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" class="form-control">
                        <option value="Técnico" {{ old('categoria', $pdi->categoria) == 'Técnico' ? 'selected' : '' }}>Técnico (Hard Skills)</option>
                        <option value="Comportamental" {{ old('categoria', $pdi->categoria) == 'Comportamental' ? 'selected' : '' }}>Comportamental (Soft Skills)</option>
                        <option value="Liderança" {{ old('categoria', $pdi->categoria) == 'Liderança' ? 'selected' : '' }}>Liderança / Gestão</option>
                        <option value="Outros" {{ old('categoria', $pdi->categoria) == 'Outros' ? 'selected' : '' }}>Outros</option>
                    </select>
                    @error('categoria')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Prazos -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label class="form-label" for="data_inicio">Data de Início</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="{{ old('data_inicio', $pdi->data_inicio ? $pdi->data_inicio->format('Y-m-d') : '') }}">
                    @error('data_inicio')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="form-label" for="data_fim">Data de Conclusão (Prazo)</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" value="{{ old('data_fim', $pdi->data_fim ? $pdi->data_fim->format('Y-m-d') : '') }}">
                    @error('data_fim')
                        <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Descrição Geral -->
            <div>
                <label class="form-label" for="descricao">Descrição e Objetivo Geral do PDI</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3">{{ old('descricao', $pdi->descricao) }}</textarea>
                @error('descricao')
                    <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- CARD SECUNDÁRIO: AÇÕES/ETAPAS -->
        <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">
                <div>
                    <h3 style="margin: 0; color: #2d3748;">
                        <i class="fa-solid fa-list-check" style="color: #6a4c7c;"></i> Etapas / Ações
                    </h3>
                </div>
                <button type="button" class="btn-secondary" onclick="adicionarAcao()" style="padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fa-solid fa-plus"></i> Adicionar Ação
                </button>
            </div>

            <div id="acoes-container" style="display: flex; flex-direction: column; gap: 15px;">
                @foreach(old('acoes', $pdi->acoes) as $index => $acao)
                    <div class="acao-row" data-index="{{ $index }}" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: start; animation: fadeIn 0.3s ease;">
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <label class="form-label" style="font-size: 0.85rem; font-weight: bold;">Título da Ação/Meta <span style="color: red;">*</span></label>
                                @php
                                    $idAcao = is_array($acao) ? ($acao['id'] ?? '') : $acao->id;
                                    $tituloOld = is_array($acao) ? ($acao['titulo'] ?? '') : $acao->titulo;
                                    $descricaoOld = is_array($acao) ? ($acao['descricao'] ?? '') : $acao->descricao;
                                @endphp
                                @if($idAcao)
                                    <input type="hidden" name="acoes[{{ $index }}][id]" value="{{ $idAcao }}">
                                @endif
                                <input type="text" name="acoes[{{ $index }}][titulo]" class="form-control" value="{{ $tituloOld }}" required>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 0.85rem;">Como realizar / Critério de sucesso (Opcional)</label>
                                <textarea name="acoes[{{ $index }}][descricao]" class="form-control" rows="2">{{ $descricaoOld }}</textarea>
                            </div>
                        </div>
                        <div style="padding-top: 25px;">
                            <button type="button" class="btn-danger btn-remove-acao" onclick="removerAcao(this)" style="padding: 10px 14px; background-color: #e74c3c; {{ count(old('acoes', $pdi->acoes)) > 1 ? '' : 'display:none;' }}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <p style="color: #e53e3e; font-size: 0.8rem; margin: 0; font-weight: bold;">
                <i class="fa-solid fa-circle-info"></i> Atenção: Ações removidas aqui serão apagadas do PDI, mesmo que já as tenha concluído.
            </p>
            @error('acoes')
                <span style="color: #e53e3e; font-size: 0.85rem;">{{ $message }}</span>
            @enderror
        </div>

    </div>

    <!-- SUBMIT -->
    <div style="margin-top: 20px; text-align: right; margin-bottom: 50px;">
        <button type="submit" class="btn-primary" style="font-size: 1.1rem; padding: 12px 30px;">
            <i class="fa-solid fa-floppy-disk"></i> Atualizar PDI
        </button>
    </div>

</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .choices__inner {
        border-radius: 8px;
        background-color: #fff;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    let choicesColaborador;
    let acaoIndex = {{ count(old('acoes', $pdi->acoes)) + 10 }};

    document.addEventListener('DOMContentLoaded', function() {
        choicesColaborador = new Choices('#colaborador_id', { removeItemButton: true, searchEnabled: true, itemSelectText: '', placeholderValue: 'Pesquise o colaborador...' });
    });

    let isAdding = false;
    function adicionarAcao() {
        if (isAdding) return;
        isAdding = true;

        const container = document.getElementById('acoes-container');

        const acaoHTML = `
            <div class="acao-row" data-index="${acaoIndex}" style="background: #f7fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: start; animation: fadeIn 0.3s ease;">
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <label class="form-label" style="font-size: 0.85rem; font-weight: bold;">Título da Ação/Meta <span style="color: red;">*</span></label>
                        <input type="text" name="acoes[${acaoIndex}][titulo]" class="form-control" placeholder="Ex: Realizar o curso XPTO de Comunicação Assertiva" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.85rem;">Como realizar / Critério de sucesso (Opcional)</label>
                        <textarea name="acoes[${acaoIndex}][descricao]" class="form-control" rows="2" placeholder="Ex: Assistir todas as aulas e aplicar a técnica de escuta ativa nas reuniões de departamento."></textarea>
                    </div>
                </div>
                <div style="padding-top: 25px;">
                    <button type="button" class="btn-danger btn-remove-acao" onclick="removerAcao(this)" style="padding: 10px 14px; background-color: #e74c3c;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', acaoHTML);
        acaoIndex++;

        atualizarBotoesRemover();
        
        setTimeout(() => { isAdding = false; }, 300);
    }

    function removerAcao(button) {
        const row = button.closest('.acao-row');
        const totalAcoes = document.querySelectorAll('.acao-row').length;

        if (totalAcoes > 1) {
            row.remove();
            atualizarBotoesRemover();
        } else {
            alert('É necessário ter pelo menos uma ação no PDI.');
        }
    }

    function atualizarBotoesRemover() {
        const botoes = document.querySelectorAll('.btn-remove-acao');
        if (botoes.length <= 1) {
            botoes.forEach(b => b.style.display = 'none');
        } else {
            botoes.forEach(b => b.style.display = 'inline-block');
        }
    }
</script>
@endpush
