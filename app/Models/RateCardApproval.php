<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateCardApproval extends Model
{
    protected $fillable = [
        'rate_card_id', 'requested_by', 'approved_by', 'status',
        'changes', 'reason', 'notes', 'approved_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'approved_at' => 'datetime',
    ];

    public function rateCard(): BelongsTo
    {
        return $this->belongsTo(RateCard::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
