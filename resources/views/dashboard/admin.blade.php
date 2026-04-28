<x-app-layout>
    <style>
        .dashboard-container {
            padding: 1.5rem;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .card-pro {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 97, 255, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card-pro:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 97, 255, 0.08);
            border-color: #ebf3ff;
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
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            border: none;
        }

        .btn-edit { background: #EBF3FF; color: var(--primary); }
        .btn-delete { background: #FEF2F2; color: #DC2626; }

        .status-pill {
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Filter UI Redesign */
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
            color: var(--primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" id="current-view-title" style="font-size: 1.4rem;">Kondang Ekspedisi Console</h2>
                <div class="text-muted fw-bold" id="current-view-subtitle" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Ringkasan Operasional</div>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-container">
        <!-- View: Overview -->
        <div id="view-overview" class="view-section active">
            <!-- Summary Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-primary-light rounded-4 text-primary"><i class="bi bi-box-seam fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">SHIPMENTS</div><div class="h4 fw-bold mb-0" id="metric-shipments">{{ number_format($metrics['shipments_today'] ?? 0) }}</div></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-success-light rounded-4 text-success" style="background: #ECFDF5;"><i class="bi bi-wallet2 fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">REVENUE</div><div class="h4 fw-bold mb-0" id="metric-revenue">Rp{{ number_format($metrics['revenue_settlement_today'] ?? 0, 0, ',', '.') }}</div></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-warning-light rounded-4 text-warning" style="background: #FFFBEB;"><i class="bi bi-patch-check fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">APPROVALS</div><div class="h4 fw-bold mb-0" id="metric-approvals">{{ number_format($metrics['pending_approvals'] ?? 0) }}</div></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card-pro p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-danger-light rounded-4 text-danger" style="background: #FEF2F2;"><i class="bi bi-bug fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">ERRORS</div><div class="h4 fw-bold mb-0" id="metric-errors">{{ number_format($metrics['errors_unresolved'] ?? 0) }}</div></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Tren Pengiriman & Pendapatan (14 Hari)</h6>
                        <div class="chart-container">
                            <canvas id="chart-main-trend"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Status Pengiriman</h6>
                        <div class="chart-container">
                            <canvas id="chart-status-pie"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Stats Grid -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-3">Aktivitas Terakhir</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>WAKTU</th>
                                        <th>AKSI</th>
                                        <th>OBJEK</th>
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
                        <h6 class="fw-bold text-dark mb-3">Kesehatan Integrasi Service</h6>
                        <div id="integration-health-list" class="d-flex flex-column gap-3">
                            <!-- Loaded via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRUD Sections -->
        @php
            $views = [
                ['id' => 'view-approvals', 'title' => 'Pusat Persetujuan', 'subtitle' => 'Approval History & Logs'],
                ['id' => 'view-shipments', 'title' => 'Logistik & Pengiriman', 'subtitle' => 'Data Seluruh Shipment Nasional'],
                ['id' => 'view-payments', 'title' => 'Transaksi Keuangan', 'subtitle' => 'Monitoring Transaksi & Settlement'],
                ['id' => 'view-rate-cards', 'title' => 'Konfigurasi Biaya', 'subtitle' => 'Rate Card Management'],
                ['id' => 'view-branches', 'title' => 'Manajemen Jaringan', 'subtitle' => 'Cabang & Zona Wilayah'],
                ['id' => 'view-users', 'title' => 'Akses & Keamanan', 'subtitle' => 'User Management'],
                ['id' => 'view-reports', 'title' => 'Data Intelligence', 'subtitle' => 'Laporan Operasional'],
            ];
        @endphp

        @foreach($views as $v)
        <div id="{{ $v['id'] }}" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">{{ $v['title'] }}</h4>
                    <small class="text-muted fw-bold uppercase">{{ $v['subtitle'] }}</small>
                </div>
                
                <div class="d-flex gap-3 align-items-center">
                    @if($v['id'] === 'view-shipments')
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 text-muted small fw-bold">SEARCH</span>
                            <input type="text" id="shipment-search" class="form-control rounded-end-pill px-3 shadow-sm border-0" placeholder="Tracking # or Name" style="background:#F1F5F9; font-weight:700;" onkeyup="debounceLoad('view-shipments')">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 text-muted small fw-bold">STATUS</span>
                            <select id="filter-shipment-status" class="form-select rounded-end-pill px-3 shadow-sm border-0" style="background:#F1F5F9; font-weight:700;" onchange="window.handleFilterChange('view-shipments')">
                                <option value="">All Status</option>
                                <option value="1">Pending</option>
                                <option value="2">In Transit</option>
                                <option value="3">Out for Delivery</option>
                                <option value="4">Delivered</option>
                                <option value="5">Cancelled</option>
                                <option value="6">Returned</option>
                            </select>
                        </div>
                    @endif
                    @if($v['id'] === 'view-payments')
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 text-muted small fw-bold">STATUS</span>
                            <select id="filter-payment-status" class="form-select rounded-end-pill px-3 shadow-sm border-0" style="background:#F1F5F9; font-weight:700;" onchange="window.handleFilterChange('view-payments')">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="settlement">Settlement</option>
                                <option value="expire">Expired</option>
                                <option value="cancel">Cancelled</option>
                            </select>
                        </div>
                    @endif
                    @if($v['id'] === 'view-approvals')
                        <div class="filter-pill-container" id="approval-status-filters">
                            <button class="filter-pill active" onclick="setApprovalFilter('status', 'all', this)">All</button>
                            <button class="filter-pill" onclick="setApprovalFilter('status', 'pending', this)">Pending</button>
                            <button class="filter-pill" onclick="setApprovalFilter('status', 'completed', this)">Approved</button>
                            <button class="filter-pill" onclick="setApprovalFilter('status', 'cancelled', this)">Rejected</button>
                        </div>
                        <div class="d-flex gap-2">
                            <select id="sel-app-scope" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700;" onchange="window.handleFilterChange('view-approvals')">
                                <option value="all">All Types</option>
                                <option value="approve_rate_card">Rate Card</option>
                                <option value="shipment_final_status_approval">Final Status</option>
                                <option value="shipment_reassign_approval">Reassign</option>
                            </select>
                        </div>
                    @else
                        @if($v['id'] !== 'view-reports' && $v['id'] !== 'view-shipments' && $v['id'] !== 'view-payments')
                            <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="handleAddData('{{ $v['id'] }}')"><i class="bi bi-plus-lg me-2"></i>Tambah Data</button>
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-{{ $v['id'] }}">
                        <thead id="thead-{{ $v['id'] }}"></thead>
                        <tbody id="body-{{ $v['id'] }}"></tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-bold">Limit:</span>
                        <select id="sel-{{ $v['id'] }}-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('{{ $v['id'] }}')">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div id="pagination-{{ $v['id'] }}" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global State for Filters
        const state = {
            approval: { status: 'all', scope: 'all', limit: 10 }
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
                    'view-overview': ['Kondang Ekspedisi Console', 'Ringkasan Operasional'],
                    'view-approvals': ['Pusat Persetujuan', 'Approval History & Logs'],
                    'view-shipments': ['Logistik & Pengiriman', 'Database Shipment'],
                    'view-payments': ['Transaksi Keuangan', 'Payment Gateway Monitoring'],
                    'view-rate-cards': ['Konfigurasi Biaya', 'Rate Card Management'],
                    'view-branches': ['Manajemen Jaringan', 'Cabang & Zona Wilayah'],
                    'view-users': ['Akses & Keamanan', 'User Management'],
                    'view-reports': ['Data Intelligence', 'Laporan Operasional']
                };
                
                if (config[viewId]) {
                    titleEl.innerText = config[viewId][0];
                    subtitleEl.innerText = config[viewId][1];
                }
            };

            window.setApprovalFilter = (key, value, el) => {
                state.approval[key] = value;
                if (el) {
                    el.parentElement.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
                    el.classList.add('active');
                }
                window.handleFilterChange('view-approvals');
            };

            window.handleFilterChange = (viewId) => {
                loadViewData(viewId, 1);
            };

            const loadOverviewData = async () => {
                try {
                    const { data } = await axios.get('/dashboard/data');
                    document.getElementById('metric-shipments').innerText = data.shipments_today;
                    document.getElementById('metric-revenue').innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(data.revenue_total);
                    document.getElementById('metric-approvals').innerText = data.outstanding_payments;
                    document.getElementById('metric-errors').innerText = data.service_reliability.critical_error_count;

                    document.getElementById('recent-activity-body').innerHTML = (data.trackings_recent || []).map(t => `
                        <tr>
                            <td class="text-muted small">${new Date(t.event_at).toLocaleTimeString()}</td>
                            <td><span class="fw-bold">${t.status.name}</span></td>
                            <td><small>${t.shipment.tracking_number}</small></td>
                        </tr>
                    `).join('') || '<tr><td colspan="3" class="text-center py-3">Belum ada aktivitas.</td></tr>';

                    document.getElementById('integration-health-list').innerHTML = (data.service_reliability.integration_statuses || []).map(s => `
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-light border shadow-sm">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:10px; height:10px; border-radius:50%; background:${s.status === 'healthy' ? '#10B981' : '#F59E0B'}"></div>
                                <span class="fw-bold text-uppercase small">${s.service_name}</span>
                            </div>
                            <span class="badge bg-white text-dark border-1 border-secondary rounded-pill">${s.success_count} Success</span>
                        </div>
                    `).join('');

                    renderCharts(data);
                } catch (error) { console.error(error); }
            };

            const renderCharts = (data) => {
                const trendCtx = document.getElementById('chart-main-trend').getContext('2d');
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: data.financial_control.settlement_trend_daily.map(i => i.period),
                        datasets: [{
                            label: 'Settlement Amount',
                            data: data.financial_control.settlement_trend_daily.map(i => i.amount),
                            borderColor: '#0061FF',
                            backgroundColor: 'rgba(0, 97, 255, 0.05)',
                            fill: true,
                            tension: 0.4
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
                            backgroundColor: ['#0061FF', '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6B778C'],
                            borderWidth: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
                });
            };

            let debounceTimer;
            window.debounceLoad = (viewId) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadViewData(viewId, 1), 500);
            };

            const loadViewData = async (viewId, page = 1) => {
                const tbody = document.getElementById('body-' + viewId);
                const thead = document.getElementById('thead-' + viewId);
                const pagination = document.getElementById('pagination-' + viewId);
                if (!tbody || !thead) return;

                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';

                try {
                    let endpoint = '';
                    let rows = '';
                    let meta = {};

                    switch(viewId) {
                        case 'view-shipments':
                            const sSearch = document.getElementById('shipment-search').value;
                            const sStatus = document.getElementById('filter-shipment-status').value;
                            const sLimit = document.getElementById('sel-view-shipments-limit').value;
                            
                            // Debugging log (can be seen in browser console)
                            console.log('Fetching shipments with status_id:', sStatus);
                            
                            endpoint = `/shipments?page=${page}&search=${sSearch}&per_page=${sLimit}`;
                            if(sStatus) endpoint += `&status_id=${sStatus}`;
                            
                            thead.innerHTML = '<tr><th>Tracking #</th><th>Sender Detail</th><th>Recipient Detail</th><th>Shipping Info</th><th>Financial</th><th class="text-end">Status</th></tr>';
                            thead.innerHTML = '<tr><th>Tracking #</th><th>Sender Detail</th><th>Recipient Detail</th><th>Shipping Info</th><th>Financial</th><th class="text-end">Status</th></tr>';
                            const sRes = await axios.get(endpoint);
                            meta = sRes.data;
                            rows = sRes.data.data.map(item => `
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary mb-1">${item.tracking_number}</div>
                                        <span class="badge bg-light text-dark border-0 p-0" style="font-size:0.65rem;">${item.service_type.toUpperCase()}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark">${item.sender_name}</div>
                                        <div style="font-size:0.75rem;" class="text-muted text-truncate" style="max-width:150px;">${item.sender_address}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark">${item.recipient_name}</div>
                                        <div style="font-size:0.75rem;" class="text-muted text-truncate" style="max-width:150px;">${item.recipient_address}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold"><i class="bi bi-geo-alt-fill me-1"></i>${item.branch ? item.branch.city : '-'}</div>
                                        <div style="font-size:0.75rem;" class="text-muted">Weight: ${item.total_weight_kg}kg</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold">Rp${new Intl.NumberFormat('id-ID').format(item.total_amount)}</div>
                                        <div style="font-size:0.75rem;" class="text-muted text-uppercase">${item.payment_status}</div>
                                    </td>
                                    <td class="text-end">
                                        <span class="status-pill bg-primary-light text-primary" style="font-size:0.7rem;">${item.status.name}</span>
                                    </td>
                                </tr>`).join('');
                            break;

                        case 'view-payments':
                            const pStatus = document.getElementById('filter-payment-status').value;
                            const pLimit = document.getElementById('sel-view-payments-limit').value;
                            endpoint = `/payments?page=${page}&status=${pStatus}&per_page=${pLimit}`;
                            thead.innerHTML = '<tr><th>#</th><th>Reference & Link</th><th>Payment Info</th><th>Amount</th><th>Transaction Status</th><th class="text-end">Last Updated</th></tr>';
                            const pRes = await axios.get(endpoint);
                            meta = pRes.data;
                            rows = pRes.data.data.map((item, index) => {
                                const s = item.status.toLowerCase();
                                let statusClass = 'bg-secondary-light text-muted';
                                if(s === 'settlement' || s === 'capture') statusClass = 'bg-success-light text-success';
                                if(s === 'pending') statusClass = 'bg-warning-light text-warning';
                                if(s === 'expire' || s === 'cancel' || s === 'deny') statusClass = 'bg-danger-light text-danger';

                                return `
                                <tr>
                                    <td><span class="text-muted small">${(meta.current_page - 1) * meta.per_page + (index + 1)}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">#${item.id}</div>
                                        <div style="font-size:0.75rem;" class="text-muted">${item.reference_id || 'Internal Ref'}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-dark text-uppercase">${item.method}</div>
                                        <div style="font-size:0.75rem;" class="text-muted">${item.channel || 'Generic Channel'}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">Rp${new Intl.NumberFormat('id-ID').format(item.amount)}</div>
                                        <div style="font-size:0.7rem;" class="text-muted">Fee Incl: ${item.fee_amount ? 'Yes' : 'No'}</div>
                                    </td>
                                    <td>
                                        <span class="status-pill ${statusClass}">${item.status.toUpperCase()}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="small fw-bold text-dark">${item.paid_at ? new Date(item.paid_at).toLocaleDateString('id-ID') : '-'}</div>
                                        <div style="font-size:0.75rem;" class="text-muted">${item.paid_at ? new Date(item.paid_at).toLocaleTimeString('id-ID') : 'Not Yet Paid'}</div>
                                    </td>
                                </tr>`;
                            }).join('');
                            break;

                        case 'view-approvals':
                            const status = state.approval.status;
                            const scope = document.getElementById('sel-app-scope').value;
                            const limit = document.getElementById('sel-view-approvals-limit').value;
                            const apiStatus = status === 'completed' ? 'completed' : (status === 'cancelled' ? 'cancelled' : (status === 'pending' ? 'pending' : 'all'));
                            endpoint = `/approvals?status=${apiStatus}&scope=${scope}&per_page=${limit}&page=${page}`;
                            thead.innerHTML = '<tr><th>#</th><th>Type</th><th>Task Detail</th><th>Creator</th><th>Status</th><th>Priority</th><th class="text-end">Aksi</th></tr>';
                            const aRes = await axios.get(endpoint);
                            const aData = aRes.data.data;
                            meta = aData;
                            rows = aData.data.map((item, index) => {
                                const s = item.status.toLowerCase();
                                let statusClass = (s === 'pending' || s === 'in_progress') ? 'bg-warning-light text-warning' : (s === 'completed' ? 'bg-success-light text-success' : 'bg-danger-light text-danger');
                                
                                let resultInfo = `<div class="small text-muted">${item.description || ''}</div>`;
                                if(item.result && item.result.decision) {
                                    const decision = item.result.decision.toLowerCase();
                                    resultInfo = `<div class="mt-1 p-2 rounded-3 bg-light border-start border-3 ${decision === 'approved' ? 'border-success' : 'border-danger'}"><div class="small fw-bold ${decision === 'approved' ? 'text-success' : 'text-danger'}">${decision.toUpperCase()}</div><div style="font-size:0.7rem" class="text-muted">Note: ${item.result.approval_note || item.result.reason || '-'}</div></div>`;
                                }

                                return `
                                <tr>
                                    <td><span class="text-muted small">${(aData.current_page - 1) * aData.per_page + (index + 1)}</span></td>
                                    <td><span class="badge bg-secondary-light text-dark border-0 text-uppercase" style="font-size:0.6rem;">${item.task_type.replace(/_/g, ' ')}</span></td>
                                    <td style="max-width:300px;"><div class="fw-bold text-dark" style="font-size:0.9rem;">${item.title}</div>${resultInfo}</td>
                                    <td><div class="small fw-bold">${item.creator ? item.creator.name : 'System'}</div></td>
                                    <td><span class="status-pill ${statusClass}">${item.status.toUpperCase()}</span></td>
                                    <td><span class="fw-bold ${item.priority === 'high' ? 'text-danger' : 'text-primary'}" style="font-size:0.8rem;">${item.priority.toUpperCase()}</span></td>
                                    <td class="text-end">
                                        ${(s === 'pending' || s === 'in_progress') ? `
                                            <button class="btn-action-sm btn-edit me-1 shadow-sm" onclick="handleApproveTask(${item.id})"><i class="bi bi-check-lg text-success"></i></button>
                                            <button class="btn-action-sm btn-delete shadow-sm" onclick="handleRejectTask(${item.id})"><i class="bi bi-x-lg"></i></button>
                                        ` : `<span class="badge bg-light text-muted border py-2 px-3 rounded-pill" style="font-size:0.7rem;"><i class="bi bi-check-all me-1"></i>PROCESSED</span>`}
                                    </td>
                                </tr>`;
                            }).join('');
                            break;

                        default:
                            rows = '<tr><td colspan="7" class="text-center py-4 text-muted">Halaman sedang dalam pengembangan.</td></tr>';
                    }

                    tbody.innerHTML = rows || '<tr><td colspan="7" class="text-center py-4">Tidak ada data.</td></tr>';
                    renderPagination(pagination, meta, viewId);
                } catch (error) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
                }
            };

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

            window.handleApproveTask = (id) => {
                Swal.fire({
                    title: 'Setujui Request?',
                    text: "Tindakan ini akan menerapkan perubahan yang diajukan.",
                    input: 'text',
                    inputPlaceholder: 'Tambahkan catatan (opsional)...',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'Ya, Approve!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            await axios.post(`/approvals/tasks/${id}/approve`, { note: result.value });
                            Swal.fire('Berhasil!', 'Request telah disetujui.', 'success');
                            loadViewData('view-approvals', 1);
                        } catch (error) { Swal.fire('Gagal!', error.response?.data?.message || 'Gagal.', 'error'); }
                    }
                });
            };

            window.handleRejectTask = (id) => {
                Swal.fire({
                    title: 'Tolak Request?',
                    text: "Berikan alasan penolakan.",
                    input: 'text',
                    inputAttributes: { required: 'true' },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed && result.value) {
                        try {
                            await axios.post(`/approvals/tasks/${id}/reject`, { reason: result.value });
                            Swal.fire('Berhasil!', 'Request ditolak.', 'success');
                            loadViewData('view-approvals', 1);
                        } catch (error) { Swal.fire('Gagal!', 'Gagal menolak.', 'error'); }
                    } else if (result.isConfirmed) { Swal.fire('Peringatan', 'Alasan penolakan wajib diisi.', 'warning'); }
                });
            };

            window.handleDelete = (resource, id) => {
                Swal.fire({
                    title: 'Hapus Data?',
                    text: "Data akan dipindahkan ke tempat sampah.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`/${resource}/${id}`);
                            Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                            loadViewData('view-' + resource, 1);
                        } catch (error) { Swal.fire('Gagal!', 'Gagal menghapus.', 'error'); }
                    }
                });
            };

            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    let targetKey = '';
                    if (href.includes('approvals')) targetKey = 'view-approvals';
                    else if (href.includes('shipments')) targetKey = 'view-shipments';
                    else if (href.includes('payments')) targetKey = 'view-payments';
                    else if (href.includes('rate-cards')) targetKey = 'view-rate-cards';
                    else if (href.includes('branches')) targetKey = 'view-branches';
                    else if (href.includes('users')) targetKey = 'view-users';
                    else if (href.includes('reports')) targetKey = 'view-reports';
                    else targetKey = 'view-overview';

                    if (targetKey !== 'view-overview') {
                        e.preventDefault();
                        navLinks.forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                        switchView(targetKey);
                        loadViewData(targetKey, 1);
                    } else {
                        navLinks.forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                        switchView('view-overview');
                        loadOverviewData();
                    }
                });
            });

            loadOverviewData();
        });
    </script>
</x-app-layout>
