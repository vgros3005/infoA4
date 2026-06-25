<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label', 'description', 'color', 'icon', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
