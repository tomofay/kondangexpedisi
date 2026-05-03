<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/pwa-icon.png">
    
    <title>{{ config('app.name', 'Kondang Ekspedisi') }} Mobile</title>

    <!-- Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons: Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .glass {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        .premium-shadow {
            box-shadow: 0 10px 30px rgba(0, 97, 255, 0.05);
        }

        .active-nav {
            color: #2563eb;
            transform: translateY(-4px);
        }

        .nav-indicator {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #2563eb;
            margin-top: 2px;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .animate-slide-up {
            animation: slideUp 0.4s ease forwards;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 h-full flex flex-col overflow-hidden">
    
    <!-- Premium Mobile Header -->
    <header class="glass sticky top-0 z-50 px-6 h-20 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary-200 dark:shadow-none">
                <i class="bi bi-rocket-takeoff-fill fs-5"></i>
            </div>
            <div>
                <h1 class="font-extrabold text-lg leading-tight tracking-tight">Kondang Ekspedisi</h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ auth()->user()->role }} Portal</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button class="w-10 h-10 rounded-2xl flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-800 transition-all">
                <i class="bi bi-bell fs-5"></i>
            </button>
            <div class="w-10 h-10 rounded-2xl bg-slate-200 dark:bg-slate-800 overflow-hidden p-0.5">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff&bold=true" class="rounded-xl" alt="User">
            </div>
        </div>
    </header>

    <!-- Main Scrollable Area -->
    <main class="flex-1 overflow-y-auto px-6 py-6 pb-32">
        @yield('content')
    </main>

    <!-- Premium Bottom Navigation -->
    <nav class="glass fixed bottom-6 left-6 right-6 z-50 h-20 rounded-4xl safe-bottom flex items-center justify-around px-4 shadow-2xl">
        <a href="{{ auth()->user()->role === 'customer' ? route('customer.dashboard') : route('courier.tasks') }}" 
           class="flex flex-col items-center justify-center transition-all {{ request()->routeIs('*.dashboard') || request()->routeIs('*.tasks') ? 'active-nav' : 'text-slate-400' }}">
            <i class="bi {{ request()->routeIs('*.dashboard') || request()->routeIs('*.tasks') ? 'bi-house-door-fill' : 'bi-house-door' }} fs-4"></i>
            @if(request()->routeIs('*.dashboard') || request()->routeIs('*.tasks')) <div class="nav-indicator"></div> @endif
        </a>

        @if(auth()->user()->role === 'customer')
            <a href="{{ route('customer.shipments.create') }}" 
               class="w-14 h-14 bg-primary-600 rounded-3xl flex items-center justify-center text-white shadow-xl shadow-primary-200 dark:shadow-none active:scale-90 transition-transform -mt-10">
                <i class="bi bi-plus-lg fs-3"></i>
            </a>
        @else
            <button class="w-14 h-14 bg-primary-600 rounded-3xl flex items-center justify-center text-white shadow-xl shadow-primary-200 dark:shadow-none active:scale-90 transition-transform -mt-10">
                <i class="bi bi-qr-code-scan fs-3"></i>
            </button>
        @endif

        <a href="{{ route('profile.edit') }}" 
           class="flex flex-col items-center justify-center transition-all {{ request()->routeIs('profile.edit') ? 'active-nav' : 'text-slate-400' }}">
            <i class="bi {{ request()->routeIs('profile.edit') ? 'bi-person-fill' : 'bi-person' }} fs-4"></i>
            @if(request()->routeIs('profile.edit')) <div class="nav-indicator"></div> @endif
        </a>
    </nav>

    @stack('scripts')
</body>
</html>
