<section>
    <header class="mb-3">
        <h2 class="h5 fw-bold text-danger-emphasis mb-1">Hapus Akun</h2>
        <p class="small text-secondary mb-0">Aksi ini bersifat permanen dan akan menghapus akses akun Anda.</p>
    </header>

    <div class="alert alert-warning py-2 small mb-3">
        Pastikan Anda sudah menyimpan data penting sebelum menghapus akun.
    </div>

    <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        Hapus Akun
    </button>

    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h3 class="modal-title fs-5" id="confirmUserDeletionModalLabel">Konfirmasi Hapus Akun</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="small text-secondary mb-3">
                            Ketik password untuk mengonfirmasi penghapusan akun secara permanen.
                        </p>

                        <label for="delete_password" class="form-label fw-semibold">Password</label>
                        <input
                            id="delete_password"
                            name="password"
                            type="password"
                            class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                            placeholder="Masukkan password"
                        >
                        @if($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-3">Ya, Hapus Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('confirmUserDeletionModal');
                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            });
        </script>
    @endif
</section>
