@extends('layouts.app')

@section('title', 'Meu Currículo - IntalenTI Vagas')

@push('styles')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .curriculo-shell {
        max-width: 1060px;
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
    .pl-step-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--pl-green-soft);
        color: var(--pl-green);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .pl-item-box {
        background: #fafafa;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-md);
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.15s ease;
    }
    .pl-item-box:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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
    .form-control::placeholder {
        color: #9ca3af;
        font-size: 0.88rem;
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
    .pl-btn-del {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 6px;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .pl-btn-del:hover {
        background: #fecaca;
        color: #b91c1c;
    }
    .pl-upload-card {
        background: #f9fafb;
        border: 1.5px dashed var(--pl-border);
        border-radius: var(--pl-radius-md);
        padding: 24px;
        transition: border-color 0.15s ease;
    }
    .pl-upload-card:hover {
        border-color: var(--pl-green);
        background: #fcfdfd;
    }
    .pl-save-bar {
        position: sticky;
        bottom: 16px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid var(--pl-border);
        border-radius: 12px;
        padding: 14px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 50;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4 curriculo-shell">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="pl-portal-badge">
                    <i class="fa-solid fa-id-card me-1"></i> Área do Candidato
                </span>
                <span class="badge bg-light text-muted border">Currículo</span>
            </div>
            <h2 class="h3 fw-bold mb-1" style="color: var(--pl-dark); letter-spacing: -0.02em;">Meu Perfil Profissional</h2>
            <p class="text-muted small mb-0">Mantenha seus dados atualizados para aumentar sua visibilidade junto aos recrutadores.</p>
        </div>
        <a href="{{ route('vagas.index') }}" class="btn btn-pl-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Vagas
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 p-3 mb-4 rounded-3" style="background: #fef2f2; border: 1px solid #fecaca !important;">
            <div class="text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fee2e2; flex-shrink: 0;">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            </div>
            <div>
                <strong class="d-block mb-1 text-danger" style="font-size: 0.92rem;">Não foi possível salvar o currículo. Verifique os campos abaixo:</strong>
                <ul class="mb-0 ps-3 small text-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0 !important;">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #dcfce7; color: var(--pl-green); flex-shrink: 0;">
                <i class="fa-solid fa-circle-check fs-5"></i>
            </div>
            <div>
                <strong class="d-block mb-0 text-dark" style="font-size: 0.92rem;">{{ session('success') }}</strong>
            </div>
        </div>
    @endif

    @if(!$candidato->isPerfilCompleto())
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3" style="background: #fffbeb; border: 1px solid #fde68a !important;">
            <div class="text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #fef3c7; flex-shrink: 0;">
                <i class="fa-solid fa-triangle-exclamation fs-5 text-warning"></i>
            </div>
            <div>
                <strong class="d-block mb-0 text-dark" style="font-size: 0.92rem;">Atenção: Seu perfil ainda está incompleto!</strong>
                <small class="text-muted">Para se candidatar às vagas, informe pelo menos: <strong>Data de Nascimento, Contato, Endereço com CEP e o campo "Sobre Mim"</strong>.</small>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('curriculo.update') }}" enctype="multipart/form-data" id="curriculoForm">
        @csrf

        <!-- 1. DADOS PESSOAIS E CONTATO -->
        <div class="pl-section-card">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="pl-step-num">1</span>
                <div>
                    <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Dados Pessoais e Contato</h5>
                    <small class="text-muted">Suas informações básicas visíveis para as empresas</small>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" id="nome_completo" name="nome_completo" value="{{ old('nome_completo', $candidato->nome_completo) }}" class="form-control @error('nome_completo') is-invalid @enderror" placeholder="Ex: Pamela Rodrigues" required>
                    @error('nome_completo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="data_nascimento" class="form-label">Data de Nascimento <span class="text-danger">*</span></label>
                    <input type="date" id="data_nascimento" name="data_nascimento" max="{{ date('Y-m-d') }}" value="{{ old('data_nascimento', $candidato->data_nascimento?->format('Y-m-d')) }}" class="form-control @error('data_nascimento') is-invalid @enderror" required>
                    @error('data_nascimento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-3">
                    <label for="escolaridade" class="form-label">Escolaridade</label>
                    <select id="escolaridade" name="escolaridade" class="form-select">
                        <option value="">Selecione...</option>
                        @foreach($niveisEscolaridade as $esc)
                            <option value="{{ $esc }}" {{ old('escolaridade', $candidato->escolaridade) == $esc ? 'selected' : '' }}>{{ $esc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label for="celular" class="form-label">Celular / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" id="celular" name="celular" value="{{ old('celular', $candidato->celular) }}" class="form-control @error('celular') is-invalid @enderror" placeholder="(44) 99999-0000" required>
                    @error('celular')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="telefone" class="form-label">Telefone Fixo / Recado</label>
                    <input type="text" id="telefone" name="telefone" value="{{ old('telefone', $candidato->telefone) }}" class="form-control" placeholder="(44) 3000-0000">
                </div>

                <div class="col-12 col-md-4">
                    <label for="cargo_pretendido" class="form-label">Cargo / Especialidade</label>
                    <input type="text" id="cargo_pretendido" name="cargo_pretendido" value="{{ old('cargo_pretendido', $candidato->cargo_pretendido) }}" class="form-control" placeholder="Ex: Desenvolvedor Back-End PHP">
                </div>
            </div>

            <hr class="my-4" style="border-color: #f1f5f9;">

            <!-- Endereço com Validador de CEP -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-location-dot" style="color: var(--pl-green);"></i>
                <h6 class="fw-bold mb-0" style="color: var(--pl-dark); font-size: 0.95rem;">Endereço Residencial</h6>
            </div>
            
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label for="cep" class="form-label">CEP <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="cep" name="cep" value="{{ old('cep', $candidato->cep) }}" class="form-control @error('cep') is-invalid @enderror" placeholder="87000-000" maxlength="9" required>
                        <button class="btn btn-pl-secondary px-3" type="button" id="btnBuscarCep" title="Buscar Endereço pelo CEP">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <small id="cepStatus" class="d-block mt-1 small"></small>
                </div>

                <div class="col-12 col-md-5">
                    <label for="logradouro" class="form-label">Rua / Logradouro</label>
                    <input type="text" id="logradouro" name="logradouro" value="{{ old('logradouro', $candidato->logradouro) }}" class="form-control" placeholder="Av. Brasil">
                </div>

                <div class="col-12 col-md-4">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" id="bairro" name="bairro" value="{{ old('bairro', $candidato->bairro) }}" class="form-control" placeholder="Zona 01 / Centro">
                </div>

                <div class="col-12 col-md-6">
                    <label for="cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
                    <input type="text" id="cidade" name="cidade" value="{{ old('cidade', $candidato->cidade) }}" class="form-control @error('cidade') is-invalid @enderror" placeholder="Maringá" required>
                </div>

                <div class="col-12 col-md-2">
                    <label for="estado" class="form-label">UF <span class="text-danger">*</span></label>
                    <input type="text" id="estado" name="estado" value="{{ old('estado', $candidato->estado) }}" class="form-control @error('estado') is-invalid @enderror" placeholder="PR" maxlength="2" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: #f1f5f9;">

            <!-- Sobre Mim & Resumo Profissional -->
            <div class="row g-3">
                <div class="col-12">
                    <label for="sobre_mim" class="form-label">
                        Sobre Mim <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(Apresentação sucinta aos recrutadores)</small>
                    </label>
                    <textarea id="sobre_mim" name="sobre_mim" rows="3" class="form-control @error('sobre_mim') is-invalid @enderror" placeholder="Descreva brevemente sua trajetória, valores profissionais e objetivos..." required>{{ old('sobre_mim', $candidato->sobre_mim) }}</textarea>
                    @error('sobre_mim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="resumo_profissional" class="form-label mb-0">
                            Resumo de Experiências & Competências
                            <small class="text-muted fw-normal">(Opcional, destaques de sua bagagem técnica)</small>
                        </label>
                        <span id="charCount" class="badge bg-light text-muted border">0 / 1000 caracteres</span>
                    </div>
                    <textarea id="resumo_profissional" name="resumo_profissional" rows="4" maxlength="1000" class="form-control" placeholder="Principais competências, frameworks, ferramentas, projetos e conquistas relevantes...">{{ old('resumo_profissional', $candidato->resumo_profissional) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. IDIOMAS -->
        <div class="pl-section-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="pl-step-num">2</span>
                    <div>
                        <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Idiomas</h5>
                        <small class="text-muted">Idiomas conhecidos e nível de proficiência</small>
                    </div>
                </div>
                <button type="button" class="pl-btn-add" id="btnAddIdioma">
                    <i class="fa-solid fa-plus"></i> Adicionar Idioma
                </button>
            </div>

            <div id="containerIdiomas">
                @php $idiomasSalvos = $candidato->idiomas ?: []; @endphp
                @forelse($idiomasSalvos as $idx => $idm)
                    <div class="row g-2 align-items-center mb-2 idioma-row" id="idioma_row_{{ $idx }}">
                        <div class="col-12 col-md-5">
                            <input type="text" name="idiomas_itens[{{ $idx }}][idioma]" value="{{ $idm['idioma'] ?? '' }}" class="form-control" placeholder="Idioma (ex: Inglês, Espanhol)">
                        </div>
                        <div class="col-12 col-md-5">
                            <select name="idiomas_itens[{{ $idx }}][nivel]" class="form-select">
                                @foreach($niveisIdioma as $nivel)
                                    <option value="{{ $nivel }}" {{ ($idm['nivel'] ?? '') == $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2 text-end">
                            <button type="button" class="pl-btn-del w-100 justify-content-center btn-remove-item" data-target="#idioma_row_{{ $idx }}">
                                <i class="fa-solid fa-trash-can"></i> Remover
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small p-3 bg-light rounded-3 text-center border" id="emptyIdiomas">
                        <i class="fa-solid fa-language me-1 text-muted"></i> Nenhum idioma adicionado ainda. Clique em "Adicionar Idioma" acima.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. EXPERIÊNCIAS PROFISSIONAIS -->
        <div class="pl-section-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="pl-step-num">3</span>
                    <div>
                        <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Experiências Profissionais</h5>
                        <small class="text-muted">Histórico das empresas onde você atuou</small>
                    </div>
                </div>
                <button type="button" class="pl-btn-add" id="btnAddExp">
                    <i class="fa-solid fa-plus"></i> Adicionar Experiência
                </button>
            </div>

            <div id="containerExperiencias">
                @forelse($candidato->experiencias as $idx => $exp)
                    <div class="pl-item-box exp-card" id="exp_{{ $idx }}">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                                    <i class="fa-solid fa-briefcase"></i>
                                </span>
                                <strong style="color: var(--pl-dark); font-size: 0.92rem;">Experiência #{{ $idx + 1 }}</strong>
                            </div>
                            <button type="button" class="pl-btn-del btn-remove-item" data-target="#exp_{{ $idx }}">
                                <i class="fa-solid fa-trash-can"></i> Excluir
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Empresa</label>
                                <input type="text" name="experiencias[{{ $idx }}][empresa]" value="{{ $exp->empresa }}" class="form-control" placeholder="Nome da Empresa" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="experiencias[{{ $idx }}][cargo]" value="{{ $exp->cargo }}" class="form-control" placeholder="Cargo Ocupado" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Data Início</label>
                                <input type="date" name="experiencias[{{ $idx }}][data_inicio]" max="{{ date('Y-m-d') }}" value="{{ $exp->data_inicio?->format('Y-m-d') }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Data Fim</label>
                                <input type="date" name="experiencias[{{ $idx }}][data_fim]" value="{{ $exp->data_fim?->format('Y-m-d') }}" class="form-control data-fim-input">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end pb-1">
                                <div class="form-check">
                                    <input type="checkbox" name="experiencias[{{ $idx }}][atual]" value="1" {{ $exp->atual ? 'checked' : '' }} class="form-check-input chk-atual" id="atual_{{ $idx }}">
                                    <label class="form-check-label small fw-semibold" for="atual_{{ $idx }}">Emprego Atual</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrição das Atividades</label>
                                <textarea name="experiencias[{{ $idx }}][descricao]" rows="2" class="form-control" placeholder="Responsabilidades, projetos entregues e tecnologias dominadas...">{{ $exp->descricao }}</textarea>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small p-3 bg-light rounded-3 text-center border" id="emptyExp">
                        <i class="fa-solid fa-briefcase me-1 text-muted"></i> Nenhuma experiência profissional cadastrada ainda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 4. FORMAÇÃO ACADÊMICA -->
        <div class="pl-section-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="pl-step-num">4</span>
                    <div>
                        <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Formação Acadêmica & Cursos</h5>
                        <small class="text-muted">Cursos superiores, técnicos ou certificações</small>
                    </div>
                </div>
                <button type="button" class="pl-btn-add" id="btnAddForm">
                    <i class="fa-solid fa-plus"></i> Adicionar Formação
                </button>
            </div>

            <div id="containerFormacoes">
                @forelse($candidato->formacoes as $idx => $form)
                    <div class="pl-item-box form-card" id="form_{{ $idx }}">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </span>
                                <strong style="color: var(--pl-dark); font-size: 0.92rem;">Formação #{{ $idx + 1 }}</strong>
                            </div>
                            <button type="button" class="pl-btn-del btn-remove-item" data-target="#form_{{ $idx }}">
                                <i class="fa-solid fa-trash-can"></i> Excluir
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Instituição de Ensino</label>
                                <input type="text" name="formacoes[{{ $idx }}][instituicao]" value="{{ $form->instituicao }}" class="form-control" placeholder="Ex: UEM, Unicesumar, USP" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Curso</label>
                                <input type="text" name="formacoes[{{ $idx }}][curso]" value="{{ $form->curso }}" class="form-control" placeholder="Ex: Ciência da Computação, Análise de Sistemas" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Data Início</label>
                                <input type="date" name="formacoes[{{ $idx }}][data_inicio]" max="{{ date('Y-m-d') }}" value="{{ $form->data_inicio?->format('Y-m-d') }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Data Conclusão / Previsão</label>
                                <input type="date" name="formacoes[{{ $idx }}][data_fim]" value="{{ $form->data_fim?->format('Y-m-d') }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end pb-1">
                                <div class="form-check">
                                    <input type="checkbox" name="formacoes[{{ $idx }}][concluido]" value="1" {{ $form->concluido ? 'checked' : '' }} class="form-check-input" id="conc_{{ $idx }}">
                                    <label class="form-check-label small fw-semibold" for="conc_{{ $idx }}">Curso Concluído</label>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small p-3 bg-light rounded-3 text-center border" id="emptyForm">
                        <i class="fa-solid fa-graduation-cap me-1 text-muted"></i> Nenhuma formação acadêmica cadastrada ainda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 5. ANEXO DE CURRÍCULO -->
        <div class="pl-section-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="pl-step-num">5</span>
                <div>
                    <h5 class="fw-bold mb-0" style="color: var(--pl-dark);">Anexo do Currículo (PDF / Word)</h5>
                    <small class="text-muted">Envie seu documento em PDF para que recrutadores possam baixar</small>
                </div>
            </div>

            <div class="pl-upload-card">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-8">
                        <label for="curriculo_file" class="form-label">Selecionar Arquivo</label>
                        <input type="file" name="curriculo_file" id="curriculo_file" class="form-control @error('curriculo_file') is-invalid @enderror" accept=".pdf,.doc,.docx">
                        <small class="text-muted d-block mt-1">Formatos aceitos: <strong>PDF, DOC ou DOCX</strong> (Tamanho máximo: 5 MB).</small>
                        @error('curriculo_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-4 text-center text-md-end">
                        @if($candidato->curriculo_path)
                            <div class="d-inline-flex align-items-center gap-2 p-2 bg-white rounded-3 border text-success small shadow-sm text-start">
                                <i class="fa-solid fa-file-pdf fs-3 text-danger"></i>
                                <div>
                                    <strong class="d-block text-dark">Arquivo Anexado</strong>
                                    <a href="{{ asset('storage/' . $candidato->curriculo_path) }}" target="_blank" class="fw-semibold" style="color: var(--pl-green); text-decoration: none;">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver Documento
                                    </a>
                                </div>
                            </div>
                        @else
                            <span class="badge bg-white text-muted border p-2 shadow-sm">
                                <i class="fa-solid fa-circle-info me-1"></i> Nenhum anexo enviado
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de Salvamento Flutuante/Fixa -->
        <div class="pl-save-bar mb-5">
            <a href="{{ route('vagas.index') }}" class="btn btn-pl-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
            <button type="submit" class="btn btn-pl-primary px-4 py-2">
                <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Currículo
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Contador de Caracteres para Resumo Profissional (Item 314)
        const textareaResumo = document.getElementById('resumo_profissional');
        const charCount = document.getElementById('charCount');

        function atualizarContador() {
            if (!textareaResumo || !charCount) return;
            const len = textareaResumo.value.length;
            charCount.textContent = `${len} / 1000 caracteres`;
            if (len >= 950) {
                charCount.className = 'badge bg-danger text-white border';
            } else {
                charCount.className = 'badge bg-light text-muted border';
            }
        }

        if (textareaResumo) {
            textareaResumo.addEventListener('input', atualizarContador);
            atualizarContador();
        }

        // 2. Validador e Busca Assíncrona de CEP (Item 312 & 350)
        const inputCep = document.getElementById('cep');
        const btnBuscarCep = document.getElementById('btnBuscarCep');
        const cepStatus = document.getElementById('cepStatus');

        function formatarCep(v) {
            v = v.replace(/\D/g, '');
            if (v.length > 5) v = v.substring(0, 5) + '-' + v.substring(5, 8);
            return v;
        }

        if (inputCep) {
            inputCep.addEventListener('input', function () {
                this.value = formatarCep(this.value);
                const cepLimpo = this.value.replace(/\D/g, '');
                if (cepLimpo.length === 8) {
                    buscarCep();
                }
            });
        }

        function buscarCep() {
            const cepLimpo = inputCep.value.replace(/\D/g, '');
            if (cepLimpo.length !== 8) {
                cepStatus.textContent = 'Informe um CEP válido com 8 dígitos.';
                cepStatus.className = 'd-block mt-1 small text-danger fw-semibold';
                return;
            }

            cepStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Localizando endereço...';
            cepStatus.className = 'd-block mt-1 small text-primary';

            fetch(`/api/cep/${cepLimpo}`)
                .then(res => {
                    if (!res.ok) throw new Error('CEP não localizado');
                    return res.json();
                })
                .then(data => {
                    if (data.logradouro) document.getElementById('logradouro').value = data.logradouro;
                    if (data.bairro) document.getElementById('bairro').value = data.bairro;
                    if (data.cidade) document.getElementById('cidade').value = data.cidade;
                    if (data.estado) document.getElementById('estado').value = data.estado;
                    
                    const descCidade = (data.cidade ? data.cidade : '') + (data.estado ? ' - ' + data.estado : '');
                    let msg = '<i class="fa-solid fa-circle-check me-1"></i> ' + (descCidade ? 'Endereço localizado: <strong>' + descCidade + '</strong>' : 'Endereço preenchido!');
                    if (!data.logradouro) {
                        msg += ' <span class="text-muted fw-normal">(você pode complementar a rua e o bairro manualmente)</span>';
                    }
                    cepStatus.innerHTML = msg;
                    cepStatus.className = 'd-block mt-1 small text-success fw-semibold';
                })
                .catch(() => {
                    cepStatus.innerHTML = '<i class="fa-solid fa-circle-info me-1"></i> CEP não localizado na base. Você pode digitar manualmente.';
                    cepStatus.className = 'd-block mt-1 small text-warning fw-semibold';
                });
        }

        if (btnBuscarCep) btnBuscarCep.addEventListener('click', buscarCep);
        if (inputCep) {
            inputCep.addEventListener('blur', function () {
                if (this.value.replace(/\D/g, '').length === 8) buscarCep();
            });
        }

        // 3. Adição Dinâmica de Idiomas (Item 309, 310)
        let idiomaIndex = {{ count($candidato->idiomas ?: []) + 10 }};
        const btnAddIdioma = document.getElementById('btnAddIdioma');
        const containerIdiomas = document.getElementById('containerIdiomas');
        const emptyIdiomas = document.getElementById('emptyIdiomas');

        if (btnAddIdioma) {
            btnAddIdioma.addEventListener('click', function () {
                if (emptyIdiomas) emptyIdiomas.remove();
                const div = document.createElement('div');
                div.className = 'row g-2 align-items-center mb-2 idioma-row';
                div.id = `idioma_row_${idiomaIndex}`;
                div.innerHTML = `
                    <div class="col-12 col-md-5">
                        <input type="text" name="idiomas_itens[${idiomaIndex}][idioma]" class="form-control" placeholder="Idioma (ex: Inglês, Espanhol)" required>
                    </div>
                    <div class="col-12 col-md-5">
                        <select name="idiomas_itens[${idiomaIndex}][nivel]" class="form-select">
                            <option value="Básico">Básico</option>
                            <option value="Intermediário">Intermediário</option>
                            <option value="Avançado">Avançado</option>
                            <option value="Fluente / Nativo">Fluente / Nativo</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 text-end">
                        <button type="button" class="pl-btn-del w-100 justify-content-center btn-remove-item" data-target="#idioma_row_${idiomaIndex}">
                            <i class="fa-solid fa-trash-can"></i> Remover
                        </button>
                    </div>
                `;
                containerIdiomas.appendChild(div);
                idiomaIndex++;
            });
        }

        // 4. Adição Dinâmica de Experiências Profissionais
        let expIndex = {{ $candidato->experiencias->count() + 10 }};
        const btnAddExp = document.getElementById('btnAddExp');
        const containerExp = document.getElementById('containerExperiencias');
        const emptyExp = document.getElementById('emptyExp');

        if (btnAddExp) {
            btnAddExp.addEventListener('click', function () {
                if (emptyExp) emptyExp.remove();
                const div = document.createElement('div');
                div.className = 'pl-item-box exp-card';
                div.id = `exp_${expIndex}`;
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                                <i class="fa-solid fa-briefcase"></i>
                            </span>
                            <strong style="color: var(--pl-dark); font-size: 0.92rem;">Nova Experiência</strong>
                        </div>
                        <button type="button" class="pl-btn-del btn-remove-item" data-target="#exp_${expIndex}">
                            <i class="fa-solid fa-trash-can"></i> Excluir
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Empresa</label>
                            <input type="text" name="experiencias[${expIndex}][empresa]" class="form-control" placeholder="Nome da Empresa" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="experiencias[${expIndex}][cargo]" class="form-control" placeholder="Cargo Ocupado" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="experiencias[${expIndex}][data_inicio]" max="{{ date('Y-m-d') }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="experiencias[${expIndex}][data_fim]" class="form-control data-fim-input">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input type="checkbox" name="experiencias[${expIndex}][atual]" value="1" class="form-check-input chk-atual" id="atual_${expIndex}">
                                <label class="form-check-label small fw-semibold" for="atual_${expIndex}">Emprego Atual</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição das Atividades</label>
                            <textarea name="experiencias[${expIndex}][descricao]" rows="2" class="form-control" placeholder="Responsabilidades, projetos e tecnologias dominadas..."></textarea>
                        </div>
                    </div>
                `;
                containerExp.appendChild(div);
                expIndex++;
            });
        }

        // 5. Adição Dinâmica de Formação Acadêmica
        let formIndex = {{ $candidato->formacoes->count() + 10 }};
        const btnAddForm = document.getElementById('btnAddForm');
        const containerForm = document.getElementById('containerFormacoes');
        const emptyForm = document.getElementById('emptyForm');

        if (btnAddForm) {
            btnAddForm.addEventListener('click', function () {
                if (emptyForm) emptyForm.remove();
                const div = document.createElement('div');
                div.className = 'pl-item-box form-card';
                div.id = `form_${formIndex}`;
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="pl-portal-badge" style="background: var(--pl-green-soft); color: var(--pl-green);">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </span>
                            <strong style="color: var(--pl-dark); font-size: 0.92rem;">Nova Formação</strong>
                        </div>
                        <button type="button" class="pl-btn-del btn-remove-item" data-target="#form_${formIndex}">
                            <i class="fa-solid fa-trash-can"></i> Excluir
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Instituição de Ensino</label>
                            <input type="text" name="formacoes[${formIndex}][instituicao]" class="form-control" placeholder="Ex: UEM, Unicesumar" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Curso</label>
                            <input type="text" name="formacoes[${formIndex}][curso]" class="form-control" placeholder="Ex: Ciência da Computação" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="formacoes[${formIndex}][data_inicio]" max="{{ date('Y-m-d') }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data Conclusão / Previsão</label>
                            <input type="date" name="formacoes[${formIndex}][data_fim]" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input type="checkbox" name="formacoes[${formIndex}][concluido]" value="1" checked class="form-check-input" id="conc_${formIndex}">
                                <label class="form-check-label small fw-semibold" for="conc_${formIndex}">Curso Concluído</label>
                            </div>
                        </div>
                    </div>
                `;
                containerForm.appendChild(div);
                formIndex++;
            });
        }

        // 6. SweetAlert2 Popup Elegante para Exclusão (Item 323)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-item');
            if (btn) {
                const targetSelector = btn.dataset.target;
                const target = document.querySelector(targetSelector);
                if (target) {
                    Swal.fire({
                        title: 'Remover este item?',
                        text: 'Esta informação será removida do currículo.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sim, remover',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            target.remove();
                        }
                    });
                }
            }
        });
    });
</script>
@endpush
