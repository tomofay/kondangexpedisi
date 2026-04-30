<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_number',
        'customer_id',
        'branch_id',
        'destination_branch_id',
        'courier_id',
        'vehicle_id',
        'status_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'service_type',
        'total_weight_kg',
        'total_volume',
        'subtotal_amount',
        'insurance_amount',
        'admin_fee',
        'total_amount',
        'auto_subtotal_amount',
        'auto_insurance_amount',
        'auto_admin_fee',
        'auto_total_amount',
        'corrected_total_amount',
        'payment_status',
        'processing_status',
        'processing_error',
        'pricing_mode',
        'pricing_approval_status',
        'pricing_approved_by',
        'pricing_approved_at',
        'manual_override_by',
        'manual_override_reason',
        'manual_override_at',
        'current_status_at',
        'estimated_delivery_at',
        'delivered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_weight_kg' => 'decimal:2',
            'total_volume' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'insurance_amount' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'auto_subtotal_amount' => 'decimal:2',
            'auto_insurance_amount' => 'decimal:2',
            'auto_admin_fee' => 'decimal:2',
            'auto_total_amount' => 'decimal:2',
            'corrected_total_amount' => 'decimal:2',
            'pricing_approved_at' => 'datetime',
            'manual_override_at' => 'datetime',
            'current_status_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ShipmentStatus::class, 'status_id');
    }

    public function manualOverrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manual_override_by');
    }

    public function pricingApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pricing_approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(ShipmentTracking::class);
    }
}
