<div>
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.berita') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-xl font-bold text-gray-800">{{ $post ? 'Edit Berita' : 'Tulis Berita' }}</h1>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="md:col-span-2">
                        <x-input-label for="title" value="Judul" />
                        <x-text-input id="title" wire:model="title" type="text" class="mt-1 block w-full" placeholder="Masukkan judul berita" />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <!-- Slug -->
                    <div class="md:col-span-2">
                        <x-input-label for="slug" value="Slug" />
                        <div class="flex gap-2 mt-1">
                            <x-text-input id="slug" wire:model="slug" type="text" class="block w-full" placeholder="auto-generated" />
                            <button type="button" wire:click="generateSlug" class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-md hover:bg-gray-200 transition-colors shrink-0">Generate</button>
                        </div>
                        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                    </div>

                    <!-- Category -->
                    <div class="md:col-span-2">
                        <x-input-label for="category_id" value="Kategori" />
                        <select id="category_id" wire:model="category_id" class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <x-input-label value="Tags" class="mb-2" />
                <div class="flex gap-2 mb-3">
                    <x-text-input wire:model="newTagName" type="text" placeholder="Tambah tag baru..." class="block w-full text-sm" x-on:keydown.shift.enter.prevent="$wire.addNewTag()" />
                    <button type="button" wire:click="addNewTag" class="px-3 py-2 bg-[#1A6FAA] text-white text-sm rounded-md hover:bg-[#155a8a] transition-colors shrink-0">Tambah</button>
                </div>
                <div class="flex flex-wrap gap-2" x-data="{ selected: @entangle('selectedTags') }">
                    @foreach($tags as $tag)
                        <button type="button"
                            @click="const id = '{{ $tag->id }}'; const has = selected.map(String).includes(id); selected = has ? selected.filter(v => String(v) !== id) : [...selected, id]"
                            :class="selected.map(String).includes('{{ $tag->id }}') ? 'bg-[#1A6FAA] border-[#1A6FAA] text-white' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg border cursor-pointer transition-colors text-sm">
                            {{ $tag->name }}
                        </button>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('selectedTags')" class="mt-1" />
                <x-input-error :messages="$errors->get('newTagName')" class="mt-1" />
            </div>

            <!-- Thumbnail -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <x-input-label value="Thumbnail" class="mb-2" />
                <div class="flex items-start gap-4">
                    @if($thumbnail)
                        <div class="relative">
                            <img src="{{ $thumbnail->temporaryUrl() }}" class="w-32 h-24 rounded-lg object-cover border border-gray-200">
                            <button type="button" wire:click="$set('thumbnail', null)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        </div>
                    @elseif($existingThumbnail)
                        <div class="relative">
                            <img src="{{ Storage::url($existingThumbnail) }}" class="w-32 h-24 rounded-lg object-cover border border-gray-200">
                            <button type="button" wire:click="$set('existingThumbnail', null)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">×</button>
                        </div>
                    @endif
                    <div class="flex-1">
                        <input type="file" wire:model="thumbnail"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#1A6FAA] file:text-white hover:file:bg-[#155a8a]">
                        <p class="mt-1 text-xs text-gray-400">Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                        <x-input-error :messages="$errors->get('thumbnail')" class="mt-1" />
                        <div wire:loading wire:target="thumbnail" class="mt-2 text-sm text-[#1A6FAA]">Mengunggah...</div>
                    </div>
                </div>
            </div>

            <!-- Content with Trix -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <x-input-label for="content" value="Konten" />
                <div class="mt-1" wire:ignore>
                    <trix-editor
                        x-data
                        x-on:trix-change="$wire.set('content', $event.target.value)"
                        x-ref="trix"
                        input="trix-content"
                        class="trix-content border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm min-h-[300px]"
                    >{!! $content !!}</trix-editor>
                </div>
                <input type="hidden" id="trix-content" value="{{ $content }}">
                <x-input-error :messages="$errors->get('content')" class="mt-1" />
            </div>

            <!-- SEO Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span class="font-medium text-gray-800">Pengaturan SEO</span>
                        <span class="text-xs text-gray-400">(opsional)</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6 space-y-4 border-t border-gray-100 pt-4">
                    <div>
                        <x-input-label for="meta_title" value="Meta Title" />
                        <x-text-input id="meta_title" wire:model="meta_title" type="text" class="mt-1 block w-full" placeholder="Judul untuk mesin pencari (default: judul artikel)" />
                        <x-input-error :messages="$errors->get('meta_title')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="meta_description" value="Meta Description" />
                        <textarea id="meta_description" wire:model="meta_description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm" placeholder="Deskripsi untuk mesin pencari (default: cuplikan konten)"></textarea>
                        <x-input-error :messages="$errors->get('meta_description')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="meta_keywords" value="Meta Keywords" />
                        <x-text-input id="meta_keywords" wire:model="meta_keywords" type="text" class="mt-1 block w-full" placeholder="kata kunci, pisahkan dengan koma" />
                        <x-input-error :messages="$errors->get('meta_keywords')" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <x-input-label value="Status" class="mb-3" />
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="status" value="draft" class="border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA]">
                        <span class="ml-2 text-sm text-gray-700">Draft</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="status" value="published" class="border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA]">
                        <span class="ml-2 text-sm text-gray-700">Published</span>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('status')" class="mt-1" />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.berita') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <x-primary-button type="submit">{{ $post ? 'Simpan Perubahan' : 'Tambah Berita' }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
