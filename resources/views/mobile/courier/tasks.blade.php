@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up">
    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-extrabold tracking-tight">Tugas Kurir</h2>
        <p class="text-slate-500 font-medium text-sm">Selesaikan {{ $tasks->count() }} pengantaran hari ini.</p>
    </div>

    <!-- Operational Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-primary-600 rounded-3xl p-6 text-white shadow-xl shadow-primary-100 dark:shadow-none">
            <div class="flex justify-between items-start mb-2">
                <i class="bi bi-bicycle fs-4"></i>
                <span class="text-[10px] font-black uppercase tracking-widest text-primary-200">Delivery</span>
            </div>
            <div class="text-3xl font-black">{{ $tasks->where('status.code', 'out_for_delivery')->count() }}</div>
        </div>
        <div class="bg-emerald-600 rounded-3xl p-6 text-white shadow-xl shadow-emerald-100 dark:shadow-none">
            <div class="flex justify-between items-start mb-2">
                <i class="bi bi-box-arrow-in-down fs-4"></i>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-200">Pickup</span>
            </div>
            <div class="text-3xl font-black">{{ $tasks->where('status.code', 'pickup')->count() }}</div>
        </div>
    </div>

    <!-- Task List -->
    <div class="space-y-4">
        <h3 class="font-extrabold text-lg flex items-center gap-2">
            <i class="bi bi-list-check text-primary-600"></i>
            Daftar Tugas
        </h3>

        @forelse($tasks as $task)
            <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-6 space-y-6">
                <!-- Task Header -->
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <span class="px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                            {{ $task->status->name }}
                        </span>
                        <h4 class="text-lg font-black uppercase tracking-tight">{{ $task->tracking_number }}</h4>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($task->recipient_address) }}" 
                       target="_blank"
                       class="w-12 h-12 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-primary-600">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </a>
                </div>

                <!-- Address Info -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center gap-1 mt-1">
                        <div class="w-2 h-2 rounded-full bg-primary-600"></div>
                        <div class="w-0.5 h-10 bg-slate-100 dark:bg-slate-800"></div>
                        <div class="w-2 h-2 rounded-full border-2 border-primary-600"></div>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Penerima</p>
                            <p class="text-sm font-extrabold">{{ $task->recipient_name }}</p>
                        </div>
                        <div class="space-y-0.5">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat Tujuan</p>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">{{ $task->recipient_address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-2">
                    <a href="tel:{{ $task->recipient_phone }}" 
                       class="flex-1 bg-slate-50 dark:bg-slate-800 h-14 rounded-2xl flex items-center justify-center gap-2 font-extrabold text-sm transition-all active:bg-slate-200">
                        <i class="bi bi-telephone-fill text-slate-400"></i>
                        Hubungi
                    </a>
                    <a href="{{ route('courier.shipments.edit', $task) }}" 
                       class="flex-[1.5] bg-primary-600 h-14 rounded-2xl flex items-center justify-center gap-2 font-extrabold text-sm text-white shadow-lg shadow-primary-100 dark:shadow-none transition-all active:scale-95">
                        <i class="bi bi-pencil-square"></i>
                        Update Status
                    </a>
                </div>
            </div>
        @empty
            <div class="py-20 text-center space-y-4">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                    <i class="bi bi-cup-hot fs-1"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="font-extrabold text-lg">Semua Tugas Selesai!</h4>
                    <p class="text-slate-400 text-sm font-medium">Anda bisa beristirahat sekarang.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

