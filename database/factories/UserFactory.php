<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => function () {
                return Role::firstOrCreate(
                    ['name' => 'super_admin'],
                    [
                        'label' => 'Super Admin',
                        'description' => 'Akses penuh',
                        'is_system' => true,
                    ]
                )->id;
            },
        ];
    }

    /**
     * Set role as Super Admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['name' => 'super_admin'],
                ['label' => 'Super Admin', 'is_system' => true]
            )->id,
        ]);
    }

    /**
     * Set role as Admin Konten.
     */
    public function adminKonten(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['name' => 'admin_konten'],
                ['label' => 'Admin Konten', 'is_system' => false]
            )->id,
        ]);
    }

    /**
     * Set role as Viewer.
     */
    public function viewer(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['name' => 'viewer'],
                ['label' => 'Viewer (Lihat Saja)', 'is_system' => false]
            )->id,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
