<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_manual_corrections_only(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        AuditLog::query()->create([
            'action' => 'shipment.manual_override',
            'subject_type' => 'App\\Models\\Shipment',
            'subject_id' => 1,
            'actor_id' => $admin->id,
            'before_state' => ['total_amount' => 10000],
            'after_state' => ['total_amount' => 12000],
            'notes' => 'Koreksi manual karena dispute invoice.',
            'source' => 'user_action',
            'is_manual_correction' => true,
            'correction_reference' => 'DISPUTE-INV-001',
            'changed_fields' => [
                'total_amount' => ['from' => 10000, 'to' => 12000],
            ],
        ]);

        AuditLog::query()->create([
            'action' => 'shipment.create',
            'subject_type' => 'App\\Models\\Shipment',
            'subject_id' => 2,
            'actor_id' => null,
            'before_state' => [],
            'after_state' => ['tracking_number' => 'SXP-001'],
            'notes' => 'Pembuatan shipment otomatis.',
            'source' => 'system_automatic',
            'is_manual_correction' => false,
            'correction_reference' => null,
            'changed_fields' => [
                'tracking_number' => ['from' => null, 'to' => 'SXP-001'],
            ],
        ]);

        $response = $this->actingAs($admin)->getJson(route('audit-logs.manual-corrections'));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.action', 'shipment.manual_override')
            ->assertJsonPath('data.0.is_manual_correction', true)
            ->assertJsonPath('data.0.correction_reference', 'DISPUTE-INV-001');

        $this->assertCount(1, $response->json('data'));
    }
}