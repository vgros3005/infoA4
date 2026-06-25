<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_a4_id', 'task_type_id', 'assigned_to', 'created_by',
        'title', 'description', 'status', 'priority',
        'start_date', 'end_date', 'estimated_hours', 'actual_hours',
        'progress', 'is_recurring', 'weekly_hours', 'recurrence_end',
        'is_milestone', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'is_recurring' => 'boolean',
            'is_milestone' => 'boolean',
        ];
    }

    public function requestA4(): BelongsTo
    {
        return $this->belongsTo(RequestA4::class, 'request_a4_id');
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TaskTimeEntry::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withPivot('dependency_type');
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withPivot('dependency_type');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopePending($oQuery)
    {
        return $oQuery->where('status', 'pending');
    }

    public function scopeInProgress($oQuery)
    {
        return $oQuery->where('status', 'in_progress');
    }

    public function scopeForGantt($oQuery)
    {
        return $oQuery->whereNotNull('start_date')->whereNotNull('end_date');
    }
}
