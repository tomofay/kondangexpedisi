<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Pusat Persetujuan (Approval)</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Validasi Operasional & Finansial</div>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-4 border border-gray-100">
                <div class="p-4 border-bottom bg-light">
                    <form id="filter-form" class="row g-3">
                        <div class="col-md-4">
                            <select name="scope" class="form-select" onchange="this.form.submit()">
                                <option value="all">Semua Tipe Tugas</option>
                                <option value="shipment_final_status_approval" <?php echo e(request('scope') == 'shipment_final_status_approval' ? 'selected' : ''); ?>>Final Status (Delivered/Cancel)</option>
                                <option value="shipment_reassign_approval" <?php echo e(request('scope') == 'shipment_reassign_approval' ? 'selected' : ''); ?>>Reassign Kurir</option>
                                <option value="payment_manual_status_approval" <?php echo e(request('scope') == 'payment_manual_status_approval' ? 'selected' : ''); ?>>Manual Payment Validation</option>
                                <option value="approve_rate_card" <?php echo e(request('scope') == 'approve_rate_card' ? 'selected' : ''); ?>>Rate Card Changes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending & In Progress</option>
                                <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Selesai (Completed)</option>
                                <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Ditolak (Rejected)</option>
                                <option value="all" <?php echo e(request('status') == 'all' ? 'selected' : ''); ?>>Semua Status</option>
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
                            <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark"><?php echo e($task->title); ?></div>
                                        <div class="text-muted small text-uppercase" style="font-size: 0.65rem;"><?php echo e(str_replace('_', ' ', $task->task_type)); ?></div>
                                    </td>
                                    <td>
                                        <?php
                                            $priorityClass = match($task->priority) {
                                                'high' => 'bg-danger text-white',
                                                'medium' => 'bg-warning text-dark',
                                                default => 'bg-info text-white'
                                            };
                                        ?>
                                        <span class="badge rounded-pill <?php echo e($priorityClass); ?>" style="font-size: 0.65rem;">
                                            <?php echo e(strtoupper($task->priority)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold small"><?php echo e($task->creator?->name); ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo e($task->creator?->role); ?></div>
                                    </td>
                                    <td>
                                        <div class="small"><?php echo e($task->created_at->diffForHumans()); ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?php echo e($task->created_at->format('d/m/Y H:i')); ?></div>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = match($task->status) {
                                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                                'in_progress' => 'bg-primary-subtle text-primary',
                                                'completed' => 'bg-success-subtle text-success',
                                                'rejected' => 'bg-danger-subtle text-danger',
                                                default => 'bg-light text-dark'
                                            };
                                        ?>
                                        <span class="badge rounded-pill fw-bold <?php echo e($statusClass); ?>" style="font-size: 0.7rem;">
                                            <?php echo e(strtoupper(str_replace('_', ' ', $task->status))); ?>

                                        </span>
                                    </td>
                                    <td class="text-end px-4">
                                        <?php if($task->status === 'pending' || $task->status === 'in_progress'): ?>
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="handleApprove(<?php echo e($task->id); ?>)">Approve</button>
                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="handleReject(<?php echo e($task->id); ?>)">Reject</button>
                                            </div>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold disabled">Selesai</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted fw-bold">Tidak ada tugas approval yang menunggu. ✅</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-top">
                    <?php echo e($tasks->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
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
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/approvals/index.blade.php ENDPATH**/ ?>