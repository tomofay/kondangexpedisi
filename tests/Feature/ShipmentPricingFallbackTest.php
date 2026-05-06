<?php

namespace Tests\Feature;

use App\Models\AdminTask;
use App\Models\Branch;
use App\Models\RateCard;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentPricingFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_creation_uses_fallback_when_rate_card_is_missing(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $this->createWorkflowStatuses();

        $originBranch = Branch::factory()->create();
        $destinationBranch = Branch::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('shipments.store'), [
            'branch_id' => $branch->id,
            'destination_branch_id' => $destinationBranch->id,
            'sender_name' => 'Pengirim Fallback',
            'sender_phone' => '081200000101',
            'sender_address' => 'Jl. Asal No. 1',
            'recipient_name' => 'Penerima Fallback',
            'recipient_phone' => '081200000102',
            'recipient_address' => 'Jl. Tujuan No. 2',
            'service_type' => 'regular',
            'total_weight_kg' => 2,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.pricing_mode', 'auto')
            ->assertJsonPath('data.processing_status', 'needs_manual_review')
            ->assertJsonPath('data.pricing_approval_status', 'pending');

        $shipment = Shipment::query()->latest('id')->first();

        $this->assertNotNull($shipment);
        $this->assertNotNull($shipment->auto_total_amount);
        $this->assertSame((string) $shipment->auto_total_amount, (string) $shipment->total_amount);
        $this->assertSame('pending', $shipment->pricing_approval_status);
        $this->assertDatabaseHas('admin_tasks', [
            'task_type' => 'shipment_pricing_fallback_review',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_request_pricing_override_with_approval_flow(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $this->createWorkflowStatuses();

        $originBranch = Branch::factory()->create();
        $destinationBranch = Branch::factory()->create();

        RateCard::query()->create([
            'origin_branch_id' => $originBranch->id,
            'destination_branch_id' => $destinationBranch->id,
            'service_type' => 'regular',
            'min_weight_kg' => 0,
            'max_weight_kg' => 10,
            'base_price' => 10000,
            'per_kg_price' => 5000,
            'insurance_fee' => 0,
            'is_active' => true,
        ]);

        $shipment = Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-REQ-001',
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000201',
            'sender_address' => 'Jl. Satu',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000202',
            'recipient_address' => 'Jl. Dua',
            'service_type' => 'regular',
            'total_weight_kg' => 2,
            'total_volume' => 1,
            'subtotal_amount' => 20000,
            'total_amount' => 20000,
            'auto_subtotal_amount' => 20000,
            'auto_total_amount' => 20000,
            'payment_status' => 'pending',
            'processing_status' => 'ok',
            'processing_error' => null,
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'not_required',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment request approval test',
        ]);

        $response = $this->actingAs($admin)->postJson(route('shipments.pricing-override.request', $shipment), [
            'subtotal_amount' => 30000,
            'total_amount' => 30000,
            'reason' => 'Koreksi harga setelah verifikasi dimensi aktual.',
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.shipment_id', $shipment->id)
            ->assertJsonPath('data.pricing_approval_status', 'pending');

        $this->assertDatabaseHas('admin_tasks', [
            'task_type' => 'shipment_pricing_override_approval',
            'status' => 'pending',
        ]);

        $shipment->refresh();
        $this->assertSame('pending', $shipment->pricing_approval_status);
        $this->assertSame('needs_manual_review', $shipment->processing_status);
    }

    public function test_admin_can_approve_requested_pricing_override(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $adminApprover = User::factory()->state(['role' => 'admin'])->create();
        $this->createWorkflowStatuses();

        $shipment = Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-APP-001',
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000301',
            'sender_address' => 'Jl. Satu',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000302',
            'recipient_address' => 'Jl. Dua',
            'service_type' => 'regular',
            'total_weight_kg' => 2,
            'total_volume' => 1,
            'subtotal_amount' => 20000,
            'total_amount' => 20000,
            'auto_subtotal_amount' => 20000,
            'auto_total_amount' => 20000,
            'payment_status' => 'pending',
            'processing_status' => 'needs_manual_review',
            'processing_error' => 'Menunggu approval override tarif manual.',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'pending',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment approve override test',
        ]);

        $task = AdminTask::query()->create([
            'task_type' => 'shipment_pricing_override_approval',
            'title' => 'Approval Override Tarif Shipment '.$shipment->tracking_number,
            'description' => 'Koreksi manual ongkir diperlukan.',
            'assigned_to' => null,
            'created_by' => $admin->id,
            'status' => 'pending',
            'priority' => 'high',
            'action_data' => [
                'shipment_id' => $shipment->id,
                'proposed_amounts' => [
                    'subtotal_amount' => 28000,
                    'total_amount' => 28000,
                ],
                'reason' => 'Perlu koreksi karena mismatch dimensi aktual.',
            ],
        ]);

        $response = $this->actingAs($adminApprover)->postJson(route('shipments.pricing-override.approve', $shipment), [
            'approval_note' => 'Disetujui setelah verifikasi admin.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.pricing_mode', 'manual')
            ->assertJsonPath('data.pricing_approval_status', 'approved')
            ->assertJsonPath('data.corrected_total_amount', '28000.00');

        $shipment->refresh();
        $task->refresh();

        $this->assertSame('manual', $shipment->pricing_mode);
        $this->assertSame('approved', $shipment->pricing_approval_status);
        $this->assertSame((string) $shipment->auto_total_amount, '20000.00');
        $this->assertSame((string) $shipment->corrected_total_amount, '28000.00');
        $this->assertSame('completed', $task->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shipment.manual_override',
            'subject_type' => Shipment::class,
            'subject_id' => $shipment->id,
        ]);
    }

    public function test_admin_can_reject_requested_pricing_override(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $adminApprover = User::factory()->state(['role' => 'admin'])->create();
        $this->createWorkflowStatuses();

        $shipment = Shipment::query()->create([
            'tracking_number' => 'SXP-TEST-REJ-001',
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Pengirim',
            'sender_phone' => '081200000401',
            'sender_address' => 'Jl. Satu',
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081200000402',
            'recipient_address' => 'Jl. Dua',
            'service_type' => 'regular',
            'total_weight_kg' => 2,
            'total_volume' => 1,
            'subtotal_amount' => 20000,
            'total_amount' => 20000,
            'auto_subtotal_amount' => 20000,
            'auto_total_amount' => 20000,
            'payment_status' => 'pending',
            'processing_status' => 'needs_manual_review',
            'processing_error' => 'Menunggu approval override tarif manual.',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'pending',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
            'notes' => 'Shipment reject override test',
        ]);

        $task = AdminTask::query()->create([
            'task_type' => 'shipment_pricing_override_approval',
            'title' => 'Approval Override Tarif Shipment '.$shipment->tracking_number,
            'description' => 'Koreksi manual ongkir diperlukan.',
            'assigned_to' => null,
            'created_by' => $admin->id,
            'status' => 'pending',
            'priority' => 'high',
            'action_data' => [
                'shipment_id' => $shipment->id,
                'proposed_amounts' => [
                    'subtotal_amount' => 28000,
                    'total_amount' => 28000,
                ],
                'reason' => 'Perlu koreksi karena mismatch dimensi aktual.',
            ],
        ]);

        $response = $this->actingAs($adminApprover)->postJson(route('shipments.pricing-override.reject', $shipment), [
            'rejection_reason' => 'Data dimensi belum valid, override tidak disetujui.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.pricing_mode', 'auto')
            ->assertJsonPath('data.pricing_approval_status', 'rejected')
            ->assertJsonPath('data.total_amount', '20000.00');

        $shipment->refresh();
        $task->refresh();

        $this->assertSame('auto', $shipment->pricing_mode);
        $this->assertSame('rejected', $shipment->pricing_approval_status);
        $this->assertSame((string) $shipment->total_amount, '20000.00');
        $this->assertSame((string) $shipment->auto_total_amount, '20000.00');
        $this->assertNull($shipment->corrected_total_amount);
        $this->assertSame('cancelled', $task->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shipment.pricing_override_rejected',
            'subject_type' => Shipment::class,
            'subject_id' => $shipment->id,
        ]);
    }

    public function test_admin_can_list_pricing_approval_inbox_with_status_filter(): void
    {
        $branch = Branch::factory()->create();
        $admin = User::factory()->state(['role' => 'manager', 'branch_id' => $branch->id])->create();
        $this->createWorkflowStatuses();

        $shipmentPending = Shipment::query()->create([
            'tracking_number' => 'SXP-INBOX-PENDING-001',
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'A',
            'sender_phone' => '0811',
            'sender_address' => 'A',
            'recipient_name' => 'B',
            'recipient_phone' => '0822',
            'recipient_address' => 'B',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 10000,
            'total_amount' => 10000,
            'auto_total_amount' => 10000,
            'payment_status' => 'pending',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'pending',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);

        $shipmentApproved = Shipment::query()->create([
            'tracking_number' => 'SXP-INBOX-APPROVED-001',
            'branch_id' => $branch->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'C',
            'sender_phone' => '0833',
            'sender_address' => 'C',
            'recipient_name' => 'D',
            'recipient_phone' => '0844',
            'recipient_address' => 'D',
            'service_type' => 'regular',
            'total_weight_kg' => 1,
            'total_volume' => 1,
            'subtotal_amount' => 12000,
            'total_amount' => 12000,
            'auto_total_amount' => 12000,
            'corrected_total_amount' => 15000,
            'payment_status' => 'pending',
            'pricing_mode' => 'manual',
            'pricing_approval_status' => 'approved',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);

        AdminTask::query()->create([
            'task_type' => 'shipment_pricing_override_approval',
            'title' => 'Pending approval task',
            'description' => 'Pending approval',
            'created_by' => $admin->id,
            'status' => 'pending',
            'priority' => 'high',
            'action_data' => [
                'shipment_id' => $shipmentPending->id,
                'current_amounts' => ['total_amount' => 10000],
                'proposed_amounts' => ['total_amount' => 15000],
            ],
        ]);

        AdminTask::query()->create([
            'task_type' => 'shipment_pricing_override_approval',
            'title' => 'Approved task',
            'description' => 'Approved override',
            'created_by' => $admin->id,
            'status' => 'completed',
            'priority' => 'high',
            'action_data' => [
                'shipment_id' => $shipmentApproved->id,
                'current_amounts' => ['total_amount' => 12000],
                'proposed_amounts' => ['total_amount' => 15000],
            ],
            'result' => [
                'decision' => 'approved',
            ],
        ]);

        $response = $this->actingAs($admin)->getJson(route('shipments.pricing-approval-inbox', [
            'status' => 'approved',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.applied_filter', 'approved')
            ->assertJsonPath('items.0.decision', 'approved')
            ->assertJsonPath('items.0.shipment.id', $shipmentApproved->id)
            ->assertJsonPath('summary.pending', 1)
            ->assertJsonPath('summary.approved', 1);
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
}
