<div>
    <!-- Toast -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
         :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
         style="display: none;">
        <span x-text="message"></span>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">Moderasi Konten</h1>
        @can('moderation-manage')
        <button wire:click="openModal" class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kata
        </button>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col lg:flex-row gap-3">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari kata..."
                   class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
        </div>
        <div class="flex gap-3 flex-col sm:flex-row">
            <select wire:model.live="categoryFilter" class="w-full sm:w-48 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="">Semua Kategori</option>
                @foreach($categoryLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="perPage" class="w-full sm:w-28 border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="15">15 / hal</option>
                <option value="30">30 / hal</option>
                <option value="50">50 / hal</option>
            </select>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(count($selectedIds) > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 flex items-center justify-between" x-data="{ showDeleteModal: false }">
            <span class="text-sm text-blue-800">{{ count($selectedIds) }} item dipilih</span>
            <div class="flex gap-2">
                @can('moderation-manage')
                <button @click="showDeleteModal = true" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition-colors">Hapus</button>
                @endcan
            </div>

            <!-- Delete Confirmation Modal -->
            <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
                <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-lg p-6 max-w-sm w-full mx-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                    <p class="text-sm text-gray-600 mb-4">Yakin ingin menghapus {{ count($selectedIds) }} kata yang dipilih? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="flex justify-end gap-2">
                        <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                        <button wire:click="deleteSelected" @click="showDeleteModal = false" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="moderation-table-wrap overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
            <style>
                .moderation-table-wrap::-webkit-scrollbar { height: 8px; }
                .moderation-table-wrap::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
                .moderation-table-wrap::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
                .moderation-table-wrap::-webkit-scrollbar-thumb:hover { background: #64748b; }
                .moderation-table-wrap { scrollbar-width: thin; scrollbar-color: #94a3b8 #f1f5f9; }
            </style>
            <table class="w-full min-w-[640px] text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 w-10 bg-gray-50">
                            <input type="checkbox" wire:click="toggleSelectAll($event.target.checked)" class="border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA] rounded"
                                :checked="$wire.selectedIds.length > 0 && $wire.selectedIds.length == {{ $words->count() }}">
                        </th>
                        <th class="px-4 py-3 bg-gray-50 cursor-pointer hover:text-gray-800 whitespace-nowrap" wire:click="sortBy('word')">
                            Kata {!! $sortField === 'word' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-4 py-3 bg-gray-50 whitespace-nowrap">Kategori</th>
                        <th class="px-4 py-3 bg-gray-50 whitespace-nowrap hidden sm:table-cell">Tingkat</th>
                        <th class="px-4 py-3 bg-gray-50 whitespace-nowrap hidden md:table-cell">Tipe</th>
                        <th class="px-4 py-3 bg-gray-50 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 bg-gray-50 text-right whitespace-nowrap min-w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($words as $word)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="row-{{ $word->id }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $word->id }}" class="border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA] rounded">
                            </td>
                            <td class="px-4 py-3">
                                <code class="text-sm bg-gray-100 px-2 py-0.5 rounded text-gray-800 break-all">{{ $word->word }}</code>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $catClass = match($word->category) {
                                        'vulgar' => 'bg-red-100 text-red-700',
                                        'sara' => 'bg-orange-100 text-orange-700',
                                        'hate_speech' => 'bg-purple-100 text-purple-700',
                                        'spam' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $catClass }}">
                                    {{ $categoryLabels[$word->category] ?? $word->category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                @php
                                    $sevClass = match($word->severity) {
                                        'high' => 'bg-red-100 text-red-700',
                                        'medium' => 'bg-yellow-100 text-yellow-700',
                                        'low' => 'bg-green-100 text-green-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sevClass }}">
                                    {{ $severityLabels[$word->severity] ?? $word->severity }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                {{ $word->is_regex ? 'Regex' : 'Teks' }}
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleActive({{ $word->id }})" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[#1A6FAA] focus:ring-offset-2 {{ $word->is_active ? 'bg-[#1A6FAA]' : 'bg-gray-300' }}">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform {{ $word->is_active ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('moderation-manage')
                                    <button wire:click="edit({{ $word->id }})" class="p-1.5 text-gray-500 hover:text-[#1A6FAA] hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $word->id }})" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500">Tidak ada data kata terlarang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200">
            {{ $words->links() }}
        </div>
    </div>

    <!-- Form Modal -->
    @if($showForm)
        <div x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="closeModal()"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 z-10 p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $editingId ? 'Edit' : 'Tambah' }} Kata Terlarang</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata / Pattern <span class="text-red-500">*</span></label>
                        <input wire:model="word" type="text" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm" placeholder="contoh: anjing atau /pattern/i">
                        @error('word') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select wire:model="category" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                            @foreach($categoryLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Keparahan <span class="text-red-500">*</span></label>
                        <select wire:model="severity" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                            @foreach($severityLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('severity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="isRegex" class="border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA] rounded">
                            <span class="ml-2 text-sm text-gray-700">Regex pattern</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="isActive" class="border-gray-300 text-[#1A6FAA] focus:ring-[#1A6FAA] rounded">
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="save" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="cancelDelete"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus kata ini dari daftar moderasi?</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    @endif

</div>
