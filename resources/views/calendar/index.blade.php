@extends('layouts.app')

@section('title', 'Kalender Konten — ' . ($indoMonths[$month] ?? 'Bulan ' . $month) . ' ' . $year)
@section('page_title', 'Kalender Konten & Perencanaan')

@section('content')
<div id="calendarContainer" class="space-y-6 transition-opacity duration-200">
    @include('calendar.partials.calendar-view')
</div>

<!-- Modal Detail Konten Kalender -->
<div id="calendarDetailModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span id="cdm_type_badge" class="px-2 py-0.5 rounded text-xs font-bold"></span>
                <span id="cdm_status_badge" class="px-2 py-0.5 rounded text-xs font-bold"></span>
            </div>
            <button type="button" onclick="document.getElementById('calendarDetailModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 cursor-pointer">&times;</button>
        </div>

        <div class="py-4 space-y-3">
            <h3 id="cdm_title" class="text-base font-extrabold text-gray-900 leading-snug"></h3>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Pilar Konten</span>
                    <strong id="cdm_pillar" class="text-gray-800"></strong>
                </div>
                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Nama PIC</span>
                    <strong id="cdm_pic" class="text-gray-800"></strong>
                </div>
                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Tanggal Posting</span>
                    <strong id="cdm_post_date" class="text-gray-800"></strong>
                </div>
                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Jatuh Tempo H+30</span>
                    <strong id="cdm_h30_date" class="text-gray-800"></strong>
                </div>
            </div>

            <!-- Metrik Ringkas -->
            <div id="cdm_metrics_box" class="p-3 rounded-xl bg-purple-50/60 border border-purple-100 grid grid-cols-3 gap-2 text-center text-xs">
                <div>
                    <span class="text-gray-500 text-[10px] block">Views H+30</span>
                    <strong id="cdm_views" class="text-gray-900 font-bold text-sm"></strong>
                </div>
                <div>
                    <span class="text-gray-500 text-[10px] block">Interaksi</span>
                    <strong id="cdm_interactions" class="text-purple-700 font-bold text-sm"></strong>
                </div>
                <div>
                    <span class="text-gray-500 text-[10px] block">ER (%)</span>
                    <strong id="cdm_er" class="text-gray-900 font-extrabold text-sm"></strong>
                </div>
            </div>

            <!-- Evidence URL -->
            <div id="cdm_evidence_container" class="text-xs">
                <span class="text-gray-500 block text-[11px] mb-1">Bukti Evidence:</span>
                <a id="cdm_evidence_link" href="#" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-purple-700 font-semibold hover:underline">
                    <span>Lihat Postingan Instagram</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>

            <!-- Catatan -->
            <div class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                <span class="text-[10px] font-bold uppercase text-gray-400 block mb-0.5">Catatan Brief / Evaluasi:</span>
                <p id="cdm_notes" class="text-gray-700 italic"></p>
            </div>
        </div>

        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
            <a id="cdm_master_link" href="{{ route('content-logs.index') }}" class="text-xs text-purple-700 hover:text-purple-900 font-semibold underline">
                Buka di Master Log Konten &rarr;
            </a>
            <button type="button" onclick="document.getElementById('calendarDetailModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function planContentOnDate(dateStr) {
        const modal = document.getElementById('createContentModal');
        if (!modal) return;
        
        const dateInput = modal.querySelector('input[name="post_date"]');
        if (dateInput && dateStr) {
            dateInput.value = dateStr;
        }
        
        modal.classList.remove('hidden');
    }

    function showContentCalendarDetail(data) {
        document.getElementById('cdm_title').textContent = data.title;
        document.getElementById('cdm_pillar').textContent = data.pillar;
        document.getElementById('cdm_pic').textContent = data.pic;
        document.getElementById('cdm_post_date').textContent = data.post_date;
        document.getElementById('cdm_h30_date').textContent = data.h30_due_date;
        document.getElementById('cdm_views').textContent = data.views;
        document.getElementById('cdm_interactions').textContent = data.interactions;
        document.getElementById('cdm_er').textContent = data.er;
        document.getElementById('cdm_notes').textContent = data.notes;

        const typeBadge = document.getElementById('cdm_type_badge');
        typeBadge.textContent = data.type;
        typeBadge.className = 'px-2 py-0.5 rounded text-xs font-bold ' + 
            (data.type === 'Reels' ? 'bg-purple-100 text-purple-800' : (data.type === 'Carousel' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'));

        const statusBadge = document.getElementById('cdm_status_badge');
        statusBadge.textContent = data.status;
        statusBadge.className = 'px-2 py-0.5 rounded text-xs font-bold ' + 
            (data.is_completed ? 'bg-green-100 text-green-800' : (data.is_overdue ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'));

        const evidenceLink = document.getElementById('cdm_evidence_link');
        const evidenceContainer = document.getElementById('cdm_evidence_container');
        if (data.evidence_url) {
            evidenceLink.href = data.evidence_url;
            evidenceContainer.classList.remove('hidden');
        } else {
            evidenceContainer.classList.add('hidden');
        }

        const masterLink = document.getElementById('cdm_master_link');
        masterLink.href = "{{ route('content-logs.index') }}?search=" + encodeURIComponent(data.title);

        document.getElementById('calendarDetailModal').classList.remove('hidden');
    }

    // AJAX Navigation for Content Calendar (tanpa refresh halaman)
    const calendarContainer = document.getElementById('calendarContainer');

    function fetchCalendar(url, pushState = true) {
        if (!calendarContainer) return;

        // Visual loading feedback
        calendarContainer.style.opacity = '0.5';
        calendarContainer.style.pointerEvents = 'none';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP status ' + response.status);
            return response.text();
        })
        .then(html => {
            calendarContainer.innerHTML = html;
            calendarContainer.style.opacity = '1';
            calendarContainer.style.pointerEvents = 'auto';

            if (pushState) {
                window.history.pushState({ url: url }, '', url);
            }
        })
        .catch(err => {
            console.error('AJAX Calendar error:', err);
            calendarContainer.style.opacity = '1';
            calendarContainer.style.pointerEvents = 'auto';
            window.location.href = url;
        });
    }

    if (calendarContainer) {
        // Intercept clicks on calendar navigation links (prev, next, bulan ini, reset)
        calendarContainer.addEventListener('click', function (e) {
            const link = e.target.closest('a.calendar-nav-link');
            if (link && link.href) {
                e.preventDefault();
                fetchCalendar(link.href, true);
            }
        });

        // Intercept filter form submit
        calendarContainer.addEventListener('submit', function (e) {
            const form = e.target.closest('#calendarFilterForm');
            if (form) {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value !== '') {
                        params.append(key, value);
                    }
                }
                const targetUrl = form.action.split('?')[0] + '?' + params.toString();
                fetchCalendar(targetUrl, true);
            }
        });

        // Intercept dropdown changes (month, year, pillar, format)
        calendarContainer.addEventListener('change', function (e) {
            const select = e.target.closest('#calendarFilterForm select');
            if (select) {
                const form = select.form || document.getElementById('calendarFilterForm');
                if (form) {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });
    }

    // Handle browser Back / Forward buttons
    window.addEventListener('popstate', function (e) {
        fetchCalendar(window.location.href, false);
    });
</script>
@endpush
