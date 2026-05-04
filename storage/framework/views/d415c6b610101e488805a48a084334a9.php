<?php $__env->startSection('content'); ?>
<div class="space-y-10 animate-slide-up pb-96 px-1" x-data="bookingForm()">
    <!-- Header Section -->
    <div class="flex items-center justify-between px-2">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('customer.dashboard')); ?>" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-soft text-blue-600 tap-scale border border-slate-50">
                <i class="bi bi-chevron-left fs-5"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-900 leading-none">Buat Pesanan</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5">Kirim Paket Baru</p>
            </div>
        </div>
    </div>

    <!-- Step Progress Indicator -->
    <div class="px-2">
        <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 text-white rounded-xl flex items-center justify-center text-[10px] font-black shadow-lg shadow-blue-200">1</div>
                <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Detail</p>
            </div>
            <div class="flex-1 h-[2px] bg-slate-50 mx-4">
                <div class="h-full bg-blue-100 w-1/2"></div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('customer.shipments.store')); ?>" method="POST" class="space-y-10">
        <?php echo csrf_field(); ?>
        
        <!-- Alamat Pengiriman -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-[11px] uppercase tracking-[0.15em] text-slate-400">Rute & Alamat</h3>
            </div>
            
            <div class="bg-white p-8 rounded-[3rem] space-y-8 shadow-xl shadow-slate-200/40 border border-slate-50">
                <!-- Branches -->
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Cabang Asal</label>
                        <div class="relative">
                            <select name="branch_id" x-model="form.branch_id" @change="updateQuote()" required
                                    class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all appearance-none text-slate-700 shadow-inner">
                                <option value="">Pilih Cabang Asal</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <i class="bi bi-geo-alt-fill absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                        </div>
                    </div>

                    <div class="flex justify-center -my-2 relative z-10">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl shadow-xl shadow-blue-200 flex items-center justify-center border-4 border-white tap-scale">
                            <i class="bi bi-arrow-down-up fs-5"></i>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Cabang Tujuan</label>
                        <div class="relative">
                            <select name="destination_branch_id" x-model="form.destination_branch_id" @change="updateQuote()" required
                                    class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all appearance-none text-slate-700 shadow-inner">
                                <option value="">Pilih Cabang Tujuan</option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <i class="bi bi-send-fill absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                        </div>
                    </div>
                </div>

                <div class="h-[1px] bg-slate-50"></div>

                <!-- Sender Info -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="bi bi-person-badge text-blue-600"></i>
                        <h4 class="font-black text-[9px] uppercase tracking-widest text-slate-900">Informasi Pengirim</h4>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Pengirim</label>
                        <div class="relative">
                            <input type="text" name="sender_name" value="<?php echo e($customer->name); ?>" required 
                                   class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                                   placeholder="Nama lengkap pengirim">
                            <i class="bi bi-person-fill absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                        </div>
                    </div>
                    
                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nomor WhatsApp</label>
                        <div class="relative">
                            <input type="tel" name="sender_phone" value="<?php echo e($customer->phone); ?>" required 
                                   class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                                   placeholder="08xxxxxxxxxx">
                            <i class="bi bi-whatsapp absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alamat Pengirim</label>
                        <textarea name="sender_address" required rows="2"
                                  class="w-full bg-slate-50 p-6 rounded-[2rem] border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                                  placeholder="Alamat penjemputan paket..."><?php echo e($customer->address); ?></textarea>
                    </div>
                </div>

                <div class="h-[1px] bg-slate-50"></div>

                <!-- Recipient Info -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="bi bi-box-arrow-in-right text-blue-600"></i>
                        <h4 class="font-black text-[9px] uppercase tracking-widest text-slate-900">Informasi Penerima</h4>
                    </div>
                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Penerima</label>
                        <div class="relative">
                            <input type="text" name="recipient_name" required 
                                   class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                                   placeholder="Siapa penerimanya?">
                            <i class="bi bi-person-fill absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                        </div>
                    </div>
                    
                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nomor WhatsApp</label>
                        <div class="relative">
                            <input type="tel" name="recipient_phone" required 
                                   class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                                   placeholder="08xxxxxxxxxx">
                            <i class="bi bi-whatsapp absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alamat Lengkap</label>
                        <textarea name="recipient_address" required rows="3"
                                  class="w-full bg-slate-50 p-6 rounded-[2rem] border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                                  placeholder="Jl. Contoh No. 123, Kelurahan, Kecamatan..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Paket -->
        <div class="space-y-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-1 h-6 bg-blue-600 rounded-full"></div>
                <h3 class="font-black text-[11px] uppercase tracking-[0.15em] text-slate-400">Informasi Paket</h3>
            </div>
            
            <div class="bg-white p-8 rounded-[3rem] space-y-8 shadow-xl shadow-slate-200/40 border border-slate-50">
                <div class="space-y-2.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Barang</label>
                    <div class="relative">
                        <input type="text" name="item_name" required 
                               class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                               placeholder="Contoh: Pakaian, Sepatu, Elektronik...">
                        <i class="bi bi-box-fill absolute right-6 top-1/2 -translate-y-1/2 text-blue-600/30"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center block">Berat / Item (Kg)</label>
                        <div class="flex items-center justify-between bg-slate-50 rounded-2xl p-2 shadow-inner">
                            <button type="button" @click="form.weight_per_item = Math.max(0.5, parseFloat(form.weight_per_item) - 0.5).toFixed(1); updateQuote()"
                                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm tap-scale">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" step="0.1" x-model="form.weight_per_item" readonly
                                   class="w-12 bg-transparent text-center font-black text-sm border-none focus:ring-0 text-slate-700">
                            <button type="button" @click="form.weight_per_item = (parseFloat(form.weight_per_item) + 0.5).toFixed(1); updateQuote()"
                                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm tap-scale">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center block">Jumlah Item</label>
                        <div class="flex items-center justify-between bg-slate-50 rounded-2xl p-2 shadow-inner">
                            <button type="button" @click="form.total_items = Math.max(1, form.total_items - 1); updateQuote()"
                                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm tap-scale">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" name="total_items" x-model="form.total_items" readonly
                                   class="w-12 bg-transparent text-center font-black text-sm border-none focus:ring-0 text-slate-700">
                            <button type="button" @click="form.total_items++; updateQuote()"
                                    class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm tap-scale">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="total_weight_kg" :value="form.weight_per_item * form.total_items">

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-center block">Pilih Layanan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php $__currentLoopData = ['express', 'regular', 'economy', 'same_day']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="relative cursor-pointer tap-scale group">
                            <input type="radio" name="service_type" value="<?php echo e($type); ?>" class="peer hidden" 
                                   x-model="form.service_type" @change="updateQuote()">
                            <div class="bg-slate-50 p-4 rounded-2xl text-center border border-transparent peer-checked:bg-blue-600 peer-checked:text-white transition-all shadow-sm group-hover:bg-slate-100 peer-checked:group-hover:bg-blue-600">
                                <span class="text-[9px] font-black uppercase tracking-widest italic"><?php echo e(str_replace('_', ' ', $type)); ?></span>
                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Catatan (Opsional)</label>
                    <input type="text" name="notes" 
                           class="w-full bg-slate-50 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-blue-600 font-bold text-sm transition-all text-slate-700 shadow-inner"
                           placeholder="Fragile, jangan dibanting, dll...">
                </div>
            </div>
        </div>



        <!-- Bottom Payment Bar -->
        <div class="fixed bottom-28 left-4 right-4 z-40 bg-white/95 backdrop-blur-2xl p-6 rounded-[2.5rem] shadow-[0_-20px_50px_rgba(0,0,0,0.1)] border border-white animate-slide-up">
            <div class="grid grid-cols-2 gap-4 mb-5 border-b border-slate-50 pb-5">
                <div class="space-y-0.5">
                    <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest leading-none">Berat Total</p>
                    <p class="text-[11px] font-black text-slate-900 leading-none"><span x-text="(form.weight_per_item * form.total_items).toFixed(1)"></span> Kg</p>
                </div>
                <div class="text-right space-y-0.5">
                    <p class="text-slate-400 text-[8px] font-black uppercase tracking-widest leading-none">Estimasi Sampai</p>
                    <p class="text-[11px] font-black text-blue-600 leading-none" x-text="getEstimateDate()">...</p>
                </div>
            </div>
            
            <div class="flex justify-between items-center mb-5">
                <div class="space-y-0.5">
                    <p class="text-slate-400 text-[9px] font-black uppercase tracking-[0.2em]">Estimasi Total</p>
                    <h4 class="text-2xl font-black italic tracking-tighter text-blue-600" x-text="loading ? '...' : formatRupiah(quote.total_amount)">Rp 0</h4>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 shadow-inner">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
            </div>
            <button type="submit" 
                    :disabled="loading || !quote.total_amount"
                    class="w-full bg-blue-600 text-white font-black h-14 rounded-2xl shadow-lg shadow-blue-500/30 tap-scale transition-all flex items-center justify-center gap-3 disabled:opacity-50 group">
                <span class="uppercase tracking-widest text-[10px]" x-text="loading ? 'Memproses...' : 'Konfirmasi Pesanan'"></span>
                <i class="bi bi-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function bookingForm() {
        return {
            form: {
                weight_per_item: 1.0,
                total_items: 1,
                service_type: 'regular',
                branch_id: '',
                destination_branch_id: ''
            },
            quote: {
                total_amount: 0,
            },
            routeRates: [],
            loading: false,
            init() {
                this.$watch('form.branch_id', () => this.fetchRouteRates());
                this.$watch('form.destination_branch_id', () => this.fetchRouteRates());
                this.updateQuote();
            },
            async fetchRouteRates() {
                if (!this.form.branch_id || !this.form.destination_branch_id) {
                    this.routeRates = [];
                    this.quote.total_amount = 0;
                    return;
                }

                this.loading = true;
                try {
                    const res = await fetch(`/customer/shipments/rates?origin_branch_id=${this.form.branch_id}&destination_branch_id=${this.form.destination_branch_id}`);
                    const data = await res.json();
                    if (data.status === 'success') {
                        this.routeRates = data.data;
                        this.updateQuote();
                    }
                } catch (e) {
                    console.error('Failed to fetch rates', e);
                } finally {
                    this.loading = false;
                }
            },
            updateQuote() {
                // Try local calculation first for seamless feel
                if (this.routeRates.length > 0) {
                    const success = this.calculateLocally();
                    if (success) return;
                }

                // Fallback to server if no rates found locally
                this.fetchServerQuote();
            },
            calculateLocally() {
                const weight = parseFloat(this.form.weight_per_item) * parseInt(this.form.total_items);
                const service = this.form.service_type;

                // Find applicable rate card
                const rate = this.routeRates.find(r => 
                    r.service_type === service && 
                    weight >= parseFloat(r.min_weight_kg) && 
                    (!r.max_weight_kg || weight <= parseFloat(r.max_weight_kg))
                ) || this.routeRates.find(r => r.service_type === service); // fallback to service-only match

                if (rate) {
                    const basePrice = parseFloat(rate.base_price);
                    const perKgPrice = parseFloat(rate.per_kg_price);
                    const subtotal = basePrice + (perKgPrice * Math.max(weight, 1));
                    
                    this.quote.total_amount = Math.round(subtotal);
                    return true;
                }
                return false;
            },
            async fetchServerQuote() {
                if (!this.form.branch_id || !this.form.destination_branch_id) return;

                this.loading = true;
                const totalWeight = (this.form.weight_per_item * this.form.total_items).toFixed(2);
                const params = new URLSearchParams({ ...this.form, total_weight_kg: totalWeight });
                
                try {
                    const res = await fetch(`/customer/shipments/quote?${params.toString()}`);
                    const data = await res.json();
                    if (data.status === 'success') {
                        this.quote = data.data;
                    }
                } catch (e) {
                    console.error('Server quote failed', e);
                } finally {
                    this.loading = false;
                }
            },
            getEstimateDate() {
                const days = this.form.service_type === 'express' ? 1 : (this.form.service_type === 'same_day' ? 0 : 3);
                const date = new Date();
                date.setDate(date.getDate() + days);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
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