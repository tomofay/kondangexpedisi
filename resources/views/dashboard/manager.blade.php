<x-app-layout>
    <style>
        .dashboard-container {
            padding: 1.5rem;
        }
        
        .card-pro {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 97, 255, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .card-pro:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 97, 255, 0.08);
            border-color: #ebf3ff;
        }
        
        .welcome-card {
            background: radial-gradient(circle at top right, #eef2ff 0%, #ffffff 60%);
            border-left: 6px solid #4F46E5; /* Indigo 600 */
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.8rem;
        }

        .stat-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            font-family: 'Sora', sans-serif;
            line-height: 1.1;
        }

        .action-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem;
            border-radius: 16px;
            background: #F8FAFC;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .action-link:hover {
            background: white;
            border-color: #4F46E5;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.1);
            transform: translateY(-2px);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
        }
    </style>

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Manager Dashboard</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Analitik & Performa Wilayah</div>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark border border-secondary px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>{{ $metrics['branch_name'] ?? 'Cabang Utama' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="card-pro welcome-card p-4 p-lg-5 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-2 text-dark" style="font-family: 'Sora', sans-serif;">Operational Oversight</h3>
                    <p class="text-muted mb-0">Pastikan tidak ada shipment overdue dan monitor kualitas layanan di seluruh jaringan cabang hari ini.</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="bi bi-graph-up-arrow" style="font-size: 4rem; color: #4F46E5; opacity: 0.15;"></i>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-4">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #EBF3FF; color: var(--primary);">
                        <i class="bi bi-truck-flatbed"></i>
                    </div>
                    <div>
                        <div class="stat-label">Shipment Berjalan</div>
                        <div class="stat-value">{{ number_format($metrics['shipments_in_progress'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-alarm-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Shipment Overdue</div>
                        <div class="stat-value" style="color: #DC2626;">{{ number_format($metrics['shipments_overdue'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #FFFBEB; color: #F59E0B;">
                        <i class="bi bi-layers-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Approval Antri</div>
                        <div class="stat-value" style="color: #F59E0B;">{{ number_format($metrics['pending_approvals'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Financial Insight -->
            <div class="col-lg-5">
                <div class="card-pro p-4 p-lg-5 h-100">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-3 text-dark">
                        <div class="stat-icon-wrapper" style="width: 45px; height: 45px; background: #ECFDF5; color: #059669; font-size: 1.2rem;">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        Wawasan Keuangan
                    </h5>
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="p-4 rounded-4" style="background: #ECFDF5; border: 1px solid #A7F3D0;">
                            <div class="fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase; color: #059669;">
                                Settlement Hari Ini
                            </div>
                            <h3 class="fw-bold m-0" style="font-size: 1.8rem; color: #047857; font-family: 'Sora', sans-serif;">
                                Rp {{ number_format($metrics['revenue_settlement_today'] ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-4 rounded-4" style="background: #F8FAFC;">
                            <div class="fw-bold mb-1 text-muted" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">
                                Payment Pending
                            </div>
                            <h4 class="fw-bold m-0 text-dark" style="font-family: 'Sora', sans-serif;">
                                {{ number_format($metrics['payments_pending'] ?? 0) }} Transaksi
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Strategic Actions -->
            <div class="col-lg-7">
                <div class="card-pro p-4 p-lg-5 h-100">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-3 text-dark">
                        <div class="stat-icon-wrapper" style="width: 45px; height: 45px; background: #EEF2FF; color: #4F46E5; font-size: 1.2rem;">
                            <i class="bi bi-lightning-fill"></i>
                        </div>
                        Aksi Strategis
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('reports.summary') }}" class="action-link">
                                <div class="action-icon" style="background: #4F46E5;"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
                                <div>
                                    <div class="mb-0">Ops Summary</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Ringkasan eksekutif</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('approvals.index') }}" class="action-link">
                                <div class="action-icon" style="background: #D97706;"><i class="bi bi-check2-all"></i></div>
                                <div>
                                    <div class="mb-0">Pusat Approval</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Validasi operasional</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('reports.branch-performance') }}" class="action-link">
                                <div class="action-icon" style="background: var(--primary);"><i class="bi bi-building-up"></i></div>
                                <div>
                                    <div class="mb-0">Kinerja Cabang</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Pantau SLA wilayah</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('reports.daily-reconciliation') }}" class="action-link">
                                <div class="action-icon" style="background: #059669;"><i class="bi bi-clipboard2-data-fill"></i></div>
                                <div>
                                    <div class="mb-0">Rekonsiliasi</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Investigasi keuangan</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
