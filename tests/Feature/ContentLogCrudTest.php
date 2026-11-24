<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentLogCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_guest_cannot_access_content_logs(): void
    {
        auth()->logout();

        $response = $this->get(route('content-logs.index'));

        $response->assertRedirect('/login');
    }

    public function test_can_view_content_logs_index_page(): void
    {
        ContentLog::factory()->count(5)->create();

        $response = $this->get(route('content-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Log Konten Instagram');
    }

    public function test_can_store_new_content_log(): void
    {
        $payload = [
            'post_date' => '2026-03-10',
            'content_type' => ContentType::Reels->value,
            'title' => 'Reels Culture Baru Dharma',
            'pillar' => ContentPillar::InternalCulture->value,
            'pic_name' => 'Budi Santoso',
            'evidence_url' => 'https://www.instagram.com/p/testReels123/',
            'notes' => 'Konten pengenalan budaya kerja',
        ];

        $response = $this->post(route('content-logs.store'), $payload);

        $response->assertRedirect(route('content-logs.index'));
        $this->assertDatabaseHas('content_logs', [
            'title' => 'Reels Culture Baru Dharma',
            'pic_name' => 'Budi Santoso',
            'data_status' => DataStatus::PendingH30->value,
        ]);
    }

    public function test_can_update_content_log(): void
    {
        $log = ContentLog::factory()->create([
            'title' => 'Judul Lama',
        ]);

        $payload = [
            'post_date' => $log->post_date->format('Y-m-d'),
            'content_type' => ContentType::Feed->value,
            'title' => 'Judul Baru Diperbarui',
            'pillar' => $log->pillar->value,
            'pic_name' => 'PIC Baru',
        ];

        $response = $this->put(route('content-logs.update', $log), $payload);

        $response->assertRedirect(route('content-logs.index'));
        $this->assertDatabaseHas('content_logs', [
            'id' => $log->id,
            'title' => 'Judul Baru Diperbarui',
            'pic_name' => 'PIC Baru',
        ]);
    }

    public function test_updating_post_date_recalculates_h30_due_date(): void
    {
        $log = ContentLog::factory()->create([
            'post_date' => '2026-03-01',
        ]);

        $this->assertEquals('2026-03-31', $log->h30_due_date->format('Y-m-d'));

        $payload = [
            'post_date' => '2026-04-10',
            'content_type' => $log->content_type->value,
            'title' => $log->title,
            'pillar' => is_object($log->pillar) ? $log->pillar->value : $log->pillar,
        ];

        $response = $this->put(route('content-logs.update', $log), $payload);

        $response->assertRedirect(route('content-logs.index'));

        $log->refresh();
        $this->assertEquals('2026-04-10', $log->post_date->format('Y-m-d'));
        $this->assertEquals('2026-05-10', $log->h30_due_date->format('Y-m-d'));
    }

    public function test_updating_content_log_with_dynamic_pillar(): void
    {
        $log = ContentLog::factory()->create([
            'pillar' => 'CSR & Sustainability',
        ]);

        $this->assertEquals('CSR & Sustainability', $log->pillar_name);
        $this->assertStringContainsString('"pillar":"CSR & Sustainability"', json_encode($log));

        $payload = [
            'post_date' => $log->post_date->format('Y-m-d'),
            'content_type' => $log->content_type->value,
            'title' => 'Updated Title',
            'pillar' => 'Brand Activation',
        ];

        $response = $this->put(route('content-logs.update', $log), $payload);

        $response->assertRedirect(route('content-logs.index'));

        $log->refresh();
        $this->assertEquals('Brand Activation', $log->pillar_name);
        $this->assertStringContainsString('"pillar":"Brand Activation"', json_encode($log));
    }

    public function test_can_update_h30_metrics(): void
    {
        $log = ContentLog::factory()->pendingH30()->create([
            'post_date' => '2026-01-01',
        ]);

        $metricsPayload = [
            'views_h30' => 8000,
            'viewers_h30' => 5000,
            'likes' => 200,
            'comments' => 20,
            'shares_reposts' => 15,
            'saves' => 5, // Total interaksi = 240
            'follows' => 12,
            'notes' => 'Evaluasi selesai H+30',
        ];

        $response = $this->post(route('content-logs.metrics', $log), $metricsPayload);

        $response->assertRedirect(route('content-logs.index'));

        $log->refresh();
        $this->assertEquals(DataStatus::Completed, $log->data_status);
        $this->assertEquals(8000, $log->views_h30);
        $this->assertEquals(240, $log->total_interactions);
        $this->assertEquals(3.0000, (float) $log->engagement_rate); // 240 / 8000 * 100 = 3%
    }

    public function test_can_delete_content_log(): void
    {
        $log = ContentLog::factory()->create();

        $response = $this->delete(route('content-logs.destroy', $log));

        $response->assertRedirect(route('content-logs.index'));
        $this->assertDatabaseMissing('content_logs', ['id' => $log->id]);
    }

    public function test_can_export_csv(): void
    {
        ContentLog::factory()->count(3)->create();

        $response = $this->get(route('export.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_show_redirects_to_index_with_search(): void
    {
        $log = ContentLog::factory()->create([
            'title' => 'Konten Peluncuran Produk',
        ]);

        $response = $this->get(route('content-logs.show', $log));

        $response->assertRedirect(route('content-logs.index', ['search' => 'Konten Peluncuran Produk']));
    }
}
