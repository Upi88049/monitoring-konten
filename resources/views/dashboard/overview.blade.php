@extends('layouts.app')

@section('title', 'Performa & Tren Konten — KPI Dashboard')
@section('page_title', 'Performa & Tren Konten')

@section('content')
<div class="space-y-6">

    <!-- Global Filter Bar -->
    <div class="card p-4 sm:p-5">
        <form method="GET" action="{{ route('dashboard.overview') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter Global:
                </span>

                <!-- Tahun -->
                <div class="flex items-center gap-1.5">
                    <label for="filter_year" class="text-xs text-gray-600 font-medium">Tahun:</label>
                    <select id="filter_year" name="year" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-semibold text-gray-800 focus:ring-2 focus:ring-purple-500">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilar Konten -->
                <div class="flex items-center gap-1.5">
                    <label for="filter_pillar" class="text-xs text-gray-600 font-medium">Pilar:</label>
                    <select id="filter_pillar" name="pillar" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua Pilar</option>
                        @foreach(\App\Enums\ContentPillar::cases() as $pillarCase)
                            <option value="{{ $pillarCase->value }}" {{ $selectedPillar === $pillarCase->value ? 'selected' : '' }}>{{ $pillarCase->value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Jenis Konten -->
                <div class="flex items-center gap-1.5">
                    <label for="filter_type" class="text-xs text-gray-600 font-medium">Format:</label>
                    <select id="filter_type" name="content_type" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua Format</option>
                        @foreach(\App\Enums\ContentType::cases() as $typeCase)
                            <option value="{{ $typeCase->value }}" {{ $selectedContentType === $typeCase->value ? 'selected' : '' }}>{{ $typeCase->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($selectedPillar || $selectedContentType || $selectedYear != 2026)
                    <a href="{{ route('dashboard.overview') }}" class="text-xs text-gray-500 hover:text-purple-700 underline font-medium">Reset Filter</a>
                @endif
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white rounded-lg shadow-xs transition">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Summary Cards (5 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <!-- Card 1: Total Konten -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Konten</span>
                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($totalContents) }}</div>
                <div class="flex items-center gap-2 mt-2 text-xs">
                    <span class="inline-flex items-center gap-1 text-green-700 bg-green-50 px-2 py-0.5 rounded-md font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ $totalCompleted }} Dievaluasi
                    </span>
                    @if($totalPending > 0)
                        <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>{{ $totalPending }} Pending H30
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 2: Total Views H+30 -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Views (H+30)</span>
                <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($totalViewsH30) }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    Rata-rata: <strong class="text-gray-800">{{ number_format($avgViewsPerContent) }}</strong> / konten
                </div>
            </div>
        </div>

        <!-- Card 3: Total Engagement -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Engagement</span>
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($totalEngagement) }}</div>
                <div class="mt-2 text-xs text-gray-500 flex items-center gap-1.5 flex-wrap">
                    <span>{{ number_format($totalLikes) }} Likes</span> &bull;
                    <span>{{ number_format($totalComments) }} Komen</span> &bull;
                    <span>{{ number_format($totalShares) }} Share</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Average Engagement Rate -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Average ER (H+30)</span>
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($avgEngagementRate, 2) }}%</span>
                    @if($avgEngagementRate >= $benchmarkER)
                        <span class="text-xs font-bold text-green-700 bg-green-50 px-1.5 py-0.5 rounded">&uarr; Di atas Benchmark</span>
                    @else
                        <span class="text-xs font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">&darr; Di bawah</span>
                    @endif
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Target Benchmark: <strong class="text-gray-800">{{ $benchmarkER }}%</strong> &bull; Agg: <strong class="text-gray-800">{{ number_format($aggregateER, 2) }}%</strong>
                </div>
            </div>
        </div>

        <!-- Card 5: New Follows -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between text-gray-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">New Follows</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-900 tracking-tight">+{{ number_format($totalFollows) }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    Diperoleh dari performa postingan {{ $selectedYear }}
                </div>
            </div>
        </div>
    </div>

    <!-- Core Charts: KPI 1 & KPI 2 (Grid 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- KPI 1: Combo Chart Engagement & Views -->
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">KPI 1 — Engagement & Views H+30</h3>
                    <p class="card-subtitle text-xs text-gray-500">Total Interaksi (Bar) vs Total Tayangan H+30 (Line) Seluruh Bulan</p>
                </div>
                <span class="text-[11px] font-semibold text-purple-700 bg-purple-50 px-2 py-1 rounded-md">Combo Chart</span>
            </div>
            <div id="chartComboEngagement" class="echart-container mt-3"></div>
        </div>

        <!-- KPI 2: Multi-Line Chart Engagement Rate -->
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">KPI 2 — Tren Engagement Rate (ER %)</h3>
                    <p class="card-subtitle text-xs text-gray-500">Perbandingan Monthly Aggregate ER vs Rata-rata per Konten vs Benchmark</p>
                </div>
                <span class="text-[11px] font-semibold text-teal-700 bg-teal-50 px-2 py-1 rounded-md">Benchmark 1.72%</span>
            </div>
            <div id="chartMultiLineER" class="echart-container mt-3"></div>
        </div>

    </div>

    <!-- Highlight Ranking: Top Views, Top ER, Low Views, Low ER -->
    <div class="card p-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="card-title text-base font-bold text-gray-900">Ranking Performa Utama: Views & Engagement Rate</h3>
                <p class="card-subtitle text-xs text-gray-500">Postingan dengan capaian tertinggi (Top) dan terendah (Low) untuk evaluasi performa</p>
            </div>
            <span class="text-xs text-purple-700 font-semibold bg-purple-50 px-2.5 py-1 rounded-lg">Tahun {{ $selectedYear }}</span>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Top Views (H+30) -->
            <div class="p-4 rounded-xl bg-teal-50/70 border border-teal-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-800 bg-teal-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                            🏆 Top Views (H+30)
                        </span>
                        <span class="text-base font-extrabold text-teal-900">{{ number_format($topViews?->views_h30 ?? 0) }}</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 line-clamp-2" title="{{ $topViews?->title }}">
                        {{ $topViews?->title ?? 'Belum ada data' }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-teal-200/50 text-[11px] text-gray-500 flex items-center justify-between">
                    <span>{{ $topViews?->post_date?->format('d M Y') ?? '-' }}</span>
                    <span class="font-semibold text-teal-800">{{ $topViews?->pillar?->value ?? '-' }}</span>
                </div>
            </div>

            <!-- 2. Low Views (H+30) -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700 bg-gray-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                            🔻 Low Views (H+30)
                        </span>
                        <span class="text-base font-extrabold text-gray-800">{{ number_format($lowViews?->views_h30 ?? 0) }}</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 line-clamp-2" title="{{ $lowViews?->title }}">
                        {{ $lowViews?->title ?? 'Belum ada data' }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-gray-200 text-[11px] text-gray-500 flex items-center justify-between">
                    <span>{{ $lowViews?->post_date?->format('d M Y') ?? '-' }}</span>
                    <span class="font-semibold text-gray-700">{{ $lowViews?->pillar?->value ?? '-' }}</span>
                </div>
            </div>

            <!-- 3. Top Engagement Rate -->
            <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-800 bg-indigo-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
                            ⚡ Top Engagement Rate
                        </span>
                        <span class="text-base font-extrabold text-indigo-900">{{ number_format((float) ($topER?->engagement_rate ?? 0), 2) }}%</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 line-clamp-2" title="{{ $topER?->title }}">
                        {{ $topER?->title ?? 'Belum ada data' }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-indigo-200/50 text-[11px] text-gray-500 flex items-center justify-between">
                    <span>{{ $topER ? number_format($topER->views_h30) . ' views' : '-' }}</span>
                    <span class="font-semibold text-indigo-800">{{ $topER?->pillar?->value ?? '-' }}</span>
                </div>
            </div>

            <!-- 4. Low Engagement Rate -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700 bg-gray-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                            📉 Low Engagement Rate
                        </span>
                        <span class="text-base font-extrabold text-gray-800">{{ number_format((float) ($lowER?->engagement_rate ?? 0), 2) }}%</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 line-clamp-2" title="{{ $lowER?->title }}">
                        {{ $lowER?->title ?? 'Belum ada data' }}
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-gray-200 text-[11px] text-gray-500 flex items-center justify-between">
                    <span>{{ $lowER ? number_format($lowER->views_h30) . ' views' : '-' }}</span>
                    <span class="font-semibold text-gray-700">{{ $lowER?->pillar?->value ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthNames = @json($monthNames);

        // 1. KPI 1: Combo Chart (Engagement Bar + Views Line)
        const comboEl = document.getElementById('chartComboEngagement');
        if (comboEl) {
            const chartCombo = echarts.init(comboEl);
            chartCombo.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'cross' },
                    formatter: function (params) {
                        let res = '<div class="font-bold text-xs mb-1">' + params[0].name + ' ' + {{ $selectedYear }} + '</div>';
                        params.forEach(p => {
                            res += '<div class="flex items-center justify-between gap-3 text-xs">'
                                + '<span>' + p.marker + p.seriesName + ':</span>'
                                + '<strong>' + p.value.toLocaleString() + '</strong>'
                                + '</div>';
                        });
                        return res;
                    }
                },
                legend: { data: ['Total Engagement', 'Total Views H+30'], bottom: 0 },
                grid: { top: '12%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
                xAxis: [{ type: 'category', data: monthNames, axisTick: { alignWithLabel: true } }],
                yAxis: [
                    { type: 'value', name: 'Interaksi', position: 'left', splitLine: { lineStyle: { color: '#f0f0f0' } } },
                    { type: 'value', name: 'Views', position: 'right', splitLine: { show: false } }
                ],
                series: [
                    {
                        name: 'Total Engagement',
                        type: 'bar',
                        itemStyle: { color: '#775da6', borderRadius: [4, 4, 0, 0] },
                        data: @json($monthlyEngagement)
                    },
                    {
                        name: 'Total Views H+30',
                        type: 'line',
                        yAxisIndex: 1,
                        smooth: true,
                        itemStyle: { color: '#f9837c' },
                        lineStyle: { width: 3 },
                        data: @json($monthlyViews)
                    }
                ]
            });
            window.addEventListener('resize', () => chartCombo.resize());
        }

        // 2. KPI 2: Multi-Line Chart (Aggregate ER vs Average ER vs Benchmark)
        const lineEl = document.getElementById('chartMultiLineER');
        if (lineEl) {
            const chartLine = echarts.init(lineEl);
            chartLine.setOption({
                tooltip: {
                    trigger: 'axis',
                    formatter: function (params) {
                        let res = '<div class="font-bold text-xs mb-1">' + params[0].name + ' ' + {{ $selectedYear }} + '</div>';
                        params.forEach(p => {
                            res += '<div class="flex items-center justify-between gap-3 text-xs">'
                                + '<span>' + p.marker + p.seriesName + ':</span>'
                                + '<strong>' + p.value + '%</strong>'
                                + '</div>';
                        });
                        return res;
                    }
                },
                legend: { data: ['Monthly Aggregate ER', 'Average ER per Konten', 'Benchmark Dharma Group (1.72%)'], bottom: 0 },
                grid: { top: '12%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
                xAxis: [{ type: 'category', data: monthNames, axisTick: { alignWithLabel: true } }],
                yAxis: [{ type: 'value', name: 'ER (%)', splitLine: { lineStyle: { color: '#f0f0f0' } } }],
                series: [
                    {
                        name: 'Monthly Aggregate ER',
                        type: 'line',
                        smooth: true,
                        itemStyle: { color: '#775da6' },
                        lineStyle: { width: 3 },
                        data: @json($monthlyAggregateER)
                    },
                    {
                        name: 'Average ER per Konten',
                        type: 'line',
                        smooth: true,
                        itemStyle: { color: '#2dd4bf' },
                        lineStyle: { width: 2, type: 'dashed' },
                        data: @json($monthlyAverageER)
                    },
                    {
                        name: 'Benchmark Dharma Group (1.72%)',
                        type: 'line',
                        symbol: 'none',
                        lineStyle: { color: '#f43f5e', width: 2, type: 'dotted' },
                        data: Array(12).fill({{ $benchmarkER }})
                    }
                ]
            });
            window.addEventListener('resize', () => chartLine.resize());
        }
    });
</script>
@endpush
