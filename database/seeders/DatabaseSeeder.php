<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminKontenRole = Role::where('name', 'admin_konten')->first();

        User::firstOrCreate(
            ['email' => 'admin@dharmap.com'],
            [
                'name' => 'Admin Dharma Group',
                'password' => 'password',
                'email_verified_at' => now(),
                'role_id' => $superAdminRole?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@dharmap.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'email_verified_at' => now(),
                'role_id' => $superAdminRole?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
                'role_id' => $adminKontenRole?->id,
            ]
        );
    }
}
