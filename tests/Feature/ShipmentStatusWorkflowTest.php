<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShipmentStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_skip_status_transitions(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $statuses = $this->createWorkflowStatuses();
        $shipment = $this->createShipmentWithStatus($statuses['pending']);

        $response = $this->actingAs($admin)->postJson(route('shipments.transition-status', $shipment), [
            'status_code' => 'out_for_delivery',
            'location' => 'Jakarta',
            'notes' => 'Coba loncat status',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_code']);

        $this->assertSame('pending', $shipment->fresh()->status?->code);
    }

    public function test_final_status_cannot_be_changed_without_override(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $statuses = $this->createWorkflowStatuses();
        $shipment = $this->createShipmentWithStatus($statuses['delivered']);

        $response = $this->actingAs($admin)->postJson(route('shipments.transition-status', $shipment), [
            'status_code' => 'pending',
            'location' => 'Jakarta',
            'notes' => 'Coba ubah status final',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_code']);

        $this->assertSame('delivered', $shipment->fresh()->status?->code);
    }

    public function test_admin_override_requires_reason(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $statuses = $this->createWorkflowStatuses();
        $shipment = $this->createShipmentWithStatus($statuses['delivered']);

        $response = $this->actingAs($admin)->postJson(route('shipments.transition-status', $shipment), [
            'status_code' => 'pending',
            'force_transition' => true,
            'location' => 'Jakarta',
            'notes' => 'Tanpa alasan override',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['override_reason']);

        $this->assertSame('delivered', $shipment->fresh()->status?->code);
    }

    public function test_admin_override_can_change_final_status_when_reason_is_provided(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $statuses = $this->createWorkflowStatuses();
        $shipment = $this->createShipmentWithStatus($statuses['delivered']);

        $response = $this->actingAs($admin)->postJson(route('shipments.transition-status', $shipment), [
            'status_code' => 'pending',
            'force_transition' => true,
            'override_reason' => 'Koreksi manual karena input final salah',
            'location' => 'Jakarta',
            'notes' => 'Status final salah input',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status.code', 'pending');

        $shipment->refresh();

        $this->assertSame('pending', $shipment->status?->code);
        $this->assertDatabaseHas('shipment_trackings', [
            'shipment_id' => $shipment->id,
            'status_id' => $statuses['pending']->id,
        ]);

        $this->assertStringContainsString(
            'Manual override',
            ShipmentTracking::query()->where('shipment_id', $shipment->id)->latest('id')->first()?->notes ?? ''
        );
    }

    public function test_regular_shipment_update_cannot_change_status_id(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $statuses = $this->createWorkflowStatuses();
        $shipment = $this->createShipmentWithStatus($statuses['pending']);

        $response = $this->actingAs($admin)->putJson(route('shipments.update', $shipment), [
            'status_id' => $statuses['in_transit']->id,
            'notes' => 'Coba ubah status lewat update biasa',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);

        $this->assertSame('pending', $shipment->fresh()->status?->code);
    }

    private function createWorkflowStatuses(): array
    {
        $statuses = [];

        foreach (config('expedition.shipment_statuses', []) as $index => $code) {
            $statuses[$code] = ShipmentStatus::query()->create([
                'code' => $code,
                'name' => str($code)->replace('_', ' ')->title()->value(),
                'sequence' => $index + 1,
                'is_final' => in_array($code, config('expedition.shipment_status_flow.final_statuses', []), true),
                'badge_color' => 'blue',
            ]);
        }

        return $statuses;
    }

    private function createShipmentWithStatus(ShipmentStatus $status): Shipment
    {
        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create([
            'zone_id' => $zone->id,
        ]);

        return Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-'.Str::upper(Str::random(8)),
            'branch_id' => $branch->id,
            'zone_id' => $zone->id,
            'status_id' => $status->id,
            'sender_name' => 'Pengirim Test',
            'sender_phone' => '081200000001',
            'sender_address' => 'Jl. Test No. 1',
            'recipient_name' => 'Penerima Test',
            'recipient_phone' => '081200000002',
            'recipient_address' => 'Jl. Tujuan No. 2',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'insurance_amount' => 0,
            'admin_fee' => 2500,
            'total_amount' => 12500,
            'is_cod' => false,
            'cod_amount' => 0,
            'payment_status' => 'pending',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment test workflow.',
        ]);
    }
}