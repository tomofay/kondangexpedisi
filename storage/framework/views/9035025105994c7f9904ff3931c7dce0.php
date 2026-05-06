<?php $__env->startSection('content'); ?>
<div class="space-y-8 animate-slide-up pb-32" x-data="updateStatusForm()">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 px-1">
        <a href="<?php echo e(route('courier.tasks')); ?>" class="w-12 h-12 glass rounded-2xl flex items-center justify-center shadow-sm text-blue-600 tap-scale">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-black tracking-tight">Update Status</h2>
    </div>

    <?php if($errors->any()): ?>
        <div class="glass p-6 rounded-[2rem] border-rose-100 bg-rose-50/30">
            <div class="flex gap-4">
                <i class="bi bi-exclamation-octagon-fill text-rose-600 fs-4"></i>
                <div class="space-y-1">
                    <p class="text-xs font-black uppercase tracking-widest text-rose-600">Terjadi Kesalahan</p>
                    <ul class="text-xs font-bold text-rose-500/80 list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="glass p-6 rounded-[2rem] border-rose-100 bg-rose-50/30">
            <div class="flex gap-4 text-rose-600">
                <i class="bi bi-x-circle-fill fs-4"></i>
                <div class="space-y-1">
                    <p class="text-xs font-black uppercase tracking-widest">Gagal Update</p>
                    <p class="text-xs font-bold opacity-80"><?php echo e(session('error')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Shipment Info Summary (Upgraded) -->
    <div class="relative group">
        <div class="absolute inset-0 bg-blue-600 rounded-[2.5rem] blur-2xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
        <div class="relative bg-blue-600 rounded-[2.5rem] p-8 text-white overflow-hidden shadow-2xl">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-blue-500/30 rounded-full blur-[80px]"></div>
            <div class="relative z-10 space-y-5">
                <div class="space-y-1">
                    <p class="text-blue-200/60 text-[10px] font-black uppercase tracking-[0.2em] leading-none">Nomor Resi</p>
                    <h3 class="text-3xl font-black italic tracking-tighter uppercase leading-none"><?php echo e($shipment->tracking_number); ?></h3>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="flex flex-col">
                        <p class="text-[9px] font-black text-blue-300 uppercase tracking-widest leading-none mb-1">Penerima</p>
                        <p class="text-xs font-bold text-white truncate"><?php echo e($shipment->recipient_name); ?></p>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-[9px] font-black text-blue-300 uppercase tracking-widest leading-none mb-1">Rute</p>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-white truncate"><?php echo e($shipment->branch->name); ?></span>
                            <i class="bi bi-arrow-right text-[10px] text-blue-300"></i>
                            <span class="text-[10px] font-black text-white truncate"><?php echo e($shipment->destinationBranch->name); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="mt-4 pt-4 border-t border-white/10">
                    <p class="text-[9px] font-black text-blue-300 uppercase tracking-widest leading-none mb-2">Isi Paket</p>
                    <div class="space-y-1.5">
                        <?php $__empty_1 = true; $__currentLoopData = $shipment->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="flex justify-between items-center text-[10px] bg-white/5 p-2 rounded-lg border border-white/5">
                                <div class="flex flex-col">
                                    <span class="font-bold text-white/90"><?php echo e($item->item_name); ?></span>
                                    <span class="text-[8px] text-white/40 uppercase tracking-tighter"><?php echo e($item->weight_kg); ?> Kg / Item</span>
                                </div>
                                <span class="font-black text-blue-300"><?php echo e($item->quantity); ?> Pcs</span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-[10px] text-white/40 italic">Tidak ada rincian barang</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('courier.shipments.update', $shipment)); ?>" method="POST" enctype="multipart/form-data" class="space-y-10">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="gps_lat" x-model="gps.lat">
        <input type="hidden" name="gps_lng" x-model="gps.lng">

        <!-- Status Selection (Upgraded Tiles) -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 px-1">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-xs uppercase tracking-[0.15em] text-slate-400">Pilih Status Baru</h3>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <?php
                    $statusOptions = [
                        ['code' => 'picked_up', 'name' => 'Pick Up', 'icon' => 'bi-box-seam'],
                        ['code' => 'arrived_at_origin', 'name' => 'Tiba Asal', 'icon' => 'bi-geo-alt'],
                        ['code' => 'departed_from_origin', 'name' => 'Keluar Asal', 'icon' => 'bi-truck-flatbed'],
                        ['code' => 'in_transit', 'name' => 'In Transit', 'icon' => 'bi-truck'],
                        ['code' => 'arrived_at_destination', 'name' => 'Tiba Tujuan', 'icon' => 'bi-geo-fill'],
                        ['code' => 'out_for_delivery', 'name' => 'Delivery', 'icon' => 'bi-bicycle'],
                        ['code' => 'delivered', 'name' => 'Selesai', 'icon' => 'bi-patch-check-fill'],
                        ['code' => 'failed_delivery', 'name' => 'Gagal', 'icon' => 'bi-exclamation-triangle-fill'],
                        ['code' => 'returned', 'name' => 'Return', 'icon' => 'bi-arrow-left-right'],
                    ];
                ?>

                <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="relative cursor-pointer tap-scale group">
                        <input type="radio" name="status_code" value="<?php echo e($opt['code']); ?>" x-model="form.status_code" class="peer hidden">
                        <div class="glass p-5 rounded-[2rem] flex flex-col items-center gap-3 border-blue-50 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all shadow-sm h-full">
                            <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800/50 rounded-2xl flex items-center justify-center text-blue-600 peer-checked:bg-white/20 peer-checked:text-white transition-colors border-none group-hover:scale-110 transition-transform shrink-0">
                                <i class="bi <?php echo e($opt['icon']); ?> fs-4"></i>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest leading-none text-center"><?php echo e($opt['name']); ?></span>
                        </div>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Evidence & Details (Modernized) -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 px-1">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-xs uppercase tracking-[0.15em] text-slate-400">Bukti Operasional</h3>
            </div>

            <div class="glass p-8 rounded-[2.5rem] space-y-8 border-blue-50">
                <!-- Photo Upload -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Foto Bukti</label>
                        <span x-show="['delivered', 'failed_delivery', 'returned'].includes(form.status_code)" 
                              class="text-[9px] font-black text-rose-500 uppercase tracking-widest animate-pulse">Wajib</span>
                    </div>
                    <div class="relative group">
                        <input type="file" name="proof_photo" accept="image/*" capture="environment" @change="previewImage($event)" class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer">
                        <div class="w-full aspect-video bg-slate-50 dark:bg-slate-900/50 rounded-3xl flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-800 transition-all overflow-hidden relative group-hover:border-blue-300">
                            <template x-if="!imagePreview">
                                <div class="text-center space-y-3">
                                    <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto text-blue-600 shadow-sm border border-slate-100 dark:border-slate-700">
                                        <i class="bi bi-camera-fill fs-3"></i>
                                    </div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ketuk untuk Ambil Foto</p>
                                </div>
                            </template>
                            <template x-if="imagePreview">
                                <div class="w-full h-full relative">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-blue-600/20 mix-blend-overlay"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Location & Notes -->
                <div class="space-y-5">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Lokasi & Keterangan</label>
                        
                        <!-- Quick Location Selection -->
                        <div class="flex flex-wrap gap-2 mb-2">
                            <?php
                                $branchName = $courier->branch ? $courier->branch->name : 'Gudang Hub';
                                $locations = [$branchName, 'Dalam Perjalanan', 'Rumah Penerima', 'Tetangga/Satpam', 'Kantor Pos'];
                            ?>
                            <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" @click="form.location = '<?php echo e($loc); ?>'" 
                                        class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl text-[9px] font-black uppercase tracking-widest border border-transparent transition-all"
                                        :class="form.location === '<?php echo e($loc); ?>' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'text-slate-400'">
                                    <?php echo e($loc); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none text-blue-600">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <input type="text" name="location" x-model="form.location" placeholder="Ketik lokasi spesifik..." 
                                   class="w-full bg-slate-50 dark:bg-slate-900/50 border-none h-16 pl-14 pr-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-blue-600 transition-all shadow-inner">
                        </div>
                    </div>

                    <!-- GPS Status Badge -->
                    <div class="flex items-center gap-2 px-2">
                        <div class="w-2 h-2 rounded-full animate-pulse" :class="gps.lat ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                        <p class="text-[8px] font-black uppercase tracking-widest" :class="gps.lat ? 'text-emerald-600' : 'text-rose-600'">
                            <span x-text="gps.lat ? 'GPS Locked: ' + gps.lat.toFixed(4) + ', ' + gps.lng.toFixed(4) : 'Menunggu Sinyal GPS...'"></span>
                        </p>
                    </div>

                    <textarea name="notes" placeholder="Catatan tambahan (Opsional)..." rows="3" 
                              class="w-full bg-slate-50 dark:bg-slate-900/50 border-none p-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-blue-600 transition-all shadow-inner"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pb-10 px-1">
            <button type="submit" 
                    class="w-full bg-blue-600 text-white font-black h-16 rounded-[2rem] shadow-xl shadow-blue-500/30 tap-scale transition-all flex items-center justify-center gap-3 uppercase tracking-[0.2em] text-xs">
                <span>Konfirmasi Update</span>
                <i class="bi bi-check-circle-fill fs-5"></i>
            </button>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function updateStatusForm() {
        return {
            form: { 
                status_code: '<?php echo e($shipment->status->code); ?>',
                location: ''
            },
            gps: { lat: null, lng: null },
            imagePreview: null,
            init() {
                this.fetchGPS();
            },
            fetchGPS() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition((position) => {
                        this.gps.lat = position.coords.latitude;
                        this.gps.lng = position.coords.longitude;
                    }, (error) => {
                        console.warn("GPS Access Denied:", error);
                    }, { enableHighAccuracy: true });
                }
            },
            previewImage(event) {
                const file = event.target.files[0];
                if (file) this.imagePreview = URL.createObjectURL(file);
            }
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('mobile.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/mobile/courier/update_status.blade.php ENDPATH**/ ?>