<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password',
        'phone', 'avatar', 'is_active', 'locale',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: $this->name;
    }

    public function teamUserRoles(): HasMany
    {
        return $this->hasMany(TeamUserRole::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user_roles')
                    ->withPivot('role_id', 'joined_at')
                    ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'team_user_roles')
                    ->withPivot('team_id');
    }

    public function requestsA4(): HasMany
    {
        return $this->hasMany(RequestA4::class, 'requester_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TaskTimeEntry::class);
    }

    public function hasRole(string $sRoleName, ?int $iTeamId = null): bool
    {
        $oQuery = $this->teamUserRoles()->whereHas('role', fn($q) => $q->where('name', $sRoleName));
        if ($iTeamId) {
            $oQuery->where('team_id', $iTeamId);
        }
        return $oQuery->exists();
    }

    public function hasAnyRole(array $aRoleNames): bool
    {
        return $this->teamUserRoles()
                    ->whereHas('role', fn($q) => $q->whereIn('name', $aRoleNames))
                    ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
