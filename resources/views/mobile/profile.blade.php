@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up pb-32">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ auth()->user()->role === 'customer' ? route('customer.dashboard') : route('courier.tasks') }}" class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center shadow-sm text-slate-400">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-extrabold tracking-tight">Profil Saya</h2>
    </div>

    <!-- Profile Hero Card -->
    <div class="bg-primary-600 rounded-4xl p-8 text-white relative overflow-hidden shadow-2xl shadow-primary-200 dark:shadow-none">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col items-center text-center space-y-4">
            <div class="w-24 h-24 rounded-3xl bg-white/20 backdrop-blur-xl p-1 relative">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=fff&color=2563eb&bold=true&size=128" class="rounded-2xl w-full h-full object-cover" alt="Avatar">
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-emerald-500 border-4 border-primary-600 rounded-full"></div>
            </div>
            <div>
                <h3 class="text-2xl font-black">{{ $user->name }}</h3>
                <p class="text-primary-100 text-xs font-bold uppercase tracking-widest">{{ $user->email }}</p>
            </div>
            <div class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[10px] font-black uppercase tracking-widest">
                {{ $user->role }} ID: #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>
    </div>

    <!-- Profile Menu -->
    <div class="space-y-6">
        <div class="flex items-center gap-2 px-1">
            <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
            <h3 class="font-extrabold text-lg uppercase tracking-tight">Pengaturan Akun</h3>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-2">
            <a href="#" onclick="toggleSection('edit-info')" class="flex items-center gap-4 p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-3xl transition-all group">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="bi bi-person-gear fs-5"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-sm">Informasi Pribadi</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Nama, Email, Telepon</p>
                </div>
                <i class="bi bi-chevron-right text-slate-300"></i>
            </a>

            <div class="h-px bg-slate-50 dark:bg-slate-800 mx-5"></div>

            <a href="#" onclick="toggleSection('change-password')" class="flex items-center gap-4 p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-3xl transition-all group">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="bi bi-shield-lock fs-5"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-sm">Keamanan</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Ubah Kata Sandi</p>
                </div>
                <i class="bi bi-chevron-right text-slate-300"></i>
            </a>

            <div class="h-px bg-slate-50 dark:bg-slate-800 mx-5"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-4 p-5 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-3xl transition-all group text-left">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/30 text-red-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-red-600">Keluar Sesi</h4>
                        <p class="text-[10px] text-red-400 font-bold uppercase tracking-tighter">Logout dari aplikasi</p>
                    </div>
                    <i class="bi bi-chevron-right text-red-200"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Floating Edit Sections (Modal-like) -->
    <div id="section-edit-info" class="fixed inset-0 z-[60] bg-slate-950/20 backdrop-blur-sm hidden flex items-end">
        <div class="w-full bg-white dark:bg-slate-900 rounded-t-4xl p-8 space-y-8 animate-slide-up">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-black">Update Informasi</h3>
                <button onclick="toggleSection('edit-info')" class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center text-slate-400">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $user->name }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                </div>
                <button type="submit" class="w-full bg-primary-600 text-white font-black h-16 rounded-3xl shadow-xl shadow-primary-200 dark:shadow-none active:scale-95 transition-all text-sm uppercase tracking-widest mt-4">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <div id="section-change-password" class="fixed inset-0 z-[60] bg-slate-950/20 backdrop-blur-sm hidden flex items-end">
        <div class="w-full bg-white dark:bg-slate-900 rounded-t-4xl p-8 space-y-8 animate-slide-up">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-black">Ubah Password</h3>
                <button onclick="toggleSection('change-password')" class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center text-slate-400">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Password Saat Ini</label>
                    <input type="password" name="current_password" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Password Baru</label>
                    <input type="password" name="password" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                </div>
                <button type="submit" class="w-full bg-primary-600 text-white font-black h-16 rounded-3xl shadow-xl shadow-primary-200 dark:shadow-none active:scale-95 transition-all text-sm uppercase tracking-widest mt-4">
                    Ubah Password
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSection(id) {
        const el = document.getElementById('section-' + id);
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        } else {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }
</script>
@endpush
@endsection
