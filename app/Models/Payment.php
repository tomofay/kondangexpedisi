<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'customer_id',
        'processed_by',
        'method',
        'status',
        'processing_status',
        'processing_error',
        'amount',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'snap_token',
        'snap_redirect_url',
        'payment_type',
        'bank_name',
        'va_number',
        'fraud_status',
        'signature_key',
        'transaction_time',
        'paid_at',
        'gateway_payload',
        'manual_override_by',
        'manual_override_reason',
        'manual_override_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'manual_override_at' => 'datetime',
            'transaction_time' => 'datetime',
            'paid_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function manualOverrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manual_override_by');
    }
}
