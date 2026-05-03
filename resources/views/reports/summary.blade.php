<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Laporan Ringkasan</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Analitik Performa Operasional</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.summary.export', request()->query()) }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-download me-2"></i>Ekspor CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-4 border border-gray-100">
                <div class="p-4 border-bottom bg-light">
                    <form id="filter-form" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Dari Tanggal</label>
                            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Sampai Tanggal</label>
                            <input type="date" name="until" class="form-control" value="{{ request('until') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">Terapkan</button>
                        </div>
                    </form>
                </div>

                <div class="p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Total Shipment</div>
                                <h3 class="fw-bold m-0 text-dark">{{ number_format($data['shipments_total'] ?? 0) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Delivered</div>
                                <h3 class="fw-bold m-0 text-success">{{ number_format($data['shipments_delivered'] ?? 0) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Revenue (Settled)</div>
                                <h3 class="fw-bold m-0 text-primary">Rp {{ number_format($data['revenue_total'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Payment Pending</div>
                                <h3 class="fw-bold m-0 text-warning">Rp {{ number_format($data['payment_pending'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 border bg-light h-100">
                                <h6 class="fw-bold mb-3">Statistik Cabang & Kurir</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Cabang</span>
                                    <span class="fw-bold text-dark">{{ number_format($data['branches_total'] ?? 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Kurir Aktif</span>
                                    <span class="fw-bold text-dark">{{ number_format($data['couriers_total'] ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-center d-flex align-items-center justify-content-center">
                            <div class="text-muted small italic">
                                <i class="bi bi-info-circle me-1"></i> Data di atas dihitung berdasarkan rentang tanggal yang dipilih.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
