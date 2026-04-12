<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationStatus extends Model
{
    protected $fillable = [
        'service_name', 'status', 'success_count', 'failure_count',
        'last_success_at', 'last_failure_at', 'last_error_message', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    public function recordSuccess(): void
    {
        $this->update([
            'success_count' => $this->success_count + 1,
            'last_success_at' => now(),
            'status' => 'healthy',
            'last_error_message' => null,
        ]);
    }

    public function recordFailure(string $errorMessage = null): void
    {
        $this->update([
            'failure_count' => $this->failure_count + 1,
            'last_failure_at' => now(),
            'last_error_message' => $errorMessage,
            'status' => $this->failure_count >= 3 ? 'down' : 'degraded',
        ]);
    }

    public function getHealthPercentage(): float
    {
        $total = $this->success_count + $this->failure_count;
        if ($total === 0) return 100.0;
        return round(($this->success_count / $total) * 100, 2);
    }
}
