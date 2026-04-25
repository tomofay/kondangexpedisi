<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function record(
        string $action,
        Model $subject,
        ?User $actor = null,
        array $before = [],
        array $after = [],
        ?string $notes = null,
        array $metadata = []
    ): AuditLog
    {
        $source = $metadata['source'] ?? ($actor ? 'user_action' : 'system_automatic');
        $isManualCorrection = (bool) ($metadata['is_manual_correction'] ?? false);
        $correctionReference = $metadata['correction_reference'] ?? null;

        return AuditLog::query()->create([
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'actor_id' => $actor?->id,
            'before_state' => $before,
            'after_state' => $after,
            'notes' => $notes,
            'source' => $source,
            'is_manual_correction' => $isManualCorrection,
            'correction_reference' => $correctionReference,
            'changed_fields' => $this->buildChangedFields($before, $after),
        ]);
    }

    private function buildChangedFields(array $before, array $after): array
    {
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $changes = [];

        foreach ($keys as $key) {
            $beforeValue = $before[$key] ?? null;
            $afterValue = $after[$key] ?? null;

            if (! $this->valuesAreEqual($beforeValue, $afterValue)) {
                $changes[$key] = [
                    'from' => $beforeValue,
                    'to' => $afterValue,
                ];
            }
        }

        return $changes;
    }

    private function valuesAreEqual(mixed $beforeValue, mixed $afterValue): bool
    {
        return json_encode($beforeValue) === json_encode($afterValue);
    }
}
