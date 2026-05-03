@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up pb-32" x-data="updateStatusForm()">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('courier.tasks') }}" class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center shadow-sm text-slate-400">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-extrabold tracking-tight">Update Status</h2>
    </div>

    <!-- Shipment Info Summary -->
    <div class="bg-primary-600 rounded-4xl p-8 text-white relative overflow-hidden shadow-2xl shadow-primary-100 dark:shadow-none">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 space-y-2">
            <p class="text-[10px] font-black text-primary-200 uppercase tracking-widest">Tracking Number</p>
            <h3 class="text-3xl font-black uppercase italic">{{ $shipment->tracking_number }}</h3>
            <p class="text-sm font-bold text-primary-100">{{ $shipment->recipient_name }} • {{ $shipment->service_type }}</p>
        </div>
    </div>

    <form action="{{ route('courier.shipments.update', $shipment) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf

        <!-- Barcode Scan -->
        <div class="space-y-6">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="font-extrabold text-lg uppercase tracking-tight">Verifikasi Paket</h3>
            </div>
            
            <button type="button" @click="startScanner()" class="w-full h-20 bg-white dark:bg-slate-900 rounded-3xl premium-shadow flex items-center justify-center gap-4 font-black text-sm uppercase tracking-widest text-slate-600 dark:text-slate-300 active:scale-95 transition-all border border-slate-100 dark:border-slate-800">
                <i class="bi bi-qr-code-scan fs-4 text-primary-600"></i>
                Scan Barcode
            </button>
            <div id="reader" x-show="showScanner" class="overflow-hidden rounded-4xl border-4 border-primary-600 bg-black aspect-square mt-4"></div>
        </div>

        <!-- Status Selection -->
        <div class="space-y-6">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="font-extrabold text-lg uppercase tracking-tight">Status Baru</h3>
            </div>

            <div class="grid grid-cols-2 gap-4">
                @php
                    $statusOptions = [
                        ['code' => 'pickup', 'name' => 'Pick Up', 'icon' => 'bi-box-seam', 'color' => 'blue'],
                        ['code' => 'in_transit', 'name' => 'In Transit', 'icon' => 'bi-truck', 'color' => 'indigo'],
                        ['code' => 'out_for_delivery', 'name' => 'Delivery', 'icon' => 'bi-bicycle', 'color' => 'emerald'],
                        ['code' => 'delivered', 'name' => 'Selesai', 'icon' => 'bi-check2-circle', 'color' => 'emerald'],
                        ['code' => 'failed', 'name' => 'Gagal', 'icon' => 'bi-exclamation-triangle', 'color' => 'red'],
                        ['code' => 'returned', 'name' => 'Return', 'icon' => 'bi-arrow-left-right', 'color' => 'orange'],
                    ];
                @endphp

                @foreach($statusOptions as $opt)
                    <label class="relative group cursor-pointer">
                        <input type="radio" name="status_code" value="{{ $opt['code'] }}" x-model="form.status_code" class="peer hidden">
                        <div class="bg-white dark:bg-slate-900 border-2 border-transparent peer-checked:border-primary-600 p-6 rounded-4xl flex flex-col items-center gap-3 premium-shadow transition-all group-active:scale-95 h-full">
                            <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800 text-slate-400 peer-checked:text-primary-600 rounded-2xl flex items-center justify-center transition-colors">
                                <i class="bi {{ $opt['icon'] }} fs-4"></i>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-primary-600">{{ $opt['name'] }}</span>
                        </div>
                        <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                            <i class="bi bi-check-circle-fill text-primary-600"></i>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Evidence & Details -->
        <div class="space-y-6">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="font-extrabold text-lg uppercase tracking-tight">Bukti Operasional</h3>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-8 space-y-6">
                <!-- Photo Upload -->
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Foto Bukti (Opsional)</label>
                    <div class="relative group">
                        <input type="file" name="proof_photo" accept="image/*" capture="environment" @change="previewImage($event)" class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer">
                        <div class="w-full aspect-video bg-slate-50 dark:bg-slate-800 rounded-3xl flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700 transition-all overflow-hidden group-hover:bg-slate-100 dark:group-hover:bg-slate-800/80">
                            <template x-if="!imagePreview">
                                <div class="text-center space-y-2">
                                    <i class="bi bi-camera fs-1 text-slate-300"></i>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ambil Foto</p>
                                </div>
                            </template>
                            <template x-if="imagePreview">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Location & Notes -->
                <div class="space-y-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-slate-400">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <input type="text" name="location" placeholder="Lokasi Terkini" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 pl-12 pr-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                    </div>
                    <textarea name="notes" placeholder="Catatan tambahan (contoh: diterima oleh satpam)" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none p-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600"></textarea>
                </div>
            </div>
        </div>

        <!-- Sticky Submit -->
        <div class="fixed bottom-0 left-0 right-0 p-6 glass border-t border-slate-100 dark:border-slate-800 z-50 rounded-t-4xl">
            <button type="submit" class="w-full bg-primary-600 text-white font-black h-16 rounded-3xl shadow-2xl shadow-primary-200 dark:shadow-none active:scale-95 transition-all text-sm uppercase tracking-widest">
                Update Status Paket
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function updateStatusForm() {
        return {
            form: { status_code: '{{ $shipment->status->code }}' },
            showScanner: false,
            imagePreview: null,
            startScanner() {
                this.showScanner = true;
                const html5QrCode = new Html5Qrcode("reader");
                html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, (text) => {
                    if (text === "{{ $shipment->tracking_number }}") {
                        alert("Berhasil Verifikasi!");
                        html5QrCode.stop();
                        this.showScanner = false;
                    }
                });
            },
            previewImage(event) {
                const file = event.target.files[0];
                if (file) this.imagePreview = URL.createObjectURL(file);
            }
        }
    }
</script>
@endpush
@endsection
