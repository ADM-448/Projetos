@extends('layouts.app')

@section('title', 'Publicar Oportunidade - IntalenTI Vagas')

@push('styles')
<style>
    .vaga-form-shell {
        max-width: 1040px;
        margin: 0 auto;
    }
    .pl-section-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 28px 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .pl-section-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--pl-dark);
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid var(--pl-border);
        font-size: 0.9rem;
        padding: 9px 13px;
        color: var(--pl-dark);
        background-color: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--pl-green);
        box-shadow: 0 0 0 3px rgba(0, 170, 69, 0.15);
        outline: none;
    }
    .pl-item-box {
        background: #fafafa;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-md);
        padding: 18px;
        margin-bottom: 14px;
        transition: all 0.15s ease;
    }
    .pl-item-box:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .pl-btn-add {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--pl-green);
        background: var(--pl-green-soft);
        border: 1px solid transparent;
        padding: 6px 14px;
        border-radius: var(--pl-radius-full);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .pl-btn-add:hover {
        background: #bbf7d0;
        color: #008737;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4 vaga-form-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="pl-portal-badge">
                    <i class="fa-solid fa-building me-1"></i> Área da Empresa
                </span>
                <span class="badge bg-light text-muted border">Vagas</span>
            </div>
            <h2 class="h3 fw-bold mb-1" style="color: var(--pl-dark); letter-spacing: -0.02em;">Publicar Oportunidade</h2>
            <p class="text-muted small mb-0">Preencha as informações da vaga para atrair os melhores talentos de TI.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-pl-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Painel
        </a>
    </div>

    <form method="POST" action="{{ route('vagas.store') }}">
        @csrf

        <div class="pl-section-card">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-briefcase"></i>
                </span>
                <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Informações Principais da Vaga</h5>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="titulo" class="form-label">Título da Vaga <span class="text-danger">*</span></label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}" class="form-control @error('titulo') is-invalid @enderror" placeholder="Ex: Desenvolvedor PHP / Laravel Pleno" required>
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="tipo_contrato" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                    <select id="tipo_contrato" name="tipo_contrato" class="form-select @error('tipo_contrato') is-invalid @enderror" required>
                        <option value="">Selecione...</option>
                        @foreach($tiposContrato as $tc)
                            <option value="{{ $tc }}" {{ old('tipo_contrato') == $tc ? 'selected' : '' }}>{{ $tc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="modalidade" class="form-label">Modalidade de Trabalho <span class="text-danger">*</span></label>
                    <select id="modalidade" name="modalidade" class="form-select @error('modalidade') is-invalid @enderror" required>
                        @foreach($modalidades as $mod)
                            <option value="{{ $mod }}" {{ old('modalidade') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="ramo_atividade" class="form-label">Área / Ramo de Atuação <span class="text-danger">*</span></label>
                    <select id="ramo_atividade" name="ramo_atividade" class="form-select @error('ramo_atividade') is-invalid @enderror" required>
                        <option value="">Selecione...</option>
                        @foreach($ramos as $ramo)
                            <option value="{{ $ramo }}" {{ old('ramo_atividade') == $ramo ? 'selected' : '' }}>{{ $ramo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="localizacao" class="form-label">Cidade e Estado <span class="text-danger">*</span></label>
                    <input type="text" id="localizacao" name="localizacao" value="{{ old('localizacao', 'Maringá, PR') }}" class="form-control @error('localizacao') is-invalid @enderror" placeholder="Ex: Maringá, PR" required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="cep" class="form-label">CEP do Local de Trabalho</label>
                    <input type="text" id="cep" name="cep" value="{{ old('cep') }}" class="form-control" placeholder="87000-000">
                    <small class="text-muted" style="font-size: 0.78rem;">Utilizado para calcular distância de candidatos</small>
                </div>

                <div class="col-12 col-md-4">
                    <label for="faixa_salarial_min" class="form-label">Salário Mínimo (R$)</label>
                    <input type="number" step="0.01" id="faixa_salarial_min" name="faixa_salarial_min" value="{{ old('faixa_salarial_min') }}" class="form-control" placeholder="Ex: 3000.00">
                </div>

                <div class="col-12 col-md-4">
                    <label for="faixa_salarial_max" class="form-label">Salário Máximo (R$)</label>
                    <input type="number" step="0.01" id="faixa_salarial_max" name="faixa_salarial_max" value="{{ old('faixa_salarial_max') }}" class="form-control @error('faixa_salarial_max') is-invalid @enderror" placeholder="Ex: 4500.00">
                    @error('faixa_salarial_max')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="d-flex gap-4 pt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="salario_a_combinar" name="salario_a_combinar" value="1" {{ old('salario_a_combinar') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="salario_a_combinar">Salário a Combinar</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confidencial" name="confidencial" value="1" {{ old('confidencial') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="confidencial">Ocultar Nome da Empresa (Vaga Confidencial)</label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="descricao" class="form-label">Descrição Detalhada da Vaga <span class="text-danger">*</span></label>
                    <textarea id="descricao" name="descricao" rows="5" class="form-control @error('descricao') is-invalid @enderror" placeholder="Descreva as responsabilidades, ambiente e objetivo do cargo..." required>{{ old('descricao') }}</textarea>
                </div>

                <div class="col-12 col-md-6">
                    <label for="requisitos" class="form-label">Requisitos e Conhecimentos</label>
                    <textarea id="requisitos" name="requisitos" rows="4" class="form-control" placeholder="Liste as habilidades técnicas, formação e tempo de experiência desejados...">{{ old('requisitos') }}</textarea>
                </div>

                <div class="col-12 col-md-6">
                    <label for="beneficios" class="form-label">Benefícios Oferecidos</label>
                    <textarea id="beneficios" name="beneficios" rows="4" class="form-control" placeholder="Vale alimentação, plano de saúde, flexibilidade, etc...">{{ old('beneficios') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Questionário e Perguntas Eliminatórias -->
        <div class="pl-section-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--pl-dark);">
                        <i class="fa-solid fa-circle-question me-1" style="color: var(--pl-green);"></i> Questionário de Triagem
                    </h5>
                    <p class="text-muted small mb-0">Adicione perguntas objetivas (Sim/Não) para os candidatos responderem no ato da inscrição.</p>
                </div>
                <button type="button" class="pl-btn-add" id="btnAddPergunta">
                    <i class="fa-solid fa-plus"></i> Nova Pergunta
                </button>
            </div>

            <div id="containerPerguntas">
                <div class="text-muted small fst-italic p-3 bg-light rounded-3 text-center border" id="emptyPerguntas">Nenhuma pergunta cadastrada. (Opcional)</div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('dashboard') }}" class="btn btn-pl-secondary px-4">Cancelar</a>
            <button type="submit" class="btn btn-pl-primary px-4 py-2">
                <i class="fa-solid fa-check-circle me-1"></i> Publicar Vaga
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let pIndex = 0;
        const btnAdd = document.getElementById('btnAddPergunta');
        const container = document.getElementById('containerPerguntas');
        const emptyMsg = document.getElementById('emptyPerguntas');

        if (btnAdd) {
            btnAdd.addEventListener('click', function () {
                if (emptyMsg) emptyMsg.remove();
                const div = document.createElement('div');
                div.className = 'pl-item-box pergunta-card';
                div.id = `pergunta_${pIndex}`;
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small" style="color: var(--pl-dark);">Pergunta #${pIndex + 1}</strong>
                        <button type="button" class="btn btn-sm text-danger p-0 fw-semibold" onclick="document.getElementById('pergunta_${pIndex}').remove()">
                            <i class="fa-solid fa-trash-can me-1"></i> Remover
                        </button>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="perguntas[${pIndex}][texto]" class="form-control" placeholder="Ex: Você possui experiência com Laravel e APIs REST?" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="perguntas[${pIndex}][eliminatoria]" value="1" id="elim_${pIndex}">
                        <label class="form-check-label small fw-semibold text-danger" for="elim_${pIndex}">
                            Marcar como Pergunta Eliminatória (Exige 'Sim' obrigatório)
                        </label>
                    </div>
                `;
                container.appendChild(div);
                pIndex++;
            });
        }
    });
</script>
@endpush
