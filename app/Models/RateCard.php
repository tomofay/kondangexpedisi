<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'origin_zone_id',
        'destination_zone_id',
        'service_type',
        'min_weight_kg',
        'max_weight_kg',
        'base_price',
        'per_kg_price',
        'insurance_fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_weight_kg' => 'decimal:2',
            'max_weight_kg' => 'decimal:2',
            'base_price' => 'decimal:2',
            'per_kg_price' => 'decimal:2',
            'insurance_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        // Legacy alias for destination zone.
        return $this->destinationZone();
    }

    public function originZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'origin_zone_id');
    }

    public function destinationZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'destination_zone_id');
    }
}
