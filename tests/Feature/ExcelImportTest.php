<?php

namespace Tests\Feature;

use App\Enums\ContentPillar;
use App\Models\ContentLog;
use App\Services\ExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_service_imports_all_44_records_from_master_file(): void
    {
        $filePath = public_path('data/Copy_of_Master_Rekap_Konten_Dharma_Group_Copy.xlsx');
        $this->assertFileExists($filePath);

        $service = app(ExcelImportService::class);
        $result = $service->import($filePath);

        $this->assertEquals(44, $result['total_found']);
        $this->assertEquals(44, $result['imported']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals(44, ContentLog::count());

        // Verifikasi sampel baris "Gen Z to Factory!"
        $log = ContentLog::where('title', 'Gen Z to Factory!')->first();
        $this->assertNotNull($log);
        $this->assertEquals(ContentPillar::EmployerBranding, $log->pillar);
        $this->assertEquals(10419, $log->views_h30);
        $this->assertEquals(170, $log->total_interactions);
        $this->assertEquals(1.6316, (float) $log->engagement_rate);
    }

    public function test_import_service_is_idempotent(): void
    {
        $filePath = public_path('data/Copy_of_Master_Rekap_Konten_Dharma_Group_Copy.xlsx');
        $service = app(ExcelImportService::class);

        $first = $service->import($filePath);
        $this->assertEquals(44, $first['imported']);

        $second = $service->import($filePath);
        $this->assertEquals(0, $second['imported']);
        $this->assertEquals(44, $second['updated']);
        $this->assertEquals(44, ContentLog::count());
    }

    public function test_artisan_command_imports_successfully(): void
    {
        $this->artisan('content:import')
            ->expectsOutputToContain('Total Konten Ditemukan')
            ->assertExitCode(0);

        $this->assertEquals(44, ContentLog::count());
    }
}
