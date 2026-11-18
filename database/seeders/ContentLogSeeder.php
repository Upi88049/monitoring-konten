<?php

namespace Database\Seeders;

use App\Models\ContentLog;
use Illuminate\Database\Seeder;

class ContentLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Konten yang sudah selesai dievaluasi
        ContentLog::factory()->count(25)->create();

        // Konten yang masih menunggu H+30
        ContentLog::factory()->count(5)->pendingH30()->create();

        // Konten tanpa link evidence
        ContentLog::factory()->count(3)->withoutEvidence()->create();
    }
}
