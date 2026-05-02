<?php

namespace App\Livewire\Admin;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class RoleManager extends Component
{
    public ?string $editingRole = null;
    public array $selectedPermissions = [];

    // Role CRUD
    public string $roleName = '';
    public ?string $originalRoleName = null;
    public string $modalMode = 'create'; // 'create' | 'rename'
    public ?string $confirmingDelete = null;

    protected function getPermissionGroups(): array
    {
        $permissions = Permission::all()->pluck('name');
        $groups = [];

        foreach ($permissions as $perm) {
            $parts = explode('-', $perm);
            $action = array_pop($parts);
            $module = implode('-', $parts);

            $moduleLabel = str($module)->replace('-', ' ')->title()->toString();

            $groups[$moduleLabel][] = $perm;
        }

        ksort($groups);

        return $groups;
    }

    protected function getActionLabels(): array
    {
        return [
            'list' => 'Lihat',
            'create' => 'Tambah',
            'edit' => 'Edit',
            'delete' => 'Hapus',
            'export' => 'Export',
            'access' => 'Akses',
            'execute' => 'Eksekusi',
            'manage' => 'Kelola',
            'tester' => 'Tester',
            'monitor' => 'Monitor',
        ];
    }

    public function openCreateModal(): void
    {
        $this->roleName = '';
        $this->originalRoleName = null;
        $this->modalMode = 'create';
        $this->resetValidation();
        $this->dispatch('open-modal', 'role-form-modal');
    }

    public function openRenameModal(string $roleName): void
    {
        if ($roleName === 'super-admin') {
            $this->dispatch('notify', type: 'error', message: 'Role super-admin tidak dapat diubah namanya.');
            return;
        }

        $this->roleName = $roleName;
        $this->originalRoleName = $roleName;
        $this->modalMode = 'rename';
        $this->resetValidation();
        $this->dispatch('open-modal', 'role-form-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', 'role-form-modal');
        $this->roleName = '';
        $this->originalRoleName = null;
        $this->resetValidation();
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles', 'name')->ignore($this->originalRoleName, 'name'),
            ],
        ], [
            'roleName.required' => 'Nama role wajib diisi.',
            'roleName.regex' => 'Nama role hanya boleh huruf kecil, angka, dan tanda hubung.',
            'roleName.unique' => 'Nama role sudah digunakan.',
        ]);

        if ($this->modalMode === 'create') {
            Role::create(['name' => $this->roleName, 'guard_name' => 'web']);
            $this->dispatch('notify', type: 'success', message: "Role {$this->roleName} berhasil dibuat.");
        } elseif ($this->modalMode === 'rename') {
            $role = Role::findByName($this->originalRoleName);
            $role->name = $this->roleName;
            $role->save();
            $this->dispatch('notify', type: 'success', message: 'Role berhasil diubah namanya.');
        }

        $this->closeModal();
    }

    public function confirmDelete(string $roleName): void
    {
        $this->confirmingDelete = $roleName;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function deleteRole(): void
    {
        if (! $this->confirmingDelete) {
            return;
        }

        $role = Role::findByName($this->confirmingDelete);

        if ($role->name === 'super-admin') {
            $this->dispatch('notify', type: 'error', message: 'Role super-admin tidak dapat dihapus.');
            $this->confirmingDelete = null;
            return;
        }

        if ($role->users->count() > 0) {
            $this->dispatch('notify', type: 'error', message: 'Role masih memiliki user. Pindahkan user ke role lain terlebih dahulu.');
            $this->confirmingDelete = null;
            return;
        }

        $role->delete();
        $this->confirmingDelete = null;
        $this->dispatch('notify', type: 'success', message: 'Role berhasil dihapus.');
    }

    public function editRole(string $roleName): void
    {
        $this->editingRole = $roleName;
        $role = Role::findByName($roleName);
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    public function cancelEdit(): void
    {
        $this->editingRole = null;
        $this->selectedPermissions = [];
    }

    public function toggleAllPermissions(): void
    {
        $allPermissions = Permission::all()->pluck('name')->toArray();

        if (count(array_diff($allPermissions, $this->selectedPermissions)) === 0) {
            $this->selectedPermissions = [];
        } else {
            $this->selectedPermissions = $allPermissions;
        }
    }

    public function toggleModulePermissions(string $module): void
    {
        $groups = $this->getPermissionGroups();
        $modulePerms = $groups[$module] ?? [];

        if (empty($modulePerms)) {
            return;
        }

        $allSelected = count(array_diff($modulePerms, $this->selectedPermissions)) === 0;

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $modulePerms));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $modulePerms)));
        }
    }

    public function savePermissions(): void
    {
        if (! $this->editingRole) {
            return;
        }

        $role = Role::findByName($this->editingRole);
        $role->syncPermissions($this->selectedPermissions);

        $this->dispatch('notify', type: 'success', message: "Permission untuk role {$this->editingRole} berhasil diperbarui.");
        $this->cancelEdit();
    }

    public function render()
    {
        $roles = Role::withCount('users')->get();

        return view('livewire.admin.role-manager', [
            'roles' => $roles,
            'permissionGroups' => $this->getPermissionGroups(),
            'actionLabels' => $this->getActionLabels(),
        ]);
    }
}
