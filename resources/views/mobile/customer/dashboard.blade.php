@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up">
    <!-- Welcome -->
    <div class="space-y-1">
        <h2 class="text-2xl font-extrabold tracking-tight">Halo, {{ explode(' ', $customer->name)[0] }}!</h2>
        <p class="text-slate-500 font-medium text-sm">Cek status paket atau kirim barang baru.</p>
    </div>

    <!-- Quick Track Search -->
    <div class="relative group">
        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary-600 transition-colors">
            <i class="bi bi-search fs-5"></i>
        </div>
        <input type="text" 
               placeholder="Masukkan No. Resi Anda..." 
               class="w-full bg-white dark:bg-slate-900 h-16 pl-14 pr-6 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-primary-600 font-semibold text-sm transition-all"
               onkeyup="if(event.key === 'Enter') window.location.href='/customer/shipments/'+this.value+'/track'">
    </div>

    <!-- Main Stats Card -->
    <div class="bg-primary-600 rounded-4xl p-8 text-white relative overflow-hidden shadow-2xl shadow-primary-200 dark:shadow-none">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-primary-400/20 rounded-full blur-2xl"></div>
        
        <div class="relative z-10 space-y-6">
            <div class="flex justify-between items-start">
                <div class="space-y-1">
                    <p class="text-primary-100 text-[10px] font-black uppercase tracking-widest">Total Pengiriman</p>
                    <h3 class="text-5xl font-black">{{ $stats['shipments_total'] }}</h3>
                </div>
                <div class="w-14 h-14 bg-white/20 backdrop-blur-xl rounded-2xl flex items-center justify-center">
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
            </div>
            
            <div class="flex gap-3">
                <div class="flex-1 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                    <p class="text-[10px] font-bold text-primary-100 uppercase mb-1">Menunggu</p>
                    <p class="text-xl font-extrabold">{{ $stats['pending_shipments'] }}</p>
                </div>
                <div class="flex-1 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                    <p class="text-[10px] font-bold text-primary-100 uppercase mb-1">Berhasil</p>
                    <p class="text-xl font-extrabold">{{ $stats['payments_total'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Types -->
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="font-extrabold text-lg">Pilih Layanan</h3>
            <i class="bi bi-arrow-right-short text-slate-400 fs-4"></i>
        </div>
        <div class="grid grid-cols-4 gap-4">
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="bi bi-lightning-charge-fill fs-4"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase">Express</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="bi bi-truck fs-4"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase">Regular</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="bi bi-shop fs-4"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase">Economy</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-500 uppercase">Sameday</span>
            </div>
        </div>
    </div>

    <!-- Recent History -->
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="font-extrabold text-lg">Aktivitas Terakhir</h3>
            <a href="#" class="text-primary-600 text-xs font-bold uppercase tracking-wider">Lihat Semua</a>
        </div>

        <div class="space-y-4">
            @forelse($stats['recent_shipments'] as $shipment)
                <a href="{{ route('customer.shipments.track', $shipment) }}" 
                   class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-3xl premium-shadow border border-slate-100 dark:border-slate-800 active:scale-[0.98] transition-all">
                    <div class="w-14 h-14 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="bi bi-box-arrow-in-down fs-4 text-primary-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm truncate uppercase">{{ $shipment->tracking_number }}</h4>
                        <p class="text-xs text-slate-500 font-medium">{{ $shipment->service_type }} • {{ $shipment->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                            {{ $shipment->status->name }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="py-10 text-center space-y-3">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-300">
                        <i class="bi bi-inbox fs-1"></i>
                    </div>
                    <p class="text-slate-400 font-bold text-sm italic">Belum ada pengiriman.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

