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
        <h1 class="text-xl font-bold text-gray-800">Master Data Kategori</h1>
        @can('master-kategori-create')
        <button wire:click="openModal" class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kategori
        </button>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari kode atau nama..."
                   class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
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
                @can('master-kategori-delete')
                <button @click="showDeleteModal = true" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition-colors">Hapus</button>
                @endcan
            </div>

            <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
                <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-lg p-6 max-w-sm w-full mx-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                    <p class="text-sm text-gray-600 mb-4">Yakin ingin menghapus {{ count($selected) }} kategori yang dipilih?</p>
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
                                $pageIds = $items->pluck('id')->map(fn($id) => (string) $id)->toArray();
                                $allPageSelected = count($pageIds) > 0 && count(array_diff($pageIds, $selected)) === 0;
                            @endphp
                            <input type="checkbox" wire:click.prevent="toggleSelectPage" @checked($allPageSelected) wire:key="select-all-page-{{ $items->currentPage() }}" class="rounded border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA]">
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('kode')">
                            Kode {!! $sortField === 'kode' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('nama')">
                            Nama {!! $sortField === 'nama' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('is_active')">
                            Status {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="row-{{ $item->id }}">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}" class="rounded border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA]">
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $item->kode }}</td>
                            <td class="px-5 py-3.5 text-gray-800">{{ $item->nama }}</td>
                            <td class="px-5 py-3.5">
                                @if($item->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Non Aktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('master-kategori-edit')
                                    <button wire:click="edit({{ $item->id }})" class="p-1.5 text-gray-500 hover:text-[#1A6FAA] hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @endcan
                                    @can('master-kategori-delete')
                                    <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">Tidak ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200">
            {{ $items->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    <x-modal name="master-kategori-modal" :show="false">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>

            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="kode" value="Kode" />
                        <x-text-input id="kode" wire:model="kode" type="text" class="mt-1 block w-full" placeholder="Contoh: KAT-001" />
                        <x-input-error :messages="$errors->get('kode')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama" />
                        <x-text-input id="nama" wire:model="nama" type="text" class="mt-1 block w-full" placeholder="Masukkan nama kategori" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="is_active" value="Status" />
                        <select id="is_active" wire:model="isActive" class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm">
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>
                        <x-input-error :messages="$errors->get('isActive')" class="mt-1" />
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
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus kategori ini? Data akan dipindahkan ke tong sampah.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <x-danger-button wire:click="delete">Hapus</x-danger-button>
                </div>
            </div>
        </div>
    @endif

</div>
