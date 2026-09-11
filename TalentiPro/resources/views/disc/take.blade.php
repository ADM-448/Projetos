@extends('layouts.app')

@section('title', 'Teste DISC')

@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<style>
.disc-btn-group {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
.disc-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 2px solid transparent;
    font-weight: 800;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s, box-shadow 0.2s, opacity 0.2s;
    opacity: 0.35;
    color: white;
}
.disc-btn:hover { opacity: 0.65; transform: scale(1.08); }
.disc-btn.selected { opacity: 1; box-shadow: 0 0 0 3px rgba(0,0,0,0.18); transform: scale(1.12); }
.disc-btn-1 { background: #e53e3e; }
.disc-btn-2 { background: #d69e2e; }
.disc-btn-3 { background: #3182ce; }
.disc-btn-4 { background: #38a169; }

.form-card.complete { border-color: #68d391 !important; }
.form-card.started { border-color: #feb2b2 !important; }

.disc-option-row {
    display: flex;
    flex-direction: column; /* Coloca o texto em cima e botões embaixo sempre */
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 14px 12px;
    background: #fafbfc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    gap: 12px;
}

@media (max-width: 640px) {
    .disc-option-row {
        /* No celular continua em coluna, mas as coisas esticam */
    }
    .disc-btn-group {
        width: 100%;
        justify-content: center;
        gap: 10px;
    }
    .disc-btn {
        flex: 1;
        height: 40px;
    }
    .disc-card-padding {
        padding: 15px !important;
    }
    .disc-submit-btn-wrap {
        justify-content: center !important;
    }
    .disc-submit-btn-wrap button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div style="margin-bottom: 28px;">
    <div style="text-align: center; max-width: 600px; margin: 0 auto; padding: 0 10px;">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #2d3748; margin-bottom: 8px; text-align: center;"><i class="fa-solid fa-puzzle-piece" style="color: #6a4c7c;"></i> Avaliação Comportamental DISC</h1>
        <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5;">
            Para cada bloco abaixo, distribua as notas <strong>1, 2, 3 e 4</strong> para as opções. <br>
            A nota <strong style="color:#38a169;">4</strong> é a que <strong>mais descreve</strong> você, e a nota <strong style="color:#e53e3e;">1</strong> é a que <strong>menos descreve</strong> você.
        </p>
        <div style="display:flex; gap:10px; margin-top:10px; align-items:center; justify-content:center; flex-wrap: wrap;">
            <span style="display:flex;align-items:center;gap:5px;font-size:0.82rem;font-weight:700;color:#e53e3e;"><span style="background:#e53e3e;color:white;border-radius:5px;width:22px;height:22px;display:flex;align-items:center;justify-content:center;">1</span> Menos descreve</span>
            <span style="display:flex;align-items:center;gap:5px;font-size:0.82rem;font-weight:700;color:#d69e2e;"><span style="background:#d69e2e;color:white;border-radius:5px;width:22px;height:22px;display:flex;align-items:center;justify-content:center;">2</span></span>
            <span style="display:flex;align-items:center;gap:5px;font-size:0.82rem;font-weight:700;color:#3182ce;"><span style="background:#3182ce;color:white;border-radius:5px;width:22px;height:22px;display:flex;align-items:center;justify-content:center;">3</span></span>
            <span style="display:flex;align-items:center;gap:5px;font-size:0.82rem;font-weight:700;color:#38a169;"><span style="background:#38a169;color:white;border-radius:5px;width:22px;height:22px;display:flex;align-items:center;justify-content:center;">4</span> Mais descreve</span>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<form action="{{ route('disc.store') }}" method="POST" id="disc-form">
    @csrf
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        @foreach($questions as $question)
        <div class="form-card disc-card-padding" id="block-{{ $question->id }}" style="padding: 22px; border-radius: 12px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 2px solid #f0f2f5; transition: border-color 0.3s;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">{{ $question->title }}</h3>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($question->options as $option)
                <div class="disc-option-row">
                    <span style="color: #4a5568; font-weight: 600; font-size: 0.92rem;">{{ $option->text }}</span>
                    
                    {{-- Hidden input stores the selected value --}}
                    <input type="hidden" name="responses[{{ $question->id }}][{{ $option->id }}]" id="inp-{{ $option->id }}" value="">
                    
                    <div class="disc-btn-group">
                        @foreach([1 => 'disc-btn-1', 2 => 'disc-btn-2', 3 => 'disc-btn-3', 4 => 'disc-btn-4'] as $num => $cls)
                        <button type="button"
                                class="disc-btn {{ $cls }}"
                                id="btn-{{ $option->id }}-{{ $num }}"
                                onclick="discSelect({{ $question->id }}, {{ $option->id }}, {{ $num }})">
                            {{ $num }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <p id="error-{{ $question->id }}" style="display:none; color: #e53e3e; font-size: 0.82rem; font-weight: 700; margin-top: 12px; background: #fff5f5; padding: 8px 12px; border-radius: 6px; border: 1px dashed #feb2b2;">
                <i class="fa-solid fa-triangle-exclamation"></i> Preencha todas as 4 opções com notas diferentes.
            </p>
        </div>
        @endforeach
    </div>

    <div class="disc-submit-btn-wrap" style="display: flex; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
        <button type="submit"
                id="disc-submit-btn"
                class="btn-primary"
                disabled
                style="padding: 15px 40px; font-size: 1.1rem; border-radius: 10px; font-weight: bold; display: flex; align-items: center; gap: 10px; opacity: 0.5; cursor: not-allowed; filter: grayscale(1); transition: all 0.3s;">
            <i class="fa-solid fa-paper-plane"></i> Finalizar Teste DISC
        </button>
    </div>
</form>

<script>
// Map: blockId -> { optionId: selectedValue }
const blockData = {
    @foreach($questions as $question)
    {{ $question->id }}: {
        @foreach($question->options as $option)
        {{ $option->id }}: 0,
        @endforeach
    },
    @endforeach
};

const blockIds = @json($questions->pluck('id'));
const blockStatus = {};
blockIds.forEach(id => { blockStatus[id] = false; });

function discSelect(blockId, optionId, val) {
    const currentVal = blockData[blockId][optionId];

    // If the same button is clicked again, deselect
    if (currentVal === val) {
        blockData[blockId][optionId] = 0;
        document.getElementById(`inp-${optionId}`).value = "";
        updateButtonsInOption(optionId, 0);
        validateBlock(blockId);
        updateSubmitButton();
        return;
    }

    // Find if this val is already used by another option in the block
    for (const [otherOptId, otherVal] of Object.entries(blockData[blockId])) {
        if (parseInt(otherOptId) !== optionId && otherVal === val) {
            // Clear that option
            blockData[blockId][otherOptId] = 0;
            document.getElementById(`inp-${otherOptId}`).value = "";
            updateButtonsInOption(parseInt(otherOptId), 0);
        }
    }

    // Set new value
    blockData[blockId][optionId] = val;
    document.getElementById(`inp-${optionId}`).value = val;
    updateButtonsInOption(optionId, val);

    validateBlock(blockId);
    updateSubmitButton();
}

function updateButtonsInOption(optionId, selectedVal) {
    [1, 2, 3, 4].forEach(n => {
        const btn = document.getElementById(`btn-${optionId}-${n}`);
        if (!btn) return;
        if (n === selectedVal) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });
}

function validateBlock(blockId) {
    const values = Object.values(blockData[blockId]).filter(v => v !== 0);
    const unique = new Set(values);
    const isValid = values.length === 4 && unique.size === 4;

    blockStatus[blockId] = isValid;

    const card = document.getElementById(`block-${blockId}`);
    const errorEl = document.getElementById(`error-${blockId}`);
    const anySelected = values.length > 0;

    card.classList.remove('complete', 'started');
    if (isValid) {
        card.classList.add('complete');
        errorEl.style.display = 'none';
    } else if (anySelected) {
        card.classList.add('started');
        errorEl.style.display = 'block';
    } else {
        errorEl.style.display = 'none';
    }
}

function updateSubmitButton() {
    const allValid = Object.values(blockStatus).every(v => v === true);
    const btn = document.getElementById('disc-submit-btn');
    if (allValid) {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        btn.style.filter = 'none';
        btn.style.boxShadow = '0 4px 15px rgba(106, 76, 124, 0.4)';
    } else {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
        btn.style.filter = 'grayscale(1)';
        btn.style.boxShadow = 'none';
    }
}
</script>
@endsection
