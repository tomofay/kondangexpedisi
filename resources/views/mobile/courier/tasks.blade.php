@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up pb-32">
    <!-- Header -->
    <div class="flex items-center justify-between px-1">
        <div class="space-y-1">
            <h2 class="text-3xl font-black tracking-tight leading-none">Dashboard <span class="text-blue-600">Kurir</span></h2>
            <p class="text-slate-500 font-bold text-[10px] uppercase tracking-widest flex items-center gap-2">
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                {{ date('d M Y') }} • {{ $stats['pending'] }} Paket Aktif
            </p>
        </div>
        <button class="w-14 h-14 glass rounded-3xl flex items-center justify-center text-blue-600 shadow-xl shadow-blue-100 dark:shadow-none tap-scale border-blue-100">
            <i class="bi bi-qr-code-scan fs-3"></i>
        </button>
    </div>

    <!-- Stats Grid (Upgraded) -->
    <div class="grid grid-cols-2 gap-4">
        <div class="relative group">
            <div class="absolute inset-0 bg-blue-600 rounded-3xl blur-xl opacity-10 group-hover:opacity-20 transition-opacity"></div>
            <div class="relative glass p-6 rounded-[2rem] space-y-3 border-white/40">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pending</p>
                </div>
                <p class="text-3xl font-black italic tracking-tighter text-blue-600">{{ $stats['pending'] }}</p>
            </div>
        </div>
        <div class="relative group">
            <div class="absolute inset-0 bg-emerald-600 rounded-3xl blur-xl opacity-10 group-hover:opacity-20 transition-opacity"></div>
            <div class="relative glass p-6 rounded-[2rem] space-y-3 border-white/40">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Selesai</p>
                </div>
                <p class="text-3xl font-black italic tracking-tighter text-emerald-600">{{ $stats['completed'] }}</p>
            </div>
        </div>
    </div>

    <!-- Task List -->
    <div class="space-y-6">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-xl tracking-tight">Tugas Hari Ini</h3>
            </div>
            <a href="{{ route('courier.shipments.index') }}" class="text-[10px] font-black uppercase tracking-widest text-blue-600">Lihat Semua</a>
        </div>

        @forelse($tasks as $task)
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-500/5 rounded-[2.5rem] -z-10"></div>
                <div class="glass p-8 rounded-[2.5rem] premium-shadow border-white/40 space-y-8 relative overflow-hidden tap-scale transition-all">
                    <!-- Mesh Gradient Decor -->
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl"></div>

                    <div class="flex justify-between items-start relative z-10">
                        <div class="space-y-1">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest leading-none">Resi Number</p>
                            <h3 class="text-2xl font-black italic tracking-tighter text-blue-600 uppercase">{{ $task->tracking_number }}</h3>
                        </div>
                        <div class="px-4 py-2 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-200 dark:shadow-none">
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $task->status->name }}</span>
                        </div>
                    </div>

                    <div class="space-y-6 border-t border-slate-100 dark:border-slate-800/50 pt-6">
                        <!-- Route Info -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-1 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Asal</p>
                                <p class="text-[10px] font-black text-slate-700 dark:text-slate-200 truncate">{{ $task->branch->name }}</p>
                            </div>
                            <div class="text-blue-600 animate-pulse">
                                <i class="bi bi-arrow-right text-lg"></i>
                            </div>
                            <div class="flex-1 p-3 bg-blue-50/50 dark:bg-blue-900/20 rounded-2xl border border-blue-100/50 dark:border-blue-900/30">
                                <p class="text-[8px] font-black text-blue-400 uppercase tracking-widest leading-none mb-1">Tujuan</p>
                                <p class="text-[10px] font-black text-blue-700 dark:text-blue-200 truncate">{{ $task->destinationBranch->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center shrink-0 shadow-sm">
                                <i class="bi bi-geo-alt-fill text-blue-600 fs-5"></i>
                            </div>
                            <div class="space-y-1 flex-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat Penerima</p>
                                <p class="text-sm font-black leading-tight text-slate-800 dark:text-slate-100">{{ $task->recipient_name }}</p>
                                <p class="text-[11px] font-bold text-slate-500 leading-relaxed truncate">{{ $task->recipient_address }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $task->recipient_phone) }}" class="flex-1 glass h-14 rounded-2xl flex items-center justify-center gap-2 text-blue-600 font-black text-[11px] uppercase tracking-widest shadow-sm border-blue-50">
                            <i class="bi bi-whatsapp fs-5"></i> Hubungi
                        </a>
                        <a href="{{ route('courier.shipments.edit', $task) }}" class="flex-1 bg-blue-600 text-white h-14 rounded-2xl flex items-center justify-center gap-2 font-black text-[11px] uppercase tracking-widest shadow-xl shadow-blue-200 dark:shadow-none">
                            Update <i class="bi bi-arrow-right fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="relative">
                <div class="absolute inset-0 bg-blue-600 rounded-[3rem] blur-3xl opacity-5"></div>
                <div class="relative glass py-24 text-center rounded-[3rem] space-y-6 border-white/40">
                    <div class="w-24 h-24 bg-slate-50 dark:bg-slate-900 rounded-[2rem] flex items-center justify-center mx-auto text-blue-200 shadow-inner">
                        <i class="bi bi-clipboard2-check-fill fs-1"></i>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-black text-2xl tracking-tight">Semua Selesai!</h4>
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-[0.2em] italic">Istirahatlah, tugas Anda sudah beres.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

