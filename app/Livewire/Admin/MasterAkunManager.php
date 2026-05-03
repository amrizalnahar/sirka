<?php

namespace App\Livewire\Admin;

use App\Models\MasterAkun;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class MasterAkunManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $editingId = null;
    public string $kode = '';
    public string $nama = '';
    public bool $isActive = true;

    public ?int $confirmingDelete = null;
    public array $selected = [];

    protected function rules(): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('master_akuns', 'kode')
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('master_akuns', 'nama')
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
            'isActive' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'kode.required' => 'Kode akun wajib diisi.',
            'kode.unique' => 'Kode akun sudah digunakan.',
            'nama.required' => 'Nama akun wajib diisi.',
            'nama.unique' => 'Nama akun sudah digunakan.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
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

    public function openModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'master-akun-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'master-akun-modal');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->kode = '';
        $this->nama = '';
        $this->isActive = true;
        $this->confirmingDelete = null;
        $this->selected = [];
        $this->resetValidation();
    }

    private function getCurrentPageIds(): array
    {
        $page = $this->getPage() ?: 1;

        return MasterAkun::when($this->search, fn ($q) => $q->search($this->search))
            ->orderBy($this->sortField, $this->sortDirection)
            ->forPage($page, $this->perPage)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function toggleSelectPage(): void
    {
        $pageIds = $this->getCurrentPageIds();

        if (empty($pageIds)) {
            return;
        }

        $allSelected = count(array_diff($pageIds, $this->selected)) === 0;

        if ($allSelected) {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        }
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'warning', message: 'Pilih minimal satu akun untuk dihapus.');
            return;
        }
        $this->dispatch('open-modal', 'bulk-delete-modal');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('close-modal', 'bulk-delete-modal');
            return;
        }

        MasterAkun::whereIn('id', $this->selected)->delete();

        $count = count($this->selected);
        $this->selected = [];
        $this->dispatch('close-modal', 'bulk-delete-modal');
        $this->dispatch('notify', type: 'success', message: $count . ' akun berhasil dihapus.');
    }

    public function cancelBulkDelete(): void
    {
        $this->dispatch('close-modal', 'bulk-delete-modal');
    }

    public function save(): void
    {
        $this->validate();

        MasterAkun::updateOrCreate(
            ['id' => $this->editingId],
            [
                'kode' => $this->kode,
                'nama' => $this->nama,
                'is_active' => $this->isActive,
            ]
        );

        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Akun berhasil diperbarui.' : 'Akun berhasil ditambahkan.');
    }

    public function edit(int $id): void
    {
        $item = MasterAkun::findOrFail($id);
        $this->editingId = $item->id;
        $this->kode = $item->kode;
        $this->nama = $item->nama;
        $this->isActive = $item->is_active;
        $this->dispatch('open-modal', 'master-akun-modal');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = $id;
    }

    public function delete(): void
    {
        if ($this->confirmingDelete) {
            MasterAkun::findOrFail($this->confirmingDelete)->delete();
            $this->confirmingDelete = null;
            $this->dispatch('notify', type: 'success', message: 'Akun berhasil dihapus.');
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function render()
    {
        $items = MasterAkun::when($this->search, fn ($q) => $q->search($this->search))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.master-akun-manager', [
            'items' => $items,
        ]);
    }
}
