@extends('layouts.app')

@section('title', 'Meus Resultados')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Meus Resultados</h1>
        <p style="color: #666; font-size: 0.95rem;">Acompanhe o seu desempenho e o feedback recebido nas avaliações que você participou.</p>
    </div>
</div>

<style>
    .filter-panel {
        background: linear-gradient(to right, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }
    
    .filter-label {
        display: block; 
        font-weight: 700; 
        color: #4a5568; 
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    
    .custom-select {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background-color: #ffffff;
        color: #2d3748;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1em;
    }
    
    .custom-select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }
    
    .btn-generate {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white; 
        border-radius: 10px; 
        padding: 12px 25px; 
        font-weight: 800; 
        height: 48px; 
        border: none; 
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
    }

    .empty-results-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 50px 30px;
        text-align: center;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        max-width: 650px;
        margin: 40px auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .empty-results-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.08);
    }
    
    .empty-results-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #ebf8ff, #bee3f8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: #3182ce;
        font-size: 2.2rem;
        animation: pulse-soft 2.5s infinite ease-in-out;
    }
    
    .empty-results-card h3 {
        color: #2d3748;
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: 10px;
        margin-top: 0;
    }
    
    .empty-results-card p {
        color: #4a5568;
        font-size: 1.02rem;
        font-weight: 600;
        margin-bottom: 8px;
        margin-top: 0;
        max-width: 500px;
    }
    
    .empty-results-card .sub-desc {
        color: #718096;
        font-size: 0.92rem;
        font-weight: 400;
        line-height: 1.6;
        margin-bottom: 25px;
        max-width: 520px;
    }
    
    .empty-results-footer {
        border-top: 1px solid #edf2f7;
        width: 100%;
        padding-top: 20px;
        display: flex;
        justify-content: center;
    }
    
    .empty-results-footer .tip {
        color: #4a5568;
        font-size: 0.85rem;
        background-color: #f7fafc;
        padding: 10px 18px;
        border-radius: 12px;
        border: 1px dashed #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 500px;
        text-align: left;
        line-height: 1.4;
    }
    
    .empty-results-footer .tip i {
        color: #dd6b20;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    @keyframes pulse-soft {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(49, 130, 206, 0.2);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(49, 130, 206, 0);
        }
    }
</style>

@if($avaliacoes->isEmpty())
    <div class="empty-results-card">
        <div class="empty-results-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <h3>Tudo pronto para a sua evolução!</h3>
        <p>Você ainda não possui nenhuma avaliação de desempenho finalizada.</p>
        <p class="sub-desc">Assim que a equipe de Recursos Humanos ou seu gestor encerrarem uma campanha de avaliação de desempenho na qual você participou, todo o seu relatório de competências, gráficos de evolução e PDIs serão liberados aqui para você acompanhar.</p>
        <div class="empty-results-footer">
            <span class="tip">
                <i class="fa-regular fa-lightbulb"></i> 
                <span><strong>Dica:</strong> Fique atento às comunicações da empresa e ao seu painel principal para saber quando novas campanhas forem abertas.</span>
            </span>
        </div>
    </div>
@else
    {{-- Seleção da Campanha --}}
    <div class="filter-panel">
        <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
            <div style="flex: 1; min-width: 250px;">
                <label class="filter-label"><i class="fa-solid fa-list-check" style="color: #3498db; margin-right: 5px;"></i> Selecione a Avaliação</label>
                <select id="avaliacao_id" class="custom-select">
                    <option value="">-- Selecione uma Avaliação --</option>
                    @foreach($avaliacoes as $av)
                        <option value="{{ $av->id }}">{{ $av->titulo }} ({{ \Carbon\Carbon::parse($av->data_inicio)->format('d/m/Y') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button onclick="buscarMeuRelatorio()" class="btn-generate">
                    <i class="fa-solid fa-chart-line"></i> Ver Resultados
                </button>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div id="loading" style="display: none; text-align: center; margin: 40px 0;">
        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; color: #3498db;"></i>
        <p style="color: #718096; margin-top: 10px;">Buscando seus resultados...</p>
    </div>

    <!-- Área do Relatório Injetado -->
    @include('avaliacoes.relatorios.partials.report_view')
@endif

@endsection

@push('scripts')
<script>
    async function buscarMeuRelatorio() {
        const avaliacaoId = document.getElementById('avaliacao_id').value;
        const colaboradorId = {{ auth()->id() }};

        if(!avaliacaoId) {
            alert('Selecione uma avaliação primeiro.');
            return;
        }

        document.getElementById('reportContent').style.display = 'none';
        document.getElementById('loading').style.display = 'block';

        try {
            const url = `{{ route('relatorios.dados') }}?avaliacao_id=${avaliacaoId}&colaborador_id=${colaboradorId}`;
            const res = await fetch(url);
            
            if(!res.ok) throw new Error('Erro no servidor');
            
            const data = await res.json();
            renderReport(data);

        } catch (error) {
            console.error(error);
            alert('Erro ao carregar dados: ' + error.message);
        } finally {
            document.getElementById('loading').style.display = 'none';
        }
    }
</script>
@endpush
