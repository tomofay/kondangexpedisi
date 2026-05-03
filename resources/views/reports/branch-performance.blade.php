<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h2 class="fw-bold text-dark m-0" style="font-size: 1.4rem;">Performa Cabang</h2>
                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase;">Monitor SLA & Revenue Antar Cabang</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.branch-performance.export', request()->query()) }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm">
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

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small fw-bold text-uppercase">
                                <th class="px-4 py-3">Kode</th>
                                <th>Nama Cabang</th>
                                <th>Total Shipment</th>
                                <th>Total Revenue</th>
                                <th class="text-end px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                                <tr>
                                    <td class="px-4"><span class="badge bg-secondary-subtle text-secondary fw-bold">{{ $branch['code'] }}</span></td>
                                    <td class="fw-bold text-dark">{{ $branch['name'] }}</td>
                                    <td>{{ number_format($branch['shipments_total']) }}</td>
                                    <td class="fw-bold text-primary">Rp {{ number_format($branch['revenue_total'], 0, ',', '.') }}</td>
                                    <td class="text-end px-4">
                                        <a href="{{ route('reports.branch-detail', $branch['id']) }}" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">Belum ada data cabang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
