<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'error_type', 'module', 'message', 'stack_trace', 'context',
        'file_name', 'line_number', 'user_id', 'severity', 'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }
}
