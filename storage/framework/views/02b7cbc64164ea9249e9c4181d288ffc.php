<?php $__env->startSection('content'); ?>
<div class="space-y-8 animate-slide-up pb-32">
    <!-- Header -->
    <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(auth()->user()->role === 'customer' ? route('customer.dashboard') : route('courier.tasks')); ?>" class="w-12 h-12 glass rounded-2xl flex items-center justify-center shadow-sm text-indigo-600 tap-scale">
                <i class="bi bi-chevron-left fs-5"></i>
            </a>
            <h2 class="text-xl font-black tracking-tight">Profil Saya</h2>
        </div>
        <div class="w-12 h-12"></div>
    </div>

    <!-- Profile Hero Card (Mesh Gradient Style) -->
    <div class="relative group">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700 rounded-[2.5rem] blur-2xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
        <div class="relative bg-slate-900 rounded-[2.5rem] p-8 text-white overflow-hidden shadow-2xl shadow-indigo-500/20">
            <!-- Decorative Blobs -->
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-[80px] animate-pulse"></div>
            <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-purple-500/20 rounded-full blur-[80px]"></div>
            
            <div class="relative z-10 flex flex-col items-center text-center space-y-6">
                <!-- Avatar with Multi-layer Shadow -->
                <div class="relative">
                    <div class="absolute inset-0 bg-white/20 rounded-[2rem] blur-lg"></div>
                    <div class="w-32 h-32 rounded-[2rem] bg-white/10 backdrop-blur-3xl p-1 relative border border-white/20 shadow-2xl overflow-hidden">
                        <img src="<?php echo e($user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&bold=true&size=256'); ?>" 
                             class="rounded-[1.75rem] w-full h-full object-cover" alt="Avatar">
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-emerald-500 border-4 border-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg">
                        <i class="bi bi-patch-check-fill fs-6"></i>
                    </div>
                </div>

                <div class="space-y-1">
                    <h3 class="text-3xl font-black tracking-tight leading-none"><?php echo e($user->name); ?></h3>
                    <p class="text-indigo-200/60 text-xs font-bold uppercase tracking-widest"><?php echo e($user->email); ?></p>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-3 gap-4 w-full pt-4">
                    <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-3 border border-white/10">
                        <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">Role</p>
                        <p class="text-xs font-bold text-white"><?php echo e(ucfirst($user->role)); ?></p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-3 border border-white/10">
                        <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">ID</p>
                        <p class="text-xs font-bold text-white">#<?php echo e(str_pad($user->id, 4, '0', STR_PAD_LEFT)); ?></p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-3 border border-white/10">
                        <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">Status</p>
                        <p class="text-xs font-bold text-emerald-400">Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Main Menu List -->
    <div class="space-y-6">
        <div class="flex items-center gap-3 px-1">
            <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
            <h3 class="font-black text-xl tracking-tight">Akun & Keamanan</h3>
        </div>

            <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-4">
        <button onclick="toggleSection('edit-info')" class="glass p-6 rounded-[2rem] flex flex-col items-center gap-3 tap-scale group">
            <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                <i class="bi bi-person-bounding-box fs-3"></i>
            </div>
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Edit Profil</p>
        </button>
        <button onclick="toggleSection('change-password')" class="glass p-6 rounded-[2rem] flex flex-col items-center gap-3 tap-scale group">
            <div class="w-14 h-14 bg-purple-50 dark:bg-purple-900/30 text-purple-600 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                <i class="bi bi-key-fill fs-3"></i>
            </div>
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Ganti Sandi</p>
        </button>
    </div>
        <div class="glass overflow-hidden rounded-[2.5rem] divide-y divide-slate-100 dark:divide-slate-800/50">
        
            <!-- Logout -->
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full p-6 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition-all tap-scale text-left">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-rose-50 dark:bg-rose-900/30 text-rose-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="bi bi-power fs-5"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-black text-sm text-rose-600">Keluar</h4>
                            <p class="text-[10px] text-rose-400 font-bold uppercase tracking-wider mt-0.5">Selesaikan sesi Anda</p>
                        </div>
                        <i class="bi bi-box-arrow-right text-rose-200"></i>
                    </div>
                </button>
            </form>
        </div>
    </div>

    <!-- Modals (Bottom Sheets) -->
    <div id="section-edit-info" class="fixed inset-0 z-[60] bg-slate-950/40 backdrop-blur-md hidden items-end" x-data="{ currentEmail: '<?php echo e($user->email); ?>' }">
        <div class="w-full bg-white dark:bg-slate-900 rounded-t-[3rem] p-10 pb-12 space-y-8 animate-slide-up shadow-2xl relative">
            <!-- Pull Handle -->
            <div class="absolute top-4 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
            
            <div class="flex justify-between items-center pt-2">
                <h3 class="text-2xl font-black tracking-tight">Data Pribadi</h3>
                <button onclick="toggleSection('edit-info')" class="w-12 h-12 glass rounded-2xl flex items-center justify-center text-slate-400 tap-scale">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-rose-50 border border-rose-100 p-5 rounded-[2rem] space-y-2">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 text-rose-600">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest"><?php echo e($error); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
            
            <form id="form-edit-info" method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data" class="space-y-6" onsubmit="handleProfileUpdate(event)">
                <?php echo csrf_field(); ?>
                <?php echo method_field('patch'); ?>
                
                <!-- Profile Picture Upload (Upgraded) -->
                <div class="flex flex-col items-center gap-4 mb-4">
                    <div class="relative group">
                        <div class="w-28 h-28 rounded-[2rem] bg-slate-50 dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shadow-inner relative">
                            <img id="photo-preview" src="<?php echo e($user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=f8fafc&color=6366f1&bold=true'); ?>" 
                                 class="w-full h-full object-cover <?php echo e($user->photo ? '' : 'opacity-50'); ?>">
                            
                            <!-- Camera Overlay -->
                            <div class="absolute inset-0 bg-indigo-600/60 opacity-0 group-hover:opacity-100 transition-all flex flex-col items-center justify-center text-white cursor-pointer backdrop-blur-sm" 
                                 onclick="document.getElementById('photo-input').click()">
                                <i class="bi bi-camera-fill fs-2 mb-1"></i>
                                <span class="text-[8px] font-black uppercase tracking-tighter">Ganti Foto</span>
                            </div>
                        </div>
                        <input type="file" id="photo-input" name="photo" class="hidden" accept="image/*" onchange="previewPhoto(event)">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Lengkap</label>
                        <input type="text" name="name" value="<?php echo e($user->name); ?>" 
                               class="w-full bg-slate-50 dark:bg-slate-800 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alamat Email</label>
                        <input type="email" id="edit-email" name="email" value="<?php echo e($user->email); ?>" 
                               class="w-full bg-slate-50 dark:bg-slate-800 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nomor Telepon</label>
                        <input type="tel" name="phone" value="<?php echo e($user->phone); ?>" 
                               class="w-full bg-slate-50 dark:bg-slate-800 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alamat Lengkap</label>
                        <textarea name="address" rows="2"
                                  class="w-full bg-slate-50 dark:bg-slate-800 p-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner"><?php echo e($user->address); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-black h-16 rounded-[2rem] shadow-xl shadow-indigo-500/30 tap-scale transition-all uppercase tracking-widest mt-4">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <!-- Password Modal (Upgraded) -->
    <div id="section-change-password" class="fixed inset-0 z-[60] bg-slate-950/40 backdrop-blur-md hidden items-end">
        <div class="w-full bg-white dark:bg-slate-900 rounded-t-[3rem] p-10 pb-12 space-y-8 animate-slide-up shadow-2xl relative">
            <div class="absolute top-4 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
            
            <div class="flex justify-between items-center pt-2">
                <h3 class="text-2xl font-black tracking-tight">Keamanan</h3>
                <button onclick="toggleSection('change-password')" class="w-12 h-12 glass rounded-2xl flex items-center justify-center text-slate-400 tap-scale">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form id="form-change-password" method="POST" action="<?php echo e(route('password.update')); ?>" class="space-y-6" onsubmit="handlePasswordUpdate(event)">
                <?php echo csrf_field(); ?>
                <?php echo method_field('put'); ?>
                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password Saat Ini</label>
                        <input type="password" name="current_password" required
                               class="w-full bg-slate-50 dark:bg-slate-800 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Password Baru</label>
                        <input type="password" name="password" required
                               class="w-full bg-slate-50 dark:bg-slate-800 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full bg-slate-50 dark:bg-slate-800 h-16 px-6 rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 font-bold text-sm transition-all shadow-inner">
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-black h-16 rounded-[2rem] shadow-xl shadow-indigo-500/30 tap-scale transition-all uppercase tracking-widest mt-4">
                    Perbarui Password
                </button>
            </form>
        </div>
    </div>

    <!-- OTP Modal (Upgraded) -->
    <div id="section-otp-verify" class="fixed inset-0 z-[70] bg-slate-950/60 backdrop-blur-xl hidden items-center justify-center p-6">
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-[3rem] p-10 space-y-10 animate-scale-up shadow-2xl relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-40 h-40 bg-indigo-600/10 rounded-full blur-3xl"></div>
            
            <div class="text-center space-y-5 relative z-10">
                <div class="w-24 h-24 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-[2rem] flex items-center justify-center mx-auto shadow-sm">
                    <i class="bi bi-shield-lock-fill fs-1"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-2xl font-black tracking-tight">Verifikasi OTP</h3>
                    <p class="text-xs text-slate-400 font-bold leading-relaxed px-4" id="otp-message">Masukkan 6 digit kode yang kami kirimkan ke email Anda.</p>
                </div>
            </div>

            <div class="space-y-10 relative z-10">
                <div class="flex justify-between gap-2" id="otp-inputs">
                    <?php for($i = 0; $i < 6; $i++): ?>
                    <input type="number" maxlength="1" 
                           class="w-11 h-16 bg-slate-50 dark:bg-slate-800 text-center font-black text-2xl rounded-2xl border-none focus:ring-2 focus:ring-indigo-600 transition-all shadow-inner appearance-none otp-input"
                           oninput="if(this.value.length > 1) this.value = this.value.slice(0,1); if(this.value.length === 1 && this.nextElementSibling) this.nextElementSibling.focus();">
                    <?php endfor; ?>
                </div>

                <div class="space-y-4">
                    <button onclick="verifyOtp()" id="btn-verify" class="w-full bg-indigo-600 text-white font-black h-16 rounded-[2rem] shadow-xl shadow-indigo-500/30 tap-scale transition-all uppercase tracking-widest flex items-center justify-center gap-3">
                        <span>Verifikasi</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    <button onclick="closeOtp()" class="w-full py-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-slate-600 transition-all">
                        Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    let currentPendingAction = null;
    let originalEmail = "<?php echo e($user->email); ?>";

    function toggleSection(id) {
        const el = document.getElementById('section-' + id);
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        } else {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    async function handleProfileUpdate(e) {
        e.preventDefault();
        const email = document.getElementById('edit-email').value;
        
        if (email !== originalEmail) {
            // Email changed, need OTP
            const res = await fetch("/profile/otp/send-email", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ email: email })
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                currentPendingAction = 'profile';
                document.getElementById('otp-message').innerText = `Kami telah mengirimkan kode OTP ke email baru Anda: ${email}`;
                openOtp();
            } else {
                alert(data.message || 'Gagal mengirim OTP');
            }
        } else {
            document.getElementById('form-edit-info').submit();
        }
    }

    async function handlePasswordUpdate(e) {
        e.preventDefault();
        
        const res = await fetch("/profile/otp/send-password", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            currentPendingAction = 'password';
            document.getElementById('otp-message').innerText = `Kami telah mengirimkan kode OTP ke email aktif Anda: ${originalEmail}`;
            openOtp();
        } else {
            alert(data.message || 'Gagal mengirim OTP');
        }
    }

    function openOtp() {
        document.getElementById('section-otp-verify').classList.remove('hidden');
        document.getElementById('section-otp-verify').classList.add('flex');
        // Clear inputs
        document.querySelectorAll('.otp-input').forEach(i => i.value = '');
        document.querySelectorAll('.otp-input')[0].focus();
    }

    function closeOtp() {
        document.getElementById('section-otp-verify').classList.add('hidden');
        document.getElementById('section-otp-verify').classList.remove('flex');
    }

    async function verifyOtp() {
        const otp = Array.from(document.querySelectorAll('.otp-input')).map(i => i.value).join('');
        if (otp.length < 6) return alert('Masukkan 6 digit kode OTP');

        const btn = document.getElementById('btn-verify');
        btn.disabled = true;
        btn.innerText = 'Memverifikasi...';

        try {
            const res = await fetch("/profile/otp/verify", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ otp: otp })
            });
            const data = await res.json();

            if (data.status === 'success') {
                // Submit the actual form
                if (currentPendingAction === 'profile') {
                    document.getElementById('form-edit-info').submit();
                } else if (currentPendingAction === 'password') {
                    document.getElementById('form-change-password').submit();
                }
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerText = 'Verifikasi';
            }
        } catch (e) {
            alert('Terjadi kesalahan koneksi');
            btn.disabled = false;
            btn.innerText = 'Verifikasi';
        }
    }

    // Auto focus back on delete
    document.querySelectorAll('.otp-input').forEach((input, index) => {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                document.querySelectorAll('.otp-input')[index - 1].focus();
            }
        });
    });

    function previewPhoto(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('photo-preview');
            output.src = reader.result;
            output.classList.remove('opacity-50');
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('mobile.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/mobile/profile.blade.php ENDPATH**/ ?>