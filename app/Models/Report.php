<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'report_type', 'title', 'frequency', 'recipients', 'filters',
        'format', 'file_path', 'status', 'generated_by', 'error_message',
        'record_count', 'generated_at', 'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'filters' => 'array',
        'generated_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(string $filePath, int $recordCount = 0): void
    {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'record_count' => $recordCount,
            'generated_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
