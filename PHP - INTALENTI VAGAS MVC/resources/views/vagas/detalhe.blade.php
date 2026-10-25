@extends('layouts.app')

@section('title', $vaga->titulo . ' - IntalenTI Vagas')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .question-card {
        border-radius: var(--pl-radius-md);
        background: #f8fafc;
        border: 1px solid var(--pl-border);
        transition: border-color 0.2s ease;
    }
    .question-card:hover {
        border-color: var(--pl-border-hover);
    }
    .btn-check:checked + .btn-outline-success {
        background-color: var(--pl-green);
        border-color: var(--pl-green);
        color: #fff;
    }
    .btn-check:checked + .btn-outline-danger {
        background-color: #ef4444;
        border-color: #ef4444;
        color: #fff;
    }

    /* Justificação Elegante de Texto */
    .vaga-texto-justificado {
        text-align: justify;
        text-justify: inter-word;
        hyphens: auto;
        -webkit-hyphens: auto;
        word-break: break-word;
        line-height: 1.8;
        white-space: pre-line;
    }

    /* Otimizações Responsivas Mobile */
    @media (max-width: 768px) {
        .vaga-detalhe-container {
            padding-left: 12px !important;
            padding-right: 12px !important;
            padding-top: 16px !important;
        }
        .vaga-detalhe-card {
            padding: 18px 14px !important;
            border-radius: var(--pl-radius-md) !important;
        }
        .vaga-header-flex {
            gap: 12px !important;
        }
        .vaga-logo-box {
            width: 48px !important;
            height: 48px !important;
            font-size: 1.3rem !important;
            border-radius: 10px !important;
        }
        .vaga-title-responsive {
            font-size: 1.25rem !important;
            line-height: 1.35 !important;
        }
        .vaga-meta-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
            padding: 12px !important;
        }
        .vaga-meta-item {
            margin: 0 !important;
        }
        .vaga-meta-item small {
            font-size: 0.72rem !important;
        }
        .vaga-meta-item strong {
            font-size: 0.88rem !important;
        }
    }

    @media (max-width: 991px) {
        .vaga-action-card {
            position: static !important;
            margin-top: 0 !important;
            padding: 20px 16px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-4 vaga-detalhe-container" style="max-width: 1200px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('vagas.index') }}" class="text-decoration-none text-muted">Vagas</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold text-truncate" style="max-width: 70vw;" aria-current="page">{{ $vaga->titulo }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="pl-card p-4 p-md-5 mb-4 vaga-detalhe-card">
                <div class="d-flex align-items-center gap-3 mb-4 vaga-header-flex">
                    <div class="rounded-3 bg-light border p-2 d-flex align-items-center justify-content-center text-dark fs-2 vaga-logo-box" style="width: 64px; height: 64px; flex-shrink: 0; border-radius: 14px !important;">
                        @if($vaga->empresa && $vaga->empresa->logo_path)
                            <img src="{{ asset('storage/' . $vaga->empresa->logo_path) }}" alt="Logo" class="img-fluid rounded" style="max-height: 100%;">
                        @else
                            <i class="fa-solid fa-building text-muted"></i>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <span class="pl-chip mb-1">
                            {{ $vaga->confidencial ? 'Empresa Confidencial' : ($vaga->empresa->nome_empresa ?? 'Empresa') }}
                        </span>
                        <h2 class="fw-bold mb-1 h3 vaga-title-responsive">{{ $vaga->titulo }}</h2>
                        <div class="text-muted small">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $vaga->localizacao ?: 'Maringá, PR' }} &bull;
                            <i class="fa-regular fa-clock me-1"></i> Publicada {{ $vaga->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <!-- Grade de Metadados Responsiva (2x2 no mobile) -->
                <div class="vaga-meta-grid d-flex flex-wrap gap-2 mb-4 p-3 bg-light rounded-3">
                    <div class="vaga-meta-item me-md-4">
                        <small class="text-muted d-block">Tipo de Contrato</small>
                        <strong class="text-dark">{{ $vaga->tipo_contrato ?: 'CLT' }}</strong>
                    </div>
                    <div class="vaga-meta-item me-md-4">
                        <small class="text-muted d-block">Modalidade</small>
                        <strong class="text-dark">{{ $vaga->modalidade ?: 'Presencial' }}</strong>
                    </div>
                    <div class="vaga-meta-item me-md-4">
                        <small class="text-muted d-block">Área de Atuação</small>
                        <strong class="text-dark">{{ $vaga->ramo_atividade ?: 'Geral' }}</strong>
                    </div>
                    <div class="vaga-meta-item">
                        <small class="text-muted d-block">Remuneração</small>
                        <strong class="text-success">{{ $vaga->salario_formatado }}</strong>
                    </div>
                </div>

                <!-- Descrição Justificada -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Descrição da Oportunidade</h5>
                    <div class="vaga-texto-justificado text-secondary">
                        {{ $vaga->descricao }}
                    </div>
                </div>

                <!-- Requisitos Justificados -->
                @if($vaga->requisitos)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Requisitos e Qualificações</h5>
                        <div class="vaga-texto-justificado text-secondary">
                            {{ $vaga->requisitos }}
                        </div>
                    </div>
                @endif

                <!-- Benefícios Justificados -->
                @if($vaga->beneficios)
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Benefícios Oferecidos</h5>
                        <div class="vaga-texto-justificado text-secondary">
                            {{ $vaga->beneficios }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Coluna Lateral de Ação -->
        <div class="col-lg-4">
            <div class="pl-card p-4 sticky-top vaga-action-card" style="top: 90px;">
                @if($jaCandidatado)
                    <!-- Já Candidatado -->
                    <div class="text-center py-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle p-3 mb-3" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-check fa-2x"></i>
                        </div>
                        <h5 class="fw-bold text-success mb-1">Candidatura Enviada!</h5>
                        <p class="text-muted small mb-3">
                            Você se candidatou em {{ $candidatura->data_candidatura->format('d/m/Y \à\s H:i') }}.
                        </p>
                        <div class="badge bg-light text-secondary border px-3 py-2 mb-4">
                            Status: <strong class="text-uppercase">{{ $candidatura->status }}</strong>
                        </div>

                        <!-- Botão de Chat -->
                        @if($chat)
                            <a href="{{ route('chat.show', $chat->id) }}" class="btn btn-pl-primary w-100 py-2 mb-3">
                                <i class="fa-solid fa-comments me-2"></i> Mensagens com a Empresa
                            </a>
                        @endif

                        <!-- Cancelar Candidatura com espaçamento (Item 302) -->
                        <form method="POST" action="{{ route('candidaturas.cancelar', $candidatura->id) }}" id="formCancelarDetalhe">
                            @csrf
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 py-2 mb-3" id="btnCancelarDetalhe">
                                <i class="fa-solid fa-ban me-1"></i> Cancelar Candidatura
                            </button>
                        </form>

                        <!-- Espaçamento entre Cancelar e Voltar (Item 302) -->
                        <div class="pt-2 border-top">
                            <a href="{{ route('vagas.index') }}" class="btn btn-pl-secondary w-100 py-2 text-secondary">
                                <i class="fa-solid fa-arrow-left me-1"></i> Voltar para a Lista de Vagas
                            </a>
                        </div>
                    </div>

                @elseif(Auth::check() && Auth::user()->isCandidato())

                    <!-- Alerta de Perfil Incompleto (Item 307 & 325) -->
                    @if(!$perfilCompleto)
                        <div class="alert alert-warning border-0 p-3 mb-3 rounded-4 shadow-sm">
                            <div class="d-flex gap-2">
                                <i class="fa-solid fa-circle-exclamation fs-4 text-warning mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Currículo Incompleto</h6>
                                    <small class="d-block mb-3">Para se candidatar a esta vaga, é necessário preencher os dados mínimos do seu currículo (dados pessoais, contato, localização e sobre você).</small>
                                    <a href="{{ route('curriculo.edit') }}" class="btn btn-warning btn-sm fw-bold w-100">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Completar Meu Currículo
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Formulário de Aplicação -->
                        <h5 class="fw-bold mb-3">Candidatar-se à Vaga</h5>
                        <form method="POST" action="{{ route('vagas.aplicar', $vaga->id) }}">
                            @csrf

                            @if($vaga->perguntas->count() > 0)
                                <div class="mb-3">
                                    <h6 class="fw-semibold text-secondary mb-2 small text-uppercase">Questionário de Triagem:</h6>
                                    @foreach($vaga->perguntas as $pergunta)
                                        <div class="question-card p-3 mb-3">
                                            <p class="small fw-semibold mb-2">
                                                {{ $pergunta->texto_pergunta }}
                                                @if($pergunta->eliminatoria)
                                                    <span class="badge bg-danger ms-1">Obrigatório</span>
                                                @endif
                                            </p>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="respostas[{{ $pergunta->id }}]" id="resp_{{ $pergunta->id }}_sim" value="Sim" required>
                                                <label class="btn btn-sm btn-outline-success" for="resp_{{ $pergunta->id }}_sim">
                                                    <i class="fa-solid fa-check me-1"></i> Sim
                                                </label>

                                                <input type="radio" class="btn-check" name="respostas[{{ $pergunta->id }}]" id="resp_{{ $pergunta->id }}_nao" value="Não" required>
                                                <label class="btn btn-sm btn-outline-danger" for="resp_{{ $pergunta->id }}_nao">
                                                    <i class="fa-solid fa-xmark me-1"></i> Não
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <button type="submit" class="btn btn-pl-primary w-100 py-3 fw-bold fs-6 mb-3 shadow-sm">
                                <i class="fa-solid fa-paper-plane me-2"></i> Confirmar Candidatura
                            </button>
                        </form>
                    @endif

                    <!-- Espaço entre ações e voltar (Item 302) -->
                    <div class="pt-3 border-top mt-2">
                        <a href="{{ route('vagas.index') }}" class="btn btn-pl-secondary w-100 py-2 text-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Lista de Vagas
                        </a>
                    </div>

                @elseif(!Auth::check())
                    <!-- Visitante não logado -->
                    <div class="text-center py-3">
                        <h5 class="fw-bold mb-2">Interessado nesta vaga?</h5>
                        <p class="text-muted small mb-4">Faça login ou crie sua conta gratuitamente para se candidatar em poucos cliques.</p>
                        <a href="{{ route('login') }}" class="btn btn-pl-primary w-100 py-2 mb-2">Entrar para Candidatar-se</a>
                        <a href="{{ route('register') }}" class="btn btn-pl-secondary w-100 py-2 mb-3">Criar Conta Gratuita</a>
                        <div class="border-top pt-3">
                            <a href="{{ route('vagas.index') }}" class="btn btn-pl-secondary btn-sm text-muted">
                                <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Lista de Vagas
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Outro perfil (Empresa / Admin) -->
                    <div class="text-center py-3">
                        <span class="badge bg-secondary p-2 mb-3">Visualização Corporativa</span>
                        <p class="text-muted small">Você está visualizando a vaga com perfil empresarial/gestor. Para se candidatar, acesse como candidato.</p>
                        <a href="{{ route('vagas.index') }}" class="btn btn-pl-secondary w-100 py-2 text-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Voltar para Lista de Vagas
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnCancelar = document.getElementById('btnCancelarDetalhe');
        const formCancelar = document.getElementById('formCancelarDetalhe');

        if (btnCancelar && formCancelar) {
            btnCancelar.addEventListener('click', function () {
                Swal.fire({
                    title: 'Cancelar Candidatura?',
                    text: 'Você deixará de participar do processo seletivo desta oportunidade.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sim, cancelar candidatura',
                    cancelButtonText: 'Manter inscrição'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formCancelar.submit();
                    }
                });
            });
        }
    });
</script>
@endpush
