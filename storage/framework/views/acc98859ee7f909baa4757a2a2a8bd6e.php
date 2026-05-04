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
    <style>
        .dashboard-container {
            padding: 1.5rem;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .card-pro {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card-pro:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.1);
            border-color: #e0e7ff;
        }

        .view-section {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .view-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table-modern th {
            background: #F8FAFC;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            border: none;
        }

        .table-modern td {
            background: white;
            padding: 1.2rem 1rem;
            border-top: 1px solid #F1F5F9;
            border-bottom: 1px solid #F1F5F9;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table-modern td:first-child { border-left: 1px solid #F1F5F9; border-radius: 16px 0 0 16px; }
        .table-modern td:last-child { border-right: 1px solid #F1F5F9; border-radius: 0 16px 16px 0; }

        .btn-action-sm {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .btn-action-sm:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .btn-print { background: #10B981; color: white; }
        .btn-print:hover { background: #059669; color: white; transform: translateY(-2px); }
        .btn-edit { background: #6366F1; color: white; }
        .btn-edit:hover { background: #4F46E5; color: white; transform: translateY(-2px); }
        .btn-history { background: #0EA5E9; color: white; }
        .btn-history:hover { background: #0284C7; color: white; transform: translateY(-2px); }
        .btn-delete { background: #F43F5E; color: white; }
        .btn-delete:hover { background: #E11D48; color: white; transform: translateY(-2px); }

        .status-pill {
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-pill i { font-size: 0.45rem; }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .filter-pill-container {
            display: flex;
            background: #F1F5F9;
            padding: 4px;
            border-radius: 14px;
            gap: 4px;
        }

        .filter-pill {
            border: none;
            padding: 6px 16px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748B;
            background: transparent;
            transition: 0.2s;
        }

        .filter-pill.active {
            background: white;
            color: #6366F1;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .hover-row { transition: 0.2s; }
        .hover-row:hover td { background: #F8FAFC !important; transform: scale(1.002); cursor: pointer; }

        .bg-success-light { background: #ECFDF5 !important; color: #10B981 !important; }
        .bg-warning-light { background: #FFFBEB !important; color: #F59E0B !important; }
        .bg-danger-light { background: #FEF2F2 !important; color: #EF4444 !important; }
        .bg-primary-light { background: #EFF6FF !important; color: #3B82F6 !important; }
        .bg-indigo-light { background: #EEF2FF !important; color: #6366F1 !important; }
        .bg-purple-light { background: #FAF5FF !important; color: #A855F7 !important; }

        .status-pill.status-pending { background: #FFFBEB !important; color: #B45309 !important; }
        .status-pill.status-in_transit { background: #EFF6FF !important; color: #1D4ED8 !important; }
        .status-pill.status-out_for_delivery { background: #F5F3FF !important; color: #6D28D9 !important; }
        .status-pill.status-delivered { background: #ECFDF5 !important; color: #047857 !important; }
        .status-pill.status-cancelled { background: #FEF2F2 !important; color: #B91C1C !important; }
        .status-pill.status-returned { background: #FFF7ED !important; color: #C2410C !important; }
        .status-pill.status-picked_up { background: #ECFEFF !important; color: #0891B2 !important; }
        .status-pill.status-arrived_at_origin { background: #F0F9FF !important; color: #0369A1 !important; }
        .status-pill.status-departed_from_origin { background: #EEF2FF !important; color: #4338CA !important; }
        .status-pill.status-arrived_at_destination { background: #F5F3FF !important; color: #7C3AED !important; }
        .status-pill.status-failed_delivery { background: #FFF1F2 !important; color: #E11D48 !important; }

        /* Quick Menu Premium Hover */
        .quick-action-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #f1f5f9;
        }
        .quick-action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.12);
            border-color: #e0e7ff;
            background: white !important;
        }
        .quick-action-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
        }
        .icon-box { transition: 0.3s; }

        /* Swal Overrides for consistency */
        .swal2-confirm { background-color: #6366F1 !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important; border-radius: 12px !important; font-weight: 700 !important; }
        .swal2-cancel { border-radius: 12px !important; font-weight: 700 !important; }
        .swal2-popup { border-radius: 24px !important; }
    </style>


     <?php $__env->slot('header', null, []); ?> 
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" id="current-view-title" style="font-size: 1.4rem;">Counter Console</h2>
                <div class="text-muted fw-bold" id="current-view-subtitle" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">
                    Cabang: <?php echo e($metrics['branch_name'] ?? 'Counter Utama'); ?>

                </div>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="dashboard-container">
        <!-- View: Overview -->
        <div id="view-overview" class="view-section active">
            <!-- Summary Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-indigo-light rounded-4 text-primary" style="background: #EEF2FF; color: #6366F1 !important;"><i class="bi bi-box-seam fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">SHIPMENTS TODAY</div><div class="h4 fw-bold mb-0" id="metric-shipments"><?php echo e(number_format($metrics['shipments_today'] ?? 0)); ?></div></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-success-light rounded-4 text-success" style="background: #ECFDF5;"><i class="bi bi-wallet2 fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">COLLECTED</div><div class="h4 fw-bold mb-0" id="metric-revenue">Rp<?php echo e(number_format($metrics['revenue_settlement_today'] ?? 0, 0, ',', '.')); ?></div></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-warning-light rounded-4 text-warning" style="background: #FFFBEB;"><i class="bi bi-hourglass-split fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">PENDING PAY</div><div class="h4 fw-bold mb-0" id="metric-approvals"><?php echo e(number_format($metrics['payments_pending'] ?? 0)); ?></div></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-danger-light rounded-4 text-danger" style="background: #FEF2F2;"><i class="bi bi-alarm-fill fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">OVERDUE</div><div class="h4 fw-bold mb-0" id="metric-errors"><?php echo e(number_format($metrics['shipments_overdue'] ?? 0)); ?></div></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Tren Transaksi Kasir (14 Hari)</h6>
                        <div class="chart-container">
                            <canvas id="chart-main-trend"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Distribusi Paket Hari Ini</h6>
                        <div class="chart-container">
                            <canvas id="chart-status-pie"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="row g-4 mb-4">
                <div class="col-lg-12">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Menu Cepat Kasir</h6>
                        <div class="row g-3">
                            <?php $__currentLoopData = [
                                ['icon' => 'bi-box-arrow-in-right', 'color' => '#6366F1', 'bg' => '#EEF2FF', 'title' => 'Input Shipment', 'onclick' => "switchView('view-shipments')"],
                                ['icon' => 'bi-credit-card-2-front', 'color' => '#10B981', 'bg' => '#ECFDF5', 'title' => 'Proses Bayar', 'onclick' => "switchView('view-payments')"],
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3">
                                <a href="<?php echo e(isset($action['route']) ? route($action['route']) : '#'); ?>" 
                                   <?php if(isset($action['onclick'])): ?> onclick="<?php echo e($action['onclick']); ?>" <?php endif; ?>
                                   class="quick-action-card p-4 rounded-4 d-block text-decoration-none text-dark bg-light text-center">
                                    <div class="icon-box p-3 rounded-4 mx-auto mb-3 d-inline-flex" style="background: <?php echo e($action['bg']); ?>; color: <?php echo e($action['color']); ?>;">
                                        <i class="bi <?php echo e($action['icon']); ?> fs-3"></i>
                                    </div>
                                    <div class="fw-bold"><?php echo e($action['title']); ?></div>
                                </a>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Stats Grid (From Manager UI) -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-3">Aktivitas Terakhir</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>WAKTU</th>
                                        <th>STATUS</th>
                                        <th>RESI</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-activity-body">
                                    <!-- Loaded via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-3">Kesehatan Operasional</h6>
                        <div id="integration-health-list" class="d-flex flex-column gap-3">
                            <!-- Loaded via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>


        
        <div id="view-shipments" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Data Pengiriman</h4>
                    <small class="text-muted fw-bold text-uppercase">Input & Monitoring Paket</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <input type="text" id="shipment-search" class="form-control form-control-sm border-0 shadow-sm rounded-pill px-3" placeholder="Tracking # or Name" style="background:#F1F5F9; font-weight:700; width:220px;" onkeyup="debounceLoad('view-shipments')">
                    <select id="filter-shipment-status" class="form-select form-select-sm border-0 shadow-sm rounded-pill" style="background:#F1F5F9; font-weight:700; width:150px;" onchange="window.loadViewData('view-shipments', 1)">
                        <option value="">All Status</option>
                    </select>
                    <button onclick="handleAddShipment()" class="btn btn-indigo rounded-pill px-4 fw-bold shadow-sm" style="background: #6366F1; color: white;"><i class="bi bi-plus-lg me-2"></i>Shipment Baru</button>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-shipments"><thead id="thead-view-shipments"></thead><tbody id="body-view-shipments"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-shipments-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.loadViewData('view-shipments', 1)">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-shipments" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>


        
        <div id="view-payments" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Layanan Pembayaran</h4>
                    <small class="text-muted fw-bold text-uppercase">Konfirmasi Transaksi Cabang</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <input type="text" id="payment-search" class="form-control form-control-sm border-0 shadow-sm rounded-pill px-3" placeholder="Search ID or Tracking" style="background:#F1F5F9; font-weight:700; width:220px;" onkeyup="debounceLoad('view-payments')">
                    <select id="filter-payment-status" class="form-select form-select-sm border-0 shadow-sm rounded-pill" style="background:#F1F5F9; font-weight:700; width:150px;" onchange="window.loadViewData('view-payments', 1)">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="settlement">Settlement</option>
                        <option value="cancel">Cancel</option>
                    </select>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-payments"><thead id="thead-view-payments"></thead><tbody id="body-view-payments"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-payments-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.loadViewData('view-payments', 1)">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-payments" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const state = {
            branches: [],
            statuses: [],
            couriers: []
        };

        const escapeHtml = (unsafe) => {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        };

        const getStatusClass = (code) => {
            return 'status-pill status-' + code;
        };

        const renderStatusPill = (code, name) => {
            const cls = getStatusClass(code.toLowerCase());
            return `<span class="${cls}"><i class="bi bi-circle-fill"></i>${escapeHtml(name)}</span>`;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('.view-section');
            const navLinks = document.querySelectorAll('.nav-link-custom');
            const titleEl = document.getElementById('current-view-title');
            const subtitleEl = document.getElementById('current-view-subtitle');

            const switchView = (viewId) => {
                sections.forEach(s => s.classList.remove('active'));
                const target = document.getElementById(viewId);
                if (target) target.classList.add('active');
                
                const config = {
                    'view-overview': ['Counter Console', 'Ringkasan Operasional Cabang'],
                    'view-shipments': ['Data Pengiriman', 'Input & Monitoring Paket'],
                    'view-payments': ['Layanan Pembayaran', 'Konfirmasi Transaksi Cabang']
                };
                
                if (config[viewId]) {
                    titleEl.innerText = config[viewId][0];
                    subtitleEl.innerText = config[viewId][1];
                }
            };

            window.switchView = switchView;

            const renderPagination = (container, meta, viewId) => {
                if (!container || !meta) return;
                let html = '<nav><ul class="pagination pagination-sm gap-1 border-0 m-0">';
                const isSinglePage = (meta.last_page || 1) <= 1;

                html += `<li class="page-item ${(isSinglePage || meta.current_page === 1) ? 'disabled' : ''}"><button class="page-link rounded-pill border-0 px-3 bg-light text-dark" onclick="loadViewData('${viewId}', ${meta.current_page - 1})">Prev</button></li>`;
                
                for (let i = 1; i <= (meta.last_page || 1); i++) {
                    if (i === 1 || i === meta.last_page || (i >= meta.current_page - 1 && i <= meta.current_page + 1)) {
                        html += `<li class="page-item ${meta.current_page === i ? 'active' : ''}"><button class="page-link rounded-circle border-0 mx-1 ${meta.current_page === i ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark'}" style="width:34px; height:34px; display:flex; align-items:center; justify-content:center; font-weight:800;" onclick="loadViewData('${viewId}', ${i})">${i}</button></li>`;
                    } else if (i === meta.current_page - 2 || i === meta.current_page + 2) {
                        html += '<li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>';
                    }
                }
                
                html += `<li class="page-item ${(isSinglePage || meta.current_page === meta.last_page) ? 'disabled' : ''}"><button class="page-link rounded-pill border-0 px-3 bg-light text-dark" onclick="loadViewData('${viewId}', ${meta.current_page + 1})">Next</button></li></ul></nav>`;
                container.innerHTML = html;
            };

            const loadOverviewData = async () => {
                try {
                    const { data } = await axios.get('/dashboard/data');
                    document.getElementById('metric-shipments').innerText = data.shipments_today;
                    document.getElementById('metric-revenue').innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(data.revenue_total);
                    document.getElementById('metric-approvals').innerText = data.outstanding_payments;
                    document.getElementById('metric-errors').innerText = data.service_reliability.critical_error_count;

                    // Load Recent Activity
                    document.getElementById('recent-activity-body').innerHTML = (data.trackings_recent || []).map(t => `
                        <tr>
                            <td class="text-muted small">${escapeHtml(new Date(t.event_at).toLocaleTimeString())}</td>
                            <td><span class="badge bg-primary-light text-primary rounded-pill px-2" style="font-size:0.7rem; background: #EEF2FF; color: #6366F1 !important;">${escapeHtml(t.status.name)}</span></td>
                            <td><small class="fw-bold">${escapeHtml(t.shipment.tracking_number)}</small></td>
                        </tr>
                    `).join('') || '<tr><td colspan="3" class="text-center py-3">Belum ada aktivitas.</td></tr>';

                    // Load Integration Health
                    document.getElementById('integration-health-list').innerHTML = (data.service_reliability.integration_statuses || []).map(s => `
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border shadow-sm">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:10px; height:10px; border-radius:50%; background:${s.status === 'healthy' ? '#10B981' : '#F59E0B'}"></div>
                                <span class="fw-bold text-uppercase small" style="font-size:0.7rem">${escapeHtml(s.service_name)}</span>
                            </div>
                            <span class="badge bg-white text-dark border rounded-pill small">${escapeHtml(s.success_count)} Transaksi</span>
                        </div>
                    `).join('');

                    if (data.shipment_statuses) {
                        const sel = document.getElementById('filter-shipment-status');
                        sel.innerHTML = '<option value="">All Status</option>' + 
                            data.shipment_statuses.map(s => `<option value="${escapeHtml(s.id)}">${escapeHtml(s.name)}</option>`).join('');
                    }

                    // Sync state from dashboard data
                    state.branches = data.branches || [];
                    state.statuses = data.shipment_statuses || [];
                    state.couriers = data.couriers || [];

                    // Fallback to API if payload is partial (for older logic compatibility)
                    if (state.branches.length === 0 || state.statuses.length === 0 || state.couriers.length === 0) {
                        try {
                            const [bRes, sRes, cRes] = await Promise.all([
                                axios.get('/branches?per_page=100').catch(() => ({data:{data:[]}})),
                                axios.get('/shipment-statuses?per_page=100').catch(() => ({data:{data:[]}})),
                                axios.get('/users?role=courier&per_page=100').catch(() => ({data:{data:[]}}))
                            ]);
                            if (state.branches.length === 0) state.branches = bRes.data.data || [];
                            if (state.statuses.length === 0) state.statuses = sRes.data.data || [];
                            if (state.couriers.length === 0) state.couriers = cRes.data.data || [];
                        } catch (e) { console.warn('Form data sync failed', e); }
                    }

                    renderCharts(data);
                } catch (error) { console.error(error); }
            };

            const renderCharts = (data) => {
                const trendCtx = document.getElementById('chart-main-trend').getContext('2d');
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: data.financial_control?.settlement_trend_daily.map(i => i.period) || [],
                        datasets: [{
                            label: 'Settlement Amount',
                            data: data.financial_control?.settlement_trend_daily.map(i => i.amount) || [],
                            borderColor: '#6366F1',
                            backgroundColor: 'rgba(99, 102, 241, 0.05)',
                            fill: true, tension: 0.4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });

                const statusCtx = document.getElementById('chart-status-pie').getContext('2d');
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.status_breakdown.map(i => i.name),
                        datasets: [{
                            data: data.status_breakdown.map(i => i.total),
                            backgroundColor: ['#6366F1', '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6B778C'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } } }
                });
            };

            let debounceTimer;
            window.debounceLoad = (viewId) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadViewData(viewId, 1), 500);
            };

            window.loadViewData = async (viewId, page = 1) => {
                const tbody = document.getElementById('body-' + viewId);
                const thead = document.getElementById('thead-' + viewId);
                const pagination = document.getElementById('pagination-' + viewId);
                if (!tbody || !thead) return;

                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';

                try {
                    let endpoint = '';
                    let response;

                    switch(viewId) {
                        case 'view-shipments':
                            const sSearch = document.getElementById('shipment-search').value;
                            const sStatus = document.getElementById('filter-shipment-status').value;
                            const sLimit = document.getElementById('sel-view-shipments-limit')?.value || 10;
                            endpoint = `/shipments?page=${page}&search=${sSearch}&status_id=${sStatus}&per_page=${sLimit}`;
                            thead.innerHTML = '<tr><th>Tracking #</th><th>Sender</th><th>Recipient</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = response.data.data.map(item => `
                                <tr class="hover-row">
                                    <td>
                                        <div class="fw-extrabold text-primary" style="font-size: 1rem;">${escapeHtml(item.tracking_number)}</div>
                                        <div class="badge bg-light text-dark border-0 mt-1" style="font-size:0.6rem; letter-spacing: 0.5px;">${escapeHtml(item.service_type.toUpperCase())}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark">${escapeHtml(item.sender_name)}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">${escapeHtml(item.sender_phone || '')}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark">${escapeHtml(item.recipient_name)}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">${escapeHtml(item.recipient_phone || '')}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">Rp${new Intl.NumberFormat('id-ID').format(item.total_amount)}</div>
                                        <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.65rem;">
                                            <i class="bi bi-wallet2 text-primary"></i> ${item.payment_status?.toUpperCase() || 'UNPAID'}
                                        </div>
                                    </td>
                                    <td>${renderStatusPill(item.status.code, item.status.name)}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="/shipments/${item.id}/label" class="btn-action-sm btn-print" title="Print Resi" target="_blank">
                                                <i class="bi bi-printer-fill"></i>
                                            </a>
                                            <button class="btn-action-sm btn-history" title="Riwayat Tracking" onclick="handleViewHistory(${item.id})">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                            <button class="btn-action-sm btn-edit" title="Edit Data" onclick="handleEditShipment(${item.id})">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button class="btn-action-sm btn-delete" title="Hapus Data" onclick="handleDeleteShipment(${item.id})">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="6" class="text-center py-5">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;

                        case 'view-payments':
                            const pSearch = document.getElementById('payment-search').value;
                            const pStatus = document.getElementById('filter-payment-status').value;
                            const pLimit = document.getElementById('sel-view-payments-limit')?.value || 10;
                            endpoint = `/payments?per_page=${pLimit}&page=${page}&search=${pSearch}&status=${pStatus}`;
                            thead.innerHTML = '<tr><th>#ID</th><th>Shipment</th><th>Method</th><th>Amount</th><th>Status</th><th class="text-end">Aksi</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = response.data.data.map(item => `
                                <tr class="hover-row">
                                    <td><div class="fw-bold text-dark">#${item.id}</div></td>
                                    <td>
                                        <div class="small fw-bold">${escapeHtml(item.shipment?.tracking_number || '-')}</div>
                                        <div class="text-muted small">${escapeHtml(item.shipment?.sender_name || '')}</div>
                                    </td>
                                    <td><div class="small fw-bold text-uppercase">${escapeHtml(item.method)}</div></td>
                                    <td><div class="fw-bold text-primary">Rp${new Intl.NumberFormat('id-ID').format(item.amount)}</div></td>
                                    <td>
                                        <span class="status-pill ${item.status === 'settlement' || item.status === 'paid' ? 'bg-success-light text-success' : (['failed', 'cancel', 'expire'].includes(item.status) ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning')}">
                                            <i class="bi bi-circle-fill"></i>${escapeHtml(item.status.toUpperCase())}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn-action-sm btn-edit shadow-sm" onclick="handleEditPayment(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                        </div>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="6" class="text-center py-4">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;
                    }
                } catch (e) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error loading data.</td></tr>'; }
            };

            // SHIPMENT HANDLERS
            window.handleAddShipment = async () => {
                const userBranchId = <?php echo e(auth()->user()->branch_id); ?>;
                const { value: formValues } = await Swal.fire({
                    title: 'Tambah Shipment Baru',
                    html: `
                        <div class="text-start" style="font-size: 0.85rem;">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-1">Pengirim</label>
                                    <input id="swal-s-name" class="form-control form-control-sm" placeholder="Nama">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-1">HP Pengirim</label>
                                    <input id="swal-s-phone" class="form-control form-control-sm" placeholder="0812...">
                                </div>
                            </div>
                            <label class="fw-bold mb-1">Alamat Pengirim</label>
                            <textarea id="swal-s-addr" class="form-control form-control-sm mb-2" rows="2"></textarea>
                            
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-1">Penerima</label>
                                    <input id="swal-r-name" class="form-control form-control-sm" placeholder="Nama">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-1">HP Penerima</label>
                                    <input id="swal-r-phone" class="form-control form-control-sm" placeholder="0812...">
                                </div>
                            </div>
                            <label class="fw-bold mb-1">Alamat Penerima</label>
                            <textarea id="swal-r-addr" class="form-control form-control-sm mb-2" rows="2"></textarea>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-1">Cabang Tujuan</label>
                                    <select id="swal-dest" class="form-select form-select-sm">
                                        ${state.branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-1">Layanan</label>
                                    <select id="swal-service" class="form-select form-select-sm">
                                        <option value="regular">REGULAR</option>
                                        <option value="express">EXPRESS</option>
                                        <option value="same_day">SAME DAY</option>
                                        <option value="economy">ECONOMY</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-1">Berat (Kg)</label>
                                    <input id="swal-weight" class="form-control form-control-sm" type="number" step="0.1" value="1">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-1">Notes</label>
                                    <input id="swal-notes" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Shipment',
                    preConfirm: () => {
                        return {
                            branch_id: userBranchId,
                            destination_branch_id: document.getElementById('swal-dest').value,
                            sender_name: document.getElementById('swal-s-name').value,
                            sender_phone: document.getElementById('swal-s-phone').value,
                            sender_address: document.getElementById('swal-s-addr').value,
                            recipient_name: document.getElementById('swal-r-name').value,
                            recipient_phone: document.getElementById('swal-r-phone').value,
                            recipient_address: document.getElementById('swal-r-addr').value,
                            service_type: document.getElementById('swal-service').value,
                            total_weight_kg: document.getElementById('swal-weight').value,
                            notes: document.getElementById('swal-notes').value
                        }
                    }
                });

                if (formValues) {
                    try {
                        await axios.post('/shipments', formValues);
                        Swal.fire('Berhasil', 'Shipment baru ditambahkan.', 'success').then(() => loadViewData('view-shipments', 1));
                    } catch (e) {
                        Swal.fire('Error', e.response?.data?.message || 'Gagal menambahkan shipment.', 'error');
                    }
                }
            };

            window.handleEditShipment = async (id) => {
                try {
                    const { data: s } = await axios.get(`/shipments/${id}`);
                    const { value: formValues } = await Swal.fire({
                        title: 'Edit Shipment',
                        html: `
                            <div class="text-start" style="font-size: 0.85rem;">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="fw-bold mb-1 small">Status Pengiriman</label>
                                        <select id="swal-status-id" class="form-select form-select-sm mb-2">
                                            ${state.statuses.map(st => `<option value="${st.id}" ${s.status_id === st.id ? 'selected' : ''}>${escapeHtml(st.name)}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="fw-bold mb-1 small">Kurir</label>
                                        <select id="swal-courier-id" class="form-select form-select-sm mb-2">
                                            <option value="">-- No Courier --</option>
                                            ${state.couriers.map(c => `<option value="${c.id}" ${s.courier_id === c.id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="fw-bold mb-1 small">Berat (kg)</label>
                                        <input id="swal-weight" type="number" step="0.1" class="form-control form-control-sm mb-2" value="${s.total_weight_kg || 0}">
                                    </div>
                                    <div class="col-6">
                                        <label class="fw-bold mb-1 small">Volume</label>
                                        <input id="swal-volume" type="number" step="0.1" class="form-control form-control-sm mb-2" value="${s.total_volume || 0}">
                                    </div>
                                </div>
                                <label class="fw-bold mb-1 small">Cabang Tujuan</label>
                                <select id="swal-dest-id" class="form-select form-select-sm mb-2">
                                    ${state.branches.map(b => `<option value="${b.id}" ${s.destination_branch_id === b.id ? 'selected' : ''}>${escapeHtml(b.name)}</option>`).join('')}
                                </select>
                                <div class="p-2 rounded-3 bg-light border mb-2">
                                    <label class="fw-bold mb-1 small text-primary">Informasi Tracking (Update)</label>
                                    <input id="swal-location" class="form-control form-control-sm mb-1" placeholder="Lokasi saat ini" value="${escapeHtml(s.branch?.name || '')}">
                                    <input id="swal-tracking-notes" class="form-control form-control-sm" placeholder="Catatan tracking" value="Status diperbarui oleh Kasir">
                                </div>
                                <hr class="my-2">
                                <label class="fw-bold mb-1">Penerima</label>
                                <input id="swal-r-name" class="form-control form-control-sm mb-2" value="${escapeHtml(s.recipient_name || '')}">
                                <label class="fw-bold mb-1">HP Penerima</label>
                                <input id="swal-r-phone" class="form-control form-control-sm mb-2" value="${escapeHtml(s.recipient_phone || '')}">
                                <label class="fw-bold mb-1">Alamat Penerima</label>
                                <textarea id="swal-r-addr" class="form-control form-control-sm mb-2">${escapeHtml(s.recipient_address || '')}</textarea>
                                <label class="fw-bold mb-1">Notes / Alasan</label>
                                <input id="swal-notes" class="form-control form-control-sm mb-2" value="${escapeHtml(s.notes || '')}">
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update Shipment',
                        preConfirm: () => {
                            return {
                                status_id: document.getElementById('swal-status-id').value,
                                courier_id: document.getElementById('swal-courier-id').value,
                                destination_branch_id: document.getElementById('swal-dest-id').value,
                                total_weight_kg: document.getElementById('swal-weight').value,
                                total_volume: document.getElementById('swal-volume').value,
                                recipient_name: document.getElementById('swal-r-name').value,
                                recipient_phone: document.getElementById('swal-r-phone').value,
                                recipient_address: document.getElementById('swal-r-addr').value,
                                notes: document.getElementById('swal-notes').value,
                                location: document.getElementById('swal-location').value,
                                tracking_notes: document.getElementById('swal-tracking-notes').value,
                                manual_override: false,
                                reason: document.getElementById('swal-notes').value || 'Update shipment oleh kasir',
                                manual_override_reason: document.getElementById('swal-notes').value || 'Update shipment oleh kasir'
                            }
                        }
                    });

                    if (formValues) {
                        await axios.put(`/shipments/${id}`, formValues);
                        Swal.fire('Berhasil', 'Shipment diperbarui.', 'success').then(() => loadViewData('view-shipments', 1));
                    }
                } catch (error) { Swal.fire('Error', 'Gagal memproses data.', 'error'); }
            };

            window.handleDeleteShipment = (id) => {
                Swal.fire({
                    title: 'Hapus Shipment?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#F43F5E',
                    confirmButtonText: 'Ya, Hapus!'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`/shipments/${id}`);
                            Swal.fire('Terhapus!', 'Shipment telah dihapus.', 'success');
                            loadViewData('view-shipments');
                        } catch (error) { Swal.fire('Error', error.response?.data?.message || 'Gagal menghapus data', 'error'); }
                    }
                });
            };

            window.handleViewHistory = async (id) => {
                try {
                    const { data: s } = await axios.get(`/shipments/${id}`);
                    const trackings = s.trackings || [];
                    
                    let historyHtml = '<div class="text-start" style="font-size: 0.85rem;">';
                    if (trackings.length === 0) {
                        historyHtml += '<p class="text-center text-muted py-3">Belum ada riwayat tracking.</p>';
                    } else {
                        historyHtml += '<div class="timeline-modern">';
                        trackings.sort((a, b) => new Date(b.event_at) - new Date(a.event_at)).forEach((t, idx) => {
                            historyHtml += `
                                <div class="timeline-item pb-3 border-start ps-3 position-relative" style="border-color: #E2E8F0 !important;">
                                    <div class="position-absolute bg-primary rounded-circle" style="width:10px; height:10px; left: -5.5px; top: 5px;"></div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <i class="bi bi-patch-check-fill text-primary" style="font-size: 0.8rem;"></i>
                                        ${escapeHtml(t.status?.name || 'Status Unknown')}
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-1">
                                        <i class="bi bi-calendar3" style="font-size: 0.7rem;"></i>
                                        ${new Date(t.event_at).toLocaleString('id-ID')}
                                    </div>
                                    <div class="small mt-1 d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt-fill text-danger" style="font-size: 0.7rem;"></i>
                                        ${escapeHtml(t.location || 'Unknown')}
                                    </div>
                                    ${t.notes ? `<div class="mt-1 p-2 bg-light rounded small border d-flex align-items-start gap-2">
                                        <i class="bi bi-info-circle text-info" style="font-size: 0.75rem; margin-top: 2px;"></i>
                                        <span>${escapeHtml(t.notes)}</span>
                                    </div>` : ''}
                                </div>
                            `;
                        });
                        historyHtml += '</div>';
                    }
                    historyHtml += '</div>';

                    Swal.fire({
                        title: 'Riwayat Tracking: ' + s.tracking_number,
                        html: historyHtml,
                        width: '500px',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                } catch (error) { Swal.fire('Error', 'Gagal memuat riwayat', 'error'); }
            };

            // PAYMENT HANDLERS
            window.handleEditPayment = async (id) => {
                try {
                    const { data } = await axios.get(`/payments/${id}`);
                    const { value: formValues } = await Swal.fire({
                        title: 'Edit Pembayaran',
                        html: `
                            <div class="text-start">
                                <label class="small fw-bold">Metode</label>
                                <select id="swal-method" class="form-select mb-3">
                                    <option value="cash" ${data.method === 'cash' ? 'selected' : ''}>Cash</option>
                                    <option value="transfer" ${data.method === 'transfer' ? 'selected' : ''}>Transfer</option>
                                    <option value="e_wallet" ${data.method === 'e_wallet' ? 'selected' : ''}>E-Wallet</option>
                                </select>
                                <label class="small fw-bold">Status</label>
                                <select id="swal-status" class="form-select mb-3">
                                    <option value="pending" ${data.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="settlement" ${data.status === 'settlement' ? 'selected' : ''}>Settlement</option>
                                    <option value="cancel" ${data.status === 'cancel' ? 'selected' : ''}>Cancel</option>
                                </select>
                                <label class="small fw-bold">Alasan Perubahan</label>
                                <textarea id="swal-reason" class="form-control" placeholder="Wajib diisi untuk approval manager..."></textarea>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Ajukan Approval',
                        preConfirm: () => {
                            const method = document.getElementById('swal-method').value;
                            const status = document.getElementById('swal-status').value;
                            const reason = document.getElementById('swal-reason').value;
                            if (!reason) return Swal.showValidationMessage('Alasan wajib diisi!');
                            return { method, status, reason };
                        }
                    });

                    if (formValues) {
                        const res = await axios.put(`/payments/${id}`, formValues);
                        if (res.status === 202) {
                            Swal.fire('Menunggu Approval', res.data.message, 'info');
                        } else {
                            Swal.fire('Berhasil', 'Pembayaran diperbarui.', 'success');
                        }
                        loadViewData('view-payments');
                    }
                } catch (error) { Swal.fire('Error', error.response?.data?.message || 'Gagal memproses', 'error'); }
            };

            window.handleDeletePayment = (id) => {
                // Feature removed for Kasir as per request
            };

            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    let targetKey = 'view-overview';
                    if (href.includes('shipments')) targetKey = 'view-shipments';
                    else if (href.includes('payments')) targetKey = 'view-payments';
                    
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    switchView(targetKey);
                    if (targetKey !== 'view-overview') loadViewData(targetKey, 1);
                    else loadOverviewData();
                });
            });

            loadOverviewData();
        });
    </script>

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
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/dashboard/kasir.blade.php ENDPATH**/ ?>