<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    protected $fillable = [
        'request_a4_id', 'from_status_id', 'to_status_id',
        'user_id', 'action', 'comment',
    ];

    public function requestA4(): BelongsTo
    {
        return $this->belongsTo(RequestA4::class, 'request_a4_id');
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'to_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
