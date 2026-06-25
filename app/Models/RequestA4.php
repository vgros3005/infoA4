<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestA4 extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'requests_a4';

    protected $fillable = [
        'reference', 'title', 'description', 'content',
        'request_type_id', 'priority_id', 'priority_justification',
        'status_id', 'requester_id', 'assigned_team_id',
        'requested_date', 'desired_date', 'planned_date', 'completed_date',
        'is_frozen', 'pdf_version', 'estimated_hours', 'actual_hours', 'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'desired_date'   => 'date',
            'planned_date'   => 'date',
            'completed_date' => 'date',
            'is_frozen'      => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $oRequest) {
            if (empty($oRequest->reference)) {
                $oRequest->reference = static::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $sYear  = date('Y');
        $iCount = static::whereYear('created_at', $sYear)->count() + 1;
        return sprintf('A4-%s-%04d', $sYear, $iCount);
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'request_a4_company');
    }

    public function softwares(): BelongsToMany
    {
        return $this->belongsToMany(Software::class, 'request_a4_software');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class)->orderByDesc('created_at');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('sort_order');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function pdfVersions(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')
                    ->where('is_pdf_version', true)
                    ->orderByDesc('pdf_version_number');
    }

    public function getTotalActualHoursAttribute(): float
    {
        return (float) $this->tasks()->sum('actual_hours');
    }

    public function getAvailableActionsForUser(User $oUser): \Illuminate\Support\Collection
    {
        return $this->status->actions()
                    ->where('is_active', true)
                    ->with('roles')
                    ->get()
                    ->filter(fn(StatusAction $oAction) =>
                        $oAction->roles->isEmpty() ||
                        $oAction->roles->contains(fn(Role $oRole) =>
                            $oUser->hasRole($oRole->name)
                        )
                    );
    }
}
