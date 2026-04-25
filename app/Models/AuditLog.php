<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'subject_type',
        'subject_id',
        'actor_id',
        'before_state',
        'after_state',
        'notes',
        'source',
        'is_manual_correction',
        'correction_reference',
        'changed_fields',
    ];

    protected function casts(): array
    {
        return [
            'before_state' => 'array',
            'after_state' => 'array',
            'changed_fields' => 'array',
            'is_manual_correction' => 'boolean',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
