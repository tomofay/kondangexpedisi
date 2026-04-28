<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Services\AuditLogService;
use App\Services\OperationalIssueService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class RetryTrackingSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [20, 90, 240];

    public function __construct(public readonly int $errorLogId)
    {
    }

    public function handle(OperationalIssueService $operationalIssueService, AuditLogService $auditLogService): void
    {
        $errorLog = ErrorLog::query()->find($this->errorLogId);

        if (! $errorLog || $errorLog->resolved_at) {
            return;
        }

        $context = is_array($errorLog->context) ? $errorLog->context : [];
        $operation = (string) ($context['operation'] ?? '');
        $payload = is_array($context['tracking_payload'] ?? null) ? $context['tracking_payload'] : [];
        $trackingId = isset($context['tracking_id']) ? (int) $context['tracking_id'] : null;

        if (! in_array($operation, ['create', 'update', 'delete'], true)) {
            throw new RuntimeException('Operation tracking retry tidak dikenali.');
        }

        DB::transaction(function () use ($operation, $payload, $trackingId, $auditLogService) {
            if ($operation === 'create') {
                $tracking = ShipmentTracking::query()->create([
                    'shipment_id' => $payload['shipment_id'],
                    'status_id' => $payload['status_id'],
                    'created_by' => $payload['created_by'] ?? null,
                    'location' => $payload['location'] ?? null,
                    'notes' => $payload['notes'] ?? 'Retry sinkronisasi tracking.',
                    'event_at' => $payload['event_at'] ?? now(),
                ]);

                $auditLogService->record(
                    'shipment_tracking.retry_create',
                    $tracking,
                    null,
                    [],
                    $tracking->fresh()->only(['shipment_id', 'status_id', 'location', 'event_at']),
                    'Retry create tracking berhasil diproses.',
                    [
                        'source' => 'system_retry',
                        'is_manual_correction' => false,
                    ]
                );

                return;
            }

            $tracking = ShipmentTracking::query()->find($trackingId);

            if (! $tracking) {
                throw new RuntimeException('Data tracking untuk retry tidak ditemukan.');
            }

            if ($operation === 'update') {
                $before = $tracking->only(['shipment_id', 'status_id', 'created_by', 'location', 'notes', 'event_at']);

                $tracking->update([
                    'shipment_id' => $payload['shipment_id'] ?? $tracking->shipment_id,
                    'status_id' => $payload['status_id'] ?? $tracking->status_id,
                    'created_by' => $payload['created_by'] ?? $tracking->created_by,
                    'location' => $payload['location'] ?? $tracking->location,
                    'notes' => $payload['notes'] ?? $tracking->notes,
                    'event_at' => $payload['event_at'] ?? $tracking->event_at,
                ]);

                $auditLogService->record(
                    'shipment_tracking.retry_update',
                    $tracking,
                    null,
                    $before,
                    $tracking->fresh()->only(['shipment_id', 'status_id', 'location', 'event_at', 'notes']),
                    'Retry update tracking berhasil diproses.',
                    [
                        'source' => 'system_retry',
                        'is_manual_correction' => false,
                    ]
                );

                return;
            }

            $before = $tracking->only(['shipment_id', 'status_id', 'location', 'event_at', 'notes']);
            $tracking->delete();

            $auditLogService->record(
                'shipment_tracking.retry_delete',
                $tracking,
                null,
                $before,
                [],
                'Retry delete tracking berhasil diproses.',
                [
                    'source' => 'system_retry',
                    'is_manual_correction' => false,
                ]
            );
        });

        $shipmentId = (int) ($payload['shipment_id'] ?? 0);

        if ($shipmentId > 0) {
            $shipment = Shipment::query()->find($shipmentId);

            if ($shipment) {
                $operationalIssueService->clearShipmentError($shipment);
            }
        }

        $operationalIssueService->resolveErrorLog($errorLog, null, 'Retry tracking sync berhasil.');
    }

    public function failed(Throwable $exception): void
    {
        $errorLog = ErrorLog::query()->find($this->errorLogId);

        if (! $errorLog || $errorLog->resolved_at !== null) {
            return;
        }

        app(OperationalIssueService::class)->createManualDeadLetterTask(
            $errorLog,
            sprintf('Retry sinkronisasi tracking gagal setelah %d percobaan.', $this->tries),
            self::class,
            $exception
        );
    }
}