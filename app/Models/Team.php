<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function teamUserRoles(): HasMany
    {
        return $this->hasMany(TeamUserRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user_roles')
                    ->withPivot('role_id', 'joined_at')
                    ->withTimestamps();
    }

    public function requestsA4(): HasMany
    {
        return $this->hasMany(RequestA4::class, 'assigned_team_id');
    }
}
