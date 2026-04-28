<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Ops - KONDANG.</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0052CC;
            --primary-dark: #003D99;
            --secondary: #FFFFFF;
            --text-main: #172B4D;
            --text-muted: #6B778C;
            --bg-body: #F4F7FA;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: white;
            border-right: 1px solid #E2E8F0;
            padding: 2rem 1.5rem;
            z-index: 1000;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        .brand-logo {
            font-family: 'Sora', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 2.5rem;
            display: block;
            text-decoration: none;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
            gap: 12px;
        }

        .nav-link i {
            font-size: 1.25rem;
        }

        .nav-link:hover, .nav-link.active {
            background: #F0F7FF;
            color: var(--primary);
        }

        .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .card-stat {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            height: 100%;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .top-bar {
            background: white;
            border-radius: 20px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }

        .btn-action {
            border-radius: 12px;
            padding: 0.6rem 1.2rem;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .table-kondang {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }

        .table-kondang th {
            background: #F8FAFC;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 1.2rem;
        }

        .table-kondang td {
            padding: 1.2rem;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        /* Views */
        .dashboard-view {
            display: none;
        }
        .dashboard-view.active {
            display: block;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="#" class="brand-logo">KONDANG.</a>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active" data-target="view-overview">
                    <i class="bi bi-grid-fill"></i> Ringkasan
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-target="view-shipments">
                    <i class="bi bi-box-seam-fill"></i> Pengiriman
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-target="view-payments">
                    <i class="bi bi-credit-card-fill"></i> Pembayaran
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-target="view-branches">
                    <i class="bi bi-building-fill"></i> Cabang & Zona
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-target="view-users">
                    <i class="bi bi-people-fill"></i> Manajemen User
                </a>
            </li>
        </ul>

        <div style="position: absolute; bottom: 2rem; left: 1.5rem; right: 1.5rem;">
            <hr class="text-muted opacity-25">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link w-100 border-0 bg-transparent text-danger">
                    <i class="bi bi-box-arrow-left"></i> Keluar Sistem
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <header class="top-bar">
            <div>
                <h5 class="fw-bold mb-0">Selamat Datang, {{ auth()->user()->name }}</h5>
                <small class="text-muted">Akses level: <span class="text-primary fw-bold text-uppercase">{{ auth()->user()->role }}</span></small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold small">{{ auth()->user()->email }}</div>
                    <div class="text-muted" style="font-size: 0.7rem;">Terakhir aktif: {{ now()->format('H:i') }}</div>
                </div>
                <div class="user-avatar">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <!-- View: Overview -->
        <div id="view-overview" class="dashboard-view active">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card-stat">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-truck"></i></div>
                        <div class="text-muted small fw-bold mb-1">PENGIRIMAN AKTIF</div>
                        <h2 class="fw-extrabold mb-0" id="stat-active-shipments">-</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-stat">
                        <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
                        <div class="text-muted small fw-bold mb-1">TOTAL PENDAPATAN</div>
                        <h2 class="fw-extrabold mb-0" id="stat-total-revenue">Rp0</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-stat">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-clock-history"></i></div>
                        <div class="text-muted small fw-bold mb-1">PENDING PAYMENT</div>
                        <h2 class="fw-extrabold mb-0" id="stat-pending-payments">-</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-stat">
                        <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-check-circle-fill"></i></div>
                        <div class="text-muted small fw-bold mb-1">ON-TIME DELIVERY</div>
                        <h2 class="fw-extrabold mb-0" id="stat-sla-rate">0%</h2>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h6 class="fw-bold mb-4">Aktivitas Pengiriman Terbaru</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Resi</th>
                                        <th>Penerima</th>
                                        <th>Cabang</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-shipments-table">
                                    <!-- Data via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h6 class="fw-bold mb-4">Peringatan Sistem (Alerts)</h6>
                        <div id="system-alerts-container">
                            <!-- Data via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View: Shipments -->
        <div id="view-shipments" class="dashboard-view">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Manajemen Pengiriman</h4>
                <button class="btn btn-primary-kondang btn-action" id="btn-create-shipment"><i class="bi bi-plus-lg me-2"></i>Buat Shipment Baru</button>
            </div>
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-0" placeholder="Cari nomor resi atau penerima...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select bg-light border-0">
                            <option value="">Semua Status</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="all-shipments-table">
                        <!-- Data via JS -->
                    </table>
                </div>
            </div>
        </div>

        <!-- Integration Loading Placeholder -->
        <div id="loading-overlay" style="position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

    </div>

    <!-- Modals & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const views = document.querySelectorAll('.dashboard-view');
            const navLinks = document.querySelectorAll('.nav-link');
            const loadingOverlay = document.getElementById('loading-overlay');

            // Navigation Handling
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    if (link.dataset.target) {
                        e.preventDefault();
                        navLinks.forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                        
                        views.forEach(v => v.classList.remove('active'));
                        document.getElementById(link.dataset.target).classList.add('active');
                    }
                });
            });

            // Fetch Dashboard Data
            const loadData = async () => {
                try {
                    const response = await axios.get('/dashboard/data');
                    const data = response.data;
                    
                    // Update Stats
                    document.getElementById('stat-active-shipments').textContent = data.shipments_total;
                    document.getElementById('stat-total-revenue').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.revenue_total);
                    document.getElementById('stat-pending-payments').textContent = data.outstanding_payments;
                    document.getElementById('stat-sla-rate').textContent = data.executive_kpi.on_time_delivery_rate + '%';

                    // Update Recent Shipments
                    const recentTable = document.getElementById('recent-shipments-table');
                    recentTable.innerHTML = '';
                    
                    // Note: In real app we would use data.trackings_recent or similar
                    // Here we just map some sample data for UI demo
                    (data.recent_shipments || []).forEach(shipment => {
                        const row = `
                            <tr>
                                <td><span class="fw-bold text-primary">${shipment.tracking_number}</span></td>
                                <td>${shipment.recipient_name}</td>
                                <td>${shipment.branch.name}</td>
                                <td><span class="badge-status bg-primary bg-opacity-10 text-primary">${shipment.status.name}</span></td>
                                <td class="small text-muted">${new Date(shipment.created_at).toLocaleDateString('id-ID')}</td>
                            </tr>
                        `;
                        recentTable.insertAdjacentHTML('beforeend', row);
                    });

                    // Update Alerts
                    const alertContainer = document.getElementById('system-alerts-container');
                    alertContainer.innerHTML = '';
                    
                    if (data.alert_center.shipments_overdue.total > 0) {
                        alertContainer.innerHTML += `
                            <div class="d-flex gap-3 mb-3 p-3 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-25">
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                                <div>
                                    <div class="fw-bold text-danger">Pengiriman Terhambat</div>
                                    <small class="text-danger opacity-75">${data.alert_center.shipments_overdue.total} paket melewati estimasi waktu.</small>
                                </div>
                            </div>
                        `;
                    }

                    loadingOverlay.style.display = 'none';
                } catch (error) {
                    console.error('Failed to load data:', error);
                    loadingOverlay.innerHTML = '<div class="text-danger fw-bold">Gagal memuat data. Periksa koneksi atau sesi login Anda.</div>';
                }
            };

            loadData();
        });
    </script>
</body>
</html>
