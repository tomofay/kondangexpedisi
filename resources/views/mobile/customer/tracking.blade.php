@extends('layouts.mobile')

@section('content')
<div class="space-y-6">
    <!-- Back Button & Title -->
    <div class="flex items-center gap-4">
        <a href="{{ route('customer.dashboard') }}" class="p-2 -ml-2 rounded-full active:bg-slate-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-xl font-bold">Lacak Paket</h1>
    </div>

    <!-- Tracking Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-start">
            <div class="space-y-1">
                <div class="text-xs text-slate-500 font-bold uppercase tracking-widest">Nomor Resi</div>
                <div class="text-xl font-bold text-indigo-600">{{ $shipment->tracking_number }}</div>
            </div>
            <div class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full text-[10px] font-bold uppercase tracking-wider">
                {{ $shipment->status->name }}
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 py-4 border-y border-slate-50 dark:border-slate-800">
            <div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Asal</div>
                <div class="text-sm font-semibold">{{ $shipment->branch->name }}</div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tujuan</div>
                <div class="text-sm font-semibold">{{ $shipment->recipient_address }}</div>
            </div>
        </div>
    </div>

    <!-- Payment Card (If Pending) -->
    @if($shipment->payment_status === 'pending' || $shipment->payment_status === 'unpaid')
    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-3xl p-6 border border-indigo-100 dark:border-indigo-800 space-y-4">
        <div class="flex justify-between items-center">
            <div class="space-y-1">
                <div class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Total Pembayaran</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white">Rp {{ number_format($shipment->total_amount, 0, ',', '.') }}</div>
            </div>
            <div class="bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                Belum Bayar
            </div>
        </div>

        <button id="pay-button" class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none active:scale-95 transition-all">
            Bayar Sekarang
        </button>
    </div>

    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script type="text/javascript">
        const payButton = document.getElementById('pay-button');
        payButton.onclick = function() {
            // Fetch snap token from server
            @php
                $payment = $shipment->payments()->whereIn('status', ['pending', 'unpaid'])->first() 
                           ?? $shipment->payments()->create([
                               'customer_id' => $shipment->customer_id,
                               'amount' => $shipment->total_amount,
                               'status' => 'pending',
                           ]);
            @endphp

            fetch("{{ route('payments.midtrans.snap-token', $payment) }}", {
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
                        onClose: function() { console.log('customer closed the popup without finishing the payment'); }
                    });
                } else {
                    alert("Gagal mendapatkan token pembayaran: " + (res.message || "Unknown error"));
                }
            });
        };
    </script>
    @endif

    <!-- Timeline -->
    <div class="space-y-8 pl-2">
        <h2 class="text-lg font-bold">Riwayat Perjalanan</h2>
        
        <div class="relative space-y-10">
            <!-- Line -->
            <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-200 dark:bg-slate-800"></div>

            @foreach($shipment->trackings as $tracking)
                <div class="relative flex gap-6">
                    <div class="mt-1.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 border-4 border-indigo-600 z-10"></div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-start">
                            <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight">{{ $tracking->status->name }}</div>
                            <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap">{{ $tracking->created_at->format('H:i, d M') }}</div>
                        </div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $tracking->location }}</div>
                        @if($tracking->notes)
                            <div class="text-xs text-slate-400 italic">"{{ $tracking->notes }}"</div>
                        @endif
                    </div>
                </div>
            @endforeach
            
            <!-- Created Status -->
            <div class="relative flex gap-6">
                <div class="mt-1.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 border-4 border-slate-300 dark:border-slate-700 z-10"></div>
                <div class="flex-1 space-y-1">
                    <div class="flex justify-between items-start">
                        <div class="font-bold text-slate-400 leading-tight">Paket Dibuat</div>
                        <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap">{{ $shipment->created_at->format('H:i, d M') }}</div>
                    </div>
                    <div class="text-sm text-slate-400">{{ $shipment->branch->name }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
