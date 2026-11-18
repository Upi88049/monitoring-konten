<?php

namespace App\Services;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use Exception;
use SimpleXMLElement;
use ZipArchive;

class ExcelImportService
{
    /**
     * Parse and import content logs from an Excel (.xlsx) file.
     *
     * @param  string  $filePath  Absolute path to the .xlsx file.
     * @return array{total_found: int, imported: int, updated: int, errors: array<string>}
     *
     * @throws Exception
     */
    public function import(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("File Excel tidak ditemukan pada: {$filePath}");
        }

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            throw new Exception('Gagal membuka file Excel sebagai arsip zip.');
        }

        // 1. Baca shared strings
        $sharedStrings = $this->extractSharedStrings($zip);

        // 2. Tentukan file worksheet yang memuat 'Log Konten'
        $sheetXmlContent = $this->extractTargetSheetXml($zip);
        $zip->close();

        if (empty($sheetXmlContent)) {
            throw new Exception("Lembar kerja 'Log Konten' tidak ditemukan di dalam file Excel.");
        }

        // 3. Parse XML worksheet
        return $this->parseSheetXml($sheetXmlContent, $sharedStrings);
    }

    /**
     * Ekstrak daftar string bersama (shared strings).
     *
     * @return list<string>
     */
    protected function extractSharedStrings(ZipArchive $zip): array
    {
        $strings = [];
        $xmlContent = $zip->getFromName('xl/sharedStrings.xml');

        if ($xmlContent === false) {
            return $strings;
        }

        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            return $strings;
        }

        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
            } elseif (isset($si->r)) {
                $textParts = [];
                foreach ($si->r as $run) {
                    $textParts[] = (string) $run->t;
                }
                $strings[] = implode('', $textParts);
            } else {
                $strings[] = '';
            }
        }

        return $strings;
    }

    /**
     * Cari dan ekstrak konten XML dari worksheet Log Konten.
     */
    protected function extractTargetSheetXml(ZipArchive $zip): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        $targetSheetFile = null;

        if ($workbookXml !== false && $relsXml !== false) {
            $wb = simplexml_load_string($workbookXml);
            $rels = simplexml_load_string($relsXml);

            $sheetRelMap = [];
            if ($wb && isset($wb->sheets)) {
                foreach ($wb->sheets->sheet as $sheet) {
                    $sheetName = (string) $sheet['name'];
                    $rId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

                    if (stripos($sheetName, 'Log Konten') !== false) {
                        $targetSheetFile = $this->resolveTargetSheetPath($rels, $rId);
                        break;
                    }
                }
            }
        }

        if ($targetSheetFile === null) {
            // Coba rId3 / worksheets/sheet3.xml secara langsung
            $targetSheetFile = 'xl/worksheets/sheet3.xml';
        }

        $content = $zip->getFromName($targetSheetFile);
        if ($content === false) {
            // Coba sheet1 jika sheet3 tidak ada
            $content = $zip->getFromName('xl/worksheets/sheet1.xml');
        }

        return $content !== false ? $content : null;
    }

    /**
     * Resolusi path relatif worksheet dari relationships XML.
     */
    protected function resolveTargetSheetPath(SimpleXMLElement $rels, string $rId): ?string
    {
        foreach ($rels->Relationship as $rel) {
            if ((string) $rel['Id'] === $rId) {
                $target = (string) $rel['Target'];
                if (! str_starts_with($target, 'xl/')) {
                    $target = 'xl/'.ltrim($target, '/');
                }

                return $target;
            }
        }

        return null;
    }

    /**
     * Parse XML baris worksheet dan petakan ke model ContentLog.
     *
     * @param  list<string>  $sharedStrings
     * @return array{total_found: int, imported: int, updated: int, errors: array<string>}
     */
    protected function parseSheetXml(string $xmlContent, array $sharedStrings): array
    {
        $sheetXml = simplexml_load_string($xmlContent);
        if ($sheetXml === false || ! isset($sheetXml->sheetData)) {
            return ['total_found' => 0, 'imported' => 0, 'updated' => 0, 'errors' => ['Gagal memproses sheetData XML.']];
        }

        $totalFound = 0;
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($sheetXml->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            if ($rowNum < 5) {
                continue; // Lewati header
            }

            $cells = [];
            foreach ($row->c as $c) {
                $cellRef = (string) $c['r'];
                $col = preg_replace('/\d+/', '', $cellRef);
                $isString = ((string) $c['t']) === 's';
                $val = isset($c->v) ? (string) $c->v : '';

                if ($isString && $val !== '' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }

                $cells[$col] = trim($val);
            }

            // Jika baris kosong atau tidak memiliki Judul / Tanggal, lewati
            $title = $cells['G'] ?? '';
            $rawDate = $cells['B'] ?? '';
            if (empty($title) && empty($rawDate)) {
                continue;
            }

            $totalFound++;

            try {
                $postDate = $this->parseExcelDate($rawDate);
                $contentType = ContentType::tryFromLoose($cells['F'] ?? null) ?? ContentType::Feed;
                $pillar = ContentPillar::tryFromLoose($cells['H'] ?? null) ?? ContentPillar::InternalCulture;

                $platform = ! empty($cells['E']) ? $cells['E'] : 'Instagram';
                $picName = ! empty($cells['I']) ? $cells['I'] : null;
                $evidenceUrl = ! empty($cells['J']) ? $cells['J'] : null;

                $viewsH30 = isset($cells['M']) && is_numeric($cells['M']) ? (int) $cells['M'] : 0;
                $viewersH30 = isset($cells['N']) && is_numeric($cells['N']) ? (int) $cells['N'] : 0;
                $likes = isset($cells['O']) && is_numeric($cells['O']) ? (int) $cells['O'] : 0;
                $comments = isset($cells['P']) && is_numeric($cells['P']) ? (int) $cells['P'] : 0;
                $shares = isset($cells['Q']) && is_numeric($cells['Q']) ? (int) $cells['Q'] : 0;
                $saves = isset($cells['R']) && is_numeric($cells['R']) ? (int) $cells['R'] : 0;
                $follows = isset($cells['T']) && is_numeric($cells['T']) ? (int) $cells['T'] : 0;
                $notes = ! empty($cells['W']) ? $cells['W'] : null;

                // Cek apakah data status disediakan
                $rawStatus = $cells['V'] ?? '';
                $dataStatus = null;
                if ($rawStatus === 'Sudah diisi') {
                    $dataStatus = DataStatus::Completed;
                } elseif ($rawStatus === 'Menunggu H+30') {
                    $dataStatus = DataStatus::PendingH30;
                }

                $attributes = [
                    'post_date' => $postDate,
                    'platform' => $platform,
                    'content_type' => $contentType,
                    'title' => $title,
                    'pillar' => $pillar,
                    'pic_name' => $picName,
                    'evidence_url' => $evidenceUrl,
                    'views_h30' => $viewsH30,
                    'viewers_h30' => $viewersH30,
                    'likes' => $likes,
                    'comments' => $comments,
                    'shares_reposts' => $shares,
                    'saves' => $saves,
                    'follows' => $follows,
                    'notes' => $notes,
                ];

                if ($dataStatus !== null) {
                    $attributes['data_status'] = $dataStatus;
                }

                // Cari berdasarkan judul dan tanggal untuk update atau buat baru
                $existing = ContentLog::where('title', $title)
                    ->whereDate('post_date', $postDate)
                    ->first();

                if ($existing) {
                    $existing->update($attributes);
                    $updated++;
                } else {
                    ContentLog::create($attributes);
                    $imported++;
                }
            } catch (Exception $e) {
                $errors[] = "Baris {$rowNum} ('{$title}'): ".$e->getMessage();
            }
        }

        return [
            'total_found' => $totalFound,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Konversi tanggal Excel (angka serial maupun string) ke format Y-m-d.
     */
    protected function parseExcelDate(string $rawDate): string
    {
        if (is_numeric($rawDate)) {
            $serial = (int) $rawDate;
            // Konversi nomor seri Excel 1900 ke UNIX timestamp: (serial - 25569) * 86400
            $timestamp = ($serial - 25569) * 86400;

            return gmdate('Y-m-d', $timestamp);
        }

        $time = strtotime($rawDate);
        if ($time !== false) {
            return date('Y-m-d', $time);
        }

        return now()->format('Y-m-d');
    }
}
