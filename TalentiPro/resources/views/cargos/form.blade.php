@extends('layouts.admin')

@section('title', isset($cargo) ? 'Editar Cargo' : 'Novo Cargo')

@section('content')
<div class="header-actions">
    <h1 class="page-title">{{ isset($cargo) ? 'EDITAR CARGO' : 'NOVO CARGO' }}</h1>
    <a href="{{ route('cargos.index') }}" class="btn-primary" style="background:#95a5a6;">Voltar</a>
</div>

<div class="card-container" style="background:white; padding:30px; border-radius:10px;">
    <form action="{{ isset($cargo) ? route('cargos.update', $cargo->id) : route('cargos.store') }}" method="POST">
        @csrf
        @if(isset($cargo)) @method('PUT') @endif

        {{-- === DADOS PRINCIPAIS === --}}
        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; display:block; margin-bottom:5px;">Nome do Cargo *</label>
            <input type="text" name="nome" value="{{ $cargo->nome ?? old('nome') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>

        <div style="display:flex; gap:20px; margin-bottom:20px;">
            <div style="flex:1;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">CBO</label>
                <input type="text" name="cbo" value="{{ $cargo->cbo ?? old('cbo') }}" placeholder="Ex: 2124-05" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>
            <div style="flex:1;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Senioridade</label>
                <select name="senioridade" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                    <option value="">Selecione...</option>
                    <option value="Júnior" {{ (isset($cargo) && $cargo->senioridade == 'Júnior') ? 'selected' : '' }}>Júnior</option>
                    <option value="Pleno" {{ (isset($cargo) && $cargo->senioridade == 'Pleno') ? 'selected' : '' }}>Pleno</option>
                    <option value="Sênior" {{ (isset($cargo) && $cargo->senioridade == 'Sênior') ? 'selected' : '' }}>Sênior</option>
                    <option value="Especialista" {{ (isset($cargo) && $cargo->senioridade == 'Especialista') ? 'selected' : '' }}>Especialista</option>
                    <option value="Gerência" {{ (isset($cargo) && $cargo->senioridade == 'Gerência') ? 'selected' : '' }}>Gerência</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap:20px; margin-bottom:20px;">
            <div style="flex:1;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Departamento *</label>
                <select name="departamento_id" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                    <option value="">Selecione...</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep->id }}" {{ (isset($cargo) && $cargo->departamento_id == $dep->id) ? 'selected' : '' }}>
                            {{ $dep->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Responde para (Cargo Superior)</label>
                <select name="cargo_superior_id" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                    <option value="">Nenhum (Topo da hierarquia)</option>
                    @foreach($cargosSuperiores as $sup)
                        <option value="{{ $sup->id }}" {{ (isset($cargo) && $cargo->cargo_superior_id == $sup->id) ? 'selected' : '' }}>
                            {{ $sup->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; display:block; margin-bottom:5px;">Descrição</label>
            <textarea name="descricao" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">{{ $cargo->descricao ?? old('descricao') }}</textarea>
        </div>

        {{-- === COMPETÊNCIAS === --}}
        <div style="margin-top:30px; border-top:1px solid #eee; padding-top:20px;">
            <h3 style="color:#6a4c7c; margin-bottom:15px;">Competências Exigidas</h3>

            <div style="display:flex; gap:10px; margin-bottom:15px;">
                <select id="selectComp" style="flex:1; padding:10px; border:1px solid #ccc; border-radius:5px;">
                    <option value="">Escolha uma competência para adicionar...</option>
                    @foreach($competencias as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->nome }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="adicionarCompetencia()" style="background:#3b5998; color:white; border:none; padding:0 20px; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-plus"></i> Adicionar
                </button>
            </div>

            <div id="listaCompetencias" style="display:flex; flex-wrap:wrap; gap:10px;">
                {{-- Se for edição, popula as skills que já existem --}}
                @if(isset($cargo) && $cargo->competencias)
                    @foreach($cargo->competencias as $c)
                    <div class="tag-comp" id="comp-{{ $c->id }}" style="background:#eef2f7; padding:8px 15px; border-radius:20px; display:flex; align-items:center; gap:10px; border:1px solid #dce4ec;">
                        <input type="hidden" name="competencias[]" value="{{ $c->id }}">
                        <span style="font-weight:600; color:#333;">{{ $c->nome }}</span>
                        <i class="fa-solid fa-xmark" onclick="removerCompetencia({{ $c->id }})" style="cursor:pointer; color:#888;"></i>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- === BOTÃO SALVAR (AQUI ESTÁ ELE!) === --}}
        <div style="margin-top:30px; text-align:right;">
            <button type="submit" style="background:#6a4c7c; color:white; padding:12px 30px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
                <i class="fa-solid fa-check"></i> Salvar Cargo
            </button>
        </div>
    </form>
</div>

<script>
    function adicionarCompetencia() {
        const select = document.getElementById('selectComp');
        const id = select.value;
        const texto = select.options[select.selectedIndex].text;
        const container = document.getElementById('listaCompetencias');

        if (!id) return alert('Selecione uma competência!');

        // Verifica se já adicionou para não duplicar
        if (document.getElementById('comp-' + id)) return alert('Competência já adicionada!');

        const div = document.createElement('div');
        div.id = 'comp-' + id;
        div.style.cssText = "background:#eef2f7; padding:8px 15px; border-radius:20px; display:flex; align-items:center; gap:10px; border:1px solid #dce4ec;";

        div.innerHTML = `
            <input type="hidden" name="competencias[]" value="${id}">
            <span style="font-weight:600; color:#333;">${texto}</span>
            <i class="fa-solid fa-xmark" onclick="removerCompetencia(${id})" style="cursor:pointer; color:#888;"></i>
        `;

        container.appendChild(div);
        select.value = ''; // Limpa select
    }

    function removerCompetencia(id) {
        document.getElementById('comp-' + id).remove();
    }
</script>
@endsection
