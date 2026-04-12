<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function record(string $action, Model $subject, ?User $actor = null, array $before = [], array $after = [], ?string $notes = null): AuditLog
    {
        return AuditLog::query()->create([
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'actor_id' => $actor?->id,
            'before_state' => $before,
            'after_state' => $after,
            'notes' => $notes,
        ]);
    }
}
