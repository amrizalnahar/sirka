<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class UserTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $confirmingDelete = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'Anda tidak dapat menonaktifkan akun sendiri.');
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch('notify', type: 'success', message: 'Status user berhasil diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = $id;
    }

    public function delete(): void
    {
        if ($this->confirmingDelete) {
            $user = User::findOrFail($this->confirmingDelete);

            if ($user->id === auth()->id()) {
                $this->dispatch('notify', type: 'error', message: 'Anda tidak dapat menghapus akun sendiri.');
                $this->confirmingDelete = null;
                return;
            }

            $user->delete();
            $this->confirmingDelete = null;
            $this->dispatch('notify', type: 'success', message: 'User berhasil dihapus.');
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function render()
    {
        $users = User::with(['roles', 'departemen'])
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhereHas('departemen', fn ($dq) => $dq->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.user-table', [
            'users' => $users,
        ]);
    }
}
