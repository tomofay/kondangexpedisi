<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/pwa-icon.png">
    
    <title><?php echo e(config('app.name', 'Kondang Ekspedisi')); ?> Mobile</title>

    <!-- Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons: Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Styles: Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                    },
                    borderRadius: {
                        '3xl': '1.5rem',
                        '4xl': '2rem',
                        '5xl': '2.5rem',
                    },
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            overflow-x: hidden;
        }

        /* Glassmorphism Core */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 30px -5px rgba(37, 99, 235, 0.05);
        }

        .dark .glass {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-blue {
            background: rgba(37, 99, 235, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(37, 99, 235, 0.1);
        }

        /* Animated Background */
        .bg-blobs {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            overflow: hidden;
            background: #ffffff;
        }
        .dark .bg-blobs { background: #020617; }

        .blob {
            position: absolute;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            filter: blur(100px);
            border-radius: 50%;
            opacity: 0.12;
            animation: move 25s infinite alternate;
        }

        .blob-2 {
            background: linear-gradient(135deg, #0ea5e9 0%, #2dd4bf 100%);
            width: 500px;
            height: 500px;
            right: -150px;
            top: -150px;
            animation-delay: -7s;
        }

        .blob-3 {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            width: 400px;
            height: 400px;
            left: -100px;
            bottom: -100px;
            animation-delay: -12s;
        }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(120px, 80px) scale(1.1); }
        }

        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        .premium-shadow {
            box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.08);
        }

        .active-nav {
            color: #2563eb;
            transform: translateY(-5px);
        }

        .nav-indicator {
            width: 14px;
            height: 4px;
            border-radius: 10px;
            background: #2563eb;
            margin-top: 4px;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.4);
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .animate-slide-up {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .tap-scale:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 h-full flex flex-col overflow-hidden">
    <!-- Animated Background Blobs -->
    <div class="bg-blobs">
        <div class="blob"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    
    <!-- Premium Mobile Header -->
    <header class="glass sticky top-0 z-50 px-6 h-20 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="font-black text-xl leading-tight tracking-tighter text-blue-600 uppercase italic">Kondang Ekspedisi</h1>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button class="w-10 h-10 rounded-2xl flex items-center justify-center hover:bg-slate-200/50 dark:hover:bg-slate-800/50 transition-all tap-scale">
                <i class="bi bi-bell fs-5"></i>
            </button>
            <div class="w-10 h-10 rounded-2xl bg-slate-200 dark:bg-slate-800 overflow-hidden p-0.5 shadow-sm">
                <img src="<?php echo e(auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=6366f1&color=fff&bold=true'); ?>" 
                     class="rounded-xl w-full h-full object-cover" alt="User">
            </div>
        </div>
    </header>

    <!-- Main Scrollable Area -->
    <main class="flex-1 overflow-y-auto px-6 py-6 pb-32">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Premium Bottom Navigation -->
    <nav class="glass fixed bottom-6 left-6 right-6 z-50 h-20 rounded-4xl safe-bottom flex items-center justify-around px-4 shadow-2xl">
        <a href="<?php echo e(auth()->user()->role === 'customer' ? route('customer.dashboard') : route('courier.tasks')); ?>" 
           class="flex flex-col items-center justify-center transition-all tap-scale <?php echo e(request()->routeIs('*.dashboard') || request()->routeIs('*.tasks') ? 'active-nav' : 'text-slate-400'); ?>">
            <i class="bi <?php echo e(request()->routeIs('*.dashboard') || request()->routeIs('*.tasks') ? 'bi-house-door-fill' : 'bi-house-door'); ?> fs-4"></i>
            <?php if(request()->routeIs('*.dashboard') || request()->routeIs('*.tasks')): ?> <div class="nav-indicator"></div> <?php endif; ?>
        </a>

        <?php if(auth()->user()->role === 'customer'): ?>
            <a href="<?php echo e(route('customer.shipments.create')); ?>" 
               class="w-14 h-14 bg-blue-600 rounded-3xl flex items-center justify-center text-white shadow-xl shadow-blue-200 dark:shadow-none active:scale-90 transition-all -mt-10 border-4 border-white dark:border-slate-900">
                <i class="bi bi-plus-lg fs-3"></i>
            </a>
        <?php else: ?>
            <button onclick="window.toggleGlobalScanner()" 
               class="w-14 h-14 bg-blue-600 rounded-3xl flex items-center justify-center text-white shadow-xl shadow-blue-200 dark:shadow-none active:scale-90 transition-all -mt-10 border-4 border-white dark:border-slate-900">
                <i class="bi bi-qr-code-scan fs-3"></i>
            </button>
        <?php endif; ?>

        <a href="<?php echo e(route('profile.edit')); ?>" 
           class="flex flex-col items-center justify-center transition-all tap-scale <?php echo e(request()->routeIs('profile.edit') ? 'active-nav' : 'text-slate-400'); ?>">
            <i class="bi <?php echo e(request()->routeIs('profile.edit') ? 'bi-person-fill' : 'bi-person'); ?> fs-4"></i>
            <?php if(request()->routeIs('profile.edit')): ?> <div class="nav-indicator"></div> <?php endif; ?>
        </a>
    </nav>
    
    <!-- Global Scanner Modal (Courier Only) -->
    <?php if(auth()->user() && auth()->user()->role === 'courier'): ?>
        <div id="global-scanner-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-md flex flex-col items-center justify-center p-8 hidden animate-slide-up">
            <div class="w-full max-w-sm space-y-8 text-center">
                <div class="space-y-2">
                    <h3 class="text-3xl font-black tracking-tight text-white uppercase italic">Scan <span class="text-blue-500">Paket</span></h3>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Arahkan kamera ke barcode resi</p>
                </div>

                <div class="relative group">
                    <div id="global-reader" class="w-full aspect-square bg-black rounded-[3rem] overflow-hidden border-4 border-blue-600 shadow-2xl shadow-blue-500/20"></div>
                    
                    <!-- Camera Toggle Button -->
                    <button onclick="window.switchCamera()" id="btn-switch-camera" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-white/10 backdrop-blur-md text-white border border-white/20 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-white/20 transition-all hidden">
                        <i class="bi bi-camera-rotate fs-5"></i> Ganti Kamera
                    </button>
                </div>

                <button onclick="window.toggleGlobalScanner()" class="w-14 h-14 glass rounded-full flex items-center justify-center text-white mx-auto border-white/20">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>
            
            <form id="global-claim-form" action="<?php echo e(route('courier.shipments.claim')); ?>" method="POST" class="hidden">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="tracking_number" id="global-tracking-input">
            </form>
        </div>

        <script src="https://unpkg.com/html5-qrcode"></script>
        <script>
            let globalScanner = null;
            let availableCameras = [];
            let currentCameraIndex = 0;

            window.toggleGlobalScanner = async function() {
                const modal = document.getElementById('global-scanner-modal');
                const switchBtn = document.getElementById('btn-switch-camera');

                if (modal.classList.contains('hidden')) {
                    modal.classList.remove('hidden');
                    globalScanner = new Html5Qrcode("global-reader");
                    
                    try {
                        availableCameras = await Html5Qrcode.getCameras();
                        if (availableCameras && availableCameras.length > 0) {
                            if (availableCameras.length > 1) switchBtn.classList.remove('hidden');
                            
                            // Try to find default back camera
                            currentCameraIndex = availableCameras.findIndex(c => c.label.toLowerCase().includes('back') && !c.label.toLowerCase().includes('wide'));
                            if (currentCameraIndex === -1) currentCameraIndex = 0;

                            await startScanning(availableCameras[currentCameraIndex].id);
                        } else {
                            throw new Error("No cameras found");
                        }
                    } catch (err) {
                        console.error("Scanner Error:", err);
                        Swal.fire('Error', 'Gagal mengakses kamera: ' + err.message, 'error');
                        modal.classList.add('hidden');
                    }
                } else {
                    modal.classList.add('hidden');
                    switchBtn.classList.add('hidden');
                    if (globalScanner) {
                        try { await globalScanner.stop(); } catch(e) {}
                    }
                }
            }

            async function startScanning(cameraId) {
                await globalScanner.start(
                    cameraId, 
                    { fps: 15, qrbox: { width: 250, height: 200 }, aspectRatio: 1.0 },
                    (text) => {
                        if(!text) return;
                        document.getElementById('global-tracking-input').value = text;
                        document.getElementById('global-scanner-modal').classList.add('hidden');
                        globalScanner.stop();
                        
                        Swal.fire({
                            title: 'Resi Terdeteksi!',
                            text: 'Memproses ' + text,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1000,
                            didClose: () => {
                                document.getElementById('global-claim-form').submit();
                            }
                        });
                    }
                );
            }

            window.switchCamera = async function() {
                if (!globalScanner || availableCameras.length < 2) return;
                
                currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
                
                try {
                    await globalScanner.stop();
                    await startScanning(availableCameras[currentCameraIndex].id);
                } catch (err) {
                    console.error("Switch Camera Error:", err);
                }
            }
        </script>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/mobile/base.blade.php ENDPATH**/ ?>