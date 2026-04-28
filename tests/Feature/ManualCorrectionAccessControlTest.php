<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualCorrectionAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_request_pricing_override_for_any_branch(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $shipment = $this->createShipment($branch, null);

        $response = $this->actingAs($admin)->postJson(route('shipments.pricing-override.request', $shipment), [
            'subtotal_amount' => 25000,
            'insurance_amount' => 1000,
            'admin_fee' => 2500,
            'total_amount' => 28500,
            'reason' => 'Koreksi admin lintas cabang.',
        ]);

        $response->assertStatus(202);
    }

    public function test_kasir_only_can_request_manual_correction_in_own_branch(): void
    {
        $this->createWorkflowStatuses();

        $zoneA = Zone::factory()->create();
        $zoneB = Zone::factory()->create();
        $branchA = Branch::factory()->create(['zone_id' => $zoneA->id]);
        $branchB = Branch::factory()->create(['zone_id' => $zoneB->id]);

        $kasir = User::factory()->state([
            'role' => 'kasir',
            'branch_id' => $branchA->id,
        ])->create();

        $shipmentOwnBranch = $this->createShipment($branchA, null);
        $shipmentOtherBranch = $this->createShipment($branchB, null);

        $okResponse = $this->actingAs($kasir)->postJson(route('shipments.pricing-override.request', $shipmentOwnBranch), [
            'subtotal_amount' => 24000,
            'insurance_amount' => 1000,
            'admin_fee' => 2500,
            'total_amount' => 27500,
            'reason' => 'Koreksi kasir cabang sendiri.',
        ]);

        $okResponse->assertStatus(202);

        $forbiddenResponse = $this->actingAs($kasir)->postJson(route('shipments.pricing-override.request', $shipmentOtherBranch), [
            'subtotal_amount' => 26000,
            'insurance_amount' => 1000,
            'admin_fee' => 2500,
            'total_amount' => 29500,
            'reason' => 'Koreksi kasir beda cabang.',
        ]);

        $forbiddenResponse->assertStatus(403);
    }

    public function test_courier_cannot_update_shipment_but_can_update_tracking_for_assigned_shipment(): void
    {
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $courier = User::factory()->state([
            'role' => 'courier',
            'branch_id' => $branch->id,
        ])->create();

        $assignedShipment = $this->createShipment($branch, $courier->id);

        $updateResponse = $this->actingAs($courier)->putJson(route('shipments.update', $assignedShipment), [
            'notes' => 'Coba update operasional langsung',
        ]);

        $updateResponse->assertStatus(403);

        $trackingResponse = $this->actingAs($courier)->postJson(route('shipment-trackings.store'), [
            'shipment_id' => $assignedShipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'in_transit')->value('id'),
            'location' => 'Gudang transit',
            'notes' => 'Paket diterima untuk pengantaran.',
        ]);

        $trackingResponse->assertStatus(201);
    }

    public function test_customer_cannot_modify_operational_shipment_data(): void
    {
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $customer = User::factory()->state(['role' => 'customer'])->create();
        $shipment = $this->createShipment($branch, null);

        $response = $this->actingAs($customer)->putJson(route('shipments.update', $shipment), [
            'notes' => 'Customer mencoba ubah data operasional.',
        ]);

        $response->assertStatus(403);
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

    private function createShipment(Branch $branch, ?int $courierId): Shipment
    {
        return Shipment::query()->create([
            'tracking_number' => 'SXP-ACL-'.strtoupper(fake()->bothify('######')),
            'branch_id' => $branch->id,
            'zone_id' => $branch->zone_id,
            'courier_id' => $courierId,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000901',
            'sender_address' => 'Alamat A',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000902',
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
