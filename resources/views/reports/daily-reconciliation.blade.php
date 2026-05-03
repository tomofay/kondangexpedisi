<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Rekonsiliasi Harian</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Validasi Kas & Setoran Cabang</div>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-4 border border-gray-100">
                <div class="p-4 border-bottom bg-light">
                    <form id="filter-form" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">Tampilkan</button>
                        </div>
                    </form>
                </div>

                <div class="p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Total Setoran Terdeteksi</div>
                                <h3 class="fw-bold m-0 text-dark">Rp {{ number_format($data['summary']['total_revenue'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Cash Terkumpul</div>
                                <h3 class="fw-bold m-0 text-success">Rp {{ number_format($data['summary']['cash_total'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-4 border bg-white h-100">
                                <div class="text-muted small fw-bold text-uppercase mb-2">Digital/Transfer</div>
                                <h3 class="fw-bold m-0 text-primary">Rp {{ number_format($data['summary']['digital_total'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-dark">Detail Transaksi Per Branch</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small fw-bold text-uppercase">
                                    <th>Cabang</th>
                                    <th>Transaksi</th>
                                    <th>Total Tagihan</th>
                                    <th>Settled</th>
                                    <th>Pending</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['branch_details'] ?? [] as $detail)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $detail['branch_name'] }}</td>
                                        <td>{{ number_format($detail['transaction_count']) }}</td>
                                        <td>Rp {{ number_format($detail['total_amount'], 0, ',', '.') }}</td>
                                        <td class="text-success fw-bold">Rp {{ number_format($detail['settled_amount'], 0, ',', '.') }}</td>
                                        <td class="text-warning fw-bold">Rp {{ number_format($detail['pending_amount'], 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">Audit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada data untuk tanggal ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
