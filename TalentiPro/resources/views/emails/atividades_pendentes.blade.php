<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Atividades Pendentes</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7fafc; margin: 0; padding: 20px; color: #2d3748; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #6a4c7c; font-size: 24px; margin: 0; }
        .content p { font-size: 16px; line-height: 1.6; }
        .atividade-list { list-style: none; padding: 0; margin: 20px 0; }
        .atividade-item { background: #f0f4f8; padding: 15px; border-left: 4px solid #6a4c7c; border-radius: 4px; margin-bottom: 10px; }
        .atividade-title { font-weight: bold; font-size: 16px; color: #2d3748; margin: 0 0 5px 0; }
        .atividade-desc { font-size: 14px; color: #4a5568; margin: 0; }
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; background-color: #6a4c7c; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #a0aec0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TalentiPro</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $user->name }}</strong>! 👋</p>
            <p>Você possui algumas atividades importantes aguardando a sua atenção na plataforma:</p>
            
            <ul class="atividade-list">
                @foreach($atividades as $atividade)
                    <li class="atividade-item">
                        <p class="atividade-title">{{ $atividade->titulo }}</p>
                        <p class="atividade-desc">{{ $atividade->descricao }}</p>
                    </li>
                @endforeach
            </ul>

            <div class="btn-container">
                <a href="{{ config('app.url') }}/login" class="btn">Acessar Plataforma</a>
            </div>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático enviado pelo TalentiPro. Por favor, não responda.</p>
        </div>
    </div>
</body>
</html>
