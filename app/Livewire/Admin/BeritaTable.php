<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class BeritaTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?int $categoryFilter = null;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public array $selected = [];
    public ?int $deleteId = null;
    public ?string $deleteTitle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
        $this->selected = [];
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

    #[Computed]
    public function posts()
    {
        return Post::with(['category', 'tags', 'author'])
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    private function getCurrentPageIds(): array
    {
        $page = $this->getPage() ?: 1;

        return Post::when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
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

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'error', message: 'Pilih minimal satu data.');
            return;
        }

        $count = count($this->selected);

        Post::whereIn('id', $this->selected)->each(function ($post) {
            if ($post->thumbnail) {
                Storage::disk('public')->delete($post->thumbnail);
            }
            $post->delete();
        });

        $this->selected = [];
        $this->dispatch('notify', type: 'success', message: $count . ' berita berhasil dihapus.');
    }

    public function bulkStatus(string $status): void
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', type: 'error', message: 'Pilih minimal satu data.');
            return;
        }

        Post::whereIn('id', $this->selected)->update([
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        $this->selected = [];
        $this->dispatch('notify', type: 'success', message: 'Status berita berhasil diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $post = Post::find($id);
        if ($post) {
            $this->deleteId = $id;
            $this->deleteTitle = $post->title;
        }
    }

    public function delete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $post = Post::findOrFail($this->deleteId);
        if ($post->thumbnail) {
            Storage::disk('public')->delete($post->thumbnail);
        }
        $post->delete();

        $this->deleteId = null;
        $this->deleteTitle = null;
        $this->dispatch('notify', type: 'success', message: 'Berita berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.berita-table', [
            'categories' => \App\Models\Category::byModule('post')->orderBy('name')->get(),
        ]);
    }
}
