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
        .btn-primary-custom { background: #6366F1; color: white; border: none; transition: 0.3s; }
        .btn-primary-custom:hover { background: #4F46E5; color: white; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); transform: translateY(-2px); }

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

        /* Swal Overrides for consistency */
        .swal2-confirm { background-color: #6366F1 !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important; border-radius: 12px !important; font-weight: 700 !important; }
        .swal2-cancel { border-radius: 12px !important; font-weight: 700 !important; }
        .swal2-popup { border-radius: 24px !important; }
    </style>

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" id="current-view-title" style="font-size: 1.4rem;">Manager Console</h2>
                <div class="text-muted fw-bold" id="current-view-subtitle" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">
                    Ringkasan Operasional Cabang: {{ $metrics['branch_name'] ?? 'Pusat' }}
                </div>
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
                        <div class="p-3 bg-danger-light rounded-4 text-danger" style="background: #FEF2F2;"><i class="bi bi-alarm-fill fs-4"></i></div>
                        <div><div class="small text-muted fw-bold">OVERDUE</div><div class="h4 fw-bold mb-0" id="metric-errors">{{ number_format($metrics['shipments_overdue'] ?? 0) }}</div></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Tren Operasional Cabang (14 Hari)</h6>
                        <div class="chart-container">
                            <canvas id="chart-main-trend"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-pro p-4">
                        <h6 class="fw-bold text-dark mb-4">Status Pengiriman Cabang</h6>
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

        {{-- CRUD Sections --}}
        <div id="view-approvals" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Pusat Persetujuan</h4>
                    <small class="text-muted fw-bold text-uppercase">Approval History & Logs</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="filter-pill-container">
                        <button class="filter-pill active" onclick="setApprovalFilter('status', 'all', this)">All</button>
                        <button class="filter-pill" onclick="setApprovalFilter('status', 'pending', this)">Pending</button>
                        <button class="filter-pill" onclick="setApprovalFilter('status', 'completed', this)">Approved</button>
                    </div>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-approvals"><thead id="thead-view-approvals"></thead><tbody id="body-view-approvals"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-approvals-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-approvals')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-approvals" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        <div id="view-shipments" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Logistik & Pengiriman</h4>
                    <small class="text-muted fw-bold text-uppercase">Data Shipment Cabang</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <input type="text" id="shipment-search" class="form-control form-control-sm border-0 shadow-sm rounded-pill px-3" placeholder="Tracking # or Name" style="background:#F1F5F9; font-weight:700; width:220px;" onkeyup="debounceLoad('view-shipments')">
                    <select id="filter-shipment-status" class="form-select form-select-sm border-0 shadow-sm rounded-pill" style="background:#F1F5F9; font-weight:700; width:150px;" onchange="window.handleFilterChange('view-shipments')">
                        <option value="">All Status</option>
                    </select>
                    <button class="btn btn-primary-custom rounded-pill fw-bold shadow-sm px-4" onclick="handleAddShipment()">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Shipment
                    </button>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-shipments"><thead id="thead-view-shipments"></thead><tbody id="body-view-shipments"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-shipments-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-shipments')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-shipments" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        <div id="view-payments" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Transaksi Keuangan</h4>
                    <small class="text-muted fw-bold text-uppercase">Monitoring Settlement Cabang</small>
                </div>
                <button class="btn btn-primary-custom rounded-pill fw-bold shadow-sm px-4" onclick="handleAddPayment()">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Payment
                </button>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-payments"><thead id="thead-view-payments"></thead><tbody id="body-view-payments"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-payments-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-payments')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-payments" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        <div id="view-rate-cards" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Konfigurasi Biaya</h4>
                    <small class="text-muted fw-bold text-uppercase">Rate Card Management (Admin Approval required)</small>
                </div>
                <button class="btn btn-primary-custom rounded-pill fw-bold shadow-sm px-4" onclick="handleAddRateCard()">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Rate Card
                </button>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-rate-cards"><thead id="thead-view-rate-cards"></thead><tbody id="body-view-rate-cards"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-rate-cards-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-rate-cards')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-rate-cards" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>


        <div id="view-users" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Manajemen Staff</h4>
                    <small class="text-muted fw-bold text-uppercase">Akses & Tim Cabang</small>
                </div>
                <button class="btn btn-primary-custom rounded-pill px-4 fw-bold shadow-sm" onclick="handleAddUser()"><i class="bi bi-person-plus-fill me-2"></i>Tambah Staff</button>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-users"><thead id="thead-view-users"></thead><tbody id="body-view-users"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-users-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-users')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-users" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        <div id="view-vehicles" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Monitoring Armada</h4>
                    <small class="text-muted fw-bold text-uppercase">Data Kendaraan Cabang</small>
                </div>
                <div class="d-flex gap-3">
                    <input type="text" id="vehicle-search" class="form-control form-control-sm border-0 shadow-sm rounded-pill px-3" placeholder="Cari nama atau plat..." style="background:#F1F5F9; font-weight:700; width:220px;" onkeyup="debounceLoad('view-vehicles')">
                    <button class="btn btn-primary-custom rounded-pill px-4 fw-bold shadow-sm" onclick="handleAddVehicle()"><i class="bi bi-plus-lg me-2"></i>Tambah Armada</button>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-vehicles"><thead id="thead-view-vehicles"></thead><tbody id="body-view-vehicles"></tbody></table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-vehicles-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-vehicles')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-vehicles" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        <div id="view-reports" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Laporan Strategis</h4>
                    <small class="text-muted fw-bold text-uppercase">Data Intelligence Cabang</small>
                </div>
            </div>
            <div class="row g-4">
                @foreach([
                    ['route' => 'reports.summary', 'icon' => 'bi-file-earmark-bar-graph-fill', 'color' => '#4F46E5', 'bg' => '#EEF2FF', 'title' => 'Ringkasan Ops'],
                    ['route' => 'reports.daily-reconciliation', 'icon' => 'bi-clipboard2-data-fill', 'color' => '#059669', 'bg' => '#ECFDF5', 'title' => 'Rekonsiliasi Harian'],
                    ['route' => 'reports.branch-performance', 'icon' => 'bi-building-up', 'color' => '#0061FF', 'bg' => '#EBF3FF', 'title' => 'Kinerja Cabang'],
                ] as $report)
                <div class="col-md-4">
                    <a href="{{ route($report['route']) }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:{{ $report['bg'] }}; color:{{ $report['color'] }};"><i class="bi {{ $report['icon'] }} fs-4"></i></div>
                        <div class="fw-bold">{{ $report['title'] }}</div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const state = {
            approval: { status: 'all', limit: 10 },
            branches: [],
            statuses: [],
            couriers: []
        };

        const getStatusClass = (code) => {
            return 'status-pill status-' + code;
        };

        const renderStatusPill = (code, name) => {
            const cls = getStatusClass(code.toLowerCase());
            return `<span class="${cls}"><i class="bi bi-circle-fill"></i>${escapeHtml(name)}</span>`;
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
                    'view-overview': ['Manager Console', 'Ringkasan Operasional Cabang'],
                    'view-approvals': ['Pusat Persetujuan', 'Approval History & Logs'],
                    'view-shipments': ['Logistik & Pengiriman', 'Data Shipment Cabang'],
                    'view-rate-cards': ['Konfigurasi Biaya', 'Rate Card Management'],
                    'view-users': ['Manajemen Staff', 'Akses & Tim Cabang'],
                    'view-vehicles': ['Monitoring Armada', 'Data Kendaraan Cabang'],
                    'view-reports': ['Laporan Strategis', 'Data Intelligence Cabang']
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

                    document.getElementById('recent-activity-body').innerHTML = (data.trackings_recent || []).map(t => `
                        <tr>
                            <td class="text-muted small">${escapeHtml(new Date(t.event_at).toLocaleTimeString())}</td>
                            <td><span class="badge bg-primary-light text-primary rounded-pill px-2" style="font-size:0.7rem">${escapeHtml(t.status.name)}</span></td>
                            <td><small class="fw-bold">${escapeHtml(t.shipment.tracking_number)}</small></td>
                        </tr>
                    `).join('') || '<tr><td colspan="3" class="text-center py-3">Belum ada aktivitas.</td></tr>';

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

                    // Fetch essential data for forms
                    const [bRes, sRes, cRes] = await Promise.all([
                        axios.get('/branches?per_page=100'),
                        axios.get('/shipment-statuses?per_page=100'),
                        axios.get('/users?role=courier&per_page=100')
                    ]);
                    state.branches = bRes.data.data || [];
                    state.statuses = sRes.data.data || [];
                    state.couriers = cRes.data.data || [];

                    renderCharts(data);
                } catch (error) { console.error('Init Error:', error); }
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
                            backgroundColor: ['#0061FF', '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6B778C'],
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
                            thead.innerHTML = '<tr><th>Tracking #</th><th>Sender</th><th>Recipient</th><th>Total</th><th>Status</th><th class="text-end">Action</th></tr>';
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
                                    <td>
                                        ${renderStatusPill(item.status.code, item.status.name)}
                                    </td>
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
                                            <button class="btn-action-sm btn-delete" title="Hapus Shipment" onclick="handleDeleteShipment(${item.id})">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="6" class="text-center py-5">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;

                        case 'view-approvals':
                            const aStatus = state.approval.status;
                            const aLimit = document.getElementById('sel-view-approvals-limit')?.value || 10;
                            endpoint = `/approvals?status=${aStatus === 'all' ? 'all' : aStatus}&per_page=${aLimit}&page=${page}`;
                            thead.innerHTML = '<tr><th>Type</th><th>Task Detail</th><th>Creator</th><th>Priority</th><th>Status</th><th class="text-end">Action</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = response.data.data.data.map(item => `
                                <tr>
                                    <td><span class="badge bg-light text-dark border-0 text-uppercase" style="font-size:0.6rem;">${escapeHtml(item.task_type.replace(/_/g, ' '))}</span></td>
                                    <td><div class="fw-bold text-dark">${escapeHtml(item.title)}</div><small class="text-muted">${escapeHtml(item.description || '')}</small></td>
                                    <td><div class="small fw-bold">${escapeHtml(item.creator?.name || 'System')}</div></td>
                                    <td><span class="fw-bold ${item.priority === 'high' ? 'text-danger' : 'text-primary'}" style="font-size:0.75rem;"><i class="bi ${item.priority === 'high' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'} me-1"></i>${escapeHtml(item.priority.toUpperCase())}</span></td>
                                    <td>
                                        <span class="status-pill ${item.status === 'pending' ? 'bg-warning-light text-warning' : 'bg-success-light text-success'}">
                                            <i class="bi bi-circle-fill"></i>${escapeHtml(item.status.toUpperCase())}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        ${item.status === 'pending' && !['approve_new_rate_card', 'approve_rate_card'].includes(item.task_type) && item.created_by !== {{ auth()->id() }} ? `
                                            <button class="btn-action-sm btn-edit shadow-sm" onclick="handleApproveTask(${item.id})"><i class="bi bi-check-lg text-success"></i></button>
                                            <button class="btn-action-sm btn-delete shadow-sm" onclick="handleRejectTask(${item.id})"><i class="bi bi-x-lg"></i></button>
                                        ` : `<span class="text-muted small">${item.status === 'pending' ? 'Waiting Admin' : 'Processed'}</span>`}
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="6" class="text-center py-4">No data found.</td></tr>';
                            renderPagination(pagination, response.data.data, viewId);
                            break;

                        case 'view-payments':
                            const pLimit = document.getElementById('sel-view-payments-limit')?.value || 10;
                            endpoint = `/payments?per_page=${pLimit}&page=${page}`;
                            thead.innerHTML = '<tr><th>ID</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th><th class="text-end">Action</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = response.data.data.map(item => `
                                <tr>
                                    <td><div class="fw-bold text-dark">#${item.id}</div></td>
                                    <td>
                                        <div class="small fw-bold text-uppercase text-primary">${escapeHtml(item.method)}</div>
                                        <div class="text-muted" style="font-size: 0.65rem;">${escapeHtml(item.shipment?.tracking_number || 'N/A')}</div>
                                    </td>
                                    <td><div class="fw-bold text-dark">Rp${new Intl.NumberFormat('id-ID').format(item.amount)}</div></td>
                                    <td>
                                        <span class="status-pill ${item.status === 'settlement' || item.status === 'paid' ? 'bg-success-light text-success' : (['failed', 'cancel', 'expire'].includes(item.status) ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning')}">
                                            <i class="bi bi-circle-fill"></i>${escapeHtml(item.status.toUpperCase())}
                                        </span>
                                    </td>
                                    <td><div class="small">${new Date(item.created_at).toLocaleDateString()}</div></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            ${['failed', 'expire', 'cancel'].includes(item.status) ? `
                                                <button class="btn-action-sm btn-history" title="Retry Payment" onclick="handleRetryPayment(${item.shipment_id})">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            ` : ''}
                                            <button class="btn-action-sm btn-edit shadow-sm" onclick="handleEditPayment(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn-action-sm btn-delete shadow-sm" onclick="handleDeletePayment(${item.id})"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="6" class="text-center py-4">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;
                        
                        case 'view-rate-cards':
                            const rcLimit = document.getElementById('sel-view-rate-cards-limit')?.value || 10;
                            endpoint = `/rate-cards?per_page=${rcLimit}&page=${page}`;
                            thead.innerHTML = '<tr><th>Rute</th><th>Layanan</th><th>Base Price</th><th>Per Kg</th><th>Est. Days</th><th class="text-end">Action</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = response.data.data.map(item => `
                                <tr>
                                    <td><div class="small fw-bold">${escapeHtml(item.origin_branch?.name)} → ${escapeHtml(item.destination_branch?.name)}</div></td>
                                    <td><span class="badge bg-primary-light text-primary">${escapeHtml(item.service_type.toUpperCase())}</span></td>
                                    <td>Rp${new Intl.NumberFormat('id-ID').format(item.base_price)}</td>
                                    <td>Rp${new Intl.NumberFormat('id-ID').format(item.per_kg_price)}</td>
                                    <td><span class="small fw-bold text-muted">${escapeHtml(item.estimated_days || '-')}</span></td>
                                    <td class="text-end">
                                        <button class="btn-action-sm btn-edit shadow-sm" onclick="handleEditRateCard(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn-action-sm btn-delete shadow-sm" onclick="handleDeleteRateCard(${item.id})"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="5" class="text-center py-4">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;


                        case 'view-users':
                            const uLimit = document.getElementById('sel-view-users-limit')?.value || 10;
                            endpoint = `/users?per_page=${uLimit}&page=${page}`;
                            thead.innerHTML = '<tr><th>Name & Email</th><th>Role</th><th>Status</th><th class="text-end">Action</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = (response.data.data.data || response.data.data).filter(u => u.role !== 'admin').map(item => `
                                <tr>
                                    <td><div class="fw-bold text-dark">${escapeHtml(item.name)}</div><small class="text-muted">${escapeHtml(item.email)}</small></td>
                                    <td><span class="badge bg-primary-light text-primary text-uppercase" style="font-size:0.65rem;">${escapeHtml(item.role)}</span></td>
                                    <td>
                                        <span class="status-pill ${item.is_active ? 'bg-success-light text-success' : 'bg-light text-muted'}">
                                            <i class="bi bi-circle-fill"></i>${item.is_active ? 'AKTIF' : 'NONAKTIF'}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn-action-sm btn-edit shadow-sm" onclick="handleEditUser(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="4" class="text-center py-4">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;

                        case 'view-vehicles':
                            const vSearch = document.getElementById('vehicle-search').value;
                            const vLimit = document.getElementById('sel-view-vehicles-limit')?.value || 10;
                            endpoint = `/vehicles?page=${page}&search=${vSearch}&per_page=${vLimit}`;
                            thead.innerHTML = '<tr><th>Armada</th><th>Plat</th><th>Type</th><th>Status</th><th class="text-end">Action</th></tr>';
                            response = await axios.get(endpoint);
                            tbody.innerHTML = response.data.data.map(item => `
                                <tr>
                                    <td><div class="fw-bold text-dark">${escapeHtml(item.name)}</div></td>
                                    <td><span class="badge bg-light text-dark border-0">${escapeHtml(item.plate_number)}</span></td>
                                    <td><div class="small text-uppercase fw-bold">${escapeHtml(item.type)}</div></td>
                                    <td>
                                        <span class="status-pill ${item.status === 'available' ? 'bg-success-light text-success' : (item.status === 'in_use' ? 'bg-primary-light text-primary' : 'bg-warning-light text-warning')}">
                                            <i class="bi bi-circle-fill"></i>${escapeHtml(item.status.toUpperCase())}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn-action-sm btn-edit shadow-sm" onclick="handleEditVehicle(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn-action-sm btn-delete shadow-sm" onclick="handleDeleteVehicle(${item.id})"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>`).join('') || '<tr><td colspan="5" class="text-center py-4">No data found.</td></tr>';
                            renderPagination(pagination, response.data, viewId);
                            break;
                    }
                } catch (e) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error loading data.</td></tr>'; }
            };

            // Sidebar Integration
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    let targetKey = 'view-overview';
                    if (href.includes('approvals')) targetKey = 'view-approvals';
                    else if (href.includes('shipments')) targetKey = 'view-shipments';
                    else if (href.includes('payments')) targetKey = 'view-payments';
                    else if (href.includes('rate-cards')) targetKey = 'view-rate-cards';
                    else if (href.includes('users')) targetKey = 'view-users';
                    else if (href.includes('vehicles')) targetKey = 'view-vehicles';
                    else if (href.includes('reports')) targetKey = 'view-reports';
                    
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

        // Approval Handlers
        async function handleApproveTask(id) {
            const { value: note } = await Swal.fire({ 
                title: 'Setujui Request?', 
                input: 'text', 
                showCancelButton: true, 
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Setujui',
                customClass: { popup: 'rounded-4' }
            });
            if (note !== undefined) {
                try { 
                    await axios.post(`/approvals/tasks/${id}/approve`, { note }); 
                    Swal.fire({ title: 'Success', text: 'Request disetujui.', icon: 'success', confirmButtonColor: '#6366F1' }).then(() => loadViewData('view-approvals')); 
                }
                catch (e) { Swal.fire('Error', 'Gagal memproses.', 'error'); }
            }
        }

        async function handleRejectTask(id) {
            const { value: reason } = await Swal.fire({ title: 'Tolak Request?', input: 'text', showCancelButton: true, confirmButtonColor: '#DC2626' });
            if (reason) {
                try { await axios.post(`/approvals/tasks/${id}/reject`, { reason }); Swal.fire('Success', 'Request ditolak.', 'success').then(() => loadViewData('view-approvals')); }
                catch (e) { Swal.fire('Error', 'Gagal memproses.', 'error'); }
            }
        }

        async function handleAddRateCard() {
            const userBranchId = {{ auth()->user()->branch_id }};
            const { value: formValues } = await Swal.fire({
                title: 'Tambah Rate Card Baru',
                html: `
                    <div class="text-start">
                        <label class="small fw-bold mb-1">Ke Cabang Tujuan</label>
                        <select id="swal-dest" class="form-select mb-3">
                            ${state.branches.filter(b => b.id !== userBranchId).map(b => `<option value="${b.id}">${b.name} (${b.city})</option>`).join('')}
                        </select>
                        <label class="small fw-bold mb-1">Layanan</label>
                        <select id="swal-service" class="form-select mb-3">
                            <option value="regular">REGULAR</option>
                            <option value="express">EXPRESS</option>
                            <option value="same_day">SAME DAY</option>
                            <option value="economy">ECONOMY</option>
                        </select>
                        <div class="row">
                            <div class="col-6">
                                <label class="small fw-bold mb-1">Base Price (Rp)</label>
                                <input id="swal-base" class="form-control mb-3" type="number" value="10000">
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold mb-1">Price Per Kg (Rp)</label>
                                <input id="swal-kg" class="form-control mb-3" type="number" value="5000">
                            </div>
                        </div>
                        <label class="small fw-bold mb-1">Estimasi Hari</label>
                        <input id="swal-est" class="form-control mb-3" type="text" placeholder="Contoh: 2-4 hari">
                        <label class="small fw-bold mb-1">Alasan Pengajuan</label>
                        <textarea id="swal-reason" class="form-control" placeholder="Contoh: Penyesuaian biaya operasional..."></textarea>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Ajukan ke Admin',
                confirmButtonColor: '#6366F1',
                customClass: { popup: 'rounded-4' },
                preConfirm: () => {
                    return {
                        origin_branch_id: userBranchId,
                        destination_branch_id: document.getElementById('swal-dest').value,
                        service_type: document.getElementById('swal-service').value,
                        base_price: document.getElementById('swal-base').value,
                        per_kg_price: document.getElementById('swal-kg').value,
                        estimated_days: document.getElementById('swal-est').value,
                        min_weight_kg: 0,
                        reason: document.getElementById('swal-reason').value
                    }
                }
            });

            if (formValues) {
                if (!formValues.reason) return Swal.fire('Error', 'Alasan pengajuan wajib diisi.', 'error');
                try {
                    await axios.post('/rate-cards', formValues);
                    Swal.fire('Berhasil', 'Pengajuan rate card baru telah dikirim ke Admin.', 'success');
                } catch (e) {
                    Swal.fire('Error', e.response?.data?.message || 'Gagal mengirim pengajuan.', 'error');
                }
            }
        }

        async function handleEditRateCard(id) {
            try {
                const { data: rc } = await axios.get(`/rate-cards/${id}`);
                const { value: formValues } = await Swal.fire({
                    title: 'Edit Rate Card',
                    html: `
                        <div class="text-start">
                            <p class="small text-muted mb-3">Rute: <b>${rc.origin_branch.name} → ${rc.destination_branch.name}</b></p>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small fw-bold mb-1">Base Price (Rp)</label>
                                    <input id="swal-base" class="form-control mb-3" type="number" value="${rc.base_price}">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold mb-1">Price Per Kg (Rp)</label>
                                    <input id="swal-kg" class="form-control mb-3" type="number" value="${rc.per_kg_price}">
                                </div>
                            </div>
                            <label class="small fw-bold mb-1">Estimasi Hari</label>
                            <input id="swal-est" class="form-control mb-3" type="text" value="${rc.estimated_days || ''}" placeholder="Contoh: 2-4 hari">
                            <label class="small fw-bold mb-1">Alasan Perubahan</label>
                            <textarea id="swal-reason" class="form-control" placeholder="Alasan perubahan..."></textarea>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Ajukan Perubahan',
                    preConfirm: () => {
                        return {
                            base_price: document.getElementById('swal-base').value,
                            per_kg_price: document.getElementById('swal-kg').value,
                            estimated_days: document.getElementById('swal-est').value,
                            reason: document.getElementById('swal-reason').value
                        }
                    }
                });

                if (formValues) {
                    if (!formValues.reason) return Swal.fire('Error', 'Alasan perubahan wajib diisi.', 'error');
                    await axios.put(`/rate-cards/${id}`, formValues);
                    Swal.fire('Berhasil', 'Permintaan perubahan rate card telah dikirim ke Admin.', 'success');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal memuat data atau mengirim pengajuan.', 'error');
            }
        }

        async function handleDeleteRateCard(id) {
            const { value: reason } = await Swal.fire({
                title: 'Hapus Rate Card?',
                text: "Penghapusan ini memerlukan approval admin.",
                input: 'textarea',
                inputPlaceholder: 'Berikan alasan penghapusan...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: 'Ajukan Hapus'
            });

            if (reason) {
                try {
                    await axios.delete(`/rate-cards/${id}`, { data: { reason } });
                    Swal.fire('Berhasil', 'Pengajuan penghapusan telah dikirim ke admin.', 'success').then(() => loadViewData('view-rate-cards'));
                } catch (e) {
                    Swal.fire('Error', e.response?.data?.message || 'Gagal mengirim pengajuan.', 'error');
                }
            }
        }

        async function handleAddUser() {
            const { value: formValues } = await Swal.fire({
                title: 'Tambah Staff Baru',
                html: `
                    <div class="text-start">
                        <label class="small fw-bold mb-1">Nama Lengkap</label>
                        <input id="swal-name" class="form-control mb-3" type="text">
                        <label class="small fw-bold mb-1">Email</label>
                        <input id="swal-email" class="form-control mb-3" type="email">
                        <label class="small fw-bold mb-1">Phone</label>
                        <input id="swal-phone" class="form-control mb-3" type="text">
                        <label class="small fw-bold mb-1">Role</label>
                        <select id="swal-role" class="form-select mb-3">
                            <option value="kasir">KASIR</option>
                            <option value="courier">COURIER</option>
                            <option value="manager">MANAGER</option>
                        </select>
                        <label class="small fw-bold mb-1">Password</label>
                        <input id="swal-pass" class="form-control mb-3" type="password">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan Staff',
                preConfirm: () => {
                    return {
                        name: document.getElementById('swal-name').value,
                        email: document.getElementById('swal-email').value,
                        phone: document.getElementById('swal-phone').value,
                        role: document.getElementById('swal-role').value,
                        password: document.getElementById('swal-pass').value,
                        is_active: true
                    }
                }
            });

            if (formValues) {
                try {
                    await axios.post('/users', formValues);
                    Swal.fire('Berhasil', 'Staff baru telah ditambahkan.', 'success').then(() => loadViewData('view-users'));
                } catch (e) {
                    Swal.fire('Error', e.response?.data?.message || 'Gagal menambahkan staff.', 'error');
                }
            }
        }

        async function handleEditUser(id) {
            try {
                const { data: user } = await axios.get(`/users/${id}`);
                const { value: formValues } = await Swal.fire({
                    title: 'Edit Data Staff',
                    html: `
                        <div class="text-start">
                            <label class="small fw-bold mb-1">Nama Lengkap</label>
                            <input id="swal-name" class="form-control mb-3" type="text" value="${escapeHtml(user.name)}">
                            <label class="small fw-bold mb-1">Phone</label>
                            <input id="swal-phone" class="form-control mb-3" type="text" value="${escapeHtml(user.phone)}">
                            <label class="small fw-bold mb-1">Role</label>
                            <select id="swal-role" class="form-select mb-3">
                                <option value="kasir" ${user.role === 'kasir' ? 'selected' : ''}>KASIR</option>
                                <option value="courier" ${user.role === 'courier' ? 'selected' : ''}>COURIER</option>
                                <option value="manager" ${user.role === 'manager' ? 'selected' : ''}>MANAGER</option>
                            </select>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="swal-active" ${user.is_active ? 'checked' : ''}>
                                <label class="form-check-label small fw-bold" for="swal-active">Akun Aktif</label>
                            </div>
                            <label class="small fw-bold mb-1">Password (Kosongkan jika tidak ganti)</label>
                            <input id="swal-pass" class="form-control mb-3" type="password">
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update Staff',
                    preConfirm: () => {
                        return {
                            name: document.getElementById('swal-name').value,
                            phone: document.getElementById('swal-phone').value,
                            role: document.getElementById('swal-role').value,
                            is_active: document.getElementById('swal-active').checked,
                            password: document.getElementById('swal-pass').value
                        }
                    }
                });

                if (formValues) {
                    await axios.put(`/users/${id}`, formValues);
                    Swal.fire('Berhasil', 'Data staff diperbarui.', 'success').then(() => loadViewData('view-users'));
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal memproses data.', 'error');
            }
        }
        async function handleAddVehicle() {
            const userBranchId = {{ auth()->user()->branch_id }};
            const { value: formValues } = await Swal.fire({
                title: 'Tambah Armada Baru',
                html: `
                    <div class="text-start">
                        <label class="small fw-bold mb-1">Nama Kendaraan</label>
                        <input id="swal-name" class="form-control mb-3" type="text" placeholder="Contoh: Grand Max Box">
                        <label class="small fw-bold mb-1">Nomor Plat</label>
                        <input id="swal-plate" class="form-control mb-3" type="text" placeholder="B 1234 ABC">
                        <label class="small fw-bold mb-1">Tipe</label>
                        <select id="swal-type" class="form-select mb-3">
                            <option value="motorcycle">Motorcycle</option>
                            <option value="car">Car</option>
                            <option value="van">Van</option>
                            <option value="truck">Truck</option>
                        </select>
                        <label class="small fw-bold mb-1">Status Awal</label>
                        <select id="swal-status" class="form-select mb-3">
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan Armada',
                preConfirm: () => {
                    return {
                        branch_id: userBranchId,
                        name: document.getElementById('swal-name').value,
                        plate_number: document.getElementById('swal-plate').value,
                        type: document.getElementById('swal-type').value,
                        status: document.getElementById('swal-status').value
                    }
                }
            });

            if (formValues) {
                try {
                    await axios.post('/vehicles', formValues);
                    Swal.fire('Berhasil', 'Armada baru telah ditambahkan.', 'success').then(() => loadViewData('view-vehicles'));
                } catch (e) {
                    Swal.fire('Error', e.response?.data?.message || 'Gagal menambahkan armada.', 'error');
                }
            }
        }

        async function handleEditVehicle(id) {
            try {
                const { data: v } = await axios.get(`/vehicles/${id}`);
                const { value: formValues } = await Swal.fire({
                    title: 'Edit Data Armada',
                    html: `
                        <div class="text-start">
                            <label class="small fw-bold mb-1">Nama Kendaraan</label>
                            <input id="swal-name" class="form-control mb-3" type="text" value="${escapeHtml(v.name)}">
                            <label class="small fw-bold mb-1">Nomor Plat</label>
                            <input id="swal-plate" class="form-control mb-3" type="text" value="${escapeHtml(v.plate_number)}">
                            <label class="small fw-bold mb-1">Tipe</label>
                            <select id="swal-type" class="form-select mb-3">
                                <option value="motorcycle" ${v.type === 'motorcycle' ? 'selected' : ''}>Motorcycle</option>
                                <option value="car" ${v.type === 'car' ? 'selected' : ''}>Car</option>
                                <option value="van" ${v.type === 'van' ? 'selected' : ''}>Van</option>
                                <option value="truck" ${v.type === 'truck' ? 'selected' : ''}>Truck</option>
                            </select>
                            <label class="small fw-bold mb-1">Status</label>
                            <select id="swal-status" class="form-select mb-3">
                                <option value="available" ${v.status === 'available' ? 'selected' : ''}>Available</option>
                                <option value="in_use" ${v.status === 'in_use' ? 'selected' : ''}>In Use</option>
                                <option value="maintenance" ${v.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                <option value="inactive" ${v.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update Armada',
                    preConfirm: () => {
                        return {
                            name: document.getElementById('swal-name').value,
                            plate_number: document.getElementById('swal-plate').value,
                            type: document.getElementById('swal-type').value,
                            status: document.getElementById('swal-status').value
                        }
                    }
                });

                if (formValues) {
                    await axios.put(`/vehicles/${id}`, formValues);
                    Swal.fire('Berhasil', 'Data armada diperbarui.', 'success').then(() => loadViewData('view-vehicles'));
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal memuat data.', 'error');
            }
        }

        async function handleDeleteVehicle(id) {
            const { isConfirmed } = await Swal.fire({
                title: 'Hapus Armada?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (isConfirmed) {
                try {
                    await axios.delete(`/vehicles/${id}`);
                    Swal.fire('Terhapus', 'Armada telah dihapus.', 'success').then(() => loadViewData('view-vehicles'));
                } catch (e) {
                    Swal.fire('Error', 'Gagal menghapus armada.', 'error');
                }
            }
        }

        async function handleAddShipment() {
            const userBranchId = {{ auth()->user()->branch_id }};
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
                    Swal.fire('Berhasil', 'Shipment baru ditambahkan.', 'success').then(() => loadViewData('view-shipments'));
                } catch (e) {
                    Swal.fire('Error', e.response?.data?.message || 'Gagal menambahkan shipment.', 'error');
                }
            }
        }

        async function handleEditShipment(id) {
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
                                <input id="swal-location" class="form-control form-control-sm mb-1" placeholder="Lokasi saat ini (cth: Cabang Bandung)" value="${escapeHtml(s.branch?.name || '')}">
                                <input id="swal-tracking-notes" class="form-control form-control-sm" placeholder="Catatan tracking (cth: Paket keluar dari gudang)" value="Status diperbarui oleh Manager">
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
                            manual_override_reason: document.getElementById('swal-notes').value
                        }
                    }
                });

                if (formValues) {
                    await axios.put(`/shipments/${id}`, formValues);
                    Swal.fire('Berhasil', 'Shipment diperbarui.', 'success').then(() => loadViewData('view-shipments'));
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal memproses data.', 'error');
            }
        }

        async function handleDeleteShipment(id) {
            const { isConfirmed } = await Swal.fire({
                title: 'Hapus Shipment?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (isConfirmed) {
                try {
                    await axios.delete(`/shipments/${id}`);
                    Swal.fire('Terhapus', 'Shipment telah dihapus.', 'success').then(() => loadViewData('view-shipments'));
                } catch (e) {
                    Swal.fire('Error', 'Gagal menghapus shipment.', 'error');
                }
            }
        }

        async function handleAddPayment() {
            const { value: formValues } = await Swal.fire({
                title: 'Tambah Payment Baru',
                html: `
                    <div class="text-start">
                        <label class="small fw-bold mb-1">ID Shipment</label>
                        <input id="swal-ship-id" class="form-control mb-3" type="number" placeholder="Contoh: 12">
                        <label class="small fw-bold mb-1">Metode</label>
                        <select id="swal-method" class="form-select mb-3">
                            <option value="cash">CASH</option>
                            <option value="transfer">TRANSFER</option>
                            <option value="e_wallet">E-WALLET</option>
                        </select>
                        <label class="small fw-bold mb-1">Jumlah (Rp)</label>
                        <input id="swal-amount" class="form-control mb-3" type="number">
                        <label class="small fw-bold mb-1">Notes</label>
                        <input id="swal-notes" class="form-control mb-3">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan Payment',
                preConfirm: () => {
                    return {
                        shipment_id: document.getElementById('swal-ship-id').value,
                        method: document.getElementById('swal-method').value,
                        amount: document.getElementById('swal-amount').value,
                        notes: document.getElementById('swal-notes').value
                    }
                }
            });

            if (formValues) {
                try {
                    await axios.post('/payments', formValues);
                    Swal.fire('Berhasil', 'Payment baru ditambahkan.', 'success').then(() => loadViewData('view-payments'));
                } catch (e) {
                    Swal.fire('Error', e.response?.data?.message || 'Gagal menambahkan payment.', 'error');
                }
            }
        }

        async function handleEditPayment(id) {
            try {
                const { data: p } = await axios.get(`/payments/${id}`);
                const { value: formValues } = await Swal.fire({
                    title: 'Edit Payment',
                    html: `
                        <div class="text-start">
                            <label class="small fw-bold mb-1">Status</label>
                            <select id="swal-status" class="form-select mb-3">
                                <option value="pending" ${p.status === 'pending' ? 'selected' : ''}>PENDING</option>
                                <option value="settlement" ${p.status === 'settlement' ? 'selected' : ''}>SETTLEMENT (SENSITIVE)</option>
                                <option value="failed" ${p.status === 'failed' ? 'selected' : ''}>FAILED</option>
                                <option value="cancel" ${p.status === 'cancel' ? 'selected' : ''}>CANCEL</option>
                                <option value="refund" ${p.status === 'refund' ? 'selected' : ''}>REFUND (SENSITIVE)</option>
                            </select>
                            <label class="small fw-bold mb-1">Metode</label>
                            <select id="swal-method" class="form-select mb-3">
                                <option value="cash" ${p.method === 'cash' ? 'selected' : ''}>CASH</option>
                                <option value="transfer" ${p.method === 'transfer' ? 'selected' : ''}>TRANSFER</option>
                                <option value="e_wallet" ${p.method === 'e_wallet' ? 'selected' : ''}>E-WALLET</option>
                                <option value="midtrans" ${p.method === 'midtrans' ? 'selected' : ''}>MIDTRANS</option>
                            </select>
                            <label class="small fw-bold mb-1">Jumlah (Rp)</label>
                            <input id="swal-amount" type="number" class="form-control mb-3" value="${p.amount || 0}">
                            <label class="small fw-bold mb-1">Notes / Alasan</label>
                            <input id="swal-notes" class="form-control mb-3" value="${escapeHtml(p.notes || '')}">
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update Payment',
                    preConfirm: () => {
                        return {
                            status: document.getElementById('swal-status').value,
                            method: document.getElementById('swal-method').value,
                            amount: document.getElementById('swal-amount').value,
                            notes: document.getElementById('swal-notes').value,
                            manual_override: true,
                            manual_override_reason: document.getElementById('swal-notes').value
                        }
                    }
                });

                if (formValues) {
                    const res = await axios.put(`/payments/${id}`, formValues);
                    const msg = res.status === 202 ? 'Perubahan sensitif menunggu approval admin/manager.' : 'Payment diperbarui.';
                    Swal.fire({
                        title: 'Berhasil',
                        text: msg,
                        icon: 'success',
                        confirmButtonColor: '#6366F1',
                        customClass: { popup: 'rounded-4' }
                    }).then(() => loadViewData('view-payments'));
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal memproses data.', 'error');
            }
        }

        async function handleDeletePayment(id) {
            const { isConfirmed } = await Swal.fire({
                title: 'Hapus Payment?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (isConfirmed) {
                try {
                    await axios.delete(`/payments/${id}`);
                    Swal.fire('Terhapus', 'Payment telah dihapus.', 'success').then(() => loadViewData('view-payments'));
                } catch (e) {
                    Swal.fire('Error', 'Gagal menghapus payment.', 'error');
                }
            }
        }
        async function handleViewHistory(id) {
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
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'Gagal memuat riwayat history.', 'error');
            }
        }

        async function handleRetryPayment(shipmentId) {
            try {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyiapkan pembayaran ulang',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const { data } = await axios.post(`/payments/midtrans/token/${shipmentId}`);
                
                if (window.snap) {
                    window.snap.pay(data.data.snap_token, {
                        onSuccess: (result) => {
                            Swal.fire('Berhasil', 'Pembayaran berhasil dikonfirmasi.', 'success').then(() => loadViewData('view-payments'));
                        },
                        onPending: (result) => {
                            Swal.fire('Info', 'Pembayaran sedang diproses.', 'info').then(() => loadViewData('view-payments'));
                        },
                        onError: (result) => {
                            Swal.fire('Error', 'Pembayaran gagal.', 'error');
                        }
                    });
                } else {
                    window.open(data.data.snap_redirect_url, '_blank');
                    Swal.fire('Info', 'Popup pembayaran dibuka di tab baru.', 'info').then(() => loadViewData('view-payments'));
                }
            } catch (e) {
                Swal.fire('Error', e.response?.data?.message || 'Gagal menyiapkan pembayaran.', 'error');
            }
        }
    </script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</x-app-layout>
