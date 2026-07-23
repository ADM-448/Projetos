@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master', 'rh']) ? 'layouts.admin' : (auth()->user()->perfil_acesso === 'gestor' ? 'layouts.gestor' : 'layouts.colaborador'))

@section('title', 'Responder Pesquisa')

@section('content_main')
<div class="container-fluid px-4 py-4">
    
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa-solid fa-clipboard-question text-primary me-2"></i> {{ $survey->titulo }}</h2>
                <a href="{{ route('clima.index') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
            </div>

            @if($survey->descricao)
                <div class="card mb-4 border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <p class="mb-0 text-muted"><i class="fa-solid fa-circle-info me-2"></i> {{ $survey->descricao }}</p>
                    </div>
                </div>
            @endif

            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="fa-solid fa-user-secret me-2"></i>
                <strong>Pesquisa Anônima:</strong> Suas respostas são sigilosas e em nenhum momento seu nome será exibido para a liderança ou RH.
            </div>

            @if($alreadyParticipated)
                <div class="alert alert-info shadow-sm text-center py-5">
                    <i class="fa-solid fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>Você já participou desta pesquisa!</h4>
                    <p class="text-muted">Agradecemos sua colaboração. Suas respostas já foram computadas de forma anônima.</p>
                    <a href="{{ route('clima.index') }}" class="btn btn-primary mt-3">Voltar para Início</a>
                </div>
            @elseif(!$survey->isAtiva())
                <div class="alert alert-warning shadow-sm text-center py-5">
                    <i class="fa-solid fa-lock fa-4x text-warning mb-3"></i>
                    <h4>Pesquisa Indisponível</h4>
                    <p class="text-muted">Esta pesquisa não está aberta para novas respostas no momento.</p>
                </div>
            @else
                <form action="{{ route('clima.submit', $survey->id) }}" method="POST">
                    @csrf
                    
                    @foreach($survey->questions as $index => $question)
                        <div class="card mb-4 shadow-sm border-0 question-card">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                    {{ $question->texto }}
                                </h5>

                                <input type="hidden" name="respostas[{{ $index }}][question_id]" value="{{ $question->id }}">

                                @if($question->isTipoNota())
                                    <div class="px-3">
                                        <div class="d-flex justify-content-between text-muted mb-2 small fw-bold">
                                            <span>1 (Péssimo)</span>
                                            <span>10 (Excelente)</span>
                                        </div>
                                        <input type="range" class="form-range" name="respostas[{{ $index }}][nota]" min="1" max="10" step="1" id="range-{{ $question->id }}" oninput="updateRangeVal('{{ $question->id }}', this.value)" required>
                                        <div class="text-center mt-3">
                                            <span class="badge bg-primary fs-5" id="val-{{ $question->id }}">5</span>
                                        </div>
                                    </div>
                                    <script>
                                        // Set inicial no meio para UX
                                        document.getElementById('range-{{ $question->id }}').value = 5;
                                    </script>
                                @else
                                    <textarea class="form-control" name="respostas[{{ $index }}][texto_resposta]" rows="4" placeholder="Escreva sua resposta (opcional)..."></textarea>
                                @endif
                            </div>
                            @if($question->category)
                                <div class="card-footer bg-white border-top-0 text-end">
                                    <span class="badge" style="background-color: {{ $question->category->cor ?? '#6c757d' }}">{{ $question->category->nome }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="d-grid gap-2 mb-5">
                        <button type="submit" class="btn btn-success btn-lg shadow" onclick="return confirm('Confirmar o envio das suas respostas? Após o envio, não será possível alterar.')">
                            <i class="fa-solid fa-paper-plane me-2"></i> Enviar Respostas (Anônimo)
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</div>

<script>
    function updateRangeVal(id, val) {
        document.getElementById('val-' + id).innerText = val;
    }
</script>

<style>
    .question-card:hover {
        border-left: 4px solid #3b5998 !important;
        transition: 0.2s;
    }
</style>
@endsection
