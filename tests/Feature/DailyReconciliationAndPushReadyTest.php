<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReconciliationAndPushReadyTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_reconciliation_returns_exceptions_and_summary(): void
    {
        $this->createWorkflowStatuses();

        $admin = User::factory()->state(['role' => 'admin'])->create();
        $branch = Branch::factory()->create();

        $deliveredShipment = $this->createShipment($branch, 'delivered', 'paid');
        $deliveredShipment->payments()->create([
            'customer_id' => $deliveredShipment->customer_id,
            'status' => 'settlement',
            'method' => 'midtrans',
            'amount' => $deliveredShipment->total_amount,
            'processed_by' => $admin->id,
        ]);
        $deliveredStatusId = ShipmentStatus::query()->where('code', 'delivered')->value('id');
        $deliveredShipment->trackings()->create([
            'status_id' => $deliveredStatusId,
            'created_by' => $admin->id,
            'location' => 'Gudang pusat',
            'notes' => 'Shipment diterima pelanggan.',
            'event_at' => now(),
        ]);

        $pendingShipment = $this->createShipment($branch, 'pending', 'pending');
        $pendingShipment->payments()->create([
            'customer_id' => $pendingShipment->customer_id,
            'status' => 'pending',
            'method' => 'midtrans',
            'amount' => $pendingShipment->total_amount,
            'processed_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->getJson(route('reports.daily-reconciliation', [
            'date' => now()->toDateString(),
            'limit' => 10,
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.summary.shipments_checked', 2)
            ->assertJsonPath('data.summary.matched_shipments', 1)
            ->assertJsonPath('data.summary.exception_shipments', 1)
            ->assertJsonPath('data.summary.issue_breakdown.0.code', 'missing_tracking')
            ->assertJsonPath('data.meta.total_exceptions', 1)
            ->assertJsonPath('data.exceptions.0.tracking_number', $pendingShipment->tracking_number)
            ->assertJsonPath('data.exceptions.0.manual_action.action', 'sync_tracking');
    }

    public function test_push_ready_returns_badge_count_and_latest_five_notifications(): void
    {
        $user = User::factory()->state(['role' => 'customer'])->create();

        $notifications = collect(range(1, 7))->map(function (int $index) use ($user) {
            return AppNotification::query()->create([
                'recipient_user_id' => $user->id,
                'type' => 'shipment_status_updated',
                'title' => 'Notif '.$index,
                'message' => 'Pesan '.$index,
                'priority' => 'medium',
                'data' => ['index' => $index],
                'read_at' => $index <= 4 ? now() : null,
            ]);
        });

        $response = $this->actingAs($user)->getJson(route('customer.notifications.push-ready'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.badge_count', 3)
            ->assertJsonCount(5, 'data.latest_notifications')
            ->assertJsonPath('data.latest_notifications.0.title', 'Notif 7')
            ->assertJsonPath('data.latest_notifications.4.title', 'Notif 3');

        $this->assertTrue($notifications->last()->recipient_user_id === $user->id);
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

    private function createShipment(Branch $branch, string $statusCode, string $paymentStatus): Shipment
    {
        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $statusId = ShipmentStatus::query()->where('code', $statusCode)->value('id');

        return Shipment::query()->create([
            'tracking_number' => 'SXP-RC-'.strtoupper(fake()->bothify('######')),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'courier_id' => null,
            'vehicle_id' => null,
            'status_id' => $statusId,
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
            'payment_status' => $paymentStatus,
            'processing_status' => 'ok',
            'pricing_mode' => 'auto',
            'pricing_approval_status' => 'not_required',
            'current_status_at' => now(),
            'estimated_delivery_at' => now()->addDay(),
        ]);
    }
}
