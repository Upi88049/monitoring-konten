@extends('layouts.app')

@section('title', 'KPI Analytics Dashboard')
@section('page_title', 'KPI Analytics Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Global Filter Bar -->
    <div class="card p-4 sm:p-5">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center justify-between gap-4">
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
                    <a href="{{ route('dashboard') }}" class="text-xs text-gray-500 hover:text-purple-700 underline font-medium">Reset Filter</a>
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

    <!-- Core Charts Grid (2x2) -->
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

        <!-- KPI 3: Stacked Bar Komposisi Interaksi -->
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">KPI 3 — Komposisi Interaksi per Bulan</h3>
                    <p class="card-subtitle text-xs text-gray-500">Breakdown Like, Comment, Share, dan Save (Klik bulan untuk drill-down)</p>
                </div>
                <span class="text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-1 rounded-md">Stacked Bar</span>
            </div>
            <div id="chartStackedInteractions" class="echart-container mt-3"></div>
        </div>

        <!-- KPI 4: Top & Low Performance Champion Cards -->
        <div class="card p-5 flex flex-col justify-between">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">KPI 4 — Top & Low Performance</h3>
                    <p class="card-subtitle text-xs text-gray-500">Performa tertinggi & terendah per kategori metrik</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Toggle Tabs Top / Low -->
                    <div class="flex items-center bg-gray-100 p-0.5 rounded-lg text-xs font-semibold">
                        <button type="button" id="tabTopBtn" onclick="switchPerformanceTab('top')" class="px-2.5 py-1 rounded-md transition text-purple-700 bg-white shadow-xs font-bold">
                            🏆 Top
                        </button>
                        <button type="button" id="tabLowBtn" onclick="switchPerformanceTab('low')" class="px-2.5 py-1 rounded-md transition text-gray-500 hover:text-gray-800 font-medium">
                            🔻 Low
                        </button>
                    </div>

                    <!-- Month Filter -->
                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-1">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        @if($selectedPillar)<input type="hidden" name="pillar" value="{{ $selectedPillar }}">@endif
                        @if($selectedContentType)<input type="hidden" name="content_type" value="{{ $selectedContentType }}">@endif
                        <select name="month" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 font-semibold text-gray-700">
                            <option value="">Semua Bulan</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $selectedMonth === $i ? 'selected' : '' }}>{{ $monthNames[$i - 1] }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>

            <!-- TAB 1: 5 Top Performance Items -->
            <div id="topPerformanceList" class="mt-4 space-y-2.5 overflow-y-auto max-h-[300px] pr-1">
                <!-- Top Views -->
                <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs">
                            👁️
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-teal-800">Top Views (H+30)</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topViews?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $topViews?->post_date?->format('d M Y') }} &bull; {{ $topViews?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-teal-900">{{ number_format($topViews?->views_h30 ?? 0) }}</div>
                        <div class="text-[10px] text-teal-700 font-semibold">Tertinggi</div>
                    </div>
                </div>

                <!-- Top Likes -->
                <div class="p-3 rounded-xl bg-purple-50/60 border border-purple-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-xs">
                            ❤️
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-purple-800">Top Likes</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topLikes?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $topLikes?->post_date?->format('d M Y') }} &bull; {{ $topLikes?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-purple-900">{{ number_format($topLikes?->likes ?? 0) }}</div>
                        <div class="text-[10px] text-purple-700 font-semibold">Tertinggi</div>
                    </div>
                </div>

                <!-- Top Share / Repost -->
                <div class="p-3 rounded-xl bg-rose-50/60 border border-rose-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-600 text-white flex items-center justify-center font-bold text-xs">
                            ↗️
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-rose-800">Top Share / Repost</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topShares?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $topShares?->post_date?->format('d M Y') }} &bull; {{ $topShares?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-rose-900">{{ number_format($topShares?->shares_reposts ?? 0) }}</div>
                        <div class="text-[10px] text-rose-700 font-semibold">Tertinggi</div>
                    </div>
                </div>

                <!-- Top Save -->
                <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-xs">
                            💾
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-amber-800">Top Save</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topSaves?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $topSaves?->post_date?->format('d M Y') }} &bull; {{ $topSaves?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-amber-900">{{ number_format($topSaves?->saves ?? 0) }}</div>
                        <div class="text-[10px] text-amber-700 font-semibold">Tertinggi</div>
                    </div>
                </div>

                <!-- Top Engagement Rate -->
                <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                            ⚡
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-indigo-800">Top Engagement Rate</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topER?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $topER ? $topER->post_date?->format('d M Y') . ' • ' . number_format($topER->views_h30) . ' views' : '-' }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-indigo-900">{{ number_format((float) ($topER?->engagement_rate ?? 0), 2) }}%</div>
                        <div class="text-[10px] text-indigo-700 font-semibold">Tertinggi</div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: 5 Lowest Performance Items (Low) -->
            <div id="lowPerformanceList" class="mt-4 space-y-2.5 overflow-y-auto max-h-[300px] pr-1 hidden">
                <!-- Lowest Views -->
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                            👁️
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Views (H+30)</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowViews?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $lowViews?->post_date?->format('d M Y') }} &bull; {{ $lowViews?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-gray-800">{{ number_format($lowViews?->views_h30 ?? 0) }}</div>
                        <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                    </div>
                </div>

                <!-- Lowest Likes -->
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                            🤍
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Likes</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowLikes?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $lowLikes?->post_date?->format('d M Y') }} &bull; {{ $lowLikes?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-gray-800">{{ number_format($lowLikes?->likes ?? 0) }}</div>
                        <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                    </div>
                </div>

                <!-- Lowest Share / Repost -->
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                            ↘️
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Share / Repost</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowShares?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $lowShares?->post_date?->format('d M Y') }} &bull; {{ $lowShares?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-gray-800">{{ number_format($lowShares?->shares_reposts ?? 0) }}</div>
                        <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                    </div>
                </div>

                <!-- Lowest Save -->
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                            💾
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Save</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowSaves?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $lowSaves?->post_date?->format('d M Y') }} &bull; {{ $lowSaves?->pillar?->value }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-gray-800">{{ number_format($lowSaves?->saves ?? 0) }}</div>
                        <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                    </div>
                </div>

                <!-- Lowest Engagement Rate -->
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                            📉
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Engagement Rate</div>
                            <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowER?->title ?? 'Belum ada data' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $lowER ? $lowER->post_date?->format('d M Y') . ' • ' . number_format($lowER->views_h30) . ' views' : '-' }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-extrabold text-gray-800">{{ number_format((float) ($lowER?->engagement_rate ?? 0), 2) }}%</div>
                        <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Secondary Insights: Perbandingan Pilar & Format Konten (Modul 3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Komparasi Pilar Konten -->
        <div class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">Modul 3 — Analisa Efektivitas Pilar Konten</h3>
                    <p class="card-subtitle text-xs text-gray-500">Perbandingan performa views, interaksi, dan Engagement Rate antar pilar</p>
                </div>
                <span class="text-xs text-gray-500 font-medium">5 Pilar</span>
            </div>

            <!-- HIGHLIGHT: Pilar Tertinggi vs Pilar Terendah -->
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <!-- Pilar Tertinggi -->
                <div class="p-4 rounded-xl bg-green-50/70 border border-green-200 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-xs">
                        🏆
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-green-800 bg-green-200/60 px-2 py-0.5 rounded-full">
                                Pilar Tertinggi (Best ER)
                            </span>
                            <span class="text-base font-extrabold text-green-900">
                                {{ number_format($highestPillar['avg_er'] ?? 0, 2) }}% ER
                            </span>
                        </div>
                        <div class="text-sm font-bold text-gray-900 truncate">
                            {{ $highestPillar['pillar'] ?? 'Belum ada data' }}
                        </div>
                        <div class="mt-1 text-xs text-gray-600 flex items-center gap-2">
                            <span>{{ number_format($highestPillar['total_views'] ?? 0) }} Views</span> &bull;
                            <span>{{ number_format($highestPillar['total_interactions'] ?? 0) }} Interaksi</span> &bull;
                            <span>{{ $highestPillar['count'] ?? 0 }} Konten</span>
                        </div>
                    </div>
                </div>

                <!-- Pilar Terendah -->
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-base shrink-0 shadow-xs">
                        ⚠️
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 bg-amber-200/60 px-2 py-0.5 rounded-full">
                                Pilar Terendah (Lowest ER)
                            </span>
                            <span class="text-base font-extrabold text-amber-900">
                                {{ number_format($lowestPillar['avg_er'] ?? 0, 2) }}% ER
                            </span>
                        </div>
                        <div class="text-sm font-bold text-gray-900 truncate">
                            {{ $lowestPillar['pillar'] ?? 'Belum ada data' }}
                        </div>
                        <div class="mt-1 text-xs text-gray-600 flex items-center gap-2">
                            <span>{{ number_format($lowestPillar['total_views'] ?? 0) }} Views</span> &bull;
                            <span>{{ number_format($lowestPillar['total_interactions'] ?? 0) }} Interaksi</span> &bull;
                            <span>{{ $lowestPillar['count'] ?? 0 }} Konten</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Pilar Konten</th>
                            <th class="py-2.5 px-3 text-center">Jml Konten</th>
                            <th class="py-2.5 px-3 text-right">Total Views</th>
                            <th class="py-2.5 px-3 text-right">Total Interaksi</th>
                            <th class="py-2.5 px-3 text-right">Avg ER (%)</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pillarInsights as $pInsight)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3 px-3 font-bold text-gray-900">{{ $pInsight['pillar'] }}</td>
                                <td class="py-3 px-3 text-center font-semibold text-gray-700">{{ $pInsight['count'] }}</td>
                                <td class="py-3 px-3 text-right font-medium text-gray-800">{{ number_format($pInsight['total_views']) }}</td>
                                <td class="py-3 px-3 text-right font-medium text-gray-800">{{ number_format($pInsight['total_interactions']) }}</td>
                                <td class="py-3 px-3 text-right font-bold text-purple-700">{{ number_format($pInsight['avg_er'], 2) }}%</td>
                                <td class="py-3 px-3 text-center">
                                    @if($pInsight['avg_er'] >= $benchmarkER)
                                        <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Optimal</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-600 text-[10px] font-medium px-2 py-0.5 rounded-full">Perlu Boost</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Komparasi Format Konten -->
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">Performa per Format</h3>
                    <p class="card-subtitle text-xs text-gray-500">Reels vs Feed vs Carousel</p>
                </div>
            </div>
            <div class="mt-4 space-y-4">
                @foreach($typeInsights as $tInsight)
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-bold text-xs text-gray-900 flex items-center gap-1.5">
                                @if($tInsight['type'] === 'Reels') 🎬 @elseif($tInsight['type'] === 'Feed') 🖼️ @else 📑 @endif
                                {{ $tInsight['type'] }}
                            </span>
                            <span class="text-xs font-extrabold text-purple-700">{{ number_format($tInsight['avg_er'], 2) }}% ER</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <span>{{ $tInsight['count'] }} Postingan</span>
                            <span>{{ number_format($tInsight['total_views']) }} Total Views</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

