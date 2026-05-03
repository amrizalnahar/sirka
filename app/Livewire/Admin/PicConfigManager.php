<?php

namespace App\Livewire\Admin;

use App\Models\Departement;
use App\Models\JenisLaporan;
use App\Models\PicConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PicConfigManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    // Modal form
    public ?int $editingId = null;
    public ?int $departemen_id = null;
    public ?int $jenis_laporan_id = null;
    public array $pics = [];

    public $confirmingDelete = null;

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
        $this->pics[] = ['user_id' => '', 'email' => ''];
        $this->dispatch('open-modal', 'pic-config-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'pic-config-modal');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->departemen_id = null;
        $this->jenis_laporan_id = null;
        $this->pics = [];
        $this->confirmingDelete = null;
        $this->resetValidation();
    }

    public function addPicItem(): void
    {
        $this->pics[] = ['user_id' => '', 'email' => ''];
    }

    public function removePicItem(int $index): void
    {
        unset($this->pics[$index]);
        $this->pics = array_values($this->pics);
    }

    public function updated($property, $value): void
    {
        if (str_starts_with($property, 'pics.') && str_ends_with($property, '.user_id')) {
            $index = explode('.', $property)[1];
            $user = User::find($this->pics[$index]['user_id'] ?? null);
            $this->pics[$index]['email'] = $user?->email ?? '';
        }
    }

    public function getSelectedUserIdsProperty(): array
    {
        return array_filter(array_column($this->pics, 'user_id'));
    }

    protected function rules(): array
    {
        return [
            'departemen_id' => ['required', 'exists:departements,id'],
            'jenis_laporan_id' => ['required', 'exists:jenis_laporans,id'],
            'pics' => ['required', 'array', 'min:1'],
            'pics.*.user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $userIds = array_column($this->pics, 'user_id');
                    if (count($userIds) !== count(array_unique($userIds))) {
                        $fail('User tidak boleh dipilih lebih dari satu kali.');
                    }
                },
            ],
            'pics.*.email' => ['required', 'email'],
        ];
    }

    protected function messages(): array
    {
        return [
            'departemen_id.required' => 'Departemen wajib dipilih.',
            'jenis_laporan_id.required' => 'Jenis laporan wajib dipilih.',
            'pics.required' => 'Minimal harus ada 1 PIC.',
            'pics.min' => 'Minimal harus ada 1 PIC.',
            'pics.*.user_id.required' => 'User wajib dipilih.',
            'pics.*.email.required' => 'Email wajib diisi.',
            'pics.*.email.email' => 'Format email tidak valid.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            PicConfig::where('departemen_id', $this->departemen_id)
                ->where('jenis_laporan_id', $this->jenis_laporan_id)
                ->delete();

            foreach ($this->pics as $pic) {
                PicConfig::create([
                    'departemen_id' => $this->departemen_id,
                    'jenis_laporan_id' => $this->jenis_laporan_id,
                    'user_id' => $pic['user_id'],
                    'email' => $pic['email'],
                ]);
            }
        });

        $this->dispatch('notify', type: 'success', message: 'Konfigurasi PIC berhasil disimpan.');
        $this->closeModal();
    }

    public function edit(int $departemen_id, int $jenis_laporan_id): void
    {
        $this->resetForm();
        $this->departemen_id = $departemen_id;
        $this->jenis_laporan_id = $jenis_laporan_id;
        $this->editingId = $departemen_id;

        $configs = PicConfig::with('user')
            ->where('departemen_id', $departemen_id)
            ->where('jenis_laporan_id', $jenis_laporan_id)
            ->get();

        foreach ($configs as $config) {
            $this->pics[] = [
                'user_id' => $config->user_id,
                'email' => $config->email,
            ];
        }

        if (empty($this->pics)) {
            $this->pics[] = ['user_id' => '', 'email' => ''];
        }

        $this->dispatch('open-modal', 'pic-config-modal');
    }

    public function confirmDelete(int $departemen_id, int $jenis_laporan_id): void
    {
        $this->confirmingDelete = ['departemen_id' => $departemen_id, 'jenis_laporan_id' => $jenis_laporan_id];
    }

    public function delete(): void
    {
        if ($this->confirmingDelete) {
            PicConfig::where('departemen_id', $this->confirmingDelete['departemen_id'])
                ->where('jenis_laporan_id', $this->confirmingDelete['jenis_laporan_id'])
                ->delete();
            $this->confirmingDelete = null;
            $this->dispatch('notify', type: 'success', message: 'Konfigurasi PIC berhasil dihapus.');
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function updatedDepartemenId(): void
    {
        // Reset PIC items ketika departemen berubah di mode create
        if (! $this->editingId) {
            $this->pics = [['user_id' => '', 'email' => '']];
        }
    }

    public function render()
    {
        // Get all PIC configs with relations
        $configs = PicConfig::with(['departemen', 'jenisLaporan', 'user'])
            ->when($this->search, function ($q) {
                $q->whereHas('departemen', fn ($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('jenisLaporan', fn ($sq) => $sq->where('nama', 'like', "%{$this->search}%"));
            })
            ->orderBy('departemen_id')
            ->orderBy('jenis_laporan_id')
            ->get();

        // Group by (departemen_id, jenis_laporan_id)
        $grouped = $configs->groupBy(fn ($c) => $c->departemen_id . '-' . ($c->jenis_laporan_id ?? '0'));

        // Manual pagination for grouped collection
        $page = $this->getPage() ?: 1;
        $perPage = $this->perPage;
        $items = $grouped->forPage($page, $perPage);

        $groups = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $grouped->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $departemen = $this->departemen_id ? Departement::find($this->departemen_id) : null;

        return view('livewire.admin.pic-config-manager', [
            'groups' => $groups,
            'departemen' => $departemen,
            'availableDepartements' => Departement::active()->orderBy('name')->get(),
            'jenisLaporans' => JenisLaporan::active()->orderBy('nama')->get(),
            'users' => User::where('departemen_id', $this->departemen_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'selectedUserIds' => $this->getSelectedUserIdsProperty(),
        ]);
    }
}
