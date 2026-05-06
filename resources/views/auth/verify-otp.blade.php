<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi OTP - Kondang Ekspedisi</title>

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

        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, .font-sora { font-family: 'Sora', sans-serif; }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .otp-card {
            background: white;
            padding: 3.5rem;
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.08);
            max-width: 500px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .brand-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--secondary);
            text-decoration: none;
            margin-bottom: 2.5rem;
            display: block;
        }

        .brand-logo span { color: var(--primary); }

        .otp-icon {
            width: 80px;
            height: 80px;
            background: #F0F7FF;
            color: var(--primary);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
        }

        .title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 0.75rem;
            letter-spacing: -1px;
        }

        .subtitle {
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            font-size: 1rem;
            line-height: 1.6;
        }

        .otp-input-group {
            margin-bottom: 2.5rem;
        }

        .otp-input-group input {
            background: #F8FAFC;
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 1.2rem;
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.5rem;
            width: 100%;
            color: var(--secondary);
            transition: 0.3s;
        }

        .otp-input-group input:focus {
            background: white;
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 5px rgba(0, 97, 255, 0.05);
        }

        .btn-verify {
            background: var(--primary);
            color: white;
            padding: 1.2rem;
            border-radius: 20px;
            font-weight: 800;
            border: none;
            transition: 0.3s;
            box-shadow: 0 10px 30px rgba(0, 97, 255, 0.2);
            width: 100%;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-verify:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 97, 255, 0.3);
        }

        .alert-custom {
            border-radius: 20px;
            border: none;
            padding: 1.2rem;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        @media (max-width: 575.98px) {
            .otp-card { padding: 2.5rem 1.5rem; border-radius: 30px; }
            .title { font-size: 1.6rem; }
            .subtitle { font-size: 0.9rem; }
            .otp-input-group input { font-size: 1.5rem; padding: 1rem; }
            .auth-container { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="otp-card animate__animated animate__fadeInUp">
            <a href="/" class="brand-logo">KONDANG<span>EKSPEDISI</span></a>
            
            <div class="otp-icon">
                <i class="bi bi-shield-check"></i>
            </div>

            <h2 class="title">Verifikasi Email</h2>
            <p class="subtitle">Kami telah mengirimkan 6 digit kode OTP ke email Anda. Silakan masukkan kode tersebut untuk mengaktifkan akun.</p>

            @if ($errors->any())
                <div class="alert alert-danger alert-custom animate__animated animate__shakeX">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.otp') }}">
                @csrf
                <div class="otp-input-group">
                    <input type="text" name="otp" maxlength="6" placeholder="000000" required autofocus autocomplete="off">
                </div>

                <button type="submit" class="btn-verify">
                    Verifikasi Sekarang
                </button>
            </form>

            <div class="mt-4 pt-3 border-top">
                <p class="small text-muted mb-0">Tidak menerima kode? <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Daftar Ulang</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
