@extends('layouts.mobile')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Halo, {{ $customer->name }}!</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Mau kirim paket apa hari ini?</p>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-6 text-white shadow-xl shadow-indigo-200 dark:shadow-none">
        <div class="flex justify-between items-start mb-6">
            <div class="space-y-1">
                <span class="text-indigo-100 text-xs font-semibold uppercase tracking-wider">Total Paket Anda</span>
                <div class="text-4xl font-bold">{{ $stats['shipments_total'] }}</div>
            </div>
            <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>
        
        <a href="{{ route('customer.shipments.create') }}" 
           class="flex items-center justify-center gap-2 bg-white text-indigo-600 font-bold py-3 px-6 rounded-2xl shadow-lg active:scale-95 transition-transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Kirim Paket Baru
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-slate-800 flex flex-col gap-2">
            <div class="text-orange-500 p-2 bg-orange-50 dark:bg-orange-900/20 rounded-xl w-fit">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-2xl font-bold">{{ $stats['pending_shipments'] }}</div>
            <div class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-tight">Menunggu Bayar</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-slate-800 flex flex-col gap-2">
            <div class="text-emerald-500 p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl w-fit">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-2xl font-bold">{{ $stats['payments_total'] }}</div>
            <div class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-tight">Total Transaksi</div>
        </div>
    </div>

    <!-- Recent Shipments -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">Paket Terbaru</h2>
            <a href="#" class="text-indigo-600 text-sm font-semibold">Lihat Semua</a>
        </div>

        <div class="space-y-4">
            @forelse($stats['recent_shipments'] as $shipment)
                <a href="{{ route('customer.shipments.track', $shipment) }}" 
                   class="block bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm active:bg-slate-50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $shipment->tracking_number }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $shipment->service_type }} • {{ $shipment->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full text-[10px] font-bold uppercase tracking-wider">
                            {{ $shipment->status->name }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="text-center py-12 text-slate-400 italic">
                    Belum ada riwayat pengiriman.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
