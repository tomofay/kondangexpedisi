<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingPageContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section',
        'title',
        'subtitle',
        'content',
        'image_url',
        'cta_label',
        'cta_url',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
