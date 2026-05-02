<div>
    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
         :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
         style="display: none;">
        <span x-text="message"></span>
    </div>

    <div class="max-w-2xl">
        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('admin.users') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-xl font-bold text-gray-800">{{ $isCreate ? 'Tambah User' : 'Edit User' }}</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Nama" />
                        <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="Nama lengkap" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" wire:model="email" type="text" class="mt-1 block w-full" placeholder="email@example.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password {{ $isCreate ? '' : '(Kosongkan jika tidak ingin mengubah)' }}" />
                        <div class="relative mt-1" x-data="{ show: false }">
                            <x-text-input id="password" wire:model="password" ::type="show ? 'text' : 'password'" class="block w-full pr-10" placeholder="{{ $isCreate ? 'Minimal 8 karakter' : '' }}" />
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M12 12l-4.242-4.242M12 12l4.242 4.242M3 3l3.59 3.59m11.46 11.46l3.59 3.59"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: @entangle('departemen_id'),
                        items: @js($departements->map(fn($d) => ['id' => $d->id, 'name' => $d->name])),
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
                    }" class="relative">
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
                        <input type="hidden" wire:model="departemen_id">
                        <x-input-error :messages="$errors->get('departemen_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Role" />
                        <select id="role" wire:model="role" class="mt-1 block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-md shadow-sm text-sm">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="$set('is_active', ! $is_active)"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[#1A6FAA] focus:ring-offset-2 {{ $is_active ? 'bg-[#1A6FAA]' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                        <span class="text-sm text-gray-700">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <input type="hidden" wire:model="is_active">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div>
                        @if(! $isCreate)
                            <button type="button" wire:click="sendInvitation" wire:loading.attr="disabled" wire:target="sendInvitation" class="inline-flex items-center justify-center gap-2 px-4 h-10 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed whitespace-nowrap">
                                <span wire:loading wire:target="sendInvitation" class="whitespace-nowrap">Mengirim...</span>
                                <span wire:loading.remove wire:target="sendInvitation" class="inline-flex items-center whitespace-nowrap">
                                    <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    Kirim Undangan
                                </span>
                            </button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.users') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                        <x-primary-button type="submit">{{ $isCreate ? 'Simpan' : 'Simpan Perubahan' }}</x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
