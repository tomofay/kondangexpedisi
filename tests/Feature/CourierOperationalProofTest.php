<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourierOperationalProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_courier_can_upload_pickup_photo_with_gps(): void
    {
        Storage::fake('public');
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courier->id, 'pending');
        $tracking = ShipmentTracking::query()->create([
            'shipment_id' => $shipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'in_transit')->value('id'),
            'created_by' => $courier->id,
            'location' => 'Gudang',
            'event_at' => now(),
        ]);

        $file = UploadedFile::fake()->image('pickup.jpg', 640, 480);

        $response = $this->withMobileToken($courier)->post(route('mobile.courier.operational-proofs.store', $shipment), [
            'tracking_id' => $tracking->id,
            'proof_type' => 'pickup_photo',
            'file' => $file,
            'gps_lat' => -6.914744,
            'gps_lng' => 107.609810,
            'gps_accuracy_m' => 12.5,
            'captured_at' => now()->subMinutes(10)->toDateTimeString(),
            'notes' => 'Bukti pickup paket.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.proof_type', 'pickup_photo');

        $this->assertDatabaseHas('shipment_tracking_proofs', [
            'shipment_id' => $shipment->id,
            'tracking_id' => $tracking->id,
            'proof_type' => 'pickup_photo',
            'uploaded_by' => $courier->id,
        ]);
    }

    public function test_courier_can_upload_handover_photo_and_signature(): void
    {
        Storage::fake('public');
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courier->id, 'out_for_delivery');
        $tracking = ShipmentTracking::query()->create([
            'shipment_id' => $shipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'out_for_delivery')->value('id'),
            'created_by' => $courier->id,
            'location' => 'Rumah penerima',
            'event_at' => now(),
        ]);

        $handover = UploadedFile::fake()->image('handover.png', 640, 480);
        $signature = UploadedFile::fake()->image('signature.png', 320, 160);

        $this->withMobileToken($courier)->post(route('mobile.courier.operational-proofs.store', $shipment), [
            'tracking_id' => $tracking->id,
            'proof_type' => 'handover_photo',
            'file' => $handover,
        ])->assertCreated();

        $this->withMobileToken($courier)->post(route('mobile.courier.operational-proofs.store', $shipment), [
            'tracking_id' => $tracking->id,
            'proof_type' => 'recipient_signature',
            'file' => $signature,
        ])->assertCreated();

        $list = $this->withMobileToken($courier)->getJson(route('mobile.courier.operational-proofs.index', $shipment));

        $list
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('total', 2);
    }

    public function test_duplicate_proof_upload_returns_existing_record(): void
    {
        Storage::fake('public');
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courier->id, 'pending');
        $tracking = ShipmentTracking::query()->create([
            'shipment_id' => $shipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'in_transit')->value('id'),
            'created_by' => $courier->id,
            'location' => 'Hub',
            'event_at' => now(),
        ]);

        $fileOne = UploadedFile::fake()->image('proof.jpg', 640, 480);
        $fileTwo = UploadedFile::fake()->createWithContent('proof-copy.jpg', file_get_contents($fileOne->getRealPath()));

        $this->withMobileToken($courier)->post(route('mobile.courier.operational-proofs.store', $shipment), [
            'tracking_id' => $tracking->id,
            'proof_type' => 'pickup_photo',
            'file' => $fileOne,
        ])->assertCreated();

        $duplicateResponse = $this->withMobileToken($courier)->post(route('mobile.courier.operational-proofs.store', $shipment), [
            'tracking_id' => $tracking->id,
            'proof_type' => 'pickup_photo',
            'file' => $fileTwo,
        ]);

        $duplicateResponse
            ->assertOk()
            ->assertJsonPath('message', 'Bukti operasional duplikat, data sebelumnya dipakai.');

        $this->assertSame(1, \App\Models\ShipmentTrackingProof::query()
            ->where('tracking_id', $tracking->id)
            ->where('proof_type', 'pickup_photo')
            ->count());
    }

    private function createWorkflowStatuses(): void
    {
        foreach (config('expedition.shipment_statuses', []) as $index => $code) {
            ShipmentStatus::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => str($code)->replace('_', ' ')->title()->value(),
                    'sequence' => $index + 1,
                    'is_final' => in_array($code, config('expedition.shipment_status_flow.final_statuses', []), true),
                    'badge_color' => 'blue',
                ]
            );
        }
    }

    private function createShipment(Branch $branch, int $courierId, string $statusCode): Shipment
    {
        $statusId = ShipmentStatus::query()->where('code', $statusCode)->value('id');

        return Shipment::query()->create([
            'tracking_number' => 'SXP-PROOF-'.strtoupper(fake()->bothify('######')),
            'branch_id' => $branch->id,
            'courier_id' => $courierId,
            'zone_id' => $branch->zone_id,
            'status_id' => $statusId,
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200001111',
            'sender_address' => 'Alamat A',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200001112',
            'recipient_address' => 'Alamat B',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'insurance_amount' => 1000,
            'admin_fee' => 2500,
            'total_amount' => 13500,
            'auto_subtotal_amount' => 10000,
            'auto_insurance_amount' => 1000,
            'auto_admin_fee' => 2500,
            'auto_total_amount' => 13500,
            'is_cod' => false,
            'cod_amount' => 0,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'not_required',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);
    }
}
