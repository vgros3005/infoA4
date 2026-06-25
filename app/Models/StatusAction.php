<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StatusAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id', 'target_status_id', 'action_label', 'action_name',
        'button_color', 'icon', 'requires_comment', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_comment' => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    public function getTranslatedLabelAttribute(): string
    {
        $sKey = 'actions.' . $this->action_name;
        $sTranslated = __($sKey);
        return ($sTranslated !== $sKey) ? $sTranslated : ($this->action_label ?? $this->action_name);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function targetStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'target_status_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'status_action_roles');
    }
}
