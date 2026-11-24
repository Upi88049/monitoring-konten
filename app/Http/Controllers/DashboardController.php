<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Enums\DataStatus;
use App\Models\ContentLog;
use App\Models\Pillar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama KPI Dashboard (default: Sub Menu Overview & Performa).
     */
    public function index(Request $request): View
    {
        return $this->overview($request);
    }

    /**
     * Sub Menu 1: Performa & Tren Konten (KPI 1, KPI 2, Top & Low Views/ER).
     */
    public function overview(Request $request): View
    {
        return view('dashboard.overview', $this->getDashboardData($request));
    }

    /**
     * Sub Menu 2: Komposisi & Efektivitas Konten (KPI 3, KPI 4, Modul 3 Pilar, Format).
     */
    public function composition(Request $request): View
    {
        return view('dashboard.composition', $this->getDashboardData($request));
    }

    /**
     * Ambil dan hitung seluruh data metrik analitik dashboard.
     *
     * @return array<string, mixed>
     */
    private function getDashboardData(Request $request): array
    {
        // 1. Ambil daftar tahun unik dari data secara database-agnostic
        $availableYears = ContentLog::pluck('post_date')
            ->map(fn ($date) => $date ? (int) $date->format('Y') : null)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        $selectedYear = (int) $request->input('year', 2026);
        $selectedMonth = $request->filled('month') ? (int) $request->input('month') : null;
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = null;
        }
        $selectedPillar = $request->input('pillar');
        $selectedContentType = $request->input('content_type');

        // 2. Query dasar dengan filter
        $query = ContentLog::whereYear('post_date', $selectedYear);

        if ($selectedMonth !== null) {
            $query->whereMonth('post_date', $selectedMonth);
        }

        if (! empty($selectedPillar)) {
            $query->where('pillar', $selectedPillar);
        }

        if (! empty($selectedContentType)) {
            $query->where('content_type', $selectedContentType);
        }

        $allContents = (clone $query)->orderBy('post_date', 'asc')->get();
        $completedContents = $allContents->where('data_status', DataStatus::Completed);
        $pendingContents = $allContents->where('data_status', DataStatus::PendingH30);

        // 3. KPI Summary Cards (terfilter sesuai tahun DAN bulan jika dipilih)
        $totalContents = $allContents->count();
        $totalCompleted = $completedContents->count();
        $totalPending = $pendingContents->count();

        $totalViewsH30 = $completedContents->sum('views_h30');
        $avgViewsPerContent = $totalCompleted > 0 ? round($totalViewsH30 / $totalCompleted) : 0;

        $totalLikes = $completedContents->sum('likes');
        $totalComments = $completedContents->sum('comments');
        $totalShares = $completedContents->sum('shares_reposts');
        $totalSaves = $completedContents->sum('saves');
        $totalEngagement = $totalLikes + $totalComments + $totalShares + $totalSaves;

        $totalFollows = $completedContents->sum('follows');

        // Average ER per content & Aggregate ER
        $avgEngagementRate = $totalCompleted > 0 ? round($completedContents->avg('engagement_rate'), 2) : 0.00;
        $aggregateER = $totalViewsH30 > 0 ? round(($totalEngagement / $totalViewsH30) * 100, 2) : 0.00;
        $benchmarkER = 1.72; // Benchmark tahunan Dharma Group (1.72%)

        // 4. Data 4 KPI Utama per Bulan (Jan–Des)
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $indoMonthFullNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Ambil data tahunan untuk komparasi drilldown
        $yearlyQuery = ContentLog::whereYear('post_date', $selectedYear);
        if (! empty($selectedPillar)) {
            $yearlyQuery->where('pillar', $selectedPillar);
        }
        if (! empty($selectedContentType)) {
            $yearlyQuery->where('content_type', $selectedContentType);
        }
        $yearlyCompleted = (clone $yearlyQuery)->where('data_status', DataStatus::Completed)->orderBy('post_date', 'asc')->get();

        $monthlyEngagement = array_fill(0, 12, 0);
        $monthlyViews = array_fill(0, 12, 0);
        $monthlyEvaluatedCount = array_fill(0, 12, 0);
        $monthlyAggregateER = array_fill(0, 12, 0);
        $monthlyAverageER = array_fill(0, 12, 0);

        $monthlyLikes = array_fill(0, 12, 0);
        $monthlyShares = array_fill(0, 12, 0);
        $monthlySaves = array_fill(0, 12, 0);
        $monthlyComments = array_fill(0, 12, 0);

        $monthlyContentsDrilldown = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyContentsDrilldown[$m] = [];
        }

        // Kelompokkan data tahunan per bulan
        for ($m = 1; $m <= 12; $m++) {
            $monthCompleted = $yearlyCompleted->filter(function ($item) use ($m) {
                return (int) $item->post_date?->format('n') === $m;
            });

            $mIdx = $m - 1;
            $count = $monthCompleted->count();
            $monthlyEvaluatedCount[$mIdx] = $count;

            if ($count > 0) {
                $mViews = $monthCompleted->sum('views_h30');
                $mLikes = $monthCompleted->sum('likes');
                $mComments = $monthCompleted->sum('comments');
                $mShares = $monthCompleted->sum('shares_reposts');
                $mSaves = $monthCompleted->sum('saves');
                $mInteractions = $mLikes + $mComments + $mShares + $mSaves;

                $monthlyViews[$mIdx] = $mViews;
                $monthlyEngagement[$mIdx] = $mInteractions;
                $monthlyLikes[$mIdx] = $mLikes;
                $monthlyComments[$mIdx] = $mComments;
                $monthlyShares[$mIdx] = $mShares;
                $monthlySaves[$mIdx] = $mSaves;

                $monthlyAverageER[$mIdx] = round($monthCompleted->avg('engagement_rate'), 2);
                $monthlyAggregateER[$mIdx] = $mViews > 0 ? round(($mInteractions / $mViews) * 100, 2) : 0;

                $monthlyContentsDrilldown[$m] = $monthCompleted->map(function ($c) {
                    return [
                        'title' => $c->title,
                        'pillar' => is_object($c->pillar) && property_exists($c->pillar, 'value') ? $c->pillar->value : (string) $c->pillar,
                        'type' => $c->content_type?->value ?? '',
                        'interactions' => $c->total_interactions,
                        'views' => $c->views_h30,
                        'er' => (float) $c->engagement_rate,
                    ];
                })->values()->toArray();
            }
        }

        // Data Chart Responsif terhadap Filter Bulan
        if ($selectedMonth === null) {
            $chartMode = 'yearly';
            $chartCategories = $monthNames;
            $chartEngagement = $monthlyEngagement;
            $chartViews = $monthlyViews;
            $chartAggregateER = $monthlyAggregateER;
            $chartAverageER = $monthlyAverageER;
            $chartBenchmark = array_fill(0, 12, $benchmarkER);
            $chartSubtitleKPI1 = 'Total Interaksi (Bar) vs Total Tayangan H+30 (Line) Seluruh Bulan';
            $chartSubtitleKPI2 = 'Perbandingan Monthly Aggregate ER vs Rata-rata per Konten vs Benchmark';
        } else {
            $chartMode = 'monthly';
            $selectedMonthName = $indoMonthFullNames[$selectedMonth] ?? 'Bulan '.$selectedMonth;
            if ($completedContents->isEmpty()) {
                $chartCategories = ['Belum ada postingan'];
                $chartEngagement = [0];
                $chartViews = [0];
                $chartAggregateER = [0];
                $chartAverageER = [0];
                $chartBenchmark = [$benchmarkER];
            } else {
                $chartCategories = $completedContents->map(function ($c) {
                    return $c->post_date?->format('d/m').' '.Str::limit($c->title, 18);
                })->values()->toArray();
                $chartEngagement = $completedContents->pluck('total_interactions')->map(fn ($v) => (int) $v)->values()->toArray();
                $chartViews = $completedContents->pluck('views_h30')->map(fn ($v) => (int) $v)->values()->toArray();
                $chartAggregateER = $completedContents->map(fn ($c) => (float) $c->engagement_rate)->values()->toArray();
                $chartAverageER = $chartAggregateER;
                $chartBenchmark = array_fill(0, count($chartCategories), $benchmarkER);
            }
            $chartSubtitleKPI1 = "Performa per Konten Bulan {$selectedMonthName} {$selectedYear}";
            $chartSubtitleKPI2 = "Engagement Rate per Konten Bulan {$selectedMonthName} {$selectedYear} vs Benchmark ({$benchmarkER}%)";
        }

        // 5. KPI 4: Top & Low Performance (terfilter sesuai selectedMonth)
        $poolForTop = $completedContents;
        $hasPoolData = $poolForTop->isNotEmpty();
        $topViews = (clone $poolForTop)->sortByDesc('views_h30')->first();
        $topLikes = (clone $poolForTop)->sortByDesc('likes')->first();
        $topComments = (clone $poolForTop)->sortByDesc('comments')->first();
        $topShares = (clone $poolForTop)->sortByDesc('shares_reposts')->first();
        $topSaves = (clone $poolForTop)->sortByDesc('saves')->first();
        $topER = (clone $poolForTop)->sortByDesc('engagement_rate')->first();

        // Low Performance (Lowest)
        $lowViews = (clone $poolForTop)->sortBy('views_h30')->first();
        $lowLikes = (clone $poolForTop)->sortBy('likes')->first();
        $lowComments = (clone $poolForTop)->sortBy('comments')->first();
        $lowShares = (clone $poolForTop)->sortBy('shares_reposts')->first();
        $lowSaves = (clone $poolForTop)->sortBy('saves')->first();
        $lowER = (clone $poolForTop)->sortBy('engagement_rate')->first();

        // 6. Secondary Insights (Pilar & Format) - Menggunakan database dinamis
        $allPillars = Pillar::names();
        $pillarInsights = [];
        foreach ($allPillars as $pName) {
            $pillarLogs = $completedContents->filter(function ($c) use ($pName) {
                $val = is_object($c->pillar) && property_exists($c->pillar, 'value') ? $c->pillar->value : (string) $c->pillar;

                return $val === $pName;
            });
            $pCount = $pillarLogs->count();
            $pViews = $pillarLogs->sum('views_h30');
            $pInteractions = $pillarLogs->sum('total_interactions');
            $pAvgER = $pCount > 0 ? round($pillarLogs->avg('engagement_rate'), 2) : 0;

            $pillarInsights[] = [
                'pillar' => $pName,
                'count' => $pCount,
                'total_views' => $pViews,
                'total_interactions' => $pInteractions,
                'avg_er' => $pAvgER,
            ];
        }

        // Pilar Tertinggi dan Terendah berdasarkan Average Engagement Rate
        $pillarsWithData = collect($pillarInsights)->filter(fn ($p) => $p['count'] > 0);
        $highestPillar = $pillarsWithData->sortByDesc('avg_er')->first();
        $lowestPillar = $pillarsWithData->sortBy('avg_er')->first();

        $typeInsights = [];
        foreach (ContentType::cases() as $typeCase) {
            $typeLogs = $completedContents->where('content_type', $typeCase);
            $tCount = $typeLogs->count();
            $tViews = $typeLogs->sum('views_h30');
            $tAvgER = $tCount > 0 ? round($typeLogs->avg('engagement_rate'), 2) : 0;

            $typeInsights[] = [
                'type' => $typeCase->value,
                'count' => $tCount,
                'total_views' => $tViews,
                'avg_er' => $tAvgER,
            ];
        }

        return compact(
            'availableYears',
            'selectedYear',
            'selectedPillar',
            'selectedContentType',
            'selectedMonth',
            'totalContents',
            'totalCompleted',
            'totalPending',
            'totalViewsH30',
            'avgViewsPerContent',
            'totalEngagement',
            'totalLikes',
            'totalComments',
            'totalShares',
            'totalSaves',
            'totalFollows',
            'avgEngagementRate',
            'aggregateER',
            'benchmarkER',
            'monthNames',
            'monthlyEngagement',
            'monthlyViews',
            'monthlyEvaluatedCount',
            'monthlyAggregateER',
            'monthlyAverageER',
            'monthlyLikes',
            'monthlyShares',
            'monthlySaves',
            'monthlyComments',
            'monthlyContentsDrilldown',
            'hasPoolData',
            'topViews',
            'topLikes',
            'topComments',
            'topShares',
            'topSaves',
            'topER',
            'lowViews',
            'lowLikes',
            'lowComments',
            'lowShares',
            'lowSaves',
            'lowER',
            'highestPillar',
            'lowestPillar',
            'pillarInsights',
            'typeInsights',
            'chartMode',
            'chartCategories',
            'chartEngagement',
            'chartViews',
            'chartAggregateER',
            'chartAverageER',
            'chartBenchmark',
            'chartSubtitleKPI1',
            'chartSubtitleKPI2',
            'allPillars'
        );
    }
}
