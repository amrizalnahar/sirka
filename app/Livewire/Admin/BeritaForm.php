<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class BeritaForm extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';
    public string $slug = '';
    public ?int $category_id = null;
    public array $selectedTags = [];
    public $thumbnail = null;
    public ?string $existingThumbnail = null;
    public string $content = '';
    public ?string $meta_title = '';
    public ?string $meta_description = '';
    public ?string $meta_keywords = '';
    public string $status = 'draft';
    public string $newTagName = '';

    public function mount(?Post $post = null): void
    {
        $this->post = $post;

        if ($post) {
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->category_id = $post->category_id;
            $this->selectedTags = $post->tags->pluck('id')->toArray();
            $this->existingThumbnail = $post->thumbnail;
            $this->content = $post->content;
            $this->meta_title = $post->meta_title ?? '';
            $this->meta_description = $post->meta_description ?? '';
            $this->meta_keywords = $post->meta_keywords ?? '';
            $this->status = $post->status;
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')
                    ->whereNull('deleted_at')
                    ->ignore($this->post?->id),
            ],
            'category_id' => ['nullable', 'exists:categories,id'],
            'selectedTags' => ['nullable', 'array'],
            'selectedTags.*' => ['exists:tags,id'],
            'thumbnail' => [
                $this->post ? 'nullable' : 'nullable',
                'image',
                'max:2048',
                'mimes:jpg,jpeg,png,webp',
            ],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.unique' => 'Slug sudah digunakan.',
            'content.required' => 'Konten wajib diisi.',
            'thumbnail.image' => 'File harus berupa gambar.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2MB.',
            'thumbnail.mimes' => 'Gambar harus berformat jpg, png, atau webp.',
        ];
    }

    public function generateSlug(): void
    {
        if (! empty($this->title)) {
            $base = Str::slug($this->title);
            $slug = $base;
            $count = 1;

            while (
                Post::where('slug', $slug)
                    ->where('id', '!=', $this->post?->id ?? 0)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $count++;
            }

            $this->slug = $slug;
        }
    }

    public function updatedTitle(): void
    {
        if (empty($this->slug) || $this->post === null) {
            $this->generateSlug();
        }
    }

    public function addNewTag(): void
    {
        $this->validate([
            'newTagName' => ['required', 'string', 'max:255'],
        ], [
            'newTagName.required' => 'Nama tag wajib diisi.',
            'newTagName.max' => 'Nama tag maksimal 255 karakter.',
        ]);

        $name = trim($this->newTagName);

        $existingTag = Tag::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if ($existingTag) {
            if (! in_array($existingTag->id, $this->selectedTags)) {
                $this->selectedTags[] = $existingTag->id;
                $this->dispatch('notify', type: 'success', message: 'Tag "' . $existingTag->name . '" dipilih.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Tag "' . $existingTag->name . '" sudah dipilih.');
            }
            $this->newTagName = '';
            return;
        }

        $base = Str::slug($name);
        $slug = $base;
        $count = 1;

        while (Tag::where('slug', $slug)->whereNull('deleted_at')->exists()) {
            $slug = $base . '-' . $count++;
        }

        $tag = Tag::create(['name' => $name, 'slug' => $slug]);

        $this->selectedTags[] = $tag->id;
        $this->newTagName = '';
        $this->dispatch('notify', type: 'success', message: 'Tag "' . $tag->name . '" berhasil ditambahkan.');
    }

    public function save(): void
    {
        $this->validate();

        $thumbnailPath = $this->existingThumbnail;

        if ($this->thumbnail) {
            if ($this->existingThumbnail) {
                Storage::disk('public')->delete($this->existingThumbnail);
            }
            $thumbnailPath = $this->thumbnail->store('posts', 'public');
        }

        $post = Post::updateOrCreate(
            ['id' => $this->post?->id],
            [
                'title' => $this->title,
                'slug' => $this->slug,
                'category_id' => $this->category_id,
                'thumbnail' => $thumbnailPath,
                'content' => $this->content,
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
                'meta_keywords' => $this->meta_keywords ?: null,
                'status' => $this->status,
                'published_at' => $this->status === 'published' ? ($this->post?->published_at ?? now()) : null,
                'author_id' => auth()->id(),
            ]
        );

        $post->tags()->sync($this->selectedTags);

        $this->dispatch('notify', type: 'success', message: $this->post ? 'Berita berhasil diperbarui.' : 'Berita berhasil ditambahkan.');
        $this->redirectRoute('admin.berita');
    }

    public function render()
    {
        return view('livewire.admin.berita-form', [
            'categories' => Category::byModule('post')->orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}
