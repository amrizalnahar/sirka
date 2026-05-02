<?php

namespace App\Livewire\Admin;

use App\Models\AuditTrail;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class AuditLogTable extends Component
{
    use WithPagination;

    public string $eventFilter = '';
    public string $modelFilter = '';
    public ?int $userFilter = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public int $perPage = 25;

    public ?int $expandedRow = null;

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function updatingModelFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function toggleRow(int $id): void
    {
        $this->expandedRow = $this->expandedRow === $id ? null : $id;
    }

    public function render()
    {
        $logs = AuditTrail::with('user')
            ->when($this->eventFilter, fn ($q) => $q->where('event', $this->eventFilter))
            ->when($this->modelFilter, fn ($q) => $q->where('auditable_type', 'like', '%' . $this->modelFilter . '%'))
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $events = AuditTrail::select('event')->distinct()->pluck('event');
        $models = AuditTrail::select('auditable_type')->distinct()->pluck('auditable_type');
        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('livewire.admin.audit-log-table', [
            'logs' => $logs,
            'events' => $events,
            'models' => $models,
            'users' => $users,
        ]);
    }
}
