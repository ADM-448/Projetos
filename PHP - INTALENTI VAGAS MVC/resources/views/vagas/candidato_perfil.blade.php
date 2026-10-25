@extends('layouts.app')

@section('title', ($candidato->nome_completo ?: 'Candidato') . ' - Perfil Profissional')

@push('styles')
<style>
    .perfil-card {
        background: #ffffff;
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius-lg);
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .perfil-section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--pl-dark);
        margin-bottom: 18px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--pl-border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--pl-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--pl-dark);
    }

    .exp-item-timeline {
        border-left: 2px solid var(--pl-green-soft);
        padding-left: 18px;
        margin-bottom: 24px;
        position: relative;
    }

    .exp-item-timeline::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--pl-green);
    }

    .btn-whatsapp {
        background-color: #25D366;
        color: #ffffff;
        border: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-whatsapp:hover {
        background-color: #1ebd56;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-lg-4 py-4" style="max-width: 1240px;">

    <!-- Topo / Navegação -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Painel</a></li>
                    @if($vaga)
                        <li class="breadcrumb-item"><a href="{{ route('vagas.candidatos', $vaga->id) }}" class="text-decoration-none text-muted">Candidatos: {{ $vaga->titulo }}</a></li>
                    @endif
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $candidato->nome_completo }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold mb-0 text-dark">Perfil do Candidato</h2>
        </div>

        @if($vaga)
            <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="btn btn-pl-secondary btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar para Lista</span>
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0 !important;">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <span class="fw-semibold text-dark">{{ session('success') }}</span>
        </div>
    @endif

    <div class="row g-4 align-items-start">

        <!-- ============================================== -->
        <!-- COLUNA PRINCIPAL: DADOS DO CANDIDATO           -->
        <!-- ============================================== -->
        <div class="col-12 col-lg-8">
            
            <!-- Card de Identificação e Contato -->
            <div class="perfil-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px; flex-shrink: 0;">
                            {{ strtoupper(substr($candidato->nome_completo, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">{{ $candidato->nome_completo }}</h3>
                            <div class="text-muted small">
                                <i class="fa-solid fa-briefcase me-1"></i> {{ $candidato->cargo_pretendido ?: 'Profissional' }}
                                &bull; <i class="fa-solid fa-graduation-cap me-1"></i> {{ $candidato->escolaridade ?: 'Escolaridade não informada' }}
                            </div>
                        </div>
                    </div>

                    @if($distanciaKm !== null)
                        <span class="badge bg-light text-success border border-success-subtle rounded-pill py-2 px-3 fw-semibold" style="font-size: 0.85rem;" title="Distância até o local da vaga">
                            <i class="fa-solid fa-location-dot me-1"></i> {{ number_format($distanciaKm, 1, ',', '.') }} km de distância
                        </span>
                    @endif
                </div>

                <!-- Meta Contatos -->
                <div class="d-flex flex-wrap gap-3 text-muted small">
                    <span class="d-flex align-items-center gap-1">
                        <i class="fa-regular fa-envelope text-primary"></i>
                        <a href="mailto:{{ $candidato->user->email }}" class="text-dark text-decoration-none">{{ $candidato->user->email }}</a>
                    </span>
                    @if($candidato->celular)
                        <span class="d-flex align-items-center gap-1">
                            <i class="fa-solid fa-mobile-screen text-success"></i>
                            <span class="text-dark">{{ $candidato->celular }}</span>
                        </span>
                    @endif
                    @if($candidato->telefone)
                        <span class="d-flex align-items-center gap-1">
                            <i class="fa-solid fa-phone text-muted"></i>
                            <span class="text-dark">{{ $candidato->telefone }}</span>
                        </span>
                    @endif
                    <span class="d-flex align-items-center gap-1">
                        <i class="fa-solid fa-location-dot text-danger"></i>
                        <span class="text-dark">{{ $candidato->cidade ?: 'Não informada' }}{{ $candidato->estado ? ' - ' . $candidato->estado : '' }}</span>
                    </span>
                </div>
            </div>

            <!-- Dados Pessoais -->
            <div class="perfil-card">
                <h4 class="perfil-section-title">
                    <i class="fa-regular fa-id-card text-success"></i> Dados Pessoais
                </h4>
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="info-label">Data de Nascimento</div>
                        <div class="info-value">
                            @if($candidato->data_nascimento)
                                {{ $candidato->data_nascimento->format('d/m/Y') }}
                                <span class="text-muted small">({{ $candidato->data_nascimento->age }} anos)</span>
                            @else
                                Não informada
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Estado Civil</div>
                        <div class="info-value">{{ $candidato->estado_civil ?: 'Não informado' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Sexo</div>
                        <div class="info-value">{{ $candidato->sexo ?: 'Não informado' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Escolaridade (Nível Máximo)</div>
                        <div class="info-value">{{ $candidato->escolaridade ?: 'Não informada' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Habilitação (CNH)</div>
                        <div class="info-value">{{ $candidato->carteira_habilitacao ?: 'Não possui / Não informada' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="info-label">Nacionalidade</div>
                        <div class="info-value">{{ $candidato->nacionalidade ?: 'Brasileiro(a)' }}</div>
                    </div>
                </div>
            </div>

            <!-- Sobre Mim -->
            @if(!empty($candidato->sobre_mim))
                <div class="perfil-card">
                    <h4 class="perfil-section-title">
                        <i class="fa-regular fa-comment-dots text-success"></i> Sobre Mim
                    </h4>
                    <p class="text-secondary mb-0" style="line-height: 1.7; white-space: pre-line;">{{ $candidato->sobre_mim }}</p>
                </div>
            @endif

            <!-- Resumo Profissional (se houver) -->
            @if(!empty($candidato->resumo_profissional))
                <div class="perfil-card">
                    <h4 class="perfil-section-title">
                        <i class="fa-solid fa-user-tie text-success"></i> Resumo Profissional
                    </h4>
                    <p class="text-secondary mb-0" style="line-height: 1.7; white-space: pre-line;">{{ $candidato->resumo_profissional }}</p>
                </div>
            @endif

            <!-- Questionário de Triagem Respondido -->
            @if($candidatura && $candidatura->respostas->count() > 0)
                <div class="perfil-card">
                    <h4 class="perfil-section-title">
                        <i class="fa-solid fa-clipboard-question text-success"></i> Questionário da Vaga
                    </h4>
                    <div class="d-flex flex-column gap-3">
                        @foreach($candidatura->respostas as $resp)
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="fw-semibold text-dark mb-1">
                                    {{ $resp->pergunta->texto_pergunta }}
                                    @if($resp->pergunta->eliminatoria)
                                        <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 0.7rem;">Eliminatória</span>
                                    @endif
                                </div>
                                <div class="small">
                                    <strong>Resposta:</strong>
                                    <span class="badge {{ strtolower($resp->resposta) === 'sim' ? 'bg-success text-white' : 'bg-danger text-white' }} px-2 py-1 ms-1">
                                        {{ $resp->resposta }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Experiência Profissional -->
            <div class="perfil-card">
                <h4 class="perfil-section-title">
                    <i class="fa-solid fa-briefcase text-success"></i> Experiência Profissional
                </h4>
                @forelse($candidato->experiencias as $exp)
                    <div class="exp-item-timeline">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                            <h5 class="fw-bold mb-0 text-dark fs-6">{{ $exp->cargo }}</h5>
                            <span class="text-muted small">
                                {{ $exp->data_inicio ? $exp->data_inicio->format('m/Y') : '?' }} &mdash;
                                @if($exp->atual)
                                    <strong class="text-success">Atual</strong>
                                @elseif($exp->data_fim)
                                    {{ $exp->data_fim->format('m/Y') }}
                                @else
                                    <strong class="text-success">Em andamento</strong>
                                @endif
                            </span>
                        </div>
                        <div class="fw-semibold text-secondary small mb-2">{{ $exp->empresa }}</div>
                        @if($exp->descricao)
                            <p class="text-muted small mb-0" style="line-height: 1.6; white-space: pre-line;">{{ $exp->descricao }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0">Nenhuma experiência profissional cadastrada.</p>
                @endforelse
            </div>

            <!-- Formação Acadêmica -->
            <div class="perfil-card">
                <h4 class="perfil-section-title">
                    <i class="fa-solid fa-graduation-cap text-success"></i> Formação Acadêmica
                </h4>
                @forelse($candidato->formacoes as $form)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                            <h5 class="fw-bold mb-0 text-dark fs-6">
                                {{ $form->nivel ? $form->nivel . ' - ' : '' }}{{ $form->curso }}
                            </h5>
                            <span class="text-muted small">
                                {{ $form->data_inicio ? $form->data_inicio->format('Y') : '?' }} &mdash;
                                {{ $form->concluido ? ($form->data_fim ? $form->data_fim->format('Y') : 'Concluído') : 'Em andamento' }}
                            </span>
                        </div>
                        <div class="text-secondary small">{{ $form->instituicao }}</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Nenhuma formação acadêmica cadastrada.</p>
                @endforelse
            </div>

            <!-- Cursos Complementares -->
            @if($candidato->cursos->count() > 0)
                <div class="perfil-card">
                    <h4 class="perfil-section-title">
                        <i class="fa-solid fa-award text-success"></i> Cursos e Aperfeiçoamento
                    </h4>
                    @foreach($candidato->cursos as $curso)
                        <div class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="fw-semibold text-dark">{{ $curso->nome_curso }}</div>
                            <div class="text-muted small">
                                {{ $curso->instituicao ?: 'Instituição não informada' }}
                                @if($curso->carga_horaria) &bull; {{ $curso->carga_horaria }}h @endif
                                @if($curso->ano_conclusao) &bull; Concluído em {{ $curso->ano_conclusao }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Idiomas -->
            @if(!empty($candidato->idiomas))
                <div class="perfil-card">
                    <h4 class="perfil-section-title">
                        <i class="fa-solid fa-language text-success"></i> Idiomas
                    </h4>
                    <div class="d-flex flex-wrap gap-2">
                        @if(is_array($candidato->idiomas))
                            @foreach($candidato->idiomas as $lang)
                                <span class="pl-chip p-2">
                                    {{ $lang['idioma'] ?? $lang }}:
                                    <strong class="text-success ms-1">{{ $lang['nivel'] ?? 'Intermediário' }}</strong>
                                </span>
                            @endforeach
                        @else
                            <span class="pl-chip p-2">{{ $candidato->idiomas }}</span>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- ============================================== -->
        <!-- COLUNA LATERAL: CARD DE AÇÕES DO RECRUTADOR   -->
        <!-- ============================================== -->
        <div class="col-12 col-lg-4">
            <div class="perfil-card" style="position: sticky; top: 90px;">
                <h4 class="fw-bold mb-3 text-dark text-center" style="font-size: 1.2rem;">
                    Ações do Recrutador
                </h4>

                @if($candidatura)
                    <!-- Status Atual -->
                    <div class="p-3 bg-light rounded-3 border text-center mb-4">
                        <div class="text-muted small fw-semibold mb-1">Status Atual:</div>
                        @php
                            $badgeClass = match(strtolower($candidatura->status)) {
                                'aprovado' => 'bg-success text-white',
                                'em_processo', 'analisando' => 'bg-primary text-white',
                                'reprovado', 'rejeitado' => 'bg-danger text-white',
                                default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                            };
                            $statusLabel = match(strtolower($candidatura->status)) {
                                'pendente' => 'Pendente',
                                'em_processo' => 'Em Processo',
                                'aprovado' => 'Aprovado',
                                'reprovado' => 'Não Selecionado',
                                default => ucfirst($candidatura->status)
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} fs-6 px-3 py-2 rounded-pill">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <!-- Formulário para Alterar Status -->
                    <form method="POST" action="{{ route('candidaturas.status', $candidatura->id) }}" class="mb-4">
                        @csrf
                        <label for="status" class="form-label small fw-bold text-muted mb-2">Alterar Status para:</label>
                        <select name="status" id="status" class="form-select mb-3" required>
                            <option value="pendente" {{ strtolower($candidatura->status) === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="em_processo" {{ in_array(strtolower($candidatura->status), ['em_processo', 'analisando']) ? 'selected' : '' }}>Em Processo</option>
                            <option value="aprovado" {{ strtolower($candidatura->status) === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                            <option value="reprovado" {{ in_array(strtolower($candidatura->status), ['reprovado', 'rejeitado']) ? 'selected' : '' }}>Não Selecionado (Reprovado)</option>
                        </select>
                        <button type="submit" class="btn btn-pl-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Atualizar Status</span>
                        </button>
                    </form>

                    <div class="d-flex flex-column gap-2 mb-4">
                        <!-- Botão Ver / Iniciar Conversa -->
                        @if($candidatura->chat)
                            <a href="{{ route('chat.show', $candidatura->chat->id) }}" class="btn btn-dark w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                                <i class="fa-solid fa-comments"></i>
                                <span>Ver Conversa no Chat</span>
                            </a>
                        @endif

                        <!-- Botão WhatsApp -->
                        @if($whatsappLink)
                            <a href="{{ $whatsappLink }}" target="_blank" class="btn btn-whatsapp w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                                <i class="fa-brands fa-whatsapp fs-5"></i>
                                <span>Conversar no WhatsApp</span>
                            </a>
                        @endif

                        <!-- Download do Currículo PDF -->
                        @if(!empty($candidato->curriculo_path))
                            <a href="{{ asset('storage/' . $candidato->curriculo_path) }}" target="_blank" class="btn btn-pl-secondary w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                                <i class="fa-solid fa-file-pdf text-danger"></i>
                                <span>Baixar Currículo PDF</span>
                            </a>
                        @endif
                    </div>

                    <div class="text-center pt-2 border-top">
                        <a href="{{ route('vagas.candidatos', $vaga->id) }}" class="text-decoration-none text-muted small">
                            <i class="fa-solid fa-arrow-left me-1"></i> Voltar para a lista de candidatos
                        </a>
                    </div>
                @else
                    <div class="text-muted small text-center p-3">
                        Este perfil foi acessado fora do fluxo de triagem de uma vaga específica.
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>
@endsection
