<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'user_id', 'entry_date', 'hours', 'comment', 'is_billable',
    ];

    protected function casts(): array
    {
        return [
            'entry_date'  => 'date',
            'is_billable' => 'boolean',
            'hours'       => 'decimal:2',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $oEntry) {
            $oEntry->syncTaskActualHours();
        });

        static::deleted(function (self $oEntry) {
            $oEntry->syncTaskActualHours();
        });
    }

    private function syncTaskActualHours(): void
    {
        $oTask = $this->task()->first();
        if ($oTask) {
            $oTask->actual_hours = $oTask->timeEntries()->sum('hours');
            $oTask->saveQuietly();
        }
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
