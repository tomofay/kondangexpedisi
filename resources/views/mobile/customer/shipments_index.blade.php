@extends('mobile.base')

@section('content')
<div class="space-y-10 animate-slide-up pb-40 px-1">
    <!-- Header Section -->
    <div class="flex items-center justify-between px-2">
        <div class="flex items-center gap-4">
            <a href="{{ route('customer.dashboard') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-soft text-blue-600 tap-scale border border-slate-50">
                <i class="bi bi-chevron-left fs-5"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900 leading-none">Daftar Paket</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5">Kelola Pengiriman Anda</p>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="px-1">
        <form action="{{ route('customer.shipments.index') }}" method="GET" class="relative group">
            <div class="absolute inset-y-0 left-7 flex items-center pointer-events-none text-slate-300 group-focus-within:text-blue-600 transition-colors">
                <i class="bi bi-search fs-5"></i>
            </div>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari resi atau nama..." 
                   class="w-full bg-white h-20 pl-16 pr-24 rounded-[2.5rem] border-none shadow-2xl shadow-slate-200/50 focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all placeholder:text-slate-300">
            
            <button type="submit" class="absolute right-3 top-3 bottom-3 px-6 bg-blue-600 text-white rounded-[1.8rem] flex items-center justify-center tap-scale shadow-xl shadow-blue-500/30">
                <span class="text-[10px] font-black uppercase tracking-widest">Cari</span>
            </button>
        </form>
    </div>

    <!-- Filter Pills (Modern alternative to dropdown) -->
    <div class="px-1 overflow-x-auto no-scrollbar py-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.shipments.index', ['q' => request('q')]) }}" 
               class="shrink-0 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ !request('status') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-white text-slate-400 border border-slate-100 shadow-sm' }}">
                Semua
            </a>
            @foreach($statuses as $status)
                <a href="{{ route('customer.shipments.index', ['q' => request('q'), 'status' => $status->id]) }}" 
                   class="shrink-0 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') == $status->id ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'bg-white text-slate-400 border border-slate-100 shadow-sm' }}">
                    {{ $status->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Shipments List -->
    <div class="space-y-6">
        @forelse($shipments as $shipment)
            @php
                $statusColors = [
                    'pending' => 'bg-amber-50 text-amber-600',
                    'in_transit' => 'bg-blue-50 text-blue-600',
                    'delivered' => 'bg-emerald-50 text-emerald-600',
                    'terkirim_/_selesai' => 'bg-emerald-50 text-emerald-600',
                    'returned' => 'bg-rose-50 text-rose-600',
                    'cancelled' => 'bg-slate-50 text-slate-400',
                ];
                $statusKey = strtolower(str_replace(' ', '_', $shipment->status->name));
                $badgeClass = $statusColors[$statusKey] ?? 'bg-blue-50 text-blue-600';
                $progressWidth = ($shipment->status->id == 8) ? '100%' : ($shipment->status->name === 'Pending' ? '15%' : '65%');
            @endphp
            <a href="{{ route('customer.shipments.track', $shipment) }}" 
               class="block bg-white p-7 rounded-[3rem] shadow-xl shadow-slate-200/30 border border-slate-50 tap-scale group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/30 rounded-full -mr-16 -mt-16 blur-3xl group-hover:bg-blue-100/50 transition-all"></div>
                
                <div class="flex items-center gap-5 mb-6 relative z-10">
                    <div class="w-16 h-16 bg-blue-50 rounded-[1.5rem] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-inner">
                        <i class="bi bi-box-seam fs-3 text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-black text-sm truncate uppercase tracking-tight text-slate-800">{{ $shipment->tracking_number }}</h4>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[8px] font-black px-3 py-1 rounded-lg {{ $badgeClass }} uppercase tracking-widest leading-none">{{ $shipment->status->name }}</span>
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest leading-none">{{ $shipment->service_type }}</span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <div class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                            <i class="bi bi-chevron-right text-xs"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Premium Progress Bar -->
                <div class="relative h-2 bg-slate-50 rounded-full overflow-hidden mb-3">
                    <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-1000 shadow-sm" 
                         style="width: {{ $progressWidth }}">
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center px-1 relative z-10">
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-calendar3 text-[8px] text-slate-300"></i>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">{{ $shipment->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="bi bi-person-fill text-[8px] text-slate-300"></i>
                        <p class="text-[8px] font-black text-slate-900 uppercase tracking-widest italic">{{ $shipment->recipient_name }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white py-24 text-center rounded-[3.5rem] border border-slate-50 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-slate-50 rounded-full blur-[80px]"></div>
                <div class="relative z-10 space-y-6 px-10">
                    <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto text-slate-200 shadow-inner">
                        <i class="bi bi-inbox fs-1"></i>
                    </div>
                    <div class="space-y-2">
                        <p class="text-slate-900 font-black text-xl tracking-tight leading-none">Belum Ada Paket</p>
                        <p class="text-slate-400 font-bold text-xs leading-relaxed italic opacity-70">Kami tidak menemukan paket yang Anda cari. Silakan coba kata kunci lain.</p>
                    </div>
                    @if(request()->anyFilled(['q', 'status']))
                        <a href="{{ route('customer.shipments.index') }}" class="inline-flex items-center gap-3 text-blue-600 font-black text-[10px] uppercase tracking-widest bg-blue-50 px-8 py-4 rounded-[1.5rem] tap-scale transition-all shadow-sm">
                            <i class="bi bi-arrow-counterclockwise fs-6"></i>
                            <span>Reset Filter</span>
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($shipments->hasPages())
        <div class="flex items-center justify-between px-2 pt-10">
            @if($shipments->onFirstPage())
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 cursor-not-allowed">
                    <i class="bi bi-chevron-left fs-4"></i>
                </div>
            @else
                <a href="{{ $shipments->previousPageUrl() }}" class="w-14 h-14 bg-white shadow-xl border border-slate-50 rounded-2xl flex items-center justify-center text-blue-600 tap-scale">
                    <i class="bi bi-chevron-left fs-4"></i>
                </a>
            @endif

            <div class="flex flex-col items-center gap-1.5">
                <p class="text-[8px] font-black text-slate-300 uppercase tracking-[0.3em]">Halaman</p>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-black text-blue-600">{{ $shipments->currentPage() }}</span>
                    <span class="text-xs font-black text-slate-200">/</span>
                    <span class="text-xs font-black text-slate-400">{{ $shipments->lastPage() }}</span>
                </div>
            </div>

            @if($shipments->hasMorePages())
                <a href="{{ $shipments->nextPageUrl() }}" class="w-14 h-14 bg-white shadow-xl border border-slate-50 rounded-2xl flex items-center justify-center text-blue-600 tap-scale">
                    <i class="bi bi-chevron-right fs-4"></i>
                </a>
            @else
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 cursor-not-allowed">
                    <i class="bi bi-chevron-right fs-4"></i>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
