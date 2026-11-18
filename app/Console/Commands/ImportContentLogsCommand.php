<?php

namespace App\Console\Commands;

use App\Services\ExcelImportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('content:import {file? : Path relatif atau absolut ke file Excel (.xlsx)}')]
#[Description('Import data riwayat konten Instagram dari file Excel Master Rekap')]
class ImportContentLogsCommand extends Command
{
    public function __construct(public ExcelImportService $importService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! $filePath) {
            $filePath = public_path('data/Copy_of_Master_Rekap_Konten_Dharma_Group_Copy.xlsx');
        } elseif (! file_exists($filePath)) {
            $filePath = base_path($filePath);
        }

        $this->info("Memulai proses import dari: {$filePath}");

        try {
            $result = $this->importService->import($filePath);

            $this->table(
                ['Kategori', 'Jumlah'],
                [
                    ['Total Konten Ditemukan', $result['total_found']],
                    ['Berhasil Ditambahkan (Baru)', $result['imported']],
                    ['Berhasil Diperbarui (Update)', $result['updated']],
                    ['Gagal / Error', count($result['errors'])],
                ]
            );

            if (! empty($result['errors'])) {
                $this->warn('Daftar kendala saat import:');
                foreach ($result['errors'] as $error) {
                    $this->error(" - {$error}");
                }
            }

            $this->info('✓ Proses import data Excel selesai.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Gagal menjalankan import: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
