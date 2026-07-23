@extends('layouts.admin')

@section('title', isset($competencia) ? 'Editar Competência' : 'Nova Competência')

@section('content')
<div class="header-actions">
    <h1 class="page-title">{{ isset($competencia) ? 'EDITAR COMPETÊNCIA' : 'NOVA COMPETÊNCIA' }}</h1>
    <a href="{{ route('competencias.index') }}" class="btn-primary" style="background:#95a5a6;">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card-container" style="background:white; padding:30px; border-radius:10px;">
    <form action="{{ isset($competencia) ? route('competencias.update', $competencia->id) : route('competencias.store') }}" method="POST">
        @csrf
        @if(isset($competencia)) @method('PUT') @endif

        {{-- Dados Principais --}}
        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; display:block; margin-bottom:5px;">Nome do Pacote de Competência *</label>
            <input type="text" name="nome" value="{{ $competencia->nome ?? old('nome') }}" placeholder="Ex: Liderança, Comunicação, Python..." required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
        </div>

        <div style="margin-bottom:20px;">
            <label style="font-weight:bold; display:block; margin-bottom:5px;">Descrição</label>
            <textarea name="descricao" rows="2" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">{{ $competencia->descricao ?? old('descricao') }}</textarea>
        </div>

        {{-- Lista de Skills --}}
        <div style="margin-top:30px; border-top:1px solid #eee; padding-top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="color:#6a4c7c; margin:0;">Critérios / Skills</h3>
                <button type="button" id="btnAddSkill" style="background:#3b5998; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-plus"></i> Adicionar Critério
                </button>
            </div>

            <div id="skillsContainer">
                @if(isset($competencia) && $competencia->skills->count() > 0)
                    {{-- Loop para EDIÇÃO (Carrega existentes) --}}
                    @foreach($competencia->skills as $index => $skill)
                    <div class="skill-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                        <input type="text" name="skills[{{ $index }}][nome]" value="{{ $skill->nome }}" class="input-skill" style="flex:1; padding:10px; border:1px solid #ccc; border-radius:5px;" placeholder="Nome da Skill" required>
                        <input type="text" name="skills[{{ $index }}][descricao]" value="{{ $skill->descricao }}" class="input-skill" style="flex:2; padding:10px; border:1px solid #ccc; border-radius:5px;" placeholder="Descrição (Opcional)">
                        <button type="button" class="btn-remove" style="background:#e74c3c; color:white; border:none; width:35px; height:35px; border-radius:5px; cursor:pointer;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    @endforeach
                @else
                    {{-- Linha padrão para CADASTRO (Vazio) --}}
                    <div class="skill-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                        <input type="text" name="skills[0][nome]" class="input-skill" style="flex:1; padding:10px; border:1px solid #ccc; border-radius:5px;" placeholder="Nome da Skill (Ex: Escuta Ativa)" required>
                        <input type="text" name="skills[0][descricao]" class="input-skill" style="flex:2; padding:10px; border:1px solid #ccc; border-radius:5px;" placeholder="Descrição (Opcional)">
                        <button type="button" class="btn-remove" style="background:#e74c3c; color:white; border:none; width:35px; height:35px; border-radius:5px; cursor:pointer;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div style="margin-top:30px; text-align:right;">
            <button type="submit" style="background:#6a4c7c; color:white; padding:12px 30px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
                <i class="fa-solid fa-check"></i> Salvar Tudo
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa o contador. Se tiver skills no banco, pega a contagem. Se não, começa do 1 (porque o 0 já está na tela).
        let skillIndex = {{ (isset($competencia) && $competencia->skills->count() > 0) ? $competencia->skills->count() : 1 }};

        // Função para Adicionar Nova Linha
        document.getElementById('btnAddSkill').addEventListener('click', function() {
            const container = document.getElementById('skillsContainer');
            const div = document.createElement('div');
            div.className = 'skill-row';
            div.style.cssText = 'display:flex; gap:10px; margin-bottom:10px; align-items:center;';

            div.innerHTML = `
                <input type="text" name="skills[${skillIndex}][nome]" class="input-skill" style="flex:1; padding:10px; border:1px solid #ccc; border-radius:5px;" placeholder="Nome da Skill" required>
                <input type="text" name="skills[${skillIndex}][descricao]" class="input-skill" style="flex:2; padding:10px; border:1px solid #ccc; border-radius:5px;" placeholder="Descrição (Opcional)">
                <button type="button" class="btn-remove" style="background:#e74c3c; color:white; border:none; width:35px; height:35px; border-radius:5px; cursor:pointer;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            `;

            container.appendChild(div);
            skillIndex++;
        });

        // Delegação de Eventos para o Botão Remover (funciona inclusive para novos elementos)
        document.getElementById('skillsContainer').addEventListener('click', function(e) {
            // Verifica se clicou no botão ou no ícone dentro do botão
            if (e.target.classList.contains('btn-remove') || e.target.closest('.btn-remove')) {
                const row = e.target.closest('.skill-row');
                // Impede remover se for a única linha (opcional, mas bom pra UX)
                if(document.querySelectorAll('.skill-row').length > 1) {
                    row.remove();
                } else {
                    alert('Você precisa ter pelo menos um critério.');
                }
            }
        });
    });
</script>
@endpush
