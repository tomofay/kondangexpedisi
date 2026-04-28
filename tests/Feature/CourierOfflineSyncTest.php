<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CourierSyncEvent;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierOfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_courier_can_sync_offline_events_when_online(): void
    {
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courier->id, 'pending');

        $payload = [
            'events' => [
                [
                    'client_event_id' => 'evt-1001',
                    'event_type' => 'tracking_event',
                    'shipment_id' => $shipment->id,
                    'status_code' => 'in_transit',
                    'location' => 'Gudang Bandung',
                    'gps_lat' => -6.914744,
                    'gps_lng' => 107.609810,
                    'gps_accuracy_m' => 15.3,
                    'notes' => 'Scan pickup offline',
                    'occurred_at' => now()->subMinutes(20)->toDateTimeString(),
                ],
                [
                    'client_event_id' => 'evt-1002',
                    'event_type' => 'transition_status',
                    'shipment_id' => $shipment->id,
                    'status_code' => 'in_transit',
                    'location' => 'Gudang Bandung',
                    'gps_lat' => -6.914745,
                    'gps_lng' => 107.609811,
                    'gps_accuracy_m' => 10.0,
                    'notes' => 'Status disinkronkan saat online',
                    'occurred_at' => now()->subMinutes(15)->toDateTimeString(),
                ],
            ],
        ];

        $response = $this->withMobileToken($courier)->postJson(route('mobile.courier.offline-sync'), $payload);

        $response
            ->assertStatus(202)
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.applied', 2)
            ->assertJsonPath('summary.duplicates', 0)
            ->assertJsonPath('summary.failed', 0)
            ->assertJsonPath('items.0.sync_status', 'applied');

        $this->assertDatabaseHas('courier_sync_events', [
            'courier_id' => $courier->id,
            'client_event_id' => 'evt-1001',
            'status' => 'applied',
        ]);

        $this->assertDatabaseHas('courier_sync_events', [
            'courier_id' => $courier->id,
            'client_event_id' => 'evt-1002',
            'status' => 'applied',
        ]);

        $this->assertDatabaseHas('shipment_trackings', [
            'shipment_id' => $shipment->id,
            'created_by' => $courier->id,
            'location' => 'Gudang Bandung',
            'gps_lat' => -6.914744,
            'gps_lng' => 107.609810,
        ]);
    }

    public function test_sync_detects_duplicate_events_with_same_client_event_id(): void
    {
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courier->id, 'pending');

        $event = [
            'client_event_id' => 'evt-dup-01',
            'event_type' => 'tracking_event',
            'shipment_id' => $shipment->id,
            'status_code' => 'in_transit',
            'location' => 'Hub Cimahi',
            'notes' => 'Event duplikat',
            'occurred_at' => now()->subMinutes(5)->toDateTimeString(),
        ];

        $this->withMobileToken($courier)->postJson(route('mobile.courier.offline-sync'), [
            'events' => [$event],
        ])->assertStatus(202);

        $secondResponse = $this->withMobileToken($courier)->postJson(route('mobile.courier.offline-sync'), [
            'events' => [$event],
        ]);

        $secondResponse
            ->assertStatus(202)
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('summary.applied', 0)
            ->assertJsonPath('summary.duplicates', 1)
            ->assertJsonPath('summary.failed', 0)
            ->assertJsonPath('items.0.sync_status', 'duplicate');

        $this->assertSame(1, CourierSyncEvent::query()
            ->where('courier_id', $courier->id)
            ->where('client_event_id', 'evt-dup-01')
            ->count());
    }

    public function test_sync_marks_failed_when_shipment_not_assigned_to_courier(): void
    {
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();
        $otherCourier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $otherCourier->id, 'pending');

        $response = $this->withMobileToken($courier)->postJson(route('mobile.courier.offline-sync'), [
            'events' => [[
                'client_event_id' => 'evt-failed-01',
                'event_type' => 'transition_status',
                'shipment_id' => $shipment->id,
                'status_code' => 'in_transit',
                'location' => 'Warehouse',
                'notes' => 'Tidak berhak sinkron',
            ]],
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('summary.failed', 1)
            ->assertJsonPath('items.0.sync_status', 'failed');

        $this->assertDatabaseHas('courier_sync_events', [
            'courier_id' => $courier->id,
            'client_event_id' => 'evt-failed-01',
            'status' => 'failed',
        ]);
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
            'tracking_number' => 'SXP-OFF-'.strtoupper(fake()->bothify('######')),
            'branch_id' => $branch->id,
            'courier_id' => $courierId,
            'zone_id' => $branch->zone_id,
            'status_id' => $statusId,
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200001001',
            'sender_address' => 'Alamat A',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200001002',
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
