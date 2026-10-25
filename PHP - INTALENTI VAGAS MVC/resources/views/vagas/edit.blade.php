@extends('layouts.app')

@section('title', 'Editar Vaga - ' . $vaga->titulo)

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
            <h2 class="h3 fw-bold mb-1" style="color: var(--pl-dark); letter-spacing: -0.02em;">Editar Vaga</h2>
            <p class="text-muted small mb-0">Atualize as informações e requisitos da vaga <strong>"{{ $vaga->titulo }}"</strong>.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-pl-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Painel
        </a>
    </div>

    <form method="POST" action="{{ route('vagas.update', $vaga->id) }}">
        @csrf
        @method('PUT')

        <div class="pl-section-card">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Informações da Vaga</h5>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="titulo" class="form-label">Título da Vaga <span class="text-danger">*</span></label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $vaga->titulo) }}" class="form-control @error('titulo') is-invalid @enderror" required>
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="tipo_contrato" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                    <select id="tipo_contrato" name="tipo_contrato" class="form-select" required>
                        @foreach($tiposContrato as $tc)
                            <option value="{{ $tc }}" {{ old('tipo_contrato', $vaga->tipo_contrato) == $tc ? 'selected' : '' }}>{{ $tc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="modalidade" class="form-label">Modalidade <span class="text-danger">*</span></label>
                    <select id="modalidade" name="modalidade" class="form-select" required>
                        @foreach($modalidades as $mod)
                            <option value="{{ $mod }}" {{ old('modalidade', $vaga->modalidade) == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="ramo_atividade" class="form-label">Ramo de Atuação <span class="text-danger">*</span></label>
                    <select id="ramo_atividade" name="ramo_atividade" class="form-select" required>
                        @foreach($ramos as $ramo)
                            <option value="{{ $ramo }}" {{ old('ramo_atividade', $vaga->ramo_atividade) == $ramo ? 'selected' : '' }}>{{ $ramo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="localizacao" class="form-label">Localização <span class="text-danger">*</span></label>
                    <input type="text" id="localizacao" name="localizacao" value="{{ old('localizacao', $vaga->localizacao) }}" class="form-control" required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" id="cep" name="cep" value="{{ old('cep', $vaga->cep) }}" class="form-control">
                </div>

                <div class="col-12 col-md-4">
                    <label for="faixa_salarial_min" class="form-label">Salário Mínimo (R$)</label>
                    <input type="number" step="0.01" id="faixa_salarial_min" name="faixa_salarial_min" value="{{ old('faixa_salarial_min', $vaga->faixa_salarial_min) }}" class="form-control">
                </div>

                <div class="col-12 col-md-4">
                    <label for="faixa_salarial_max" class="form-label">Salário Máximo (R$)</label>
                    <input type="number" step="0.01" id="faixa_salarial_max" name="faixa_salarial_max" value="{{ old('faixa_salarial_max', $vaga->faixa_salarial_max) }}" class="form-control">
                </div>

                <div class="col-12">
                    <div class="d-flex gap-4 pt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="salario_a_combinar" name="salario_a_combinar" value="1" {{ old('salario_a_combinar', $vaga->salario_a_combinar) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="salario_a_combinar">Salário a Combinar</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confidencial" name="confidencial" value="1" {{ old('confidencial', $vaga->confidencial) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="confidencial">Ocultar Nome da Empresa</label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                    <textarea id="descricao" name="descricao" rows="5" class="form-control" required>{{ old('descricao', $vaga->descricao) }}</textarea>
                </div>

                <div class="col-12 col-md-6">
                    <label for="requisitos" class="form-label">Requisitos</label>
                    <textarea id="requisitos" name="requisitos" rows="4" class="form-control">{{ old('requisitos', $vaga->requisitos) }}</textarea>
                </div>

                <div class="col-12 col-md-6">
                    <label for="beneficios" class="form-label">Benefícios</label>
                    <textarea id="beneficios" name="beneficios" rows="4" class="form-control">{{ old('beneficios', $vaga->beneficios) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('dashboard') }}" class="btn btn-pl-secondary px-4">Cancelar</a>
            <button type="submit" class="btn btn-pl-primary px-4 py-2">
                <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
