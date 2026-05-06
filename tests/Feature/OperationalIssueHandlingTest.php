<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ErrorLog;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalIssueHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_override_can_create_shipment_with_manual_pricing(): void
    {
        $branch = $this->createBranch();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $this->createWorkflowStatuses();

        $destinationBranch = Branch::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('shipments.store'), [
            'branch_id' => $branch->id,
            'destination_branch_id' => $destinationBranch->id,
            'sender_name' => 'Pengirim Manual',
            'sender_phone' => '081200000001',
            'sender_address' => 'Jl. Manual No. 1',
            'recipient_name' => 'Penerima Manual',
            'recipient_phone' => '081200000002',
            'recipient_address' => 'Jl. Manual No. 2',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'subtotal_amount' => 50000,
            'total_amount' => 50000,
            'manual_override' => true,
            'manual_override_reason' => 'Rate card belum tersedia, koreksi manual dilakukan.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.pricing_mode', 'manual')
            ->assertJsonPath('data.processing_status', 'ok');

        $shipment = Shipment::query()->latest('id')->first();

        $this->assertNotNull($shipment);
        $this->assertSame('manual', $shipment->pricing_mode);
        $this->assertSame('ok', $shipment->processing_status);
        $this->assertSame('Rate card belum tersedia, koreksi manual dilakukan.', $shipment->manual_override_reason);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shipment.manual_override',
            'subject_type' => Shipment::class,
            'subject_id' => $shipment->id,
            'source' => 'user_action',
            'is_manual_correction' => 1,
            'correction_reference' => 'Rate card belum tersedia, koreksi manual dilakukan.',
        ]);

        $auditLog = \App\Models\AuditLog::query()
            ->where('action', 'shipment.manual_override')
            ->where('subject_id', $shipment->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertArrayHasKey('processing_status', $auditLog->changed_fields ?? []);
    }

    public function test_midtrans_callback_failure_marks_payment_processing_error(): void
    {
        $this->createWorkflowStatuses();
        $branch = $this->createBranch();
        $shipment = Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-'.strtoupper(fake()->bothify('######')),
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000003',
            'sender_address' => 'Jl. Satu No. 1',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000004',
            'recipient_address' => 'Jl. Dua No. 2',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'total_amount' => 10000,
            'payment_status' => 'pending',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment callback test.',
        ]);

        $payment = Payment::query()->create([
            'shipment_id' => $shipment->id,
            'method' => 'midtrans',
            'status' => 'pending',
            'amount' => 10000,
            'midtrans_order_id' => 'ORDER-'.$shipment->tracking_number,
        ]);

        $response = $this->postJson(route('payments.midtrans.callback'), [
            'order_id' => $payment->midtrans_order_id,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'status_code' => '200',
            'gross_amount' => '10000',
            'signature_key' => 'invalid-signature',
            'transaction_id' => 'trx-123',
        ]);

        $response->assertStatus(403);

        $payment->refresh();

        $this->assertSame('error', $payment->processing_status);
        $this->assertNotNull($payment->processing_error);
        $this->assertDatabaseHas('error_logs', [
            'module' => 'integration.midtrans',
        ]);
        $this->assertDatabaseHas('error_logs', [
            'module' => 'payment',
            'message' => 'Signature Midtrans tidak valid.',
        ]);
    }

    public function test_tracking_failure_marks_shipment_processing_error(): void
    {
        $branch = $this->createBranch();
        $manager = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $this->createWorkflowStatuses();

        $shipment = Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-'.strtoupper(fake()->bothify('######')),
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000003',
            'sender_address' => 'Jl. Satu No. 1',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000004',
            'recipient_address' => 'Jl. Dua No. 2',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'total_amount' => 10000,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment tracking failure test.',
        ]);

        ShipmentTracking::creating(function () {
            throw new \RuntimeException('Forced tracking save failure.');
        });

        try {
            $response = $this->actingAs($manager)->postJson(route('shipment-trackings.store'), [
                'shipment_id' => $shipment->id,
                'status_id' => ShipmentStatus::query()->where('code', 'in_transit')->value('id'),
                'location' => 'Jakarta',
                'notes' => 'Kurir gagal menyimpan tracking.',
                'event_at' => now()->toDateTimeString(),
            ]);

            $response->assertStatus(500);
        } finally {
            ShipmentTracking::flushEventListeners();
        }

        $shipment->refresh();

        $this->assertSame('error', $shipment->processing_status);
        $this->assertNotNull($shipment->processing_error);
        $this->assertDatabaseHas('error_logs', [
            'module' => 'shipment',
            'message' => 'Tracking shipment gagal disimpan.',
        ]);
    }

    public function test_create_shipment_rolls_back_if_payment_create_fails(): void
    {
        $branch = $this->createBranch();
        $destinationBranch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $this->createWorkflowStatuses();

        Payment::creating(function () {
            throw new \RuntimeException('Forced payment create failure.');
        });

        try {
            $response = $this->actingAs($admin)->postJson(route('shipments.store'), [
                'branch_id' => $branch->id,
                'destination_branch_id' => $destinationBranch->id,
                'sender_name' => 'Pengirim',
                'sender_phone' => '081200000001',
                'sender_address' => 'Jl. Satu No. 1',
                'recipient_name' => 'Penerima',
                'recipient_phone' => '081200000002',
                'recipient_address' => 'Jl. Dua No. 2',
                'service_type' => 'regular',
                'total_weight_kg' => 1,
            ]);

            $response->assertStatus(500);
        } finally {
            Payment::flushEventListeners();
        }

        $this->assertSame(0, Shipment::query()->count());
        $this->assertSame(0, ShipmentTracking::query()->count());
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_assign_courier_rolls_back_if_tracking_create_fails(): void
    {
        $this->createWorkflowStatuses();
        $branch = $this->createBranch();
        $courier = User::factory()->state([
            'role' => 'courier',
            'branch_id' => $branch->id,
        ])->create();
        $vehicle = Vehicle::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $shipment = Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-'.strtoupper(fake()->bothify('######')),
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000003',
            'sender_address' => 'Jl. Satu No. 1',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000004',
            'recipient_address' => 'Jl. Dua No. 2',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'total_amount' => 10000,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment assign rollback test.',
        ]);

        ShipmentTracking::creating(function () {
            throw new \RuntimeException('Forced tracking create failure on assign.');
        });

        $thrown = false;

        try {
            app(ShipmentService::class)->assignCourier($shipment, $courier->id, $vehicle->id, $courier->id);
        } catch (\RuntimeException) {
            $thrown = true;
        } finally {
            ShipmentTracking::flushEventListeners();
        }

        $this->assertTrue($thrown);

        $shipment->refresh();

        $this->assertNull($shipment->courier_id);
        $this->assertNull($shipment->vehicle_id);
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

    private function createBranch(): Branch
    {
        return Branch::factory()->create([
        ]);
    }
}