    <!-- Header Navigation & Filter Bar -->
    <div class="card p-4 sm:p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            <!-- Left: Current Month Title & Quick Nav -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'pillar' => $selectedPillar, 'content_type' => $selectedContentType]) }}" class="calendar-nav-link p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-600 transition cursor-pointer" title="Bulan Sebelumnya ({{ $indoMonths[$prevMonth->month] ?? $prevMonth->month }} {{ $prevMonth->year }})">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <a href="{{ route('calendar.index', ['year' => now()->year, 'month' => now()->month, 'pillar' => $selectedPillar, 'content_type' => $selectedContentType]) }}" class="calendar-nav-link px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-xs font-semibold text-gray-700 transition cursor-pointer">
                        Bulan Ini
                    </a>
                    <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'pillar' => $selectedPillar, 'content_type' => $selectedContentType]) }}" class="calendar-nav-link p-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-600 transition cursor-pointer" title="Bulan Berikutnya ({{ $indoMonths[$nextMonth->month] ?? $nextMonth->month }} {{ $nextMonth->year }})">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>

                <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                    <span>🗓️ {{ $indoMonths[$month] ?? 'Bulan ' . $month }} {{ $year }}</span>
                </h2>

                <!-- Month Stats Badges -->
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 font-bold border border-purple-100">
                        Total: {{ $totalMonthContents }} Konten
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 text-green-700 font-semibold border border-green-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ $completedCount }} Dievaluasi
                    </span>
                    @if($pendingCount > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-semibold border border-amber-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>{{ $pendingCount }} Menunggu H+30
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right: Filters & Action Button -->
            <div class="flex flex-wrap items-center gap-3">
                <form id="calendarFilterForm" method="GET" action="{{ route('calendar.index') }}" class="flex flex-wrap items-center gap-2">
                    <!-- Dropdown Bulan -->
                    <select name="month" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-semibold text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        @foreach($indoMonths as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $month === $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>

                    <!-- Dropdown Tahun -->
                    <select name="year" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-semibold text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>

                    <!-- Filter Pilar -->
                    <select name="pillar" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <option value="">Semua Pilar</option>
                        @foreach($allPillars as $p)
                            <option value="{{ $p }}" {{ $selectedPillar === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>

                    <!-- Filter Format -->
                    <select name="content_type" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <option value="">Semua Format</option>
                        @foreach($contentTypes as $t)
                            <option value="{{ $t->value }}" {{ $selectedContentType === $t->value ? 'selected' : '' }}>{{ $t->value }}</option>
                        @endforeach
                    </select>

                    @if($selectedPillar || $selectedContentType)
                        <a href="{{ route('calendar.index', ['year' => $year, 'month' => $month]) }}" class="calendar-nav-link text-xs text-gray-500 hover:text-purple-700 underline font-medium">Reset</a>
                    @endif
                </form>

                @can('content_logs.create')
                <button type="button" onclick="planContentOnDate('{{ sprintf('%04d-%02d-01', $year, $month) }}')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Rencanakan Konten</span>
                </button>
                @endcan
            </div>

        </div>
    </div>

    <!-- Calendar Grid Card -->
    <div class="card overflow-hidden">
        <!-- Day of Week Headers -->
        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50/80 text-center text-xs font-bold uppercase tracking-wider text-gray-600">
            <div class="py-3 px-2">Senin</div>
            <div class="py-3 px-2">Selasa</div>
            <div class="py-3 px-2">Rabu</div>
            <div class="py-3 px-2">Kamis</div>
            <div class="py-3 px-2">Jumat</div>
            <div class="py-3 px-2 text-rose-600">Sabtu</div>
            <div class="py-3 px-2 text-rose-600">Minggu</div>
        </div>

        <!-- Days Grid -->
        <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-gray-100 bg-gray-100/40">
            @foreach($calendarDays as $day)
                @php
                    $isWeekend = in_array($day['date']->dayOfWeekIso, [6, 7], true);
                    $hasContents = $day['contents']->isNotEmpty();
                @endphp
                <div class="min-h-[120px] p-2 flex flex-col justify-between transition-colors relative group {{ $day['isCurrentMonth'] ? 'bg-white hover:bg-purple-50/20' : 'bg-gray-50/70 text-gray-400' }}">
                    <!-- Day Header (Date & Add Button) -->
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $day['isToday'] ? 'bg-purple-700 text-white shadow-xs' : ($day['isCurrentMonth'] ? ($isWeekend ? 'text-rose-600' : 'text-gray-900') : 'text-gray-400') }}">
                            {{ $day['dayNumber'] }}
                        </span>

                        @can('content_logs.create')
                        <button type="button" onclick="planContentOnDate('{{ $day['dateString'] }}')" title="Rencanakan konten pada tanggal {{ $day['date']->format('d/m/Y') }}" class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-purple-100 text-purple-700 transition cursor-pointer text-[10px] font-bold flex items-center gap-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                        @endcan
                    </div>

                    <!-- Day Content Cards -->
                    <div class="space-y-1.5 flex-1 overflow-y-auto max-h-[140px] pr-0.5">
                        @foreach($day['contents'] as $content)
                            @php
                                $isCompleted = $content->data_status === \App\Enums\DataStatus::Completed;
                                $isOverdue = ! $isCompleted && $content->h30_due_date && $content->h30_due_date->isPast();
                            @endphp
                            <div onclick="showContentCalendarDetail({{ json_encode([
                                'id' => $content->id,
                                'title' => $content->title,
                                'post_date' => $content->post_date?->format('d M Y'),
                                'h30_due_date' => $content->h30_due_date?->format('d M Y'),
                                'type' => $content->content_type?->value,
                                'pillar' => is_object($content->pillar) && property_exists($content->pillar, 'value') ? $content->pillar->value : (string) $content->pillar,
                                'pic' => $content->pic_name ?: '-',
                                'status' => $content->data_status?->value ?? 'Menunggu H+30',
                                'views' => number_format($content->views_h30),
                                'interactions' => number_format($content->total_interactions),
                                'er' => number_format((float) $content->engagement_rate, 2) . '%',
                                'evidence_url' => $content->evidence_url,
                                'notes' => $content->notes ?: '-',
                                'is_overdue' => $isOverdue,
                                'is_completed' => $isCompleted
                            ]) }})" class="p-1.5 rounded-lg border text-left cursor-pointer transition hover:shadow-xs {{ $isCompleted ? 'bg-green-50/70 border-green-200 hover:border-green-400' : ($isOverdue ? 'bg-red-50/80 border-red-200 hover:border-red-400' : 'bg-amber-50/70 border-amber-200 hover:border-amber-400') }}">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <span class="text-[10px] font-bold truncate text-gray-800 flex items-center gap-1">
                                        @if($content->content_type === \App\Enums\ContentType::Reels)
                                            <span>🎬</span>
                                        @elseif($content->content_type === \App\Enums\ContentType::Carousel)
                                            <span>📑</span>
                                        @else
                                            <span>🖼️</span>
                                        @endif
                                        <span class="truncate">{{ $content->title }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[9px] text-gray-500">
                                    <span class="truncate font-medium text-purple-700 max-w-[80px]">{{ is_object($content->pillar) && property_exists($content->pillar, 'value') ? $content->pillar->value : (string) $content->pillar }}</span>
                                    @if($isCompleted)
                                        <span class="text-green-700 font-bold">✓ {{ number_format((float) $content->engagement_rate, 1) }}%</span>
                                    @elseif($isOverdue)
                                        <span class="text-red-700 font-extrabold">⚠️ Overdue</span>
                                    @else
                                        <span class="text-amber-700 font-semibold">H+30</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Footer Indicator if any -->
                    @if(! $hasContents && $day['isCurrentMonth'])
                        <div class="text-[10px] text-gray-300 italic pt-1 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            Klik + untuk rencanakan
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>