<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deep Sync - Login</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #4e5296 0%, #0bc862 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Source Sans Pro', sans-serif;
        }
        
        .login-page {
            background: transparent;
        }
        
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-logo-wrapper {
            text-align: center;
            margin-bottom: 20px;
            animation: fadeInDown 0.8s ease-out;
        }
        
        .login-logo-wrapper img {
            max-width: 200px;
            height: auto;
            filter: drop-shadow(0 10px 25px rgba(0,0,0,0.2));
            margin-bottom: 15px;
        }
        
        .app-name {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 4px 15px rgba(0,0,0,0.3);
            letter-spacing: 2px;
            margin: 0;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .app-subtitle {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.9);
            margin-top: 3px;
            font-weight: 300;
            letter-spacing: 1px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease-out;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        
        .card-header {
            background: linear-gradient(135deg, #4e5296 0%, #0bc862 100%);
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 15px;
        }
        
        .card-header h3 {
            margin: 0;
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group-text {
            background: linear-gradient(135deg, #4e5296 0%, #0bc862 100%);
            border: none;
            color: #ffffff;
            width: 45px;
            justify-content: center;
            border-radius: 8px 0 0 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 8px 8px 0;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4e5296;
            box-shadow: 0 0 0 0.2rem rgba(78, 82, 150, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4e5296 0%, #0bc862 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(78, 82, 150, 0.4);
            background: linear-gradient(135deg, #3d4078 0%, #09a550 100%);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .icheck-primary label {
            color: #495057;
            font-weight: 400;
            cursor: pointer;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }
        
        .forgot-password a {
            color: #4e5296;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #0bc862;
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 576px) {
            .login-logo-wrapper img {
                max-width: 160px;
            }
            
            .app-name {
                font-size: 1.6rem;
            }
            
            .app-subtitle {
                font-size: 0.75rem;
            }
            
            .card-body {
                padding: 25px 20px;
            }
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 4px solid #fff;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="hold-transition login-page">

<div class="login-container">
    <div class="login-logo-wrapper">
        <img src="{{ asset('img/marca_deepfreeze_site_2022.png') }}" alt="Deep Freeze Logo">
        <h1 class="app-name">DEEP SYNC</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-lock mr-2"></i>Área Restrita</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Erro!</strong> {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="post" id="loginForm">
                @csrf

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                    <input type="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           placeholder="E-mail"
                           value="{{ old('email') }}"
                           required 
                           autofocus>
                </div>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                    <input type="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Senha"
                           required>
                </div>

                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">
                        Lembrar-me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt mr-2"></i>ENTRAR
                </button>

                @if (Route::has('password.request'))
                <div class="forgot-password">
                    <a href="{{ route('password.request') }}">
                        <i class="fas fa-question-circle mr-1"></i>Esqueci minha senha
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px; color: rgba(255,255,255,0.8); font-size: 0.85rem;">
        <p style="margin: 0;">&copy; {{ date('Y') }} Deep Freeze - Todos os direitos reservados</p>
        <p style="margin: 5px 0 0 0;">Versão 1.0.0</p>
    </div>
</div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.add('active');
    });
</script>

</body>
</html>
