<?php

namespace App\Http\Controllers;

use App\Models\ContentLog;
use App\Services\ActivityLogger;
use App\Services\ExcelImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ContentImportExportController extends Controller
{
    public function __construct(public ExcelImportService $importService) {}

    /**
     * Upload dan import file Excel (.xlsx).
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,zip|max:20480',
        ]);

        $file = $request->file('excel_file');
        if (! $file || ! $file->isValid()) {
            return back()->with('error', 'File Excel tidak valid atau gagal diunggah.');
        }

        try {
            $result = $this->importService->import($file->getRealPath());

            ActivityLogger::log(
                action: 'excel_import',
                module: 'Integrasi Data',
                description: "Import Excel berhasil: {$result['imported']} data baru ditambahkan, {$result['updated']} data diperbarui.",
                properties: ['imported' => $result['imported'], 'updated' => $result['updated'], 'filename' => $file->getClientOriginalName()]
            );

            $message = "Import berhasil: {$result['imported']} data baru ditambahkan, {$result['updated']} data diperbarui.";
            if (! empty($result['errors'])) {
                $message .= ' ('.count($result['errors']).' peringatan/error).';
            }

            return back()->with('success', $message);
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
    }

    /**
     * Import otomatis dari file master rekap bawaan (public/data).
     */
    public function importOriginal(): RedirectResponse
    {
        $filePath = public_path('data/Copy_of_Master_Rekap_Konten_Dharma_Group_Copy.xlsx');

        try {
            $result = $this->importService->import($filePath);

            ActivityLogger::log(
                action: 'excel_import',
                module: 'Integrasi Data',
                description: "Sinkronisasi master data Excel otomatis: {$result['imported']} data baru, {$result['updated']} data diperbarui.",
                properties: ['imported' => $result['imported'], 'updated' => $result['updated'], 'file' => 'Master_Rekap_Konten_Dharma_Group.xlsx']
            );

            return back()->with(
                'success',
                "Sinkronisasi master data Excel selesai: {$result['imported']} data baru, {$result['updated']} data diperbarui."
            );
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memproses file: '.$e->getMessage());
        }
    }

    /**
     * Export seluruh content logs ke format CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'Dharma_Group_Content_Logs_'.now()->format('Ymd_His').'.csv';

        ActivityLogger::log(
            action: 'csv_export',
            module: 'Integrasi Data',
            description: 'Mengunduh rekapitulasi data log konten ke file CSV.',
            properties: ['filename' => $fileName]
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // BOM untuk UTF-8 Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header kolom
            fputcsv($handle, [
                'ID',
                'Tanggal Posting',
                'Tahun',
                'Bulan',
                'Platform',
                'Jenis Konten',
                'Judul / Topik',
                'Pilar Konten',
                'PIC',
                'Link Evidence',
                'Status Evidence',
                'Tgl Jatuh Tempo H+30',
                'Views H+30',
                'Viewers H+30',
                'Likes',
                'Comments',
                'Shares / Reposts',
                'Saves',
                'Follows',
                'Total Interaksi',
                'Engagement Rate (%)',
                'Status Data',
                'Catatan',
            ]);

            ContentLog::orderBy('post_date', 'asc')->chunk(100, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->post_date ? $log->post_date->format('Y-m-d') : '',
                        $log->post_date ? $log->post_date->format('Y') : '',
                        $log->post_date ? $log->post_date->translatedFormat('F') : '',
                        $log->platform,
                        $log->content_type?->value ?? '',
                        $log->title,
                        $log->pillar?->value ?? '',
                        $log->pic_name ?? '',
                        $log->evidence_url ?? '',
                        $log->evidence_status?->value ?? '',
                        $log->h30_due_date ? $log->h30_due_date->format('Y-m-d') : '',
                        $log->views_h30,
                        $log->viewers_h30,
                        $log->likes,
                        $log->comments,
                        $log->shares_reposts,
                        $log->saves,
                        $log->follows,
                        $log->total_interactions,
                        $log->engagement_rate,
                        $log->data_status?->value ?? '',
                        $log->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
