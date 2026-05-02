<div>
    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition
         class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
         :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
         style="display: none;"
    >
        <span x-text="message"></span>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">Manajemen Kategori</h1>
        @can('categories-create')
        <button wire:click="openModal" class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kategori
        </button>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari nama kategori..."
                   class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
        </div>
        <div class="sm:w-48">
            <select wire:model.live="moduleFilter"
                    class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="">Semua Tipe Modul</option>
                @foreach($moduleTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-32">
            <select wire:model.live="perPage"
                    class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="10">10 / hal</option>
                <option value="25">25 / hal</option>
                <option value="50">50 / hal</option>
            </select>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selected) > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 flex items-center justify-between" x-data="{ showDeleteModal: false }">
            <span class="text-sm text-blue-800">{{ count($selected) }} item dipilih</span>
            <div class="flex gap-2">
                @can('categories-delete')
                <button @click="showDeleteModal = true" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition-colors">Hapus</button>
                @endcan
            </div>

            <!-- Delete Confirmation Modal -->
            <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
                <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-lg p-6 max-w-sm w-full mx-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                    <p class="text-sm text-gray-600 mb-4">Yakin ingin menghapus {{ count($selected) }} kategori yang dipilih? Kategori yang masih memiliki relasi konten akan dilewati.</p>
                    <div class="flex justify-end gap-2">
                        <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <button wire:click="bulkDelete" @click="showDeleteModal = false" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 w-10">
                            @php
                                $pageIds = $categories->pluck('id')->map(fn($id) => (string) $id)->toArray();
                                $allPageSelected = count($pageIds) > 0 && count(array_diff($pageIds, $selected)) === 0;
                            @endphp
                            <input type="checkbox" wire:click.prevent="toggleSelectPage" @checked($allPageSelected) wire:key="select-all-page-{{ $categories->currentPage() }}" class="rounded border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA]">
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('name')">
                            Nama {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('slug')">
                            Slug {!! $sortField === 'slug' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('module_type')">
                            Tipe Modul {!! $sortField === 'module_type' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3">Deskripsi</th>
                        <th class="px-5 py-3 text-center">Jumlah Konten</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="row-{{ $category->id }}">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" wire:model.live="selected" value="{{ $category->id }}" class="rounded border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA]">
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $category->name }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $category->slug }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $moduleTypes[$category->module_type] ?? $category->module_type }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate">{{ $category->description ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-xs font-semibold text-gray-700">
                                    {{ $category->total_content }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('categories-edit')
                                    <button wire:click="edit({{ $category->id }})" class="p-1.5 text-gray-500 hover:text-[#1A6FAA] hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @endcan
                                    @can('categories-delete')
                                    <button wire:click="confirmDelete({{ $category->id }})" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500">Tidak ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    <x-modal name="category-modal" :show="false">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>

            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="module_type" value="Tipe Modul" />
                        <select id="module_type" wire:model="module_type" class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm">
                            @foreach($moduleTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('module_type')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama Kategori" />
                        <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="Masukkan nama kategori" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="slug" value="Slug" />
                        <div class="flex gap-2 mt-1">
                            <x-text-input id="slug" wire:model="slug" type="text" class="block w-full" placeholder="auto-generated" />
                            <button type="button" wire:click="generateSlug" class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-md hover:bg-gray-200 transition-colors">Generate</button>
                        </div>
                        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm" placeholder="Deskripsi kategori (opsional)"></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <x-primary-button type="submit">{{ $editingId ? 'Simpan Perubahan' : 'Tambah Kategori' }}</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="cancelDelete"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                @if($deleteError)
                    <div class="mb-4 rounded-lg px-4 py-3 text-sm bg-red-50 text-red-700 border border-red-200 flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ $deleteError }}</span>
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus kategori ini? Data akan dipindahkan ke tong sampah.</p>
                @endif
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    @if(!$deleteError)
                        <x-danger-button wire:click="delete">Hapus</x-danger-button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
