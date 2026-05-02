<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleTask extends Model
{
    protected $fillable = [
        'name',
        'command',
        'expression',
        'description',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function executions(): HasMany
    {
        return $this->hasMany(ScheduleTaskExecution::class);
    }
}
