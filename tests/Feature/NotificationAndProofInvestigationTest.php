<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\ShipmentTrackingProof;
use App\Models\User;
use App\Services\OperationalIssueService;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationAndProofInvestigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_error_creates_admin_notification(): void
    {
        $this->createWorkflowStatuses();

        $admin = User::factory()->state(['role' => 'admin'])->create();
        $branch = Branch::factory()->create();

        $shipment = $this->createShipment($branch, null, null, 'pending');

        app(OperationalIssueService::class)->markShipmentError(
            $shipment,
            'Tracking shipment gagal disimpan.',
            ['operation' => 'create']
        );

        $this->assertDatabaseHas('app_notifications', [
            'recipient_user_id' => $admin->id,
            'type' => 'shipment_error',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.notifications.index'));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.type', 'shipment_error');
    }

    public function test_payment_failed_notifies_customer(): void
    {
        $this->createWorkflowStatuses();

        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        $customer = \App\Models\Customer::factory()->create([
            'user_id' => $customerUser->id,
        ]);

        $branch = Branch::factory()->create();

        $shipment = $this->createShipment($branch, null, $customer->id, 'pending');

        app(ShipmentService::class)->syncPaymentStatus($shipment, 'deny');

        $this->assertDatabaseHas('app_notifications', [
            'recipient_user_id' => $customerUser->id,
            'type' => 'payment_failed',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($customerUser)->getJson(route('customer.notifications.index'));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.type', 'payment_failed');
    }

    public function test_courier_status_update_notifies_customer(): void
    {
        $this->createWorkflowStatuses();

        $courier = User::factory()->state(['role' => 'courier'])->create();
        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        $customer = \App\Models\Customer::factory()->create([
            'user_id' => $customerUser->id,
        ]);

        $branch = Branch::factory()->create();

        $shipment = $this->createShipment($branch, $courier->id, $customer->id, 'pending');

        app(ShipmentService::class)->transitionStatus(
            $shipment,
            'in_transit',
            $courier->id,
            'Gudang transit',
            'Kurir mulai membawa paket',
            false,
            null,
            -6.914744,
            107.609810,
            9.5
        );

        $this->assertDatabaseHas('app_notifications', [
            'recipient_user_id' => $customerUser->id,
            'type' => 'shipment_status_updated',
            'priority' => 'medium',
        ]);
    }

    public function test_admin_can_filter_operational_proof_investigation_endpoint(): void
    {
        $this->createWorkflowStatuses();

        $admin = User::factory()->state(['role' => 'admin'])->create();
        $courierA = User::factory()->state(['role' => 'courier'])->create();
        $courierB = User::factory()->state(['role' => 'courier'])->create();

        $branch = Branch::factory()->create();

        $shipment = $this->createShipment($branch, $courierA->id, null, 'in_transit');

        $trackingA = ShipmentTracking::query()->create([
            'shipment_id' => $shipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'in_transit')->value('id'),
            'created_by' => $courierA->id,
            'location' => 'Hub A',
            'event_at' => now()->subDay(),
        ]);

        $trackingB = ShipmentTracking::query()->create([
            'shipment_id' => $shipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'out_for_delivery')->value('id'),
            'created_by' => $courierB->id,
            'location' => 'Hub B',
            'event_at' => now(),
        ]);

        $proofA = ShipmentTrackingProof::query()->create([
            'shipment_id' => $shipment->id,
            'tracking_id' => $trackingA->id,
            'uploaded_by' => $courierA->id,
            'proof_type' => 'pickup_photo',
            'file_path' => 'operational-proofs/a.jpg',
            'file_mime' => 'image/jpeg',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'proof-a'),
            'captured_at' => now()->subDay(),
        ]);

        ShipmentTrackingProof::query()->create([
            'shipment_id' => $shipment->id,
            'tracking_id' => $trackingB->id,
            'uploaded_by' => $courierB->id,
            'proof_type' => 'recipient_signature',
            'file_path' => 'operational-proofs/b.jpg',
            'file_mime' => 'image/jpeg',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'proof-b'),
            'captured_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('operational-proofs.investigation.index', [
            'proof_type' => 'pickup_photo',
            'courier_id' => $courierA->id,
            'from' => now()->subDays(2)->toDateString(),
            'until' => now()->toDateString(),
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('items.0.id', $proofA->id)
            ->assertJsonPath('items.0.proof_type', 'pickup_photo')
            ->assertJsonPath('meta.total', 1);
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

    private function createShipment(Branch $branch, ?int $courierId, ?int $customerId, string $statusCode): Shipment
    {
        $statusId = ShipmentStatus::query()->where('code', $statusCode)->value('id');

        return Shipment::query()->create([
            'tracking_number' => 'SXP-NOTIF-'.strtoupper(fake()->bothify('######')),
            'customer_id' => $customerId,
            'branch_id' => $branch->id,
            'courier_id' => $courierId,
            'status_id' => $statusId,
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200002001',
            'sender_address' => 'Alamat A',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200002002',
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
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'not_required',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);
    }
}
