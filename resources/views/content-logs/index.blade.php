@extends('layouts.app')

@section('title', 'Master Log Konten')
@section('page_title', 'Master Log Konten')

@section('content')
<div class="space-y-6">

    <!-- Filter & Search Toolbar -->
    <div class="card p-4 sm:p-5">
        <form id="filterForm" method="GET" action="{{ route('content-logs.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <input type="hidden" name="per_page" id="filterPerPage" value="{{ $perPage ?? 15 }}">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Box -->
                <div class="relative min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul konten / PIC..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Filter Status H+30 -->
                <select name="status" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                    <option value="">Semua Status Data</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Sudah Diisi</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu H+30</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>⚠️ Lewat Jatuh Tempo (Overdue)</option>
                </select>

                <!-- Filter Pilar -->
                <select name="pillar" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                    <option value="">Semua Pilar</option>
                    @foreach($pillars as $p)
                        <option value="{{ $p->value }}" {{ request('pillar') === $p->value ? 'selected' : '' }}>{{ $p->value }}</option>
                    @endforeach
                </select>

                <!-- Filter Format -->
                <select name="content_type" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                    <option value="">Semua Format</option>
                    @foreach($contentTypes as $t)
                        <option value="{{ $t->value }}" {{ request('content_type') === $t->value ? 'selected' : '' }}>{{ $t->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'status', 'pillar', 'content_type', 'month', 'year']))
                    <a href="{{ route('content-logs.index', ['per_page' => $perPage ?? 15]) }}" class="text-xs text-gray-500 hover:text-purple-700 underline font-medium">Reset Filter</a>
                @endif
                <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white rounded-lg shadow-xs transition">
                    Cari & Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card Container (AJAX Target) -->
    <div id="logsTableContainer" class="transition-opacity duration-200">
        @include('content-logs.partials.table')
    </div>

</div>

