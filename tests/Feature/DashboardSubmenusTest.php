<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSubmenusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $adminRole = Role::where('name', 'super_admin')->firstOrFail();
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);
    }

    public function test_guest_cannot_access_dashboard_submenus(): void
    {
        $this->get(route('dashboard.overview'))->assertRedirect(route('login'));
        $this->get(route('dashboard.composition'))->assertRedirect(route('login'));
    }

    public function test_user_can_access_submenu_1_overview_and_performance(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard.overview'));

        $response->assertStatus(200);
        $response->assertSee('Performa & Tren Konten', false);
        $response->assertSee('KPI 1 — Engagement & Views H+30', false);
        $response->assertSee('KPI 2 — Tren Engagement Rate (ER %)', false);
        $response->assertSee('Top Views (H+30)', false);
        $response->assertSee('Low Views (H+30)', false);
        $response->assertSee('Top Engagement Rate', false);
        $response->assertSee('Low Engagement Rate', false);
    }

    public function test_user_can_access_submenu_2_composition_and_effectiveness(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard.composition'));

        $response->assertStatus(200);
        $response->assertSee('Komposisi & Efektivitas Konten', false);
        $response->assertSee('KPI 3 — Komposisi Interaksi per Bulan', false);
        $response->assertSee('KPI 4 — Top & Low Performance', false);
        $response->assertSee('Modul 3 — Analisa Efektivitas Pilar Konten', false);
        $response->assertSee('Performa per Format', false);
    }

    public function test_legacy_dashboard_route_serves_overview_properly(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Performa & Tren Konten', false);
        $response->assertSee('KPI 1 — Engagement & Views H+30', false);
    }
}
