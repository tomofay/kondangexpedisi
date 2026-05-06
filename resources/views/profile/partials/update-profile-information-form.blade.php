<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-primary-emphasis mb-1">Informasi Profil</h2>
        <p class="small text-secondary mb-0">Perbarui nama dan email akun Anda.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success py-2 small">Profil berhasil diperbarui.</div>
    @endif

    <style>
        .profile-photo-wrapper:hover .photo-overlay {
            opacity: 1 !important;
        }
        .profile-photo-wrapper:hover img, .profile-photo-wrapper:hover .photo-placeholder-div {
            filter: brightness(0.8);
        }
    </style>

    <form method="post" action="{{ route('profile.update') }}" class="row g-3" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="col-12 text-center mb-4">
            <label for="photo" class="position-relative d-inline-block profile-photo-wrapper cursor-pointer" style="cursor: pointer;">
                @if ($user->photo)
                    <img id="photo-preview" src="{{ asset('storage/' . $user->photo) }}" class="rounded-circle border border-4 border-white shadow-lg" style="width: 140px; height: 140px; object-fit: cover; transition: 0.3s;">
                @else
                    <div id="photo-placeholder" class="photo-placeholder-div rounded-circle border border-4 border-white shadow-lg bg-primary text-white d-flex align-items-center justify-content-center" style="width: 140px; height: 140px; font-size: 3.5rem; font-weight: 800; transition: 0.3s;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <img id="photo-preview" src="#" class="rounded-circle border border-4 border-white shadow-lg d-none" style="width: 140px; height: 140px; object-fit: cover; transition: 0.3s;">
                @endif
                
                <div class="photo-overlay rounded-circle d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.4); opacity: 0; transition: 0.3s; color: white; border: 4px solid white;">
                    <div class="text-center">
                        <i class="bi bi-camera-fill fs-2 d-block mb-1"></i>
                        <span class="fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Ubah Foto</span>
                    </div>
                </div>
            </label>
            <input id="photo" name="photo" type="file" class="d-none" onchange="previewImage(this)">
            @error('photo')
                <div class="text-danger small mt-2 fw-bold animate__animated animate__shakeX">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="phone" class="form-label fw-semibold">Nomor Telepon</label>
            <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-3 mb-0 py-2 small">
                    Email Anda belum terverifikasi.
                    <button form="send-verification" class="btn btn-link btn-sm p-0 ms-1 align-baseline">Kirim ulang link verifikasi</button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="text-success small mt-2">Link verifikasi baru sudah dikirim ke email Anda.</div>
                @endif
            @endif
        </div>

        <div class="col-12">
            <label for="address" class="form-label fw-semibold">Alamat</label>
            <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $user->address) }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 pt-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Perubahan</button>
        </div>
    </form>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('photo-preview');
            const placeholder = document.getElementById('photo-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    if (placeholder) placeholder.classList.add('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</section>
