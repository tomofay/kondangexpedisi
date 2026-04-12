<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CourierEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'shipment_id',
        'earning_date',
        'base_fee',
        'bonus',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'earning_date' => 'date',
            'base_fee' => 'decimal:2',
            'bonus' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
