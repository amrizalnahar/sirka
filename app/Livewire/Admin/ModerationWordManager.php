<?php

namespace App\Livewire\Admin;

use App\Helpers\ContentModerator;
use App\Models\ModerationWord;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ModerationWordManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // Form fields
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $word = '';
    public string $category = 'vulgar';
    public string $severity = 'medium';
    public bool $isRegex = false;
    public bool $isActive = true;

    public ?int $confirmingDelete = null;
    public bool $confirmingBulkDelete = false;
    public array $selectedIds = [];

    protected function rules(): array
    {
        return [
            'word' => [
                'required',
                'string',
                'max:255',
                Rule::unique('moderation_words', 'word')
                    ->ignore($this->editingId),
            ],
            'category' => ['required', 'in:vulgar,sara,hate_speech,spam'],
            'severity' => ['required', 'in:low,medium,high'],
            'isRegex' => ['boolean'],
            'isActive' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'word.required' => 'Kata wajib diisi.',
            'word.unique' => 'Kata sudah ada di daftar.',
            'category.required' => 'Kategori wajib dipilih.',
            'severity.required' => 'Tingkat keparahan wajib dipilih.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
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
        $this->showForm = true;
    }

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->word = '';
        $this->category = 'vulgar';
        $this->severity = 'medium';
        $this->isRegex = false;
        $this->isActive = true;
        $this->confirmingDelete = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        ModerationWord::updateOrCreate(
            ['id' => $this->editingId],
            [
                'word' => $this->word,
                'category' => $this->category,
                'severity' => $this->severity,
                'is_regex' => $this->isRegex,
                'is_active' => $this->isActive,
            ]
        );

        ContentModerator::clearCache();

        $this->closeModal();
        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Kata berhasil diperbarui.' : 'Kata berhasil ditambahkan.');
    }

    public function edit(int $id): void
    {
        $word = ModerationWord::findOrFail($id);
        $this->editingId = $word->id;
        $this->word = $word->word;
        $this->category = $word->category;
        $this->severity = $word->severity;
        $this->isRegex = $word->is_regex;
        $this->isActive = $word->is_active;
        $this->showForm = true;
    }

    public function toggleActive(int $id): void
    {
        $word = ModerationWord::findOrFail($id);
        $word->update(['is_active' => ! $word->is_active]);
        ContentModerator::clearCache();
        $this->dispatch('notify', type: 'success', message: 'Status kata diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = $id;
    }

    public function delete(): void
    {
        if ($this->confirmingDelete) {
            ModerationWord::findOrFail($this->confirmingDelete)->delete();
            $this->confirmingDelete = null;
            ContentModerator::clearCache();
            $this->dispatch('notify', type: 'success', message: 'Kata berhasil dihapus.');
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function updatedSelectedIds(): void
    {
        $this->confirmingBulkDelete = false;
    }

    public function toggleSelectAll($value): void
    {
        if ($value) {
            $this->selectedIds = ModerationWord::when($this->search, fn ($q) => $q->where('word', 'like', "%{$this->search}%"))
                ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
                ->orderBy($this->sortField, $this->sortDirection)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function confirmBulkDelete(): void
    {
        $this->confirmingBulkDelete = true;
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        ModerationWord::whereIn('id', $this->selectedIds)->delete();
        $this->selectedIds = [];
        $this->confirmingBulkDelete = false;
        ContentModerator::clearCache();
        $this->dispatch('notify', type: 'success', message: 'Kata terpilih berhasil dihapus.');
    }

    public function cancelBulkDelete(): void
    {
        $this->confirmingBulkDelete = false;
    }

    public function render()
    {
        $words = ModerationWord::when($this->search, fn ($q) => $q->where('word', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categoryLabels = [
            'vulgar' => 'Vulgar',
            'sara' => 'SARA',
            'hate_speech' => 'Ujaran Kebencian',
            'spam' => 'Spam',
        ];

        $severityLabels = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
        ];

        return view('livewire.admin.moderation-word-manager', [
            'words' => $words,
            'categoryLabels' => $categoryLabels,
            'severityLabels' => $severityLabels,
        ]);
    }
}
