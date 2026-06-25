<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamUserRole extends Model
{
    protected $table = 'team_user_roles';

    protected $fillable = ['team_id', 'user_id', 'role_id', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
