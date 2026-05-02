<?php

namespace App\Services;

use App\Models\ScheduleTask;
use App\Models\ScheduleTaskExecution;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\BufferedOutput;

class ScheduleTaskService
{
    /**
     * Get all available schedule tasks from bootstrap/app.php
     */
    public function getAvailableTasks(): array
    {
        $tasks = [
            [
                'name' => 'Livewire Cleanup Temporary Files',
                'command' => 'livewire:cleanup:temporary-files',
                'expression' => '0 * * * *',
                'description' => 'Membersihkan file temporary Livewire setiap jam',
            ],
            [
                'name' => 'Queue Prune Batches',
                'command' => 'queue:prune-batches',
                'expression' => '0 0 * * *',
                'description' => 'Membersihkan job batches yang sudah selesai',
            ],
            [
                'name' => 'Queue Prune Failed',
                'command' => 'queue:prune-failed',
                'expression' => '0 0 * * *',
                'description' => 'Membersihkan failed jobs yang sudah lama',
            ],
            [
                'name' => 'Cache Clear',
                'command' => 'cache:clear',
                'expression' => '0 0 * * 0',
                'description' => 'Membersihkan application cache setiap minggu',
            ],
            [
                'name' => 'Route Cache',
                'command' => 'route:cache',
                'expression' => '0 0 * * 0',
                'description' => 'Membuat ulang route cache setiap minggu',
            ],
            [
                'name' => 'Config Cache',
                'command' => 'config:cache',
                'expression' => '0 0 * * 0',
                'description' => 'Membuat ulang config cache setiap minggu',
            ],
            [
                'name' => 'View Cache',
                'command' => 'view:cache',
                'expression' => '0 0 * * 0',
                'description' => 'Membuat ulang view cache setiap minggu',
            ],
        ];

        foreach ($tasks as $taskData) {
            ScheduleTask::updateOrCreate(
                ['command' => $taskData['command']],
                $taskData
            );
        }

        return ScheduleTask::orderBy('name')->get()->toArray();
    }

    /**
     * Execute a schedule task manually
     */
    public function executeTask(int $taskId, \DateTime $scheduledFor): array
    {
        $task = ScheduleTask::findOrFail($taskId);

        if (! $task->is_active) {
            throw new \Exception('Task tidak aktif.');
        }

        $execution = ScheduleTaskExecution::create([
            'schedule_task_id' => $taskId,
            'executed_at' => now(),
            'scheduled_for' => $scheduledFor,
            'status' => 'running',
        ]);

        try {
            $output = new BufferedOutput();
            $exitCode = Artisan::call($task->command, [], $output);
            $outputString = $output->fetch();

            $execution->update([
                'status' => $exitCode === 0 ? 'completed' : 'failed',
                'output' => $outputString,
                'exit_code' => $exitCode,
            ]);

            $task->update(['last_run_at' => now()]);

            Log::info("Schedule Task Executed: {$task->name}", [
                'command' => $task->command,
                'scheduled_for' => $scheduledFor->format('Y-m-d H:i:s'),
                'exit_code' => $exitCode,
                'execution_id' => $execution->id,
            ]);

            return [
                'success' => true,
                'execution' => $execution,
                'output' => $outputString,
                'exit_code' => $exitCode,
            ];
        } catch (\Exception $e) {
            $execution->update([
                'status' => 'failed',
                'output' => $e->getMessage(),
                'exit_code' => -1,
            ]);

            Log::error("Schedule Task Failed: {$task->name}", [
                'command' => $task->command,
                'error' => $e->getMessage(),
                'execution_id' => $execution->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution' => $execution,
            ];
        }
    }

    /**
     * Get execution history for a task
     */
    public function getExecutionHistory(int $taskId, int $limit = 50)
    {
        return ScheduleTaskExecution::where('schedule_task_id', $taskId)
            ->orderBy('executed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
