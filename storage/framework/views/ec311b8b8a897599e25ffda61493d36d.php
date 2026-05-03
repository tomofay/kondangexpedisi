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
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Manajemen Pengiriman</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Monitor & Kelola Status Paket</div>
            </div>
            <div class="d-flex gap-2">
                <?php if(in_array(auth()->user()->role, ['admin', 'kasir', 'manager'])): ?>
                <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="showCreateModal()">
                    <i class="bi bi-plus-lg me-2"></i>Buat Baru
                </button>
                <?php endif; ?>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-4 border border-gray-100">
                <div class="p-4 border-bottom bg-light">
                    <form id="filter-form" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Resi, Pengirim, Penerima..." value="<?php echo e(request('search')); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="status_id" class="form-select">
                                <option value="">Semua Status</option>
                                <?php $__currentLoopData = $statuses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status->id); ?>" <?php echo e(request('status_id') == $status->id ? 'selected' : ''); ?>><?php echo e($status->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select">
                                <option value="">Semua Pembayaran</option>
                                <option value="pending" <?php echo e(request('payment_status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="paid" <?php echo e(request('payment_status') == 'paid' ? 'selected' : ''); ?>>Paid</option>
                                <option value="settlement" <?php echo e(request('payment_status') == 'settlement' ? 'selected' : ''); ?>>Settlement</option>
                                <option value="failed" <?php echo e(request('payment_status') == 'failed' ? 'selected' : ''); ?>>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100 fw-bold rounded-pill">Filter</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small fw-bold text-uppercase">
                                <th class="px-4 py-3">Resi & Tanggal</th>
                                <th>Pengirim</th>
                                <th>Penerima</th>
                                <th>Layanan</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th class="text-end px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-primary"><?php echo e($shipment->tracking_number); ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?php echo e($shipment->created_at->format('d M Y, H:i')); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo e($shipment->sender_name); ?></div>
                                        <div class="text-muted small"><?php echo e($shipment->origin_branch_city ?? $shipment->branch?->city); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo e($shipment->recipient_name); ?></div>
                                        <div class="text-muted small"><?php echo e($shipment->destination_branch_city ?? $shipment->destinationBranch?->city); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;"><?php echo e($shipment->service_type); ?></span>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        Rp <?php echo e(number_format($shipment->total_amount, 0, ',', '.')); ?>

                                    </td>
                                    <td>
                                        <?php
                                            $payStatus = $shipment->payment_status;
                                            $payClass = match($payStatus) {
                                                'paid', 'settlement' => 'bg-success-subtle text-success',
                                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                                default => 'bg-danger-subtle text-danger'
                                            };
                                        ?>
                                        <span class="badge rounded-pill fw-bold <?php echo e($payClass); ?>" style="font-size: 0.75rem;">
                                            <?php echo e(strtoupper($payStatus)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill" style="font-size: 0.75rem;">
                                            <?php echo e($shipment->status?->name ?? 'Unknown'); ?>

                                        </span>
                                    </td>
                                    <td class="text-end px-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                                <li><a class="dropdown-item fw-bold" href="<?php echo e(route('shipments.show', $shipment->id)); ?>"><i class="bi bi-eye me-2"></i>Detail</a></li>
                                                <li><a class="dropdown-item fw-bold" href="<?php echo e(route('shipments.label', $shipment->id)); ?>" target="_blank"><i class="bi bi-printer me-2"></i>Cetak Label</a></li>
                                                <?php if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager'): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item fw-bold text-danger" href="#" onclick="confirmDelete(<?php echo e($shipment->id); ?>)"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted fw-bold">Tidak ada data pengiriman ditemukan.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-top">
                    <?php echo e($shipments->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Shipment?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Form delete logic here
                }
            })
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
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/shipments/index.blade.php ENDPATH**/ ?>