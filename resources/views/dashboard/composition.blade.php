@extends('layouts.app')

@section('title', 'Komposisi & Efektivitas Konten — KPI Dashboard')
@section('page_title', 'Komposisi & Efektivitas Konten')

@section('content')
<div class="space-y-6">

    <!-- Global Filter Bar -->
    <div class="card p-4 sm:p-5">
        <form method="GET" action="{{ route('dashboard.composition') }}" class="flex flex-wrap items-center justify-between gap-4">
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

                <!-- Bulan -->
                <div class="flex items-center gap-1.5">
                    <label for="filter_month" class="text-xs text-gray-600 font-medium">Bulan:</label>
                    <select id="filter_month" name="month" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-semibold text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <option value="">Semua Bulan (Jan - Des)</option>
                        @php
                            $indoMonths = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach($indoMonths as $num => $mName)
                            <option value="{{ $num }}" {{ (int) $selectedMonth === $num ? 'selected' : '' }}>{{ $num }} - {{ $mName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilar Konten -->
                <div class="flex items-center gap-1.5">
                    <label for="filter_pillar" class="text-xs text-gray-600 font-medium">Pilar:</label>
                    <select id="filter_pillar" name="pillar" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua Pilar</option>
                        @foreach($allPillars as $pillarName)
                            <option value="{{ $pillarName }}" {{ $selectedPillar === $pillarName ? 'selected' : '' }}>{{ $pillarName }}</option>
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
                @if($selectedMonth || $selectedPillar || $selectedContentType || $selectedYear != 2026)
                    <a href="{{ route('dashboard.composition') }}" class="text-xs text-gray-500 hover:text-purple-700 underline font-medium">Reset Filter</a>
                @endif
                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white rounded-lg shadow-xs transition">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Core Charts Grid: KPI 3 & KPI 4 (Grid 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- KPI 3: Stacked Bar Komposisi Interaksi -->
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">KPI 3 — Komposisi Interaksi per Bulan</h3>
                    <p class="card-subtitle text-xs text-gray-500">Breakdown Like, Comment, Share, dan Save (Klik bar bulan untuk rincian)</p>
                </div>
                <span class="text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-1 rounded-md">Stacked Bar</span>
            </div>
            <div id="chartStackedInteractions" class="echart-container mt-3"></div>
        </div>

        <!-- KPI 4: Top & Low Performance Champion Cards -->
        <div class="card p-5 flex flex-col">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="card-title text-base font-bold text-gray-900">KPI 4 — Top & Low Performance</h3>
                    <p class="card-subtitle text-xs text-gray-500">Performa tertinggi & terendah per metrik interaksi</p>
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
                    <form method="GET" action="{{ route('dashboard.composition') }}" class="flex items-center gap-1">
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

            @if(! $hasPoolData)
                <!-- Empty State jika tidak ada data evaluasi di bulan/filter terpilih -->
                <div class="my-auto py-12 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-2 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <div class="text-xs font-bold text-gray-800">Tidak Ada Data Evaluasi</div>
                    <div class="text-[11px] text-gray-500 max-w-xs mt-0.5">Belum ada konten yang berstatus selesai evaluasi pada bulan atau filter yang dipilih.</div>
                </div>
            @else
                <!-- TAB 1: Top Performance Items -->
                <div id="topPerformanceList" class="mt-4 space-y-2.5 overflow-y-auto max-h-[350px] pr-1">
                    <!-- Top Views -->
                    <div class="p-3 rounded-xl bg-teal-50/60 border border-teal-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs">
                                👁️
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider font-bold text-teal-800">Top Views (H+30)</div>
                                <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topViews?->title ?? 'Belum ada data' }}</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $topViews?->post_date ? $topViews->post_date->format('d M Y') : '-' }} &bull; {{ $topViews?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-teal-900">{{ number_format($topViews?->views_h30 ?? 0) }}</div>
                            <div class="text-[10px] text-teal-700 font-semibold">Tertinggi</div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $topER ? $topER->post_date?->format('d M Y') . ' • ' . number_format($topER->views_h30) . ' views' : '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-indigo-900">{{ number_format((float) ($topER?->engagement_rate ?? 0), 2) }}%</div>
                            <div class="text-[10px] text-indigo-700 font-semibold">Tertinggi</div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $topLikes?->post_date ? $topLikes->post_date->format('d M Y') : '-' }} &bull; {{ $topLikes?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-purple-900">{{ number_format($topLikes?->likes ?? 0) }}</div>
                            <div class="text-[10px] text-purple-700 font-semibold">Tertinggi</div>
                        </div>
                    </div>

                    <!-- Top Comments -->
                    <div class="p-3 rounded-xl bg-cyan-50/60 border border-cyan-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyan-600 text-white flex items-center justify-center font-bold text-xs">
                                💬
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider font-bold text-cyan-800">Top Comments</div>
                                <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $topComments?->title ?? 'Belum ada data' }}</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $topComments?->post_date ? $topComments->post_date->format('d M Y') : '-' }} &bull; {{ $topComments?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-cyan-900">{{ number_format($topComments?->comments ?? 0) }}</div>
                            <div class="text-[10px] text-cyan-700 font-semibold">Tertinggi</div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $topShares?->post_date ? $topShares->post_date->format('d M Y') : '-' }} &bull; {{ $topShares?->pillar?->value ?? '-' }}
                                </div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $topSaves?->post_date ? $topSaves->post_date->format('d M Y') : '-' }} &bull; {{ $topSaves?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-amber-900">{{ number_format($topSaves?->saves ?? 0) }}</div>
                            <div class="text-[10px] text-amber-700 font-semibold">Tertinggi</div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Lowest Performance Items (Low) -->
                <div id="lowPerformanceList" class="mt-4 space-y-2.5 overflow-y-auto max-h-[350px] pr-1 hidden">
                    <!-- Lowest Views -->
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                                👁️
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Views (H+30)</div>
                                <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowViews?->title ?? 'Belum ada data' }}</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $lowViews?->post_date ? $lowViews->post_date->format('d M Y') : '-' }} &bull; {{ $lowViews?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-gray-800">{{ number_format($lowViews?->views_h30 ?? 0) }}</div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $lowER ? $lowER->post_date?->format('d M Y') . ' • ' . number_format($lowER->views_h30) . ' views' : '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-gray-800">{{ number_format((float) ($lowER?->engagement_rate ?? 0), 2) }}%</div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $lowLikes?->post_date ? $lowLikes->post_date->format('d M Y') : '-' }} &bull; {{ $lowLikes?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-gray-800">{{ number_format($lowLikes?->likes ?? 0) }}</div>
                            <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                        </div>
                    </div>

                    <!-- Lowest Comments -->
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-500 text-white flex items-center justify-center font-bold text-xs">
                                💬
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider font-bold text-gray-700">Lowest Comments</div>
                                <div class="text-xs font-bold text-gray-900 truncate max-w-xs">{{ $lowComments?->title ?? 'Belum ada data' }}</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $lowComments?->post_date ? $lowComments->post_date->format('d M Y') : '-' }} &bull; {{ $lowComments?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-gray-800">{{ number_format($lowComments?->comments ?? 0) }}</div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $lowShares?->post_date ? $lowShares->post_date->format('d M Y') : '-' }} &bull; {{ $lowShares?->pillar?->value ?? '-' }}
                                </div>
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
                                <div class="text-[11px] text-gray-500">
                                    {{ $lowSaves?->post_date ? $lowSaves->post_date->format('d M Y') : '-' }} &bull; {{ $lowSaves?->pillar?->value ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-extrabold text-gray-800">{{ number_format($lowSaves?->saves ?? 0) }}</div>
                            <div class="text-[10px] text-gray-500 font-semibold">Terendah</div>
                        </div>
                    </div>
                </div>
            @endif
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
            <button type="button" onclick="document.getElementById('drilldownModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
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
    function switchPerformanceTab(type) {
        const topList = document.getElementById('topPerformanceList');
        const lowList = document.getElementById('lowPerformanceList');
        const tabTop = document.getElementById('tabTopBtn');
        const tabLow = document.getElementById('tabLowBtn');

        if (type === 'top') {
            topList.classList.remove('hidden');
            lowList.classList.add('hidden');
            tabTop.className = 'px-2.5 py-1 rounded-md transition text-purple-700 bg-white shadow-xs font-bold';
            tabLow.className = 'px-2.5 py-1 rounded-md transition text-gray-500 hover:text-gray-800 font-medium';
        } else {
            topList.classList.add('hidden');
            lowList.classList.remove('hidden');
            tabLow.className = 'px-2.5 py-1 rounded-md transition text-rose-700 bg-white shadow-xs font-bold';
            tabTop.className = 'px-2.5 py-1 rounded-md transition text-gray-500 hover:text-gray-800 font-medium';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        requestAnimationFrame(function () {
            const monthNames = @json($monthNames);
            const drilldownData = @json($monthlyContentsDrilldown);

            // KPI 3: Stacked Bar Chart Komposisi Interaksi
            const stackedEl = document.getElementById('chartStackedInteractions');
            if (stackedEl) {
                const chartStacked = echarts.init(stackedEl, null, { renderer: 'canvas' });
                chartStacked.setOption({
                    animation: true,
                    animationDuration: 900,
                    animationEasing: 'cubicOut',
                    animationDurationUpdate: 500,
                    animationEasingUpdate: 'cubicInOut',
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'shadow' }
                    },
                    legend: { data: ['Likes', 'Comments', 'Shares / Reposts', 'Saves'], bottom: 0 },
                    grid: { top: '12%', left: '3%', right: '4%', bottom: '15%', containLabel: true },
                    xAxis: [{ type: 'category', data: monthNames }],
                    yAxis: [{ type: 'value', name: 'Interaksi', splitLine: { lineStyle: { color: '#f0f0f0' } } }],
                    series: [
                        {
                            name: 'Likes',
                            type: 'bar',
                            stack: 'total',
                            emphasis: { focus: 'series' },
                            itemStyle: { color: '#775da6' },
                            animationDelay: function (idx) {
                                return idx * 40;
                            },
                            animationDuration: 800,
                            animationEasing: 'cubicOut',
                            data: @json($monthlyLikes)
                        },
                        {
                            name: 'Comments',
                            type: 'bar',
                            stack: 'total',
                            emphasis: { focus: 'series' },
                            itemStyle: { color: '#2dd4bf' },
                            animationDelay: function (idx) {
                                return idx * 40 + 70;
                            },
                            animationDuration: 800,
                            animationEasing: 'cubicOut',
                            data: @json($monthlyComments)
                        },
                        {
                            name: 'Shares / Reposts',
                            type: 'bar',
                            stack: 'total',
                            emphasis: { focus: 'series' },
                            itemStyle: { color: '#f9837c' },
                            animationDelay: function (idx) {
                                return idx * 40 + 140;
                            },
                            animationDuration: 800,
                            animationEasing: 'cubicOut',
                            data: @json($monthlyShares)
                        },
                        {
                            name: 'Saves',
                            type: 'bar',
                            stack: 'total',
                            emphasis: { focus: 'series' },
                            itemStyle: { color: '#fbbf24', borderRadius: [4, 4, 0, 0] },
                            animationDelay: function (idx) {
                                return idx * 40 + 210;
                            },
                            animationDuration: 800,
                            animationEasing: 'cubicOut',
                            data: @json($monthlySaves)
                        }
                    ]
                });

                // Drilldown click interaction
                chartStacked.on('click', function (params) {
                    const monthIdx = params.dataIndex + 1;
                    const items = drilldownData[monthIdx] || [];
                    const monthName = monthNames[params.dataIndex];

                    document.getElementById('drilldownTitle').innerText = 'Rincian Konten Bulan ' + monthName + ' ' + {{ $selectedYear }};
                    const tbody = document.getElementById('drilldownTbody');
                    tbody.innerHTML = '';

                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-gray-400">Tidak ada konten yang dievaluasi pada bulan ini.</td></tr>';
                    } else {
                        items.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50';
                            tr.innerHTML = `
                                <td class="py-2.5 px-2 font-bold text-gray-900">${item.title}</td>
                                <td class="py-2.5 px-2"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">${item.pillar}</span></td>
                                <td class="py-2.5 px-2 text-right font-medium">${item.views.toLocaleString()}</td>
                                <td class="py-2.5 px-2 text-right font-medium">${item.interactions.toLocaleString()}</td>
                                <td class="py-2.5 px-2 text-right font-bold text-purple-700">${item.er.toFixed(2)}%</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

                    document.getElementById('drilldownModal').classList.remove('hidden');
                });

                window.addEventListener('resize', () => chartStacked.resize());
            }
        });
    });
</script>
@endpush
