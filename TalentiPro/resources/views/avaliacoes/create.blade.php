@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', isset($avaliacao) ? 'Editar Avaliação' : 'Criar Avaliação')

@section('content')

@push('styles')
<style>
    /* Estilos base inspirados no HTML modelo e mantendo compatibilidade */
    .form-card { background: white; border-radius: 10px; padding: 40px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0; max-width: 900px; margin: 0 auto; }
    .form-group { margin-bottom: 20px; }
    label { font-weight: 600; color: #444; margin-bottom: 8px; display: block; font-size: 14px; }
    
    input[type="text"], input[type="date"], select, textarea { 
        width: 100%; padding: 10px 12px; border-radius: 5px; border: 1px solid #ccc; 
        font-size: 14px; outline: none; background-color: #f9f9f9; 
    }
    textarea { resize: vertical; height: 80px; }
    
    .form-row { display: flex; gap: 20px; }
    .col-half { flex: 1; }
    
    .form-footer { margin-top: 30px; text-align: center; }
    
    .btn-save { background-color: #3b5998; color: white; padding: 12px 60px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition:0.3s; }
    .btn-save:hover { background-color: #2d4373; }
    .btn-back { background-color: #3b5998; color: white; padding: 8px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 14px; }

    /* ESTILO DAS TAGS */
    .tag-input-container { 
        border: 1px solid #ccc; border-radius: 5px; padding: 5px; background-color: #f9f9f9; 
        display: flex; flex-wrap: wrap; align-items: center; gap: 5px; min-height: 42px; width: 100%; cursor: text;
    }
    .tag-chip { 
        background-color: #3b5998; color: white; padding: 4px 10px; border-radius: 15px; 
        font-size: 12px; display: flex; align-items: center; gap: 5px;
    }
    .tag-chip i { cursor: pointer; font-size: 10px; opacity: 0.8; transition: 0.2s; }
    .tag-chip i:hover { opacity: 1; color: #ffcccc; }
    
    .select-invisible { 
        border: none; background: transparent; outline: none; flex-grow: 1; min-width: 150px; 
        font-size: 13px; color: #333; margin-left: 5px; 
    }
</style>
@endpush

<div class="page-content">
    <div class="header-actions">
        <h1 class="page-title">{{ isset($avaliacao) ? 'Editar Avaliação' : 'Criar Avaliação' }}</h1>
        <a href="{{ route('avaliacoes.index') }}" class="btn-back">Voltar</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="color:red; margin-bottom:15px; text-align:center;">
            <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <form action="{{ isset($avaliacao) ? route('avaliacoes.update', $avaliacao->id) : route('avaliacoes.store') }}" method="POST" class="form-card">
        @csrf
        @if(isset($avaliacao)) @method('PUT') @endif

        <div class="form-group">
            <label>Nome da Avaliação</label>
            <input type="text" name="titulo" value="{{ $avaliacao->titulo ?? old('titulo') }}" required>
        </div>

        <div class="form-group">
            <label>Descrição</label>
            <textarea name="descricao">{{ $avaliacao->descricao ?? old('descricao') }}</textarea>
        </div>

        {{-- BOX EXPLICATIVO DO FILTRO --}}
        <div class="form-group" style="background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; border-radius: 4px; font-size: 0.9em; color: #0d47a1; display:flex; gap:10px; align-items:center;">
            <i class="fa-solid fa-circle-info" style="font-size:1.2rem;"></i>
            <div>
                <strong>Como funciona o filtro?</strong><br>
                Ao selecionar Departamentos, Cargos ou Senioridade, o sistema filtrará os colaboradores que correspondam a esses critérios. <br>
                <em>(Departamentos OU Cargos) E Senioridade E Data Admissão (Posterior a...)</em>
            </div>
        </div>

        <div class="form-group">
            <label>Escolha os departamentos a serem avaliados</label>
            <div class="tag-input-container" onclick="document.getElementById('sel_dept').focus()">
                <div id="tags_dept" style="display:flex; gap:5px; flex-wrap:wrap;"></div>
                <select id="sel_dept" class="select-invisible" onchange="addTag(this, 'dept')">
                    <option value="">+ Adicionar Departamento...</option>
                    @foreach($departamentos as $dep)
                        <option value="{{ $dep->id }}">{{ $dep->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div id="inputs_dept"></div>
        </div>

        <div class="form-group">
            <label>Escolha os cargos a serem avaliados</label>
            <div class="tag-input-container" onclick="document.getElementById('sel_cargo').focus()">
                <div id="tags_cargo" style="display:flex; gap:5px; flex-wrap:wrap;"></div>
                <select id="sel_cargo" class="select-invisible" onchange="addTag(this, 'cargo')">
                    <option value="">+ Adicionar Cargo...</option>
                    @foreach($cargos as $cargo)
                        <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div id="inputs_cargo"></div>
        </div>

        <div class="form-row">
            <div class="form-group col-half">
                <label>Selecione a senioridade</label>
                {{-- Adicionado onchange=updateFilters() para gatilho imediato se o listener falhar --}}
                <select name="senioridade" id="sel_senioridade">
                    <option value="">Todas</option>
                    <option value="estagiario" {{ (isset($avaliacao) && $avaliacao->senioridade == 'estagiario') ? 'selected' : '' }}>Estagiário</option>
                    <option value="junior" {{ (isset($avaliacao) && $avaliacao->senioridade == 'junior') ? 'selected' : '' }}>Júnior</option>
                    <option value="pleno" {{ (isset($avaliacao) && $avaliacao->senioridade == 'pleno') ? 'selected' : '' }}>Pleno</option>
                    <option value="senior" {{ (isset($avaliacao) && $avaliacao->senioridade == 'senior') ? 'selected' : '' }}>Sênior</option>
                    <option value="especialista" {{ (isset($avaliacao) && $avaliacao->senioridade == 'especialista') ? 'selected' : '' }}>Especialista</option>
                    <option value="gerente" {{ (isset($avaliacao) && $avaliacao->senioridade == 'gerente') ? 'selected' : '' }}>Gerente</option>
                </select>
            </div>
            <div class="form-group col-half">
                <label>Selecione a data de admissão (A partir de)</label>
                <input type="date" name="data_admissao" value="{{ isset($avaliacao) ? $avaliacao->data_admissao : '' }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-half">
                <label>Data de Início</label>
                <input type="date" name="data_inicio" id="avaliacaoInicio" value="{{ isset($avaliacao) ? $avaliacao->data_inicio->format('Y-m-d') : '' }}" required>
            </div>
            <div class="form-group col-half">
                <label>Data Término</label>
                <input type="date" name="data_fim" id="avaliacaoFim" value="{{ isset($avaliacao) ? $avaliacao->data_fim->format('Y-m-d') : '' }}" required>
            </div>
        </div>

        <div class="form-group">
            <label>Escolha os AVALIADORES</label>
            <div class="tag-input-container" onclick="document.getElementById('sel_avaliador').focus()">
                <div id="tags_avaliador" style="display:flex; gap:5px; flex-wrap:wrap;"></div>
                <select id="sel_avaliador" class="select-invisible" onchange="addTag(this, 'avaliador')">
                    <option value="">+ Adicionar Avaliador...</option>
                    @foreach($users as $user)
                         @if(in_array($user->perfil_acesso, ['admin', 'master', 'gestor']))
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->cargo->nome ?? 'Sem Cargo' }}</option>
                         @endif
                    @endforeach
                </select>
            </div>
            <div id="inputs_avaliador"></div>
        </div>

        <div class="form-group">
            <label>Escolha os MEDIADORES</label>
            <div class="tag-input-container" onclick="document.getElementById('sel_mediador').focus()">
                <div id="tags_mediador" style="display:flex; gap:5px; flex-wrap:wrap;"></div>
                <select id="sel_mediador" class="select-invisible" onchange="addTag(this, 'mediador')">
                    <option value="">+ Adicionar Mediador...</option>
                    @foreach($mediadoresUsers as $med)
                        <option value="{{ $med->id }}">{{ $med->name }} - {{ $med->cargo->nome ?? 'Sem Cargo' }}</option>
                    @endforeach
                </select>
            </div>
            <div id="inputs_mediador"></div>
        </div>

        <div class="form-group">
            <label>Escolha os AVALIADOS</label>
            <div class="tag-input-container" onclick="document.getElementById('sel_avaliado').focus()">
                <div id="tags_avaliado" style="display:flex; gap:5px; flex-wrap:wrap;"></div>
                <select id="sel_avaliado" class="select-invisible" onchange="addTag(this, 'avaliado')">
                    <option value="">+ Adicionar Colaborador...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->cargo->nome ?? 'Sem Cargo' }}</option>
                    @endforeach
                </select>
            </div>
            <div id="inputs_avaliado"></div>
        </div>

        <div class="form-group">
            <label>Escolha as competências</label>
            <div class="tag-input-container" onclick="document.getElementById('sel_comp').focus()">
                <div id="tags_comp" style="display:flex; gap:5px; flex-wrap:wrap;"></div>
                <select id="sel_comp" class="select-invisible" onchange="addTag(this, 'comp')">
                    <option value="">+ Selecionar Competência...</option>
                    @foreach($competencias as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div id="inputs_comp"></div>
        </div>

        <div class="form-group">
            <label>Escolha a forma de avaliar</label>
            <select name="forma_avaliacao">
                <option value="nota_1_10" {{ (isset($avaliacao) && $avaliacao->forma_avaliacao == 'nota_1_10') ? 'selected' : '' }}>Nota Numérica (1 a 10)</option>
                <option value="estrelas" {{ (isset($avaliacao) && $avaliacao->forma_avaliacao == 'estrelas') ? 'selected' : '' }}>Estrelas (1 a 5 ⭐)</option>
                <option value="conceito" {{ (isset($avaliacao) && $avaliacao->forma_avaliacao == 'conceito') ? 'selected' : '' }}>Conceito (Ruim / Bom / Ótimo)</option>
            </select>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn-save">Salvar</button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
    // --- DADOS PARA FILTROS (Injetados pelo Laravel) ---
    const allUsers = @json($users); // Lista completa de usuários com dept_id, cargo_id, etc.
    const allDepts = @json($departamentos);
    const allCargos = @json($cargos);
    const allMediators = @json($mediadoresUsers); // Lista estrita de Mediadores (RH/Master)

    // --- LÓGICA DAS TAGS ---
    function addTag(selectElement, type) {
        const id = selectElement.value;
        const text = selectElement.options[selectElement.selectedIndex].text;

        if (!id) return;

        if (document.getElementById(`tag_${type}_${id}`)) {
            selectElement.value = "";
            return;
        }

        createTagVisual(type, id, text);
        selectElement.value = "";

        // Se for Departamento ou Cargo, atualiza os filtros das listas
        if (type === 'dept' || type === 'cargo') {
            updateFilters();
        }
    }

    function createTagVisual(type, id, text) {
        const tagContainer = document.getElementById(`tags_${type}`);
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.id = `tag_${type}_${id}`;
        chip.innerHTML = `${text} <i class="fa-solid fa-xmark" onclick="removeTag('${type}', '${id}')"></i>`;
        tagContainer.appendChild(chip);

        const inputContainer = document.getElementById(`inputs_${type}`);
        const input = document.createElement('input');
        input.type = 'hidden';
        
        let arrayName = '';
        if(type === 'dept') arrayName = 'depts[]';
        else if(type === 'cargo') arrayName = 'cargos[]';
        else if(type === 'comp') arrayName = 'comps[]';
        else if(type === 'avaliado') arrayName = 'avaliados[]';
        else if(type === 'avaliador') arrayName = 'gestores[]'; // Mapeado para Avaliadores/Gestores
        else if(type === 'mediador') arrayName = 'mediadores[]'; // Mapeado para Mediadores Reais
        
        input.name = arrayName;
        input.value = id;
        input.id = `input_${type}_${id}`;
        input.classList.add(`input-${type}`); // Classe para facilitar seleção depois
        inputContainer.appendChild(input);
    }

    function removeTag(type, id) {
        const chip = document.getElementById(`tag_${type}_${id}`);
        if(chip) chip.remove();
        const input = document.getElementById(`input_${type}_${id}`);
        if(input) input.remove();

        // Se remover Dept ou Cargo, atualiza filtros
        if (type === 'dept' || type === 'cargo') {
            updateFilters();
        }
    }

    // --- LÓGICA DE FILTRAGEM ---
    function updateFilters() {
        // 1. Coleta IDs selecionados
        const selectedDeptIds = Array.from(document.querySelectorAll('.input-dept')).map(i => i.value);
        const selectedCargoIds = Array.from(document.querySelectorAll('.input-cargo')).map(i => i.value);
        
        // Coleta Senioridade selecionada
        const senioridadeSelect = document.getElementById('sel_senioridade');
        const senioridadeValue = senioridadeSelect ? senioridadeSelect.value.toLowerCase() : '';

        // Coleta Data Admissão
        const admissaoInput = document.querySelector('input[name="data_admissao"]');
        const admissaoValue = admissaoInput ? admissaoInput.value : '';

        const hasDeptFilter = selectedDeptIds.length > 0;
        const hasCargoFilter = selectedCargoIds.length > 0;
        const hasSenioridadeFilter = senioridadeValue && senioridadeValue !== 'todas';
        const hasAdmissaoFilter = admissaoValue !== '';

        // 2. Filtra Avaliados e Avaliadores
        const filterLogic = (user) => {
            // Se nenhum filtro estiver ativo, mostra todos
            if (!hasDeptFilter && !hasCargoFilter && !hasSenioridadeFilter && !hasAdmissaoFilter) {
               // Se não há filtros, queremos mostrar todos em Avaliados?
               // Geralmente sim. Mas o usuário disse que "não consegue editar".
               // Se não há filtros, deve mostrar todos.
               return true;
            }
            
            // Lógica 1: (Departamento OU Cargo)
            let matchesDeptOrCargo = true;
            if (hasDeptFilter || hasCargoFilter) {
                const inDept = hasDeptFilter && (
                    selectedDeptIds.includes(String(user.departamento_id)) ||
                    selectedDeptIds.some(deptId => {
                        const d = allDepts.find(x => String(x.id) === deptId);
                        return d && String(d.gestor_id) === String(user.id);
                    })
                );
                const inCargo = hasCargoFilter && selectedCargoIds.includes(String(user.cargo_id));
                
                if (hasDeptFilter && hasCargoFilter) {
                    matchesDeptOrCargo = inDept || inCargo;
                } else if (hasDeptFilter) {
                    matchesDeptOrCargo = inDept;
                } else {
                    matchesDeptOrCargo = inCargo;
                }
            }

            // Lógica 2: Senioridade (AND)
            let matchesSenioridade = true;
            if (hasSenioridadeFilter) {
                const userCargoName = user.cargo ? user.cargo.nome.toLowerCase() : '';
                matchesSenioridade = userCargoName.includes(senioridadeValue) || 
                                     userCargoName.normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(senioridadeValue);
            }

            // Lógica 3: Admissão (AND)
            let matchesAdmissao = true;
            if (hasAdmissaoFilter) {
                if (user.data_admissao) {
                    matchesAdmissao = user.data_admissao >= admissaoValue;
                } else {
                    matchesAdmissao = false; 
                }
            }

            return matchesDeptOrCargo && matchesSenioridade && matchesAdmissao;
        };
        
        // CORREÇÃO: Vamos filtrar tanto se houver filtros quanto se não houver (mostra tudo)
        const filteredAvaliados = allUsers.filter(filterLogic);
        
        // Avaliadores: SEMPRE MOSTRAR GESTORES/ADMINS/MASTERS
        // NÃO aplicar filterLogic neles (pois Avaliador pode ser de outro setor e Master avalia qualquer um)
        const filteredAvaliadores = allUsers.filter(user => {
            return ['master', 'admin', 'gestor'].includes(user.perfil_acesso);
        });
        
        // Atualiza os selects
        // ATENÇÃO: Se estiver editando e já tiver tags selecionadas, elas não somem do visual (estão em outra div),
        // mas o select de adicionar novas opções será atualizado.
        populateSelect('sel_avaliado', filteredAvaliados, '+ Adicionar Colaborador...');
        populateSelect('sel_avaliador', filteredAvaliadores, '+ Adicionar Avaliador...');
        
        // Mediadores: USAM A LISTA ESTRITA DE MEDIADORES (RH/MASTER)
        // Não filtramos por departamento/cargo do avaliado, pois RH é global
        populateSelect('sel_mediador', allMediators, '+ Adicionar Mediador...');
    }

    function populateSelect(selectId, users, defaultText) {
        const select = document.getElementById(selectId);
        select.innerHTML = `<option value="">${defaultText}</option>`;
        
        users.forEach(user => {
            const cargo = user.cargo ? user.cargo.nome : 'Sem Cargo';
            const option = document.createElement('option');
            option.value = user.id;
            option.text = `${user.name} - ${cargo}`;
            select.appendChild(option);
        });
    }

    // --- RECARREGAR DADOS NA EDIÇÃO ---
    document.addEventListener("DOMContentLoaded", function() {
        // Listener para Senioridade
        const senSelect = document.getElementById('sel_senioridade');
        if(senSelect) {
            senSelect.addEventListener('change', updateFilters);
        }

        // Listener para Admissão
        const admInput = document.querySelector('input[name="data_admissao"]');
        if(admInput) {
            admInput.addEventListener('change', updateFilters);
        }

        @if(isset($avaliacao))
            // Competências
            @foreach($avaliacao->competencias as $c)
                createTagVisual('comp', '{{ $c->id }}', '{!! addslashes($c->nome) !!}');
            @endforeach

            // Recupera GESTORES (Avaliadores)
            @foreach($avaliacao->participantes->where('tipo', 'gestor')->unique('avaliador_id') as $p)
                @if($p->avaliador)
                    createTagVisual('avaliador', '{{ $p->avaliador_id }}', '{!! addslashes($p->avaliador->name) !!} - {!! addslashes($p->avaliador->cargo->nome ?? 'Sem Cargo') !!}');
                @endif
            @endforeach

            // Recupera MEDIADORES
            @foreach($avaliacao->participantes->where('tipo', 'mediador')->unique('avaliador_id') as $p)
                @if($p->avaliador)
                    createTagVisual('mediador', '{{ $p->avaliador_id }}', '{!! addslashes($p->avaliador->name) !!} - {!! addslashes($p->avaliador->cargo->nome ?? 'Sem Cargo') !!}');
                @endif
            @endforeach

            // --- RECUPERAÇÃO DOS FILTROS SALVOS ---
            {{-- Verifica se existe e não é nulo --}}
            @if($avaliacao->filtros)
                @php $filtros = $avaliacao->filtros; @endphp
                
                // Departamentos
                @if(isset($filtros['depts']) && is_array($filtros['depts']))
                    @foreach($filtros['depts'] as $deptId)
                        @if($dept = $departamentos->find($deptId))
                             createTagVisual('dept', '{{ $dept->id }}', '{{ $dept->nome }}');
                        @endif
                    @endforeach
                @endif

                // Cargos
                @if(isset($filtros['cargos']) && is_array($filtros['cargos']))
                    @foreach($filtros['cargos'] as $cargoId)
                        @if($c = $cargos->find($cargoId))
                             createTagVisual('cargo', '{{ $c->id }}', '{{ $c->nome }}');
                        @endif
                    @endforeach
                @endif
            @endif

            // Recupera Avaliados Manuais (SEMPRE recupera, mesmo com filtros, para permitir edição mista)
            @foreach($avaliacao->participantes->unique('avaliado_id') as $p)
                 // Verifica se este usuário JÁ NÃO SERIA incluído pelos filtros visuais carregados acima
                 // Mas como o JS não sabe o que veio do banco vs filtro, melhor carregar visualmente tudo que está no banco
                 // O usuário pode remover se quiser.
                 @if($p->avaliado)
                    createTagVisual('avaliado', '{{ $p->avaliado_id }}', '{!! addslashes($p->avaliado->name) !!} - {!! addslashes($p->avaliado->cargo->nome ?? 'Sem Cargo') !!}');
                 @endif
            @endforeach

            // Força atualização da lista com base nos filtros carregados
            // Pequeno delay para garantir que o DOM renderizou
            setTimeout(updateFilters, 500);
        @endif

        // --- DATE VALIDATION ---
        function validarDatas(startId, endId) {
            const startInput = document.getElementById(startId);
            const endInput = document.getElementById(endId);

            // Create error message element
            let errorMsg = endInput.parentElement.querySelector('.date-error-msg');
            if (!errorMsg) {
                errorMsg = document.createElement('small');
                errorMsg.className = 'date-error-msg';
                errorMsg.style.color = '#e74c3c';
                errorMsg.style.fontSize = '12px';
                errorMsg.style.marginTop = '5px';
                errorMsg.style.display = 'none';
                endInput.parentElement.appendChild(errorMsg);
            }

            function checkDates() {
                const startDate = startInput.value;
                const endDate = endInput.value;

                // Set min attribute
                if (startDate) {
                    endInput.min = startDate;
                }

                // Only validate if both dates are complete (10 chars: YYYY-MM-DD)
                if (startDate && endDate && startDate.length === 10 && endDate.length === 10) {
                    if (endDate < startDate) {
                        errorMsg.textContent = '⚠️ A Data de Término não pode ser anterior à Data de Início.';
                        errorMsg.style.display = 'block';
                        endInput.style.borderColor = '#e74c3c';
                    } else {
                        errorMsg.style.display = 'none';
                        endInput.style.borderColor = '#ccc';
                    }
                } else {
                    // Hide error if dates are incomplete
                    errorMsg.style.display = 'none';
                    endInput.style.borderColor = '#ccc';
                }
            }

            startInput.addEventListener('change', checkDates);
            endInput.addEventListener('input', checkDates);
            endInput.addEventListener('change', checkDates);
        }

        // Apply validation
        validarDatas('avaliacaoInicio', 'avaliacaoFim');
    });
</script>
@endpush