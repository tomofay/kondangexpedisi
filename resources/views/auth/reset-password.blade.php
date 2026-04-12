<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Kondang Ekspedisi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root {
            --k-line: #d9e7ff;
            --k-blue-800: #17479c;
            --k-blue-500: #4f9bff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(900px 380px at 0% -8%, #d9e8ff 0%, transparent 65%),
                radial-gradient(900px 420px at 100% 0%, #e8f2ff 0%, transparent 60%),
                linear-gradient(180deg, #f8fbff 0%, #ffffff 56%);
            color: #14284b;
        }

        .brand-font {
            font-family: 'Sora', sans-serif;
            letter-spacing: -0.02em;
        }

        .auth-shell { min-height: 100vh; }

        .auth-panel {
            border: 1px solid var(--k-line);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 42px rgba(22, 68, 150, 0.14);
            backdrop-filter: blur(8px);
        }

        .brand-chip {
            border: 1px solid #c6ddff;
            color: var(--k-blue-800);
            background: #edf4ff;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            padding: 0.35rem 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .brand-chip::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--k-blue-500);
        }

        .form-control {
            border-color: #b7d1ff;
            border-radius: 12px;
            min-height: 46px;
        }

        .btn-kondang {
            border: none;
            color: #fff;
            background: linear-gradient(90deg, #1e63cf 0%, #448ff9 100%);
            border-radius: 999px;
            box-shadow: 0 10px 20px rgba(24, 87, 192, 0.3);
        }

        .btn-kondang:hover { color: #fff; filter: brightness(0.97); }
    </style>
</head>
<body>
    <div class="container auth-shell d-flex align-items-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-panel p-4 p-md-5">
                    <a href="{{ route('landing') }}" class="text-decoration-none">
                        <div class="brand-font h4 text-primary-emphasis mb-3">Kondang Ekspedisi</div>
                    </a>

                    <span class="brand-chip mb-3">Atur Ulang Password</span>
                    <h1 class="brand-font h3 mb-2 text-primary-emphasis">Reset Password</h1>
                    <p class="text-secondary small mb-4">Masukkan email dan password baru untuk menyelesaikan pemulihan akun.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control" autocomplete="username" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password Baru</label>
                            <input id="password" type="password" name="password" class="form-control" autocomplete="new-password" required>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        </div>

                        <button type="submit" class="btn btn-kondang w-100 py-2 fw-semibold">Simpan Password Baru</button>
                    </form>

                    <p class="small text-secondary mt-4 mb-0">
                        Kembali ke halaman
                        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
