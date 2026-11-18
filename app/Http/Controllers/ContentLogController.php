<?php

namespace App\Http\Controllers;

use App\Enums\ContentPillar;
use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ContentLogController extends Controller
{
    /**
     * Tampilkan daftar Master Log Konten dengan pencarian dan filter.
     */
    public function index(Request $request): View
    {
        $query = ContentLog::query();

        // Pencarian judul / PIC
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        // Filter status H+30
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'completed') {
                $query->completed();
            } elseif ($status === 'pending') {
                $query->pendingH30();
            } elseif ($status === 'overdue') {
                $query->overdueH30();
            }
        }

        // Filter Pilar
        if ($request->filled('pillar')) {
            $query->byPillar($request->input('pillar'));
        }

        // Filter Format Konten
        if ($request->filled('content_type')) {
            $query->byContentType($request->input('content_type'));
        }

        // Filter Bulan
        if ($request->filled('month')) {
            $query->whereMonth('post_date', (int) $request->input('month'));
        }

        // Filter Tahun
        if ($request->filled('year')) {
            $query->byYear((int) $request->input('year'));
        }

        $allowedPerPage = [15, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $logs = $query->orderBy('post_date', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('content-logs.partials.table', compact('logs', 'perPage'));
        }

        $pillars = ContentPillar::cases();
        $contentTypes = ContentType::cases();

        return view('content-logs.index', compact('logs', 'pillars', 'contentTypes', 'perPage'));
    }

    /**
     * Simpan konten baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'post_date' => 'required|date',
            'content_type' => ['required', new Enum(ContentType::class)],
            'title' => 'required|string|max:255',
            'pillar' => ['required', new Enum(ContentPillar::class)],
            'pic_name' => 'nullable|string|max:100',
            'evidence_url' => 'nullable|url|max:1000',
            'notes' => 'nullable|string',
        ]);

        $content = ContentLog::create($validated);

        ActivityLogger::log(
            action: 'content_create',
            module: 'Master Log Konten',
            description: "Menambahkan konten baru: '{$content->title}' ({$content->content_type?->value}, {$content->pillar?->value}).",
            properties: ['content_id' => $content->id, 'title' => $content->title]
        );

        return redirect()->route('content-logs.index')
            ->with('success', 'Konten baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data identitas konten.
     */
    public function update(Request $request, ContentLog $contentLog): RedirectResponse
    {
        $validated = $request->validate([
            'post_date' => 'required|date',
            'content_type' => ['required', new Enum(ContentType::class)],
            'title' => 'required|string|max:255',
            'pillar' => ['required', new Enum(ContentPillar::class)],
            'pic_name' => 'nullable|string|max:100',
            'evidence_url' => 'nullable|url|max:1000',
            'notes' => 'nullable|string',
        ]);

        $contentLog->update($validated);

        ActivityLogger::log(
            action: 'content_update',
            module: 'Master Log Konten',
            description: "Memperbarui informasi konten: '{$contentLog->title}'.",
            properties: ['content_id' => $contentLog->id, 'title' => $contentLog->title]
        );

        return redirect()->route('content-logs.index')
            ->with('success', 'Data konten berhasil diperbarui.');
    }

    /**
     * Input atau perbarui metrik evaluasi H+30.
     */
    public function updateMetrics(Request $request, ContentLog $contentLog): RedirectResponse
    {
        $validated = $request->validate([
            'views_h30' => 'required|integer|min:0',
            'viewers_h30' => 'nullable|integer|min:0',
            'likes' => 'required|integer|min:0',
            'comments' => 'required|integer|min:0',
            'shares_reposts' => 'required|integer|min:0',
            'saves' => 'required|integer|min:0',
            'follows' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['data_status'] = DataStatus::Completed;
        $contentLog->update($validated);

        ActivityLogger::log(
            action: 'content_metrics_update',
            module: 'Master Log Konten',
            description: "Mengisi metrik H+30 untuk konten '{$contentLog->title}': ".number_format($validated['views_h30']).' views, ER '.number_format((float) $contentLog->engagement_rate, 2).'%.',
            properties: ['content_id' => $contentLog->id, 'title' => $contentLog->title, 'views_h30' => $validated['views_h30'], 'er' => $contentLog->engagement_rate]
        );

        return redirect()->route('content-logs.index')
            ->with('success', "Metrik H+30 untuk '{$contentLog->title}' berhasil disimpan.");
    }

    /**
     * Hapus data konten.
     */
    public function destroy(ContentLog $contentLog): RedirectResponse
    {
        $title = $contentLog->title;
        $id = $contentLog->id;
        $contentLog->delete();

        ActivityLogger::log(
            action: 'content_delete',
            module: 'Master Log Konten',
            description: "Menghapus konten: '{$title}'.",
            properties: ['content_id' => $id, 'title' => $title]
        );

        return redirect()->route('content-logs.index')
            ->with('success', "Konten '{$title}' berhasil dihapus.");
    }
}
