<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlutterApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_login_and_use_bearer_token_for_mobile_api(): void
    {
        $this->createWorkflowStatuses();

        $zone = Zone::factory()->create();
        $branch = Branch::factory()->create(['zone_id' => $zone->id]);

        $customerUser = User::factory()->state([
            'role' => 'customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('secret123'),
        ])->create();

        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);

        Shipment::query()->create([
            'tracking_number' => 'SXP-TKN-'.strtoupper(fake()->bothify('######')),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'zone_id' => $zone->id,
            'status_id' => ShipmentStatus::query()->where('code', 'pending')->value('id'),
            'sender_name' => 'Sender',
            'sender_phone' => '081234567890',
            'sender_address' => 'Alamat A',
            'recipient_name' => 'Recipient',
            'recipient_phone' => '081234567891',
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

        $login = $this->postJson(route('mobile.auth.login'), [
            'email' => 'customer@example.com',
            'password' => 'secret123',
            'device_name' => 'flutter-device-1',
        ]);

        $login
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.role', 'customer');

        $token = $login->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('mobile.customer.shipments.index'))
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_mobile_api_rejects_web_session_fallback_without_bearer_token(): void
    {
        $this->createWorkflowStatuses();

        $customerUser = User::factory()->state(['role' => 'customer'])->create();
        Customer::factory()->create(['user_id' => $customerUser->id]);

        $this->actingAs($customerUser, 'web')
            ->getJson(route('mobile.customer.shipments.index'))
            ->assertStatus(401);
    }

    public function test_non_mobile_internal_role_cannot_login_to_mobile_auth(): void
    {
        $admin = User::factory()->state([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ])->create();

        $response = $this->postJson(route('mobile.auth.login'), [
            'email' => $admin->email,
            'password' => 'secret123',
            'device_name' => 'flutter-device-2',
        ]);

        $response->assertStatus(403);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->state(['role' => 'courier'])->create();

        $this->withMobileToken($user)->postJson(route('mobile.auth.logout'))->assertOk();

        $this->assertSame(0, $user->fresh()->tokens()->count());
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
