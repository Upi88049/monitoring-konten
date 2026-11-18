<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_page_loads_successfully(): void
    {
        // Sub menu 1: Overview & Performa
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Performa & Tren Konten', false);
        $response->assertSee('KPI 1 — Engagement & Views H+30', false);
        $response->assertSee('KPI 2 — Tren Engagement Rate (ER %)', false);
        $response->assertSee('Top Views (H+30)', false);
        $response->assertSee('Low Views (H+30)', false);

        // Sub menu 2: Komposisi & Efektivitas
        $response2 = $this->actingAs($this->user)->get(route('dashboard.composition'));
        $response2->assertStatus(200);
        $response2->assertSee('Komposisi & Efektivitas Konten', false);
        $response2->assertSee('KPI 3 — Komposisi Interaksi per Bulan', false);
        $response2->assertSee('KPI 4 — Top & Low Performance', false);
        $response2->assertSee('Pilar Tertinggi (Best ER)');
        $response2->assertSee('Pilar Terendah (Lowest ER)');
    }

    public function test_dashboard_calculates_kpi_summary_correctly(): void
    {
        // Konten selesai evaluasi
        ContentLog::factory()->create([
            'post_date' => '2026-01-15',
            'views_h30' => 10000,
            'likes' => 150,
            'comments' => 20,
            'shares_reposts' => 20,
            'saves' => 10, // Total interaksi = 200
            'follows' => 15,
            'data_status' => DataStatus::Completed,
        ]);

        // Konten pending H+30 (tidak boleh merusak pembagi rata-rata)
        ContentLog::factory()->create([
            'post_date' => '2026-01-20',
            'views_h30' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares_reposts' => 0,
            'saves' => 0,
            'follows' => 0,
            'data_status' => DataStatus::PendingH30,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard?year=2026');

        $response->assertStatus(200);
        $response->assertViewHas('totalContents', 2);
        $response->assertViewHas('totalCompleted', 1);
        $response->assertViewHas('totalPending', 1);
        $response->assertViewHas('totalViewsH30', 10000);
        $response->assertViewHas('lowViews');
        $response->assertViewHas('highestPillar');
        $response->assertViewHas('lowestPillar');
        $response->assertViewHas('totalEngagement', 200);
        $response->assertViewHas('totalFollows', 15);
        $response->assertViewHas('avgEngagementRate', 2.00); // 200 / 10000 * 100 = 2%
    }

    public function test_dashboard_filters_by_pillar_and_content_type(): void
    {
        ContentLog::factory()->create([
            'post_date' => '2026-03-01',
            'pillar' => ContentPillar::EmployerBranding,
            'content_type' => ContentType::Reels,
            'title' => 'Reels Khusus Employer Branding',
            'data_status' => DataStatus::Completed,
            'views_h30' => 5000,
        ]);

        ContentLog::factory()->create([
            'post_date' => '2026-03-05',
            'pillar' => ContentPillar::InternalCulture,
            'content_type' => ContentType::Feed,
            'title' => 'Feed Khusus Internal Culture',
            'data_status' => DataStatus::Completed,
            'views_h30' => 3000,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard?year=2026&pillar=Employer+Branding&content_type=Reels');

        $response->assertStatus(200);
        $response->assertViewHas('totalContents', 1);
        $response->assertViewHas('totalViewsH30', 5000);
    }
}
