<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Enums\EvidenceStatus;
use App\Models\ContentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_content_log_via_factory(): void
    {
        $log = ContentLog::factory()->create([
            'title' => 'Konten Inovasi 2026',
        ]);

        $this->assertDatabaseHas('content_logs', [
            'id' => $log->id,
            'title' => 'Konten Inovasi 2026',
        ]);
        $this->assertInstanceOf(ContentType::class, $log->content_type);
        $this->assertInstanceOf(ContentPillar::class, $log->pillar);
    }

    public function test_auto_calculates_total_interactions_and_engagement_rate(): void
    {
        $log = ContentLog::create([
            'post_date' => '2026-02-01',
            'content_type' => ContentType::Reels,
            'title' => 'Reels Plant Tour',
            'pillar' => ContentPillar::EmployerBranding,
            'views_h30' => 5000,
            'likes' => 100,
            'comments' => 15,
            'shares_reposts' => 25,
            'saves' => 10,
        ]);

        // Total interaksi: 100 + 15 + 25 + 10 = 150
        $this->assertEquals(150, $log->total_interactions);

        // Engagement Rate: (150 / 5000) * 100 = 3.0000%
        $this->assertEquals('3.0000', (string) $log->engagement_rate);
    }

    public function test_auto_calculates_h30_due_date(): void
    {
        $log = ContentLog::create([
            'post_date' => '2026-01-10',
            'content_type' => ContentType::Feed,
            'title' => 'Tahun Baru 2026',
            'pillar' => ContentPillar::InternalCulture,
        ]);

        $this->assertNotNull($log->h30_due_date);
        $this->assertEquals('2026-02-09', $log->h30_due_date->format('Y-m-d'));
    }

    public function test_auto_assigns_evidence_status_based_on_url(): void
    {
        $logWithUrl = ContentLog::create([
            'post_date' => '2026-01-10',
            'content_type' => ContentType::Feed,
            'title' => 'Ada Evidence',
            'pillar' => ContentPillar::InternalCulture,
            'evidence_url' => 'https://instagram.com/p/abc123xyz',
        ]);

        $logWithoutUrl = ContentLog::create([
            'post_date' => '2026-01-10',
            'content_type' => ContentType::Feed,
            'title' => 'Tanpa Evidence',
            'pillar' => ContentPillar::InternalCulture,
            'evidence_url' => null,
        ]);

        $this->assertEquals(EvidenceStatus::Ada, $logWithUrl->evidence_status);
        $this->assertEquals(EvidenceStatus::Belum, $logWithoutUrl->evidence_status);
    }

    public function test_auto_assigns_data_status_based_on_metrics(): void
    {
        $pendingLog = ContentLog::create([
            'post_date' => '2026-03-01',
            'content_type' => ContentType::Carousel,
            'title' => 'Konten Baru Menunggu H30',
            'pillar' => ContentPillar::InfoLowongan,
            'views_h30' => 0,
            'likes' => 0,
        ]);

        $completedLog = ContentLog::create([
            'post_date' => '2026-01-01',
            'content_type' => ContentType::Carousel,
            'title' => 'Konten Selesai Evaluasi',
            'pillar' => ContentPillar::InfoLowongan,
            'views_h30' => 2000,
            'likes' => 50,
        ]);

        $this->assertEquals(DataStatus::PendingH30, $pendingLog->data_status);
        $this->assertEquals(DataStatus::Completed, $completedLog->data_status);
    }

    public function test_query_scopes_work_correctly(): void
    {
        ContentLog::factory()->create([
            'post_date' => '2026-01-15',
            'pillar' => ContentPillar::InfoLowongan,
            'content_type' => ContentType::Feed,
            'data_status' => DataStatus::Completed,
            'views_h30' => 1000,
        ]);

        ContentLog::factory()->create([
            'post_date' => '2026-02-10',
            'pillar' => ContentPillar::EmployerBranding,
            'content_type' => ContentType::Reels,
            'data_status' => DataStatus::PendingH30,
            'views_h30' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares_reposts' => 0,
            'saves' => 0,
            'h30_due_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->assertCount(1, ContentLog::completed()->get());
        $this->assertCount(1, ContentLog::pendingH30()->get());
        $this->assertCount(1, ContentLog::overdueH30()->get());
        $this->assertCount(2, ContentLog::byYear(2026)->get());
        $this->assertCount(1, ContentLog::byMonth(1, 2026)->get());
        $this->assertCount(1, ContentLog::byPillar(ContentPillar::InfoLowongan)->get());
        $this->assertCount(1, ContentLog::byContentType(ContentType::Reels)->get());
    }
}
