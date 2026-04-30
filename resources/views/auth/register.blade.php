<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - Kondang Ekspedisi</title>

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
            background: url('https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
        }

        .auth-form-side {
            width: 100%;
            max-width: 550px;
            padding: 3rem 4rem;
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
            margin-bottom: 2rem;
            display: block;
        }

        .brand-logo span { color: var(--primary); }

        .login-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }

        .login-subtitle {
            color: var(--text-muted);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .input-group-clean {
            background: #F8FAFC;
            border-radius: 12px;
            padding: 6px 16px;
            display: flex;
            align-items: center;
            border: 2px solid transparent;
            transition: 0.3s;
            margin-bottom: 1rem;
        }

        .input-group-clean:focus-within {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 97, 255, 0.05);
        }

        .input-group-clean i {
            color: var(--primary);
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .input-group-clean input {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            font-weight: 600;
            width: 100%;
            padding: 8px 0;
            color: var(--secondary);
        }

        .btn-login {
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 800;
            border: none;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0, 97, 255, 0.2);
            width: 100%;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 97, 255, 0.3);
            color: white;
        }

        .form-label-custom {
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.4rem;
            display: block;
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 0.8rem 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 992px) {
            .auth-side-brand { display: flex; }
            .auth-form-side { box-shadow: -20px 0 60px rgba(0,0,0,0.02); }
        }

        @media (max-width: 575.98px) {
            .auth-form-side { padding: 2rem; }
            .login-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Sidebar Brand (Visible on Desktop) -->
        <div class="auth-side-brand">
            <div class="animate__animated animate__fadeInLeft">
                <h1 class="display-4 fw-bold mb-4">Mulai Kirim Barang Tanpa Ribet.</h1>
                <p class="fs-5 text-white-50">Bergabung dengan ribuan pelanggan lainnya. Daftar sekarang untuk kemudahan membuat pesanan, cek resi real-time, dan memantau seluruh aktivitas pengiriman Anda.</p>
                
                <div class="mt-5 d-flex gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-white p-3 rounded-circle" style="background:rgba(255,255,255,0.1)"><i class="bi bi-box-seam fs-4"></i></div>
                        <div>
                            <div class="fw-bold text-white">Layanan Lengkap</div>
                            <small class="text-white-50">Reguler, Express & Sameday</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Form Side -->
        <div class="auth-form-side animate__animated animate__fadeInRight">
            <a href="/" class="brand-logo">KONDANG<span>EKSPEDISI</span></a>
            
            <h2 class="login-title">Daftar Akun Baru</h2>
            <p class="login-subtitle">Lengkapi data Anda untuk mulai menggunakan layanan.</p>

            @if ($errors->any())
                <div class="alert alert-danger alert-custom animate__animated animate__shakeX">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-2">
                    <label for="name" class="form-label-custom">Nama Lengkap</label>
                    <div class="input-group-clean">
                        <i class="bi bi-person-fill"></i>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required autofocus autocomplete="name">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="phone" class="form-label-custom">Nomor Telepon</label>
                    <div class="input-group-clean">
                        <i class="bi bi-telephone-fill"></i>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 08123456789" required autocomplete="tel">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="email" class="form-label-custom">Alamat Email</label>
                    <div class="input-group-clean">
                        <i class="bi bi-envelope-at-fill"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autocomplete="username">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mb-2">
                        <label for="password" class="form-label-custom">Kata Sandi</label>
                        <div class="input-group-clean">
                            <i class="bi bi-shield-lock-fill"></i>
                            <input id="password" type="password" name="password" placeholder="Min. 8 Karakter" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <label for="password_confirmation" class="form-label-custom">Konfirmasi</label>
                        <div class="input-group-clean">
                            <i class="bi bi-shield-check"></i>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi Sandi" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-login mt-3">
                    BUAT AKUN SEKARANG <i class="bi bi-person-plus-fill ms-2"></i>
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <p class="small text-muted mb-0">Sudah memiliki akun?</p>
                <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Masuk di sini</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
