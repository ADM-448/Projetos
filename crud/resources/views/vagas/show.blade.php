@extends('layouts.app')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('vagas.index') }}" class="btn btn-secondary">← Voltar para lista</a>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('vagas.edit', $vaga) }}" class="btn btn-primary">Editar Vaga</a>
            <form action="{{ route('vagas.destroy', $vaga) }}" method="POST" onsubmit="return confirm('Excluir esta vaga?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Excluir</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
            <div>
                <h2 style="margin: 0 0 0.5rem 0; font-size: 1.6rem;">{{ $vaga->titulo }}</h2>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    @if ($vaga->ativa)
                        <span class="badge badge-active">Ativa</span>
                    @else
                        <span class="badge badge-inactive">Inativa</span>
                    @endif

                    @if ($vaga->isPremium())
                        <span class="badge badge-premium">⭐ Vaga Premium</span>
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 1.25rem; font-weight: 700; color: var(--success-color);">
                    @if ($vaga->salario)
                        R$ {{ number_format($vaga->salario, 2, ',', '.') }}
                    @else
                        Salário a combinar
                    @endif
                </span>
            </div>
        </div>

        <hr style="border-color: var(--border-color); margin: 1.5rem 0;">

        <div>
            <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Descrição da Vaga:</h4>
            <p style="line-height: 1.6; white-space: pre-line;">{{ $vaga->descricao }}</p>
        </div>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-muted);">
            Criado em {{ $vaga->created_at ? $vaga->created_at->format('d/m/Y H:i') : 'N/A' }}
        </div>
    </div>
</div>
@endsection
