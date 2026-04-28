<?php

namespace Tests\Feature;

use App\Jobs\RetryMidtransCallbackJob;
use App\Jobs\RetryTrackingSyncJob;
use App\Models\AdminTask;
use App\Models\AuditLog;
use App\Models\ErrorLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ErrorLogEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_error_logs(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'payment',
            'message' => 'Midtrans timeout',
            'stack_trace' => null,
            'context' => ['order_id' => 'ORDER-001'],
            'severity' => 'high',
        ]);

        $response = $this->actingAs($admin)->getJson(route('error-logs.index', [
            'module' => 'payment',
            'resolved' => '0',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $errorLog->id);
    }

    public function test_admin_can_resolve_error_logs_and_audit_is_recorded(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'shipment',
            'message' => 'Tracking save failed',
            'stack_trace' => 'sample stack',
            'context' => ['shipment_id' => 1],
            'severity' => 'critical',
        ]);

        $response = $this->actingAs($admin)->postJson(route('error-logs.resolve', $errorLog), [
            'reason' => 'Sudah diperbaiki manual setelah retry.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $errorLog->id)
            ->assertJsonPath('data.resolved_at', fn ($value) => $value !== null);

        $this->assertNotNull($errorLog->fresh()->resolved_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'error_log.resolve',
            'subject_type' => ErrorLog::class,
            'subject_id' => $errorLog->id,
            'actor_id' => $admin->id,
        ]);
    }

    public function test_admin_can_view_unresolved_queue_with_retry_metadata(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'integration.midtrans',
            'message' => 'Callback Midtrans gagal diproses.',
            'context' => [
                'payload' => [
                    'order_id' => 'ORDER-001',
                    'transaction_status' => 'settlement',
                ],
            ],
            'severity' => 'critical',
        ]);

        $response = $this->actingAs($admin)->getJson(route('error-logs.unresolved-queue'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('items.0.retry.capable', true)
            ->assertJsonPath('items.0.retry.type', 'midtrans_callback');
    }

    public function test_admin_can_dispatch_retry_job_for_midtrans_error_log(): void
    {
        Queue::fake();

        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'integration.midtrans',
            'message' => 'Callback Midtrans gagal diproses.',
            'context' => [
                'payload' => [
                    'order_id' => 'ORDER-001',
                    'transaction_status' => 'settlement',
                    'fraud_status' => 'accept',
                ],
                'payment_id' => 1,
            ],
            'severity' => 'critical',
        ]);

        $response = $this->actingAs($admin)->postJson(route('error-logs.retry', $errorLog));

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.retry_type', 'midtrans_callback');

        Queue::assertPushed(RetryMidtransCallbackJob::class, function (RetryMidtransCallbackJob $job) use ($errorLog) {
            return $job->errorLogId === $errorLog->id;
        });
    }

    public function test_admin_can_dispatch_retry_job_for_tracking_sync_error_log(): void
    {
        Queue::fake();

        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'shipment',
            'message' => 'Tracking shipment gagal disimpan.',
            'context' => [
                'operation' => 'create',
                'tracking_payload' => [
                    'shipment_id' => 1,
                    'status_id' => 1,
                ],
            ],
            'severity' => 'high',
        ]);

        $response = $this->actingAs($admin)->postJson(route('error-logs.retry', $errorLog));

        $response
            ->assertStatus(202)
            ->assertJsonPath('data.retry_type', 'tracking_sync');

        Queue::assertPushed(RetryTrackingSyncJob::class, function (RetryTrackingSyncJob $job) use ($errorLog) {
            return $job->errorLogId === $errorLog->id;
        });
    }

    public function test_dead_letter_monitoring_returns_retry_job_failure_totals(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        DB::table('failed_jobs')->insert([
            [
                'uuid' => (string) fake()->uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => json_encode(['displayName' => RetryMidtransCallbackJob::class]),
                'exception' => 'RuntimeException',
                'failed_at' => now(),
            ],
            [
                'uuid' => (string) fake()->uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => json_encode(['displayName' => RetryTrackingSyncJob::class]),
                'exception' => 'RuntimeException',
                'failed_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->getJson(route('error-logs.dead-letter-monitoring'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.retry_jobs.midtrans_callback.failed_count', 1)
            ->assertJsonPath('data.retry_jobs.tracking_sync.failed_count', 1)
            ->assertJsonPath('data.total_failed_jobs', 2);
    }

    public function test_admin_can_view_manual_dead_letter_queue(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'integration.midtrans',
            'message' => 'Retry exhausted',
            'context' => ['order_id' => 'ORDER-001'],
            'severity' => 'critical',
        ]);

        $task = AdminTask::query()->create([
            'task_type' => 'retry_dead_letter',
            'title' => 'Manual Follow-up: integration.midtrans',
            'description' => 'Retry callback Midtrans gagal setelah 3 percobaan.',
            'created_by' => $admin->id,
            'status' => 'pending',
            'priority' => 'high',
            'action_data' => [
                'error_log_id' => $errorLog->id,
            ],
        ]);

        $response = $this->actingAs($admin)->getJson(route('error-logs.manual-dead-letter-queue'));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('items.0.task_id', $task->id)
            ->assertJsonPath('items.0.error_log.id', $errorLog->id)
            ->assertJsonPath('summary.pending_total', 1);
    }

    public function test_admin_can_escalate_error_log_to_manual_dead_letter_queue(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'shipment',
            'message' => 'Tracking shipment gagal disimpan.',
            'context' => ['operation' => 'create'],
            'severity' => 'high',
        ]);

        $response = $this->actingAs($admin)->postJson(route('error-logs.escalate-manual', $errorLog), [
            'reason' => 'Butuh investigasi manual oleh admin operasional.',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.error_log_id', $errorLog->id)
            ->assertJsonPath('data.task_status', 'pending');

        $this->assertDatabaseHas('admin_tasks', [
            'task_type' => 'retry_dead_letter',
            'created_by' => $admin->id,
            'status' => 'pending',
        ]);
    }

    public function test_retry_midtrans_failed_escalates_to_manual_dead_letter_task(): void
    {
        $admin = User::factory()->state(['role' => 'admin'])->create();

        $errorLog = ErrorLog::query()->create([
            'error_type' => 'exception',
            'module' => 'integration.midtrans',
            'message' => 'Callback Midtrans gagal diproses.',
            'context' => [
                'payload' => [
                    'order_id' => 'ORDER-EXHAUSTED',
                ],
            ],
            'severity' => 'critical',
            'user_id' => $admin->id,
        ]);

        $job = new RetryMidtransCallbackJob($errorLog->id);
        $job->failed(new \RuntimeException('Retry exhausted.'));

        $this->assertDatabaseHas('admin_tasks', [
            'task_type' => 'retry_dead_letter',
            'created_by' => $admin->id,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'error_log.escalate_dead_letter',
            'subject_type' => ErrorLog::class,
            'subject_id' => $errorLog->id,
        ]);
    }
}