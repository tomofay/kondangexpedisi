@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('customer.dashboard') }}" class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center shadow-sm text-slate-400">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-extrabold tracking-tight">Detail Pengiriman</h2>
    </div>

    <!-- Status Card -->
    <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex justify-between items-start">
            <div class="space-y-1">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor Resi</p>
                <h3 class="text-2xl font-black text-primary-600 uppercase">{{ $shipment->tracking_number }}</h3>
            </div>
            <span class="px-4 py-2 bg-primary-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary-100 dark:shadow-none">
                {{ $shipment->status->name }}
            </span>
        </div>

        <!-- Route visualization -->
        <div class="flex items-center gap-4 py-6 border-y border-slate-50 dark:border-slate-800">
            <div class="flex-1 text-center space-y-1">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Asal</p>
                <p class="text-sm font-extrabold truncate">{{ $shipment->branch->name }}</p>
            </div>
            <div class="flex flex-col items-center gap-1">
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-primary-600"></div>
                    <div class="w-8 h-0.5 bg-slate-100 dark:bg-slate-800"></div>
                    <i class="bi bi-truck text-primary-600 text-sm"></i>
                    <div class="w-8 h-0.5 bg-slate-100 dark:bg-slate-800"></div>
                    <div class="w-1.5 h-1.5 rounded-full border border-primary-600"></div>
                </div>
                <p class="text-[8px] font-bold text-slate-300 uppercase tracking-tighter">{{ $shipment->service_type }}</p>
            </div>
            <div class="flex-1 text-center space-y-1">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tujuan</p>
                <p class="text-sm font-extrabold truncate">{{ explode(',', $shipment->recipient_address)[0] }}</p>
            </div>
        </div>
    </div>

    <!-- Payment Section (if pending) -->
    @if($shipment->payment_status === 'pending' || $shipment->payment_status === 'unpaid')
    <div class="bg-primary-600 rounded-4xl p-8 text-white space-y-6 shadow-2xl shadow-primary-200 dark:shadow-none">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <p class="text-primary-100 text-[10px] font-black uppercase tracking-widest">Total Tagihan</p>
                <h4 class="text-3xl font-black italic">Rp {{ number_format($shipment->total_amount, 0, ',', '.') }}</h4>
            </div>
            <div class="w-14 h-14 bg-white/20 backdrop-blur-xl rounded-2xl flex items-center justify-center">
                <i class="bi bi-wallet2 fs-3"></i>
            </div>
        </div>
        <button id="pay-button" class="w-full bg-white text-primary-600 font-black h-16 rounded-3xl shadow-lg active:scale-95 transition-all">
            Bayar Sekarang
        </button>
    </div>
    @endif

    <!-- Timeline -->
    <div class="space-y-6">
        <h3 class="font-extrabold text-lg flex items-center gap-2">
            <i class="bi bi-clock-history text-primary-600"></i>
            Riwayat Perjalanan
        </h3>

        <div class="relative space-y-8 pl-4">
            <!-- Timeline Line -->
            <div class="absolute left-[23px] top-2 bottom-2 w-0.5 bg-slate-100 dark:bg-slate-800"></div>

            @foreach($shipment->trackings as $tracking)
                <div class="relative flex gap-6">
                    <div class="w-5 h-5 rounded-full bg-white dark:bg-slate-900 border-4 border-primary-600 z-10 shrink-0 mt-1"></div>
                    <div class="flex-1 space-y-1 pb-4 border-b border-slate-50 dark:border-slate-900/50">
                        <div class="flex justify-between items-start">
                            <h4 class="font-black text-sm text-slate-900 dark:text-slate-100 uppercase tracking-tight">{{ $tracking->status->name }}</h4>
                            <span class="text-[9px] font-bold text-slate-400">{{ $tracking->created_at->format('H:i • d M') }}</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed">{{ $tracking->location }}</p>
                        @if($tracking->notes)
                            <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-2xl text-[10px] text-slate-400 italic mt-2 border border-slate-100 dark:border-slate-800">
                                "{{ $tracking->notes }}"
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Created Status -->
            <div class="relative flex gap-6">
                <div class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 border-4 border-white dark:border-slate-950 z-10 shrink-0 mt-1"></div>
                <div class="flex-1 space-y-1">
                    <div class="flex justify-between items-start">
                        <h4 class="font-bold text-sm text-slate-400 uppercase">Paket Dibuat</h4>
                        <span class="text-[9px] font-bold text-slate-400">{{ $shipment->created_at->format('H:i • d M') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($shipment->payment_status === 'pending' || $shipment->payment_status === 'unpaid')
@push('scripts')
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script type="text/javascript">
    const payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.onclick = function() {
            fetch("{{ route('payments.midtrans.snap-token', $shipment) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.data && res.data.snap_token) {
                    window.snap.pay(res.data.snap_token, {
                        onSuccess: function(result) { window.location.reload(); },
                        onPending: function(result) { window.location.reload(); },
                        onError: function(result) { alert("Pembayaran gagal!"); },
                        onClose: function() { console.log('customer closed the popup'); }
                    });
                } else {
                    alert("Gagal: " + (res.message || "Unknown error"));
                }
            });
        };
    }
</script>
@endpush
@endif
@endsection

