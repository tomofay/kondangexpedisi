<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MobileRoleApiSegregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_mobile_shipments_returns_compact_payload_for_own_shipments_only(): void
    {
        $this->createWorkflowStatuses();
        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        $customer = Customer::factory()->create([
            'user_id' => $customerUser->id,
            'phone' => '081200000001',
        ]);

        $otherCustomer = Customer::factory()->create();

        $ownShipment = $this->createShipment($branch, [
            'customer_id' => $customer->id,
            'recipient_address' => 'Alamat Customer Sendiri',
        ]);

        $this->createShipment($branch, [
            'customer_id' => $otherCustomer->id,
        ]);

        $response = $this->withMobileToken($customerUser)->getJson(route('mobile.customer.shipments.index'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('items.0.id', $ownShipment->id)
            ->assertJsonMissingPath('items.0.recipient_address')
            ->assertJsonMissingPath('items.0.sender_address');
    }

    public function test_courier_mobile_shipments_only_contains_assigned_shipments(): void
    {
        $this->createWorkflowStatuses();
        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $courier = User::factory()->state([
            'role' => 'courier',
            'branch_id' => $branch->id,
        ])->create();

        $anotherCourier = User::factory()->state([
            'role' => 'courier',
            'branch_id' => $branch->id,
        ])->create();

        $assignedShipment = $this->createShipment($branch, [
            'courier_id' => $courier->id,
            'recipient_phone' => '081234567890',
        ]);

        $this->createShipment($branch, [
            'courier_id' => $anotherCourier->id,
        ]);

        $response = $this->withMobileToken($courier)->getJson(route('mobile.courier.shipments.index'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('items.0.id', $assignedShipment->id)
            ->assertJsonMissingPath('items.0.recipient_phone');
    }

    public function test_legacy_mobile_admin_kasir_contract_is_disabled(): void
    {
        $this->assertFalse(Route::has('mobile.admin-kasir.shipments.index'));
        $this->assertFalse(Route::has('mobile.admin-kasir.payments.index'));
    }

    public function test_role_cannot_access_other_role_mobile_endpoint(): void
    {
        $this->createWorkflowStatuses();
        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        Customer::factory()->create(['user_id' => $customerUser->id]);

        $courier = User::factory()->state([
            'role' => 'courier',
            'branch_id' => $branch->id,
        ])->create();

        $this->createShipment($branch, ['courier_id' => $courier->id]);

        $response = $this->withMobileToken($customerUser)->getJson(route('mobile.courier.shipments.index'));

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

    private function createShipment(Branch $branch, array $overrides): Shipment
    {
        $statusId = ShipmentStatus::query()->where('code', 'pending')->value('id');

        return Shipment::query()->create(array_merge([
            'tracking_number' => 'SXP-MOBILE-'.strtoupper(fake()->bothify('######')),
            'customer_id' => null,
            'branch_id' => $branch->id,
            'courier_id' => null,
            'zone_id' => $branch->zone_id,
            'status_id' => $statusId,
            'sender_name' => 'Sender Mobile',
            'sender_phone' => '081200000111',
            'sender_address' => 'Alamat Sender',
            'recipient_name' => 'Recipient Mobile',
            'recipient_phone' => '081200000222',
            'recipient_address' => 'Alamat Recipient',
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
        ], $overrides));
    }
}
