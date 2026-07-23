@extends('layouts.admin')

@section('title', 'AUDITORIA ADMIN: ' . $survey->titulo)

@section('content_main')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-user-shield text-danger me-2"></i> Auditoria: {{ $survey->titulo }}</h2>
        <a href="{{ route('clima.dashboard', $survey->id) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Dashboard</a>
    </div>

    <div class="alert alert-danger shadow-sm mb-4 border-0 border-start border-danger border-4">
        <h4><i class="fa-solid fa-triangle-exclamation me-2"></i> ACESSO RESTRITO - MODO AUDITORIA</h4>
        <p class="mb-0">
            Você está acessando dados descriptografados. Estas informações são estritamente sigilosas e devem ser usadas apenas para fins legais ou auditorias autorizadas.<br>
            <strong>Este acesso está sendo registrado nos logs de segurança do sistema.</strong>
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Usuário</th>
                            <th>E-mail</th>
                            <th>Departamento</th>
                            <th>Pergunta</th>
                            <th>Resposta (Nota / Texto)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($respostas as $resp)
                            <tr>
                                <td>{{ $resp['data'] }}</td>
                                <td class="fw-bold">{{ $resp['usuario'] }}</td>
                                <td>{{ $resp['email'] }}</td>
                                <td>{{ $resp['departamento'] }}</td>
                                <td>{{ $resp['pergunta'] }}</td>
                                <td>
                                    @if(is_numeric($resp['resposta']))
                                        <span class="badge bg-primary fs-6">{{ $resp['resposta'] }}</span>
                                    @else
                                        <em>"{{ Str::limit($resp['resposta'], 100) }}"</em>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Nenhuma resposta registrada até o momento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
