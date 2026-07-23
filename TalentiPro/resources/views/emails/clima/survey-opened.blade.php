<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesquisa de Clima</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #3b5998;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eaeaea;
        }
        .alert {
            background-color: #e9f7ef;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 14px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ config('app.name', 'TalentiPro') }}</h2>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $user->name }}</strong>!</p>
            <p>Uma nova pesquisa de clima organizacional está disponível para você responder.</p>
            
            <h3 style="color: #3b5998;">{{ $survey->titulo }}</h3>
            @if($survey->descricao)
                <p><em>{{ $survey->descricao }}</em></p>
            @endif
            
            <p>Data limite para responder: <strong>{{ $survey->data_fim->format('d/m/Y') }}</strong></p>

            <div style="text-align: center;">
                <a href="{{ url('/clima/' . $survey->id) }}" class="btn">Responder Pesquisa Agora</a>
            </div>

            <div class="alert">
                <strong>🔒 Anonimato Garantido:</strong> Suas respostas são totalmente anônimas e sigilosas. Em nenhum momento seu nome será vinculado às suas avaliações.
            </div>
        </div>
        <div class="footer">
            Você está recebendo este e-mail porque faz parte da equipe na plataforma {{ config('app.name', 'TalentiPro') }}.<br>
            © {{ date('Y') }} Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
