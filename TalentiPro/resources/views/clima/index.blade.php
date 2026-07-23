@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master', 'rh']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', 'Pesquisas de Clima')

@section('content_main')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-chart-line text-primary me-2"></i> Pesquisas de Clima Organizacional</h2>
        @if(auth()->user()->perfil_acesso === 'master')
            <a href="{{ route('clima.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Criar Nova Pesquisa
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        @forelse($surveys as $survey)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h5 class="card-title text-truncate mb-0" title="{{ $survey->titulo }}">{{ $survey->titulo }}</h5>
                            <div>
                                @if($survey->status === 'rascunho')
                                    <span class="badge bg-secondary">Rascunho</span>
                                @elseif($survey->status === 'ativa')
                                    <span class="badge bg-success">Ativa</span>
                                @else
                                    <span class="badge bg-danger">Encerrada</span>
                                @endif
                            </div>
                        </div>
                        
                        <p class="text-muted small mb-3">
                            <i class="fa-regular fa-calendar me-1"></i> {{ $survey->data_inicio->format('d/m/Y') }} até {{ $survey->data_fim->format('d/m/Y') }}
                        </p>
                        
                        @if($survey->descricao)
                            <p class="card-text text-truncate" style="font-size: 0.9rem;" title="{{ $survey->descricao }}">{{ $survey->descricao }}</p>
                        @endif
                    </div>
                    
                    <div class="card-footer bg-white border-top-0 pt-0">
                        <div class="d-grid gap-2">
                            
                            {{-- LÓGICA PARA MASTER --}}
                            @if($user->perfil_acesso === 'master')
                                <div class="d-flex justify-content-between gap-2 mb-2">
                                    <a href="{{ route('clima.dashboard', $survey->id) }}" class="btn btn-outline-primary btn-sm flex-grow-1">Resultados</a>
                                    
                                    @if($survey->status === 'rascunho')
                                        <form action="{{ route('clima.abrir', $survey->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Tem certeza? Isso notificará todos os colaboradores.')">Abrir</button>
                                        </form>
                                    @elseif($survey->status === 'ativa')
                                        <form action="{{ route('clima.encerrar', $survey->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Tem certeza? Ninguém mais poderá responder.')">Encerrar</button>
                                        </form>
                                    @endif
                                </div>
                                
                                {{-- Controles de Visibilidade --}}
                                <div class="border rounded p-2 bg-light">
                                    <form action="{{ route('clima.toggle-gestor', $survey->id) }}" method="POST" class="d-flex justify-content-between align-items-center mb-1">
                                        @csrf
                                        <small class="mb-0 text-muted">Gestores veem:</small>
                                        <button type="submit" class="btn btn-sm {{ $survey->visibilidade_gestor === 'geral' ? 'btn-primary' : 'btn-outline-secondary' }}" style="font-size: 0.75rem;">
                                            {{ $survey->visibilidade_gestor === 'geral' ? 'Geral' : 'Só o Depto' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('clima.toggle-colaborador', $survey->id) }}" method="POST" class="d-flex justify-content-between align-items-center">
                                        @csrf
                                        <small class="mb-0 text-muted">Acesso Colab.:</small>
                                        <button type="submit" class="btn btn-sm {{ $survey->resultado_liberado_colaboradores ? 'btn-success' : 'btn-outline-danger' }}" style="font-size: 0.75rem;">
                                            {{ $survey->resultado_liberado_colaboradores ? 'Liberado' : 'Bloqueado' }}
                                        </button>
                                    </form>
                                </div>

                            {{-- LÓGICA PARA RH --}}
                            @elseif($user->perfil_acesso === 'rh')
                                <a href="{{ route('clima.dashboard', $survey->id) }}" class="btn btn-outline-primary btn-sm w-100">Ver Resultados (Geral)</a>
                            
                            {{-- LÓGICA PARA GESTOR --}}
                            @elseif($user->perfil_acesso === 'gestor')
                                <a href="{{ route('clima.dashboard', $survey->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                    Ver Resultados {{ $survey->visibilidade_gestor === 'apenas_departamento' ? '(Meu Depto)' : '(Geral)' }}
                                </a>

                            {{-- LÓGICA PARA COLABORADOR --}}
                            @elseif($user->perfil_acesso === 'colaborador')
                                @if($survey->resultado_liberado_colaboradores)
                                    <a href="{{ route('clima.dashboard', $survey->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">Ver Resultados (Geral)</a>
                                @endif
                            @endif
                            
                            {{-- Botão de Responder para todos se estiver ativa --}}
                            @if($survey->status === 'ativa')
                                @if(in_array($survey->id, $participations))
                                    <button class="btn btn-secondary btn-sm w-100" disabled><i class="fa-solid fa-check"></i> Você já respondeu</button>
                                @else
                                    <a href="{{ route('clima.show', $survey->id) }}" class="btn btn-success btn-sm w-100">Responder Pesquisa</a>
                                @endif
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info border-0 shadow-sm text-center p-5">
                    <i class="fa-solid fa-chart-pie fa-3x text-secondary mb-3"></i>
                    <h4>Nenhuma pesquisa encontrada</h4>
                    <p class="text-muted">As pesquisas de clima organizacional criadas aparecerão aqui.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