<!-- Modal Input Metrik H+30 -->
<div id="metricsModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">Input / Edit Metrik H+30</h3>
                <p id="metricsModalSubtitle" class="text-xs text-gray-500 truncate max-w-md"></p>
            </div>
            <button type="button" onclick="document.getElementById('metricsModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form id="metricsForm" method="POST" class="py-4 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Views H+30 *</label>
                    <input type="number" id="m_views_h30" name="views_h30" min="0" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Viewers H+30 (Unik)</label>
                    <input type="number" id="m_viewers_h30" name="viewers_h30" min="0" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Like *</label>
                    <input type="number" id="m_likes" name="likes" min="0" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Comment *</label>
                    <input type="number" id="m_comments" name="comments" min="0" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Share / Repost *</label>
                    <input type="number" id="m_shares" name="shares_reposts" min="0" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Save *</label>
                    <input type="number" id="m_saves" name="saves" min="0" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Follows Baru</label>
                <input type="number" id="m_follows" name="follows" min="0" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Evaluasi</label>
                <textarea id="m_notes" name="notes" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('metricsModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-lg shadow-sm">
                    Simpan Metrik H+30
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Identitas Konten -->
<div id="editModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Edit Identitas Konten</h3>
            <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form id="editForm" method="POST" class="py-4 space-y-3">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Posting *</label>
                    <input type="date" id="e_post_date" name="post_date" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Konten *</label>
                    <select id="e_content_type" name="content_type" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        @foreach($contentTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Judul / Topik Konten *</label>
                <input type="text" id="e_title" name="title" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tujuan / Pilar Konten *</label>
                    <select id="e_pillar" name="pillar" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        @foreach($pillars as $pillar)
                            <option value="{{ $pillar->value }}">{{ $pillar->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama PIC</label>
                    <input type="text" id="e_pic_name" name="pic_name" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Link Evidence Posting (Instagram URL)</label>
                <input type="url" id="e_evidence_url" name="evidence_url" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea id="e_notes" name="notes" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-lg shadow-sm">
                    Perbarui Konten
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openMetricsModalFromButton(btn) {
        try {
            const log = JSON.parse(btn.getAttribute('data-log'));
            openMetricsModal(log);
        } catch (e) {
            console.error('Failed to parse log data:', e);
        }
    }

    function openEditModalFromButton(btn) {
        try {
            const log = JSON.parse(btn.getAttribute('data-log'));
            openEditModal(log);
        } catch (e) {
            console.error('Failed to parse log data:', e);
        }
    }

    function openMetricsModal(log) {
        const postDateStr = log.post_date ? String(log.post_date).substring(0, 10) : '-';
        document.getElementById('metricsModalSubtitle').textContent = (log.title || '') + ' (Post: ' + postDateStr + ')';
        document.getElementById('metricsForm').action = "{{ url('content-logs') }}/" + log.id + '/metrics';

        document.getElementById('m_views_h30').value = log.views_h30 || 0;
        document.getElementById('m_viewers_h30').value = log.viewers_h30 || 0;
        document.getElementById('m_likes').value = log.likes || 0;
        document.getElementById('m_comments').value = log.comments || 0;
        document.getElementById('m_shares').value = log.shares_reposts || 0;
        document.getElementById('m_saves').value = log.saves || 0;
        document.getElementById('m_follows').value = log.follows || 0;
        document.getElementById('m_notes').value = log.notes || '';

        document.getElementById('metricsModal').classList.remove('hidden');
    }

    function openEditModal(log) {
        document.getElementById('editForm').action = "{{ url('content-logs') }}/" + log.id;

        document.getElementById('e_post_date').value = log.post_date ? String(log.post_date).substring(0, 10) : '';
        document.getElementById('e_content_type').value = log.content_type || '';
        document.getElementById('e_title').value = log.title || '';

        let pillarVal = '';
        if (log.pillar) {
            if (typeof log.pillar === 'object' && log.pillar.value !== undefined) {
                pillarVal = log.pillar.value;
            } else {
                pillarVal = String(log.pillar);
            }
        }

        const selectPillar = document.getElementById('e_pillar');
        if (selectPillar) {
            if (pillarVal) {
                let exists = false;
                for (let i = 0; i < selectPillar.options.length; i++) {
                    if (selectPillar.options[i].value === pillarVal) {
                        exists = true;
                        break;
                    }
                }
                if (!exists) {
                    const newOpt = document.createElement('option');
                    newOpt.value = pillarVal;
                    newOpt.textContent = pillarVal;
                    selectPillar.appendChild(newOpt);
                }
                selectPillar.value = pillarVal;
            } else {
                selectPillar.value = '';
            }
        }

        document.getElementById('e_pic_name').value = log.pic_name || '';
        document.getElementById('e_evidence_url').value = log.evidence_url || '';
        document.getElementById('e_notes').value = log.notes || '';

        document.getElementById('editModal').classList.remove('hidden');
    }

    // AJAX Pagination and Filtering
    const logsContainer = document.getElementById('logsTableContainer');
    const filterForm = document.getElementById('filterForm');

    function fetchLogsTable(url, pushState = true) {
        if (!logsContainer) return;

        // Visual loading feedback
        logsContainer.style.opacity = '0.5';
        logsContainer.style.pointerEvents = 'none';

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
            logsContainer.innerHTML = html;
            logsContainer.style.opacity = '1';
            logsContainer.style.pointerEvents = 'auto';

            if (pushState) {
                window.history.pushState({ url: url }, '', url);
            }

            // Sync hidden filterPerPage if URL has per_page
            try {
                const parsedUrl = new URL(url, window.location.origin);
                const perPageVal = parsedUrl.searchParams.get('per_page');
                const filterPerPageInput = document.getElementById('filterPerPage');
                if (filterPerPageInput && perPageVal) {
                    filterPerPageInput.value = perPageVal;
                }
            } catch (err) {}

            // Smooth scroll to top of table if scrolled past it
            const rect = logsContainer.getBoundingClientRect();
            if (rect.top < 0) {
                logsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })
        .catch(err => {
            console.error('AJAX Pagination error:', err);
            logsContainer.style.opacity = '1';
            logsContainer.style.pointerEvents = 'auto';
            window.location.href = url;
        });
    }

    // Intercept clicks on pagination links
    if (logsContainer) {
        logsContainer.addEventListener('click', function (e) {
            const link = e.target.closest('.pagination-container a, nav[role="navigation"] a');
            if (link && link.href) {
                e.preventDefault();
                fetchLogsTable(link.href, true);
            }
        });
    }

    // Handle change of "Show Data" (15, 25, 50, 100)
    function changePerPage(perPageVal) {
        const filterPerPageInput = document.getElementById('filterPerPage');
        if (filterPerPageInput) {
            filterPerPageInput.value = perPageVal;
        }

        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPageVal);
        currentUrl.searchParams.set('page', '1'); // Reset to page 1 on page size change
        fetchLogsTable(currentUrl.toString(), true);
    }

    // Handle filter form submission via AJAX
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value !== '') {
                    params.append(key, value);
                }
            }
            params.set('page', '1'); // Reset to page 1 on new filter
            const targetUrl = filterForm.action + '?' + params.toString();
            fetchLogsTable(targetUrl, true);
        });

        // Trigger AJAX filter on change for select elements
        filterForm.querySelectorAll('select').forEach(sel => {
            sel.addEventListener('change', function () {
                filterForm.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        });
    }

    // Handle browser Back / Forward buttons
    window.addEventListener('popstate', function (e) {
        fetchLogsTable(window.location.href, false);
    });
</script>
@endpush
