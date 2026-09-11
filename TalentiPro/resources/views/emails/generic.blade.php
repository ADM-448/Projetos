<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assunto }}</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .header {
            background-color: #0f172a;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 32px 24px;
        }
        .content p {
            line-height: 1.6;
            margin-bottom: 16px;
            font-size: 16px;
        }
        .btn-container {
            text-align: center;
            margin-top: 32px;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 16px 24px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TalentiPro</h1>
        </div>
        <div class="content">
            @if($nome)
                <p>Olá, <strong>{{ $nome }}</strong>!</p>
            @endif
            
            <p>{!! nl2br(e($mensagem)) !!}</p>
            
            @if($link)
                <div class="btn-container">
                    <a href="{{ $link }}" class="btn">{{ $textoBotao ?? 'Acessar TalentiPro' }}</a>
                </div>
            @endif
        </div>
        <div class="footer">
            <p>Este é um e-mail automático enviado pela plataforma TalentiPro.</p>
            <p>&copy; {{ date('Y') }} TalentiPro. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
