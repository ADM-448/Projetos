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
    gap: 4px;
    flex-shrink: 0;
    width: auto;
    flex-wrap: nowrap;
}
.disc-btn {
    flex: none;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 2px solid transparent;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s, box-shadow 0.2s, opacity 0.2s;
    opacity: 0.35;
    color: white;
    padding: 0;
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
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    text-align: left;
    padding: 8px;
    background: #fafbfc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    gap: 6px;
}

@media (max-width: 640px) {
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

@keyframes pulseAttention {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.7); }
    50% { transform: scale(1.02); box-shadow: 0 0 0 15px rgba(229, 62, 62, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(229, 62, 62, 0); }
}
</style>

<div style="margin-bottom: 28px;">
    <div style="text-align: center; max-width: 600px; margin: 0 auto; padding: 0 10px;">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #2d3748; margin-bottom: 8px; text-align: center;"><i class="fa-solid fa-puzzle-piece" style="color: #6a4c7c;"></i> Avaliação Comportamental DISC</h1>
        
        <div style="background: linear-gradient(135deg, #e53e3e, #d69e2e); padding: 18px 25px; border-radius: 12px; color: white; box-shadow: 0 8px 20px rgba(229, 62, 62, 0.4); margin-top: 15px; margin-bottom: 10px; animation: pulseAttention 2.5s infinite;">
            <p style="font-size: 1.25rem; font-weight: 900; line-height: 1.3; margin: 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.3); text-transform: uppercase;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> Atenção: Não repita as notas!
            </p>
            <p style="font-size: 1.05rem; font-weight: 600; line-height: 1.5; margin: 10px 0 0 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                Em cada bloco, você deve distribuir obrigatoriamente as notas <strong>1, 2, 3 e 4</strong>.<br>
                <span style="display:inline-block; margin-top:5px;">A nota <strong style="background: white; color: #38a169; padding: 2px 8px; border-radius: 6px; font-size: 1.1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">4</strong> é a que <strong>MAIS DESCREVE</strong> você.</span><br>
                <span style="display:inline-block; margin-top:5px;">A nota <strong style="background: white; color: #e53e3e; padding: 2px 8px; border-radius: 6px; font-size: 1.1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">1</strong> é a que <strong>MENOS DESCREVE</strong> você.</span>
            </p>
        </div>
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
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr)); gap: 20px;">
        @foreach($questions as $question)
        <div class="form-card disc-card-padding" id="block-{{ $question->id }}" style="padding: 22px; border-radius: 12px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 2px solid #f0f2f5; transition: border-color 0.3s;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: #2d3748; margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 10px;">{{ $question->title }}</h3>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($question->options as $option)
                <div class="disc-option-row" style="flex-wrap: wrap;">
                    <span style="color: #4a5568; font-weight: 600; font-size: 0.95rem; flex: 1; text-align: left; padding-right: 6px; line-height: 1.3; min-width: 120px;">{{ $option->text }}</span>
                    
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
