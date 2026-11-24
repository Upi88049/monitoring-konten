<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use App\Models\Pillar;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $role = Role::where('name', 'super_admin')->first();
        $this->superAdmin = User::factory()->create(['role_id' => $role?->id]);
    }

    public function test_user_can_access_calendar_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('calendar.index', ['year' => 2026, 'month' => 3]));

        $response->assertOk();
        $response->assertSee('Kalender Konten & Perencanaan');
        $response->assertSee('Maret 2026');
        $response->assertViewHas('calendarDays');
    }

    public function test_calendar_ajax_request_returns_partial_view(): void
    {
        ContentLog::create([
            'post_date' => '2026-03-15',
            'content_type' => ContentType::Reels,
            'title' => 'Konten Kalender AJAX',
            'pillar' => 'Employer Branding',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('calendar.index', ['year' => 2026, 'month' => 3]));

        $response->assertOk();
        $response->assertViewIs('calendar.partials.calendar-view');
        $response->assertSee('Konten Kalender AJAX');
        $response->assertSee('Maret 2026');
        $response->assertDontSee('<!DOCTYPE html>', false);
    }

    public function test_calendar_json_api_returns_content(): void
    {
        ContentLog::create([
            'post_date' => '2026-03-15',
            'content_type' => ContentType::Reels,
            'title' => 'Konten Kalender Test',
            'pillar' => 'Employer Branding',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('calendar.index', ['year' => 2026, 'month' => 3]));

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Konten Kalender Test',
        ]);
    }

    public function test_can_create_dynamic_pillar_via_api(): void
    {
        $response = $this->actingAs($this->superAdmin)->postJson(route('pillars.store'), [
            'name' => 'Kesehatan & Olahraga',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'pillar' => [
                'name' => 'Kesehatan & Olahraga',
            ],
        ]);

        $this->assertDatabaseHas('pillars', [
            'name' => 'Kesehatan & Olahraga',
        ]);
    }

    public function test_can_create_content_with_dynamic_pillar(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('content-logs.store'), [
            'post_date' => '2026-03-20',
            'content_type' => ContentType::Feed->value,
            'title' => 'Konten CSR Berbagi',
            'pillar' => 'CSR & Peduli Lingkungan',
        ]);

        $response->assertRedirect(route('content-logs.index'));
        $this->assertDatabaseHas('content_logs', [
            'title' => 'Konten CSR Berbagi',
            'pillar' => 'CSR & Peduli Lingkungan',
        ]);

        $log = ContentLog::where('title', 'Konten CSR Berbagi')->first();
        $this->assertNotNull($log);
        $this->assertEquals('CSR & Peduli Lingkungan', $log->pillar?->value);

        // Test scopeByPillar works with dynamic pillar
        $found = ContentLog::byPillar('CSR & Peduli Lingkungan')->first();
        $this->assertNotNull($found);
        $this->assertEquals($log->id, $found->id);
    }

    public function test_dashboard_overview_filters_metrics_by_month(): void
    {
        // Content in March (views: 1000, likes: 50)
        ContentLog::create([
            'post_date' => '2026-03-10',
            'content_type' => ContentType::Feed,
            'title' => 'Post Maret',
            'pillar' => 'Employer Branding',
            'data_status' => DataStatus::Completed,
            'views_h30' => 1000,
            'likes' => 50,
        ]);

        // Content in April (views: 5000, likes: 200)
        ContentLog::create([
            'post_date' => '2026-04-10',
            'content_type' => ContentType::Reels,
            'title' => 'Post April',
            'pillar' => 'Internal/Culture',
            'data_status' => DataStatus::Completed,
            'views_h30' => 5000,
            'likes' => 200,
        ]);

        // Request with month=3 (March)
        $response = $this->actingAs($this->superAdmin)
            ->get(route('dashboard.overview', ['year' => 2026, 'month' => 3]));

        $response->assertOk();
        $response->assertViewHas('totalContents', 1);
        $response->assertViewHas('totalViewsH30', 1000);
        $response->assertViewHas('totalLikes', 50);
        $response->assertViewHas('selectedMonth', 3);
        $response->assertViewHas('chartMode', 'monthly');
    }

    public function test_login_flashes_reminder_session_and_detects_overdue_contents(): void
    {
        // Create an overdue content
        ContentLog::create([
            'post_date' => now()->subDays(35)->format('Y-m-d'),
            'content_type' => ContentType::Feed,
            'title' => 'Post Overdue Evaluasi',
            'pillar' => 'Employer Branding',
            'data_status' => DataStatus::PendingH30,
        ]);

        $response = $this->post('/login', [
            'email' => $this->superAdmin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('show_h30_reminder', true);

        // When visiting dashboard with reminder flash
        $dashboardResponse = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Pengingat Evaluasi H+30');
        $dashboardResponse->assertSee('Post Overdue Evaluasi');
    }
}
