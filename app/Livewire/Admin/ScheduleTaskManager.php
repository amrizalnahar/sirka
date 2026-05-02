<?php

namespace App\Livewire\Admin;

use App\Models\ScheduleTask;
use App\Services\ScheduleTaskService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ScheduleTaskManager extends Component
{
    public string $search = '';
    public string $statusFilter = '';
    public ?int $selectedTaskId = null;
    public string $scheduledFor = '';
    public bool $showExecuteModal = false;
    public bool $showHistoryModal = false;
    public array $executionHistory = [];

    public function mount(): void
    {
        $service = new ScheduleTaskService();
        $service->getAvailableTasks();
    }

    public function refreshTasks(): void
    {
        $service = new ScheduleTaskService();
        $service->getAvailableTasks();
        $this->dispatch('notify', type: 'success', message: 'Daftar task berhasil diperbarui.');
    }

    public function openExecuteModal(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->scheduledFor = now()->format('Y-m-d\TH:i');
        $this->showExecuteModal = true;
    }

    public function openHistoryModal(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $service = new ScheduleTaskService();
        $this->executionHistory = $service->getExecutionHistory($taskId)->toArray();
        $this->showHistoryModal = true;
    }

    public function toggleActive(int $taskId): void
    {
        $task = ScheduleTask::findOrFail($taskId);
        $task->update(['is_active' => ! $task->is_active]);

        $message = $task->is_active
            ? 'Task berhasil diaktifkan.'
            : 'Task berhasil dinonaktifkan.';

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function executeTask(): void
    {
        $this->validate([
            'selectedTaskId' => 'required|exists:schedule_tasks,id',
            'scheduledFor' => 'required|date',
        ]);

        try {
            $scheduledFor = new \DateTime($this->scheduledFor);
            $service = new ScheduleTaskService();
            $result = $service->executeTask($this->selectedTaskId, $scheduledFor);

            $this->showExecuteModal = false;
            $this->selectedTaskId = null;
            $this->scheduledFor = '';

            if ($result['success']) {
                $this->dispatch('notify', type: 'success', message: 'Task berhasil dieksekusi. Exit code: '.$result['exit_code']);
            } else {
                $this->dispatch('notify', type: 'error', message: 'Task gagal: '.$result['error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showExecuteModal = false;
        $this->showHistoryModal = false;
        $this->selectedTaskId = null;
        $this->resetValidation();
    }

    public function getTasksProperty()
    {
        $query = ScheduleTask::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('command', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        return $query->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.admin.schedule-task-manager', [
            'tasks' => $this->tasks,
        ]);
    }
}
