<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'label', 'description', 'color', 'icon',
        'is_initial', 'is_final', 'freezes_request', 'generates_pdf', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_initial'      => 'boolean',
            'is_final'        => 'boolean',
            'freezes_request' => 'boolean',
            'generates_pdf'   => 'boolean',
        ];
    }

    /**
     * Returns the status label translated into the current application locale.
     * Falls back to the stored label if no translation key is found.
     */
    public function getTranslatedLabelAttribute(): string
    {
        $sKey = 'statuses.' . $this->name;
        $sTranslated = __($sKey);
        return ($sTranslated !== $sKey) ? $sTranslated : ($this->label ?? $this->name);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(StatusAction::class)->orderBy('sort_order');
    }

    public function requestsA4(): HasMany
    {
        return $this->hasMany(RequestA4::class);
    }

    public function statusHistoriesFrom(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'from_status_id');
    }

    public function statusHistoriesTo(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'to_status_id');
    }
}
