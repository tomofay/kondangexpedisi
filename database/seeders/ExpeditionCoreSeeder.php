<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\LandingPageContent;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\RateCard;
use App\Models\RateCardApproval;
use App\Models\IntegrationStatus;
use App\Models\ErrorLog;
use App\Models\AdminTask;
use App\Models\Report;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExpeditionCoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchesByCode = collect([
            [
                'code' => 'JKT-HQ',
                'name' => 'Cabang Pusat Jakarta',
                'city' => 'Jakarta',
                'phone' => '021-555-1000',
                'email' => 'jakarta@kondangekspedisi.test',
                'address' => 'Jl. Medan Merdeka Selatan No. 10, Jakarta Pusat',
            ],
            [
                'code' => 'SBY-01',
                'name' => 'Cabang Surabaya',
                'city' => 'Surabaya',
                'phone' => '031-555-2000',
                'email' => 'surabaya@kondangekspedisi.test',
                'address' => 'Jl. Basuki Rahmat No. 78, Surabaya',
            ],
            [
                'code' => 'BDG-01',
                'name' => 'Cabang Bandung',
                'city' => 'Bandung',
                'phone' => '022-555-3000',
                'email' => 'bandung@kondangekspedisi.test',
                'address' => 'Jl. Asia Afrika No. 30, Bandung',
            ],
        ])->mapWithKeys(function (array $branch) {
            $record = Branch::query()->updateOrCreate(
                ['code' => $branch['code']],
                [
                    'name' => $branch['name'],
                    'city' => $branch['city'],
                    'phone' => $branch['phone'],
                    'email' => $branch['email'],
                    'address' => $branch['address'],
                    'is_active' => true,
                ]
            );

            return [$branch['code'] => $record];
        });

        $zonesByCode = collect([
            [
                'code' => 'JABODETABEK',
                'name' => 'Jabodetabek',
                'description' => 'Area Jakarta, Bogor, Depok, Tangerang, dan Bekasi',
                'multiplier' => 1,
            ],
            [
                'code' => 'JAWA-BALI',
                'name' => 'Jawa dan Bali',
                'description' => 'Pengiriman antar kota utama di Jawa dan Bali',
                'multiplier' => 1.8,
            ],
            [
                'code' => 'SUMAPA',
                'name' => 'Sumatera, Kalimantan, Sulawesi, Papua',
                'description' => 'Pengiriman antarpulau di luar Jawa dan Bali',
                'multiplier' => 2.6,
            ],
        ])->mapWithKeys(function (array $zone) {
            $record = Zone::query()->updateOrCreate(
                ['code' => $zone['code']],
                [
                    'name' => $zone['name'],
                    'description' => $zone['description'],
                    'multiplier' => $zone['multiplier'],
                    'is_active' => true,
                ]
            );

            return [$zone['code'] => $record];
        });

        $branchZoneMap = [
            'JKT-HQ' => 'JABODETABEK',
            'SBY-01' => 'JAWA-BALI',
            'BDG-01' => 'JAWA-BALI',
        ];

        foreach ($branchesByCode as $code => $branch) {
            $zoneCode = $branchZoneMap[$code] ?? null;

            if ($zoneCode && isset($zonesByCode[$zoneCode])) {
                $branch->update(['zone_id' => $zonesByCode[$zoneCode]->id]);
            }
        }

        $zoneOrder = ['JABODETABEK', 'JAWA-BALI', 'SUMAPA'];
        $zoneRank = [
            'JABODETABEK' => 1,
            'JAWA-BALI' => 2,
            'SUMAPA' => 3,
        ];

        $serviceFactor = [
            'economy' => 0.85,
            'regular' => 1,
            'express' => 1.28,
            'same_day' => 1.6,
        ];

        foreach ($zoneOrder as $originZoneCode) {
            foreach ($zoneOrder as $destinationZoneCode) {
                $distanceRank = abs($zoneRank[$destinationZoneCode] - $zoneRank[$originZoneCode]);
                $crossZonePenalty = $originZoneCode === $destinationZoneCode ? 1 : 1.2;
                $baseRouteFactor = (1 + ($zoneRank[$originZoneCode] * 0.2) + ($zoneRank[$destinationZoneCode] * 0.3) + ($distanceRank * 0.25)) * $crossZonePenalty;

                foreach ($serviceFactor as $serviceType => $factor) {
                    $basePrice = (int) round(14000 * $baseRouteFactor * $factor);
                    $perKgPrice = (int) round(3500 * $baseRouteFactor * ($serviceType === 'same_day' ? 1.15 : 1));
                    $insuranceFee = (int) round(1500 * $baseRouteFactor * ($serviceType === 'economy' ? 0.9 : 1));

                    RateCard::query()->updateOrCreate(
                        [
                            'origin_zone_id' => $zonesByCode[$originZoneCode]->id,
                            'destination_zone_id' => $zonesByCode[$destinationZoneCode]->id,
                            'service_type' => $serviceType,
                            'min_weight_kg' => 0,
                        ],
                        [
                            'zone_id' => $zonesByCode[$destinationZoneCode]->id,
                            'max_weight_kg' => 1,
                            'base_price' => $basePrice,
                            'per_kg_price' => $perKgPrice,
                            'insurance_fee' => $insuranceFee,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $statuses = [
            ['code' => 'pending', 'name' => 'Menunggu Proses', 'sequence' => 1, 'is_final' => false, 'badge_color' => 'amber'],
            ['code' => 'in_transit', 'name' => 'Dalam Perjalanan', 'sequence' => 2, 'is_final' => false, 'badge_color' => 'blue'],
            ['code' => 'out_for_delivery', 'name' => 'Siap Diantar', 'sequence' => 3, 'is_final' => false, 'badge_color' => 'indigo'],
            ['code' => 'delivered', 'name' => 'Terkirim', 'sequence' => 4, 'is_final' => true, 'badge_color' => 'green'],
            ['code' => 'cancelled', 'name' => 'Dibatalkan', 'sequence' => 5, 'is_final' => true, 'badge_color' => 'red'],
            ['code' => 'returned', 'name' => 'Dikembalikan', 'sequence' => 6, 'is_final' => true, 'badge_color' => 'orange'],
        ];

        foreach ($statuses as $status) {
            ShipmentStatus::query()->updateOrCreate(['code' => $status['code']], $status);
        }

        $users = [
            ['name' => 'Admin Sistem', 'email' => 'admin@kondangekspedisi.test', 'role' => 'admin', 'branch_code' => 'JKT-HQ'],
            ['name' => 'Kasir Jakarta', 'email' => 'kasir.jakarta@kondangekspedisi.test', 'role' => 'kasir', 'branch_code' => 'JKT-HQ'],
            ['name' => 'Kurir Jakarta', 'email' => 'kurir.jakarta@kondangekspedisi.test', 'role' => 'courier', 'branch_code' => 'JKT-HQ'],
            ['name' => 'Kurir Surabaya', 'email' => 'kurir.surabaya@kondangekspedisi.test', 'role' => 'courier', 'branch_code' => 'SBY-01'],
            ['name' => 'Kurir Bandung', 'email' => 'kurir.bandung@kondangekspedisi.test', 'role' => 'courier', 'branch_code' => 'BDG-01'],
            ['name' => 'Manajer Operasional', 'email' => 'wisnubgalih4@gmail.com', 'role' => 'manager', 'branch_code' => 'JKT-HQ'],
            ['name' => 'Pelanggan Demo', 'email' => 'pelanggan.demo@kondangekspedisi.test', 'role' => 'customer', 'branch_code' => 'JKT-HQ'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password123'),
                    'role' => $user['role'],
                    'branch_id' => $branchesByCode[$user['branch_code']]->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $customerUser = User::query()->where('email', 'pelanggan.demo@kondangekspedisi.test')->first();

        $customerRows = [
            ['name' => 'Pelanggan Demo', 'email' => 'pelanggan.demo@kondangekspedisi.test', 'phone' => '081220000111', 'address' => 'Jl. Melati No. 5, Cempaka Putih, Jakarta Pusat', 'city' => 'Jakarta'],
            ['name' => 'Andi Pratama', 'email' => 'andi.pratama@contoh.id', 'phone' => '081220000112', 'address' => 'Jl. Kencana No. 8, Kebayoran Baru, Jakarta Selatan', 'city' => 'Jakarta'],
            ['name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@contoh.id', 'phone' => '081220000113', 'address' => 'Jl. Mawar Merah No. 12, Cimahi Tengah', 'city' => 'Bandung'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@contoh.id', 'phone' => '081220000114', 'address' => 'Jl. Ikan Dorang No. 44, Gubeng', 'city' => 'Surabaya'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@contoh.id', 'phone' => '081220000115', 'address' => 'Jl. Sriwijaya No. 17, Denpasar Barat', 'city' => 'Denpasar'],
            ['name' => 'Rizky Maulana', 'email' => 'rizky.maulana@contoh.id', 'phone' => '081220000116', 'address' => 'Jl. Ahmad Yani No. 66, Pontianak Kota', 'city' => 'Pontianak'],
            ['name' => 'Nadia Putri', 'email' => 'nadia.putri@contoh.id', 'phone' => '081220000117', 'address' => 'Jl. Panakkukang Mas No. 28, Makassar', 'city' => 'Makassar'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@contoh.id', 'phone' => '081220000118', 'address' => 'Jl. Diponegoro No. 120, Semarang Tengah', 'city' => 'Semarang'],
            ['name' => 'Intan Permata', 'email' => 'intan.permata@contoh.id', 'phone' => '081220000119', 'address' => 'Jl. Riau No. 22, Pekanbaru', 'city' => 'Pekanbaru'],
            ['name' => 'Hendra Kurniawan', 'email' => 'hendra.kurniawan@contoh.id', 'phone' => '081220000120', 'address' => 'Jl. Sam Ratulangi No. 9, Manado', 'city' => 'Manado'],
        ];

        $customers = collect($customerRows)->map(function (array $customer) use ($customerUser) {
            return Customer::query()->updateOrCreate(
                ['email' => $customer['email']],
                [
                    'user_id' => $customer['email'] === 'pelanggan.demo@kondangekspedisi.test' ? $customerUser?->id : null,
                    'name' => $customer['name'],
                    'email_verified_at' => now(),
                    'phone' => $customer['phone'],
                    'password' => Hash::make('password123'),
                    'address' => $customer['address'],
                    'city' => $customer['city'],
                    'photo' => null,
                ]
            );
        });

        $vehicleRows = [
            ['branch_code' => 'JKT-HQ', 'name' => 'Motor Kurir Jakarta 01', 'plate_number' => 'B 4101 KDX', 'type' => 'motorcycle', 'capacity_kg' => 80],
            ['branch_code' => 'JKT-HQ', 'name' => 'Van Operasional Jakarta 01', 'plate_number' => 'B 9201 KDX', 'type' => 'van', 'capacity_kg' => 1000],
            ['branch_code' => 'SBY-01', 'name' => 'Motor Kurir Surabaya 01', 'plate_number' => 'L 5102 KDX', 'type' => 'motorcycle', 'capacity_kg' => 90],
            ['branch_code' => 'SBY-01', 'name' => 'Mobil Box Surabaya 01', 'plate_number' => 'L 9302 KDX', 'type' => 'truck', 'capacity_kg' => 1800],
            ['branch_code' => 'BDG-01', 'name' => 'Motor Kurir Bandung 01', 'plate_number' => 'D 6203 KDX', 'type' => 'motorcycle', 'capacity_kg' => 85],
            ['branch_code' => 'BDG-01', 'name' => 'Van Operasional Bandung 01', 'plate_number' => 'D 9403 KDX', 'type' => 'van', 'capacity_kg' => 950],
        ];

        foreach ($vehicleRows as $vehicle) {
            Vehicle::query()->updateOrCreate(
                ['plate_number' => $vehicle['plate_number']],
                [
                    'branch_id' => $branchesByCode[$vehicle['branch_code']]->id,
                    'name' => $vehicle['name'],
                    'type' => $vehicle['type'],
                    'capacity_kg' => $vehicle['capacity_kg'],
                    'status' => 'available',
                ]
            );
        }

        LandingPageContent::query()->delete();

        $landingRows = [
            ['section' => 'hero', 'title' => 'Kondang Ekspedisi', 'subtitle' => 'Kirim paket ke seluruh Indonesia dengan proses cepat dan aman.', 'content' => 'Mulai dari dokumen, produk UMKM, hingga barang bernilai tinggi, semua bisa dipantau real-time.', 'cta_label' => 'Lacak Paket', 'cta_url' => '#tracking', 'sort_order' => 1],
            ['section' => 'feature', 'title' => 'Penjemputan Terjadwal', 'subtitle' => 'Pickup rutin harian', 'content' => 'Kurir kami siap menjemput paket di rumah, kantor, maupun gudang Anda sesuai jadwal.', 'sort_order' => 1],
            ['section' => 'feature', 'title' => 'Tracking Transparan', 'subtitle' => 'Timeline status lengkap', 'content' => 'Setiap perpindahan paket tercatat agar pelanggan bisa memantau proses kirim dengan jelas.', 'sort_order' => 2],
            ['section' => 'feature', 'title' => 'Tarif Kompetitif', 'subtitle' => 'Skema zona dan layanan', 'content' => 'Perhitungan ongkir fleksibel berdasarkan zona tujuan, berat paket, dan jenis layanan.', 'sort_order' => 3],
            ['section' => 'testimonial', 'title' => 'Toko Sembako Berkah Jaya', 'content' => 'Pengiriman antarkota untuk stok toko jadi lebih teratur. Status paketnya juga jelas.', 'sort_order' => 1],
            ['section' => 'testimonial', 'title' => 'UMKM Batik Nusantara', 'content' => 'Respon admin cepat, kurir ramah, dan pelanggan kami puas karena paket sampai tepat waktu.', 'sort_order' => 2],
            ['section' => 'faq', 'title' => 'Bagaimana cara cek nomor resi?', 'content' => 'Masukkan nomor resi pada form tracking publik untuk melihat status terbaru pengiriman.', 'sort_order' => 1],
            ['section' => 'faq', 'title' => 'Apakah tersedia pembayaran online?', 'content' => 'Ya, pembayaran online tersedia melalui Midtrans Sandbox untuk kebutuhan demo sistem.', 'sort_order' => 2],
            ['section' => 'cta', 'title' => 'Kelola pengiriman lebih mudah bersama Kondang Ekspedisi', 'subtitle' => 'Satu dashboard untuk order, tracking, dan laporan.', 'cta_label' => 'Masuk Dashboard', 'cta_url' => '/login', 'sort_order' => 1],
            ['section' => 'contact', 'title' => 'Layanan Pelanggan', 'content' => '0812-9000-7000', 'sort_order' => 1],
            ['section' => 'contact', 'title' => 'Email Resmi', 'content' => 'halo@kondangekspedisi.test', 'sort_order' => 2],
            ['section' => 'statistic', 'title' => 'Shipment Terselesaikan', 'content' => '2.500+', 'sort_order' => 1],
            ['section' => 'statistic', 'title' => 'Kota Terlayani', 'content' => '120+', 'sort_order' => 2],
            ['section' => 'statistic', 'title' => 'Akurasi Tracking', 'content' => '99.4%', 'sort_order' => 3],
        ];

        foreach ($landingRows as $row) {
            LandingPageContent::query()->create([
                'section' => $row['section'],
                'title' => $row['title'] ?? null,
                'subtitle' => $row['subtitle'] ?? null,
                'content' => $row['content'] ?? null,
                'image_url' => null,
                'cta_label' => $row['cta_label'] ?? null,
                'cta_url' => $row['cta_url'] ?? null,
                'sort_order' => $row['sort_order'],
                'is_active' => true,
                'metadata' => ['bahasa' => 'id'],
            ]);
        }

        $branches = Branch::query()->get();
        $couriers = User::query()->where('role', 'courier')->get();
        $zoneIds = Zone::query()->where('is_active', true)->pluck('id')->all();
        $vehicleIds = Vehicle::query()->pluck('id')->all();
        $statusMap = ShipmentStatus::query()->pluck('id', 'code');
        $kasirId = User::query()->where('role', 'kasir')->value('id');

        $senderProfiles = [
            ['name' => 'Toko Kelontong Sinar Rejeki', 'phone' => '081290001001', 'address' => 'Jl. Mangga Dua Raya No. 14, Jakarta'],
            ['name' => 'PT Nusantara Kriya', 'phone' => '081290001002', 'address' => 'Jl. Gatot Subroto No. 88, Jakarta'],
            ['name' => 'Butik Anggrek', 'phone' => '081290001003', 'address' => 'Jl. Cihampelas No. 45, Bandung'],
            ['name' => 'CV Laut Timur', 'phone' => '081290001004', 'address' => 'Jl. Kenjeran No. 102, Surabaya'],
            ['name' => 'Percetakan Mitra Usaha', 'phone' => '081290001005', 'address' => 'Jl. Dipati Ukur No. 39, Bandung'],
        ];

        $itemCatalog = [
            ['item_name' => 'Dokumen Kontrak', 'description' => 'Berkas dokumen perusahaan', 'weight_kg' => 0.4, 'length_cm' => 32, 'width_cm' => 24, 'height_cm' => 4, 'declared_value' => 150000],
            ['item_name' => 'Batik Tulis', 'description' => 'Produk UMKM batik premium', 'weight_kg' => 1.2, 'length_cm' => 38, 'width_cm' => 30, 'height_cm' => 10, 'declared_value' => 450000],
            ['item_name' => 'Suku Cadang Motor', 'description' => 'Komponen otomotif', 'weight_kg' => 2.8, 'length_cm' => 28, 'width_cm' => 22, 'height_cm' => 16, 'declared_value' => 700000],
            ['item_name' => 'Peralatan Dapur', 'description' => 'Paket perlengkapan dapur', 'weight_kg' => 3.2, 'length_cm' => 42, 'width_cm' => 32, 'height_cm' => 24, 'declared_value' => 550000],
            ['item_name' => 'Buku Pelajaran', 'description' => 'Paket buku sekolah', 'weight_kg' => 2.1, 'length_cm' => 30, 'width_cm' => 24, 'height_cm' => 14, 'declared_value' => 280000],
            ['item_name' => 'Kosmetik Lokal', 'description' => 'Produk kecantikan UMKM', 'weight_kg' => 1.0, 'length_cm' => 24, 'width_cm' => 18, 'height_cm' => 12, 'declared_value' => 320000],
        ];

        $trackingNotes = [
            'pending' => 'Paket telah diterima di loket dan menunggu proses sortir.',
            'in_transit' => 'Paket sedang dalam perjalanan menuju kota tujuan.',
            'out_for_delivery' => 'Paket sedang dibawa kurir untuk diantar ke alamat penerima.',
            'delivered' => 'Paket telah diterima oleh penerima dalam kondisi baik.',
            'cancelled' => 'Pengiriman dibatalkan sesuai permintaan pengirim.',
            'returned' => 'Paket dikembalikan ke pengirim karena kendala pengantaran.',
        ];

        $statusDistribution = array_merge(
            array_fill(0, 5, 'delivered'),
            array_fill(0, 8, 'in_transit'),
            array_fill(0, 4, 'pending'),
            array_fill(0, 2, 'cancelled'),
            ['returned']
        );

        foreach ($statusDistribution as $index => $statusCode) {
            $branch = $branches->random();
            $customer = $customers->random();
            $courier = $couriers->where('branch_id', $branch->id)->first() ?? $couriers->random();
            $zoneId = $zoneIds[array_rand($zoneIds)];
            $vehicleId = $vehicleIds[array_rand($vehicleIds)];
            $sender = $senderProfiles[array_rand($senderProfiles)];
            $weight = (float) rand(5, 80) / 10;
            $baseAmount = rand(25000, 180000);
            $tracking = sprintf('KND-%s-%03d', now()->format('Ymd'), $index + 1);

            $shipment = Shipment::query()->updateOrCreate(
                ['tracking_number' => $tracking],
                [
                    'customer_id' => $customer->id,
                    'branch_id' => $branch->id,
                    'courier_id' => $courier?->id,
                    'vehicle_id' => $vehicleId,
                    'zone_id' => $zoneId,
                    'status_id' => $statusMap[$statusCode],
                    'sender_name' => $sender['name'],
                    'sender_phone' => $sender['phone'],
                    'sender_address' => $sender['address'],
                    'recipient_name' => $customer->name,
                    'recipient_phone' => $customer->phone,
                    'recipient_address' => $customer->address,
                    'service_type' => ['regular', 'express', 'same_day', 'economy'][array_rand(['regular', 'express', 'same_day', 'economy'])],
                    'total_weight_kg' => $weight,
                    'total_volume' => (float) rand(15, 140),
                    'subtotal_amount' => $baseAmount,
                    'insurance_amount' => 3000,
                    'admin_fee' => 2500,
                    'total_amount' => $baseAmount + 5500,
                    'is_cod' => false,
                    'cod_amount' => 0,
                    'payment_status' => in_array($statusCode, ['delivered', 'in_transit'], true) ? 'paid' : 'pending',
                    'current_status_at' => now()->subHours(rand(1, 72)),
                    'estimated_delivery_at' => now()->addDays(rand(1, 5)),
                    'delivered_at' => $statusCode === 'delivered' ? now()->subHours(rand(1, 24)) : null,
                    'notes' => 'Data pengiriman contoh untuk simulasi operasional Indonesia.',
                ]
            );

            ShipmentItem::query()->where('shipment_id', $shipment->id)->delete();
            $itemCount = rand(1, 2);
            for ($itemIndex = 0; $itemIndex < $itemCount; $itemIndex++) {
                $item = $itemCatalog[array_rand($itemCatalog)];
                ShipmentItem::query()->create([
                    'shipment_id' => $shipment->id,
                    'item_name' => $item['item_name'],
                    'quantity' => rand(1, 3),
                    'weight_kg' => $item['weight_kg'],
                    'length_cm' => $item['length_cm'],
                    'width_cm' => $item['width_cm'],
                    'height_cm' => $item['height_cm'],
                    'declared_value' => $item['declared_value'],
                    'description' => $item['description'],
                ]);
            }

            $paymentStatus = match ($statusCode) {
                'delivered', 'in_transit' => 'settlement',
                'cancelled' => 'cancel',
                'returned' => 'refund',
                default => 'pending',
            };

            Payment::query()->updateOrCreate(
                ['midtrans_order_id' => 'ORDER-'.$tracking],
                [
                    'shipment_id' => $shipment->id,
                    'customer_id' => $customer->id,
                    'processed_by' => $kasirId,
                    'method' => 'midtrans',
                    'status' => $paymentStatus,
                    'amount' => $shipment->total_amount,
                    'midtrans_transaction_id' => (string) Str::uuid(),
                    'snap_token' => Str::random(32),
                    'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.Str::random(20),
                    'payment_type' => 'bank_transfer',
                    'bank_name' => 'bca',
                    'va_number' => (string) rand(1000000000, 9999999999),
                    'fraud_status' => 'accept',
                    'signature_key' => hash('sha256', $tracking),
                    'transaction_time' => now()->subHours(rand(1, 96)),
                    'paid_at' => $paymentStatus === 'settlement' ? now()->subHours(rand(1, 48)) : null,
                    'gateway_payload' => ['sumber' => 'seeder', 'status' => $paymentStatus],
                    'notes' => 'Pembayaran contoh untuk kebutuhan demo aplikasi.',
                ]
            );

            ShipmentTracking::query()->where('shipment_id', $shipment->id)->delete();

            $trackingSteps = match ($statusCode) {
                'pending' => ['pending'],
                'in_transit' => ['pending', 'in_transit'],
                'delivered' => ['pending', 'in_transit', 'out_for_delivery', 'delivered'],
                'cancelled' => ['pending', 'cancelled'],
                'returned' => ['pending', 'in_transit', 'returned'],
                default => ['pending'],
            };

            foreach ($trackingSteps as $stepIndex => $stepCode) {
                ShipmentTracking::query()->create([
                    'shipment_id' => $shipment->id,
                    'status_id' => $statusMap[$stepCode],
                    'created_by' => $courier?->id,
                    'location' => $stepCode === 'delivered' ? $customer->city : $branch->city,
                    'notes' => $trackingNotes[$stepCode],
                    'event_at' => now()->subHours((count($trackingSteps) - $stepIndex) * 4),
                ]);
            }
        }

        $riskUser = User::query()->where('email', 'admin@kondangekspedisi.test')->first();
        if ($riskUser) {
            for ($i = 0; $i < 4; $i++) {
                AuditLog::query()->create([
                    'action' => 'auth.login_failed',
                    'subject_type' => User::class,
                    'subject_id' => $riskUser->id,
                    'actor_id' => null,
                    'before_state' => null,
                    'after_state' => [
                        'ip' => '127.0.0.1',
                        'source' => 'seeder',
                    ],
                    'notes' => 'Seeded login failure sample.',
                    'created_at' => now()->subHours(rand(1, 18)),
                    'updated_at' => now()->subHours(rand(1, 18)),
                ]);
            }
        }

        for ($i = 0; $i < 3; $i++) {
            AuditLog::query()->create([
                'action' => 'payment.midtrans_callback_failed',
                'subject_type' => Payment::class,
                'subject_id' => 0,
                'actor_id' => null,
                'before_state' => null,
                'after_state' => [
                    'payload' => ['order_id' => 'SEEDER-FAIL-'.$i],
                ],
                'notes' => 'Simulasi callback Midtrans gagal dari seeder.',
                'created_at' => now()->subHours(rand(1, 20)),
                'updated_at' => now()->subHours(rand(1, 20)),
            ]);
        }

        // Feature 6: Master Data Governance - Rate Card Approvals
        $rateCards = RateCard::query()->limit(3)->get();
        $adminUser = User::query()->where('email', 'admin@kondangekspedisi.test')->first();
        if ($adminUser) {
            foreach ($rateCards as $rateCard) {
                RateCardApproval::query()->create([
                    'rate_card_id' => $rateCard->id,
                    'requested_by' => $adminUser->id,
                    'approved_by' => null,
                    'status' => 'pending',
                    'changes' => [
                        'base_price' => ['old' => $rateCard->base_price, 'new' => $rateCard->base_price * 1.1],
                        'per_kg_price' => ['old' => $rateCard->per_kg_price, 'new' => $rateCard->per_kg_price * 1.1],
                    ],
                    'reason' => 'Penyesuaian harga sesuai inflasi Q1 2026.',
                ]);
            }
        }

        // Feature 8: Service Reliability - Integration Statuses
        IntegrationStatus::query()->updateOrCreate(
            ['service_name' => 'midtrans'],
            [
                'status' => 'healthy',
                'success_count' => 256,
                'failure_count' => 4,
                'last_success_at' => now()->subMinutes(5),
                'last_failure_at' => now()->subHours(3),
                'metadata' => ['version' => '2.0', 'endpoint' => 'https://api.sandbox.midtrans.com'],
            ]
        );

        IntegrationStatus::query()->updateOrCreate(
            ['service_name' => 'email'],
            [
                'status' => 'healthy',
                'success_count' => 142,
                'failure_count' => 1,
                'last_success_at' => now()->subMinutes(10),
                'last_failure_at' => now()->subDays(2),
                'metadata' => ['provider' => 'mailtrap', 'queue' => 'redis'],
            ]
        );

        IntegrationStatus::query()->updateOrCreate(
            ['service_name' => 'backup'],
            [
                'status' => 'healthy',
                'success_count' => 30,
                'failure_count' => 0,
                'last_success_at' => now()->subHours(2),
                'last_failure_at' => null,
                'metadata' => ['storage' => 's3', 'frequency' => 'daily'],
            ]
        );

        // Feature 8: Error Logs
        $errorMessages = [
            'Payment gateway timeout',
            'Database connection timeout',
            'File upload size exceeds limit',
            'Invalid payment signature',
            'Email delivery failed',
        ];

        foreach ($errorMessages as $idx => $message) {
            ErrorLog::query()->create([
                'error_type' => 'exception',
                'module' => ['payment', 'shipment', 'user', 'report'][rand(0, 3)],
                'message' => $message,
                'stack_trace' => 'Sample stack trace from seeder',
                'severity' => ['low', 'medium', 'high', 'critical'][rand(0, 3)],
                'created_at' => now()->subHours(rand(1, 23)),
            ]);
        }

        // Feature 9: Admin Tasks (Action Queue)
        if ($adminUser) {
            AdminTask::query()->create([
                'task_type' => 'approve_rate_card',
                'title' => 'Approve Rate Card Update - Zone JABODETABEK',
                'description' => 'Perubahan tarif regular service untuk zona Jabodetabek.',
                'assigned_to' => $adminUser->id,
                'created_by' => $adminUser->id,
                'status' => 'pending',
                'priority' => 'high',
                'action_data' => ['rate_card_id' => 1, 'approval_id' => 1],
            ]);

            AdminTask::query()->create([
                'task_type' => 'reassign_shipment',
                'title' => 'Reassign Stuck Shipment KND-20260412-001',
                'description' => 'Reasign pengiriman terhambat ke kurir alternatif.',
                'assigned_to' => $adminUser->id,
                'created_by' => $adminUser->id,
                'status' => 'pending',
                'priority' => 'high',
                'action_data' => ['shipment_id' => 1],
            ]);

            AdminTask::query()->create([
                'task_type' => 'follow_up_payment',
                'title' => 'Follow-up Pembayaran Pending > 24 jam',
                'description' => 'Hubungi pelanggan untuk konfirmasi pembayaran.',
                'assigned_to' => null,
                'created_by' => $adminUser->id,
                'status' => 'pending',
                'priority' => 'medium',
                'action_data' => ['payment_count' => 5],
            ]);
        }

        // Feature 10: Reports
        Report::query()->create([
            'report_type' => 'kpi_snapshot',
            'title' => 'KPI Snapshot - 2026-04-11',
            'frequency' => 'daily',
            'recipients' => ['admin@kondangekspedisi.test', 'manager@kondangekspedisi.test'],
            'filters' => ['date' => '2026-04-11'],
            'format' => 'pdf',
            'file_path' => 'reports/kpi_snapshot_20260411.pdf',
            'status' => 'completed',
            'record_count' => 150,
            'generated_at' => now()->subDay(),
            'generated_by' => $adminUser?->id,
        ]);

        Report::query()->create([
            'report_type' => 'daily_export',
            'title' => 'Daily Shipment Export - 2026-04-12',
            'frequency' => 'daily',
            'recipients' => ['operations@kondangekspedisi.test'],
            'filters' => ['date' => '2026-04-12'],
            'format' => 'csv',
            'file_path' => 'reports/shipments_20260412.csv',
            'status' => 'completed',
            'record_count' => 42,
            'generated_at' => now(),
            'generated_by' => $adminUser?->id,
        ]);
    }
}
