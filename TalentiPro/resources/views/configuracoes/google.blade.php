@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : 'layouts.gestor')
@section('title', 'Configurações Google')
@section('content')

<div class="page-content" style="padding: 40px; max-width: 800px; margin: 0 auto;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0;">Integração Google da Empresa</h2>
        <p style="color: #666; margin-bottom: 25px;">Configure a conta Google que será usada por <strong>todos os colaboradores</strong> para gerar links do Google Meet automaticamente ao agendar reuniões. Esta configuração é feita uma única vez pelo administrador.</p>

        @if(session('success'))
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Status atual --}}
        <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
            <div style="flex: 1;">
                <strong>Status da Integração:</strong>
            </div>
            <div>
                @if(isset($config) && ($config->access_token || !empty($config->service_account_json)))
                    <span style="color: #28a745; font-weight: bold; font-size: 15px;">
                        <i class="fa-solid fa-check-circle"></i> Ativa e Funcionando
                    </span>
                @else
                    <span style="color: #dc3545; font-weight: bold; font-size: 15px;">
                        <i class="fa-solid fa-circle-xmark"></i> Não Configurada
                    </span>
                @endif
            </div>
        </div>

        {{-- Método OAuth --}}
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-bottom: 25px;">
            <h3 style="margin-top: 0; font-size: 17px;">
                <i class="fa-brands fa-google" style="color: #db4437;"></i>
                Método 1: Vincular Conta Google (Recomendado)
            </h3>
            <p style="color: #555; font-size: 14px; margin-bottom: 20px;">
                Conecte uma conta Google da empresa (ex: <code>rh@suaempresa.com.br</code>) de forma segura com login e senha. Após vincular uma vez, todos os colaboradores podem agendar reuniões automaticamente.
            </p>

            @if(isset($config) && $config->access_token)
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <span style="color: #28a745; font-weight: bold;">
                        <i class="fa-solid fa-check-circle"></i> Conta Google Conectada
                    </span>
                    <form action="{{ route('google.unlink.empresa') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Tem certeza que deseja desvincular a conta Google da empresa?')">
                        @csrf
                        <button type="submit" style="background: none; border: 1px solid #dc3545; color: #dc3545; padding: 6px 15px; border-radius: 5px; cursor: pointer; font-size: 13px;">
                            <i class="fa-solid fa-unlink"></i> Desvincular
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('google.redirect', ['tipo' => 'empresa']) }}" 
                   style="display: inline-block; background-color: #db4437; color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 15px;">
                    <i class="fa-brands fa-google"></i> Vincular Conta Google da Empresa
                </a>
                <p style="font-size: 12px; color: #888; margin-top: 10px; margin-bottom: 0;">
                    Será aberta uma tela de login do Google. Use a conta que a empresa quer usar para as reuniões.
                </p>
            @endif
        </div>

        {{-- Método Service Account --}}
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 25px;">
            <h3 style="margin-top: 0; font-size: 17px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;"
                onclick="var el = document.getElementById('sa-body'); el.style.display = el.style.display === 'none' ? 'block' : 'none'">
                <span>
                    <i class="fa-solid fa-robot" style="color: #6c757d;"></i>
                    Método 2: Service Account (JSON) — Técnico
                </span>
                <i class="fa-solid fa-chevron-down" style="color: #999; font-size: 14px;"></i>
            </h3>
            
            <div id="sa-body" style="display: {{ (isset($config) && !empty($config->service_account_json)) ? 'block' : 'none' }};">
                <p style="color: #555; font-size: 14px; margin-bottom: 15px;">
                    Para uso com Google Workspace corporativo. Requer criar uma "Conta de Serviço" no Google Cloud Console e colar o JSON aqui.
                </p>

                {{-- Tutorial --}}
                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 13px; color: #3b5998; cursor: pointer;"
                        onclick="var t = document.getElementById('tut'); t.style.display = t.style.display === 'none' ? 'block' : 'none'">
                        <i class="fa-solid fa-circle-question"></i> Como obter o JSON? (Passo a Passo)
                    </h4>
                    <div id="tut" style="display: none;">
                        <ol style="margin: 0; padding-left: 20px; color: #444; font-size: 13px; line-height: 1.8;">
                            <li>Acesse o <a href="https://console.cloud.google.com/" target="_blank" style="color: #3b5998;">Google Cloud Console</a> e crie um projeto.</li>
                            <li>Em <strong>APIs e Serviços &gt; Biblioteca</strong>, ative <strong>Google Calendar API</strong> e <strong>Google Meet API</strong>.</li>
                            <li>Em <strong>APIs e Serviços &gt; Credenciais</strong>, clique em <strong>+ Criar Credenciais &gt; Conta de Serviço</strong>.</li>
                            <li>Dê um nome, conclua a criação, clique na conta criada &gt; aba <strong>CHAVES</strong> &gt; <strong>Adicionar Chave &gt; JSON</strong>.</li>
                            <li>Baixe o arquivo JSON e cole o conteúdo abaixo.</li>
                        </ol>
                    </div>
                </div>

                <form action="{{ route('configuracoes.google.store') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px;">JSON da Service Account</label>
                        <textarea name="service_account_json" rows="10" 
                            placeholder='{"type": "service_account", "project_id": "...", ...}'
                            style="width: 100%; padding: 10px; font-family: monospace; font-size: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">{{ old('service_account_json', $config->service_account_json ?? '') }}</textarea>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px;">ID da Pasta no Google Drive (Opcional)</label>
                        <input type="text" name="drive_folder_id" 
                            value="{{ old('drive_folder_id', $config->drive_folder_id ?? '') }}"
                            placeholder="Ex: 1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs"
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                        <small style="color: #888; font-size: 12px;">Se informado, as gravações serão salvas nesta pasta do Drive.</small>
                    </div>
                    <div style="text-align: right;">
                        <button type="submit" style="background-color: #3b5998; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px;">
                            <i class="fa-solid fa-save"></i> Salvar JSON
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
