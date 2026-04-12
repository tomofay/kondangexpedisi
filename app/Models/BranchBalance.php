<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BranchBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'recorded_by',
        'balance_date',
        'opening_balance',
        'cash_in',
        'cash_out',
        'closing_balance',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance_date' => 'date',
            'opening_balance' => 'decimal:2',
            'cash_in' => 'decimal:2',
            'cash_out' => 'decimal:2',
            'closing_balance' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
