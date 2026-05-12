<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            'dashboard-access',
            // Posts
            'posts-list', 'posts-create', 'posts-edit', 'posts-delete',
            // Master
            'categories-list', 'categories-create', 'categories-edit', 'categories-delete',
            'tags-list', 'tags-create', 'tags-edit', 'tags-delete',
            // Departements
            'departements-list', 'departements-create', 'departements-edit', 'departements-delete',
            // PIC Configs
            'pic-configs-list', 'pic-configs-create', 'pic-configs-edit', 'pic-configs-delete',
            // System
            'users-list', 'users-create', 'users-edit', 'users-delete',
            'roles-list', 'roles-create', 'roles-edit', 'roles-delete',
            'settings-list', 'settings-edit',
            'audit-logs-list',
            'system-logs-list',
            'system-email-tester',
            'system-queue-monitor',
            'schedule-tasks-list',
            'schedule-tasks-execute',
            'moderation-manage',
            // Laporan
            'laporan-list', 'laporan-create', 'laporan-edit', 'laporan-delete', 'laporan-submit', 'laporan-approve',
            // Master Data Laporan
            'jenis-laporan-list', 'jenis-laporan-create', 'jenis-laporan-edit', 'jenis-laporan-delete',
            'master-akun-list', 'master-akun-create', 'master-akun-edit', 'master-akun-delete',
            'master-kategori-list', 'master-kategori-create', 'master-kategori-edit', 'master-kategori-delete',
            'approval-chain-list', 'approval-chain-create', 'approval-chain-edit', 'approval-chain-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $defaultRoles = ['super-admin', 'editor', 'viewer'];
        foreach ($defaultRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $superAdmin = Role::findByName('super-admin');
        $editor = Role::findByName('editor');
        $viewer = Role::findByName('viewer');

        $superAdmin->givePermissionTo(Permission::all());

        $editor->givePermissionTo([
            'dashboard-access',
            'posts-list', 'posts-create', 'posts-edit', 'posts-delete',
            'categories-list', 'categories-create', 'categories-edit', 'categories-delete',
            'tags-list', 'tags-create', 'tags-edit', 'tags-delete',
            'departements-list', 'departements-create', 'departements-edit', 'departements-delete',
            'pic-configs-list', 'pic-configs-create', 'pic-configs-edit', 'pic-configs-delete',
            'laporan-list', 'laporan-create', 'laporan-edit', 'laporan-delete', 'laporan-submit',
            'jenis-laporan-list', 'jenis-laporan-create', 'jenis-laporan-edit', 'jenis-laporan-delete',
            'master-akun-list', 'master-akun-create', 'master-akun-edit', 'master-akun-delete',
            'master-kategori-list', 'master-kategori-create', 'master-kategori-edit', 'master-kategori-delete',
            'approval-chain-list', 'approval-chain-create', 'approval-chain-edit', 'approval-chain-delete',
        ]);

        $viewer->givePermissionTo([
            'dashboard-access',
            'posts-list',
        ]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('secret'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('super-admin');

        $editorUser = User::firstOrCreate(
            ['email' => 'editor@mail.com'],
            [
                'name' => 'Editor',
                'password' => bcrypt('secret'),
                'email_verified_at' => now(),
            ]
        );
        $editorUser->assignRole('editor');

        $viewerUser = User::firstOrCreate(
            ['email' => 'viewer@mail.com'],
            [
                'name' => 'Viewer',
                'password' => bcrypt('secret'),
                'email_verified_at' => now(),
            ]
        );
        $viewerUser->assignRole('viewer');
    }
}