@extends('layouts.app')

@section('title', 'Editar Empresa (Tenant)')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
<div class="header-actions">
    <div>
        <h1 class="page-title">Editar Empresa: {{ $empresa->nome }}</h1>
        <p style="color: #666; font-size: 0.95rem;">Atualize os dados cadastrais e o status da assinatura.</p>
    </div>
    <div>
        <a href="{{ route('admin.empresas.index') }}" class="btn-secondary" style="text-decoration: none;">Voltar</a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px;">
    
    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #e53e3e;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.empresas.update', $empresa) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Logotipo e Identidade Visual (White-Label) --}}
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
            <h3 style="font-size: 1.1rem; color: #1e293b; margin-top: 0; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-palette" style="color: #6366f1;"></i> Identidade Visual & White-Label
            </h3>

            {{-- Logotipo --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 600; color: #334155;">
                    <i class="fa-solid fa-image"></i> Logotipo da Empresa
                </label>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px; margin-bottom: 10px;">
                    Exibido no topo do menu lateral e nos relatórios de avaliação impressos e em PDF. (Máx: 3MB)
                </p>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    @if($empresa->logo)
                        <div id="currentLogoContainer" style="display: flex; align-items: center; gap: 15px; background: white; padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo {{ $empresa->nome }}" 
                                 onerror="this.onerror=null; document.getElementById('currentLogoContainer').style.display='none';"
                                 style="max-height: 50px; max-width: 140px; object-fit: contain;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #dc2626; cursor: pointer; font-weight: 600;">
                                <input type="checkbox" name="remover_logo" value="1"> Remover logo atual
                            </label>
                        </div>
                    @endif

                    <div style="flex: 1; min-width: 240px;">
                        <input type="file" id="logo" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="padding: 9px 12px;">
                    </div>
                </div>
            </div>

            {{-- Cores do Sistema (Degradê do Tema) --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" for="cor_primaria" style="font-weight: 600; color: #334155;">
                        Cor Primária (Início do degradê da sidebar / topo)
                    </label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="color" id="cor_primaria_picker" value="{{ old('cor_primaria', $empresa->cor_primaria ?? '#6a4c7c') }}" style="width: 45px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;" onchange="document.getElementById('cor_primaria').value = this.value">
                        <input type="text" id="cor_primaria" name="cor_primaria" class="form-control" value="{{ old('cor_primaria', $empresa->cor_primaria ?? '#6a4c7c') }}" placeholder="#6a4c7c" onchange="document.getElementById('cor_primaria_picker').value = this.value">
                    </div>
                    <small style="color: #64748b;">Ex: #e67e22 (Fielmart - Laranja), #800020 (Intalenti - Bordô), #6a4c7c (TalentiPro)</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cor_secundaria" style="font-weight: 600; color: #334155;">
                        Cor Secundária (Fim do degradê)
                    </label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="color" id="cor_secundaria_picker" value="{{ old('cor_secundaria', $empresa->cor_secundaria ?? '#8b5cf6') }}" style="width: 45px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;" onchange="document.getElementById('cor_secundaria').value = this.value">
                        <input type="text" id="cor_secundaria" name="cor_secundaria" class="form-control" value="{{ old('cor_secundaria', $empresa->cor_secundaria ?? '#8b5cf6') }}" placeholder="#8b5cf6" onchange="document.getElementById('cor_secundaria_picker').value = this.value">
                    </div>
                    <small style="color: #64748b;">Tom secundário para transição suave no menu.</small>
                </div>
            </div>

            {{-- Marca D'água Personalizada --}}
            <div style="border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                <label class="form-label" style="font-weight: 600; color: #334155;">
                    <i class="fa-solid fa-stamp"></i> Marca d'água no Fundo do Ecrã
                </label>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 2px; margin-bottom: 10px;">
                    Imagem ou texto sutil aplicado como marca d'água de fundo em todo o sistema para reforçar a identidade visual do cliente.
                </p>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 15px;">
                    @if($empresa->marca_dagua)
                        <div style="display: flex; align-items: center; gap: 15px; background: white; padding: 10px 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <img src="{{ asset('storage/' . $empresa->marca_dagua) }}" alt="Marca d'água" style="max-height: 40px; max-width: 100px; object-fit: contain; opacity: 0.6;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #dc2626; cursor: pointer; font-weight: 600;">
                                <input type="checkbox" name="remover_marca_dagua" value="1"> Remover imagem de marca d'água
                            </label>
                        </div>
                    @endif

                    <div style="flex: 1; min-width: 240px;">
                        <input type="file" id="marca_dagua" name="marca_dagua" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="padding: 9px 12px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="texto_marca_dagua" style="font-weight: 600; color: #334155;">Texto Alternativo de Marca d'água (opcional)</label>
                    <input type="text" id="texto_marca_dagua" name="texto_marca_dagua" class="form-control" value="{{ old('texto_marca_dagua', $empresa->texto_marca_dagua) }}" placeholder="Ex: FIELMART - CONFIDENCIAL">
                    <small style="color: #64748b;">Utilizado caso não haja imagem de marca d'água cadastrada.</small>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="nome">Nome Fantasia <span style="color: red;">*</span></label>
            <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome', $empresa->nome) }}" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="cnpj">CNPJ <span style="color: red;">*</span></label>
                <input type="text" id="cnpj" name="cnpj" class="form-control" value="{{ old('cnpj', $empresa->cnpj) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="razao_social">Razão Social</label>
                <input type="text" id="razao_social" name="razao_social" class="form-control" value="{{ old('razao_social', $empresa->razao_social) }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="email">E-mail de Contato <span style="color: red;">*</span></label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" class="form-control" value="{{ old('telefone', $empresa->telefone) }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="max_usuarios">Limite de Usuários <span style="color: red;">*</span></label>
                <input type="number" id="max_usuarios" name="max_usuarios" class="form-control" value="{{ old('max_usuarios', $empresa->max_usuarios) }}" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="ativa" {{ old('status', $empresa->status->value ?? $empresa->status) == 'ativa' ? 'selected' : '' }}>Ativa</option>
                    <option value="suspensa" {{ old('status', $empresa->status->value ?? $empresa->status) == 'suspensa' ? 'selected' : '' }}>Suspensa</option>
                </select>
                <small style="color: #666;">Se suspensa, todos os usuários desta empresa perderão o acesso ao sistema.</small>
            </div>
        </div>

        <div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
            <h3 style="font-size: 1.1rem; color: #4338ca; margin-bottom: 15px;"><i class="fa-solid fa-robot"></i> Configurações de Inteligência Artificial</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="ia_ativa">Acesso à IA (Transcrições e Resumos)</label>
                    <select id="ia_ativa" name="ia_ativa" class="form-control">
                        <option value="1" {{ old('ia_ativa', $empresa->ia_ativa) ? 'selected' : '' }}>Liberado</option>
                        <option value="0" {{ old('ia_ativa', $empresa->ia_ativa) ? '' : 'selected' }}>Bloqueado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="whatsapp_ativo">Acesso à API WhatsApp (Notificações)</label>
                    <select id="whatsapp_ativo" name="whatsapp_ativo" class="form-control">
                        <option value="1" {{ old('whatsapp_ativo', $empresa->whatsapp_ativo) ? 'selected' : '' }}>Liberado</option>
                        <option value="0" {{ old('whatsapp_ativo', $empresa->whatsapp_ativo) ? '' : 'selected' }}>Bloqueado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="ia_limite_transcricoes_mes">Limite de Transcrições (Mês)</label>
                    <input type="number" id="ia_limite_transcricoes_mes" name="ia_limite_transcricoes_mes" class="form-control" value="{{ old('ia_limite_transcricoes_mes', $empresa->ia_limite_transcricoes_mes) }}" min="0">
                </div>
            </div>

            <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <strong style="color: #334155;">Consumo no mês atual:</strong>
                    <span style="color: {{ $empresa->ia_transcricoes_utilizadas >= $empresa->ia_limite_transcricoes_mes ? '#dc2626' : '#10b981' }}; font-weight: bold; margin-left: 10px;">
                        {{ $empresa->ia_transcricoes_utilizadas }} / {{ $empresa->ia_limite_transcricoes_mes }}
                    </span>
                </div>
                @if($empresa->ia_limite_reset_em)
                    <div style="font-size: 0.85rem; color: #64748b;">Próximo reset: {{ \Carbon\Carbon::parse($empresa->ia_limite_reset_em)->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-primary" style="padding: 12px 24px; font-size: 1rem;">
                <i class="fa-solid fa-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
