<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Avaliação DISC - {{ $empresa->nome_fantasia ?? 'Empresa' }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #2d3748;
        }

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
            transition: all 0.2s;
            opacity: 0.35;
            color: white;
            padding: 0;
        }

        .disc-btn:hover {
            opacity: 0.65;
            transform: scale(1.05);
        }

        .disc-btn.selected {
            opacity: 1;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.18);
            transform: scale(1.05);
        }

        .disc-btn-1 {
            background: #e53e3e;
        }

        .disc-btn-2 {
            background: #d69e2e;
        }

        .disc-btn-3 {
            background: #3182ce;
        }

        .disc-btn-4 {
            background: #38a169;
        }

        .disc-option-row {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            text-align: left;
            padding: 8px;
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            gap: 6px;
            margin-bottom: 8px;
        }

        .form-card {
            border: 2px solid #f0f2f5;
            transition: border-color 0.3s;
        }

        .form-card.complete {
            border-color: #68d391 !important;
        }

        .form-card.started {
            border-color: #feb2b2 !important;
        }

        .btn-purple {
            background: #6a4c7c;
            color: white;
            font-weight: bold;
            transition: all 0.2s;
        }

        .btn-purple:hover {
            background: #5b3f6c;
            color: white;
        }

        @keyframes pulseAttention {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.7);
            }

            50% {
                transform: scale(1.02);
                box-shadow: 0 0 0 15px rgba(229, 62, 62, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(229, 62, 62, 0);
            }
        }
    </style>
</head>

<body>

    <div class="container py-4 py-md-5" style="max-width: 800px;">
        <div class="text-center mb-4">
            <h1 class="fw-bolder text-dark mb-1">Avaliação Comportamental</h1>
            <p class="text-secondary fs-5 mb-0">{{ $empresa->nome_fantasia ?? '' }}</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger shadow-sm">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('disc.storeExternal', $empresa->id) }}" method="POST" id="disc-form">
            @csrf

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title fw-bold border-bottom pb-2 mb-3">Seus Dados</h4>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nome Completo *</label>
                        <input type="text" class="form-control form-control-lg" name="respondent_name"
                            value="{{ old('respondent_name') }}" required placeholder="Digite seu nome completo">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-secondary">E-mail *</label>
                        <input type="email" class="form-control form-control-lg" name="respondent_email"
                            value="{{ old('respondent_email') }}" required
                            placeholder="Digite seu e-mail corporativo ou pessoal">
                    </div>
                </div>
            </div>

            <div
                style="background: linear-gradient(135deg, #e53e3e, #d69e2e); padding: 20px 25px; border-radius: 12px; color: white; box-shadow: 0 8px 20px rgba(229, 62, 62, 0.4); margin-top: 25px; margin-bottom: 25px; animation: pulseAttention 2.5s infinite; text-align: center;">
                <p
                    style="font-size: 1.3rem; font-weight: 900; line-height: 1.3; margin: 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.3); text-transform: uppercase;">
                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> Atenção: Leia
                    atentamente as instruções para preenchimento do teste e Não repita as
                    notas!
                </p>
                <p
                    style="font-size: 1.05rem; font-weight: 600; line-height: 1.5; margin: 12px 0 0 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                    Em cada bloco abaixo, você deve distribuir obrigatoriamente as notas <strong>1, 2, 3 e
                        4</strong>.<br>
                    <span style="display:inline-block; margin-top:8px;">A nota <strong
                            style="background: white; color: #38a169; padding: 2px 8px; border-radius: 6px; font-size: 1.1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">4</strong>
                        é a que <strong>MAIS DESCREVE</strong> você.</span><br>
                    <span style="display:inline-block; margin-top:5px;">A nota <strong
                            style="background: white; color: #e53e3e; padding: 2px 8px; border-radius: 6px; font-size: 1.1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">1</strong>
                        é a que <strong>MENOS DESCREVE</strong> você.</span>
                    <span style="display:block; margin-top:12px; font-size: 0.95rem;">
                        Não existem respostas certas ou erradas.
                    </span>
                    <span style="display:block; margin-top:4px; font-size: 0.95rem;">
                        Seja você mesmo(a), a sua sinceridade vai fazer toda a diferença ao final do processo.
                    </span>
                </p>
            </div>

            <div class="row g-4">
                @foreach($questions as $question)
                    <div class="col-12 col-md-6">
                        <div class="card form-card h-100 shadow-sm border-0" id="block-{{ $question->id }}">
                            <div class="card-body p-4">
                                <h5 class="fw-bolder mb-3 text-dark">{{ $question->title }}</h5>

                                <div class="d-flex flex-column">
                                    @foreach($question->options as $option)
                                        <div class="disc-option-row">
                                            <span class="fw-bold text-secondary flex-grow-1 text-start"
                                                style="font-size: 0.95rem; padding-right: 6px; line-height: 1.3; min-width: 120px;">{{ $option->text }}</span>

                                            <input type="hidden" name="responses[{{ $question->id }}][{{ $option->id }}]"
                                                id="inp-{{ $option->id }}" value="">

                                            <div class="disc-btn-group">
                                                @foreach([1 => 'disc-btn-1', 2 => 'disc-btn-2', 3 => 'disc-btn-3', 4 => 'disc-btn-4'] as $num => $cls)
                                                    <button type="button" class="disc-btn {{ $cls }}"
                                                        id="btn-{{ $option->id }}-{{ $num }}"
                                                        onclick="discSelect({{ $question->id }}, {{ $option->id }}, {{ $num }})">
                                                        {{ $num }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div id="error-{{ $question->id }}" class="alert alert-danger mt-3 mb-0 py-2 px-3 fw-bold"
                                    style="display:none; font-size: 0.85rem;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Preencha todas as opções sem repetir.
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-grid mt-5">
                <button type="submit" id="disc-submit-btn" class="btn btn-purple btn-lg py-3 shadow-sm" disabled>
                    <i class="fa-solid fa-paper-plane me-2"></i> Finalizar e Enviar
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
            if (currentVal === val) {
                blockData[blockId][optionId] = 0;
                document.getElementById(`inp-${optionId}`).value = "";
                updateButtonsInOption(optionId, 0);
                validateBlock(blockId);
                updateSubmitButton();
                return;
            }
            for (const [otherOptId, otherVal] of Object.entries(blockData[blockId])) {
                if (parseInt(otherOptId) !== optionId && otherVal === val) {
                    blockData[blockId][otherOptId] = 0;
                    document.getElementById(`inp-${otherOptId}`).value = "";
                    updateButtonsInOption(parseInt(otherOptId), 0);
                }
            }
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
            card.classList.remove('complete', 'started');
            if (isValid) {
                card.classList.add('complete');
                errorEl.style.display = 'none';
            } else if (values.length > 0) {
                card.classList.add('started');
                errorEl.style.display = 'block';
            } else {
                errorEl.style.display = 'none';
            }
        }

        function updateSubmitButton() {
            const allValid = Object.values(blockStatus).every(v => v === true);
            const nameInput = document.querySelector('input[name="respondent_name"]').value.trim();
            const emailInput = document.querySelector('input[name="respondent_email"]').value.trim();
            const btn = document.getElementById('disc-submit-btn');
            if (allValid && nameInput && emailInput) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }

        document.querySelector('input[name="respondent_name"]').addEventListener('input', updateSubmitButton);
        document.querySelector('input[name="respondent_email"]').addEventListener('input', updateSubmitButton);
    </script>

</body>

</html>