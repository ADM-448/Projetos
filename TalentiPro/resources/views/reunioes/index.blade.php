@extends('layouts.app')

@section('title', 'Reuniões de Feedback 1:1')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-video" style="color: var(--primary-color);"></i> Reuniões de Feedback 1:1</h1>
        <p style="color: #666; font-size: 0.95rem;">Suas reuniões agendadas com inteligência artificial.</p>
    </div>
    
    @if(auth()->user()->empresa->configuracaoGoogle)
        <div class="reunioes-btn-group" style="display: flex; gap: 10px; align-items: center;">
            <form action="{{ route('google.disconnect') }}" method="POST" style="margin: 0; display: inline-block;">
                @csrf
                <button type="submit" class="btn-danger" style="text-decoration: none; width: 100%; justify-content: center;">Desconectar Google</button>
            </form>
            <a href="{{ route('reunioes.create') }}" class="btn-primary" style="text-decoration: none; display: inline-flex; justify-content: center; align-items: center; gap: 6px;"><i class="fa-solid fa-plus"></i> Agendar Reunião</a>
        </div>
    @else
        <div>
            <a href="{{ route('google.login') }}" class="btn-primary" style="text-decoration: none; background-color: #4285F4;">
                <i class="fa-brands fa-google"></i> Conectar Google Calendar
            </a>
        </div>
    @endif
</div>

<div class="card" style="padding: 20px;">
    @if(auth()->user()->empresa->configuracaoGoogle)
        <div style="background-color: #f0fdf4; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
            <i class="fa-solid fa-check-circle"></i> Sua conta Google está conectada. Você pode agendar reuniões com geração automática de links do Google Meet!
        </div>
    @else
        <div style="background-color: #fffbeb; color: #92400e; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #f59e0b;">
            <i class="fa-solid fa-triangle-exclamation"></i> Conecte sua conta do Google para permitir o agendamento automático de links do Meet e transcrições com a IA.
        </div>
    @endif

    <style>
        .table-reunioes { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-reunioes th, .table-reunioes td { padding: 12px; text-align: left; }
        .table-reunioes th { color: #666; font-weight: 600; border-bottom: 2px solid #eee; }
        .table-reunioes tr { border-bottom: 1px solid #eee; }
        @media (max-width: 768px) {
            .table-reunioes, .table-reunioes tbody, .table-reunioes tr, .table-reunioes td { display: block; width: 100%; }
            .table-reunioes thead { display: none; }
            .table-reunioes tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
            .table-reunioes td { text-align: right; padding: 10px 0 10px 45%; border-bottom: 1px solid #eee; position: relative; min-height: 40px; }
            .table-reunioes td:last-child { border-bottom: 0; display: flex; justify-content: flex-end; padding-left: 0; }
            .table-reunioes td::before { content: attr(data-label); position: absolute; left: 0; font-weight: 600; color: #666; text-align: left; max-width: 40%; }
        }
    </style>

    <div style="margin-bottom: 20px; display: flex; align-items: center; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 5px 15px;">
        <i class="fa-solid fa-search" style="color: #999; margin-right: 10px;"></i>
        <input type="text" id="pesquisaReunioes" placeholder="Buscar por assunto ou participante..." style="border: none; outline: none; width: 100%; padding: 10px 0; font-size: 0.95rem;">
    </div>

    <table class="table-reunioes" id="tabelaReunioes">
        <thead>
            <tr>
                <th>Data / Hora</th>
                <th>Participante</th>
                <th>Assunto</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agendamentos as $reuniao)
                <tr class="reuniao-row">
                    <td data-label="Data / Hora">{{ $reuniao->data_hora->format('d/m/Y H:i') }}</td>
                    <td data-label="Participante" class="reuniao-participante">
                        @if(auth()->id() === $reuniao->gestor_id)
                            {{ $reuniao->participantes->pluck('name')->join(', ') }} (Convidados)
                        @else
                            {{ $reuniao->gestor->name }} (Organizador)
                        @endif
                    </td>
                    <td data-label="Assunto" class="reuniao-assunto">{{ $reuniao->titulo }}</td>
                    <td data-label="Status">
                        @if($reuniao->status === 'agendado')
                            <span class="badge" style="background: #e0f2fe; color: #0284c7;">Agendado</span>
                        @elseif($reuniao->status === 'concluido')
                            <span class="badge" style="background: #dcfce7; color: #166534;">Concluído</span>
                        @else
                            <span class="badge" style="background: #f1f5f9; color: #475569;">{{ ucfirst($reuniao->status) }}</span>
                        @endif
                    </td>
                    <td data-label="Ações">
                        <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-end;">
                            @if($reuniao->status === 'agendado' || $reuniao->status === 'em_andamento')
                                @if(auth()->id() !== $reuniao->gestor_id && $reuniao->google_meet_link)
                                    <a href="{{ $reuniao->google_meet_link }}" target="_blank" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; background: #4285F4; border: none;"><i class="fa-solid fa-video"></i> Entrar na Sala</a>
                                @else
                                    <a href="{{ route('reunioes.sala', $reuniao) }}" 
                                       @if(auth()->id() === $reuniao->gestor_id && $reuniao->google_meet_link)
                                           onclick="window.open('{{ $reuniao->google_meet_link }}', '_blank')"
                                       @endif
                                       class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem;"><i class="fa-solid fa-microphone"></i> Entrar na Sala</a>
                                @endif
                            @elseif($reuniao->status === 'concluido')
                                <a href="{{ route('reunioes.show', $reuniao) }}" class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;"><i class="fa-solid fa-file-lines"></i> Ver Resumo da IA</a>
                            @endif

                            @if(auth()->id() === $reuniao->gestor_id || in_array(auth()->user()->perfil_acesso->value ?? '', ['master', 'admin']))
                                <form action="{{ route('reunioes.destroy', $reuniao) }}" method="POST" style="margin: 0;" data-confirm="Tem certeza que deseja excluir esta reunião e todos os registros associados?" data-confirm-title="Excluir Reunião">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding: 6px 10px; font-size: 0.85rem; border-radius: 6px; background-color: #ef4444; border: none; cursor: pointer; color: white;" title="Excluir Reunião">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: #666;">
                        Nenhuma reunião agendada no momento.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('pesquisaReunioes');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('.reuniao-row');
            
            rows.forEach(row => {
                let assunto = row.querySelector('.reuniao-assunto').textContent.toLowerCase();
                let participante = row.querySelector('.reuniao-participante').textContent.toLowerCase();
                
                if (assunto.includes(filter) || participante.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection
