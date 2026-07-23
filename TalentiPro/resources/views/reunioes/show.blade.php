@extends(in_array(auth()->user()->perfil_acesso, ['admin', 'master']) ? 'layouts.admin' : 'layouts.gestor')
@section('title', 'Detalhes da Reunião')
@section('content')
<style>
    .reuniao-box { background: white; padding: 25px; border-radius: 10px; border: 1px solid #ccc; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .reuniao-title { font-size: 24px; font-weight: bold; margin-bottom: 15px; color: #3b5998; }
    .badge-part { background: #e0e0e0; padding: 5px 10px; border-radius: 15px; font-size: 13px; margin-right: 5px; }
    .audio-player { width: 100%; margin-top: 20px; }
    .texto-transcricao { background: #f9f9f9; padding: 15px; border-left: 4px solid #3b5998; font-style: italic; color: #555; white-space: pre-wrap; line-height: 1.6; }
    .texto-resumo { background: #e8f4fd; padding: 15px; border-radius: 5px; border: 1px solid #bce8f1; white-space: pre-wrap; line-height: 1.6; font-size: 15px; color: #31708f; }
</style>

<div class="page-content" style="padding: 40px; max-width: 900px; margin: 0 auto;">
    
    <a href="{{ route('reunioes.index') }}" style="color: #666; text-decoration: none; margin-bottom: 15px; display: inline-block;">&larr; Voltar para o Histórico</a>
    
    <div class="reuniao-box">
        <div class="reuniao-title">{{ $reuniao->titulo }}</div>
        <p><strong>Data:</strong> {{ $reuniao->created_at->format('d/m/Y às H:i') }} | <strong>Gestor:</strong> {{ $reuniao->gestor->name }}</p>
        
        <div style="margin-bottom: 20px;">
            <strong>Participantes Envolvidos:</strong><br>
            <div style="margin-top: 5px;">
                @foreach($reuniao->participantes as $p)
                    <span class="badge-part">{{ $p->name }}</span>
                @endforeach
            </div>
        </div>

        <hr>

        <h4>Resumo da Reunião (Gerado por IA)</h4>
        <div class="texto-resumo">
{{ $reuniao->resumo }}
        </div>

        @if($reuniao->audio_path)
            <hr>
            <h4>Gravação de Áudio</h4>
            <audio controls class="audio-player">
                <source src="{{ asset('storage/' . $reuniao->audio_path) }}" type="audio/webm">
                Seu navegador não suporta o elemento de áudio.
            </audio>
        @endif

        <hr>

        <h4>Transcrição Completa Bruta</h4>
        <div class="texto-transcricao">{{ $reuniao->transcricao }}</div>

    </div>

</div>
@endsection
