<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Daftar Permissions Lengkap
        $permissionsData = [
            // KPI Dashboard
            [
                'name' => 'dashboard.view',
                'feature_group' => 'KPI Dashboard',
                'action' => 'view',
                'label' => 'Lihat KPI Dashboard Analytics',
                'description' => 'Melihat ringkasan metrik performa, grafik tren, dan drilldown postingan bulanan',
            ],

            // Master Log Konten
            [
                'name' => 'content_logs.view',
                'feature_group' => 'Master Log Konten',
                'action' => 'view',
                'label' => 'Lihat Log Konten',
                'description' => 'Melihat tabel rekap konten Instagram dan tautan bukti evidence',
            ],
            [
                'name' => 'content_logs.create',
                'feature_group' => 'Master Log Konten',
                'action' => 'create',
                'label' => 'Tambah Konten Baru',
                'description' => 'Menambahkan data konten baru dan mengatur jadwal jatuh tempo H+30',
            ],
            [
                'name' => 'content_logs.edit',
                'feature_group' => 'Master Log Konten',
                'action' => 'edit',
                'label' => 'Edit Konten & Input Metrik H+30',
                'description' => 'Mengubah data postingan dan mengisi metrik interaksi (views, likes, shares, dll)',
            ],
            [
                'name' => 'content_logs.delete',
                'feature_group' => 'Master Log Konten',
                'action' => 'delete',
                'label' => 'Hapus Konten',
                'description' => 'Menghapus data postingan dari master log konten',
            ],

            // Integrasi Data
            [
                'name' => 'import.create',
                'feature_group' => 'Integrasi Data',
                'action' => 'create',
                'label' => 'Upload & Sinkronisasi Excel',
                'description' => 'Mengunggah file spreadsheet Excel (.xlsx) untuk import data massal',
            ],
            [
                'name' => 'export.view',
                'feature_group' => 'Integrasi Data',
                'action' => 'view',
                'label' => 'Unduh / Export CSV',
                'description' => 'Mengunduh rekapitulasi data log konten ke format CSV / Excel',
            ],

            // Manajemen Pengguna & Role
            [
                'name' => 'roles.manage',
                'feature_group' => 'Manajemen Sistem',
                'action' => 'manage',
                'label' => 'Kelola Role & Hak Akses',
                'description' => 'Mengatur role akun dan matriks permissions (view only, edit, tambah, hapus)',
            ],
            [
                'name' => 'users.manage',
                'feature_group' => 'Manajemen Sistem',
                'action' => 'manage',
                'label' => 'Kelola Akun Pengguna',
                'description' => 'Menambah user baru, mengubah profil, dan menugaskan role akun',
            ],
            [
                'name' => 'activity_logs.view',
                'feature_group' => 'Manajemen Sistem',
                'action' => 'view',
                'label' => 'Lihat Log Aktivitas',
                'description' => 'Melihat riwayat audit trail aktivitas pengguna di sistem',
            ],
        ];

        $permissionModels = [];
        foreach ($permissionsData as $pData) {
            $permissionModels[$pData['name']] = Permission::updateOrCreate(
                ['name' => $pData['name']],
                $pData
            );
        }

        // 2. Role: Super Admin (Akses Penuh)
        $superAdmin = Role::updateOrCreate(
            ['name' => 'super_admin'],
            [
                'label' => 'Super Admin',
                'description' => 'Akses penuh tanpa batas ke seluruh modul, pengaturan hak akses, dan manajemen akun',
                'is_system' => true,
            ]
        );
        $superAdmin->permissions()->sync(array_values(array_map(fn ($p) => $p->id, $permissionModels)));

        // 3. Role: Admin Konten
        $adminKonten = Role::updateOrCreate(
            ['name' => 'admin_konten'],
            [
                'label' => 'Admin Konten',
                'description' => 'Dapat mengelola konten secara penuh (lihat, tambah, edit, hapus), upload Excel, dan export CSV',
                'is_system' => false,
            ]
        );
        $adminKonten->permissions()->sync([
            $permissionModels['dashboard.view']->id,
            $permissionModels['content_logs.view']->id,
            $permissionModels['content_logs.create']->id,
            $permissionModels['content_logs.edit']->id,
            $permissionModels['content_logs.delete']->id,
            $permissionModels['import.create']->id,
            $permissionModels['export.view']->id,
            $permissionModels['activity_logs.view']->id,
        ]);

        // 4. Role: Editor Konten
        $editorKonten = Role::updateOrCreate(
            ['name' => 'editor_konten'],
            [
                'label' => 'Editor Konten',
                'description' => 'Dapat melihat, menambah, dan mengedit konten serta metrik H+30 (tanpa izin menghapus atau import)',
                'is_system' => false,
            ]
        );
        $editorKonten->permissions()->sync([
            $permissionModels['dashboard.view']->id,
            $permissionModels['content_logs.view']->id,
            $permissionModels['content_logs.create']->id,
            $permissionModels['content_logs.edit']->id,
            $permissionModels['export.view']->id,
        ]);

        // 5. Role: Viewer (View Only)
        $viewer = Role::updateOrCreate(
            ['name' => 'viewer'],
            [
                'label' => 'Viewer (Lihat Saja)',
                'description' => 'Akses murni baca / view only untuk melihat dashboard KPI, data tabel konten, dan mengunduh laporan CSV',
                'is_system' => false,
            ]
        );
        $viewer->permissions()->sync([
            $permissionModels['dashboard.view']->id,
            $permissionModels['content_logs.view']->id,
            $permissionModels['export.view']->id,
        ]);

        // 6. Hubungkan akun user yang sudah ada
        User::where('email', 'admin@dharmap.com')->update(['role_id' => $superAdmin->id]);
        User::where('email', 'admin@dharmagroup.co.id')->update(['role_id' => $superAdmin->id]);
        User::where('email', 'test@example.com')->update(['role_id' => $adminKonten->id]);
        User::whereNull('role_id')->update(['role_id' => $viewer->id]);
    }
}
