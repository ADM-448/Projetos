@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : 'layouts.gestor')
@section('title', 'Reuniões de Feedback (IA)')

@push('styles')
<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .page-title { font-size: 22px; font-weight: 700; color: #333; margin: 0; }
    
    .btn-action { background-color: #3b5998; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 14px; transition: 0.2s; display: inline-block; }
    .btn-action:hover { background-color: #2d4373; color: white; text-decoration: none; }
    .btn-secondary { background-color: #6c757d; color: white; }
    .btn-secondary:hover { background-color: #5a6268; color: white; text-decoration: none; }
    
    .list-card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0; }
    
    .table-reunioes { width: 100%; border-collapse: collapse; font-size: 14px; }
    .table-reunioes th { background-color: #f8f9fa; color: #666; font-weight: 600; text-transform: uppercase; font-size: 12px; padding: 15px 12px; border-bottom: 2px solid #eaeaea; text-align: left; }
    .table-reunioes td { padding: 16px 12px; border-bottom: 1px solid #eaeaea; color: #444; vertical-align: middle; }
    .table-reunioes tr:last-child td { border-bottom: none; }
    .table-reunioes tr:hover { background-color: #fafafa; }
    
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
    .status-agendado { background: #fff3cd; color: #856404; }
    .status-concluido { background: #d4edda; color: #155724; }
    .status-em_andamento { background: #cce5ff; color: #004085; }
    
    .btn-sm { font-size: 12px; padding: 6px 12px; border-radius: 4px; font-weight: 600; text-decoration: none; display: inline-block; border: none; cursor: pointer; transition: 0.2s; }
    .btn-meet { background-color: #28a745; color: white; }
    .btn-meet:hover { background-color: #218838; color: white; text-decoration: none; }
    .btn-extract { background-color: #ffc107; color: #333; }
    .btn-extract:hover { background-color: #e0a800; color: #333; text-decoration: none; }
    .btn-view { background-color: #e9ecef; color: #333; border: 1px solid #ccc; }
    .btn-view:hover { background-color: #dde0e3; color: #333; text-decoration: none; }
    
    .btn-icon { background: none; border: none; font-size: 16px; cursor: pointer; color: #dc3545; transition: 0.2s; padding: 0 5px; }
    .btn-icon:hover { transform: scale(1.15); color: #bd2130; }
    
    .actions-flex { display: flex; gap: 8px; align-items: center; }
</style>
@endpush

@section('content')

<div class="page-content" style="padding: 40px; max-width: 1100px; margin: 0 auto;">
    
    <div class="header-actions">
        <h1 class="page-title">Histórico e Agenda de Reuniões</h1>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('reunioes.create') }}" class="btn-action btn-secondary">+ Lançar Antiga Manualmente</a>
            <button class="btn-action" onclick="document.getElementById('modalAgendamento').style.display='flex'">+ Agendar Reunião</button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
            {{ session('warning') }}
        </div>
    @endif

    @php
        $googleConfig = \App\Models\ConfiguracaoGoogle::where('empresa_id', auth()->user()->empresa_id ?? 1)->first();
    @endphp
    <div style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="flex: 1;">
            <h3 style="margin-top: 0; font-size: 16px; color: #6c757d; display: flex; align-items: center; gap: 8px;">
                <i class="fa-brands fa-google"></i> Integração Google da Empresa
            </h3>
            <p style="font-size: 13px; color: #555; margin: 0;">A conta Google da empresa é usada para gerar links do Meet automaticamente para todos os colaboradores.</p>
        </div>
        <div style="text-align: right; white-space: nowrap;">
            @if($googleConfig && (!empty($googleConfig->service_account_json) || !empty($googleConfig->access_token)))
                <span style="color: #28a745; font-weight: bold; font-size: 14px;"><i class="fa-solid fa-check-circle"></i> Integração Ativa</span>
            @else
                <span style="color: #e67e22; font-weight: bold; font-size: 14px;"><i class="fa-solid fa-triangle-exclamation"></i> Não Configurada</span>
                @if(in_array(auth()->user()->perfil_acesso, ['admin', 'master']))
                    <br><a href="{{ route('configuracoes.google') }}" style="color: #3b5998; font-size: 13px; text-decoration: underline; margin-top: 5px; display: inline-block;">Configurar agora</a>
                @endif
            @endif
        </div>
    </div>

    <div class="list-card">
        <table class="table-reunioes">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Título</th>
                    <th>Com quem</th>
                    <th>Gestor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lista as $item)
                <tr>
                    <td data-label="Data/Hora">{{ $item->data_hora->format('d/m/Y H:i') }}</td>
                    <td data-label="Título" style="font-weight: 500; color: #222;">{{ $item->titulo }}</td>
                    <td data-label="Com quem">{{ $item->com_quem }}</td>
                    <td data-label="Gestor">{{ $item->gestor_nome }}</td>
                    <td data-label="Status">
                        <span class="status-badge status-{{ $item->status }}">
                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                        </span>
                    </td>
                    <td data-label="Ações">
                        <div class="actions-flex">
                            @if($item->status === 'concluido')
                                @if($item->feedback_id)
                                    <a href="{{ route('reunioes.show', $item->feedback_id) }}" class="btn-sm btn-view">Ver Detalhes</a>
                                @endif
                            @else
                                @if($item->link_meet)
                                    <a href="{{ $item->link_meet }}" target="_blank" class="btn-sm btn-meet" title="Entrar no Meet">🎥 Meet</a>
                                @endif
                                @if($item->gestor_id === auth()->id())
                                    <a href="{{ route('reunioes.create', ['agendamento_id' => $item->id]) }}" class="btn-sm btn-extract">Extrair Transcrição</a>
                                @endif
                            @endif
                            
                            @if($item->gestor_id === auth()->id() || in_array(auth()->user()->perfil_acesso, ['admin', 'master']))
                                <form action="{{ $item->tipo === 'agendamento' ? route('reunioes.agendamento.destroy', $item->id) : route('reunioes.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta reunião e seus registros?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Deletar">🗑️</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                        Nenhuma reunião encontrada no histórico ou na agenda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agendamento -->
<div id="modalAgendamento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(4px);">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 450px; max-width: 95%; box-sizing: border-box; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 style="margin-top: 0; font-size: 1.5rem; color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;">🗓️ Agendar Reunião</h2>
        <form method="POST" action="{{ route('reunioes.agendar') }}">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Título da Reunião</label>
                <input type="text" name="titulo" class="form-control" placeholder="Ex: Feedback Trimestral" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b5998'" onblur="this.style.borderColor='#ccc'">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Colaborador</label>
                <select name="colaborador_id" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; background-color: #fff; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b5998'" onblur="this.style.borderColor='#ccc'">
                    <option value="">Selecione um colaborador...</option>
                    @foreach($users as $u)
                        @if($u->id !== auth()->id())
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Data e Hora</label>
                <input type="datetime-local" name="data_hora" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b5998'" onblur="this.style.borderColor='#ccc'">
            </div>
            
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button type="button" style="background: #e9ecef; color: #495057; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; flex: 1; transition: background 0.2s;" onclick="document.getElementById('modalAgendamento').style.display='none'" onmouseover="this.style.background='#dde0e3'" onmouseout="this.style.background='#e9ecef'">Cancelar</button>
                <button type="submit" style="background: #3b5998; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer; flex: 1; transition: background 0.2s;" onmouseover="this.style.background='#2d4373'" onmouseout="this.style.background='#3b5998'">💾 Salvar Agendamento</button>
            </div>
        </form>
    </div>
</div>
@endsection
