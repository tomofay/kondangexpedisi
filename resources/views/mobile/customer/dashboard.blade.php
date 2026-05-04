@extends('mobile.base')

@section('content')
<div class="space-y-12 animate-slide-up pb-10">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-blue-600 p-10 rounded-[3rem] text-white shadow-2xl">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-[80px]"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-blue-400/20 rounded-full blur-[60px]"></div>
        
        <div class="relative z-10 space-y-4">
            <div class="space-y-1">
                <p class="text-blue-100 text-[10px] font-black uppercase tracking-[0.2em] opacity-70">Selamat Datang,</p>
                <h2 class="text-3xl font-black tracking-tight leading-none">{{ explode(' ', $customer->name)[0] }}!</h2>
            </div>
        </div>
    </div>

    <!-- Quick Track Search -->
    <div class="px-1">
        <form action="{{ route('customer.shipments.search') }}" method="GET" class="relative group">
            <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none text-slate-300 group-focus-within:text-blue-600 transition-colors">
                <i class="bi bi-search fs-5"></i>
            </div>
            <input type="text" name="tracking_number"
                   placeholder="Cari nomor resi anda..." 
                   class="w-full bg-white h-20 pl-16 pr-8 rounded-[2.5rem] border-none shadow-xl shadow-slate-200/50 focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all placeholder:text-slate-300">
            <button type="submit" class="absolute right-3 top-3 bottom-3 w-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center tap-scale shadow-lg shadow-blue-500/30">
                <i class="bi bi-arrow-right fs-4"></i>
            </button>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 px-1">
        <div class="bg-white rounded-[3rem] p-8 border border-slate-50 relative overflow-hidden shadow-2xl shadow-slate-200/40">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-blue-50 rounded-full blur-[80px]"></div>
            
            <div class="relative z-10 space-y-10">
                <div class="flex justify-between items-center">
                    <div class="space-y-1">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">Total Pengiriman</p>
                        <h3 class="text-5xl font-black text-slate-900 tracking-tighter italic leading-none">{{ $stats['shipments_total'] }}</h3>
                    </div>
                    <div class="w-20 h-20 bg-blue-600 text-white rounded-[2rem] flex items-center justify-center shadow-2xl shadow-blue-500/40">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100/50 space-y-2">
                        <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-2">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest">Pending</p>
                        <p class="text-2xl font-black text-slate-900 leading-none">{{ $stats['pending_shipments'] }}</p>
                    </div>
                    <div class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100/50 space-y-2">
                        <div class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mb-2">
                            <i class="bi bi-check-all fs-4"></i>
                        </div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest">Selesai</p>
                        <p class="text-2xl font-black text-slate-900 leading-none">{{ $stats['payments_total'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Selection -->
    <div class="space-y-6 px-1">
        <div class="flex justify-between items-center px-4">
            <h3 class="font-black text-xl tracking-tight text-slate-900">Layanan</h3>
            <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-lg">Pilihan Terbaik</span>
        </div>
        <div class="grid grid-cols-4 gap-4">
            @foreach([
                ['icon' => 'lightning-charge-fill', 'color' => 'blue', 'label' => 'Express'],
                ['icon' => 'truck', 'color' => 'blue', 'label' => 'Regular'],
                ['icon' => 'shop', 'color' => 'blue', 'label' => 'Economy'],
                ['icon' => 'clock-history', 'color' => 'blue', 'label' => 'Sameday']
            ] as $service)
            <a href="{{ route('customer.shipments.create') }}" class="flex flex-col items-center gap-4 tap-scale group">
                <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center shadow-xl shadow-slate-200/50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all border border-slate-50">
                    <i class="bi bi-{{ $service['icon'] }} fs-3"></i>
                </div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center leading-tight">{{ $service['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="space-y-6 px-1">
        <div class="flex justify-between items-end px-4">
            <div class="space-y-1">
                <h3 class="font-black text-xl tracking-tight text-slate-900">Daftar Paket</h3>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Status Terakhir</p>
            </div>
            <a href="{{ route('customer.shipments.index') }}" class="text-blue-600 text-[10px] font-black uppercase tracking-widest bg-blue-50 px-4 py-2 rounded-xl tap-scale transition-all hover:bg-blue-600 hover:text-white">Lihat Semua</a>
        </div>

        <div class="space-y-5">
            @forelse($stats['recent_shipments'] as $shipment)
                @php
                    $statusColors = [
                        'pending' => 'bg-amber-50 text-amber-600',
                        'in_transit' => 'bg-blue-50 text-blue-600',
                        'delivered' => 'bg-emerald-50 text-emerald-600',
                        'terkirim_/_selesai' => 'bg-emerald-50 text-emerald-600',
                    ];
                    $statusKey = strtolower(str_replace(' ', '_', $shipment->status->name));
                    $badgeClass = $statusColors[$statusKey] ?? 'bg-blue-50 text-blue-600';
                @endphp
                <a href="{{ route('customer.shipments.track', $shipment) }}" 
                   class="flex items-center gap-5 bg-white p-6 rounded-[2.5rem] shadow-xl shadow-slate-200/30 border border-slate-50 tap-scale group">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="bi bi-box-seam fs-3 text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-black text-sm truncate uppercase tracking-tight text-slate-800">{{ $shipment->tracking_number }}</h4>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-[8px] font-black px-2.5 py-1 rounded-lg {{ $badgeClass }} uppercase tracking-widest">{{ $shipment->status->name }}</span>
                            <span class="text-[8px] font-bold text-slate-300 uppercase italic">{{ $shipment->created_at->format('d M') }}</span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <div class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="bi bi-chevron-right text-xs"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white py-16 text-center rounded-[3rem] border border-slate-100 shadow-xl">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-100 mb-4">
                        <i class="bi bi-inbox fs-1"></i>
                    </div>
                    <p class="text-slate-900 font-black text-sm tracking-tight">Belum Ada Paket</p>
                    <p class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mt-1 italic">Mulai kirim paket sekarang!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

