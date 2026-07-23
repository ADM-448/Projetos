@extends('layouts.admin')

@section('title', 'Nova Pesquisa de Clima')

@section('content_main')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-plus text-primary me-2"></i> Nova Pesquisa de Clima</h2>
        <a href="{{ route('clima.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('clima.store') }}" method="POST" id="form-pesquisa">
                @csrf
                
                <h5 class="mb-3 text-primary border-bottom pb-2">Informações Gerais</h5>
                
                <div class="row mb-4">
                    <div class="col-md-12 mb-3">
                        <label for="titulo" class="form-label fw-bold">Título da Pesquisa *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" value="{{ old('titulo') }}" required placeholder="Ex: Pesquisa de Clima Q3 2026">
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="descricao" class="form-label fw-bold">Mensagem Introdutória (Opcional)</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2" placeholder="Ex: Sua opinião é muito importante. Gostaríamos de ouvir como tem sido sua experiência...">{{ old('descricao') }}</textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="data_inicio" class="form-label fw-bold">Data de Início *</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="{{ old('data_inicio', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="data_fim" class="form-label fw-bold">Data de Encerramento *</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" value="{{ old('data_fim', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="text-primary mb-0">Perguntas da Pesquisa</h5>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="addQuestion()">
                        <i class="fa-solid fa-plus me-1"></i> Adicionar Pergunta
                    </button>
                </div>

                <div id="questions-container">
                    {{-- Perguntas dinâmicas entrarão aqui via JS --}}
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fa-solid fa-save me-1"></i> Salvar como Rascunho
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template para nova pergunta (Oculto) --}}
<template id="question-template">
    <div class="card mb-3 border question-item shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
            <span class="fw-bold"><i class="fa-solid fa-grip-lines text-muted me-2" style="cursor: move;"></i> Pergunta <span class="q-number">#</span></span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-q" onclick="removeQuestion(this)"><i class="fa-solid fa-trash"></i></button>
        </div>
        <div class="card-body row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Texto da Pergunta *</label>
                <input type="text" class="form-control q-texto" required placeholder="Ex: Como você avalia a comunicação da liderança?">
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Tipo de Resposta *</label>
                <select class="form-select q-tipo" required>
                    <option value="nota">Nota (1 a 10)</option>
                    <option value="texto">Texto (Aberta)</option>
                </select>
            </div>

            <div class="col-md-6 mb-2">
                <label class="form-label">Categoria (Opcional - para gráficos)</label>
                <select class="form-select q-category">
                    <option value="">Sem Categoria</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</template>

<script>
    let questionCount = 0;

    function addQuestion() {
        const container = document.getElementById('questions-container');
        const template = document.getElementById('question-template');
        const clone = template.content.cloneNode(true);
        
        const index = questionCount;
        
        // Atualiza os names para o array do Laravel (ex: questions[0][texto])
        clone.querySelector('.q-texto').name = `questions[${index}][texto]`;
        clone.querySelector('.q-tipo').name = `questions[${index}][tipo]`;
        clone.querySelector('.q-category').name = `questions[${index}][category_id]`;
        clone.querySelector('.q-number').textContent = index + 1;
        
        container.appendChild(clone);
        questionCount++;
        updateQuestionNumbers();
    }

    function removeQuestion(btn) {
        btn.closest('.question-item').remove();
        updateQuestionNumbers();
    }

    function updateQuestionNumbers() {
        const items = document.querySelectorAll('.question-item');
        items.forEach((item, index) => {
            item.querySelector('.q-number').textContent = index + 1;
            // Re-indexa os inputs para garantir o submit array sem buracos
            item.querySelector('.q-texto').name = `questions[${index}][texto]`;
            item.querySelector('.q-tipo').name = `questions[${index}][tipo]`;
            item.querySelector('.q-category').name = `questions[${index}][category_id]`;
        });
        questionCount = items.length;
    }

    // Inicia com 1 pergunta obrigatória
    document.addEventListener('DOMContentLoaded', () => {
        addQuestion();
    });
</script>
@endsection
