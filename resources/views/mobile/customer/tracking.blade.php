@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up pb-40 px-1">
    <!-- Header -->
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dashboard') }}" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm text-blue-600 tap-scale border border-slate-100">
                <i class="bi bi-chevron-left fs-6"></i>
            </a>
            <h2 class="text-xl font-black tracking-tight text-slate-900">Detail Paket</h2>
        </div>
        <div class="px-4 py-2 bg-blue-50 rounded-2xl shrink-0">
            <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest">{{ $shipment->status->name }}</span>
        </div>
    </div>

    <!-- Main Resi Card -->
    <div class="bg-blue-600 rounded-[3rem] p-10 text-white relative overflow-hidden shadow-2xl">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-[80px]"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-blue-400/20 rounded-full blur-[60px]"></div>
        
        <div class="relative z-10 space-y-8">
            <div class="flex justify-between items-start">
                <div class="space-y-1.5">
                    <p class="text-blue-100/60 text-[10px] font-black uppercase tracking-[0.2em]">Nomor Resi</p>
                    <h3 class="text-3xl font-black italic tracking-tighter uppercase leading-none">{{ $shipment->tracking_number }}</h3>
                </div>
                <div class="w-14 h-14 bg-white/20 backdrop-blur-3xl rounded-[1.5rem] flex items-center justify-center border border-white/20 shadow-inner">
                    <i class="bi bi-box-seam-fill fs-3 text-white"></i>
                </div>
            </div>

            <!-- Route visualization -->
            <div class="flex items-center gap-4 py-8 border-y border-white/10">
                <div class="flex-1 space-y-1.5">
                    <p class="text-[9px] font-black text-blue-100/40 uppercase tracking-widest">Asal</p>
                    <p class="text-xs font-black leading-tight">{{ $shipment->branch->name }}</p>
                </div>
                <div class="flex flex-col items-center gap-2 px-4 shrink-0">
                    <div class="w-10 h-10 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-xl">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                </div>
                <div class="flex-1 text-right space-y-1.5">
                    <p class="text-[9px] font-black text-blue-100/40 uppercase tracking-widest">Tujuan</p>
                    <p class="text-xs font-black leading-tight">{{ explode(',', $shipment->recipient_address)[0] }}</p>
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-emerald-400 rounded-full"></div>
                    <span class="text-[9px] font-black text-blue-50 uppercase tracking-widest">{{ $shipment->service_type }} Service</span>
                </div>
                <p class="text-[9px] font-black text-blue-100/60 uppercase tracking-widest italic">{{ $shipment->created_at->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Shipment Details Section -->
    <div class="grid grid-cols-1 gap-6">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-50 shadow-xl shadow-slate-200/40 space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-lg tracking-tight text-slate-900">Informasi Penerima</h3>
            </div>
            
            <div class="space-y-5">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 shrink-0">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Lengkap</p>
                        <p class="text-xs font-black text-slate-900">{{ $shipment->recipient_name }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 shrink-0">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">WhatsApp</p>
                        <p class="text-xs font-black text-slate-900">{{ $shipment->recipient_phone }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 shrink-0">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat Lengkap</p>
                        <p class="text-xs font-bold text-slate-600 leading-relaxed">{{ $shipment->recipient_address }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Section -->
    <div>
        @if($shipment->payment_status === 'paid')
            <div class="bg-emerald-500 rounded-[2.5rem] p-8 text-white space-y-4 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-white/20 rounded-full blur-[40px]"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div class="space-y-1">
                        <p class="text-emerald-100 text-[9px] font-black uppercase tracking-widest">Status Pembayaran</p>
                        <h4 class="text-2xl font-black tracking-tight">Lunas</h4>
                    </div>
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white border border-white/20">
                        <i class="bi bi-patch-check-fill fs-3"></i>
                    </div>
                </div>
                <div class="pt-4 border-t border-white/10 relative z-10">
                    <a href="{{ route('shipments.label', $shipment) }}" target="_blank" 
                       class="w-full bg-white text-emerald-600 font-black h-16 rounded-[1.5rem] shadow-xl flex items-center justify-center gap-3 text-xs uppercase tracking-widest tap-scale">
                        <span>Cetak Label Resi</span>
                        <i class="bi bi-printer-fill fs-5"></i>
                    </a>
                </div>
                <p class="text-[10px] font-bold text-white/80 leading-relaxed italic">Terima kasih, paket anda sedang diproses ke tahap selanjutnya.</p>
            </div>
        @elseif($shipment->payment_status === 'pending' || $shipment->payment_status === 'unpaid')
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 text-slate-900 space-y-8 shadow-xl relative overflow-hidden">
                <div class="flex justify-between items-center relative z-10">
                    <div class="space-y-1">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">Tagihan Anda</p>
                        <h4 class="text-3xl font-black italic tracking-tighter text-blue-600">Rp {{ number_format($shipment->total_amount, 0, ',', '.') }}</h4>
                    </div>
                    <div class="w-16 h-16 bg-blue-50 rounded-[1.5rem] flex items-center justify-center text-blue-600 shadow-inner">
                        <i class="bi bi-credit-card-2-front fs-3"></i>
                    </div>
                </div>
                <button id="pay-button" class="w-full bg-blue-600 text-white font-black h-16 rounded-[1.5rem] shadow-xl shadow-blue-500/30 active:scale-95 transition-all flex items-center justify-center gap-3 text-xs uppercase tracking-widest">
                    <span>Bayar Sekarang</span>
                    <i class="bi bi-arrow-right fs-5"></i>
                </button>
            </div>
        @elseif(in_array($shipment->payment_status, ['failed', 'expire', 'cancel']))
            <div class="bg-rose-500 rounded-[2.5rem] p-8 text-white space-y-6 shadow-xl relative overflow-hidden">
                <div class="flex justify-between items-center relative z-10">
                    <div class="space-y-1">
                        <p class="text-rose-100 text-[10px] font-black uppercase tracking-widest">Pembayaran</p>
                        <h4 class="text-2xl font-black tracking-tight">Gagal / Kadaluarsa</h4>
                    </div>
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white border border-white/20">
                        <i class="bi bi-exclamation-octagon-fill fs-3"></i>
                    </div>
                </div>
                <button id="pay-button" class="w-full bg-white text-rose-600 font-black h-16 rounded-[1.5rem] shadow-xl active:scale-95 transition-all flex items-center justify-center gap-3 text-xs uppercase tracking-widest">
                    <span>Coba Pembayaran Lagi</span>
                    <i class="bi bi-arrow-clockwise fs-5"></i>
                </button>
            </div>
        @endif
    </div>

    <!-- Timeline Section -->
    <div class="bg-white p-8 rounded-[3rem] border border-slate-50 shadow-xl shadow-slate-200/40 space-y-10">
        <div class="flex items-center gap-3">
            <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
            <h3 class="font-black text-lg tracking-tight text-slate-900">Lacak Status</h3>
        </div>

        <div class="relative space-y-12">
            <!-- Timeline Line -->
            <div class="absolute left-[13px] top-2 bottom-2 w-0.5 bg-slate-100"></div>

            <!-- Created Status -->
            <div class="relative flex gap-5">
                <div class="w-7 h-7 rounded-xl bg-white border border-slate-200 z-10 shrink-0 flex items-center justify-center shadow-sm">
                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                </div>
                <div class="flex-1 space-y-1.5">
                    <div class="flex justify-between items-start">
                        <h4 class="font-black text-[11px] text-slate-400 uppercase tracking-widest italic">Order Dibuat</h4>
                        <span class="text-[8px] font-black text-slate-400 bg-slate-50 px-3 py-1 rounded-lg uppercase tracking-widest">{{ $shipment->created_at->format('H:i • d M') }}</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-bold leading-relaxed">Pesanan pengiriman telah berhasil dibuat di sistem.</p>
                </div>
            </div>

            <!-- Dynamic Trackings -->
            @foreach($shipment->trackings->sortBy('created_at') as $tracking)
                @php $isLatest = $loop->last; @endphp
                <div class="relative flex gap-5">
                    <div class="w-7 h-7 rounded-xl @if($isLatest) bg-blue-600 shadow-xl shadow-blue-200 @else bg-white border border-slate-200 @endif z-10 shrink-0 flex items-center justify-center transition-all">
                        @if($isLatest)
                            <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                        @else
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-600"></div>
                        @endif
                    </div>
                    <div class="flex-1 space-y-2.5 @if(!$isLatest) pb-10 border-b border-slate-50 @endif">
                        <div class="flex justify-between items-start">
                            <h4 class="font-black text-xs @if($isLatest) text-blue-600 @else text-slate-700 @endif uppercase tracking-tight">{{ $tracking->status->name }}</h4>
                            <span class="text-[8px] font-black text-slate-400 bg-slate-50 px-3 py-1 rounded-lg uppercase tracking-widest">{{ $tracking->created_at->format('H:i • d M') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                                <i class="bi bi-geo-alt-fill text-[10px]"></i>
                            </div>
                            <p class="text-[11px] text-slate-600 font-black leading-tight">{{ $tracking->location }}</p>
                        </div>
                        @if($tracking->notes)
                            <div class="p-5 bg-blue-50/30 rounded-[1.5rem] text-[10px] text-blue-600 font-bold italic border border-blue-100/30 leading-relaxed">
                                "{{ $tracking->notes }}"
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if(in_array($shipment->payment_status, ['pending', 'unpaid', 'failed', 'expire', 'cancel']))
@push('scripts')
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script type="text/javascript">
    const payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.onclick = function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            btn.disabled = true;

            fetch(`/payments/{{ $shipment->id }}/midtrans/snap-token`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyiapkan pembayaran.');
                return data;
            })
            .then(res => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (res.data && res.data.snap_token) {
                    window.snap.pay(res.data.snap_token, {
                        onSuccess: function(result) { 
                            console.log('Payment Success:', result);
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil',
                                text: 'Terima kasih, pembayaran Anda telah kami terima.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        onPending: function(result) { 
                            console.log('Payment Pending:', result);
                            Swal.fire({
                                icon: 'info',
                                title: 'Pembayaran Tertunda',
                                text: 'Mohon selesaikan pembayaran Anda sesuai instruksi.',
                                timer: 3000,
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        onError: function(result) { 
                            console.error('Payment Error:', result);
                            Swal.fire("Gagal", "Pembayaran gagal diproses. Silakan coba lagi.", "error"); 
                        },
                        onClose: function() { 
                            console.log('Customer closed the popup'); 
                        }
                    });
                } else {
                    Swal.fire("Gagal", res.message || "Gagal mendapatkan token pembayaran.", "error");
                }
            })
            .catch(err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                console.error(err);
                Swal.fire("Error", err.message || "Terjadi kesalahan sistem.", "error");
            });
        };
    }
</script>
@endpush
@endif
@endsection

