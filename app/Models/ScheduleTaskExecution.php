<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTaskExecution extends Model
{
    protected $fillable = [
        'schedule_task_id',
        'executed_at',
        'scheduled_for',
        'status',
        'output',
        'exit_code',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    public function scheduleTask(): BelongsTo
    {
        return $this->belongsTo(ScheduleTask::class);
    }
}