<!-- Modal Drilldown Konten Bulanan -->
<div id="drilldownModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-gray-100 max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 id="drilldownTitle" class="text-base font-bold text-gray-900">Rincian Konten Bulan</h3>
                <p class="text-xs text-gray-500">Daftar postingan yang dievaluasi pada bulan tersebut</p>
            </div>
            <button type="button" onclick="document.getElementById('drilldownModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <div class="py-4 overflow-y-auto flex-1">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-600 font-bold uppercase">
                        <th class="py-2 px-2">Judul Konten</th>
                        <th class="py-2 px-2">Pilar</th>
                        <th class="py-2 px-2 text-right">Views</th>
                        <th class="py-2 px-2 text-right">Interaksi</th>
                        <th class="py-2 px-2 text-right">ER (%)</th>
                    </tr>
                </thead>
                <tbody id="drilldownTbody" class="divide-y divide-gray-100">
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthNames = @json($monthNames);
        const drilldownData = @json($monthlyContentsDrilldown);

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
                legend: { data: ['Monthly Aggregate ER (%)', 'Rata-rata ER per Konten (%)'], bottom: 0 },
                grid: { top: '12%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
                xAxis: { type: 'category', data: monthNames },
                yAxis: {
                    type: 'value',
                    axisLabel: { formatter: '{value}%' },
                    splitLine: { lineStyle: { color: '#f0f0f0' } }
                },
                series: [
                    {
                        name: 'Monthly Aggregate ER (%)',
                        type: 'line',
                        smooth: true,
                        itemStyle: { color: '#70b6c1' },
                        lineStyle: { width: 3 },
                        markLine: {
                            symbol: 'none',
                            data: [{ yAxis: {{ $benchmarkER }}, name: 'Benchmark (1.72%)', lineStyle: { color: '#f3cc5c', width: 2, type: 'dashed' } }]
                        },
                        data: @json($monthlyAggregateER)
                    },
                    {
                        name: 'Rata-rata ER per Konten (%)',
                        type: 'line',
                        smooth: true,
                        itemStyle: { color: '#775da6' },
                        lineStyle: { width: 3 },
                        data: @json($monthlyAverageER)
                    }
                ]
            });
            window.addEventListener('resize', () => chartLine.resize());
        }

        // 3. KPI 3: Stacked Bar Komposisi Interaksi
        const stackedEl = document.getElementById('chartStackedInteractions');
        if (stackedEl) {
            const chartStacked = echarts.init(stackedEl);
            chartStacked.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' }
                },
                legend: { data: ['Like', 'Share/Repost', 'Save', 'Comment'], bottom: 0 },
                grid: { top: '12%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
                xAxis: { type: 'category', data: monthNames },
                yAxis: { type: 'value', splitLine: { lineStyle: { color: '#f0f0f0' } } },
                series: [
                    { name: 'Like', type: 'bar', stack: 'total', itemStyle: { color: '#775da6' }, data: @json($monthlyLikes) },
                    { name: 'Share/Repost', type: 'bar', stack: 'total', itemStyle: { color: '#f9837c' }, data: @json($monthlyShares) },
                    { name: 'Save', type: 'bar', stack: 'total', itemStyle: { color: '#70b6c1' }, data: @json($monthlySaves) },
                    { name: 'Comment', type: 'bar', stack: 'total', itemStyle: { color: '#f3cc5c', borderRadius: [4, 4, 0, 0] }, data: @json($monthlyComments) }
                ]
            });

            // Drilldown handler on click
            chartStacked.on('click', function (params) {
                const monthIndex = params.dataIndex + 1;
                const monthName = monthNames[params.dataIndex];
                const list = drilldownData[monthIndex] || [];

                document.getElementById('drilldownTitle').innerText = 'Rincian Konten Bulan ' + monthName + ' ' + {{ $selectedYear }};
                const tbody = document.getElementById('drilldownTbody');
                tbody.innerHTML = '';

                function escapeHtml(str) {
                    if (!str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                if (list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-gray-500">Tidak ada konten terisi pada bulan ini.</td></tr>';
                } else {
                    list.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';
                        tr.innerHTML = '<td class="py-2.5 px-2 font-medium text-gray-900">' + escapeHtml(item.title) + '</td>'
                            + '<td class="py-2.5 px-2 text-gray-600">' + escapeHtml(item.pillar) + ' (' + escapeHtml(item.type) + ')</td>'
                            + '<td class="py-2.5 px-2 text-right">' + Number(item.views || 0).toLocaleString() + '</td>'
                            + '<td class="py-2.5 px-2 text-right font-semibold text-purple-700">' + Number(item.interactions || 0).toLocaleString() + '</td>'
                            + '<td class="py-2.5 px-2 text-right font-bold text-gray-900">' + Number(item.er || 0).toFixed(2) + '%</td>';
                        tbody.appendChild(tr);
                    });
                }
                document.getElementById('drilldownModal').classList.remove('hidden');
            });

            window.addEventListener('resize', () => chartStacked.resize());
        }
    });

    function switchPerformanceTab(tab) {
        const topList = document.getElementById('topPerformanceList');
        const lowList = document.getElementById('lowPerformanceList');
        const tabTopBtn = document.getElementById('tabTopBtn');
        const tabLowBtn = document.getElementById('tabLowBtn');

        if (tab === 'top') {
            topList.classList.remove('hidden');
            lowList.classList.add('hidden');
            tabTopBtn.className = 'px-2.5 py-1 rounded-md transition text-purple-700 bg-white shadow-xs font-bold';
            tabLowBtn.className = 'px-2.5 py-1 rounded-md transition text-gray-500 hover:text-gray-800 font-medium';
        } else {
            topList.classList.add('hidden');
            lowList.classList.remove('hidden');
            tabLowBtn.className = 'px-2.5 py-1 rounded-md transition text-gray-800 bg-white shadow-xs font-bold';
            tabTopBtn.className = 'px-2.5 py-1 rounded-md transition text-gray-500 hover:text-gray-800 font-medium';
        }
    }
</script>
@endpush
