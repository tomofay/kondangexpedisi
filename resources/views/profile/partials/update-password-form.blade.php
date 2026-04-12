<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-primary-emphasis mb-1">Update Password</h2>
        <p class="small text-secondary mb-0">Gunakan password kuat agar akun tetap aman.</p>
    </header>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success py-2 small">Password berhasil diperbarui.</div>
    @endif

    <form method="post" action="{{ route('password.update') }}" class="row g-3">
        @csrf
        @method('put')

        <div class="col-12">
            <label for="update_password_current_password" class="form-label fw-semibold">Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" autocomplete="current-password">
            @if($errors->updatePassword->has('current_password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="col-12">
            <label for="update_password_password" class="form-label fw-semibold">Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" autocomplete="new-password">
            @if($errors->updatePassword->has('password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="col-12">
            <label for="update_password_password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" autocomplete="new-password">
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="col-12 pt-1">
            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Password</button>
        </div>
    </form>
</section>
