<?php $__env->startSection('content'); ?>
<div class="space-y-8 animate-slide-up pb-32" x-data="bulkUpdateForm()">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 px-1">
        <a href="<?php echo e(route('courier.shipments.index')); ?>" class="w-12 h-12 glass rounded-2xl flex items-center justify-center shadow-sm text-blue-600 tap-scale">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-black tracking-tight">Update Jamak</h2>
    </div>

    <?php if($errors->any()): ?>
        <div class="glass p-6 rounded-[2rem] border-rose-100 bg-rose-50/30 mb-4">
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
        <div class="glass p-6 rounded-[2rem] border-rose-100 bg-rose-50/30 mb-4">
            <div class="flex gap-4 text-rose-600">
                <i class="bi bi-x-circle-fill fs-4"></i>
                <div class="space-y-1">
                    <p class="text-xs font-black uppercase tracking-widest">Gagal Update</p>
                    <p class="text-xs font-bold opacity-80"><?php echo e(session('error')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(session('info')): ?>
        <div class="glass p-6 rounded-[2rem] border-blue-100 bg-blue-50/30 mb-4">
            <div class="flex gap-4 text-blue-600">
                <i class="bi bi-info-circle-fill fs-4"></i>
                <div class="space-y-1">
                    <p class="text-xs font-black uppercase tracking-widest">Informasi</p>
                    <p class="text-xs font-bold opacity-80"><?php echo e(session('info')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Shipment Info Summary (Bulk Version) -->
    <div class="relative group">
        <div class="absolute inset-0 bg-slate-900 rounded-[2.5rem] blur-2xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
        <div class="relative bg-slate-900 rounded-[2.5rem] p-8 text-white overflow-hidden shadow-2xl border border-white/10">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-blue-500/10 rounded-full blur-[80px]"></div>
            <div class="relative z-10 space-y-5">
                <div class="space-y-1">
                    <p class="text-blue-400 text-[10px] font-black uppercase tracking-[0.2em] leading-none">Paket Terpilih</p>
                    <h3 class="text-3xl font-black italic tracking-tighter uppercase leading-none"><?php echo e($shipments->count()); ?> <span class="text-blue-500">Resi</span></h3>
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    <?php $__currentLoopData = $shipments->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-3 py-1 bg-white/5 rounded-lg border border-white/10 text-[8px] font-bold text-slate-400">
                            <?php echo e($s->tracking_number); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($shipments->count() > 5): ?>
                        <div class="px-3 py-1 bg-white/5 rounded-lg border border-white/10 text-[8px] font-bold text-blue-400">
                            +<?php echo e($shipments->count() - 5); ?> lainnya
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('courier.shipments.bulk-update')); ?>" method="POST" enctype="multipart/form-data" class="space-y-10">
        <?php echo csrf_field(); ?>
        <?php $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <input type="hidden" name="shipment_ids[]" value="<?php echo e($s->id); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <input type="hidden" name="gps_lat" x-model="gps.lat">
        <input type="hidden" name="gps_lng" x-model="gps.lng">

        <!-- Status Selection -->
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
                    ];
                ?>

                <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="relative cursor-pointer tap-scale group">
                        <input type="radio" name="status_code" value="<?php echo e($opt['code']); ?>" x-model="form.status_code" class="peer hidden" required>
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

        <!-- Evidence & Details -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 px-1">
                <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-xs uppercase tracking-[0.15em] text-slate-400">Detail Update</h3>
            </div>

            <div class="glass p-8 rounded-[2.5rem] space-y-8 border-blue-50">
                <!-- Location & Notes -->
                <div class="space-y-5">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Lokasi & Keterangan</label>
                        
                        <div class="flex flex-wrap gap-2 mb-2">
                            <?php
                                $branchName = $courier->branch ? $courier->branch->name : 'Gudang Hub';
                                $locations = [$branchName, 'Dalam Perjalanan', 'Kantor Cabang', 'Tiba di Tujuan'];
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
                            <input type="text" name="location" x-model="form.location" placeholder="Ketik lokasi spesifik..." required
                                   class="w-full bg-slate-50 dark:bg-slate-900/50 border-none h-16 pl-14 pr-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-blue-600 transition-all shadow-inner">
                        </div>
                    </div>

                    <!-- GPS Status -->
                    <div class="flex items-center gap-2 px-2">
                        <div class="w-2 h-2 rounded-full animate-pulse" :class="gps.lat ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                        <p class="text-[8px] font-black uppercase tracking-widest" :class="gps.lat ? 'text-emerald-600' : 'text-rose-600'">
                            <span x-text="gps.lat ? 'GPS Locked' : 'Menunggu GPS...'"></span>
                        </p>
                    </div>

                    <textarea name="notes" placeholder="Catatan untuk semua paket ini..." rows="3" 
                              class="w-full bg-slate-50 dark:bg-slate-900/50 border-none p-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-blue-600 transition-all shadow-inner"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pb-10 px-1">
            <button type="submit" 
                    class="w-full bg-blue-600 text-white font-black h-16 rounded-[2rem] shadow-xl shadow-blue-500/30 tap-scale transition-all flex items-center justify-center gap-3 uppercase tracking-[0.2em] text-xs">
                <span>Konfirmasi Update Jamak</span>
                <i class="bi bi-check-circle-fill fs-5"></i>
            </button>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function bulkUpdateForm() {
        return {
            form: { 
                status_code: '',
                location: ''
            },
            gps: { lat: null, lng: null },
            init() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition((position) => {
                        this.gps.lat = position.coords.latitude;
                        this.gps.lng = position.coords.longitude;
                    }, null, { enableHighAccuracy: true });
                }
            }
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('mobile.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/mobile/courier/bulk_update_status.blade.php ENDPATH**/ ?>