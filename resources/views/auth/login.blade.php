<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - AgilyGov</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 1rem;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-placeholder {
            width: 120px;
            height: 48px;
            background: #f0f0f0;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #666;
        }

        .logo-container img {
            max-width: 100%;
            height: auto;
            object-fit: contain; /* Ajusta a imagem para manter a proporção */
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.875rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .password-container {
            position: relative;
        }

        .forgot-password {
            position: absolute;
            right: 0;
            top: 0;
            font-size: 0.875rem;
            color: #2563eb;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo-container">
        <div class="logo-placeholder">
            <img src="{{ asset('storage/images/logo.jpg') }}" alt="Logo">
        </div>
    </div>

    <h1>Bem-vindo de volta</h1>
    <p class="subtitle">Informe suas credenciais para acessar o sistema</p>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <div class="password-container">
                <label for="password">Senha</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-password">Esqueceu a senha?</a>
                @endif
            </div>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
