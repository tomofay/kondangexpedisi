<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\RateCard;
use App\Models\RateCardApproval;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use App\Services\OperationalIssueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowAndDataSplitTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_reconciliation_separates_automatic_and_manual_data(): void
    {
        $this->createWorkflowStatuses();

        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);

        $autoShipment = Shipment::query()->create([
            'tracking_number' => 'SXP-AUTO-'.strtoupper(fake()->bothify('#####')),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Auto Sender',
            'sender_phone' => '08120001001',
            'sender_address' => 'Alamat auto',
            'recipient_name' => 'Auto Receiver',
            'recipient_phone' => '08120001002',
            'recipient_address' => 'Alamat auto tujuan',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'insurance_amount' => 0,
            'admin_fee' => 2500,
            'total_amount' => 12500,
            'auto_subtotal_amount' => 10000,
            'auto_insurance_amount' => 0,
            'auto_admin_fee' => 2500,
            'auto_total_amount' => 12500,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'not_required',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);

        $manualShipment = Shipment::query()->create([
            'tracking_number' => 'SXP-MAN-'.strtoupper(fake()->bothify('#####')),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Manual Sender',
            'sender_phone' => '08120002001',
            'sender_address' => 'Alamat manual',
            'recipient_name' => 'Manual Receiver',
            'recipient_phone' => '08120002002',
            'recipient_address' => 'Alamat manual tujuan',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'insurance_amount' => 0,
            'admin_fee' => 2500,
            'total_amount' => 12500,
            'auto_subtotal_amount' => 10000,
            'auto_insurance_amount' => 0,
            'auto_admin_fee' => 2500,
            'auto_total_amount' => 12500,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'pricing_mode' => 'manual',
            'pricing_approval_status' => 'approved',
            'manual_override_by' => $admin->id,
            'manual_override_reason' => 'Koreksi tarif',
            'manual_override_at' => now(),
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);

        $manualShipment->payments()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
            'method' => 'midtrans',
            'amount' => 12500,
            'processed_by' => $admin->id,
            'processing_status' => 'ok',
            'manual_override_by' => $admin->id,
            'manual_override_reason' => 'Koreksi payment',
            'manual_override_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('reports.daily-reconciliation', [
            'date' => now()->toDateString(),
            'limit' => 10,
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.summary.automatic_shipments', 1)
            ->assertJsonPath('data.summary.manual_shipments', 1)
            ->assertJsonPath('data.summary.manual_payments', 1);

        $exceptions = collect($response->json('data.exceptions'));

        $this->assertTrue($exceptions->contains(fn (array $item) => ($item['data_origin']['shipment'] ?? null) === 'manual_override'));
    }

    public function test_rate_card_update_requires_approval_and_applies_after_approval(): void
    {
        $originBranch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $originBranch->id])->create();
        $destinationBranch = Branch::factory()->create();
        $rateCard = RateCard::query()->create([
            'origin_branch_id' => $originBranch->id,
            'destination_branch_id' => $destinationBranch->id,
            'service_type' => 'regular',
            'min_weight_kg' => 1,
            'max_weight_kg' => 10,
            'base_price' => 15000,
            'per_kg_price' => 7000,
            'insurance_fee' => 1000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(route('rate-cards.update', $rateCard), [
            'base_price' => 18000,
            'per_kg_price' => 8000,
            'insurance_fee' => 1200,
            'reason' => 'Penyesuaian tarif',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('message', 'Pengajuan perubahan rate card dikirim ke admin untuk disetujui.');

        $this->assertDatabaseHas('rate_card_approvals', [
            'rate_card_id' => $rateCard->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('rate_cards', [
            'id' => $rateCard->id,
            'base_price' => 15000,
        ]);

        $approval = RateCardApproval::query()->where('rate_card_id', $rateCard->id)->latest('id')->firstOrFail();

        $centerAdmin = User::factory()->state(['role' => 'admin'])->create();
        $approveResponse = $this->actingAs($centerAdmin)->postJson(route('approvals.rate-cards.approve', $approval), [
            'note' => 'Disetujui setelah review.',
        ]);

        $approveResponse->assertOk();

        $this->assertDatabaseHas('rate_cards', [
            'id' => $rateCard->id,
            'base_price' => 18000,
            'per_kg_price' => 8000,
            'insurance_fee' => 1200,
        ]);

        $this->assertDatabaseHas('rate_card_approvals', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);
    }

    public function test_final_status_transition_requires_approval_and_applies_after_approval(): void
    {
        $this->createWorkflowStatuses();

        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $courier = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courier->id, 'out_for_delivery');

        $response = $this->actingAs($courier)->postJson(route('shipments.transition-status', $shipment), [
            'status_code' => 'delivered',
            'location' => 'Drop point',
            'notes' => 'Paket selesai diantar',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.task_status', 'pending');

        $this->assertDatabaseHas('admin_tasks', [
            'task_type' => 'shipment_final_status_approval',
            'status' => 'pending',
        ]);

        $task = \App\Models\AdminTask::query()->where('task_type', 'shipment_final_status_approval')->latest('id')->firstOrFail();

        $approveResponse = $this->actingAs($admin)->postJson(route('approvals.tasks.approve', $task), [
            'note' => 'Final status validated.',
        ]);

        $approveResponse->assertOk();

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status_id' => ShipmentStatus::query()->where('code', 'delivered')->value('id'),
        ]);
    }

    public function test_running_shipment_reassign_requires_approval(): void
    {
        $this->createWorkflowStatuses();

        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $courierA = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();
        $courierB = User::factory()->state(['role' => 'courier', 'branch_id' => $branch->id])->create();

        $shipment = $this->createShipment($branch, $courierA->id, 'in_transit');

        $response = $this->actingAs($admin)->postJson(route('shipments.assign-courier', $shipment), [
            'courier_id' => $courierB->id,
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.task_status', 'pending');

        $task = \App\Models\AdminTask::query()->where('task_type', 'shipment_reassign_approval')->latest('id')->firstOrFail();

        $this->actingAs($admin)->postJson(route('approvals.tasks.approve', $task))->assertOk();

        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'courier_id' => $courierB->id,
        ]);
    }

    public function test_manual_payment_settlement_requires_approval(): void
    {
        $this->createWorkflowStatuses();

        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $shipment = $this->createShipment($branch, null, 'pending');

        $payment = Payment::query()->create([
            'shipment_id' => $shipment->id,
            'customer_id' => $shipment->customer_id,
            'processed_by' => $admin->id,
            'method' => 'midtrans',
            'status' => 'pending',
            'processing_status' => 'ok',
            'amount' => 12500,
            'notes' => 'Original payment',
        ]);

        $response = $this->actingAs($admin)->putJson(route('payments.update', $payment), [
            'status' => 'settlement',
            'manual_override' => true,
            'manual_override_reason' => 'Settlement manual setelah pengecekan',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.task_status', 'pending');

        $task = \App\Models\AdminTask::query()->where('task_type', 'payment_manual_status_approval')->latest('id')->firstOrFail();

        $this->actingAs($admin)->postJson(route('approvals.tasks.approve', $task))->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'settlement',
            'manual_override_by' => $admin->id,
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

    private function createShipment(Branch $branch, ?int $courierId, string $statusCode): Shipment
    {
        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);

        return Shipment::query()->create([
            'tracking_number' => 'SXP-APR-'.strtoupper(fake()->bothify('######')),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'courier_id' => $courierId,
            'status_id' => ShipmentStatus::query()->where('code', $statusCode)->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000001',
            'sender_address' => 'Alamat pengirim',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000002',
            'recipient_address' => 'Alamat penerima',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'insurance_amount' => 0,
            'admin_fee' => 2500,
            'total_amount' => 12500,
            'auto_subtotal_amount' => 10000,
            'auto_insurance_amount' => 0,
            'auto_admin_fee' => 2500,
            'auto_total_amount' => 12500,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'not_required',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);
    }
}
