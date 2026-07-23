@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master', 'rh']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', 'Resultados: ' . $survey->titulo)

@section('content_main')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-chart-pie text-primary me-2"></i> Resultados da Pesquisa</h2>
        <div>
            @if(auth()->user()->perfil_acesso === 'admin')
                <a href="{{ route('clima.auditoria', $survey->id) }}" class="btn btn-danger me-2"><i class="fa-solid fa-user-shield me-1"></i> Auditoria Admin</a>
            @endif
            <a href="{{ route('clima.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar</a>
        </div>
    </div>

    {{-- Banners Informativos de Visibilidade --}}
    @if($user->perfil_acesso === 'gestor' && $survey->visibilidade_gestor === 'apenas_departamento')
        <div class="alert alert-warning shadow-sm mb-4 border-0 border-start border-warning border-4">
            <i class="fa-solid fa-filter me-2"></i> <strong>Visão Filtrada:</strong> Mostrando apenas os resultados do departamento <strong>{{ $user->departamento->nome ?? 'N/A' }}</strong>.
        </div>
    @elseif($user->perfil_acesso === 'colaborador')
        <div class="alert alert-success shadow-sm mb-4 border-0 border-start border-success border-4">
            <i class="fa-solid fa-users me-2"></i> <strong>Visão Geral:</strong> Os resultados globais da empresa foram liberados para visualização.
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body p-4">
                    <h3 class="mb-1">{{ $survey->titulo }}</h3>
                    <p class="mb-0 text-white-50"><i class="fa-regular fa-calendar me-1"></i> Período: {{ $survey->data_inicio->format('d/m/Y') }} até {{ $survey->data_fim->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtro (Apenas Master/RH) --}}
    @if(in_array($user->perfil_acesso, ['master', 'rh']))
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form action="{{ route('clima.dashboard', $survey->id) }}" method="GET" class="d-flex align-items-end gap-3">
                <div class="flex-grow-1">
                    <label for="department_id" class="form-label fw-bold small text-muted">Filtrar por Departamento:</label>
                    <select name="department_id" id="department_id" class="form-select">
                        <option value="">Todos os Departamentos (Geral)</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->id }}" {{ $deptId == $dep->id ? 'selected' : '' }}>{{ $dep->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter me-1"></i> Aplicar Filtro</button>
                    @if($deptId)
                        <a href="{{ route('clima.dashboard', $survey->id) }}" class="btn btn-outline-secondary">Limpar</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- KPIs --}}
    <div class="row mb-4">
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-5">
                    <h6 class="text-muted fw-bold text-uppercase mb-3">Participação (Geral)</h6>
                    <h1 class="display-4 fw-bold text-primary mb-2">{{ $metricas['response_rate']['rate'] }}%</h1>
                    <p class="text-muted mb-0">{{ $metricas['response_rate']['responded'] }} de {{ $metricas['response_rate']['total'] }} colaboradores ativos</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-5">
                    <h6 class="text-muted fw-bold text-uppercase mb-3">eNPS Score</h6>
                    @php
                        $enps = (float) $metricas['enps'];
                        $corEnps = $enps >= 50 ? 'text-success' : ($enps > 0 ? 'text-primary' : 'text-danger');
                    @endphp
                    <h1 class="display-4 fw-bold {{ $corEnps }} mb-2">{{ $enps }}</h1>
                    <p class="text-muted mb-0">De -100 a +100</p>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-xl-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h6 class="text-muted fw-bold text-uppercase mb-4">Maiores Médias por Categoria</h6>
                    @forelse(array_slice($metricas['averages_by_category'], 0, 3) as $cat)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span style="display:inline-block; width:12px; height:12px; background-color:{{ $cat->cor }}; border-radius:50%; margin-right:8px;"></span>
                                <span class="fw-bold">{{ $cat->categoria }}</span>
                            </div>
                            <span class="badge bg-light text-dark border fs-6">{{ $cat->media }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center">Dados insuficientes</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold"><i class="fa-solid fa-chart-bar text-primary me-2"></i> Média por Categoria</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Respostas Abertas --}}
    @if(count($metricas['text_responses']) > 0)
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4 mt-2"><i class="fa-regular fa-comments text-primary me-2"></i> Respostas Abertas (Anônimas)</h4>
            
            <div class="accordion shadow-sm" id="accordionRespostas">
                @foreach($metricas['text_responses'] as $pergunta => $respostas)
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} bg-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->index }}">
                                {{ $pergunta }}
                                <span class="badge bg-primary ms-2 rounded-pill">{{ count($respostas) }}</span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#accordionRespostas">
                            <div class="accordion-body bg-white p-4">
                                <div class="row">
                                    @foreach($respostas as $resp)
                                        <div class="col-md-6 mb-3">
                                            <div class="p-3 bg-light rounded border-start border-primary border-4 text-dark" style="font-style: italic;">
                                                "{{ $resp }}"
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        const data = @json($metricas['averages_by_category']);
        
        const labels = data.map(item => item.categoria);
        const medias = data.map(item => item.media);
        const cores = data.map(item => item.cor);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Média de Nota (1 a 10)',
                    data: medias,
                    backgroundColor: cores,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection
