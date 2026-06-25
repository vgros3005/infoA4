<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'label', 'description', 'color',
        'can_create_request', 'can_validate_request', 'can_change_status',
        'can_assign_task', 'can_export_pdf', 'can_admin', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'can_create_request'   => 'boolean',
            'can_validate_request' => 'boolean',
            'can_change_status'    => 'boolean',
            'can_assign_task'      => 'boolean',
            'can_export_pdf'       => 'boolean',
            'can_admin'            => 'boolean',
        ];
    }

    public function teamUserRoles(): HasMany
    {
        return $this->hasMany(TeamUserRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user_roles')
                    ->withPivot('team_id');
    }

    public function statusActions(): BelongsToMany
    {
        return $this->belongsToMany(StatusAction::class, 'status_action_roles');
    }
}
