<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use App\Models\Pillar;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Tampilkan kalender rencana dan postingan konten bulanan.
     */
    public function index(Request $request): View|JsonResponse
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        $year = (int) $request->input('year', $currentYear);
        $month = (int) $request->input('month', $currentMonth);

        if ($month < 1 || $month > 12) {
            $month = $currentMonth;
        }

        $selectedPillar = $request->input('pillar');
        $selectedContentType = $request->input('content_type');

        // Query konten pada bulan dan tahun terpilih
        $query = ContentLog::whereYear('post_date', $year)
            ->whereMonth('post_date', $month);

        if (! empty($selectedPillar)) {
            $query->where('pillar', $selectedPillar);
        }

        if (! empty($selectedContentType)) {
            $query->where('content_type', $selectedContentType);
        }

        $contents = $query->orderBy('post_date', 'asc')->orderBy('id', 'asc')->get();

        // Kelompokkan konten berdasarkan tanggal Y-m-d
        $contentsByDate = $contents->groupBy(function ($item) {
            return $item->post_date?->format('Y-m-d');
        });

        // Hitung struktur grid kalender
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Grid kalender Senin - Minggu
        $startGrid = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endGrid = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $calendarDays = [];
        $cursor = $startGrid->copy();

        while ($cursor->lte($endGrid)) {
            $dateString = $cursor->format('Y-m-d');
            $dayContents = $contentsByDate->get($dateString, collect());

            $calendarDays[] = [
                'date' => $cursor->copy(),
                'dateString' => $dateString,
                'dayNumber' => $cursor->day,
                'isCurrentMonth' => $cursor->month === $month,
                'isToday' => $cursor->isToday(),
                'isPast' => $cursor->isPast() && ! $cursor->isToday(),
                'contents' => $dayContents,
            ];

            $cursor->addDay();
        }

        // Statistik bulanan kalender
        $totalMonthContents = $contents->count();
        $completedCount = $contents->where('data_status', DataStatus::Completed)->count();
        $pendingCount = $contents->where('data_status', DataStatus::PendingH30)->count();

        // Navigasi bulan
        $prevMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        $availableYears = ContentLog::pluck('post_date')
            ->map(fn ($date) => $date ? (int) $date->format('Y') : null)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        }

        if (! in_array($currentYear, $availableYears, true)) {
            $availableYears[] = $currentYear;
            rsort($availableYears);
        }

        $allPillars = Pillar::names();
        $contentTypes = ContentType::cases();

        $indoMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'year' => $year,
                'month' => $month,
                'total' => $totalMonthContents,
                'contents' => $contents,
            ]);
        }

        $viewData = compact(
            'year',
            'month',
            'startOfMonth',
            'endOfMonth',
            'prevMonth',
            'nextMonth',
            'calendarDays',
            'totalMonthContents',
            'completedCount',
            'pendingCount',
            'availableYears',
            'allPillars',
            'contentTypes',
            'selectedPillar',
            'selectedContentType',
            'indoMonths'
        );

        if ($request->ajax()) {
            return view('calendar.partials.calendar-view', $viewData);
        }

        return view('calendar.index', $viewData);
    }
}
