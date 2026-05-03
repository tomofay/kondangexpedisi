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
        'origin_branch_id',
        'destination_branch_id',
        'service_type',
        'min_weight_kg',
        'max_weight_kg',
        'base_price',
        'per_kg_price',
        'estimated_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_weight_kg' => 'decimal:2',
            'max_weight_kg' => 'decimal:2',
            'base_price' => 'decimal:2',
            'per_kg_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }
}
