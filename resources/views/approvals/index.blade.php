<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Pusat Persetujuan (Approval)</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Validasi Operasional & Finansial</div>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-4 border border-gray-100">
                <div class="p-4 border-bottom bg-light">
                    <form id="filter-form" class="row g-3">
                        <div class="col-md-4">
                            <select name="scope" class="form-select" onchange="this.form.submit()">
                                <option value="all">Semua Tipe Tugas</option>
                                <option value="shipment_final_status_approval" {{ request('scope') == 'shipment_final_status_approval' ? 'selected' : '' }}>Final Status (Delivered/Cancel)</option>
                                <option value="shipment_reassign_approval" {{ request('scope') == 'shipment_reassign_approval' ? 'selected' : '' }}>Reassign Kurir</option>
                                <option value="payment_manual_status_approval" {{ request('scope') == 'payment_manual_status_approval' ? 'selected' : '' }}>Manual Payment Validation</option>
                                <option value="approve_rate_card" {{ request('scope') == 'approve_rate_card' ? 'selected' : '' }}>Rate Card Changes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending & In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small fw-bold text-uppercase">
                                <th class="px-4 py-3">Tugas</th>
                                <th>Prioritas</th>
                                <th>Diajukan Oleh</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th class="text-end px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $task->title }}</div>
                                        <div class="text-muted small text-uppercase" style="font-size: 0.65rem;">{{ str_replace('_', ' ', $task->task_type) }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $priorityClass = match($task->priority) {
                                                'high' => 'bg-danger text-white',
                                                'medium' => 'bg-warning text-dark',
                                                default => 'bg-info text-white'
                                            };
                                        @endphp
                                        <span class="badge rounded-pill {{ $priorityClass }}" style="font-size: 0.65rem;">
                                            {{ strtoupper($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold small">{{ $task->creator?->name }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $task->creator?->role }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $task->created_at->diffForHumans() }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($task->status) {
                                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                                'in_progress' => 'bg-primary-subtle text-primary',
                                                'completed' => 'bg-success-subtle text-success',
                                                'rejected' => 'bg-danger-subtle text-danger',
                                                default => 'bg-light text-dark'
                                            };
                                        @endphp
                                        <span class="badge rounded-pill fw-bold {{ $statusClass }}" style="font-size: 0.7rem;">
                                            {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end px-4">
                                        @if($task->status === 'pending' || $task->status === 'in_progress')
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="handleApprove({{ $task->id }})">Approve</button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="handleReject({{ $task->id }})">Reject</button>
                                            </div>
                                        @else
                                            <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold disabled">Selesai</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted fw-bold">Tidak ada tugas approval yang menunggu. ✅</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-top">
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function handleApprove(id) {
            const { value: note } = await Swal.fire({
                title: 'Setujui Tugas?',
                input: 'text',
                inputPlaceholder: 'Tambahkan catatan (opsional)...',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            });

            if (note !== undefined) {
                try {
                    await axios.post(`/approvals/tasks/${id}/approve`, { note });
                    Swal.fire('Berhasil!', 'Tugas telah disetujui.', 'success').then(() => location.reload());
                } catch (e) {
                    Swal.fire('Gagal!', e.response?.data?.message || 'Terjadi kesalahan.', 'error');
                }
            }
        }

        async function handleReject(id) {
            const { value: reason } = await Swal.fire({
                title: 'Tolak Tugas?',
                input: 'text',
                inputPlaceholder: 'Alasan penolakan (wajib)...',
                inputAttributes: { required: 'true' },
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            });

            if (reason) {
                try {
                    await axios.post(`/approvals/tasks/${id}/reject`, { reason });
                    Swal.fire('Berhasil!', 'Tugas telah ditolak.', 'success').then(() => location.reload());
                } catch (e) {
                    Swal.fire('Gagal!', e.response?.data?.message || 'Terjadi kesalahan.', 'error');
                }
            } else if (reason === '') {
                Swal.fire('Error', 'Alasan penolakan wajib diisi.', 'error');
            }
        }
    </script>
    @endpush
</x-app-layout>
