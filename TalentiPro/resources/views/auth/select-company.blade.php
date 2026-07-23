<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentiPro - Selecionar Empresa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo {
            font-size: 1.8em;
            color: #6a4c7c;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .subheader {
            color: #666;
            margin-bottom: 25px;
        }

        .company-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .company-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
        }

        .company-item:hover {
            border-color: #6a4c7c;
            background: #fdfdfd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .company-info {
            display: flex;
            flex-direction: column;
        }

        .company-name {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .user-role {
            font-size: 13px;
            color: #777;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .btn-select {
            background: #6a4c7c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-logout {
            background: none;
            border: none;
            color: #999;
            margin-top: 20px;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }
        
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo"><i class="fa-solid fa-building-user"></i> Selecione a Conta</div>
        <p class="subheader">Encontramos seu e-mail em mais de uma empresa. Por favor, escolha qual conta deseja acessar:</p>

        @if(session('error'))
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('auth.select-company.post') }}" method="POST" id="selectForm">
            @csrf
            <input type="hidden" name="user_id" id="selectedUserId">

            <ul class="company-list">
                @foreach($accounts as $account)
                <li class="company-item" onclick="selectCompany({{ $account['id'] }})">
                    <div class="company-info">
                        <span class="company-name">
                             <i class="fa-regular fa-building" style="margin-right: 5px;"></i>
                             {{ $account['empresa_nome'] ?? 'Empresa Padrão' }}
                        </span>
                        <span class="user-role">
                            {{ ucfirst($account['perfil']) }} 
                            @if($account['departamento'])
                                • {{ $account['departamento'] }}
                            @endif
                        </span>
                    </div>
                    <button type="button" class="btn-select">Acessar <i class="fa-solid fa-arrow-right"></i></button>
                </li>
                @endforeach
            </ul>
        </form>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Cancelar e Sair</button>
        </form>
    </div>

    <script>
        function selectCompany(userId) {
            document.getElementById('selectedUserId').value = userId;
            document.getElementById('selectForm').submit();
        }
    </script>
</body>

</html>
