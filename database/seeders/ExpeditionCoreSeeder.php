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
        // 1. Zones
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

        // 2. Branches
        $branchesByCode = collect([
            [
                'code' => 'JKT-HQ',
                'name' => 'Cabang Pusat Jakarta',
                'city' => 'Jakarta',
                'phone' => '021-555-1000',
                'email' => 'jakarta@kondangekspedisi.test',
                'address' => 'Jl. Medan Merdeka Selatan No. 10, Jakarta Pusat',
                'zone_code' => 'JABODETABEK',
            ],
            [
                'code' => 'SBY-01',
                'name' => 'Cabang Surabaya',
                'city' => 'Surabaya',
                'phone' => '031-555-2000',
                'email' => 'surabaya@kondangekspedisi.test',
                'address' => 'Jl. Basuki Rahmat No. 78, Surabaya',
                'zone_code' => 'JAWA-BALI',
            ],
            [
                'code' => 'BDG-01',
                'name' => 'Cabang Bandung',
                'city' => 'Bandung',
                'phone' => '022-555-3000',
                'email' => 'bandung@kondangekspedisi.test',
                'address' => 'Jl. Asia Afrika No. 30, Bandung',
                'zone_code' => 'JAWA-BALI',
            ],
        ])->mapWithKeys(function (array $branch) use ($zonesByCode) {
            $record = Branch::query()->updateOrCreate(
                ['code' => $branch['code']],
                [
                    'name' => $branch['name'],
                    'city' => $branch['city'],
                    'phone' => $branch['phone'],
                    'email' => $branch['email'],
                    'address' => $branch['address'],
                    'zone_id' => $zonesByCode[$branch['zone_code']]->id,
                    'is_active' => true,
                ]
            );

            return [$branch['code'] => $record];
        });

        // 3. Rate Cards
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

        // 4. Shipment Statuses
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

        // 5. Users
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
                    'permissions' => null,
                ]
            );
        }

        $customerUser = User::query()->where('email', 'pelanggan.demo@kondangekspedisi.test')->first();

        // 6. Customers
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

        // 7. Vehicles
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

        // 8. Landing Page Content
        LandingPageContent::query()->delete();

        $landingRows = [
            ['section' => 'hero', 'title' => 'KONDANG.', 'subtitle' => 'Kirim Paket Tanpa Khawatir.', 'content' => 'Solusi logistik modern dengan jangkauan terluas dan sistem pelacakan real-time yang akurat.', 'cta_label' => 'Lacak Sekarang', 'cta_url' => '#tracking', 'sort_order' => 1],
            ['section' => 'feature', 'title' => 'Express Delivery', 'subtitle' => 'Cepat & Prioritas', 'content' => 'Pengiriman super cepat untuk dokumen atau paket penting. Estimasi sampai dalam 1 hari kerja.', 'sort_order' => 1],
            ['section' => 'feature', 'title' => 'Tracking Real-time', 'subtitle' => 'Pantau 24/7', 'content' => 'Sistem GPS terintegrasi yang memungkinkan Anda memantau posisi paket secara langsung.', 'sort_order' => 2],
            ['section' => 'feature', 'title' => 'Safe & Secure', 'subtitle' => 'Asuransi Terjamin', 'content' => 'Setiap paket kami perlakukan dengan standar keamanan tinggi dan opsi proteksi asuransi.', 'sort_order' => 3],
            ['section' => 'testimonial', 'title' => 'Budi, Pemilik UMKM', 'content' => 'Sejak pakai Kondang, pengiriman barang ke reseller jadi jauh lebih teratur dan minim komplain.', 'sort_order' => 1],
            ['section' => 'testimonial', 'title' => 'Siti, Online Seller', 'content' => 'Fitur tracking-nya juara, sangat akurat dan membantu pelanggan saya merasa tenang.', 'sort_order' => 2],
            ['section' => 'faq', 'title' => 'Berapa lama estimasi pengiriman?', 'content' => 'Reguler 2-4 hari, Express 1-2 hari kerja tergantung kota tujuan.', 'sort_order' => 1],
            ['section' => 'faq', 'title' => 'Apakah bisa bayar di tempat (COD)?', 'content' => 'Ya, kami mendukung layanan COD untuk seluruh pengiriman domestik.', 'sort_order' => 2],
            ['section' => 'cta', 'title' => 'Siap kirim paket pertama Anda?', 'subtitle' => 'Daftar sekarang dan nikmati kemudahan mengelola logistik bisnis Anda.', 'cta_label' => 'Mulai Sekarang', 'cta_url' => '/register', 'sort_order' => 1],
            ['section' => 'contact', 'title' => 'Customer Service', 'content' => '(021) 555-1000', 'sort_order' => 1],
            ['section' => 'contact', 'title' => 'Email Bantuan', 'content' => 'halo@kondang.co.id', 'sort_order' => 2],
            ['section' => 'statistic', 'title' => 'Paket/Hari', 'content' => '2.5K+', 'sort_order' => 1],
            ['section' => 'statistic', 'title' => 'Kota Tujuan', 'content' => '120+', 'sort_order' => 2],
            ['section' => 'statistic', 'title' => 'Tepat Waktu', 'content' => '99%', 'sort_order' => 3],
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

        // 9. Shipment & Payments Simulation
        $branches = Branch::query()->get();
        $couriers = User::query()->where('role', 'courier')->get();
        $vehicleIds = Vehicle::query()->pluck('id')->all();
        $statusMap = ShipmentStatus::query()->pluck('id', 'code');
        $kasirId = User::query()->where('role', 'kasir')->value('id');

        $senderProfiles = [
            ['name' => 'Toko Kelontong Sinar Rejeki', 'phone' => '081290001001', 'address' => 'Jl. Mangga Dua Raya No. 14, Jakarta'],
            ['name' => 'PT Nusantara Kriya', 'phone' => '081290001002', 'address' => 'Jl. Gatot Subroto No. 88, Jakarta'],
            ['name' => 'Butik Anggrek', 'phone' => '081290001003', 'address' => 'Jl. Cihampelas No. 45, Bandung'],
        ];

        $itemCatalog = [
            ['item_name' => 'Dokumen Kontrak', 'description' => 'Berkas dokumen perusahaan', 'weight_kg' => 0.4, 'length_cm' => 32, 'width_cm' => 24, 'height_cm' => 4, 'declared_value' => 150000],
            ['item_name' => 'Suku Cadang Motor', 'description' => 'Komponen otomotif', 'weight_kg' => 2.8, 'length_cm' => 28, 'width_cm' => 22, 'height_cm' => 16, 'declared_value' => 700000],
            ['item_name' => 'Peralatan Dapur', 'description' => 'Paket perlengkapan dapur', 'weight_kg' => 3.2, 'length_cm' => 42, 'width_cm' => 32, 'height_cm' => 24, 'declared_value' => 550000],
        ];

        $statusDistribution = ['delivered', 'in_transit', 'pending', 'cancelled', 'delivered'];

        foreach ($statusDistribution as $index => $statusCode) {
            $branch = $branches->random();
            $customer = $customers->random();
            $courier = $couriers->where('branch_id', $branch->id)->first() ?? $couriers->random();
            $destBranch = $branches->where('id', '!=', $branch->id)->random();
            $vehicleId = $vehicleIds[array_rand($vehicleIds)];
            $sender = $senderProfiles[array_rand($senderProfiles)];
            $weight = (float) rand(5, 50) / 10;
            $baseAmount = rand(30000, 150000);
            $tracking = sprintf('KND-%s-%03d', now()->format('Ymd'), $index + 1);

            $shipment = Shipment::query()->create([
                'tracking_number' => $tracking,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'destination_branch_id' => $destBranch->id,
                'courier_id' => $courier?->id,
                'vehicle_id' => $vehicleId,
                'zone_id' => $destBranch->zone_id,
                'status_id' => $statusMap[$statusCode],
                'sender_name' => $sender['name'],
                'sender_phone' => $sender['phone'],
                'sender_address' => $sender['address'],
                'recipient_name' => $customer->name,
                'recipient_phone' => $customer->phone,
                'recipient_address' => $customer->address,
                'service_type' => 'regular',
                'total_weight_kg' => $weight,
                'total_volume' => (float) rand(10, 100),
                'subtotal_amount' => $baseAmount,
                'insurance_amount' => 3000,
                'admin_fee' => 2500,
                'total_amount' => $baseAmount + 5500,
                'is_cod' => false,
                'cod_amount' => 0,
                'payment_status' => $statusCode === 'pending' ? 'pending' : 'paid',
                'current_status_at' => now(),
                'estimated_delivery_at' => now()->addDays(3),
                'delivered_at' => $statusCode === 'delivered' ? now() : null,
                'notes' => 'Generated by seeder',
                'processing_status' => 'ok',
                'pricing_mode' => 'auto',
            ]);

            $item = $itemCatalog[array_rand($itemCatalog)];
            ShipmentItem::query()->create([
                'shipment_id' => $shipment->id,
                'item_name' => $item['item_name'],
                'quantity' => 1,
                'weight_kg' => $item['weight_kg'],
                'length_cm' => $item['length_cm'],
                'width_cm' => $item['width_cm'],
                'height_cm' => $item['height_cm'],
                'declared_value' => $item['declared_value'],
                'description' => $item['description'],
            ]);

            Payment::query()->create([
                'shipment_id' => $shipment->id,
                'customer_id' => $customer->id,
                'processed_by' => $kasirId,
                'method' => 'midtrans',
                'status' => $statusCode === 'pending' ? 'pending' : 'settlement',
                'amount' => $shipment->total_amount,
                'midtrans_order_id' => 'ORDER-'.$tracking,
                'midtrans_transaction_id' => (string) Str::uuid(),
                'payment_type' => 'bank_transfer',
                'bank_name' => 'bca',
                'va_number' => (string) rand(1000000000, 9999999999),
                'transaction_time' => now(),
                'paid_at' => $statusCode === 'pending' ? null : now(),
            ]);

            ShipmentTracking::query()->create([
                'shipment_id' => $shipment->id,
                'status_id' => $statusMap['pending'],
                'created_by' => $courier?->id,
                'location' => $branch->city,
                'notes' => 'Shipment created',
                'event_at' => now()->subHours(2),
            ]);
        }

        // 10. Admin Governance & Reliability Simulation
        $adminUser = User::query()->where('role', 'admin')->first();
        if ($adminUser) {
            // Integration Health
            IntegrationStatus::query()->updateOrCreate(
                ['service_name' => 'midtrans'],
                ['status' => 'healthy', 'success_count' => 500, 'failure_count' => 2, 'last_success_at' => now()]
            );

            // Admin Tasks
            AdminTask::query()->create([
                'task_type' => 'approve_rate_card',
                'title' => 'Review Penyesuaian Tarif',
                'description' => 'Permintaan update tarif untuk rute Jakarta-Surabaya.',
                'assigned_to' => $adminUser->id,
                'created_by' => $adminUser->id,
                'status' => 'pending',
                'priority' => 'high',
                'action_data' => ['rate_card_id' => 1],
            ]);

            // Error Logs
            ErrorLog::query()->create([
                'error_type' => 'exception',
                'module' => 'payment',
                'message' => 'Midtrans connection timeout',
                'severity' => 'high',
            ]);
        }
    }
}
