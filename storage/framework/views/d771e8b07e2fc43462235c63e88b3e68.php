<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Profil Akun - Kondang Ekspedisi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root {
            --k-line: #d9e7ff;
            --k-blue-900: #0b2b67;
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

        .profile-shell {
            min-height: 100vh;
        }

        .surface-card {
            border: 1px solid var(--k-line);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 16px 38px rgba(22, 68, 150, 0.12);
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
            box-shadow: 0 0 10px rgba(79, 155, 255, 0.9);
        }

        .form-control {
            border-color: #b7d1ff;
            border-radius: 12px;
            min-height: 46px;
        }

        .form-control:focus {
            border-color: #5a98f8;
            box-shadow: 0 0 0 0.2rem rgba(56, 125, 237, 0.14);
        }

        .btn-kondang {
            border: none;
            color: #fff;
            background: linear-gradient(90deg, #1e63cf 0%, #448ff9 100%);
            box-shadow: 0 10px 20px rgba(24, 87, 192, 0.28);
            border-radius: 999px;
        }

        .btn-kondang:hover {
            color: #fff;
            filter: brightness(0.98);
        }
    </style>
</head>
<body>
    <div class="container profile-shell py-4 py-md-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <span class="brand-chip mb-2">Pengaturan Akun</span>
                <h1 class="brand-font h3 mb-1 text-primary-emphasis">Profil Pengguna</h1>
                <p class="text-secondary mb-0">Kelola data profil, keamanan akun, dan kontrol akses akun Anda.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('landing')); ?>" class="btn btn-outline-primary rounded-pill">Landing</a>
                <a href="<?php echo e(in_array($user->role, ['admin', 'kasir', 'manager'], true) ? route('dashboard') : route('landing')); ?>" class="btn btn-kondang px-4">Dashboard</a>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <div class="col-lg-6">
                <div class="surface-card p-4 p-md-5 h-100">
                    <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="surface-card p-4 p-md-5 h-100">
                    <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/profile/edit.blade.php ENDPATH**/ ?>