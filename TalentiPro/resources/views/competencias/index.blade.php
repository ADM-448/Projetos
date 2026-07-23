@extends('layouts.admin')

@section('title', 'Competências')

@section('content')

@push('styles')
<style>
    .table-list { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .table-list th { text-align: left; padding: 15px; background: #f8f9fa; color: #666; font-weight: 700; border-bottom: 2px solid #eee; }
    .table-list td { padding: 15px; border-bottom: 1px solid #eee; color: #333; vertical-align: middle; }
    .badge-skill { background: #eef2f7; color: #3b5998; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-right: 5px; display: inline-block; margin-bottom: 2px;}

    /* Modal Styles */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 2000; justify-content: center; align-items: center; }
    .modal-content { background-color: white; padding: 30px; border-radius: 10px; width: 600px; max-width: 95%; position: relative; animation: fadeIn 0.3s; }
    .close-modal { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #999; }
    .btn-modal-save { background-color: #3b5998; color: white; padding: 12px 0; border: none; width: 100%; border-radius: 5px; font-weight: bold; margin-top: 20px; cursor: pointer; font-size: 16px; }
    .modal-input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; margin-top: 5px; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

<div class="header-actions">
    <h1 class="page-title">COMPETÊNCIAS</h1>
    {{-- Botão agora leva para a página create --}}
    <a href="{{ route('competencias.create') }}" class="btn-primary">
        <i class="fa-solid fa-plus"></i> Nova Competência
    </a>
    <button class="btn-primary" onclick="abrirModalImportar()" style="background-color: #27ae60; margin-left: 10px;">
        <i class="fa-solid fa-file-csv"></i> Importar
    </button>
</div>

<div class="card-container">

    <table class="table-list">
        <thead>
            <tr>
                <th style="width: 30%;">Competência</th>
                <th style="width: 50%;">Skills (Critérios)</th>
                <th style="width: 20%; text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($competencias as $comp)
            <tr>
                <td data-label="Competência">
                    <strong>{{ $comp->nome }}</strong>
                    <div style="font-size:12px; color:#888;">{{ $comp->descricao }}</div>
                </td>
                <td data-label="Skills">
                    @foreach($comp->skills as $skill)
                        <span class="badge-skill" title="{{ $skill->descricao }}">{{ $skill->nome }}</span>
                    @endforeach
                </td>
                <td data-label="Ações" style="text-align: right;">
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 15px;">
                        <a href="{{ route('competencias.edit', $comp->id) }}" style="color:#3b5998; font-size:1.2em; transition: 0.2s;" onmouseover="this.style.color='#2d4373'" onmouseout="this.style.color='#3b5998'" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <form action="{{ route('competencias.destroy', $comp->id) }}" method="POST" style="margin: 0;" class="confirm-delete" data-confirm-message="Deseja excluir a competência '{{ $comp->nome }}'?">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none; border:none; cursor:pointer; color:#e74c3c; font-size:1.2em; transition: 0.2s;" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#e74c3c'" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center; padding:30px; color:#777;">Nenhuma competência cadastrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- Modal Importar --}}
<div id="modalImportar" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal" onclick="fecharModalImportar()">&times;</span>
        <h2 style="margin-bottom:20px;">Importar Competências</h2>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd; font-size: 14px; color: #444;">
            <p><strong>Instruções:</strong></p>
            <ul style="margin-left: 20px; list-style-type: disc;">
                <li>Arquivo <strong>CSV</strong> ou <strong>TXT</strong>.</li>
                <li>Colunas: <code>nome</code>, <code>descricao</code>, <code>skills</code>.</li>
                <li>Skills devem ser separadas por <strong>|</strong> (pipe).</li>
            </ul>
        </div>

        <button type="button" class="btn-modal-save" style="background-color: #7f8c8d; margin-bottom: 15px;" onclick="baixarModeloCompetencias()">
            <i class="fa-solid fa-download"></i> Baixar Modelo
        </button>

        <form action="{{ route('competencias.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Arquivo CSV</label>
            <input type="file" name="arquivo" accept=".csv,.txt" class="modal-input" required>
            
            <button type="submit" class="btn-modal-save" style="background-color: #27ae60;">
                <i class="fa-solid fa-upload"></i> Importar
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function abrirModalImportar() {
        document.getElementById('modalImportar').style.display = 'flex';
    }
    
    function fecharModalImportar() {
        document.getElementById('modalImportar').style.display = 'none';
    }

    function baixarModeloCompetencias() {
        const headers = ['nome', 'descricao', 'skills'];
        const rows = [
            ['Exemplo Competência', 'Descrição da competência aqui', 'Skill 1 | Skill 2 | Skill 3']
        ];
        
        let csvContent = "data:text/csv;charset=utf-8,\uFEFF" 
            + headers.join(";") + "\n" 
            + rows.map(e => e.join(";")).join("\n");
            
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "modelo_competencias.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    window.onclick = function(event) {
        if (event.target == document.getElementById('modalImportar')) {
            fecharModalImportar();
        }
    }
</script>
@endpush
