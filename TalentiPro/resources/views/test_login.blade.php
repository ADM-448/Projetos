<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>TalentiPro - Login Test</title>
</head>
<body>
    <h1>Login Test</h1>
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <input type="email" name="email" required>
        <input type="password" name="password" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
