<div class="card overflow-hidden">
    <div class="p-4 sm:px-6 sm:py-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100">
        <div>
            <h3 class="card-title text-base font-bold text-gray-900">Daftar Log Konten Instagram</h3>
            <p class="card-subtitle text-xs text-gray-500">
                Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari total {{ $logs->total() }} konten
            </p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Selector Show Data -->
            <div class="flex items-center gap-1.5">
                <label for="perPageSelect" class="text-xs text-gray-500 font-medium whitespace-nowrap">Tampilkan:</label>
                <select id="perPageSelect" onchange="changePerPage(this.value)" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-semibold text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                    <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15 data</option>
                    <option value="25" {{ ($perPage ?? 15) == 25 ? 'selected' : '' }}>25 data</option>
                    <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50 data</option>
                    <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }}>100 data</option>
                </select>
            </div>

            @can('export.view')
            <a href="{{ route('export.csv') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export CSV</span>
            </a>
            @endcan
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                    <th class="py-3 px-4">No</th>
                    <th class="py-3 px-4">Tgl Posting</th>
                    <th class="py-3 px-4">Format</th>
                    <th class="py-3 px-4 min-w-[200px]">Judul / Topik</th>
                    <th class="py-3 px-4">Pilar</th>
                    <th class="py-3 px-4">PIC</th>
                    <th class="py-3 px-4">Evidence</th>
                    <th class="py-3 px-4">Jatuh Tempo H+30</th>
                    <th class="py-3 px-4 text-right">Views</th>
                    <th class="py-3 px-4 text-right">Interaksi</th>
                    <th class="py-3 px-4 text-right">ER (%)</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $index => $log)
                    @php
                        $isOverdue = $log->data_status === \App\Enums\DataStatus::PendingH30 
                            && $log->h30_due_date 
                            && $log->h30_due_date->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition {{ $isOverdue ? 'bg-amber-50/30' : '' }}">
                        <td class="py-3 px-4 text-gray-400 font-mono">{{ $logs->firstItem() + $index }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-900">{{ $log->post_date?->format('d/m/Y') }}</td>
                        <td class="py-3 px-4">
                            @if($log->content_type === \App\Enums\ContentType::Reels)
                                <span class="badge badge-secondary">🎬 Reels</span>
                            @elseif($log->content_type === \App\Enums\ContentType::Carousel)
                                <span class="badge badge-warning">📑 Carousel</span>
                            @else
                                <span class="badge badge-primary">🖼️ Feed</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $log->title }}">{{ $log->title }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge badge-info">{{ $log->pillar_name }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $log->pic_name ?: '-' }}</td>
                        <td class="py-3 px-4">
                            @if($log->evidence_url)
                                <a href="{{ $log->evidence_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-purple-700 hover:text-purple-900 font-semibold bg-purple-50 hover:bg-purple-100 px-2 py-0.5 rounded text-[11px] transition">
                                    <span>Lihat Post</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @else
                                <span class="text-gray-400 italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-700">{{ $log->h30_due_date?->format('d/m/Y') }}</span>
                                @if($isOverdue)
                                    <span class="bg-red-100 text-red-800 text-[10px] font-bold px-1.5 py-0.5 rounded" title="Sudah lewat 30 hari tapi metrik belum diisi">Overdue</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-gray-800">
                            {{ $log->data_status === \App\Enums\DataStatus::Completed ? number_format($log->views_h30) : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-purple-700">
                            {{ $log->data_status === \App\Enums\DataStatus::Completed ? number_format($log->total_interactions) : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-gray-900">
                            {{ $log->data_status === \App\Enums\DataStatus::Completed ? number_format((float) $log->engagement_rate, 2) . '%' : '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($log->data_status === \App\Enums\DataStatus::Completed)
                                <span class="badge badge-success">Sudah diisi</span>
                            @else
                                <span class="badge badge-warning">Menunggu H+30</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $canEdit = auth()->user()?->can('content_logs.edit');
                                $canDelete = auth()->user()?->can('content_logs.delete');
                            @endphp

                            @if($canEdit || $canDelete)
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($canEdit)
                                        <!-- Tombol Input / Edit Metrik -->
                                        <button type="button" data-log="{{ json_encode($log) }}" onclick="openMetricsModalFromButton(this)" class="p-1 text-teal-600 hover:text-teal-800 hover:bg-teal-50 rounded cursor-pointer" title="Input / Edit Metrik H+30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                        </button>

                                        <!-- Tombol Edit Info -->
                                        <button type="button" data-log="{{ json_encode($log) }}" onclick="openEditModalFromButton(this)" class="p-1 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded cursor-pointer" title="Edit Identitas Konten">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                    @endif

                                    @if($canDelete)
                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('content-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konten ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded cursor-pointer" title="Hapus Konten">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-medium" title="Hanya memiliki hak akses melihat (View Only)">
                                    🔒 View Only
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="py-8 text-center text-gray-500">
                            Tidak ada data log konten yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50 pagination-container">
            {{ $logs->links() }}
        </div>
    @endif
</div>
