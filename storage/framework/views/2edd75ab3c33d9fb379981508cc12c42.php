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
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Detail Pengiriman</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Tracking #<?php echo e($shipment->tracking_number); ?></div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('shipments.label', $shipment->id)); ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-printer me-2"></i>Cetak Label
                </a>
                <?php if(in_array(auth()->user()->role, ['admin', 'manager', 'kasir'])): ?>
                <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="showStatusModal()">
                    Update Status
                </button>
                <?php endif; ?>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="row g-4">
                <!-- Main Info -->
                <div class="col-lg-8">
                    <div class="card-pro p-4 mb-4 bg-white rounded-4 border">
                        <h5 class="fw-bold mb-4">Informasi Paket</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small text-muted text-uppercase fw-bold">Pengirim</label>
                                <div class="fw-bold fs-5"><?php echo e($shipment->sender_name); ?></div>
                                <div class="text-muted"><?php echo e($shipment->sender_phone); ?></div>
                                <div class="mt-1 small"><?php echo e($shipment->sender_address); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted text-uppercase fw-bold">Penerima</label>
                                <div class="fw-bold fs-5"><?php echo e($shipment->recipient_name); ?></div>
                                <div class="text-muted"><?php echo e($shipment->recipient_phone); ?></div>
                                <div class="mt-1 small"><?php echo e($shipment->recipient_address); ?></div>
                            </div>
                            <hr>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Layanan</label>
                                <div class="fw-bold"><?php echo e(strtoupper($shipment->service_type)); ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Berat Total</label>
                                <div class="fw-bold"><?php echo e(number_format($shipment->total_weight, 2)); ?> kg</div>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Total Tagihan</label>
                                <div class="fw-bold text-primary fs-5">Rp <?php echo e(number_format($shipment->total_amount, 0, ',', '.')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-pro p-4 bg-white rounded-4 border">
                        <h5 class="fw-bold mb-4">Tracking History</h5>
                        <div class="timeline ps-3">
                            <?php $__currentLoopData = $shipment->trackings->sortByDesc('event_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tracking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item border-start border-primary position-relative ps-4 pb-4">
                                    <div class="position-absolute start-0 translate-middle-x bg-primary rounded-circle" style="width:12px;height:12px;margin-left:0px;margin-top:6px;"></div>
                                    <div class="fw-bold text-dark"><?php echo e($tracking->status?->name); ?></div>
                                    <div class="small text-muted mb-1"><?php echo e($tracking->event_at->format('d M Y, H:i')); ?></div>
                                    <div class="text-muted small"><?php echo e($tracking->note); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="col-lg-4">
                    <div class="card-pro p-4 mb-4 bg-white rounded-4 border">
                        <h5 class="fw-bold mb-4">Status & Pembayaran</h5>
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Status Pengiriman</label>
                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill w-100 fs-6">
                                <?php echo e($shipment->status?->name); ?>

                            </span>
                        </div>
                        <div>
                            <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Status Pembayaran</label>
                            <?php
                                $payStatus = $shipment->payment_status;
                                $payClass = match($payStatus) {
                                    'paid', 'settlement' => 'bg-success text-white',
                                    'pending' => 'bg-warning text-dark',
                                    default => 'bg-danger text-white'
                                };
                            ?>
                            <span class="badge <?php echo e($payClass); ?> px-3 py-2 rounded-pill w-100 fs-6">
                                <?php echo e(strtoupper($payStatus)); ?>

                            </span>
                        </div>
                        <?php if($shipment->payment_status === 'pending'): ?>
                            <div class="mt-3">
                                <button class="btn btn-outline-success w-100 fw-bold rounded-pill">Konfirmasi Bayar</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-pro p-4 bg-white rounded-4 border">
                        <h5 class="fw-bold mb-3">Rute</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-center">
                                <i class="bi bi-building fs-4 text-muted"></i>
                                <div class="small fw-bold mt-1"><?php echo e($shipment->branch?->code); ?></div>
                            </div>
                            <div class="flex-grow-1 border-top border-2 border-dashed mx-2"></div>
                            <div class="text-center">
                                <i class="bi bi-geo-alt-fill fs-4 text-primary"></i>
                                <div class="small fw-bold mt-1"><?php echo e($shipment->destinationBranch?->code); ?></div>
                            </div>
                        </div>
                        <div class="mt-3 small text-muted text-center">
                            Estimasi: <?php echo e($shipment->estimated_delivery_at ? $shipment->estimated_delivery_at->format('d M Y') : '-'); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline-item:last-child { border-left: none !important; }
    </style>
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
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/shipments/show.blade.php ENDPATH**/ ?>