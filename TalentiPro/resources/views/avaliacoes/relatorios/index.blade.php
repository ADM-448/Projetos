@extends('layouts.app')

@section('title', 'Relatórios de Avaliações')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <div class="header-actions">
        <div>
            <h1 class="page-title">Relatórios Individuais</h1>
            <p style="color: #64748b; font-size: 0.95rem;">Visualize o desempenho consolidado dos colaboradores em suas
                avaliações.</p>
        </div>
    </div>

    <style>
        .filter-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            position: relative;
            overflow: visible;
        }

        .filter-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
        }

        .filter-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 12px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #f1f5f9;
        }

        .custom-select {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1.2rem center;
            background-size: 1.2em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .custom-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .custom-select:disabled {
            background-color: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-generate {
            background-color: #6b46c1;
            /* Roxo tema */
            color: white;
            border-radius: 12px;
            padding: 0 28px;
            font-weight: 700;
            height: 52px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(107, 70, 193, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            width: 100%;
        }

        .btn-generate:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(107, 70, 193, 0.5);
            background-color: #553c9a;
        }

        .filter-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 24px;
            align-items: flex-end;
        }

        @media (max-width: 768px) {
            .filter-wrapper {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .filter-panel {
                padding: 20px;
            }

            .custom-select {
                text-overflow: ellipsis;
                max-width: 100%;
            }
        }
    </style>

    {{-- Painel de Filtros --}}
    <div class="filter-panel">
        <div class="filter-wrapper">

            <div>
                <label class="filter-label">
                    <div class="filter-icon-box" style="color: #3b82f6;"><i class="fa-solid fa-layer-group"></i></div>
                    Campanha / Avaliação
                </label>
                <select id="avaliacao_id" class="custom-select" onchange="carregarAlvos(this.value)">
                    <option value="">-- Selecione uma Avaliação --</option>
                    @foreach($avaliacoes as $av)
                        <option value="{{ $av->id }}">{{ $av->titulo }}
                            ({{ \Carbon\Carbon::parse($av->data_inicio)->format('d/m/Y') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="filter-label">
                    <div class="filter-icon-box" style="color: #8b5cf6;"><i class="fa-solid fa-user-astronaut"></i></div>
                    Colaborador Avaliado
                </label>
                <select id="colaborador_id" class="custom-select" disabled>
                    <option value="">-- Selecione primeiro a avaliação --</option>
                </select>
            </div>

            <div>
                <button onclick="buscarRelatorio()" class="btn-generate">
                    <i class="fa-solid fa-chart-pie"></i> Carregar Relatório
                </button>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div id="loading" style="display: none; text-align: center; margin: 60px 0;">
        <div style="display: inline-block; position: relative; width: 80px; height: 80px;">
            <div
                style="position: absolute; top: 33px; width: 13px; height: 13px; border-radius: 50%; background: #6366f1; animation: loading-dots 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite; left: 8px;">
            </div>
            <div
                style="position: absolute; top: 33px; width: 13px; height: 13px; border-radius: 50%; background: #8b5cf6; animation: loading-dots 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite; left: 32px; animation-delay: -0.24s;">
            </div>
            <div
                style="position: absolute; top: 33px; width: 13px; height: 13px; border-radius: 50%; background: #ec4899; animation: loading-dots 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite; left: 56px; animation-delay: -0.48s;">
            </div>
        </div>
        <p style="color: #64748b; margin-top: 15px; font-weight: 500; letter-spacing: 0.5px;">Processando inteligência de
            dados...</p>
    </div>

    <style>
        @keyframes loading-dots {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1);
            }

            100% {
                transform: scale(0);
            }
        }
    </style>

    <!-- Área do Relatório Injetado -->
    @include('avaliacoes.relatorios.partials.report_view')

@endsection

@push('scripts')
    <script>
        async function carregarAlvos(avaliacaoId) {
            const selectColaborador = document.getElementById('colaborador_id');
            selectColaborador.innerHTML = '<option value="">Carregando...</option>';
            selectColaborador.disabled = true;

            document.getElementById('reportContent').style.display = 'none';

            if (!avaliacaoId) {
                selectColaborador.innerHTML = '<option value="">-- Selecione primeiro a avaliação --</option>';
                return;
            }

            try {
                const res = await fetch(`/api/relatorios/alvos/${avaliacaoId}`);
                if (!res.ok) throw new Error('Erro ao buscar colaboradores');
                const data = await res.json();

                selectColaborador.innerHTML = '<option value="">-- Selecione o Colaborador --</option>';
                data.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    opt.textContent = user.name;
                    selectColaborador.appendChild(opt);
                });

                selectColaborador.disabled = false;
            } catch (e) {
                console.error(e);
                alert('Erro ao carregar colaboradores.');
            }
        }

        async function buscarRelatorio() {
            const avaliacaoId = document.getElementById('avaliacao_id').value;
            const colaboradorId = document.getElementById('colaborador_id').value;

            if (!avaliacaoId || !colaboradorId) {
                alert('Selecione a campanha e o colaborador!');
                return;
            }

            document.getElementById('reportContent').style.display = 'none';
            document.getElementById('loading').style.display = 'block';

            try {
                const url = `{{ route('relatorios.dados') }}?avaliacao_id=${avaliacaoId}&colaborador_id=${colaboradorId}`;
                const res = await fetch(url);

                if (!res.ok) throw new Error('Erro no servidor');

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