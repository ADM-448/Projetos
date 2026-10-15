@extends('layouts.app')

@section('title', 'Colaboradores')
@section('body-class', 'has-sidebar')

@section('sidebar')
    @include('layouts.partials.sidebar')
@endsection

@section('content')
    <div class="header-actions">
        <div>
            <h1 class="page-title" style="margin-bottom: 5px;">Gestão de Colaboradores</h1>
            <p style="color: #666; font-size: 0.95rem;">Gerencie o quadro de colaboradores da empresa, atribua cargos, setores e defina as permissões de acesso.</p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" id="btnBulkDelete" onclick="confirmBulkDelete()" class="btn-danger" style="display: none; align-items: center; gap: 8px; white-space: nowrap; padding: 10px 15px; font-size: 0.95rem; border-radius: 8px;">
                <i class="fa-solid fa-trash"></i> Excluir (<span id="bulkDeleteCount">0</span>)
            </button>
            <button onclick="openImportModal()" class="btn-secondary" style="background-color: #27ae60; color: white; border-color: #27ae60;">
                <i class="fa-solid fa-file-excel"></i> Importar via Planilha
            </button>
            <button onclick="openCreateModal()" class="btn-primary">
                <i class="fa-solid fa-user-plus"></i> Novo Colaborador
            </button>
        </div>
    </div>

    @php
        $empresa = auth()->user()->empresa;
        $totalUsuarios = \App\Models\User::where('empresa_id', $empresa->id)->count();
        $limiteUsuarios = $empresa->max_usuarios;
        $porcentagemUso = $limiteUsuarios > 0 ? round(($totalUsuarios / $limiteUsuarios) * 100) : 0;
        $estaNoLimite = $totalUsuarios >= $limiteUsuarios;
    @endphp

    <div style="margin-bottom: 25px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #edf2f7; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h3 style="font-size: 1rem; font-weight: 700; color: #2d3748; margin-bottom: 5px;">
                <i class="fa-solid fa-id-card-clip" style="color: #6a4c7c;"></i> Limite de Colaboradores
            </h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Você utilizou <strong>{{ $totalUsuarios }}</strong> de <strong>{{ $limiteUsuarios }}</strong> vagas disponíveis no seu plano.</p>
        </div>
        <div style="width: 300px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 700; color: {{ $estaNoLimite ? '#e53e3e' : '#4a5568' }}; margin-bottom: 5px;">
                <span>Uso do Plano</span>
                <span>{{ $porcentagemUso }}%</span>
            </div>
            <div style="width: 100%; height: 8px; background-color: #edf2f7; border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; background-color: {{ $estaNoLimite ? '#e53e3e' : '#27ae60' }}; width: {{ min($porcentagemUso, 100) }}%; border-radius: 4px;"></div>
            </div>
            @if($estaNoLimite)
                <div style="font-size: 0.7rem; color: #e53e3e; margin-top: 5px; font-weight: bold; text-align: right;">
                    Limite atingido!
                </div>
            @endif
        </div>
    </div>

    {{-- LISTAGEM DE COLABORADORES --}}
    <div class="list-container">
        @if($colaboradores->isEmpty())
            <div style="text-align: center; padding: 40px 20px;">
                <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 style="font-size: 1.2rem; color: #475569; margin-bottom: 5px; font-weight: bold;">Nenhum colaborador cadastrado</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Cadastre os membros da sua equipe para iniciar o acompanhamento.</p>
                <button onclick="openCreateModal()" class="btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Cadastrar Primeiro
                </button>
            </div>
        @else
            <div class="table-responsive">
                <!-- Barra de Pesquisa -->
                <div style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
                    <div style="position: relative; width: 100%; max-width: 350px;">
                        <input type="text" id="searchInput" onkeyup="filterColabs()" placeholder="Buscar por nome ou e-mail..." style="width: 100%; padding: 10px 15px 10px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #334155; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#6a4c7c'" onblur="this.style.borderColor='#cbd5e1'">
                        <i class="fa-solid fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    </div>
                </div>

                <form id="bulkDeleteForm" action="{{ route('colaboradores.destroyLote') }}" method="POST">
                    @csrf
                    @method('DELETE')
                <table class="custom-table table-cards-mobile" id="colaboradoresTable" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #6a4c7c;">
                            <th style="padding: 12px 15px; width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllColabs" onclick="toggleAllColabs(this)" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Nome</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">E-mail</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: left;">Estrutura (Setor/Cargo)</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Perfil</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: center;">Status</th>
                            <th style="padding: 12px 15px; font-weight: bold; color: #333; text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($colaboradores as $colab)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; text-align: center;">
                                    @if($colab->id !== auth()->id())
                                        <input type="checkbox" name="ids[]" value="{{ $colab->id }}" class="colab-checkbox" onchange="updateBulkDeleteButton()" style="cursor: pointer; width: 16px; height: 16px;">
                                    @else
                                        <div title="Você não pode excluir a si mesmo" style="color: #cbd5e1; display: inline-flex; justify-content: center; align-items: center; width: 16px; height: 16px;">
                                            <i class="fa-solid fa-ban" style="font-size: 14px;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 15px; font-weight: bold; color: #2d3748; text-align: left;" data-label="Nome">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="background-color: #f3e5f5; color: #6a4c7c; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">
                                            {{ Str::upper(collect(explode(' ', $colab->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('')) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: #2d3748;" class="colab-name-mobile">{{ $colab->name }}</div>
                                            @if($colab->gestor)
                                                <div style="font-size: 0.75rem; color: #888; font-weight: normal;">
                                                    <i class="fa-solid fa-user-tie"></i> Gestor: {{ $colab->gestor->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 15px; color: #64748b; font-size: 0.9rem; text-align: left;" data-label="E-mail">
                                    {{ $colab->email }}
                                </td>
                                <td style="padding: 15px; color: #4a5568; text-align: left;" data-label="Estrutura (Setor/Cargo)">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        @if($colab->departamento)
                                            <span style="align-self: flex-start; background-color: #e8f8f5; color: #1abc9c; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">
                                                <i class="fa-solid fa-sitemap"></i> {{ $colab->departamento->nome }}
                                            </span>
                                        @else
                                            <span style="color: #cbd5e1; font-size: 0.75rem; font-style: italic;">Sem Setor</span>
                                        @endif
                                        
                                        @if($colab->cargo)
                                            <span style="align-self: flex-start; background-color: #fdf2e9; color: #e67e22; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">
                                                <i class="fa-solid fa-briefcase"></i> {{ $colab->cargo->nome }}
                                            </span>
                                        @else
                                            <span style="color: #cbd5e1; font-size: 0.75rem; font-style: italic;">Sem Cargo</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 15px; text-align: center;" data-label="Perfil">
                                    @php
                                        $badgeColor = match($colab->perfil_acesso->value) {
                                            'master' => 'background-color: #ebf5fb; color: #2980b9;',
                                            'gestor' => 'background-color: #fcf3cf; color: #f39c12;',
                                            default => 'background-color: #f4ecf7; color: #8e44ad;',
                                        };
                                    @endphp
                                    <span style="{{ $badgeColor }} padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                        {{ $colab->perfil_acesso->value }}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center;" data-label="Status">
                                    @php
                                        $statusBadge = match($colab->status->value ?? 'ativo') {
                                            'ativo' => 'background-color: #e8f8f5; color: #2ecc71;',
                                            'inativo' => 'background-color: #fce4d6; color: #e74c3c;',
                                            'ferias' => 'background-color: #fef9e7; color: #f39c12;',
                                            default => 'background-color: #ebf5fb; color: #3498db;',
                                        };
                                        $statusLabel = match($colab->status->value ?? 'ativo') {
                                            'ativo' => 'Ativo',
                                            'inativo' => 'Inativo',
                                            'ferias' => 'Férias',
                                            default => ucfirst($colab->status->value ?? 'ativo'),
                                        };
                                    @endphp
                                    <span style="{{ $statusBadge }} padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: right;" data-label="Ações">
                                    <div style="display: inline-flex; gap: 10px;">
                                        <button
                                            type="button"
                                            onclick="openEditModal(this)"
                                            data-id="{{ $colab->id }}"
                                            data-name="{{ $colab->name }}"
                                            data-email="{{ $colab->email }}"
                                            data-cpf="{{ $colab->cpf }}"
                                            data-telefone="{{ $colab->telefone }}"
                                            data-perfil-acesso="{{ $colab->perfil_acesso->value }}"
                                            data-status="{{ $colab->status->value ?? 'ativo' }}"
                                            data-departamento-id="{{ $colab->departamento_id ?? '' }}"
                                            data-cargo-id="{{ $colab->cargo_id ?? '' }}"
                                            data-gestor-id="{{ $colab->gestor_id ?? '' }}"
                                            style="background: none; border: none; color: #2980b9; cursor: pointer; font-weight: 600; font-size: 0.85rem;"
                                            title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i> Editar
                                        </button>

                                        @if($colab->id !== auth()->id())
                                            <button 
                                                type="button" 
                                                onclick="confirmDelete(this)"
                                                data-id="{{ $colab->id }}"
                                                data-name="{{ $colab->name }}"
                                                style="background: none; border: none; color: #e74c3c; cursor: pointer; font-weight: 600; font-size: 0.85rem;"
                                                title="Excluir">
                                                <i class="fa-solid fa-trash"></i> Excluir
                                            </button>
                                        @else
                                            <button 
                                                type="button" 
                                                disabled
                                                style="background: none; border: none; color: #cbd5e1; cursor: not-allowed; font-weight: 600; font-size: 0.85rem;"
                                                title="Você não pode excluir a si mesmo">
                                                <i class="fa-solid fa-trash"></i> Excluir
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </form>
            </div>
        @endif
    </div>

    {{-- MODAL DE CRIAÇÃO --}}
    <div id="createModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 480px; margin-bottom: 0; padding: 25px 20px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeCreateModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 20px; margin-top: 0;">
                Novo Colaborador</h3>

            <form action="{{ route('colaboradores.store') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 12px;">
                    <label for="create_name" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="create_name" required placeholder="Ex: Carlos Eduardo Silva"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="create_email" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">E-mail</label>
                        <input type="email" name="email" id="create_email" placeholder="carlos@empresa.com"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="create_cpf" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">CPF</label>
                        <input type="text" name="cpf" id="create_cpf" placeholder="000.000.000-00"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="create_telefone" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Telefone</label>
                        <input type="text" name="telefone" id="create_telefone" placeholder="(00) 00000-0000"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="create_password" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Senha <span class="text-danger">*</span></label>
                        <div class="password-container" style="position: relative;">
                            <input type="password" name="password" id="create_password" required placeholder="Senha inicial"
                                style="padding: 10px 45px 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <button type="button" class="toggle-password-btn" onclick="toggleColabPasswordVisibility('create_password', 'createEyeIcon')" aria-label="Mostrar/ocultar senha" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #666; cursor: pointer; display: flex; align-items: center; padding: 0; height: 100%;">
                                <i class="fa-solid fa-eye" id="createEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="create_perfil_acesso" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Perfil de Acesso <span class="text-danger">*</span></label>
                        <select name="perfil_acesso" id="create_perfil_acesso" required
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="colaborador">Colaborador</option>
                            <option value="gestor">Gestor</option>
                            <option value="master">Master (Admin Tenant)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="create_status" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Status <span class="text-danger">*</span></label>
                        <select name="status" id="create_status" required
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="ativo">Ativo</option>
                            <option value="ferias">Férias</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="create_departamento_id" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Departamento</label>
                        <select name="departamento_id" id="create_departamento_id"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="">Selecione...</option>
                            @foreach($departamentos as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="create_cargo_id" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Cargo</label>
                        <select name="cargo_id" id="create_cargo_id"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="">Selecione...</option>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="create_gestor_id" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Gestor Responsável</label>
                    <select name="gestor_id" id="create_gestor_id" class="form-control"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Nenhum (Reporta ao Master)</option>
                        @foreach($gestores as $gestor)
                            <option value="{{ $gestor->id }}">{{ $gestor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border-radius: 8px;">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE EDIÇÃO --}}
    <div id="editModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 480px; margin-bottom: 0; padding: 25px 20px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeEditModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 20px; margin-top: 0;">
                Editar Colaborador</h3>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 12px;">
                    <label for="edit_name" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="edit_name" required
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_email" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">E-mail</label>
                        <input type="email" name="email" id="edit_email"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_cpf" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">CPF</label>
                        <input type="text" name="cpf" id="edit_cpf"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_telefone" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Telefone</label>
                        <input type="text" name="telefone" id="edit_telefone" placeholder="(00) 00000-0000"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_password" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Nova Senha (Opcional)</label>
                        <div class="password-container" style="position: relative;">
                            <input type="password" name="password" id="edit_password" placeholder="Deixe em branco"
                                style="padding: 10px 45px 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <button type="button" class="toggle-password-btn" onclick="toggleColabPasswordVisibility('edit_password', 'editEyeIcon')" aria-label="Mostrar/ocultar senha" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #666; cursor: pointer; display: flex; align-items: center; padding: 0; height: 100%;">
                                <i class="fa-solid fa-eye" id="editEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_perfil_acesso" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Perfil de Acesso <span class="text-danger">*</span></label>
                        <select name="perfil_acesso" id="edit_perfil_acesso" required
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="colaborador">Colaborador</option>
                            <option value="gestor">Gestor</option>
                            <option value="master">Master (Admin Tenant)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_status" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" required
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="ativo">Ativo</option>
                            <option value="ferias">Férias</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_departamento_id" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Departamento</label>
                        <select name="departamento_id" id="edit_departamento_id"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="">Selecione...</option>
                            @foreach($departamentos as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="edit_cargo_id" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Cargo</label>
                        <select name="cargo_id" id="edit_cargo_id"
                            style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                            <option value="">Selecione...</option>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->id }}">{{ $cargo->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="edit_gestor_id" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Gestor Responsável</label>
                    <select name="gestor_id" id="edit_gestor_id" class="form-control"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                        <option value="">Nenhum (Reporta ao Master)</option>
                        @foreach($gestores as $gestor)
                            <option value="{{ $gestor->id }}" id="opt_gestor_{{ $gestor->id }}">{{ $gestor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn-primary"
                        style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border-radius: 8px;">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE CONFIRMAÇÃO DE DELEÇÃO --}}
    <div id="deleteModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 400px; margin-bottom: 0; padding: 35px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px; position: relative;">
            <button type="button" onclick="closeDeleteModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div style="background-color: rgba(231, 76, 60, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #e74c3c; font-size: 2rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #2d3748; margin-bottom: 10px;">Confirmar Exclusão</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; text-align: center;">
                Deseja realmente excluir o colaborador <strong id="delete_colab_name" style="color: #2d3748;"></strong>?
                Essa ação não poderá ser desfeita.
            </p>

            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')

                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button type="button" onclick="closeDeleteModal()" class="btn-secondary"
                        style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; justify-content: center;">Cancelar</button>
                    <button type="submit" class="btn-danger"
                        style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; font-size: 14px;">Excluir</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE CONFIRMAÇÃO DE DELEÇÃO EM LOTE --}}
    <div id="bulkDeleteModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 400px; margin-bottom: 0; padding: 35px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px; position: relative;">
            <button type="button" onclick="closeBulkDeleteModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div style="background-color: rgba(231, 76, 60, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #e74c3c; font-size: 2rem;">
                <i class="fa-solid fa-users-slash"></i>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #2d3748; margin-bottom: 10px;">Excluir em Lote</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; text-align: center;">
                Deseja realmente excluir os <strong id="bulkDeleteModalCount" style="color: #2d3748;">0</strong> colaboradores selecionados?
                Essa ação não poderá ser desfeita.
            </p>

            <div style="display: flex; gap: 15px; justify-content: center;">
                <button type="button" onclick="closeBulkDeleteModal()" class="btn-secondary"
                    style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; justify-content: center;">Cancelar</button>
                <button type="button" onclick="submitBulkDelete()" class="btn-danger"
                    style="flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; font-size: 14px;">Excluir Tudo</button>
            </div>
        </div>
    </div>

    {{-- MODAL DE IMPORTAÇÃO --}}
    <div id="importModal"
        style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
        <div class="form-card"
            style="max-width: 480px; margin-bottom: 0; padding: 25px 20px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 16px;">
            <button type="button" onclick="closeImportModal()"
                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.25rem; color: #b2bec3; cursor: pointer; transition: 0.2s;"
                onmouseover="this.style.color='#636e72'" onmouseout="this.style.color='#b2bec3'">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <h3 style="font-size: 1.25rem; font-weight: bold; color: #6a4c7c; text-align: center; margin-bottom: 20px; margin-top: 0;">
                Importar Colaboradores</h3>

            <p style="color: #555; font-size: 14px; margin-bottom: 20px; text-align: center;">
                Envie um arquivo Excel (.xlsx) ou CSV com as colunas: <strong>Nome, Email, CPF, Perfil, Telefone</strong>. 
                Se faltar e-mail e CPF simultaneamente, a linha será ignorada. O telefone pode conter apenas números ou formato (ex: (11) 99999-9999).<br><br>
                <a href="{{ asset('modelos/modelo_importacao_colaborador.csv') }}" download class="btn-secondary" style="font-size: 0.85rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-download"></i> Baixar Arquivo Modelo (CSV)
                </a>
            </p>

            <form action="{{ route('colaboradores.importar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="arquivo" style="font-weight: 600; color: #555; margin-bottom: 4px; font-size: 13px;">Arquivo <span class="text-danger">*</span></label>
                    <input type="file" name="arquivo" id="arquivo" required accept=".xlsx,.xls,.csv"
                        style="padding: 10px 12px; border-radius: 8px; border: 1px solid #ccc; background-color: #f9f9f9; width: 100%; font-size: 14px;">
                </div>

                <div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border-radius: 8px; background-color: #27ae60; border-color: #27ae60;">
                        <i class="fa-solid fa-upload"></i> Importar Planilha
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @push('scripts')
        <script>
            // Deletar em Lote
            function toggleAllColabs(source) {
                const checkboxes = document.querySelectorAll('.colab-checkbox');
                checkboxes.forEach(cb => cb.checked = source.checked);
                updateBulkDeleteButton();
            }

            function updateBulkDeleteButton() {
                const checked = document.querySelectorAll('.colab-checkbox:checked').length;
                const btn = document.getElementById('btnBulkDelete');
                const countSpan = document.getElementById('bulkDeleteCount');
                
                if (checked > 0) {
                    btn.style.display = 'inline-flex';
                    countSpan.innerText = checked;
                } else {
                    btn.style.display = 'none';
                }
                
                const allCheckboxes = document.querySelectorAll('.colab-checkbox').length;
                const selectAllBtn = document.getElementById('selectAllColabs');
                if (selectAllBtn) {
                    selectAllBtn.checked = (checked === allCheckboxes && allCheckboxes > 0);
                }
            }

            function confirmBulkDelete() {
                const checked = document.querySelectorAll('.colab-checkbox:checked').length;
                document.getElementById('bulkDeleteModalCount').innerText = checked;
                document.getElementById('bulkDeleteModal').style.display = 'flex';
            }

            function closeBulkDeleteModal() {
                document.getElementById('bulkDeleteModal').style.display = 'none';
            }

            function submitBulkDelete() {
                document.getElementById('bulkDeleteForm').submit();
            }

            // Controle do Modal de Criação
            function openCreateModal() {
                const modal = document.getElementById('createModal');
                modal.style.display = 'flex';
            }

            function closeCreateModal() {
                const modal = document.getElementById('createModal');
                modal.style.display = 'none';
            }

            function openImportModal() {
                const modal = document.getElementById('importModal');
                modal.style.display = 'flex';
            }

            function closeImportModal() {
                const modal = document.getElementById('importModal');
                modal.style.display = 'none';
            }

            // Controle do Modal de Edição
            function openEditModal(button) {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const email = button.getAttribute('data-email');
                // Adicionando dataset para capturar CPF (que não estava na view mas assumimos empty)
                const cpf = button.getAttribute('data-cpf') || '';
                const telefone = button.getAttribute('data-telefone') || '';
                const perfilAcesso = button.getAttribute('data-perfil-acesso');
                const status = button.getAttribute('data-status');
                const departamentoId = button.getAttribute('data-departamento-id');
                const cargoId = button.getAttribute('data-cargo-id');
                const gestorId = button.getAttribute('data-gestor-id');

                const modal = document.getElementById('editModal');
                const form = document.getElementById('editForm');

                // Define o action dinamicamente para o ID correto
                form.action = `/colaboradores/${id}`;

                // Preenche os campos
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_cpf').value = cpf;
                document.getElementById('edit_telefone').value = telefone;
                document.getElementById('edit_password').value = ''; // Limpa para segurança
                
                // Regra de segurança: Bloqueia rebaixamento/desativação da própria conta logada
                const selectPerfil = document.getElementById('edit_perfil_acesso');
                const selectStatus = document.getElementById('edit_status');
                const loggedInUserId = "{{ auth()->id() }}";
                
                selectPerfil.value = perfilAcesso;
                selectStatus.value = status;
                
                if (id == loggedInUserId) {
                    selectPerfil.disabled = true;
                    selectStatus.disabled = true;
                    selectPerfil.title = "Você não pode alterar o seu próprio perfil de acesso.";
                    selectStatus.title = "Você não pode alterar o seu próprio status.";
                } else {
                    selectPerfil.disabled = false;
                    selectStatus.disabled = false;
                    selectPerfil.title = "";
                    selectStatus.title = "";
                }
                
                document.getElementById('edit_departamento_id').value = departamentoId;
                document.getElementById('edit_cargo_id').value = cargoId;
                
                // Ocultar a si mesmo do select de gestores para evitar auto-gestão
                const selectGestor = document.getElementById('edit_gestor_id');
                
                // Primeiro, mostra todas as opções
                for (let i = 0; i < selectGestor.options.length; i++) {
                    selectGestor.options[i].style.display = 'block';
                    selectGestor.options[i].disabled = false;
                }
                
                // Desabilita/Oculta a opção correspondente ao próprio colaborador (se for elegível a gestor)
                const selfOption = document.getElementById(`opt_gestor_${id}`);
                if (selfOption) {
                    selfOption.style.display = 'none';
                    selfOption.disabled = true;
                }

                selectGestor.value = gestorId || '';

                modal.style.display = 'flex';
            }

            // Fechar modal de edição
            function closeEditModal() {
                const modal = document.getElementById('editModal');
                modal.style.display = 'none';
            }

            // Garantir que os campos desativados (disabled) sejam enviados no formulário ao salvar a si mesmo
            document.getElementById('editForm').addEventListener('submit', function() {
                document.getElementById('edit_perfil_acesso').disabled = false;
                document.getElementById('edit_status').disabled = false;
            });

            // Controle do Modal de Confirmação de Deleção
            function confirmDelete(button) {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                const modal = document.getElementById('deleteModal');
                const form = document.getElementById('deleteForm');
                const namePlaceholder = document.getElementById('delete_colab_name');

                form.action = `/colaboradores/${id}`;
                namePlaceholder.textContent = name;

                modal.style.display = 'flex';
            }

            function closeDeleteModal() {
                const modal = document.getElementById('deleteModal');
                modal.style.display = 'none';
            }

            // Toggle de visualização da senha
            function toggleColabPasswordVisibility(inputId, eyeIconId) {
                const passwordInput = document.getElementById(inputId);
                const eyeIcon = document.getElementById(eyeIconId);
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            }

            // Fechar modais ao clicar fora
            window.onclick = function (event) {
                const createModal = document.getElementById('createModal');
                const editModal = document.getElementById('editModal');
                const deleteModal = document.getElementById('deleteModal');
                const importModal = document.getElementById('importModal');

                if (event.target === createModal) closeCreateModal();
                if (event.target === editModal) closeEditModal();
                if (event.target === deleteModal) closeDeleteModal();
                if (event.target === importModal) closeImportModal();
            }

            // Máscara simples de CPF
            function aplicarMascaraCPF(input) {
                input.addEventListener('input', function(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
                    if (v.length > 9) v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                    else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
                    else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3})/, "$1.$2");
                    e.target.value = v;
                });
            }

            const createCpf = document.getElementById('create_cpf');
            if(createCpf) aplicarMascaraCPF(createCpf);
            const editCpf = document.getElementById('edit_cpf');
            if(editCpf) aplicarMascaraCPF(editCpf);

            function aplicarMascaraTelefone(input) {
                input.addEventListener('input', function(e) {
                    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
                    if (v.length > 10) v = v.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
                    else if (v.length > 6) v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
                    else if (v.length > 2) v = v.replace(/(\d{2})(\d{0,5})/, "($1) $2");
                    e.target.value = v;
                });
            }

            const createTelefone = document.getElementById('create_telefone');
            if(createTelefone) aplicarMascaraTelefone(createTelefone);
            const editTelefone = document.getElementById('edit_telefone');
            if(editTelefone) aplicarMascaraTelefone(editTelefone);

            // Filtro de Pesquisa (Nome e E-mail)
            function filterColabs() {
                const input = document.getElementById('searchInput');
                const filter = input.value.toLowerCase();
                const table = document.getElementById('colaboradoresTable');
                const tr = table.getElementsByTagName('tr');

                // Percorre todas as linhas (escondendo as do thead que é 0)
                for (let i = 1; i < tr.length; i++) {
                    const tdNome = tr[i].getElementsByTagName('td')[1]; // Coluna 2 (Nome)
                    const tdEmail = tr[i].getElementsByTagName('td')[2]; // Coluna 3 (E-mail)
                    
                    if (tdNome || tdEmail) {
                        const txtNome = tdNome.textContent || tdNome.innerText;
                        const txtEmail = tdEmail.textContent || tdEmail.innerText;
                        
                        if (txtNome.toLowerCase().indexOf(filter) > -1 || txtEmail.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }       
                }
            }
        </script>
    @endpush
@endsection
