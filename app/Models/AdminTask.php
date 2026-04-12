<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminTask extends Model
{
    protected $fillable = [
        'task_type', 'title', 'description', 'assigned_to', 'created_by',
        'status', 'priority', 'action_data', 'result', 'started_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'action_data' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete(array $result = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'result' => $result,
        ]);
    }
}
