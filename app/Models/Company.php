<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code', 'description', 'contact_email', 'contact_phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function requestsA4(): BelongsToMany
    {
        return $this->belongsToMany(RequestA4::class, 'request_a4_company');
    }
}
