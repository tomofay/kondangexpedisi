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

    <form method="post" action="{{ route('profile.update') }}" class="row g-3">
        @csrf
        @method('patch')

        <div class="col-12">
            <label for="name" class="form-label fw-semibold">Nama</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
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

        <div class="col-12 pt-1">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
        </div>
    </form>
</section>
