<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - Kondang Ekspedisi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary: #0061FF;
            --primary-dark: #004ecc;
            --secondary: #0B2B67;
            --text-main: #172B4D;
            --text-muted: #6B778C;
            --bg-light: #F4F7FA;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            background-color: var(--white);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        h1, h2, h3, .font-sora { font-family: 'Sora', sans-serif; }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .auth-side-brand {
            background: linear-gradient(135deg, var(--secondary) 0%, #051937 100%);
            color: white;
            padding: 4rem;
            flex: 1;
            display: none;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .auth-side-brand::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
        }

        .auth-form-side {
            width: 100%;
            max-width: 550px;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
            z-index: 1;
        }

        .brand-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--secondary);
            text-decoration: none;
            margin-bottom: 3rem;
            display: block;
        }

        .brand-logo span { color: var(--primary); }

        .login-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 0.75rem;
            letter-spacing: -1px;
        }

        .login-subtitle {
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }

        .input-group-clean {
            background: #F8FAFC;
            border-radius: 16px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            border: 2px solid transparent;
            transition: 0.3s;
            margin-bottom: 1.5rem;
        }

        .input-group-clean:focus-within {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 97, 255, 0.05);
        }

        .input-group-clean i {
            color: var(--primary);
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .input-group-clean input {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            font-weight: 600;
            width: 100%;
            padding: 10px 0;
            color: var(--secondary);
        }

        .btn-login {
            background: var(--primary);
            color: white;
            padding: 1.1rem;
            border-radius: 16px;
            font-weight: 800;
            border: none;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0, 97, 255, 0.2);
            width: 100%;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 97, 255, 0.3);
            color: white;
        }

        .form-label-custom {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .alert-custom {
            border-radius: 16px;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 992px) {
            .auth-side-brand { display: flex; }
            .auth-form-side { box-shadow: -20px 0 60px rgba(0,0,0,0.02); }
        }

        @media (max-width: 575.98px) {
            .auth-form-side { padding: 2rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Sidebar Brand (Visible on Desktop) -->
        <div class="auth-side-brand">
            <div class="animate__animated animate__fadeInLeft">
                <h1 class="display-4 fw-bold mb-4">Efisiensi Logistik di Genggaman Anda.</h1>
                <p class="fs-5 text-white-50">Kelola pengiriman, armada, dan pantau performa bisnis secara real-time dengan sistem operasional terintegrasi.</p>
                
                <div class="mt-5 d-flex gap-4">
                    <div class="text-center">
                        <div class="h3 fw-bold mb-0">100%</div>
                        <small class="text-white-50">Transparan</small>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="text-center">
                        <div class="h3 fw-bold mb-0">24/7</div>
                        <small class="text-white-50">Operasional</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Form Side -->
        <div class="auth-form-side animate__animated animate__fadeInRight">
            <a href="/" class="brand-logo">KONDANG<span>EKSPEDISI</span></a>
            
            <h2 class="login-title">Selamat Datang Kembali</h2>
            <p class="login-subtitle">Masuk ke panel manajemen internal Anda.</p>

            @if (session('status'))
                <div class="alert alert-success alert-custom animate__animated animate__headShake">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-custom animate__animated animate__shakeX">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label-custom">Alamat Email</label>
                    <div class="input-group-clean">
                        <i class="bi bi-envelope-at-fill"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label-custom">Kata Sandi</label>
                    <div class="input-group-clean">
                        <i class="bi bi-shield-lock-fill"></i>
                        <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                        <label class="form-check-label small fw-semibold" for="remember_me">Ingat Perangkat</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a class="text-decoration-none small fw-bold text-primary" href="{{ route('password.request') }}">Lupa Sandi?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    MASUK KE DASHBOARD <i class="bi bi-arrow-right-short fs-4"></i>
                </button>
            </form>

            <div class="mt-5 pt-4 border-top">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-primary fs-4"><i class="bi bi-info-circle-fill"></i></div>
                    <p class="small text-muted mb-0">Hanya staf berwenang yang dapat mengakses area ini. Jika Anda pelanggan, silakan gunakan aplikasi mobile untuk melacak paket.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
