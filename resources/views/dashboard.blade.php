<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Kondang Ekspedisi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --k-white: #ffffff;
            --k-paper: #f5f9ff;
            --k-line: #d9e7ff;
            --k-blue-900: #0b2b67;
            --k-blue-800: #17479c;
            --k-blue-700: #2769d8;
            --k-blue-500: #4f9bff;
            --k-text: #14284b;
            --k-muted: #60759a;
            --k-shadow: 0 16px 40px rgba(22, 68, 150, 0.14);
            --k-danger: #dc3545;
            --k-warning: #f59e0b;
            --k-success: #10b981;
            --k-info: #0ea5e9;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--k-text);
            background:
                radial-gradient(860px 360px at 6% -6%, #dbe9ff 0%, transparent 70%),
                radial-gradient(900px 460px at 100% 0%, #e9f2ff 0%, transparent 62%),
                linear-gradient(180deg, #f8fbff 0%, #ffffff 54%);
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Sora', sans-serif;
            letter-spacing: -0.02em;
        }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
        }

        .sidebar {
            border-right: 1px solid var(--k-line);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(242, 249, 255, 0.96) 100%);
            backdrop-filter: blur(10px);
            padding: 1rem;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .brand-chip {
            border: 1px solid #c6ddff;
            color: var(--k-blue-800);
            background: #edf4ff;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            padding: 0.35rem 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .brand-chip::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--k-blue-500);
            box-shadow: 0 0 10px rgba(79, 155, 255, 0.9);
        }

        .menu-btn {
            width: 100%;
            border: 1px solid var(--k-line);
            background: #fff;
            color: var(--k-blue-900);
            border-radius: 14px;
            text-align: left;
            padding: 0.8rem 0.9rem;
            font-weight: 600;
            margin-bottom: 0.55rem;
            transition: all 0.2s ease;
        }

        .menu-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(16, 73, 165, 0.1);
        }

        .menu-btn.active {
            background: linear-gradient(90deg, #1e63cf 0%, #448ff9 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 10px 24px rgba(24, 87, 192, 0.25);
        }

        .menu-btn i {
            margin-right: 0.55rem;
        }

        .main-panel {
            padding: 1.2rem 1.4rem 1.6rem;
        }

        .header-card,
        .surface-box,
        .metric-box {
            border: 1px solid var(--k-line);
            border-radius: 18px;
            background: var(--k-white);
            box-shadow: 0 8px 24px rgba(11, 43, 103, 0.08);
        }

        .header-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
        }

        .metric-box {
            padding: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        }

        .metric-label {
            color: var(--k-muted);
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .metric-value {
            margin-top: 0.4rem;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--k-blue-900);
        }

        .surface-box {
            padding: 1rem;
        }

        .section-title {
            color: var(--k-blue-900);
            font-size: 1.18rem;
            margin-bottom: 0.7rem;
        }

        .chart-box {
            height: 340px;
            display: flex;
            flex-direction: column;
        }

        .chart-fixed {
            position: relative;
            flex: 1;
            min-height: 0;
        }

        .chart-fixed canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .table > :not(caption) > * > * {
            color: var(--k-text);
            border-color: #e6efff;
        }

        .table thead th {
            background: #f3f8ff;
            color: var(--k-blue-900);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-soft {
            border: 1px solid #d0e2ff;
            background: #edf4ff;
            color: var(--k-blue-800);
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .crud-grid {
            display: grid;
            grid-template-columns: minmax(280px, 340px) 1fr;
            gap: 1rem;
        }

        .form-label {
            font-size: 0.83rem;
            font-weight: 700;
            color: var(--k-muted);
            margin-bottom: 0.3rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #cfe0ff;
            border-radius: 10px;
            min-height: 42px;
        }

        .btn-kondang {
            background: linear-gradient(90deg, #1e63cf 0%, #448ff9 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(24, 87, 192, 0.22);
        }

        .btn-kondang:hover {
            color: #fff;
            filter: brightness(0.97);
        }

        .btn-kondang-secondary {
            border: 1px solid #9fc3ff;
            color: var(--k-blue-800);
            background: #fff;
            font-weight: 700;
        }

        .hidden-view {
            display: none;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            display: inline-block;
            margin-right: 0.35rem;
        }

        .status-healthy { background: var(--k-success); }
        .status-warning { background: var(--k-warning); }
        .status-error { background: var(--k-danger); }

        .table-toolbar {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-trigger {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            border: 1px solid #cfe0ff;
            background: #fff;
            color: var(--k-blue-800);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .filter-popup {
            position: absolute;
            top: 40px;
            right: 0;
            z-index: 30;
            width: min(820px, 92vw);
            border: 1px solid var(--k-line);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 16px 36px rgba(11, 43, 103, 0.16);
            padding: 0.75rem;
            display: none;
        }

        .filter-popup.show {
            display: block;
        }

        .mini-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.22rem 0.7rem;
            background: #edf4ff;
            border: 1px solid #d0e2ff;
            color: var(--k-blue-800);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .card-soft {
            border: 1px solid var(--k-line);
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 8px 20px rgba(11, 43, 103, 0.06);
        }

        @media (max-width: 1200px) {
            .crud-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
                border-right: none;
                border-bottom: 1px solid var(--k-line);
            }

            .menu-wrap {
                display: flex;
                gap: 0.5rem;
                overflow-x: auto;
                padding-bottom: 0.2rem;
            }

            .menu-btn {
                white-space: nowrap;
                margin-bottom: 0;
            }

            .chart-box {
                height: 300px;
            }

            .filter-popup {
                left: 0;
                right: auto;
                width: min(96vw, 560px);
            }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="mb-3">
            <div class="brand-chip mb-2">Kondang Admin</div>
            <h5 class="mb-1">Operational Dashboard</h5>
            <p class="text-muted small mb-0">Business monitoring dan CRUD data master dalam satu panel.</p>
        </div>

        <div class="menu-wrap">
            <button class="menu-btn active" data-view="ringkasan"><i class="bi bi-speedometer2"></i>Overview</button>
            <!-- <button class="menu-btn" data-view="governance"><i class="bi bi-shield-lock"></i>Governance</button> -->
            <button class="menu-btn" data-view="zona"><i class="bi bi-globe-asia-australia"></i>Zona</button>
            <button class="menu-btn" data-view="kartu-tarif"><i class="bi bi-cash-coin"></i>Kartu Tarif</button>
            <button class="menu-btn" data-view="cabang"><i class="bi bi-diagram-3"></i>Cabang</button>
            <button class="menu-btn" data-view="kendaraan"><i class="bi bi-truck"></i>Kendaraan</button>
            <button class="menu-btn" data-view="user"><i class="bi bi-people"></i>User</button>
            <button class="menu-btn" data-view="shipment"><i class="bi bi-box-seam"></i>Shipment</button>
            <button class="menu-btn" data-view="payment"><i class="bi bi-wallet2"></i>Payment</button>
            <button class="menu-btn" data-view="landing-content"><i class="bi bi-layout-text-window-reverse"></i>Landing Content</button>
        </div>

        <div class="surface-box mt-3">
            <div class="small text-muted mb-2">Quick Access</div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('landing') }}" class="btn btn-sm btn-kondang-secondary">Landing</a>
                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-kondang-secondary">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main-panel">
        <section class="header-card d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="mb-1">Modern Admin Panel</h4>
                <div class="text-muted small">Visual style mengikuti landing page dengan istilah campuran yang umum.</div>
            </div>
            <span class="badge-soft" id="statusPeran">Memuat data...</span>
        </section>

        <section id="view-ringkasan">
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button class="btn btn-sm btn-kondang" onclick="exportPrimaryData()">Export Data</button>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Pengiriman Bulan Ini</div><div class="metric-value" id="kpiPengiriman">0</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Pendapatan Settled</div><div class="metric-value" id="kpiPendapatan">Rp0</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Ketepatan Waktu</div><div class="metric-value" id="kpiOnTime">0%</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Rasio Batal/Retur</div><div class="metric-value" id="kpiCancel">0%</div></div></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-8">
                    <div class="surface-box chart-box">
                        <h6 class="section-title">Tren Settlement 14 Hari</h6>
                        <div class="chart-fixed">
                            <canvas id="chartSettlement"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="surface-box chart-box">
                        <h6 class="section-title">Distribusi Status Pengiriman</h6>
                        <div class="chart-fixed">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="surface-box">
                        <h6 class="section-title">Performa Cabang</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Cabang</th><th>Volume</th><th>SLA</th></tr></thead>
                                <tbody id="tabelPerformaCabang">
                                <tr><td colspan="3" class="text-muted text-center py-3">Memuat...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="surface-box">
                        <h6 class="section-title">Kesehatan Integrasi</h6>
                        <div id="daftarIntegrasi" class="small text-muted">Memuat...</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="view-governance" class="hidden-view">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="section-title mb-1">Governance & Controls</h5>
                    <div class="text-muted small">Trash, alerts, audit trail, queue health, permissions, dan snapshot sistem.</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-kondang-secondary" onclick="loadGovernanceData()">Refresh</button>
                    <button class="btn btn-sm btn-kondang" onclick="loadAuditLogs()">Reload Audit</button>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Pending Approval</div><div class="metric-value" id="govPendingApproval">0</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Trashed Items</div><div class="metric-value" id="govTrashedTotal">0</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Critical Errors</div><div class="metric-value" id="govCriticalErrors">0</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Failed Jobs</div><div class="metric-value" id="govFailedJobs">0</div></div></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="surface-box h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Alert Center</h6>
                            <span class="mini-chip" id="govAlertCount">0 alerts</span>
                        </div>
                        <div id="govAlertList" class="d-grid gap-2"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="surface-box h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">System Snapshot</h6>
                            <span class="mini-chip" id="govEnvChip">-</span>
                        </div>
                        <div class="row g-2" id="govSystemSnapshot"></div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-7">
                    <div class="surface-box h-100">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <h6 class="mb-0">Audit Log Viewer</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <input id="auditSearch" class="form-control form-control-sm" style="width: 140px;" placeholder="Search">
                                <input id="auditActorFilter" class="form-control form-control-sm" style="width: 120px;" placeholder="Actor ID">
                                <input id="auditActionFilter" class="form-control form-control-sm" style="width: 160px;" placeholder="Action">
                                <input id="auditSubjectFilter" class="form-control form-control-sm" style="width: 140px;" placeholder="Subject">
                                <input id="auditFromFilter" type="date" class="form-control form-control-sm" style="width: 140px;">
                                <input id="auditUntilFilter" type="date" class="form-control form-control-sm" style="width: 140px;">
                                <button class="btn btn-sm btn-kondang-secondary" onclick="loadAuditLogs(1)">Apply</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Waktu</th><th>Actor</th><th>Aksi</th><th>Objek</th><th>Detail</th></tr></thead>
                                <tbody id="auditLogTable"></tbody>
                            </table>
                        </div>
                        <div id="auditPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="surface-box h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Trash & Restore</h6>
                            <span class="mini-chip" id="govTrashChip">0 items</span>
                        </div>
                        <div id="trashSummary" class="row g-2 mb-3"></div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Data</th><th>Dihapus</th><th>Aksi</th></tr></thead>
                                <tbody id="trashTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <div class="surface-box h-100">
                        <h6 class="mb-2">Queue & Integration Health</h6>
                        <div id="govHealthList" class="d-grid gap-2"></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="view-zona" class="hidden-view">
            <h5 class="section-title">Manajemen Zona</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">Form Zona</h6>
                    <form id="formZona" class="row g-2">
                        <input type="hidden" id="zonaId">
                        <div class="col-12">
                            <label class="form-label" for="zonaKode">Kode Zona</label>
                            <input id="zonaKode" class="form-control" required maxlength="20">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="zonaNama">Nama Zona</label>
                            <input id="zonaNama" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="zonaDeskripsi">Deskripsi</label>
                            <textarea id="zonaDeskripsi" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="zonaMultiplier">Pengali Tarif</label>
                            <input id="zonaMultiplier" type="number" class="form-control" min="1" step="0.01" value="1" required>
                        </div>
                        <div class="col-12 form-check mt-2 ms-1">
                            <input class="form-check-input" type="checkbox" id="zonaAktif" checked>
                            <label class="form-check-label" for="zonaAktif">Zona aktif</label>
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Simpan</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormZona()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Daftar Zona</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('zoneFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadZones()">Reload</button>
                            <div id="zoneFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-4"><input id="zoneSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="zoneActiveFilter" class="form-select form-select-sm"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                                    <div class="col-md-2"><select id="zoneSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="code">Code</option><option value="name">Name</option><option value="multiplier">Multiplier</option></select></div>
                                    <div class="col-md-1"><select id="zoneSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="zonePerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Kode</th><th>Nama</th><th>Pengali</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody id="tabelZona"></tbody>
                        </table>
                    </div>
                    <div id="zonePagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-kartu-tarif" class="hidden-view">
            <h5 class="section-title">Manajemen Kartu Tarif</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">Form Kartu Tarif</h6>
                    <form id="formRateCard" class="row g-2">
                        <input type="hidden" id="rateCardId">
                        <div class="col-6">
                            <label class="form-label" for="rateCardOriginZona">Zona Asal</label>
                            <select id="rateCardOriginZona" class="form-select" required></select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rateCardDestinationZona">Zona Tujuan</label>
                            <select id="rateCardDestinationZona" class="form-select" required></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="rateCardService">Jenis Layanan</label>
                            <select id="rateCardService" class="form-select" required>
                                <option value="regular">Reguler</option>
                                <option value="express">Express</option>
                                <option value="same_day">Same Day</option>
                                <option value="economy">Ekonomi</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rateCardMin">Berat Minimum (kg)</label>
                            <input id="rateCardMin" type="number" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rateCardMax">Berat Maksimum (kg)</label>
                            <input id="rateCardMax" type="number" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rateCardBase">Tarif Dasar</label>
                            <input id="rateCardBase" type="number" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rateCardPerKg">Tarif per Kg</label>
                            <input id="rateCardPerKg" type="number" class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="rateCardInsurance">Biaya Asuransi</label>
                            <input id="rateCardInsurance" type="number" class="form-control" min="0" required>
                        </div>
                        <div class="col-12 form-check mt-2 ms-1">
                            <input class="form-check-input" type="checkbox" id="rateCardAktif" checked>
                            <label class="form-check-label" for="rateCardAktif">Kartu tarif aktif</label>
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Simpan</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormRateCard()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Daftar Kartu Tarif</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('rateCardFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadRateCards()">Reload</button>
                            <div id="rateCardFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="rateCardSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="rateCardOriginZoneFilter" class="form-select form-select-sm"><option value="">All Zona Asal</option></select></div>
                                    <div class="col-md-2"><select id="rateCardDestinationZoneFilter" class="form-select form-select-sm"><option value="">All Zona Tujuan</option></select></div>
                                    <div class="col-md-2"><select id="rateCardServiceFilter" class="form-select form-select-sm"><option value="">All Service</option><option value="regular">Regular</option><option value="express">Express</option><option value="same_day">Same Day</option><option value="economy">Economy</option></select></div>
                                    <div class="col-md-1"><select id="rateCardSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="service_type">Service</option><option value="base_price">Base Price</option></select></div>
                                    <div class="col-md-1"><select id="rateCardSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="rateCardPerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Rute Zona</th><th>Layanan</th><th>Rentang</th><th>Tarif Dasar</th><th>Aksi</th></tr></thead>
                            <tbody id="tabelRateCard"></tbody>
                        </table>
                    </div>
                    <div id="rateCardPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-cabang" class="hidden-view">
            <h5 class="section-title">Performa & Manajemen Cabang</h5>
            
            <!-- Performance Section -->
            <div id="branchPerformanceSection"></div>

            <!-- CRUD Section -->
            <div class="crud-grid mt-4">
                <div class="surface-box">
                    <h6 class="mb-3">Form Cabang</h6>
                    <form id="formCabang" class="row g-2">
                        <input type="hidden" id="cabangId">
                        <div class="col-6"><label class="form-label" for="cabangKode">Kode</label><input id="cabangKode" class="form-control" required></div>
                        <div class="col-6"><label class="form-label" for="cabangNama">Nama</label><input id="cabangNama" class="form-control" required></div>
                        <div class="col-6"><label class="form-label" for="cabangKota">Kota</label><input id="cabangKota" class="form-control" required></div>
                        <div class="col-6"><label class="form-label" for="cabangZona">Zona</label><select id="cabangZona" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="cabangTelepon">Telepon</label><input id="cabangTelepon" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="cabangEmail">Email</label><input id="cabangEmail" type="email" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="cabangAlamat">Alamat</label><textarea id="cabangAlamat" class="form-control" rows="2" required></textarea></div>
                        <div class="col-12 form-check mt-2 ms-1"><input class="form-check-input" type="checkbox" id="cabangAktif" checked><label class="form-check-label" for="cabangAktif">Cabang aktif</label></div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Simpan</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormCabang()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Daftar Cabang</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('branchFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadBranches()">Reload</button>
                            <div id="branchFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="branchSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="branchZoneFilter" class="form-select form-select-sm"><option value="">All Zona</option></select></div>
                                    <div class="col-md-2"><select id="branchActiveFilter" class="form-select form-select-sm"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                                    <div class="col-md-2"><select id="branchSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="code">Code</option><option value="name">Name</option><option value="city">City</option></select></div>
                                    <div class="col-md-1"><select id="branchSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="branchPerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Kode</th><th>Nama</th><th>Kota</th><th>Zona</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody id="tabelCabang"></tbody>
                        </table>
                    </div>
                    <div id="branchPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-kendaraan" class="hidden-view">
            <h5 class="section-title">Manajemen Kendaraan</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">Form Kendaraan</h6>
                    <form id="formKendaraan" class="row g-2">
                        <input type="hidden" id="kendaraanId">
                        <div class="col-12"><label class="form-label" for="kendaraanCabang">Cabang</label><select id="kendaraanCabang" class="form-select" required></select></div>
                        <div class="col-12"><label class="form-label" for="kendaraanNama">Nama Kendaraan</label><input id="kendaraanNama" class="form-control" required></div>
                        <div class="col-12"><label class="form-label" for="kendaraanPlat">Nomor Plat</label><input id="kendaraanPlat" class="form-control" required></div>
                        <div class="col-6">
                            <label class="form-label" for="kendaraanTipe">Tipe</label>
                            <select id="kendaraanTipe" class="form-select" required>
                                <option value="motorcycle">Motor</option>
                                <option value="car">Mobil</option>
                                <option value="van">Van</option>
                                <option value="truck">Truk</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label" for="kendaraanKapasitas">Kapasitas (kg)</label><input id="kendaraanKapasitas" type="number" class="form-control" min="0" step="0.01" required></div>
                        <div class="col-12">
                            <label class="form-label" for="kendaraanStatus">Status</label>
                            <select id="kendaraanStatus" class="form-select" required>
                                <option value="available">Tersedia</option>
                                <option value="in_use">Digunakan</option>
                                <option value="maintenance">Perawatan</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Simpan</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormKendaraan()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Daftar Kendaraan</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('vehicleFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadVehicles()">Reload</button>
                            <div id="vehicleFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="vehicleSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="vehicleBranchFilter" class="form-select form-select-sm"><option value="">All Branch</option></select></div>
                                    <div class="col-md-2"><select id="vehicleStatusFilter" class="form-select form-select-sm"><option value="">All Status</option><option value="available">Available</option><option value="in_use">In Use</option><option value="maintenance">Maintenance</option><option value="inactive">Inactive</option></select></div>
                                    <div class="col-md-2"><select id="vehicleSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="plate_number">Plate</option><option value="name">Name</option><option value="status">Status</option></select></div>
                                    <div class="col-md-1"><select id="vehicleSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="vehiclePerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Plat</th><th>Nama</th><th>Cabang</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody id="tabelKendaraan"></tbody>
                        </table>
                    </div>
                    <div id="vehiclePagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-user" class="hidden-view">
            <h5 class="section-title">User Management</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">User Form</h6>
                    <form id="formUser" class="row g-2">
                        <input type="hidden" id="userId">
                        <div class="col-12"><label class="form-label" for="userName">Name</label><input id="userName" class="form-control" required></div>
                        <div class="col-12"><label class="form-label" for="userEmail">Email</label><input id="userEmail" type="email" class="form-control" required></div>
                        <div class="col-6"><label class="form-label" for="userRole">Role</label>
                            <select id="userRole" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="kasir">Kasir</option>
                                <option value="courier">Courier</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label" for="userBranch">Cabang</label><select id="userBranch" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="userPhone">Phone</label><input id="userPhone" class="form-control"></div>
                        <div class="col-6"><label class="form-label" for="userPassword">Password</label><input id="userPassword" type="password" class="form-control" minlength="8"></div>
                        <div class="col-12"><label class="form-label" for="userAddress">Address</label><textarea id="userAddress" class="form-control" rows="2"></textarea></div>
                        <div class="col-12 form-check mt-2 ms-1"><input class="form-check-input" type="checkbox" id="userActive" checked><label class="form-check-label" for="userActive">Active</label></div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Save</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormUser()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">User List</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('userFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadUsersCrud()">Reload</button>
                            <div id="userFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="userSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="userRoleFilter" class="form-select form-select-sm"><option value="">All Role</option><option value="admin">Admin</option><option value="manager">Manager</option><option value="kasir">Kasir</option><option value="courier">Courier</option><option value="customer">Customer</option></select></div>
                                    <div class="col-md-2"><select id="userActiveFilter" class="form-select form-select-sm"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                                    <div class="col-md-2"><select id="userSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="name">Name</option><option value="email">Email</option><option value="role">Role</option></select></div>
                                    <div class="col-md-1"><select id="userSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="userPerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody id="tabelUser"></tbody>
                        </table>
                    </div>
                    <div id="userPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-shipment" class="hidden-view">
            <h5 class="section-title">Shipment Management</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">Shipment Form</h6>
                    <form id="formShipment" class="row g-2">
                        <input type="hidden" id="shipmentId">
                        <div class="col-6"><label class="form-label" for="shipmentCustomer">Customer</label><select id="shipmentCustomer" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentBranch">Cabang Asal</label><select id="shipmentBranch" class="form-select" required></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentDestinationBranch">Cabang Tujuan</label><select id="shipmentDestinationBranch" class="form-select" required></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentCourier">Courier</label><select id="shipmentCourier" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentVehicle">Kendaraan</label><select id="shipmentVehicle" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentZone">Zona Tujuan (otomatis)</label><select id="shipmentZone" class="form-select" required disabled></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentService">Service</label>
                            <select id="shipmentService" class="form-select" required>
                                <option value="regular">Regular</option>
                                <option value="express">Express</option>
                                <option value="same_day">Same Day</option>
                                <option value="economy">Economy</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label" for="senderName">Sender Name</label><input id="senderName" class="form-control" required></div>
                        <div class="col-6"><label class="form-label" for="senderPhone">Sender Phone</label><input id="senderPhone" class="form-control" required></div>
                        <div class="col-12"><label class="form-label" for="senderAddress">Sender Address</label><textarea id="senderAddress" class="form-control" rows="2" required></textarea></div>
                        <div class="col-6"><label class="form-label" for="recipientName">Recipient Name</label><input id="recipientName" class="form-control" required></div>
                        <div class="col-6"><label class="form-label" for="recipientPhone">Recipient Phone</label><input id="recipientPhone" class="form-control" required></div>
                        <div class="col-12"><label class="form-label" for="recipientAddress">Recipient Address</label><textarea id="recipientAddress" class="form-control" rows="2" required></textarea></div>
                        <div class="col-4"><label class="form-label" for="shipmentWeight">Weight (kg)</label><input id="shipmentWeight" type="number" class="form-control" step="0.01" min="0.1" required></div>
                        <div class="col-4"><label class="form-label" for="shipmentVolume">Volume</label><input id="shipmentVolume" type="number" class="form-control" step="0.01" min="0"></div>
                        <div class="col-4"><label class="form-label" for="shipmentStatusId">Status</label><select id="shipmentStatusId" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="shipmentPaymentStatus">Payment Status</label>
                            <select id="shipmentPaymentStatus" class="form-select">
                                <option value="unpaid">Unpaid</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label" for="shipmentEta">ETA</label><input id="shipmentEta" type="datetime-local" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="shipmentNotes">Notes</label><textarea id="shipmentNotes" class="form-control" rows="2"></textarea></div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Save</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormShipment()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Shipment List</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('shipmentFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadShipmentsCrud()">Reload</button>
                            <div id="shipmentFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="shipmentSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="shipmentBranchFilter" class="form-select form-select-sm"><option value="">All Branch</option></select></div>
                                    <div class="col-md-2"><select id="shipmentStatusFilter" class="form-select form-select-sm"><option value="">All Status</option></select></div>
                                    <div class="col-md-2"><select id="shipmentSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="tracking_number">Tracking</option><option value="estimated_delivery_at">ETA</option><option value="payment_status">Payment Status</option></select></div>
                                    <div class="col-md-1"><select id="shipmentSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="shipmentPerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Tracking</th><th>Recipient</th><th>Status</th><th>Branch</th><th>Action</th></tr></thead>
                            <tbody id="tabelShipment"></tbody>
                        </table>
                    </div>
                    <div id="shipmentPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-payment" class="hidden-view">
            <h5 class="section-title">Payment Management</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">Payment Form</h6>
                    <form id="formPayment" class="row g-2">
                        <input type="hidden" id="paymentId">
                        <div class="col-12"><label class="form-label" for="paymentShipment">Shipment</label><select id="paymentShipment" class="form-select" required></select></div>
                        <div class="col-6"><label class="form-label" for="paymentCustomer">Customer</label><select id="paymentCustomer" class="form-select"></select></div>
                        <div class="col-6"><label class="form-label" for="paymentMethod">Method</label>
                            <select id="paymentMethod" class="form-select" required>
                                <option value="midtrans">Midtrans</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="e_wallet">E-Wallet</option>
                                <option value="cod">COD</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label" for="paymentAmount">Amount</label><input id="paymentAmount" type="number" class="form-control" min="0" required></div>
                        <div class="col-6"><label class="form-label" for="paymentStatus">Status</label>
                            <select id="paymentStatus" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="settlement">Settlement</option>
                                <option value="deny">Deny</option>
                                <option value="expire">Expire</option>
                                <option value="cancel">Cancel</option>
                                <option value="refund">Refund</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label" for="paymentNotes">Notes</label><textarea id="paymentNotes" class="form-control" rows="2"></textarea></div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Save</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormPayment()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Payment List</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('paymentFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadPaymentsCrud()">Reload</button>
                            <div id="paymentFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="paymentSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="paymentStatusFilter" class="form-select form-select-sm"><option value="">All Status</option><option value="pending">Pending</option><option value="settlement">Settlement</option><option value="deny">Deny</option><option value="expire">Expire</option><option value="cancel">Cancel</option><option value="refund">Refund</option><option value="failed">Failed</option></select></div>
                                    <div class="col-md-2"><select id="paymentMethodFilter" class="form-select form-select-sm"><option value="">All Method</option><option value="midtrans">Midtrans</option><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="e_wallet">E-Wallet</option><option value="cod">COD</option></select></div>
                                    <div class="col-md-2"><select id="paymentSortBy" class="form-select form-select-sm"><option value="created_at">Created</option><option value="amount">Amount</option><option value="status">Status</option><option value="method">Method</option></select></div>
                                    <div class="col-md-1"><select id="paymentSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="paymentPerPage" class="form-select form-select-sm"><option>10</option><option selected>15</option><option>25</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>ID</th><th>Shipment</th><th>Method</th><th>Status</th><th>Amount</th><th>Action</th></tr></thead>
                            <tbody id="tabelPayment"></tbody>
                        </table>
                    </div>
                    <div id="paymentPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>

        <section id="view-landing-content" class="hidden-view">
            <h5 class="section-title">Landing Page Content</h5>
            <div class="crud-grid">
                <div class="surface-box">
                    <h6 class="mb-3">Content Form</h6>
                    <form id="formLandingContent" class="row g-2">
                        <input type="hidden" id="landingContentId">
                        <div class="col-6"><label class="form-label" for="landingSection">Section</label>
                            <select id="landingSection" class="form-select" required>
                                <option value="hero">Hero</option>
                                <option value="feature">Feature</option>
                                <option value="testimonial">Testimonial</option>
                                <option value="faq">FAQ</option>
                                <option value="cta">CTA</option>
                                <option value="contact">Contact</option>
                                <option value="statistic">Statistic</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label" for="landingSortOrder">Sort Order</label><input id="landingSortOrder" type="number" class="form-control" min="0" value="0"></div>
                        <div class="col-12"><label class="form-label" for="landingTitle">Title</label><input id="landingTitle" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="landingSubtitle">Subtitle</label><input id="landingSubtitle" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="landingBody">Content</label><textarea id="landingBody" class="form-control" rows="3"></textarea></div>
                        <div class="col-6"><label class="form-label" for="landingCtaLabel">CTA Label</label><input id="landingCtaLabel" class="form-control"></div>
                        <div class="col-6"><label class="form-label" for="landingCtaUrl">CTA URL</label><input id="landingCtaUrl" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="landingImageUrl">Image URL</label><input id="landingImageUrl" class="form-control"></div>
                        <div class="col-12"><label class="form-label" for="landingMetadata">Metadata (JSON)</label><textarea id="landingMetadata" class="form-control" rows="3" placeholder='{"key":"value"}'></textarea></div>
                        <div class="col-12 form-check mt-2 ms-1"><input class="form-check-input" type="checkbox" id="landingActive" checked><label class="form-check-label" for="landingActive">Active</label></div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-sm btn-kondang">Save</button>
                            <button type="button" class="btn btn-sm btn-kondang-secondary" onclick="resetFormLandingContent()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="surface-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Content List</h6>
                        <div class="table-toolbar">
                            <button class="filter-trigger" title="Filter" onclick="toggleFilterPopup('landingFiltersPopup', event)"><i class="bi bi-funnel"></i></button>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="loadLandingContentsCrud()">Reload</button>
                            <div id="landingFiltersPopup" class="filter-popup">
                                <div class="row g-2">
                                    <div class="col-md-3"><input id="landingSearch" class="form-control form-control-sm" placeholder="Search"></div>
                                    <div class="col-md-2"><select id="landingSectionFilter" class="form-select form-select-sm"><option value="">All Section</option><option value="hero">Hero</option><option value="feature">Feature</option><option value="testimonial">Testimonial</option><option value="faq">FAQ</option><option value="cta">CTA</option><option value="contact">Contact</option><option value="statistic">Statistic</option></select></div>
                                    <div class="col-md-2"><select id="landingActiveFilter" class="form-select form-select-sm"><option value="">All Status</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                                    <div class="col-md-2"><select id="landingSortBy" class="form-select form-select-sm"><option value="sort_order">Sort Order</option><option value="created_at">Created</option><option value="section">Section</option><option value="title">Title</option></select></div>
                                    <div class="col-md-1"><select id="landingSortDir" class="form-select form-select-sm"><option value="desc">Desc</option><option value="asc">Asc</option></select></div>
                                    <div class="col-md-1"><select id="landingPerPage" class="form-select form-select-sm"><option>10</option><option>15</option><option selected>20</option><option>50</option></select></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Section</th><th>Title</th><th>Order</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody id="tabelLandingContent"></tbody>
                        </table>
                    </div>
                    <div id="landingPagination" class="d-flex justify-content-between align-items-center mt-2 small"></div>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROLE_VIEWS = {
        admin: ['ringkasan', 'governance', 'zona', 'kartu-tarif', 'cabang', 'kendaraan', 'user', 'shipment', 'payment', 'landing-content'],
        manager: ['ringkasan', 'cabang', 'kendaraan', 'user', 'shipment', 'payment'],
        kasir: ['ringkasan', 'shipment', 'payment'],
        courier: ['ringkasan', 'shipment'],
    };

    const appState = {
        isAdmin: false,
        role: null,
        branchId: null,
        branch: null,
        allowedViews: ['ringkasan'],
        payload: null,
        charts: {},
        zones: [],
        rateCards: [],
        branches: [],
        vehicles: [],
        users: [],
        shipments: [],
        payments: [],
        customers: [],
        shipmentStatuses: [],
        landingContents: [],
        trashCenter: null,
        auditLogs: [],
        lists: {
            zones: { page: 1 },
            rateCards: { page: 1 },
            branches: { page: 1 },
            vehicles: { page: 1 },
            users: { page: 1 },
            shipments: { page: 1 },
            payments: { page: 1 },
            landingContents: { page: 1 },
            auditLogs: { page: 1 },
        },
    };

    const statusLabel = {
        available: 'Tersedia',
        in_use: 'Digunakan',
        maintenance: 'Perawatan',
        inactive: 'Nonaktif',
    };

    function toggleFilterPopup(id, event) {
        if (event) event.stopPropagation();
        const target = document.getElementById(id);
        if (!target) return;

        const willShow = !target.classList.contains('show');
        closeAllFilterPopups();
        if (willShow) target.classList.add('show');
    }

    function closeAllFilterPopups() {
        document.querySelectorAll('.filter-popup.show').forEach((el) => el.classList.remove('show'));
    }

    function inputValue(id, fallback = '') {
        const el = document.getElementById(id);
        return el ? el.value : fallback;
    }

    function appendIf(params, key, value) {
        if (value !== null && value !== undefined && value !== '') {
            params.append(key, value);
        }
    }

    function renderPagination(containerId, listKey, currentPage, lastPage, reloadFnName) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const page = Number(currentPage || 1);
        const totalPages = Number(lastPage || 1);
        appState.lists[listKey].page = page;

        container.innerHTML = `
            <span>Page ${page} / ${totalPages}</span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" ${page <= 1 ? 'disabled' : ''} onclick="${reloadFnName}(${page - 1})">Prev</button>
                <button class="btn btn-sm btn-outline-secondary" ${page >= totalPages ? 'disabled' : ''} onclick="${reloadFnName}(${page + 1})">Next</button>
            </div>
        `;
    }

    function exportOverview(format) {
        const payload = appState.payload || {};
        const now = new Date().toISOString().replace(/[:.]/g, '-');

        if (format === 'json') {
            const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
            downloadBlob(blob, `overview-${now}.json`);
            return;
        }

        const rows = [];
        const kpi = payload.executive_kpi || {};
        rows.push(['metric', 'value']);
        rows.push(['shipments_month', kpi.shipments?.month ?? 0]);
        rows.push(['revenue_settled', kpi.revenue?.settled ?? 0]);
        rows.push(['on_time_delivery_rate', kpi.on_time_delivery_rate ?? 0]);
        rows.push(['cancel_return_rate', kpi.cancel_return_rate ?? 0]);

        rows.push([]);
        rows.push(['branch', 'shipment_volume', 'sla_rate']);
        (payload.branch_performance?.ranking || []).forEach((item) => {
            rows.push([item.name || '-', item.shipment_volume || 0, item.sla_rate || 0]);
        });

        const csv = rows.map((row) => row.map(escapeCsv).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        downloadBlob(blob, `overview-${now}.csv`);
    }

    async function exportPrimaryData() {
        if (appState.role === 'admin') {
            await exportAdminDataPack('json');
            return;
        }

        await exportOperationalBundle('json');
    }

    function getExportTimestamp() {
        return new Date().toISOString().replace(/[:.]/g, '-');
    }

    function buildShipmentsExportParams(page = 1, perPage = 200) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('shipmentSearch'));
        appendIf(params, 'branch_id', inputValue('shipmentBranchFilter'));
        appendIf(params, 'status_id', inputValue('shipmentStatusFilter'));
        appendIf(params, 'sort_by', inputValue('shipmentSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('shipmentSortDir', 'desc'));
        params.append('per_page', String(perPage));
        params.append('page', String(page));
        return params;
    }

    function buildPaymentsExportParams(page = 1, perPage = 200) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('paymentSearch'));
        appendIf(params, 'status', inputValue('paymentStatusFilter'));
        appendIf(params, 'method', inputValue('paymentMethodFilter'));
        appendIf(params, 'sort_by', inputValue('paymentSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('paymentSortDir', 'desc'));
        params.append('per_page', String(perPage));
        params.append('page', String(page));
        return params;
    }

    async function fetchAllPaginated(endpoint, paramsBuilder) {
        const firstParams = paramsBuilder(1);
        const first = await api(`${endpoint}?${firstParams.toString()}`);
        const lastPage = Number(first.last_page || 1);
        const records = [...(first.data || [])];

        for (let page = 2; page <= lastPage; page += 1) {
            const pageParams = paramsBuilder(page);
            const data = await api(`${endpoint}?${pageParams.toString()}`);
            records.push(...(data.data || []));
        }

        return records;
    }

    async function fetchSnapshotResource(path, perPage = 200) {
        const paramsBuilder = (page) => {
            const params = new URLSearchParams();
            params.append('per_page', String(perPage));
            params.append('page', String(page));
            return params;
        };
        return fetchAllPaginated(path, paramsBuilder);
    }

    function createCsvBlob(rows) {
        const csv = rows.map((row) => row.map(escapeCsv).join(',')).join('\n');
        return new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    }

    async function exportOperationalBundle(format = 'json') {
        const now = getExportTimestamp();
        const shipments = await fetchAllPaginated('/shipments', buildShipmentsExportParams);
        const payments = await fetchAllPaginated('/payments', buildPaymentsExportParams);
        const payload = appState.payload || {};

        const bundle = {
            role: appState.role,
            generated_at: new Date().toISOString(),
            summary: {
                shipments_total: payload.shipments_total || 0,
                shipments_pending: payload.shipments_pending || 0,
                shipments_in_transit: payload.shipments_in_transit || 0,
                shipments_delivered: payload.shipments_delivered || 0,
                payments_total: payload.payments_total || 0,
                outstanding_payments: payload.outstanding_payments || 0,
                revenue_total: payload.revenue_total || 0,
            },
            status_breakdown: payload.status_breakdown || [],
            recent_shipments: payload.recent_shipments || [],
            recent_payments: payload.recent_payments || [],
            recent_trackings: payload.trackings_recent || [],
            shipments,
            payments,
        };

        if (format === 'json') {
            const blob = new Blob([JSON.stringify(bundle, null, 2)], { type: 'application/json' });
            downloadBlob(blob, `operational-bundle-${now}.json`);
            return;
        }

        const rows = [
            ['dataset', 'record'],
            ...bundle.status_breakdown.map((item) => ['status_breakdown', JSON.stringify(item)]),
            ...bundle.recent_shipments.map((item) => ['recent_shipments', JSON.stringify(item)]),
            ...bundle.recent_payments.map((item) => ['recent_payments', JSON.stringify(item)]),
            ...shipments.map((item) => ['shipments', JSON.stringify(item)]),
            ...payments.map((item) => ['payments', JSON.stringify(item)]),
        ];

        downloadBlob(createCsvBlob(rows), `operational-bundle-${now}.csv`);
    }

    async function exportAdminDataPack(format = 'json') {
        if (appState.role !== 'admin') {
            alert('Export full data hanya untuk admin.');
            return;
        }

        const now = getExportTimestamp();
        const [
            zones,
            rateCards,
            branches,
            vehicles,
            users,
            customers,
            shipmentStatuses,
            landingContents,
            shipments,
            payments,
            auditLogs,
        ] = await Promise.all([
            fetchSnapshotResource('/zones'),
            fetchSnapshotResource('/rate-cards'),
            fetchSnapshotResource('/branches'),
            fetchSnapshotResource('/vehicles'),
            fetchSnapshotResource('/users'),
            fetchSnapshotResource('/customers'),
            fetchSnapshotResource('/shipment-statuses'),
            fetchSnapshotResource('/landing-page-contents'),
            fetchAllPaginated('/shipments', buildShipmentsExportParams),
            fetchAllPaginated('/payments', buildPaymentsExportParams),
            fetchSnapshotResource('/audit-logs'),
        ]);

        const bundle = {
            role: appState.role,
            generated_at: new Date().toISOString(),
            overview_payload: appState.payload || {},
            datasets: {
                zones,
                rate_cards: rateCards,
                branches,
                vehicles,
                users,
                customers,
                shipment_statuses: shipmentStatuses,
                landing_contents: landingContents,
                shipments,
                payments,
                audit_logs: auditLogs,
            },
        };

        if (format === 'json') {
            const blob = new Blob([JSON.stringify(bundle, null, 2)], { type: 'application/json' });
            downloadBlob(blob, `admin-full-data-${now}.json`);
            return;
        }

        const rows = [['dataset', 'record']];
        Object.entries(bundle.datasets).forEach(([dataset, items]) => {
            (items || []).forEach((item) => {
                rows.push([dataset, JSON.stringify(item)]);
            });
        });
        downloadBlob(createCsvBlob(rows), `admin-full-data-${now}.csv`);
    }

    function escapeCsv(value) {
        const str = String(value ?? '');
        if (/[",\n]/.test(str)) {
            return `"${str.replace(/"/g, '""')}"`;
        }
        return str;
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function normalizeStatusValue(value) {
        return String(value || '').trim().toLowerCase().replace(/\s+/g, '_');
    }

    function statusBadgeClass(status) {
        const value = normalizeStatusValue(status);

        const green = ['active', 'aktif', 'available', 'tersedia', 'settlement', 'settled', 'paid', 'success', 'healthy', 'completed', 'done', 'finished', 'delivered'];
        const yellow = ['pending', 'processing', 'in_process', 'in_progress', 'in_transit', 'out_for_delivery', 'maintenance', 'perawatan', 'degraded', 'unpaid'];
        const red = ['failed', 'error', 'cancel', 'cancelled', 'deny', 'denied', 'expire', 'expired', 'refund', 'refunded', 'returned', 'damaged', 'rusak', 'inactive', 'nonaktif', 'off'];

        if (green.includes(value)) return 'success';
        if (yellow.includes(value)) return 'warning';
        if (red.includes(value)) return 'danger';
        return 'secondary';
    }

    function renderStatusBadge(label, statusValue = null) {
        const color = statusBadgeClass(statusValue ?? label);
        return `<span class="badge text-bg-${color}">${label}</span>`;
    }

    function renderBooleanStatusBadge(isActive, activeLabel = 'Aktif', inactiveLabel = 'Nonaktif') {
        return renderStatusBadge(isActive ? activeLabel : inactiveLabel, isActive ? 'active' : 'inactive');
    }

    document.addEventListener('DOMContentLoaded', async () => {
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.filter-popup') && !event.target.closest('.filter-trigger')) {
                closeAllFilterPopups();
            }
        });

        bindMenu();
        bindForms();
        bindAutoFilterControls();
        const role = await loadDashboardPayload();

        if (role === 'admin') {
            await Promise.all([
                loadZones(),
                loadRateCards(),
                loadBranches(),
                loadVehicles(),
                loadCustomers(),
                loadShipmentStatuses(),
                loadUsersCrud(),
                loadShipmentsCrud(),
                loadPaymentsCrud(),
                loadLandingContentsCrud(),
            ]);
            return;
        }

        if (role === 'manager') {
            await Promise.all([
                loadBranches(),
                loadZones(),
                loadVehicles(),
                loadUsersCrud(),
                loadCustomers(),
                loadShipmentStatuses(),
                loadShipmentsCrud(),
                loadPaymentsCrud(),
            ]);
            return;
        }

        if (role === 'kasir') {
            await Promise.all([
                loadBranches(),
                loadZones(),
                loadVehicles(),
                loadCustomers(),
                loadShipmentStatuses(),
                loadShipmentsCrud(),
                loadPaymentsCrud(),
            ]);
        }
    });

    function bindMenu() {
        document.querySelectorAll('.menu-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const view = button.getAttribute('data-view');

                if (!appState.allowedViews.includes(view)) {
                    alert('Fitur ini tidak tersedia untuk peran Anda.');
                    return;
                }

                switchView(view);

                if (view === 'zona') await loadZones();
                if (view === 'kartu-tarif') await loadRateCards();
                if (view === 'cabang') await Promise.all([loadBranchPerformance(), loadBranches()]);
                if (view === 'kendaraan') await loadVehicles();
                if (view === 'user') await loadUsersCrud();
                if (view === 'shipment') await loadShipmentsCrud();
                if (view === 'payment') await loadPaymentsCrud();
                if (view === 'landing-content') await loadLandingContentsCrud();
                // if (view === 'governance') await loadGovernanceData();
            });
        });
    }

    function switchView(view) {
        document.querySelectorAll('.menu-btn').forEach((btn) => {
            btn.classList.toggle('active', btn.getAttribute('data-view') === view);
        });

        document.querySelectorAll('main section[id^="view-"]').forEach((section) => {
            section.classList.add('hidden-view');
        });

        const target = document.getElementById(`view-${view}`);
        if (target) target.classList.remove('hidden-view');
    }

    function bindForms() {
        document.getElementById('formZona').addEventListener('submit', submitZona);
        document.getElementById('formRateCard').addEventListener('submit', submitRateCard);
        document.getElementById('formCabang').addEventListener('submit', submitCabang);
        document.getElementById('formKendaraan').addEventListener('submit', submitKendaraan);
        document.getElementById('formUser').addEventListener('submit', submitUser);
        document.getElementById('formShipment').addEventListener('submit', submitShipment);
        document.getElementById('formPayment').addEventListener('submit', submitPayment);
        document.getElementById('formLandingContent').addEventListener('submit', submitLandingContent);
        document.getElementById('shipmentDestinationBranch').addEventListener('change', syncShipmentDestinationZone);
    }

    function debounce(fn, delay = 350) {
        let timer = null;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    }

    function bindAutoFilterControls() {
        const groups = [
            { ids: ['zoneSearch', 'zoneActiveFilter', 'zoneSortBy', 'zoneSortDir', 'zonePerPage'], loader: loadZones },
            { ids: ['rateCardSearch', 'rateCardOriginZoneFilter', 'rateCardDestinationZoneFilter', 'rateCardServiceFilter', 'rateCardSortBy', 'rateCardSortDir', 'rateCardPerPage'], loader: loadRateCards },
            { ids: ['branchSearch', 'branchZoneFilter', 'branchActiveFilter', 'branchSortBy', 'branchSortDir', 'branchPerPage'], loader: loadBranches },
            { ids: ['vehicleSearch', 'vehicleBranchFilter', 'vehicleStatusFilter', 'vehicleSortBy', 'vehicleSortDir', 'vehiclePerPage'], loader: loadVehicles },
            { ids: ['userSearch', 'userRoleFilter', 'userActiveFilter', 'userSortBy', 'userSortDir', 'userPerPage'], loader: loadUsersCrud },
            { ids: ['shipmentSearch', 'shipmentBranchFilter', 'shipmentStatusFilter', 'shipmentSortBy', 'shipmentSortDir', 'shipmentPerPage'], loader: loadShipmentsCrud },
            { ids: ['paymentSearch', 'paymentStatusFilter', 'paymentMethodFilter', 'paymentSortBy', 'paymentSortDir', 'paymentPerPage'], loader: loadPaymentsCrud },
            { ids: ['landingSearch', 'landingSectionFilter', 'landingActiveFilter', 'landingSortBy', 'landingSortDir', 'landingPerPage'], loader: loadLandingContentsCrud },
        ];

        groups.forEach(({ ids, loader }) => {
            const delayedLoad = debounce(() => loader(1));

            ids.forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;

                if (el.tagName === 'INPUT') {
                    el.addEventListener('input', () => delayedLoad());
                } else {
                    el.addEventListener('change', () => loader(1));
                }
            });
        });
    }

    async function loadDashboardPayload() {
        const payload = await api('/dashboard/data');
        appState.payload = payload;
        appState.role = payload.role;
        appState.branchId = payload.branch_id || null;
        appState.branch = payload.branch || null;
        appState.allowedViews = ROLE_VIEWS[payload.role] || ['ringkasan'];

        applyRoleViewAccess(appState.allowedViews);

        if (payload.role === 'admin') {
            appState.isAdmin = true;
            document.getElementById('statusPeran').textContent = 'Peran: Admin';
            renderRingkasan(payload);
            renderGovernance(payload);
            return payload.role;
        }

        appState.isAdmin = false;

        if (payload.role === 'manager') {
            document.getElementById('statusPeran').textContent = 'Peran: Manager';
            renderManagerRingkasan(payload);
            return payload.role;
        }

        if (payload.role === 'kasir') {
            document.getElementById('statusPeran').textContent = 'Peran: Kasir';
            renderKasirRingkasan(payload);
            return payload.role;
        }

        document.getElementById('statusPeran').textContent = `Peran: ${payload.role}`;
        document.getElementById('view-ringkasan').innerHTML = '<div class="surface-box"><div class="alert alert-info mb-0">Dashboard untuk peran ini masih disederhanakan. Silakan gunakan panel operasional yang tersedia.</div></div>';
        return payload.role;
    }

    function applyRoleViewAccess(allowedViews) {
        document.querySelectorAll('.menu-btn').forEach((btn) => {
            const view = btn.getAttribute('data-view');
            btn.style.display = allowedViews.includes(view) ? '' : 'none';
        });

        const activeBtn = document.querySelector('.menu-btn.active');
        if (!activeBtn || !allowedViews.includes(activeBtn.getAttribute('data-view'))) {
            switchView('ringkasan');
        }
    }

    function renderManagerRingkasan(payload) {
        const statusBreakdown = payload.status_breakdown || [];
        const branchPerformance = payload.branch_performance || {};
        const branchRanking = branchPerformance.ranking || [];
        const trackingsRecent = payload.trackings_recent || [];

        const statusRows = statusBreakdown.map((item) => `
            <tr>
                <td>${item.name || '-'}</td>
                <td>${formatNumber(item.total || 0)}</td>
            </tr>
        `).join('') || '<tr><td colspan="2" class="text-center text-muted py-3">-</td></tr>';

        const trackingRows = trackingsRecent.slice(0, 5).map((t) => `
            <tr>
                <td><small>${t.shipment?.tracking_number || '-'}</small></td>
                <td><small>${t.status?.name || '-'}</small></td>
                <td><small>${new Date(t.event_at).toLocaleDateString('id-ID')}</small></td>
            </tr>
        `).join('') || '<tr><td colspan="3" class="text-center text-muted py-2">-</td></tr>';

        const branchRows = branchRanking.slice(0, 5).map((b) => `
            <tr>
                <td><strong>${b.name}</strong></td>
                <td>${formatNumber(b.shipment_volume || 0)}</td>
                <td>${formatCurrency(b.revenue_settled || 0)}</td>
            </tr>
        `).join('') || '<tr><td colspan="3" class="text-center text-muted py-2">-</td></tr>';

        document.getElementById('view-ringkasan').innerHTML = `
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3 flex-wrap">
                <div>
                    <h5 class="section-title mb-1">Dashboard Manager - Sistem Keseluruhan</h5>
                    <div class="text-muted small">Monitoring operasi seluruh cabang dan kinerja real-time</div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Total Shipment</div><div class="metric-value">${formatNumber(payload.shipments_total || 0)}</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Hari Ini</div><div class="metric-value">${formatNumber(payload.shipments_today || 0)}</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Revenue Settled</div><div class="metric-value">${formatCurrency(payload.revenue_total || 0)}</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Payment Pending</div><div class="metric-value">${formatNumber(payload.outstanding_payments || 0)}</div></div></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="surface-box">
                        <h6 class="mb-2">Top 5 Cabang (Revenue)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Cabang</th><th>Shipment</th><th>Revenue</th></tr></thead>
                                <tbody>${branchRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="surface-box">
                        <h6 class="mb-2">Status Breakdown</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Status</th><th>Total</th></tr></thead>
                                <tbody>${statusRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="surface-box">
                        <h6 class="mb-2">Tracking Activity Terbaru</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Tracking</th><th>Status</th><th>Waktu</th></tr></thead>
                                <tbody>${trackingRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12">
                    <button class="btn btn-kondang" onclick="switchView('cabang')">Lihat Detail Performa Semua Cabang →</button>
                </div>
            </div>
        `;
    }

    function renderKasirRingkasan(payload) {
        const recentShipments = payload.recent_shipments || [];
        const recentPayments = payload.recent_payments || [];

        const shipmentRows = recentShipments.map((item) => `
            <tr>
                <td>${item.tracking_number || '-'}</td>
                <td>${item.recipient_name || '-'}</td>
                <td>${renderStatusBadge(item.status?.name || '-', item.status?.code || item.status?.name)}</td>
                <td>${item.branch?.name || '-'}</td>
            </tr>
        `).join('') || '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada data shipment terbaru.</td></tr>';

        const paymentRows = recentPayments.map((item) => `
            <tr>
                <td>${item.shipment?.tracking_number || item.shipment_id || '-'}</td>
                <td>${item.method || '-'}</td>
                <td>${renderStatusBadge(item.status || '-', item.status)}</td>
                <td>${formatCurrency(item.amount || 0)}</td>
            </tr>
        `).join('') || '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada data payment terbaru.</td></tr>';

        document.getElementById('view-ringkasan').innerHTML = `
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button class="btn btn-sm btn-kondang" onclick="exportPrimaryData()">Export Data</button>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Shipment Hari Ini</div><div class="metric-value">${formatNumber(payload.shipments_today || 0)}</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Shipment Pending</div><div class="metric-value">${formatNumber(payload.shipments_pending || 0)}</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Payment Pending</div><div class="metric-value">${formatNumber(payload.outstanding_payments || 0)}</div></div></div>
                <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Revenue Settled</div><div class="metric-value">${formatCurrency(payload.revenue_total || 0)}</div></div></div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="surface-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Shipment Terbaru (Cabang Anda)</h6>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="switchView('shipment')">Tambah / Kelola Shipment</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Tracking</th><th>Penerima</th><th>Status</th><th>Cabang</th></tr></thead>
                                <tbody>${shipmentRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="surface-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Payment Terbaru (Cabang Anda)</h6>
                            <button class="btn btn-sm btn-kondang-secondary" onclick="switchView('payment')">Bantu Pembayaran</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Tracking</th><th>Metode</th><th>Status</th><th>Amount</th></tr></thead>
                                <tbody>${paymentRows}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-0">
                <div class="col-12">
                    <div class="surface-box">
                        <h6 class="mb-2">Catatan Akses Kasir</h6>
                        <div class="small text-muted">Kasir dapat membantu input shipment baru dan memproses payment pada cabang operasionalnya.</div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderGovernance(payload) {
        const trash = payload.trash_center || { summary: {}, recent: [], total: 0 };
        const snapshot = payload.system_snapshot || {};
        const alerts = payload.alert_center || {};
        const reliability = payload.service_reliability || {};
        const actionQueue = payload.action_queue || {};
        appState.trashCenter = trash;

        document.getElementById('govPendingApproval').textContent = formatNumber(actionQueue.quick_actions_summary?.pending_rate_card_approvals || 0);
        document.getElementById('govTrashedTotal').textContent = formatNumber(trash.total || 0);
        document.getElementById('govCriticalErrors').textContent = formatNumber(reliability.critical_error_count || 0);
        document.getElementById('govFailedJobs').textContent = formatNumber(reliability.job_queue_health?.failed_jobs || 0);
        document.getElementById('govAlertCount').textContent = `${formatNumber(countAlerts(alerts))} alerts`;
        document.getElementById('govTrashChip').textContent = `${formatNumber(trash.total || 0)} items`;
        document.getElementById('govEnvChip').textContent = `${snapshot.app_env || '-'} | ${snapshot.queue_driver || '-'}`;

        document.getElementById('govAlertList').innerHTML = renderAlertCards(alerts);
        document.getElementById('govSystemSnapshot').innerHTML = renderSystemSnapshot(snapshot);
        document.getElementById('govHealthList').innerHTML = renderHealthSnapshot(reliability, actionQueue, snapshot);
        document.getElementById('trashSummary').innerHTML = renderTrashSummary(trash.summary || {});
        document.getElementById('trashTable').innerHTML = renderTrashRows(trash.recent || []);
    }

    function countAlerts(alerts) {
        return Object.values(alerts || {}).reduce((sum, group) => sum + Number(group?.total || group?.total_last_24h || 0), 0);
    }

    function renderAlertCards(alerts) {
        const entries = [
            ['Pembayaran Pending 24h', alerts.payments_pending_24h],
            ['Shipment Overdue', alerts.shipments_overdue],
            ['Branch Spike', alerts.cancel_return_spike_branches],
            ['Login Risk', alerts.failed_login_repeated_users],
            ['Midtrans Failure', alerts.midtrans_callback_failures],
        ];

        return entries.map(([label, group]) => {
            const total = Number(group?.total || group?.total_last_24h || 0);
            return `
                <div class="card-soft p-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <strong>${label}</strong>
                        <span class="badge text-bg-${total > 0 ? 'warning' : 'success'}">${formatNumber(total)}</span>
                    </div>
                    <div class="small text-muted">${renderAlertItems(group?.items || group || [])}</div>
                </div>
            `;
        }).join('');
    }

    function renderAlertItems(items) {
        if (!Array.isArray(items) || items.length === 0) {
            return 'Tidak ada alert aktif.';
        }

        return items.slice(0, 3).map((item) => `<div class="small">• ${item.tracking_number || item.action || item.name || item.service_name || item.email || '-'}</div>`).join('');
    }

    function renderSystemSnapshot(snapshot) {
        const items = [
            ['App', snapshot.app_name],
            ['Env', snapshot.app_env],
            ['PHP', snapshot.php_version],
            ['Queue', snapshot.queue_driver],
            ['Mail', snapshot.mail_mailer],
            ['Broadcast', snapshot.broadcast_driver],
            ['Storage', snapshot.integration_summary?.storage],
            ['Midtrans', snapshot.integration_summary?.midtrans],
        ];

        return items.map(([label, value]) => `
            <div class="col-sm-6">
                <div class="card-soft p-3 h-100">
                    <div class="small text-uppercase text-secondary fw-semibold">${label}</div>
                    <div class="fw-bold text-primary-emphasis">${value || '-'}</div>
                </div>
            </div>
        `).join('');
    }

    function renderHealthSnapshot(reliability, actionQueue, snapshot) {
        const items = [
            ['Integration Health', `${Number(reliability.integration_health_score || 0).toFixed(1)}%`],
            ['Failed Jobs', formatNumber(reliability.job_queue_health?.failed_jobs || 0)],
            ['Pending Jobs', formatNumber(reliability.job_queue_health?.pending_jobs || 0)],
            ['Processing Jobs', formatNumber(reliability.job_queue_health?.processing_jobs || 0)],
            ['Retry Pending', formatNumber(reliability.job_queue_health?.retry_pending || 0)],
            ['Maintenance', snapshot.maintenance_mode ? 'On' : 'Off'],
            ['Pending Tasks', formatNumber(actionQueue.pending_tasks?.length || 0)],
            ['In Progress Tasks', formatNumber(actionQueue.in_progress_tasks?.length || 0)],
        ];

        return items.map(([label, value]) => `
            <div class="d-flex justify-content-between align-items-center border rounded-3 p-2">
                <span class="small fw-semibold text-muted">${label}</span>
                <strong>${value}</strong>
            </div>
        `).join('');
    }

    function renderTrashSummary(summary) {
        const items = Object.entries(summary || {});
        if (!items.length) {
            return '<div class="text-muted small">Tidak ada data soft deleted.</div>';
        }

        return items.map(([type, total]) => `
            <div class="col-6">
                <div class="card-soft p-2 text-center">
                    <div class="small text-uppercase text-secondary fw-semibold">${type.replace(/_/g, ' ')}</div>
                    <div class="h5 mb-0 text-primary-emphasis">${formatNumber(total)}</div>
                </div>
            </div>
        `).join('');
    }

    function renderTrashRows(items) {
        if (!Array.isArray(items) || items.length === 0) {
            return '<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada item di trash.</td></tr>';
        }

        return items.map((item) => `
            <tr>
                <td><strong>${item.label || '-'}</strong><div class="small text-muted">${item.type}</div></td>
                <td class="small text-muted">${item.deleted_at || '-'}</td>
                <td><button class="btn btn-sm btn-outline-primary" onclick="restoreTrashItem('${item.type}', ${item.id})">Restore</button></td>
            </tr>
        `).join('');
    }

    function renderPermissionMatrix(matrix) {
        const roles = Object.entries(matrix || {});
        if (!roles.length) {
            return '<div class="text-muted small">Permission matrix belum tersedia.</div>';
        }

        return roles.map(([role, permissions]) => `
            <div class="col-md-6">
                <div class="card-soft p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-capitalize">${role}</strong>
                        <span class="mini-chip">${formatNumber(Object.keys(permissions || {}).length)} permissions</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        ${Object.entries(permissions || {}).map(([key, value]) => `<span class="badge text-bg-${value ? 'primary' : 'secondary'}">${key.replace(/_/g, ' ')}</span>`).join('')}
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function loadGovernanceData() {
        if (!appState.payload) {
            return;
        }

        const trash = await api('/admin/trash');
        appState.payload.trash_center = trash;
        renderGovernance(appState.payload);
        await loadAuditLogs(1);
    }

    async function loadAuditLogs(page = 1) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('auditSearch'));
        appendIf(params, 'actor_id', inputValue('auditActorFilter'));
        appendIf(params, 'action', inputValue('auditActionFilter'));
        appendIf(params, 'subject_type', inputValue('auditSubjectFilter'));
        appendIf(params, 'from', inputValue('auditFromFilter'));
        appendIf(params, 'until', inputValue('auditUntilFilter'));
        params.append('page', String(page));

        const data = await api(`/audit-logs?${params.toString()}`);
        appState.auditLogs = data.data || [];

        document.getElementById('auditLogTable').innerHTML = (appState.auditLogs || []).map((item) => `
            <tr>
                <td class="small text-muted">${item.created_at || '-'}</td>
                <td>${item.actor?.name || item.actor_id || '-'}</td>
                <td><span class="badge text-bg-primary">${item.action || '-'}</span></td>
                <td class="small">${item.subject_type || '-'} #${item.subject_id || '-'}</td>
                <td class="small text-muted">${item.description || '-'}</td>
            </tr>
        `).join('') || '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada log.</td></tr>';

        renderPagination('auditPagination', 'auditLogs', data.current_page, data.last_page, 'loadAuditLogs');
    }

    async function restoreTrashItem(type, id) {
        if (!confirm('Restore data ini?')) return;

        await api(`/admin/trash/${type}/${id}/restore`, 'POST');
        await loadGovernanceData();
    }

    function renderRingkasan(payload) {
        const kpi = payload.executive_kpi || {};
        const branch = payload.branch_performance || {};
        const reliability = payload.service_reliability || {};
        const financial = payload.financial_control || {};

        document.getElementById('kpiPengiriman').textContent = formatNumber(kpi.shipments?.month || 0);
        document.getElementById('kpiPendapatan').textContent = formatCurrency(kpi.revenue?.settled || 0);
        document.getElementById('kpiOnTime').textContent = `${(kpi.on_time_delivery_rate || 0).toFixed(1)}%`;
        document.getElementById('kpiCancel').textContent = `${(kpi.cancel_return_rate || 0).toFixed(1)}%`;

        const cabangRows = (branch.ranking || []).slice(0, 6).map((item) => `
            <tr>
                <td>${item.name || '-'}</td>
                <td>${formatNumber(item.shipment_volume || 0)}</td>
                <td><span class="badge text-bg-${(item.sla_rate || 0) >= 95 ? 'success' : 'warning'}">${(item.sla_rate || 0).toFixed(1)}%</span></td>
            </tr>
        `).join('');
        document.getElementById('tabelPerformaCabang').innerHTML = cabangRows || '<tr><td colspan="3" class="text-muted text-center py-3">Belum ada data.</td></tr>';

        const integrasi = (reliability.integration_statuses || []).map((item) => {
            const total = Number(item.success_count || 0) + Number(item.failure_count || 0);
            const health = total > 0 ? ((Number(item.success_count || 0) / total) * 100) : 100;
            const state = item.status === 'healthy' ? 'healthy' : (item.status === 'degraded' ? 'warning' : 'error');
            return `
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                    <div><span class="status-dot status-${state}"></span><strong>${toTitle(item.service_name || '-')}</strong></div>
                    <div class="fw-semibold">${health.toFixed(1)}%</div>
                </div>
            `;
        }).join('');
        document.getElementById('daftarIntegrasi').innerHTML = integrasi || '<div class="text-muted">Belum ada status integrasi.</div>';

        renderCharts(payload.status_breakdown || [], financial.settlement_trend_daily || []);
    }

    function renderCharts(statusBreakdown, settlementDaily) {
        destroyCharts();

        const settlementCtx = document.getElementById('chartSettlement').getContext('2d');
        appState.charts.settlement = new Chart(settlementCtx, {
            type: 'line',
            data: {
                labels: settlementDaily.map((item) => item.period || '-'),
                datasets: [{
                    label: 'Settlement',
                    data: settlementDaily.map((item) => Number(item.amount || 0)),
                    borderColor: '#2769d8',
                    backgroundColor: 'rgba(39, 105, 216, 0.12)',
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { ticks: { callback: (value) => formatShortCurrency(value) } } },
            },
        });

        const statusCtx = document.getElementById('chartStatus').getContext('2d');
        appState.charts.status = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusBreakdown.map((item) => item.name || '-'),
                datasets: [{
                    data: statusBreakdown.map((item) => Number(item.total || 0)),
                    backgroundColor: ['#2769d8', '#4f9bff', '#0ea5e9', '#10b981', '#f59e0b', '#dc3545'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    function destroyCharts() {
        Object.values(appState.charts).forEach((chart) => {
            if (chart && typeof chart.destroy === 'function') chart.destroy();
        });
        appState.charts = {};
    }

    async function loadZones(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('zoneSearch'));
        appendIf(params, 'is_active', inputValue('zoneActiveFilter'));
        appendIf(params, 'sort_by', inputValue('zoneSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('zoneSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('zonePerPage', '15'));
        params.append('page', String(page || appState.lists.zones.page || 1));

        const data = await api(`/zones?${params.toString()}`);
        appState.zones = data.data || [];

        const rows = appState.zones.map((zone) => `
            <tr>
                <td>${zone.code}</td>
                <td>${zone.name}</td>
                <td>${Number(zone.multiplier).toFixed(2)}</td>
                <td>${renderBooleanStatusBadge(Boolean(zone.is_active), 'Aktif', 'Nonaktif')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editZona(${zone.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="hapusZona(${zone.id})">Hapus</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelZona').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada data zona.</td></tr>';

        populateZoneSelect();
        populateShipmentFormSelects();
        populateCabangZoneSelect();
        populateZoneFilterOptions();
        renderPagination('zonePagination', 'zones', data.current_page, data.last_page, 'loadZones');
    }

    function populateZoneSelect() {
        const originSelect = document.getElementById('rateCardOriginZona');
        const destinationSelect = document.getElementById('rateCardDestinationZona');
        const options = appState.zones.map((zone) => `<option value="${zone.id}">${zone.code} - ${zone.name}</option>`).join('');

        if (originSelect) originSelect.innerHTML = options;
        if (destinationSelect) destinationSelect.innerHTML = options;
    }

    function populateZoneFilterOptions() {
        const ids = ['rateCardOriginZoneFilter', 'rateCardDestinationZoneFilter', 'branchZoneFilter'];
        ids.forEach((id) => {
            const select = document.getElementById(id);
            if (!select) return;
            const current = select.value;
            const defaultLabel = id === 'rateCardOriginZoneFilter'
                ? 'All Zona Asal'
                : (id === 'rateCardDestinationZoneFilter' ? 'All Zona Tujuan' : 'All Zona');
            select.innerHTML = `<option value="">${defaultLabel}</option>${appState.zones.map((zone) => `<option value="${zone.id}">${zone.code} - ${zone.name}</option>`).join('')}`;
            select.value = current;
        });
    }

    function editZona(id) {
        const zone = appState.zones.find((item) => item.id === id);
        if (!zone) return;

        document.getElementById('zonaId').value = zone.id;
        document.getElementById('zonaKode').value = zone.code || '';
        document.getElementById('zonaNama').value = zone.name || '';
        document.getElementById('zonaDeskripsi').value = zone.description || '';
        document.getElementById('zonaMultiplier').value = zone.multiplier || 1;
        document.getElementById('zonaAktif').checked = Boolean(zone.is_active);
        switchView('zona');
    }

    async function submitZona(event) {
        event.preventDefault();

        const id = document.getElementById('zonaId').value;
        const payload = {
            code: document.getElementById('zonaKode').value.trim(),
            name: document.getElementById('zonaNama').value.trim(),
            description: document.getElementById('zonaDeskripsi').value.trim() || null,
            multiplier: Number(document.getElementById('zonaMultiplier').value || 1),
            is_active: document.getElementById('zonaAktif').checked,
        };

        if (id) {
            await api(`/zones/${id}`, 'PUT', payload);
        } else {
            await api('/zones', 'POST', payload);
        }

        resetFormZona();
        await loadZones();
    }

    async function hapusZona(id) {
        if (!confirm('Hapus zona ini?')) return;
        await api(`/zones/${id}`, 'DELETE');
        await loadZones();
    }

    function resetFormZona() {
        document.getElementById('formZona').reset();
        document.getElementById('zonaId').value = '';
        document.getElementById('zonaMultiplier').value = 1;
        document.getElementById('zonaAktif').checked = true;
    }

    async function loadRateCards(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('rateCardSearch'));
        appendIf(params, 'origin_zone_id', inputValue('rateCardOriginZoneFilter'));
        appendIf(params, 'destination_zone_id', inputValue('rateCardDestinationZoneFilter'));
        appendIf(params, 'service_type', inputValue('rateCardServiceFilter'));
        appendIf(params, 'sort_by', inputValue('rateCardSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('rateCardSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('rateCardPerPage', '15'));
        params.append('page', String(page || appState.lists.rateCards.page || 1));

        const data = await api(`/rate-cards?${params.toString()}`);
        appState.rateCards = data.data || [];

        const rows = appState.rateCards.map((item) => `
            <tr>
                <td>
                    <div class="fw-semibold">${item.origin_zone?.code || '-'} - ${item.origin_zone?.name || '-'}</div>
                    <div class="small text-muted">ke ${item.destination_zone?.code || '-'} - ${item.destination_zone?.name || '-'}</div>
                </td>
                <td>${translateService(item.service_type)}</td>
                <td>${Number(item.min_weight_kg).toFixed(2)} - ${item.max_weight_kg ? Number(item.max_weight_kg).toFixed(2) : 'tanpa batas'} kg</td>
                <td>${formatCurrency(item.base_price || 0)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editRateCard(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="hapusRateCard(${item.id})">Hapus</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelRateCard').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada kartu tarif.</td></tr>';
        renderPagination('rateCardPagination', 'rateCards', data.current_page, data.last_page, 'loadRateCards');
    }

    function editRateCard(id) {
        const item = appState.rateCards.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('rateCardId').value = item.id;
        document.getElementById('rateCardOriginZona').value = item.origin_zone_id || '';
        document.getElementById('rateCardDestinationZona').value = item.destination_zone_id || item.zone_id || '';
        document.getElementById('rateCardService').value = item.service_type || 'regular';
        document.getElementById('rateCardMin').value = item.min_weight_kg || 0;
        document.getElementById('rateCardMax').value = item.max_weight_kg || '';
        document.getElementById('rateCardBase').value = item.base_price || 0;
        document.getElementById('rateCardPerKg').value = item.per_kg_price || 0;
        document.getElementById('rateCardInsurance').value = item.insurance_fee || 0;
        document.getElementById('rateCardAktif').checked = Boolean(item.is_active);
        switchView('kartu-tarif');
    }

    async function submitRateCard(event) {
        event.preventDefault();

        const id = document.getElementById('rateCardId').value;
        const rawMax = document.getElementById('rateCardMax').value;
        const payload = {
            origin_zone_id: Number(document.getElementById('rateCardOriginZona').value),
            destination_zone_id: Number(document.getElementById('rateCardDestinationZona').value),
            service_type: document.getElementById('rateCardService').value,
            min_weight_kg: Number(document.getElementById('rateCardMin').value || 0),
            max_weight_kg: rawMax === '' ? null : Number(rawMax),
            base_price: Number(document.getElementById('rateCardBase').value || 0),
            per_kg_price: Number(document.getElementById('rateCardPerKg').value || 0),
            insurance_fee: Number(document.getElementById('rateCardInsurance').value || 0),
            is_active: document.getElementById('rateCardAktif').checked,
        };

        if (id) {
            await api(`/rate-cards/${id}`, 'PUT', payload);
        } else {
            await api('/rate-cards', 'POST', payload);
        }

        resetFormRateCard();
        await loadRateCards();
    }

    async function hapusRateCard(id) {
        if (!confirm('Hapus kartu tarif ini?')) return;
        await api(`/rate-cards/${id}`, 'DELETE');
        await loadRateCards();
    }

    function resetFormRateCard() {
        document.getElementById('formRateCard').reset();
        document.getElementById('rateCardId').value = '';
        document.getElementById('rateCardAktif').checked = true;
    }

    async function loadBranches(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('branchSearch'));
        appendIf(params, 'zone_id', inputValue('branchZoneFilter'));
        appendIf(params, 'is_active', inputValue('branchActiveFilter'));
        appendIf(params, 'sort_by', inputValue('branchSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('branchSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('branchPerPage', '15'));
        params.append('page', String(page || appState.lists.branches.page || 1));

        const data = await api(`/branches?${params.toString()}`);
        appState.branches = data.data || [];

        const rows = appState.branches.map((item) => `
            <tr>
                <td>${item.code}</td>
                <td>${item.name}</td>
                <td>${item.city}</td>
                <td>${item.zone?.name || '-'}</td>
                <td>${renderBooleanStatusBadge(Boolean(item.is_active), 'Aktif', 'Nonaktif')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editCabang(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="hapusCabang(${item.id})">Hapus</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelCabang').innerHTML = rows || '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada data cabang.</td></tr>';

        populateBranchSelect();
        populateCabangZoneSelect();
        populateUserBranchSelect();
        populateShipmentFormSelects();
        populateBranchFilterOptions();
        renderPagination('branchPagination', 'branches', data.current_page, data.last_page, 'loadBranches');
    }

    function populateBranchSelect() {
        const select = document.getElementById('kendaraanCabang');
        select.innerHTML = appState.branches.map((item) => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('');
    }

    function populateBranchFilterOptions() {
        const ids = ['vehicleBranchFilter', 'shipmentBranchFilter'];
        ids.forEach((id) => {
            const select = document.getElementById(id);
            if (!select) return;
            const current = select.value;
            select.innerHTML = `<option value="">All Branch</option>${appState.branches.map((item) => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('')}`;
            select.value = current;
        });
    }

    function populateCabangZoneSelect() {
        const select = document.getElementById('cabangZona');
        select.innerHTML = `<option value="">-</option>${appState.zones.map((item) => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('')}`;
    }

    async function loadBranchPerformance() {
        try {
            const branchPerf = appState.payload?.branch_performance?.ranking || [];
            
            if (!branchPerf.length) {
                document.getElementById('branchPerformanceSection').innerHTML = '';
                return;
            }

            const perfRows = branchPerf.map((b) => `
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card-soft p-3 d-flex flex-column" style="cursor: pointer;" onclick="loadDetailBranchPerf(${b.id})">
                        <div><strong data-branch-name>${b.name}</strong></div>
                        <div class="small text-muted">${b.code}</div>
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between small">
                                <span>Shipment</span>
                                <strong>${formatNumber(b.shipment_volume || 0)}</strong>
                            </div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span>Revenue</span>
                                <strong>${formatCurrency(b.revenue_settled || 0)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            document.getElementById('branchPerformanceSection').innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">📊 Performa Cabang (Klik untuk detail)</h6>
                    <button class="btn btn-sm btn-kondang-secondary" onclick="loadBranchPerformance()">Refresh</button>
                </div>
                <div class="row g-2">
                    ${perfRows}
                </div>
                <div id="branchPerfDetail" class="mt-3"></div>
            `;
        } catch (e) {
            console.error('Error loading branch performance:', e);
            document.getElementById('branchPerformanceSection').innerHTML = '<div class="alert alert-danger">Error loading performance data</div>';
        }
    }

    async function loadDetailBranchPerf(branchId) {
        const detail = await api(`/reports/branch/${branchId}`);
        renderDetailBranchPerf(detail);
    }

    function renderDetailBranchPerf(detail) {
        const shipmentsByStatus = detail.shipments_by_status || [];
        const recentShipments = detail.recent_shipments || [];
        const topCouriers = detail.top_couriers || [];

        const statusLabels = shipmentsByStatus.map(s => `"${s.code}"`).join(', ');
        const statusCounts = shipmentsByStatus.map(s => s.total).join(', ');

        const html = `
            <div class="surface-box mt-3">
                <h6 class="mb-3">${detail.name} (${detail.code})</h6>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Total Shipment</div><div class="metric-value">${formatNumber(detail.shipments_total || 0)}</div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Revenue</div><div class="metric-value">${formatCurrency(detail.revenue_total || 0)}</div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Payment Pending</div><div class="metric-value">${formatCurrency(detail.payments_pending || 0)}</div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="metric-box"><div class="metric-label">Vehicles / Users</div><div class="metric-value">${detail.vehicles_count || 0} / ${detail.users_count || 0}</div></div></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-6">
                        <div class="surface-box">
                            <h6 class="mb-2">Status Distribution</h6>
                            <div class="chart-box">
                                <canvas id="chartBranchStatus_${detail.id}"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="surface-box">
                            <h6 class="mb-2">Top 5 Kurir</h6>
                            <div class="table-responsive small">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Nama</th><th>Terkirim</th></tr></thead>
                                    <tbody>
                                        ${topCouriers.map(c => `
                                            <tr>
                                                <td>${c.name}</td>
                                                <td><strong>${formatNumber(c.delivered)}</strong></td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="2" class="text-muted text-center py-2">-</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="surface-box">
                            <h6 class="mb-2">Shipment Terbaru (10)</h6>
                            <div class="table-responsive small">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Tracking</th><th>Kurir</th><th>Status</th><th>Tanggal</th></tr></thead>
                                    <tbody>
                                        ${recentShipments.map(s => `
                                            <tr>
                                                <td>${s.tracking_number}</td>
                                                <td>${s.courier || '-'}</td>
                                                <td>${renderStatusBadge(s.status, s.status)}</td>
                                                <td>${new Date(s.created_at).toLocaleDateString('id-ID')}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="4" class="text-muted text-center py-2">-</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('branchPerfDetail').innerHTML = html;

        // Render chart
        setTimeout(() => {
            const ctx = document.getElementById(`chartBranchStatus_${detail.id}`);
            if (ctx && appState.charts[`branchStatus_${detail.id}`]) {
                appState.charts[`branchStatus_${detail.id}`].destroy();
            }
            if (ctx) {
                appState.charts[`branchStatus_${detail.id}`] = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: shipmentsByStatus.map(s => s.name),
                        datasets: [{
                            data: shipmentsByStatus.map(s => s.total),
                            backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    },
                });
            }
        }, 100);
    }

    function editCabang(id) {
        const item = appState.branches.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('cabangId').value = item.id;
        document.getElementById('cabangKode').value = item.code || '';
        document.getElementById('cabangNama').value = item.name || '';
        document.getElementById('cabangKota').value = item.city || '';
        document.getElementById('cabangZona').value = item.zone_id || '';
        document.getElementById('cabangTelepon').value = item.phone || '';
        document.getElementById('cabangEmail').value = item.email || '';
        document.getElementById('cabangAlamat').value = item.address || '';
        document.getElementById('cabangAktif').checked = Boolean(item.is_active);
        switchView('cabang');
    }

    async function submitCabang(event) {
        event.preventDefault();

        const id = document.getElementById('cabangId').value;
        const payload = {
            code: document.getElementById('cabangKode').value.trim(),
            name: document.getElementById('cabangNama').value.trim(),
            city: document.getElementById('cabangKota').value.trim(),
            zone_id: document.getElementById('cabangZona').value ? Number(document.getElementById('cabangZona').value) : null,
            phone: document.getElementById('cabangTelepon').value.trim() || null,
            email: document.getElementById('cabangEmail').value.trim() || null,
            address: document.getElementById('cabangAlamat').value.trim(),
            is_active: document.getElementById('cabangAktif').checked,
        };

        if (id) {
            await api(`/branches/${id}`, 'PUT', payload);
        } else {
            await api('/branches', 'POST', payload);
        }

        resetFormCabang();
        await loadBranches();
    }

    async function hapusCabang(id) {
        if (!confirm('Hapus cabang ini?')) return;
        await api(`/branches/${id}`, 'DELETE');
        await loadBranches();
    }

    function resetFormCabang() {
        document.getElementById('formCabang').reset();
        document.getElementById('cabangId').value = '';
        document.getElementById('cabangAktif').checked = true;
        populateCabangZoneSelect();
    }

    async function loadVehicles(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('vehicleSearch'));
        appendIf(params, 'branch_id', inputValue('vehicleBranchFilter'));
        appendIf(params, 'status', inputValue('vehicleStatusFilter'));
        appendIf(params, 'sort_by', inputValue('vehicleSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('vehicleSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('vehiclePerPage', '15'));
        params.append('page', String(page || appState.lists.vehicles.page || 1));

        const data = await api(`/vehicles?${params.toString()}`);
        appState.vehicles = data.data || [];

        const rows = appState.vehicles.map((item) => `
            <tr>
                <td>${item.plate_number}</td>
                <td>${item.name}</td>
                <td>${item.branch?.name || '-'}</td>
                <td>${renderStatusBadge(statusLabel[item.status] || item.status, item.status)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editKendaraan(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="hapusKendaraan(${item.id})">Hapus</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelKendaraan').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada data kendaraan.</td></tr>';
        renderPagination('vehiclePagination', 'vehicles', data.current_page, data.last_page, 'loadVehicles');
        populateShipmentFormSelects();
    }

    function editKendaraan(id) {
        const item = appState.vehicles.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('kendaraanId').value = item.id;
        document.getElementById('kendaraanCabang').value = item.branch_id || '';
        document.getElementById('kendaraanNama').value = item.name || '';
        document.getElementById('kendaraanPlat').value = item.plate_number || '';
        document.getElementById('kendaraanTipe').value = item.type || 'motorcycle';
        document.getElementById('kendaraanKapasitas').value = item.capacity_kg || 0;
        document.getElementById('kendaraanStatus').value = item.status || 'available';
        switchView('kendaraan');
    }

    async function submitKendaraan(event) {
        event.preventDefault();

        const id = document.getElementById('kendaraanId').value;
        const payload = {
            branch_id: Number(document.getElementById('kendaraanCabang').value),
            name: document.getElementById('kendaraanNama').value.trim(),
            plate_number: document.getElementById('kendaraanPlat').value.trim(),
            type: document.getElementById('kendaraanTipe').value,
            capacity_kg: Number(document.getElementById('kendaraanKapasitas').value || 0),
            status: document.getElementById('kendaraanStatus').value,
        };

        if (id) {
            await api(`/vehicles/${id}`, 'PUT', payload);
        } else {
            await api('/vehicles', 'POST', payload);
        }

        resetFormKendaraan();
        await loadVehicles();
    }

    async function hapusKendaraan(id) {
        if (!confirm('Hapus kendaraan ini?')) return;
        await api(`/vehicles/${id}`, 'DELETE');
        await loadVehicles();
    }

    function resetFormKendaraan() {
        document.getElementById('formKendaraan').reset();
        document.getElementById('kendaraanId').value = '';
    }

    async function loadCustomers() {
        const data = await api('/customers');
        appState.customers = data.data || [];
        populateCustomerSelects();
    }

    async function loadShipmentStatuses() {
        const data = await api('/shipment-statuses');
        appState.shipmentStatuses = data.data || [];
        populateShipmentStatusSelect();
        populateShipmentFilterOptions();
    }

    function populateUserBranchSelect() {
        const select = document.getElementById('userBranch');
        select.innerHTML = `<option value="">-</option>${appState.branches.map((item) => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('')}`;
    }

    function populateCustomerSelects() {
        const options = `<option value="">-</option>${appState.customers.map((item) => `<option value="${item.id}">${item.name}</option>`).join('')}`;
        document.getElementById('shipmentCustomer').innerHTML = options;
        document.getElementById('paymentCustomer').innerHTML = options;
    }

    function populateCourierSelect() {
        const couriers = appState.users.filter((item) => item.role === 'courier');
        const options = `<option value="">-</option>${couriers.map((item) => `<option value="${item.id}">${item.name}</option>`).join('')}`;
        document.getElementById('shipmentCourier').innerHTML = options;
    }

    function populateShipmentStatusSelect() {
        const select = document.getElementById('shipmentStatusId');
        select.innerHTML = `<option value="">-</option>${appState.shipmentStatuses.map((item) => `<option value="${item.id}">${item.name}</option>`).join('')}`;
    }

    function populateShipmentFilterOptions() {
        const select = document.getElementById('shipmentStatusFilter');
        if (!select) return;
        const current = select.value;
        select.innerHTML = `<option value="">All Status</option>${appState.shipmentStatuses.map((item) => `<option value="${item.id}">${item.name}</option>`).join('')}`;
        select.value = current;
    }

    function populateShipmentFormSelects() {
        const originBranchSelect = document.getElementById('shipmentBranch');
        const destinationBranchSelect = document.getElementById('shipmentDestinationBranch');
        const isStaffLockedBranch = ['manager', 'kasir'].includes(appState.role) && appState.branchId;

        if (isStaffLockedBranch) {
            const branch = appState.branches.find((item) => Number(item.id) === Number(appState.branchId)) || appState.branch;
            const originOption = branch ? `<option value="${branch.id}">${branch.code} - ${branch.name}</option>` : '';
            originBranchSelect.innerHTML = originOption;
            originBranchSelect.value = branch?.id || appState.branchId || '';
            originBranchSelect.disabled = true;
        } else {
            originBranchSelect.innerHTML = appState.branches.map((item) => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('');
            originBranchSelect.disabled = false;
        }
        document.getElementById('shipmentDestinationBranch').innerHTML = appState.branches.map((item) => `<option value="${item.id}" data-zone-id="${item.zone_id || ''}">${item.code} - ${item.name}</option>`).join('');
        document.getElementById('shipmentZone').innerHTML = appState.zones.map((item) => `<option value="${item.id}">${item.code} - ${item.name}</option>`).join('');
        document.getElementById('shipmentVehicle').innerHTML = `<option value="">-</option>${appState.vehicles.map((item) => `<option value="${item.id}">${item.plate_number} - ${item.name}</option>`).join('')}`;
        populateCourierSelect();
        syncShipmentDestinationZone();
    }

    function syncShipmentDestinationZone() {
        const destinationSelect = document.getElementById('shipmentDestinationBranch');
        const zoneSelect = document.getElementById('shipmentZone');
        if (!destinationSelect || !zoneSelect) return;

        const selected = destinationSelect.options[destinationSelect.selectedIndex];
        const zoneId = selected?.dataset?.zoneId || '';
        zoneSelect.value = zoneId;
    }

    function populatePaymentShipmentSelect() {
        const select = document.getElementById('paymentShipment');
        select.innerHTML = appState.shipments.map((item) => `<option value="${item.id}">${item.tracking_number || ('Shipment #' + item.id)}</option>`).join('');
    }

    async function loadUsersCrud(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('userSearch'));
        appendIf(params, 'role', inputValue('userRoleFilter'));
        appendIf(params, 'is_active', inputValue('userActiveFilter'));
        appendIf(params, 'sort_by', inputValue('userSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('userSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('userPerPage', '15'));
        params.append('page', String(page || appState.lists.users.page || 1));

        const data = await api(`/users?${params.toString()}`);
        appState.users = data.data || [];
        const canManageUsers = appState.role === 'admin';

        const rows = appState.users.map((item) => `
            <tr>
                <td>${item.name}</td>
                <td>${item.email}</td>
                <td><span class="badge text-bg-primary">${item.role}</span></td>
                <td>${renderBooleanStatusBadge(Boolean(item.is_active), 'Active', 'Inactive')}</td>
                <td>
                    ${canManageUsers
                        ? `<button class="btn btn-sm btn-outline-primary me-1" onclick="editUser(${item.id})">Edit</button>
                           <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${item.id})">Delete</button>`
                        : '<span class="small text-muted">View only</span>'}
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelUser').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted py-3">No user data.</td></tr>';

        populateUserBranchSelect();
        populateCourierSelect();
        renderPagination('userPagination', 'users', data.current_page, data.last_page, 'loadUsersCrud');
    }

    function editUser(id) {
        const item = appState.users.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('userId').value = item.id;
        document.getElementById('userName').value = item.name || '';
        document.getElementById('userEmail').value = item.email || '';
        document.getElementById('userRole').value = item.role || 'customer';
        document.getElementById('userBranch').value = item.branch_id || '';
        document.getElementById('userPhone').value = item.phone || '';
        document.getElementById('userAddress').value = item.address || '';
        document.getElementById('userPassword').value = '';
        document.getElementById('userPassword').required = false;
        document.getElementById('userActive').checked = Boolean(item.is_active);
        switchView('user');
    }

    async function submitUser(event) {
        event.preventDefault();

        if (appState.role !== 'admin') {
            alert('Anda hanya memiliki akses lihat data user.');
            return;
        }

        const id = document.getElementById('userId').value;
        const password = document.getElementById('userPassword').value;
        const payload = {
            name: document.getElementById('userName').value.trim(),
            email: document.getElementById('userEmail').value.trim(),
            role: document.getElementById('userRole').value,
            branch_id: document.getElementById('userBranch').value ? Number(document.getElementById('userBranch').value) : null,
            phone: document.getElementById('userPhone').value.trim() || null,
            address: document.getElementById('userAddress').value.trim() || null,
            is_active: document.getElementById('userActive').checked,
        };

        if (password) payload.password = password;
        if (!id && !password) {
            alert('Password wajib saat create user.');
            return;
        }

        if (id) {
            await api(`/users/${id}`, 'PUT', payload);
        } else {
            await api('/users', 'POST', payload);
        }

        resetFormUser();
        await loadUsersCrud();
    }

    async function deleteUser(id) {
        if (appState.role !== 'admin') {
            alert('Anda hanya memiliki akses lihat data user.');
            return;
        }

        if (!confirm('Delete user ini?')) return;
        await api(`/users/${id}`, 'DELETE');
        await loadUsersCrud();
    }

    function resetFormUser() {
        document.getElementById('formUser').reset();
        document.getElementById('userId').value = '';
        document.getElementById('userPassword').required = true;
        document.getElementById('userActive').checked = true;
        populateUserBranchSelect();
    }

    async function loadShipmentsCrud(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('shipmentSearch'));
        appendIf(params, 'branch_id', inputValue('shipmentBranchFilter'));
        appendIf(params, 'status_id', inputValue('shipmentStatusFilter'));
        appendIf(params, 'sort_by', inputValue('shipmentSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('shipmentSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('shipmentPerPage', '15'));
        params.append('page', String(page || appState.lists.shipments.page || 1));

        const data = await api(`/shipments?${params.toString()}`);
        appState.shipments = data.data || [];

        const rows = appState.shipments.map((item) => `
            <tr>
                <td>${item.tracking_number || '-'}</td>
                <td>${item.recipient_name || '-'}</td>
                <td>${renderStatusBadge(item.status?.name || '-', item.status?.code || item.status?.name)}</td>
                <td>${item.branch?.name || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary me-1" onclick="printShipmentLabel(${item.id})">Cetak Resi</button>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editShipment(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteShipment(${item.id})">Delete</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelShipment').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted py-3">No shipment data.</td></tr>';

        populateShipmentFormSelects();
        populatePaymentShipmentSelect();
        populateShipmentFilterOptions();
        renderPagination('shipmentPagination', 'shipments', data.current_page, data.last_page, 'loadShipmentsCrud');
    }

    function editShipment(id) {
        const item = appState.shipments.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('shipmentId').value = item.id;
        document.getElementById('shipmentCustomer').value = item.customer_id || '';
        document.getElementById('shipmentBranch').value = item.branch_id || appState.branchId || '';
        const fallbackDestinationBranch = item.destinationBranch?.id || appState.branches.find((branch) => Number(branch.zone_id) === Number(item.zone_id))?.id || '';
        document.getElementById('shipmentDestinationBranch').value = item.destination_branch_id || fallbackDestinationBranch;
        document.getElementById('shipmentCourier').value = item.courier_id || '';
        document.getElementById('shipmentVehicle').value = item.vehicle_id || '';
        document.getElementById('shipmentZone').value = item.zone_id || '';
        document.getElementById('shipmentService').value = item.service_type || 'regular';
        document.getElementById('senderName').value = item.sender_name || '';
        document.getElementById('senderPhone').value = item.sender_phone || '';
        document.getElementById('senderAddress').value = item.sender_address || '';
        document.getElementById('recipientName').value = item.recipient_name || '';
        document.getElementById('recipientPhone').value = item.recipient_phone || '';
        document.getElementById('recipientAddress').value = item.recipient_address || '';
        document.getElementById('shipmentWeight').value = item.total_weight_kg || 0.1;
        document.getElementById('shipmentVolume').value = item.total_volume || '';
        document.getElementById('shipmentStatusId').value = item.status_id || '';
        document.getElementById('shipmentPaymentStatus').value = item.payment_status || 'unpaid';
        document.getElementById('shipmentEta').value = toDatetimeLocal(item.estimated_delivery_at);
        document.getElementById('shipmentNotes').value = item.notes || '';
        syncShipmentDestinationZone();
        switchView('shipment');
    }

    async function submitShipment(event) {
        event.preventDefault();

        syncShipmentDestinationZone();
        const destinationBranchId = Number(document.getElementById('shipmentDestinationBranch').value || 0);
        const destinationZoneId = Number(document.getElementById('shipmentZone').value || 0);
        const originBranchId = Number(document.getElementById('shipmentBranch').value || appState.branchId || 0);

        if (!originBranchId || !destinationBranchId || !destinationZoneId) {
            alert('Pilih cabang tujuan yang memiliki zona aktif.');
            return;
        }

        const id = document.getElementById('shipmentId').value;
        const payloadCreate = {
            customer_id: document.getElementById('shipmentCustomer').value ? Number(document.getElementById('shipmentCustomer').value) : null,
            branch_id: originBranchId,
            destination_branch_id: destinationBranchId,
            courier_id: document.getElementById('shipmentCourier').value ? Number(document.getElementById('shipmentCourier').value) : null,
            vehicle_id: document.getElementById('shipmentVehicle').value ? Number(document.getElementById('shipmentVehicle').value) : null,
            sender_name: document.getElementById('senderName').value.trim(),
            sender_phone: document.getElementById('senderPhone').value.trim(),
            sender_address: document.getElementById('senderAddress').value.trim(),
            recipient_name: document.getElementById('recipientName').value.trim(),
            recipient_phone: document.getElementById('recipientPhone').value.trim(),
            recipient_address: document.getElementById('recipientAddress').value.trim(),
            service_type: document.getElementById('shipmentService').value,
            total_weight_kg: Number(document.getElementById('shipmentWeight').value || 0.1),
            zone_id: destinationZoneId,
            total_volume: document.getElementById('shipmentVolume').value ? Number(document.getElementById('shipmentVolume').value) : null,
            estimated_delivery_at: document.getElementById('shipmentEta').value || null,
            notes: document.getElementById('shipmentNotes').value.trim() || null,
        };

        const payloadUpdate = {
            destination_branch_id: destinationBranchId,
            courier_id: payloadCreate.courier_id,
            vehicle_id: payloadCreate.vehicle_id,
            status_id: document.getElementById('shipmentStatusId').value ? Number(document.getElementById('shipmentStatusId').value) : null,
            payment_status: document.getElementById('shipmentPaymentStatus').value,
            service_type: document.getElementById('shipmentService').value,
            total_weight_kg: Number(document.getElementById('shipmentWeight').value || 0.1),
            total_volume: document.getElementById('shipmentVolume').value ? Number(document.getElementById('shipmentVolume').value) : null,
            sender_name: document.getElementById('senderName').value.trim(),
            sender_phone: document.getElementById('senderPhone').value.trim(),
            sender_address: document.getElementById('senderAddress').value.trim(),
            recipient_name: document.getElementById('recipientName').value.trim(),
            recipient_phone: document.getElementById('recipientPhone').value.trim(),
            recipient_address: document.getElementById('recipientAddress').value.trim(),
            insurance_amount: document.getElementById('shipmentInsuranceAmount') ? Number(document.getElementById('shipmentInsuranceAmount').value || 0) : null,
            admin_fee: document.getElementById('shipmentAdminFee') ? Number(document.getElementById('shipmentAdminFee').value || 0) : null,
            estimated_delivery_at: payloadCreate.estimated_delivery_at,
            notes: payloadCreate.notes,
        };

        if (id) {
            await api(`/shipments/${id}`, 'PUT', payloadUpdate);
        } else {
            await api('/shipments', 'POST', payloadCreate);
        }

        resetFormShipment();
        await loadShipmentsCrud();
    }

    async function deleteShipment(id) {
        if (!confirm('Delete shipment ini?')) return;
        await api(`/shipments/${id}`, 'DELETE');
        await loadShipmentsCrud();
    }

    function printShipmentLabel(id) {
        window.open(`/shipments/${id}/label`, '_blank', 'noopener');
    }

    function resetFormShipment() {
        document.getElementById('formShipment').reset();
        document.getElementById('shipmentId').value = '';
        populateShipmentFormSelects();
        populateCustomerSelects();
        populateShipmentStatusSelect();
        syncShipmentDestinationZone();
    }

    async function loadPaymentsCrud(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('paymentSearch'));
        appendIf(params, 'status', inputValue('paymentStatusFilter'));
        appendIf(params, 'method', inputValue('paymentMethodFilter'));
        appendIf(params, 'sort_by', inputValue('paymentSortBy', 'created_at'));
        appendIf(params, 'sort_dir', inputValue('paymentSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('paymentPerPage', '15'));
        params.append('page', String(page || appState.lists.payments.page || 1));

        const data = await api(`/payments?${params.toString()}`);
        appState.payments = data.data || [];

        const rows = appState.payments.map((item) => `
            <tr>
                <td>${item.id}</td>
                <td>${item.shipment?.tracking_number || item.shipment_id || '-'}</td>
                <td>${item.method || '-'}</td>
                <td>${renderStatusBadge(item.status || '-', item.status)}</td>
                <td>${formatCurrency(item.amount || 0)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editPayment(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deletePayment(${item.id})">Delete</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelPayment').innerHTML = rows || '<tr><td colspan="6" class="text-center text-muted py-3">No payment data.</td></tr>';

        populatePaymentShipmentSelect();
        populateCustomerSelects();
        renderPagination('paymentPagination', 'payments', data.current_page, data.last_page, 'loadPaymentsCrud');
    }

    function editPayment(id) {
        const item = appState.payments.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('paymentId').value = item.id;
        document.getElementById('paymentShipment').value = item.shipment_id || '';
        document.getElementById('paymentCustomer').value = item.customer_id || '';
        document.getElementById('paymentMethod').value = item.method || 'midtrans';
        document.getElementById('paymentStatus').value = item.status || 'pending';
        document.getElementById('paymentAmount').value = item.amount || 0;
        document.getElementById('paymentNotes').value = item.notes || '';
        switchView('payment');
    }

    async function submitPayment(event) {
        event.preventDefault();

        const id = document.getElementById('paymentId').value;
        const payloadCreate = {
            shipment_id: Number(document.getElementById('paymentShipment').value),
            customer_id: document.getElementById('paymentCustomer').value ? Number(document.getElementById('paymentCustomer').value) : null,
            method: document.getElementById('paymentMethod').value,
            amount: Number(document.getElementById('paymentAmount').value || 0),
            notes: document.getElementById('paymentNotes').value.trim() || null,
        };

        const payloadUpdate = {
            status: document.getElementById('paymentStatus').value,
            method: payloadCreate.method,
            notes: payloadCreate.notes,
        };

        if (id) {
            await api(`/payments/${id}`, 'PUT', payloadUpdate);
        } else {
            await api('/payments', 'POST', payloadCreate);
        }

        resetFormPayment();
        await loadPaymentsCrud();
    }

    async function deletePayment(id) {
        if (!confirm('Delete payment ini?')) return;
        await api(`/payments/${id}`, 'DELETE');
        await loadPaymentsCrud();
    }

    function resetFormPayment() {
        document.getElementById('formPayment').reset();
        document.getElementById('paymentId').value = '';
        populatePaymentShipmentSelect();
        populateCustomerSelects();
    }

    async function loadLandingContentsCrud(page = null) {
        const params = new URLSearchParams();
        appendIf(params, 'search', inputValue('landingSearch'));
        appendIf(params, 'section', inputValue('landingSectionFilter'));
        appendIf(params, 'is_active', inputValue('landingActiveFilter'));
        appendIf(params, 'sort_by', inputValue('landingSortBy', 'sort_order'));
        appendIf(params, 'sort_dir', inputValue('landingSortDir', 'desc'));
        appendIf(params, 'per_page', inputValue('landingPerPage', '20'));
        params.append('page', String(page || appState.lists.landingContents.page || 1));

        const data = await api(`/landing-page-contents?${params.toString()}`);
        appState.landingContents = data.data || [];

        const rows = appState.landingContents.map((item) => `
            <tr>
                <td>${item.section}</td>
                <td>${item.title || '-'}</td>
                <td>${item.sort_order ?? 0}</td>
                <td>${renderBooleanStatusBadge(Boolean(item.is_active), 'Active', 'Inactive')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editLandingContent(${item.id})">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteLandingContent(${item.id})">Delete</button>
                </td>
            </tr>
        `).join('');
        document.getElementById('tabelLandingContent').innerHTML = rows || '<tr><td colspan="5" class="text-center text-muted py-3">No content data.</td></tr>';
        renderPagination('landingPagination', 'landingContents', data.current_page, data.last_page, 'loadLandingContentsCrud');
    }

    function editLandingContent(id) {
        const item = appState.landingContents.find((row) => row.id === id);
        if (!item) return;

        document.getElementById('landingContentId').value = item.id;
        document.getElementById('landingSection').value = item.section || 'feature';
        document.getElementById('landingSortOrder').value = item.sort_order ?? 0;
        document.getElementById('landingTitle').value = item.title || '';
        document.getElementById('landingSubtitle').value = item.subtitle || '';
        document.getElementById('landingBody').value = item.content || '';
        document.getElementById('landingCtaLabel').value = item.cta_label || '';
        document.getElementById('landingCtaUrl').value = item.cta_url || '';
        document.getElementById('landingImageUrl').value = item.image_url || '';
        document.getElementById('landingMetadata').value = item.metadata ? JSON.stringify(item.metadata, null, 2) : '';
        document.getElementById('landingActive').checked = Boolean(item.is_active);
        switchView('landing-content');
    }

    async function submitLandingContent(event) {
        event.preventDefault();

        const id = document.getElementById('landingContentId').value;
        const metadataRaw = document.getElementById('landingMetadata').value.trim();
        let metadata = null;

        if (metadataRaw) {
            try {
                metadata = JSON.parse(metadataRaw);
            } catch (error) {
                alert('Metadata harus valid JSON.');
                return;
            }
        }

        const payload = {
            section: document.getElementById('landingSection').value,
            sort_order: Number(document.getElementById('landingSortOrder').value || 0),
            title: document.getElementById('landingTitle').value.trim() || null,
            subtitle: document.getElementById('landingSubtitle').value.trim() || null,
            content: document.getElementById('landingBody').value.trim() || null,
            cta_label: document.getElementById('landingCtaLabel').value.trim() || null,
            cta_url: document.getElementById('landingCtaUrl').value.trim() || null,
            image_url: document.getElementById('landingImageUrl').value.trim() || null,
            is_active: document.getElementById('landingActive').checked,
            metadata,
        };

        if (id) {
            await api(`/landing-page-contents/${id}`, 'PUT', payload);
        } else {
            await api('/landing-page-contents', 'POST', payload);
        }

        resetFormLandingContent();
        await loadLandingContentsCrud();
    }

    async function deleteLandingContent(id) {
        if (!confirm('Delete content ini?')) return;
        await api(`/landing-page-contents/${id}`, 'DELETE');
        await loadLandingContentsCrud();
    }

    function resetFormLandingContent() {
        document.getElementById('formLandingContent').reset();
        document.getElementById('landingContentId').value = '';
        document.getElementById('landingSortOrder').value = 0;
        document.getElementById('landingActive').checked = true;
    }

    function toDatetimeLocal(value) {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '';
        const tzOffset = date.getTimezoneOffset() * 60000;
        return new Date(date.getTime() - tzOffset).toISOString().slice(0, 16);
    }

    async function api(url, method = 'GET', body = null) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : null,
        });

        if (!response.ok) {
            let message = `Request gagal (${response.status})`;
            try {
                const err = await response.json();
                if (err.message) message = err.message;
            } catch (e) {
                // Abaikan parse error jika respons bukan JSON.
            }
            alert(message);
            throw new Error(message);
        }

        return response.json();
    }

    function translateService(type) {
        const map = {
            regular: 'Reguler',
            express: 'Express',
            same_day: 'Same Day',
            economy: 'Ekonomi',
        };
        return map[type] || type;
    }

    function toTitle(value) {
        return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value || 0));
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(Number(value || 0));
    }

    function formatShortCurrency(value) {
        const number = Number(value || 0);
        if (number >= 1000000000) return `Rp${(number / 1000000000).toFixed(1)} M`;
        if (number >= 1000000) return `Rp${(number / 1000000).toFixed(1)} Jt`;
        if (number >= 1000) return `Rp${(number / 1000).toFixed(1)} Rb`;
        return `Rp${number}`;
    }
</script>
</body>
</html>
