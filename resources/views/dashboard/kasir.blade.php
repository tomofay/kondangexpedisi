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
            background: radial-gradient(circle at top right, #f0fdf4 0%, #ffffff 60%);
            border-left: 6px solid #0D9488; /* Teal 600 */
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
            border-color: #0D9488;
            box-shadow: 0 10px 20px rgba(13, 148, 136, 0.1);
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
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Counter Dashboard</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Layanan Transaksi Cabang</div>
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
                    <h3 class="fw-bold mb-2 text-dark" style="font-family: 'Sora', sans-serif;">Semangat Melayani!</h3>
                    <p class="text-muted mb-0">Monitor transaksi masuk, proses pembayaran, dan pastikan data shipment cabang akurat hari ini.</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="bi bi-shop-window" style="font-size: 4rem; color: #0D9488; opacity: 0.15;"></i>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #EBF3FF; color: var(--primary);">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Entry Hari Ini</div>
                        <div class="stat-value">{{ number_format($metrics['shipments_today'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #FFFBEB; color: #F59E0B;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Pending Payment</div>
                        <div class="stat-value" style="color: #F59E0B;">{{ number_format($metrics['payments_pending'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #ECFDF5; color: #059669;">
                        <i class="bi bi-check-all"></i>
                    </div>
                    <div>
                        <div class="stat-label">Settled Today</div>
                        <div class="stat-value" style="color: #059669;">{{ number_format($metrics['payments_settlement_today'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card-pro stat-card h-100">
                    <div class="stat-icon-wrapper" style="background: #F0F9FF; color: #4338CA;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Siap Pick-up</div>
                        <div class="stat-value" style="color: #4338CA;">{{ number_format($metrics['shipments_pending'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Revenue -->
            <div class="col-lg-5">
                <div class="card-pro p-4 p-lg-5 h-100" style="background: #059669; color: white;">
                    <div class="d-flex flex-column h-100 justify-content-between">
                        <div>
                            <div class="fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; color: #D1FAE5;">
                                Total Setoran Tunai & Digital (Hari Ini)
                            </div>
                            <h2 class="fw-bold mt-2" style="font-size: 2.8rem; font-family: 'Sora', sans-serif;">
                                Rp {{ number_format($metrics['revenue_settlement_today'] ?? 0, 0, ',', '.') }}
                            </h2>
                        </div>
                        <div class="text-end mt-4">
                            <i class="bi bi-cash-coin" style="font-size: 5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fast Actions -->
            <div class="col-lg-7">
                <div class="card-pro p-4 p-lg-5 h-100">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-3 text-dark">
                        <div class="stat-icon-wrapper" style="width: 45px; height: 45px; background: #ECFDF5; color: #059669; font-size: 1.2rem;">
                            <i class="bi bi-grid-fill"></i>
                        </div>
                        Menu Cepat Operasional
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('shipments.index') }}" class="action-link">
                                <div class="action-icon" style="background: #1E293B;"><i class="bi bi-box-arrow-in-right"></i></div>
                                <div>
                                    <div class="mb-0">Kelola Kiriman</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Input & cetak resi</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('payments.index') }}" class="action-link">
                                <div class="action-icon" style="background: var(--primary);"><i class="bi bi-credit-card-2-front-fill"></i></div>
                                <div>
                                    <div class="mb-0">Proses Bayar</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Konfirmasi pembayaran</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('reports.payment-overview') }}" class="action-link">
                                <div class="action-icon" style="background: #D97706;"><i class="bi bi-journal-check"></i></div>
                                <div>
                                    <div class="mb-0">Buku Kas</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Ringkasan payment harian</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('reports.daily-reconciliation') }}" class="action-link">
                                <div class="action-icon" style="background: #059669;"><i class="bi bi-clipboard2-data-fill"></i></div>
                                <div>
                                    <div class="mb-0">Rekonsiliasi</div>
                                    <div class="small text-muted" style="font-size: 0.75rem; font-weight: 500;">Validasi setoran kasir</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
