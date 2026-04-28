<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentTrackingProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'tracking_id',
        'uploaded_by',
        'proof_type',
        'file_path',
        'file_mime',
        'file_size',
        'file_hash',
        'gps_lat',
        'gps_lng',
        'gps_accuracy_m',
        'captured_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'file_size' => 'integer',
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
            'gps_accuracy_m' => 'decimal:2',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(ShipmentTracking::class, 'tracking_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
