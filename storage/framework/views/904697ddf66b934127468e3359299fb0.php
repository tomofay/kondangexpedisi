<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 4mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #000; margin: 0; }
        .label-container { border: 1.5px solid #000; width: 100%; position: relative; }
        table { width: 100%; border-collapse: collapse; }
        td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        
        .header-logo { color: #2563eb; font-weight: 900; font-size: 14px; text-transform: uppercase; font-style: italic; border: none; }
        .header-type { font-size: 14px; font-weight: bold; text-align: center; border-left: 1px solid #000; border-right: 1px solid #000; }
        .header-tracking { font-size: 10px; font-weight: bold; text-align: right; border: none; }

        .barcode-section { text-align: center; padding: 6px; }
        .barcode-section img { height: 25mm; width: 25mm; margin: 0 auto; }
        .tracking-number { font-size: 16px; font-weight: 900; letter-spacing: 1px; margin-top: 2px; }

        .address-header { background: #000; color: #fff; font-size: 7px; font-weight: bold; padding: 2px 4px; border: none; }
        .address-box { height: 28mm; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .info-grid td { font-size: 7px; width: 50%; }
        .cashless-banner { background: #f3f4f6; text-align: center; font-weight: bold; padding: 4px; font-size: 9px; }
        .cashless-banner span { font-style: italic; font-weight: normal; font-size: 7px; margin-left: 10px; }

        .items-table th { background: #f9fafb; font-size: 6.5px; text-align: left; padding: 3px; border: 1px solid #000; }
        .items-table td { font-size: 6.5px; padding: 2px 3px; border: 1px solid #ccc; }

        .footer-note { font-size: 6px; color: #666; text-align: center; padding: 4px; border: none; border-top: 1px solid #000; }
    </style>
</head>
<body>
    <div class="label-container">
        <!-- Top Header -->
        <table style="border-bottom: 2px solid #000;">
            <tr>
                <td class="header-logo" style="width: 45%; font-size: 11px;">KONDANG <span style="font-weight: 900; color: #1e3a8a;">EKSPEDISI</span></td>
                <td class="header-type" style="width: 20%;"><?php echo e(strtoupper($shipment->service_type ?? 'REGULER')); ?></td>
                <td class="header-tracking" style="width: 35%;">
                    <div style="font-size: 7px; color: #444;">No. Resi:</div>
                    <?php echo e($shipment->tracking_number); ?>

                </td>
            </tr>
        </table>

        <!-- Main QR Code -->
        <div class="barcode-section" style="border-bottom: 1.5px dashed #000;">
            <div style="margin: 0 auto; width: 25mm; height: 25mm;">
                <img src="data:image/svg+xml;base64,<?php echo e($qrcode); ?>" style="width: 100%; height: 100%;">
            </div>
            <div class="tracking-number"><?php echo e($shipment->tracking_number); ?></div>
        </div>

        <!-- Address Section -->
        <table>
            <tr>
                <td style="width: 50%;" class="address-box">
                    <div class="bold uppercase" style="margin-bottom: 4px;">Penerima: <?php echo e($shipment->recipient_name); ?></div>
                    <div style="margin-bottom: 4px;"><?php echo e($shipment->recipient_phone); ?></div>
                    <div><?php echo e($shipment->recipient_address); ?></div>
                    <div class="bold uppercase" style="margin-top: 6px;"><?php echo e($destinationBranch?->city ?? '-'); ?></div>
                </td>
                <td style="width: 50%;" class="address-box">
                    <div class="bold uppercase" style="margin-bottom: 4px;">Pengirim: <?php echo e($shipment->sender_name); ?></div>
                    <div style="margin-bottom: 4px;"><?php echo e($shipment->sender_phone); ?></div>
                    <div><?php echo e($shipment->sender_address); ?></div>
                    <div class="bold uppercase" style="margin-top: 6px;"><?php echo e($originBranch?->city ?? '-'); ?></div>
                </td>
            </tr>
        </table>

        <!-- Specs Section -->
        <table class="info-grid">
            <tr>
                <td>
                    <div class="bold">Berat: <?php echo e(number_format($shipment->total_weight_kg, 2)); ?> kg</div>
                    <div>COD: Rp <?php echo e(number_format($shipment->total_amount, 0, ',', '.')); ?></div>
                </td>
                <td>
                    <div class="bold">Batas Kirim: <?php echo e(optional($shipment->estimated_delivery_at)->format('d-m-Y') ?? now()->addDays(3)->format('d-m-Y')); ?></div>
                    <div>Order ID: <?php echo e($shipment->id); ?></div>
                </td>
            </tr>
        </table>

        <!-- Cashless Banner -->
        <div class="cashless-banner">
            CASHLESS <span>Penjual tidak perlu bayar ongkir ke Kurir</span>
        </div>

        <!-- Items Section -->
        <?php if($shipment->items->isNotEmpty()): ?>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 65%;">Nama Produk</th>
                    <th style="width: 15%;">SKU/Variasi</th>
                    <th style="width: 15%;">Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $shipment->items->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e(\Illuminate\Support\Str::limit($item->name ?? $item->item_name ?? '-', 45)); ?></td>
                    <td><?php echo e($item->sku ?? '-'); ?></td>
                    <td><?php echo e($item->quantity ?? 1); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="footer-note">
            Dicetak melalui Sistem Kondang Ekspedisi pada <?php echo e(now()->format('d/m/Y H:i')); ?>. Resi ini adalah bukti pengiriman yang sah.
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/pdf/shipment-label.blade.php ENDPATH**/ ?>