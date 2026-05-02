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
        <h1 class="text-xl font-bold text-gray-800">Konfigurasi PIC</h1>
        @can('pic-configs-create')
        <button wire:click="openModal" class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Konfigurasi PIC
        </button>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari nama departemen..."
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

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 cursor-pointer hover:text-gray-800" wire:click="sortBy('name')">
                            Departemen {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' !!}
                        </th>
                        <th class="px-5 py-3 text-center">Jumlah PIC</th>
                        <th class="px-5 py-3">Nama PIC</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($departements as $dept)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="row-{{ $dept->id }}">
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $dept->name }}</td>
                            <td class="px-5 py-3.5 text-center">
                                @if($dept->pic_configs_count > 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-xs font-semibold text-green-700">
                                        {{ $dept->pic_configs_count }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        Belum dikonfigurasi
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                @if($dept->pic_configs_count > 0)
                                    {{ $dept->picConfigs->pluck('user.name')->filter()->implode(', ') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-500">
                                @if($dept->pic_configs_count > 0)
                                    {{ $dept->picConfigs->pluck('email')->filter()->implode(', ') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($dept->pic_configs_count > 0)
                                        @can('pic-configs-edit')
                                        <button wire:click="edit({{ $dept->id }})" class="px-3 py-1.5 text-sm text-[#1A6FAA] hover:bg-blue-50 rounded-md transition-colors border border-[#1A6FAA]" title="Edit">
                                            Edit
                                        </button>
                                        @endcan
                                        @can('pic-configs-delete')
                                        <button wire:click="confirmDelete({{ $dept->id }})" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus Konfigurasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500">Tidak ada data departemen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200">
            {{ $departements->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    <x-modal name="pic-config-modal" :show="false">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ $editingId ? 'Edit Konfigurasi PIC' : 'Tambah Konfigurasi PIC' }}
            </h3>

            <form wire:submit="save">
                <!-- Departemen Selection (Create Mode Only) -->
                @if(! $editingId)
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: @entangle('departemen_id').live,
                    items: @js($availableDepartements->map(fn($d) => ['id' => $d->id, 'name' => $d->name])),
                    get filteredItems() {
                        if (this.search === '') return this.items;
                        return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    get selectedName() {
                        const found = this.items.find(i => i.id == this.selectedId);
                        return found ? found.name : '';
                    },
                    select(item) {
                        this.selectedId = item.id;
                        this.search = '';
                        this.open = false;
                    },
                    clear() {
                        this.selectedId = '';
                        this.search = '';
                        this.open = false;
                    }
                }" class="relative mb-4">
                    <x-input-label for="departemen_id" value="Departemen" />
                    <div class="mt-1 relative">
                        <input
                            type="text"
                            x-show="!selectedId"
                            x-model="search"
                            @focus="open = true"
                            @click.outside="open = false"
                            placeholder="Cari dan pilih departemen..."
                            class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm"
                        >
                        <div
                            x-show="selectedId"
                            class="flex items-center justify-between w-full border border-gray-300 rounded-md shadow-sm bg-gray-50 px-3 py-2 text-sm cursor-pointer"
                            @click="selectedId = ''; open = true; $nextTick(() => $refs.searchInput.focus())"
                        >
                            <span x-text="selectedName" class="text-gray-800"></span>
                            <button type="button" @click.stop="clear()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div
                            x-show="open"
                            x-cloak
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
                        >
                            <template x-if="filteredItems.length === 0">
                                <div class="px-3 py-2 text-sm text-gray-500">Tidak ada hasil</div>
                            </template>
                            <template x-for="item in filteredItems" :key="item.id">
                                <div
                                    @click="select(item)"
                                    class="px-3 py-2 text-sm text-gray-700 hover:bg-[#E8F4FB] hover:text-[#1A6FAA] cursor-pointer"
                                    :class="selectedId == item.id ? 'bg-[#E8F4FB] text-[#1A6FAA] font-medium' : ''"
                                    x-text="item.name"
                                ></div>
                            </template>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('departemen_id')" class="mt-1" />
                </div>
                @else
                <div class="mb-4">
                    <x-input-label value="Departemen" />
                    <div class="mt-1 block w-full border border-gray-200 rounded-md shadow-sm bg-gray-50 px-3 py-2 text-sm text-gray-800">
                        {{ $departemen?->name ?? '-' }}
                    </div>
                    <input type="hidden" wire:model="departemen_id">
                </div>
                @endif

                <!-- Section PIC Users (Repeater) -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-700">User PIC</h3>
                        @can('pic-configs-create')
                        <button type="button" wire:click="addPicItem" class="text-sm text-[#1A6FAA] hover:text-[#155a8a] font-medium">
                            + Tambah PIC
                        </button>
                        @endcan
                    </div>

                    @foreach($pics as $index => $pic)
                    <div class="bg-gray-50 rounded-lg p-4 mb-3 relative" wire:key="pic-item-{{ $index }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- User Dropdown -->
                            <div>
                                <x-input-label value="User" />
                                <select wire:model.live="pics.{{ $index }}.user_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm"
                                        @disabled(! auth()->user()->can('pic-configs-edit'))
                                >
                                    <option value="">Pilih User</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" @disabled(in_array($u->id, $selectedUserIds) && $u->id != $pic['user_id'])>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pics.'.$index.'.user_id')" class="mt-1" />
                            </div>

                            <!-- Email Readonly -->
                            <div>
                                <x-input-label value="Email" />
                                <x-text-input type="text" wire:model="pics.{{ $index }}.email" readonly class="mt-1 block w-full bg-gray-100" />
                                <x-input-error :messages="$errors->get('pics.'.$index.'.email')" class="mt-1" />
                            </div>
                        </div>

                        @if(count($pics) > 1)
                            @can('pic-configs-delete')
                            <button type="button" wire:click="removePicItem({{ $index }})" class="absolute top-2 right-2 text-red-400 hover:text-red-600 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            @endcan
                        @endif
                    </div>
                    @endforeach

                    @if(empty($pics))
                    <p class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">Belum ada PIC. Klik "Tambah PIC" untuk menambahkan.</p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    @can('pic-configs-edit')
                    <x-primary-button type="submit">Simpan Konfigurasi</x-primary-button>
                    @endcan
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
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus semua konfigurasi PIC untuk departemen ini?</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <x-danger-button wire:click="delete">Hapus</x-danger-button>
                </div>
            </div>
        </div>
    @endif
</div>
