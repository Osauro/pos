<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TPV') }} - Login</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Nunito Sans', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #2d3748;
            min-height: 100vh;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1100px;
            width: 100%;
            display: flex;
        }

        .auth-logo-section {
            flex: 1;
            background: #1a1f2e;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
        }

        .auth-logo-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(255,255,255,0.05) 0%, transparent 70%);
        }

        .auth-logo-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .auth-logo-content img {
            max-width: 450px;
            width: 100%;
            height: auto;
            margin-bottom: 0;
            filter: drop-shadow(0 10px 30px rgba(0,0,0,0.5));
        }

        .auth-logo-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: none;
        }

        .auth-logo-content p {
            font-size: 1.1rem;
            opacity: 0.8;
            display: none;
        }

        .auth-form-section {
            flex: 1;
            background: white;
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-form-container {
            width: 100%;
            max-width: 400px;
        }

        .auth-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            text-align: left;
        }

        .auth-subtitle {
            color: #718096;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: 50px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-group-text {
            background: white;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.85rem 2rem;
            font-size: 1.05rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        @media (max-width: 768px) {
            .auth-card {
                flex-direction: column;
            }

            .auth-logo-section {
                padding: 2rem;
                min-height: 200px;
            }

            .auth-logo-content h2 {
                font-size: 1.8rem;
            }

            .auth-form-section {
                padding: 2rem;
            }

            .auth-title {
                font-size: 2rem;
            }
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo-section">
                <div class="auth-logo-content">
                    @if(file_exists(public_path('assets/images/logo.png')))
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" />
                    @else
                        <i class="fa-solid fa-cash-register fa-5x mb-3"></i>
                        <h2>Sistema TPV</h2>
                        <p>Punto de Venta Profesional</p>
                    @endif
                </div>
            </div>
            <div class="auth-form-section">
                <div class="auth-form-container">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
