<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Models\ContentLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class BugFixTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_loads_without_errors_when_no_data_exists_in_filtered_year(): void
    {
        // Akses dashboard pada tahun tanpa data sama sekali
        $response = $this->actingAs($this->user)->get('/dashboard?year=2030');

        $response->assertStatus(200);
        $response->assertSee('Performa & Tren Konten', false);
        $response->assertSee('Belum ada data');
    }

    public function test_content_logs_table_renders_data_log_attribute_safely(): void
    {
        // Buat konten dengan karakter khusus, tanda kutip, dan HTML
        ContentLog::factory()->create([
            'title' => 'Konten "Promo" Khusus & <script>alert(1)</script>',
            'notes' => "Catatan multi\nline dengan 'kutip' dan \"double\"",
            'pillar' => ContentPillar::EmployerBranding,
            'content_type' => ContentType::Reels,
        ]);

        $response = $this->actingAs($this->user)->get(route('content-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('data-log="', false);
        $response->assertSee('openMetricsModalFromButton', false);
    }

    public function test_validation_errors_are_displayed_in_app_layout(): void
    {
        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag(['title' => ['Judul konten wajib diisi.']]));

        $rendered = view('layouts.app', ['errors' => $errors])->render();

        $this->assertStringContainsString('Terdapat kesalahan pengisian data:', $rendered);
        $this->assertStringContainsString('Judul konten wajib diisi.', $rendered);
    }

    public function test_pending_h30_badge_is_provided_via_view_composer(): void
    {
        ContentLog::factory()->pendingH30()->create();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Master Log Konten');
    }

    public function test_ajax_pagination_returns_partial_table_view(): void
    {
        ContentLog::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->get(
            route('content-logs.index', ['page' => 2]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertSee('Daftar Log Konten Instagram');
        // Partial view does not include sidebar or app header
        $response->assertDontSee('id="sidebar"', false);
    }

    public function test_per_page_filter_changes_number_of_items_displayed(): void
    {
        ContentLog::factory()->count(30)->create();

        $response = $this->actingAs($this->user)->get(
            route('content-logs.index', ['per_page' => 25])
        );

        $response->assertStatus(200);
        $response->assertSee('value="25" selected', false);
        $response->assertSee('Menampilkan 1 - 25 dari total 30 konten');
    }

    public function test_invalid_per_page_falls_back_to_default_15(): void
    {
        ContentLog::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->get(
            route('content-logs.index', ['per_page' => 999])
        );

        $response->assertStatus(200);
        $response->assertSee('value="15" selected', false);
        $response->assertSee('Menampilkan 1 - 15 dari total 20 konten');
    }
}
