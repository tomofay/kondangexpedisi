<?php $__env->startSection('content'); ?>
<div class="space-y-8 animate-slide-up pb-32" x-data="bookingForm()">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('customer.dashboard')); ?>" class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center shadow-sm text-slate-400">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-extrabold tracking-tight">Kirim Paket Baru</h2>
    </div>

    <form action="<?php echo e(route('customer.shipments.store')); ?>" method="POST" class="space-y-10">
        <?php echo csrf_field(); ?>

        <!-- Rute -->
        <div class="space-y-6">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="font-extrabold text-lg uppercase tracking-tight">Rute Pengiriman</h3>
            </div>
            
            <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Cabang Asal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-primary-600">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <select name="branch_id" x-model="form.branch_id" @change="updateQuote()" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 pl-12 pr-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600 transition-all appearance-none">
                            <option value="">Pilih Cabang</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="flex justify-center -my-3 relative z-10">
                    <div class="w-10 h-10 bg-primary-600 text-white rounded-xl shadow-lg flex items-center justify-center">
                        <i class="bi bi-arrow-down fs-5"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Cabang Tujuan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none text-emerald-600">
                            <i class="bi bi-geo-fill"></i>
                        </div>
                        <select name="destination_branch_id" x-model="form.destination_branch_id" @change="updateQuote()" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 pl-12 pr-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600 transition-all appearance-none">
                            <option value="">Pilih Cabang</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Pengirim & Penerima -->
        <div class="space-y-6">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="font-extrabold text-lg uppercase tracking-tight">Informasi Alamat</h3>
            </div>

            <!-- Sender -->
            <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-8 space-y-4">
                <h4 class="text-[10px] font-black text-primary-600 uppercase tracking-widest mb-4">Data Pengirim</h4>
                <input type="text" name="sender_name" value="<?php echo e(auth()->user()->name); ?>" placeholder="Nama Pengirim" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                <input type="tel" name="sender_phone" value="<?php echo e(auth()->user()->phone); ?>" placeholder="No. Telepon" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                <textarea name="sender_address" placeholder="Alamat Lengkap Pengirim" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none p-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600"><?php echo e(auth()->user()->address); ?></textarea>
            </div>

            <!-- Recipient -->
            <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-8 space-y-4">
                <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4">Data Penerima</h4>
                <input type="text" name="recipient_name" placeholder="Nama Penerima" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                <input type="tel" name="recipient_phone" placeholder="No. Telepon Penerima" class="w-full bg-slate-50 dark:bg-slate-800 border-none h-14 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600">
                <textarea name="recipient_address" placeholder="Alamat Lengkap Penerima" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none p-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-primary-600"></textarea>
            </div>
        </div>

        <!-- Paket -->
        <div class="space-y-6">
            <div class="flex items-center gap-2 px-1">
                <div class="w-1 h-6 bg-primary-600 rounded-full"></div>
                <h3 class="font-extrabold text-lg uppercase tracking-tight">Detail Paket</h3>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-4xl premium-shadow border border-slate-100 dark:border-slate-800 p-8 space-y-8">
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Layanan Pengiriman</label>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="type in serviceTypes">
                            <button type="button" 
                                    @click="form.service_type = type.id; updateQuote()"
                                    :class="form.service_type === type.id ? 'bg-primary-600 text-white shadow-xl shadow-primary-100 scale-[1.02]' : 'bg-slate-50 dark:bg-slate-800 text-slate-400'"
                                    class="h-16 rounded-2xl font-extrabold text-[10px] uppercase tracking-widest transition-all">
                                <span x-text="type.name"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="service_type" x-model="form.service_type">
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Estimasi Berat (KG)</label>
                    <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800 rounded-2xl p-2 h-16">
                        <button type="button" @click="form.total_weight_kg = Math.max(0.1, parseFloat(form.total_weight_kg) - 0.5).toFixed(1); updateQuote()" class="w-12 h-12 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center text-primary-600 font-black shadow-sm">-</button>
                        <input type="number" name="total_weight_kg" x-model="form.total_weight_kg" step="0.1" @input="updateQuote()" class="bg-transparent border-none text-center font-black text-xl focus:ring-0 w-20">
                        <button type="button" @click="form.total_weight_kg = (parseFloat(form.total_weight_kg) + 0.5).toFixed(1); updateQuote()" class="w-12 h-12 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center text-primary-600 font-black shadow-sm">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer Summary -->
        <div class="fixed bottom-0 left-0 right-0 p-6 glass border-t border-slate-100 dark:border-slate-800 z-50 rounded-t-4xl">
            <div class="max-w-md mx-auto flex items-center justify-between gap-6">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Estimasi</p>
                    <p class="text-2xl font-black text-primary-600 italic" x-text="loading ? '...' : formatRupiah(quote.total_amount)">Rp 0</p>
                </div>
                <button type="submit" 
                        :disabled="loading || !quote.total_amount"
                        class="flex-1 bg-primary-600 text-white font-black h-16 rounded-3xl shadow-2xl shadow-primary-200 dark:shadow-none active:scale-95 disabled:opacity-50 disabled:grayscale transition-all text-sm uppercase tracking-widest">
                    Booking
                </button>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function bookingForm() {
        return {
            form: {
                branch_id: '',
                destination_branch_id: '',
                service_type: 'regular',
                total_weight_kg: 1.0,
                insurance_amount: 0
            },
            serviceTypes: [
                { id: 'regular', name: 'Regular' },
                { id: 'express', name: 'Express' },
                { id: 'economy', name: 'Economy' },
                { id: 'same_day', name: 'Sameday' }
            ],
            quote: { total_amount: 0 },
            loading: false,
            updateQuote() {
                if (!this.form.branch_id || !this.form.destination_branch_id) return;
                this.loading = true;
                const params = new URLSearchParams(this.form);
                fetch(`<?php echo e(route('customer.shipments.quote')); ?>?${params.toString()}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') this.quote = res.data;
                    })
                    .finally(() => this.loading = false);
            },
            formatRupiah(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(amount);
            }
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('mobile.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/mobile/customer/create_shipment.blade.php ENDPATH**/ ?>