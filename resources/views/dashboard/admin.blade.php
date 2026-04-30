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
                ['id' => 'view-branches', 'title' => 'Manajemen Jaringan', 'subtitle' => 'Manajemen Cabang'],
                ['id' => 'view-users', 'title' => 'Akses & Keamanan', 'subtitle' => 'User Management'],
                ['id' => 'view-reports', 'title' => 'Data Intelligence', 'subtitle' => 'Laporan Operasional'],
            ];
        @endphp

        {{-- VIEW: APPROVALS --}}
        <div id="view-approvals" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Pusat Persetujuan</h4>
                    <small class="text-muted fw-bold text-uppercase">Approval History & Logs</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="filter-pill-container" id="approval-status-filters">
                        <button class="filter-pill active" onclick="setApprovalFilter('status', 'all', this)">All</button>
                        <button class="filter-pill" onclick="setApprovalFilter('status', 'pending', this)">Pending</button>
                        <button class="filter-pill" onclick="setApprovalFilter('status', 'completed', this)">Approved</button>
                        <button class="filter-pill" onclick="setApprovalFilter('status', 'cancelled', this)">Rejected</button>
                    </div>
                    <select id="sel-app-scope" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700;" onchange="window.handleFilterChange('view-approvals')">
                        <option value="all">All Types</option>
                        <option value="approve_rate_card">Rate Card</option>
                        <option value="shipment_final_status_approval">Final Status</option>
                        <option value="shipment_reassign_approval">Reassign</option>
                    </select>
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

        {{-- VIEW: SHIPMENTS --}}
        <div id="view-shipments" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Logistik & Pengiriman</h4>
                    <small class="text-muted fw-bold text-uppercase">Data Seluruh Shipment Nasional</small>
                </div>
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-0 text-muted small fw-bold">SEARCH</span>
                        <input type="text" id="shipment-search" class="form-control rounded-end-pill px-3 shadow-sm border-0" placeholder="Tracking # or Name" style="background:#F1F5F9; font-weight:700;" onkeyup="debounceLoad('view-shipments')">
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-0 text-muted small fw-bold">STATUS</span>
                        <select id="filter-shipment-status" class="form-select rounded-end-pill px-3 shadow-sm border-0" style="background:#F1F5F9; font-weight:700;" onchange="window.handleFilterChange('view-shipments')">
                            <option value="">All Status</option>
                            {{-- Options populated dynamically via JS --}}
                        </select>
                    </div>
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

        {{-- VIEW: PAYMENTS --}}
        <div id="view-payments" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Transaksi Keuangan</h4>
                    <small class="text-muted fw-bold text-uppercase">Monitoring Transaksi & Settlement</small>
                </div>
                <div class="input-group input-group-sm" style="width:200px;">
                    <span class="input-group-text bg-white border-0 text-muted small fw-bold">STATUS</span>
                    <select id="filter-payment-status" class="form-select rounded-end-pill px-3 shadow-sm border-0" style="background:#F1F5F9; font-weight:700;" onchange="window.handleFilterChange('view-payments')">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="settlement">Settlement</option>
                        <option value="expire">Expired</option>
                        <option value="cancel">Cancelled</option>
                    </select>
                </div>
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

        {{-- VIEW: RATE CARDS --}}
        <div id="view-rate-cards" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Konfigurasi Biaya</h4>
                    <small class="text-muted fw-bold text-uppercase">Rate Card Management</small>
                </div>
                <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="handleAddData('view-rate-cards')"><i class="bi bi-plus-lg me-2"></i>Tambah Rate Card</button>
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

        {{-- VIEW: BRANCHES --}}
        <div id="view-branches" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Manajemen Jaringan</h4>
                    <small class="text-muted fw-bold text-uppercase">Manajemen Cabang</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <input type="text" id="branch-search" class="form-control form-control-sm border-0 shadow-sm rounded-pill px-3" placeholder="Cari nama atau kota..." style="background:#F1F5F9; font-weight:700; width:220px;" onkeyup="debounceLoad('view-branches')">
                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="handleAddBranch()"><i class="bi bi-plus-lg me-2"></i>Tambah Cabang</button>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-branches">
                        <thead id="thead-view-branches"></thead>
                        <tbody id="body-view-branches"></tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-branches-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-branches')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-branches" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        {{-- VIEW: USERS --}}
        <div id="view-users" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Akses & Keamanan</h4>
                    <small class="text-muted fw-bold text-uppercase">User Management</small>
                </div>
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <input type="text" id="user-search" class="form-control form-control-sm border-0 shadow-sm rounded-pill px-3" placeholder="Cari nama atau email..." style="background:#F1F5F9; font-weight:700; width:220px;" onkeyup="debounceLoad('view-users')">
                    <select id="filter-user-role" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:150px;" onchange="window.handleFilterChange('view-users')">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="kasir">Kasir</option>
                        <option value="courier">Kurir</option>
                        <option value="customer">Customer</option>
                    </select>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="handleAddUser()"><i class="bi bi-person-plus-fill me-2"></i>Tambah User</button>
                </div>
            </div>
            <div class="card-pro p-4">
                <div class="table-responsive">
                    <table class="table-modern" id="table-view-users">
                        <thead id="thead-view-users"></thead>
                        <tbody id="body-view-users"></tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <select id="sel-view-users-limit" class="form-select form-select-sm border-0 shadow-sm rounded-3" style="background:#F1F5F9; font-weight:700; width:80px;" onchange="window.handleFilterChange('view-users')">
                        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
                    </select>
                    <div id="pagination-view-users" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </div>

        {{-- VIEW: REPORTS --}}
        <div id="view-reports" class="view-section">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Data Intelligence</h4>
                    <small class="text-muted fw-bold text-uppercase">Laporan Operasional</small>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.summary') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark" style="display:block;">
                        <div class="p-3 rounded-4" style="background:#EEF2FF; color:#4F46E5;"><i class="bi bi-file-earmark-bar-graph-fill fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">OPERATIONAL</div><div class="fw-bold">Ringkasan Ops</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.daily-reconciliation') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#ECFDF5; color:#059669;"><i class="bi bi-clipboard2-data-fill fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">KEUANGAN</div><div class="fw-bold">Rekonsiliasi Harian</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.branch-performance') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#EBF3FF; color:#0061FF;"><i class="bi bi-building-up fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">CABANG</div><div class="fw-bold">Kinerja Cabang</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.courier-performance') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#FFFBEB; color:#D97706;"><i class="bi bi-person-badge-fill fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">KURIR</div><div class="fw-bold">Performa Kurir</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.payment-overview') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#FEF2F2; color:#DC2626;"><i class="bi bi-credit-card-fill fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">PEMBAYARAN</div><div class="fw-bold">Buku Kas</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.branch-balances') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#F0F9FF; color:#0284C7;"><i class="bi bi-bank fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">SALDO</div><div class="fw-bold">Saldo Cabang</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.courier-earnings') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#F7FEE7; color:#65A30D;"><i class="bi bi-wallet2 fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">PENDAPATAN</div><div class="fw-bold">Komisi Kurir</div></div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('reports.summary.export') }}" class="card-pro p-4 d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <div class="p-3 rounded-4" style="background:#F8FAFC; color:#6B778C;"><i class="bi bi-download fs-4"></i></div>
                        <div><div class="small fw-bold text-muted">EXPORT</div><div class="fw-bold">Download Laporan</div></div>
                    </a>
                </div>
            </div>
        </div>
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
                    'view-branches': ['Manajemen Jaringan', 'Manajemen Cabang'],
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

                            endpoint = `/shipments?page=${page}&search=${sSearch}&per_page=${sLimit}`;
                            if(sStatus) endpoint += `&status_id=${sStatus}`;
                            
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

                        case 'view-rate-cards':
                            const rcLimit = document.getElementById('sel-view-rate-cards-limit')?.value || 10;
                            endpoint = `/rate-cards?per_page=${rcLimit}&page=${page}`;
                            thead.innerHTML = '<tr><th>#</th><th>Rute Cabang</th><th>Layanan</th><th>Base Price</th><th>Per Kg</th><th class="text-end">Aksi</th></tr>';
                            const rcRes = await axios.get(endpoint);
                            const rcPaginated = rcRes.data;
                            meta = rcPaginated;
                            rows = rcPaginated.data.map((item, index) => `
                                <tr>
                                    <td><span class="text-muted small">${(rcPaginated.current_page - 1) * rcPaginated.per_page + (index + 1)}</span></td>
                                    <td>
                                        <div class="small fw-bold text-dark">${item.origin_branch ? item.origin_branch.name : '-'}</div>
                                        <i class="bi bi-arrow-down small text-muted"></i>
                                        <div class="small fw-bold text-dark">${item.destination_branch ? item.destination_branch.name : '-'}</div>
                                    </td>
                                    <td><span class="badge bg-primary-light text-primary border-0 text-uppercase" style="font-size:0.7rem;">${item.service_type}</span></td>
                                    <td class="fw-bold text-dark">Rp${new Intl.NumberFormat('id-ID').format(item.base_price || 0)}</td>
                                    <td class="text-primary fw-bold">Rp${new Intl.NumberFormat('id-ID').format(item.per_kg_price || 0)}</td>
                                    <td class="text-end">
                                        <button class="btn-action-sm btn-edit me-1 shadow-sm" onclick="handleEdit('rate-cards', ${item.id})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn-action-sm btn-delete shadow-sm" onclick="handleDelete('rate-cards', ${item.id})"><i class="bi bi-trash-fill"></i></button>
                                    </td>
                                </tr>
                            `).join('');
                            break;

                        case 'view-branches': {
                            const bSearch = document.getElementById('branch-search')?.value || '';
                            const bLimit = document.getElementById('sel-view-branches-limit')?.value || 10;
                            endpoint = `/branches?per_page=${bLimit}&page=${page}&search=${bSearch}`;
                            thead.innerHTML = '<tr><th>#</th><th>Nama & Kode</th><th>Kota</th><th>Status</th><th class="text-end">Aksi</th></tr>';
                            const bRes = await axios.get(endpoint);
                            meta = bRes.data;
                            rows = bRes.data.data.map((item, i) => `
                                <tr>
                                    <td><span class="text-muted small">${(bRes.data.current_page - 1) * bRes.data.per_page + (i+1)}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">${item.name}</div>
                                        <div class="small text-muted">${item.code || '-'}</div>
                                    </td>
                                    <td class="fw-bold">${item.city || '-'}</td>
                                    <td><span class="status-pill ${item.is_active ? 'bg-success-light text-success' : 'bg-danger-light text-danger'}" style="font-size:0.7rem;">${item.is_active ? 'AKTIF' : 'NONAKTIF'}</span></td>
                                    <td class="text-end">
                                        <button class="btn-action-sm btn-edit me-1 shadow-sm" onclick="handleEditBranch(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn-action-sm btn-delete shadow-sm" onclick="handleToggleBranch(${item.id}, ${item.is_active})"><i class="bi bi-${item.is_active ? 'toggle-on' : 'toggle-off'}"></i></button>
                                    </td>
                                </tr>
                            `).join('');
                            break;
                        }

                        case 'view-users': {
                            const uSearch = document.getElementById('user-search')?.value || '';
                            const uRole = document.getElementById('filter-user-role')?.value || '';
                            const uLimit = document.getElementById('sel-view-users-limit')?.value || 10;
                            endpoint = `/users?per_page=${uLimit}&page=${page}&search=${uSearch}&role=${uRole}`;
                            const roleColors = { admin:'#DC2626', manager:'#4F46E5', kasir:'#059669', courier:'#D97706', customer:'#0061FF' };
                            thead.innerHTML = '<tr><th>#</th><th>Nama & Kontak</th><th>Role</th><th>Cabang</th><th>Status</th><th class="text-end">Aksi</th></tr>';
                            const uRes = await axios.get(endpoint);
                            meta = uRes.data;
                            rows = uRes.data.data.map((item, i) => `
                                <tr>
                                    <td><span class="text-muted small">${(uRes.data.current_page - 1) * uRes.data.per_page + (i+1)}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">${item.name}</div>
                                        <div class="small text-muted">${item.email}</div>
                                    </td>
                                    <td><span class="status-pill text-white fw-bold" style="font-size:0.65rem; background:${roleColors[item.role] || '#6B778C'}">${item.role?.toUpperCase()}</span></td>
                                    <td class="small fw-bold">${item.branch?.name || '-'}</td>
                                    <td><span class="status-pill ${item.is_active ? 'bg-success-light text-success' : 'bg-secondary-light text-muted'}" style="font-size:0.7rem;">${item.is_active ? 'AKTIF' : 'NONAKTIF'}</span></td>
                                    <td class="text-end">
                                        <button class="btn-action-sm btn-edit me-1 shadow-sm" onclick="handleEditUser(${item.id})"><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn-action-sm btn-delete shadow-sm" onclick="handleDelete('users', ${item.id})"><i class="bi bi-trash-fill"></i></button>
                                    </td>
                                </tr>
                            `).join('');
                            break;
                        }

                        default:
                            rows = '<tr><td colspan="7" class="text-center py-4 text-muted">Halaman ini tidak memiliki tabel data.</td></tr>';
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

            window.handleAddData = async (viewId) => {
                if (viewId === 'view-rate-cards') {
                    const { data: bData } = await axios.get('/branches?per_page=100');
                    const branchOptions = bData.data.map(b => `<option value="${b.id}">${b.name} (${b.city})</option>`).join('');

                    Swal.fire({
                        title: 'Tambah Rate Card',
                        html: `
                            <div class="text-start">
                                <label class="small fw-bold text-muted">Origin Branch</label>
                                <select id="swal-origin" class="form-select mb-3">${branchOptions}</select>
                                <label class="small fw-bold text-muted">Destination Branch</label>
                                <select id="swal-dest" class="form-select mb-3">${branchOptions}</select>
                                <label class="small fw-bold text-muted">Service Type</label>
                                <select id="swal-service" class="form-select mb-3">
                                    <option value="economy">Economy</option>
                                    <option value="regular" selected>Regular</option>
                                    <option value="express">Express</option>
                                    <option value="same_day">Same Day</option>
                                </select>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Base Price</label>
                                        <input type="number" id="swal-base" class="form-control mb-3" placeholder="e.g. 15000">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Per KG Price</label>
                                        <input type="number" id="swal-perkg" class="form-control mb-3" placeholder="e.g. 3500">
                                    </div>
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan Rate Card',
                        preConfirm: () => {
                            return {
                                origin_branch_id: document.getElementById('swal-origin').value,
                                destination_branch_id: document.getElementById('swal-dest').value,
                                service_type: document.getElementById('swal-service').value,
                                base_price: document.getElementById('swal-base').value,
                                per_kg_price: document.getElementById('swal-perkg').value,
                                min_weight_kg: 0,
                                is_active: true
                            }
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                await axios.post('/rate-cards', result.value);
                                Swal.fire('Berhasil!', 'Rate card baru telah ditambahkan.', 'success');
                                loadViewData('view-rate-cards', 1);
                            } catch (error) { Swal.fire('Gagal!', error.response?.data?.message || 'Gagal menyimpan.', 'error'); }
                        }
                    });
                }
            };

            window.handleEdit = async (resource, id) => {
                if (resource === 'rate-cards') {
                    const { data: item } = await axios.get(`/rate-cards/${id}`);
                    const { data: bData } = await axios.get('/branches?per_page=100');
                    const branchOptions = bData.data.map(b => `<option value="${b.id}" ${b.id == item.origin_branch_id ? 'selected' : ''}>${b.name} (${b.city})</option>`).join('');

                    Swal.fire({
                        title: 'Edit Rate Card',
                        html: `
                            <div class="text-start">
                                <label class="small fw-bold text-muted">Origin Branch</label>
                                <select id="swal-origin" class="form-select mb-3">${branchOptions}</select>
                                <label class="small fw-bold text-muted">Destination Branch</label>
                                <select id="swal-dest" class="form-select mb-3">${bData.data.map(b => `<option value="${b.id}" ${b.id == item.destination_branch_id ? 'selected' : ''}>${b.name} (${b.city})</option>`).join('')}</select>
                                <label class="small fw-bold text-muted">Service Type</label>
                                <select id="swal-service" class="form-select mb-3">
                                    <option value="economy" ${item.service_type == 'economy' ? 'selected' : ''}>Economy</option>
                                    <option value="regular" ${item.service_type == 'regular' ? 'selected' : ''}>Regular</option>
                                    <option value="express" ${item.service_type == 'express' ? 'selected' : ''}>Express</option>
                                    <option value="same_day" ${item.service_type == 'same_day' ? 'selected' : ''}>Same Day</option>
                                </select>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Base Price</label>
                                        <input type="number" id="swal-base" class="form-control mb-3" value="${item.base_price}">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted">Per KG Price</label>
                                        <input type="number" id="swal-perkg" class="form-control mb-3" value="${item.per_kg_price}">
                                    </div>
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update Rate Card',
                        preConfirm: () => {
                            return {
                                origin_branch_id: document.getElementById('swal-origin').value,
                                destination_branch_id: document.getElementById('swal-dest').value,
                                service_type: document.getElementById('swal-service').value,
                                base_price: document.getElementById('swal-base').value,
                                per_kg_price: document.getElementById('swal-perkg').value
                            }
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                await axios.put(`/rate-cards/${id}`, result.value);
                                Swal.fire('Berhasil!', 'Rate card telah diperbarui.', 'success');
                                loadViewData('view-rate-cards', 1);
                            } catch (error) { Swal.fire('Gagal!', 'Gagal memperbarui.', 'error'); }
                        }
                    });
                }
            };

            window.handleDelete = (resource, id) => {
                Swal.fire({
                    title: 'Hapus Data?',
                    text: "Data akan dipindahkan ke tempat sampah (Soft Delete).",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const endpoint = resource === 'rate-cards' ? `/rate-cards/${id}` : `/${resource}/${id}`;
                            await axios.delete(endpoint);
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

