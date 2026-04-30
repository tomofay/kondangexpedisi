<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(["database.default" => "sqlite"]);
config(["database.connections.sqlite.database" => ":memory:"]);
Illuminate\Support\Facades\Artisan::call("migrate");

echo "Testing factories...\n";
App\Models\Branch::factory()->create();
echo "Branch factory OK\n";
App\Models\Customer::factory()->create();
echo "Customer factory OK\n";
App\Models\User::factory()->create();
echo "User factory OK\n";
App\Models\RateCard::factory()->create();
echo "RateCard factory OK\n";
App\Models\Shipment::factory()->create();
echo "Shipment factory OK\n";
echo "All done.\n";

