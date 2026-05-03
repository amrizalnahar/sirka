<?php

namespace App\Livewire\Admin;

use App\Models\ApprovalChain;
use App\Models\Departement;
use App\Models\JenisLaporan;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ApprovalChainManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $editingId = null;
    public ?int $jenisLaporanId = null;
    public ?int $departemenId = null;
    public ?int $approverLevel1Id = null;
    public ?int $approverLevel2Id = null;

    public ?int $confirmingDelete = null;
    public array $selected = [];

    protected function rules(): array
    {
        return [
            'jenisLaporanId' => ['required', 'exists:jenis_laporans,id'],
            'departemenId' => ['required', 'exists:departements,id'],
            'approverLevel1Id' => ['required', 'exists:users,id'],
            'approverLevel2Id' => ['required', 'exists:users,id', 'different:approverLevel1Id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'jenisLaporanId.required' => 'Jenis laporan wajib dipilih.',
            'departemenId.required' => 'Departemen wajib dipilih.',
            'approverLevel1Id.required' => 'Approver Level 1 wajib dipilih.',
            'approverLevel2Id.required' => 'Approver Level 2 wajib dipilih.',
            'approverLevel2Id.different' => 'Approver Level 2 harus berbeda dengan Level 1.',
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
        $this->dispatch('open-modal', 'approval-chain-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'approval-chain-modal');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->jenisLaporanId = null;
        $this->departemenId = null;
        $this->approverLevel1Id = null;
        $this->approverLevel2Id = null;
        $this->confirmingDelete = null;
        $this->selected = [];
        $this->resetValidation();
    }

    private function getCurrentPageIds(): array
    {
        $page = $this->getPage() ?: 1;

        return ApprovalChain::with(['jenisLaporan', 'departemen', 'approverLevel1', 'approverLevel2'])
            ->when($this->search, fn ($q) => $q->whereHas('jenisLaporan', fn ($sq) => $sq->search($this->search))
                ->orWhereHas('departemen', fn ($sq) => $sq->search($this->search)))
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
            $this->dispatch('notify', type: 'warning', message: 'Pilih minimal satu approval chain untuk dihapus.');
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

        ApprovalChain::whereIn('id', $this->selected)->delete();

        $count = count($this->selected);
        $this->selected = [];
        $this->dispatch('close-modal', 'bulk-delete-modal');
        $this->dispatch('notify', type: 'success', message: $count . ' approval chain berhasil dihapus.');
    }

    public function cancelBulkDelete(): void
    {
        $this->dispatch('close-modal', 'bulk-delete-modal');
    }

    public function save(): void
    {
        $this->validate();

        // Check unique combination
        $exists = ApprovalChain::where('jenis_laporan_id', $this->jenisLaporanId)
            ->where('departemen_id', $this->departemenId)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($exists) {
            $this->dispatch('notify', type: 'error', message: 'Kombinasi jenis laporan dan departemen sudah ada.');
            return;
        }

        ApprovalChain::updateOrCreate(
            ['id' => $this->editingId],
            [
                'jenis_laporan_id' => $this->jenisLaporanId,
                'departemen_id' => $this->departemenId,
                'approver_level_1_id' => $this->approverLevel1Id,
                'approver_level_2_id' => $this->approverLevel2Id,
            ]
        );

        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Approval chain berhasil diperbarui.' : 'Approval chain berhasil ditambahkan.');
    }

    public function edit(int $id): void
    {
        $item = ApprovalChain::findOrFail($id);
        $this->editingId = $item->id;
        $this->jenisLaporanId = $item->jenis_laporan_id;
        $this->departemenId = $item->departemen_id;
        $this->approverLevel1Id = $item->approver_level_1_id;
        $this->approverLevel2Id = $item->approver_level_2_id;
        $this->dispatch('open-modal', 'approval-chain-modal');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = $id;
    }

    public function delete(): void
    {
        if ($this->confirmingDelete) {
            ApprovalChain::findOrFail($this->confirmingDelete)->delete();
            $this->confirmingDelete = null;
            $this->dispatch('notify', type: 'success', message: 'Approval chain berhasil dihapus.');
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function render()
    {
        $items = ApprovalChain::with(['jenisLaporan', 'departemen', 'approverLevel1', 'approverLevel2'])
            ->when($this->search, function ($q) {
                $q->whereHas('jenisLaporan', fn ($sq) => $sq->search($this->search))
                  ->orWhereHas('departemen', fn ($sq) => $sq->search($this->search));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.approval-chain-manager', [
            'items' => $items,
            'jenisLaporans' => JenisLaporan::active()->orderBy('nama')->get(),
            'departements' => Departement::active()->orderBy('name')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
