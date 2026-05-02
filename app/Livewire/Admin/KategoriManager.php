<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class KategoriManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $moduleFilter = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $module_type = 'post';
    public string $description = '';

    public ?int $confirmingDelete = null;
    public array $selected = [];
    public ?string $deleteError = null;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('module_type', $this->module_type)
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->where('module_type', $this->module_type)
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
            'module_type' => ['required', 'in:post'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.unique' => 'Slug sudah digunakan.',
            'module_type.required' => 'Tipe modul wajib dipilih.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingModuleFilter(): void
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
        $this->dispatch('open-modal', 'category-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'category-modal');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->module_type = 'post';
        $this->description = '';
        $this->confirmingDelete = null;
        $this->deleteError = null;
        $this->resetValidation();
    }

    private function getCurrentPageIds(): array
    {
        $page = $this->getPage() ?: 1;

        return Category::withCount(['posts'])
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->moduleFilter, fn ($q) => $q->where('module_type', $this->moduleFilter))
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

    private function hasRelations(Category $category): bool
    {
        return $category->posts()->exists();
    }

    public function generateSlug(): void
    {
        if (! empty($this->name)) {
            $base = Str::slug($this->name);
            $slug = $base;
            $count = 1;

            while (
                Category::where('slug', $slug)
                    ->where('module_type', $this->module_type)
                    ->where('id', '!=', $this->editingId ?? 0)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $count++;
            }

            $this->slug = $slug;
        }
    }

    public function updatedName(): void
    {
        if (empty($this->slug) || $this->editingId === null) {
            $this->generateSlug();
        }
    }

    public function save(): void
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'module_type' => $this->module_type,
                'description' => $this->description ?: null,
            ]
        );

        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->module_type = $category->module_type;
        $this->description = $category->description ?? '';
        $this->dispatch('open-modal', 'category-modal');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = $id;
        $this->deleteError = null;
    }

    public function delete(): void
    {
        if (! $this->confirmingDelete) {
            return;
        }

        $category = Category::findOrFail($this->confirmingDelete);

        if ($this->hasRelations($category)) {
            $this->deleteError = 'Kategori "' . $category->name . '" tidak bisa dihapus karena masih digunakan oleh Berita.';
            return;
        }

        $category->delete();
        $this->confirmingDelete = null;
        $this->deleteError = null;
        $this->dispatch('notify', type: 'success', message: 'Kategori berhasil dihapus.');
    }

    public function confirmBulkDelete(): void
    {
        $this->deleteError = null;
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'warning', message: 'Pilih minimal satu kategori untuk dihapus.');
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

        $categories = Category::whereIn('id', $this->selected)->get();
        $deleted = 0;
        $blocked = [];

        foreach ($categories as $category) {
            if ($this->hasRelations($category)) {
                $blocked[] = $category->name;
                continue;
            }
            $category->delete();
            $deleted++;
        }

        $this->selected = [];
        $this->dispatch('close-modal', 'bulk-delete-modal');

        if ($deleted > 0 && empty($blocked)) {
            $this->dispatch('notify', type: 'success', message: $deleted . ' kategori berhasil dihapus.');
        } elseif ($deleted > 0 && ! empty($blocked)) {
            $this->dispatch('notify', type: 'warning', message: $deleted . ' kategori dihapus. ' . count($blocked) . ' kategori lain tidak bisa dihapus karena masih memiliki relasi konten.');
        } else {
            $this->dispatch('notify', type: 'error', message: 'Semua kategori terpilih tidak bisa dihapus karena masih memiliki relasi konten.');
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
        $this->deleteError = null;
    }

    public function cancelBulkDelete(): void
    {
        $this->dispatch('close-modal', 'bulk-delete-modal');
    }

    public function render()
    {
        $categories = Category::withCount(['posts'])
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->moduleFilter, fn ($q) => $q->where('module_type', $this->moduleFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Add computed total content count
        $categories->getCollection()->transform(function ($category) {
            $category->total_content = $category->posts_count;
            return $category;
        });

        return view('livewire.admin.kategori-manager', [
            'categories' => $categories,
            'moduleTypes' => ['post' => 'Berita'],
        ]);
    }
}
