@extends('layouts.mobile')

@section('content')
<div class="space-y-6 pb-20" x-data="updateStatus()">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('courier.tasks') }}" class="p-2 -ml-2 rounded-full active:bg-slate-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-xl font-bold">Update Status</h1>
    </div>

    <!-- Shipment Brief -->
    <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-lg shadow-indigo-200 dark:shadow-none">
        <div class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Tracking Number</div>
        <div class="text-2xl font-black mb-4">{{ $shipment->tracking_number }}</div>
        <div class="flex items-center gap-2 text-xs font-bold bg-white/20 w-fit px-3 py-1 rounded-full">
            <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
            {{ $shipment->status->name }}
        </div>
    </div>

    <form action="{{ route('courier.shipments.update', $shipment) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Barcode Scanner Trigger -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Verifikasi Barcode</h2>
            <button type="button" @click="startScanner()" class="w-full py-4 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center gap-3 font-bold text-slate-600 dark:text-slate-300 active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 17h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Scan Barcode Label
            </button>
            <div id="reader" x-show="showScanner" class="overflow-hidden rounded-3xl border-4 border-indigo-500 bg-black"></div>
        </div>

        <!-- Status Selection -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pilih Status Baru</h2>
            <div class="grid grid-cols-1 gap-2">
                @php
                    $nextStatuses = [
                        'in_transit' => 'Dalam Perjalanan',
                        'out_for_delivery' => 'Kurir Menuju Lokasi',
                        'delivered' => 'Sampai Tujuan (Selesai)',
                        'failed_delivery' => 'Gagal Kirim (Coba Lagi)',
                    ];
                @endphp
                @foreach($nextStatuses as $code => $name)
                    <label class="relative block group">
                        <input type="radio" name="status_code" value="{{ $code }}" x-model="form.status_code" class="peer hidden">
                        <div class="p-4 rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20 transition-all font-bold flex items-center justify-between">
                            <span class="text-sm">{{ $name }}</span>
                            <div class="w-5 h-5 rounded-full border-2 border-slate-200 peer-checked:border-indigo-600 flex items-center justify-center">
                                <div x-show="form.status_code === '{{ $code }}'" class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Proof Photo -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Foto Bukti (Opsional)</h2>
            <div class="relative group">
                <input type="file" name="proof_photo" accept="image/*" capture="environment" @change="previewImage($event)" class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer">
                <div class="w-full aspect-video rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex flex-col items-center justify-center gap-3 overflow-hidden">
                    <template x-if="!imagePreview">
                        <div class="text-center">
                            <div class="p-4 bg-white dark:bg-slate-800 rounded-full shadow-sm mx-auto w-fit mb-3">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-xs font-bold text-slate-500">Ambil Foto / Upload</span>
                        </div>
                    </template>
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="w-full h-full object-cover">
                    </template>
                </div>
            </div>
        </div>

        <!-- Notes & Location -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Detail Tambahan</h2>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <input type="text" name="location" placeholder="Lokasi Saat Ini (cth: Bandung Hub)" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                <textarea name="notes" placeholder="Catatan Tambahan..." rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        <!-- GPS Data -->
        <input type="hidden" name="gps_lat" x-model="gps.lat">
        <input type="hidden" name="gps_lng" x-model="gps.lng">

        <button type="submit" class="w-full bg-indigo-600 text-white font-black py-5 rounded-2xl shadow-xl shadow-indigo-100 dark:shadow-none active:scale-95 transition-all">
            Simpan Perubahan
        </button>
    </form>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function updateStatus() {
        return {
            form: {
                status_code: ''
            },
            showScanner: false,
            imagePreview: null,
            gps: { lat: '', lng: '' },
            
            init() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        this.gps.lat = pos.coords.latitude;
                        this.gps.lng = pos.coords.longitude;
                    });
                }
            },

            startScanner() {
                this.showScanner = true;
                const html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                    if (decodedText === "{{ $shipment->tracking_number }}") {
                        alert("Barcode terverifikasi!");
                        html5QrCode.stop();
                        this.showScanner = false;
                    } else {
                        alert("Barcode tidak cocok: " + decodedText);
                    }
                });
            },

            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.imagePreview = URL.createObjectURL(file);
                }
            }
        }
    }
</script>
@endsection
