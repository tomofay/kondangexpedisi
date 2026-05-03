<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 6mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
            margin: 0;
        }

        .sheet {
            border: 1px solid #111827;
            padding: 6px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            border-bottom: 2px solid #111827;
            padding-bottom: 4px;
        }

        .header .brand,
        .header .meta {
            display: table-cell;
            vertical-align: top;
        }

        .header .brand {
            width: 62%;
        }

        .brand h1 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 0.08em;
        }

        .brand .subtitle {
            margin-top: 2px;
            color: #4b5563;
        }

        .header .meta {
            width: 38%;
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #111827;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .tracking {
            margin-top: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .barcode-wrap {
            text-align: center;
            margin: 4px 0;
            padding: 4px;
            border: 1px dashed #9ca3af;
        }

        .barcode-wrap img {
            display: block;
            margin: 0 auto 2px;
            max-width: 100%;
            height: 20mm;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .grid td,
        .grid th {
            border: 1px solid #111827;
            padding: 3px 4px;
            vertical-align: top;
            line-height: 1.2;
        }

        .section-title {
            background: #111827;
            color: #fff;
            padding: 3px 4px;
            font-size: 8px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0;
        }

        .muted {
            color: #6b7280;
        }

        .details td:first-child {
            width: 28%;
            font-weight: bold;
            background: #f9fafb;
        }

        .mini td,
        .mini th {
            font-size: 8px;
        }

        .summary td:first-child {
            font-weight: bold;
            width: 65%;
        }

        .summary .total-row td {
            font-size: 9px;
            font-weight: bold;
            background: #f3f4f6;
        }

        .two-col {
            width: 100%;
            border-collapse: collapse;
        }

        .two-col td {
            width: 50%;
            vertical-align: top;
            padding-right: 3px;
        }

        .small {
            font-size: 8px;
        }

        .footer {
            margin-top: 4px;
            font-size: 7px;
            color: #6b7280;
            text-align: center;
            line-height: 1.2;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div class="brand">
                <h1>Kondang Ekspedisi</h1>
                <div class="subtitle">Shipment Label / Resi Pengiriman</div>
                <div class="tracking">{{ $shipment->tracking_number }}</div>
            </div>
            <div class="meta">
                <div class="badge">{{ strtoupper($shipment->service_type ?? 'regular') }}</div>
                <div class="small muted" style="margin-top: 8px;">Cetak: {{ now()->format('d/m/Y H:i') }}</div>
                <div class="small muted">Status: {{ $shipment->status?->name ?? '-' }}</div>
                <div class="small muted">Payment: {{ strtoupper($shipment->payment_status ?? '-') }}</div>
            </div>
        </div>

        <div class="barcode-wrap">
            <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode {{ $shipment->tracking_number }}">
            <div class="small">Scan untuk identifikasi shipment</div>
        </div>

        <table class="two-col no-break" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <p class="section-title">Rute Pengiriman</p>
                    <table class="grid details" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Cabang Asal</td>
                            <td>
                                {{ $originBranch?->name ?? '-' }}<br>
                                <span class="muted">{{ $originBranch?->city ?? '-' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Cabang Tujuan</td>
                            <td>
                                {{ $destinationBranch?->name ?? '-' }}<br>
                                <span class="muted">{{ $destinationBranch?->city ?? '-' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Kurir / Armada</td>
                            <td>
                                {{ $shipment->courier?->name ?? '-' }}<br>
                                <span class="muted">{{ $shipment->vehicle?->plate_number ?? '-' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Tanggal Masuk</td>
                            <td>{{ optional($shipment->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Estimasi Sampai</td>
                            <td>{{ optional($shipment->estimated_delivery_at)->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <p class="section-title">Detail Penerima</p>
                    <table class="grid details" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Nama</td>
                            <td>{{ $shipment->recipient_name }}</td>
                        </tr>
                        <tr>
                            <td>Telepon</td>
                            <td>{{ $shipment->recipient_phone }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>{{ \Illuminate\Support\Str::limit($shipment->recipient_address, 58) }}</td>
                        </tr>
                        <tr>
                            <td>Pengirim</td>
                            <td>
                                {{ $shipment->sender_name }}<br>
                                <span class="muted">{{ $shipment->sender_phone }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Alamat Pengirim</td>
                            <td>{{ \Illuminate\Support\Str::limit($shipment->sender_address, 58) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="two-col no-break" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <p class="section-title">Rincian Paket</p>
                    <table class="grid mini summary" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Berat</td>
                            <td>{{ number_format((float) $shipment->total_weight_kg, 2) }} kg</td>
                        </tr>
                        <tr>
                            <td>Volume</td>
                            <td>{{ number_format((float) $shipment->total_volume, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Subtotal</td>
                            <td>{{ number_format((float) $shipment->subtotal_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Asuransi</td>
                            <td>{{ number_format((float) $shipment->insurance_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Admin Fee</td>
                            <td>{{ number_format((float) $shipment->admin_fee, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total</td>
                            <td>{{ number_format((float) $shipment->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <p class="section-title">Pembayaran & Catatan</p>
                    <table class="grid mini summary" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Metode</td>
                            <td>{{ $latestPayment?->method ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>{{ $latestPayment?->status ?? $shipment->payment_status }}</td>
                        </tr>
                        <tr>
                            <td>Nominal</td>
                            <td>{{ number_format((float) ($latestPayment?->amount ?? $shipment->total_amount), 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Catatan</td>
                            <td>{{ \Illuminate\Support\Str::limit($shipment->notes ?: '-', 42) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @if ($shipment->items->isNotEmpty())
            <p class="section-title">Daftar Item</p>
            <table class="grid mini no-break" cellspacing="0" cellpadding="0">
                <tr>
                    <th style="width: 6%;">#</th>
                    <th>Nama Barang</th>
                    <th style="width: 12%;">Qty</th>
                    <th style="width: 18%;">Kondisi</th>
                </tr>
                @foreach ($shipment->items->take(2) as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($item->name ?? $item->item_name ?? '-', 24) }}
                        </td>
                        <td>{{ $item->quantity ?? 1 }}</td>
                        <td>{{ $item->condition ?? '-' }}</td>
                    </tr>
                @endforeach
                @if ($shipment->items->count() > 2)
                    <tr>
                        <td colspan="4" class="muted">+{{ $shipment->items->count() - 2 }} item lain diringkas.</td>
                    </tr>
                @endif
            </table>
        @endif

        <div class="footer">
            Dokumen otomatis Kondang Ekspedisi.
        </div>
    </div>
</body>
</html>
