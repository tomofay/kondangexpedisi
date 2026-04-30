@extends('layouts.mobile')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold">Tugas Kurir</h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm">Anda memiliki {{ $tasks->count() }} tugas hari ini.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-indigo-600 rounded-3xl p-4 text-white">
            <div class="text-xs font-semibold opacity-80 uppercase tracking-wider mb-1">Pick Up</div>
            <div class="text-2xl font-bold">{{ $tasks->where('status.code', 'pickup')->count() }}</div>
        </div>
        <div class="bg-emerald-600 rounded-3xl p-4 text-white">
            <div class="text-xs font-semibold opacity-80 uppercase tracking-wider mb-1">Delivery</div>
            <div class="text-2xl font-bold">{{ $tasks->where('status.code', 'out_for_delivery')->count() }}</div>
        </div>
    </div>

    <!-- Task List -->
    <div class="space-y-4">
        @forelse($tasks as $task)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-4 shadow-sm">
                <div class="flex justify-between items-start mb-3">
                    <div class="space-y-0.5">
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-widest">{{ $task->status->name }}</div>
                        <div class="text-lg font-bold">{{ $task->tracking_number }}</div>
                    </div>
                    <div class="p-2 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex gap-3">
                        <div class="w-1.5 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                        <div class="text-sm">
                            <div class="font-bold">Penerima: {{ $task->recipient_name }}</div>
                            <div class="text-slate-500">{{ $task->recipient_address }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="tel:{{ $task->recipient_phone }}" class="flex-1 flex items-center justify-center gap-2 bg-slate-100 dark:bg-slate-800 py-3 rounded-2xl font-bold text-sm active:bg-slate-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        Hubungi
                    </a>
                    <a href="{{ route('courier.shipments.edit', $task) }}" class="flex-1 flex items-center justify-center gap-2 bg-indigo-600 py-3 rounded-2xl font-bold text-sm text-white active:scale-95 transition-transform shadow-lg shadow-indigo-100 dark:shadow-none">
                        Update Status
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                <div class="text-4xl mb-4">☕</div>
                <div class="font-bold text-slate-900 dark:text-slate-100">Semua Tugas Selesai!</div>
                <div class="text-sm text-slate-500">Santai sejenak sambil menunggu tugas baru.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
