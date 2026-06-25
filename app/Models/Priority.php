<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'label', 'description', 'color', 'icon',
        'requires_justification', 'level', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_justification' => 'boolean',
            'is_active'              => 'boolean',
        ];
    }

    public function requestsA4(): HasMany
    {
        return $this->hasMany(RequestA4::class);
    }
}
