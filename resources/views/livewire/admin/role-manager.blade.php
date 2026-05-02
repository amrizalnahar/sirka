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

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">Manajemen Roles</h1>
        @can('roles-create')
        <button wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Role
        </button>
        @endcan
    </div>

    <!-- Roles Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($roles as $role)
            @php
                $roleColors = [
                    'super-admin' => ['bg-purple-100', 'text-purple-700'],
                    'editor' => ['bg-blue-100', 'text-blue-700'],
                    'viewer' => ['bg-gray-100', 'text-gray-600'],
                ];
                $color = $roleColors[$role->name] ?? ['bg-emerald-100', 'text-emerald-700'];
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 capitalize">{{ $role->name }}</h3>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $color[0] }} {{ $color[1] }}">
                        {{ $role->users_count }} user
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-4">{{ $role->permissions->count() }} permission</p>
                <div class="flex gap-2">
                    @can('roles-edit')
                    <button wire:click="editRole('{{ $role->name }}')"
                            class="flex-1 px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">
                        Edit Permission
                    </button>
                    @endcan
                    @if($role->name !== 'super-admin')
                        @can('roles-edit')
                        <button wire:click="openRenameModal('{{ $role->name }}')"
                                class="px-3 py-2 text-gray-500 hover:text-[#1A6FAA] hover:bg-blue-50 rounded-lg transition-colors"
                                title="Ubah Nama">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        @endcan
                        @can('roles-delete')
                        <button wire:click="confirmDelete('{{ $role->name }}')"
                                class="px-3 py-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        @endcan
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Permission Editor -->
    @if($editingRole)
        @php
            $foundActions = [];
            foreach($permissionGroups as $permissions) {
                foreach($permissions as $perm) {
                    $foundActions[] = substr($perm, strrpos($perm, '-') + 1);
                }
            }
            $foundActions = array_unique($foundActions);

            $orderedActions = [];
            foreach(array_keys($actionLabels) as $action) {
                if (in_array($action, $foundActions)) {
                    $orderedActions[$action] = $actionLabels[$action];
                }
            }
            foreach($foundActions as $action) {
                if (!isset($orderedActions[$action])) {
                    $orderedActions[$action] = ucfirst($action);
                }
            }
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Edit Permission: <span class="capitalize">{{ $editingRole }}</span></h2>
                <button wire:click="cancelEdit" class="text-sm text-gray-500 hover:text-gray-700">Batal</button>
            </div>

            @php
                $allPermissions = [];
                foreach($permissionGroups as $permissions) {
                    foreach($permissions as $perm) {
                        $allPermissions[] = $perm;
                    }
                }
                $allChecked = count($allPermissions) > 0 && count(array_diff($allPermissions, $selectedPermissions)) === 0;
            @endphp

            <div class="mb-3">
                <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                    <input type="checkbox" wire:click="toggleAllPermissions" {{ $allChecked ? 'checked' : '' }}
                           class="w-4 h-4 text-[#1A6FAA] border-gray-300 rounded focus:ring-[#1A6FAA]">
                    Pilih Semua Permission
                </label>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Modul</th>
                            @foreach($orderedActions as $action => $label)
                                <th class="px-4 py-3 text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($permissionGroups as $module => $permissions)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-700">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:click="toggleModulePermissions('{{ $module }}')"
                                               {{ count($permissions) > 0 && count(array_diff($permissions, $selectedPermissions)) === 0 ? 'checked' : '' }}
                                               class="w-4 h-4 text-[#1A6FAA] border-gray-300 rounded focus:ring-[#1A6FAA]">
                                        <span>{{ $module }}</span>
                                    </label>
                                </td>
                                @foreach($orderedActions as $action => $label)
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $permName = collect($permissions)->first(fn($p) => str_ends_with($p, '-' . $action));
                                        @endphp
                                        @if($permName)
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $permName }}"
                                                       class="w-4 h-4 text-[#1A6FAA] border-gray-300 rounded focus:ring-[#1A6FAA]">
                                            </label>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelEdit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                <button wire:click="savePermissions" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">Simpan Permission</button>
            </div>
        </div>
    @endif

    <!-- Role Form Modal (Create / Rename) -->
    <x-modal name="role-form-modal" focusable>
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ $modalMode === 'create' ? 'Tambah Role Baru' : 'Ubah Nama Role' }}
            </h3>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Role</label>
                <input type="text" wire:model="roleName"
                       class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"
                       placeholder="contoh: admin, editor-keuangan">
                @error('roleName')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Hanya huruf kecil, angka, dan tanda hubung.</p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                <button type="button" wire:click="saveRole" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">Simpan</button>
            </div>
        </div>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="cancelDelete"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus role <strong class="text-gray-700">{{ $confirmingDelete }}</strong>? Role yang memiliki user tidak dapat dihapus.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <x-danger-button wire:click="deleteRole">Hapus</x-danger-button>
                </div>
            </div>
        </div>
    @endif
</div>
