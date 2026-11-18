@extends('layouts.app')

@section('title', 'Log Aktivitas Pengguna')
@section('page_title', 'Log Aktivitas Pengguna')

@section('content')
<div class="space-y-6" x-data="{ detailModal: false, selectedLog: null }">

    <!-- Header & Info Card -->
    <div class="card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-purple-100 text-purple-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Audit Trail & Riwayat Aktivitas</h2>
                    <p class="text-xs text-gray-500">Mencatat seluruh rekam jejak operasional sistem: login/logout, CRUD konten, impor/ekspor data, dan manajemen hak akses.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700">
                Total Riwayat: <strong class="text-purple-700 font-bold">{{ number_format($logs->total()) }}</strong>
            </span>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card p-4 sm:p-5">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas / user / IP..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Filter Modul -->
                <div>
                    <select name="module" class="w-full text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Aksi -->
                <div>
                    <select name="action" class="w-full text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $act)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Pengguna -->
                <div>
                    <select name="user_id" class="w-full text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Submit & Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full px-3.5 py-1.5 text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white rounded-lg shadow-xs transition cursor-pointer">
                        Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['search', 'module', 'action', 'user_id', 'start_date', 'end_date']))
                        <a href="{{ route('activity-logs.index') }}" class="px-2.5 py-1.5 text-xs text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Date range row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100 text-xs text-gray-600">
                <span class="font-semibold text-gray-500">Rentang Tanggal:</span>
                <div class="flex items-center gap-1.5">
                    <label for="start_date" class="text-gray-400">Dari:</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="px-2 py-1 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none">
                </div>
                <div class="flex items-center gap-1.5">
                    <label for="end_date" class="text-gray-400">Sampai:</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="px-2 py-1 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500 focus:outline-none">
                </div>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table Card -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-3">Modul</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                        <th class="py-3 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3 px-3 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-purple-50/20 transition">
                            <!-- Waktu -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-900">{{ $log->created_at->format('d M Y, H:i') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Pengguna -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-purple-700 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($log->user_name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="font-bold text-gray-900 truncate">{{ $log->user_name }}</div>
                                        <span class="inline-block text-[10px] font-medium text-gray-500">
                                            {{ $log->role_name ?? ($log->user?->role?->label ?? 'User') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Modul -->
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $log->module }}
                                </span>
                            </td>

                            <!-- Aksi Badge -->
                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                @php
                                    $actionClass = match(true) {
                                        str_contains($log->action, 'create') || str_contains($log->action, 'import') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        str_contains($log->action, 'delete') => 'bg-rose-50 text-rose-700 border-rose-200',
                                        str_contains($log->action, 'update') => 'bg-blue-50 text-blue-700 border-blue-200',
                                        str_contains($log->action, 'export') => 'bg-amber-50 text-amber-700 border-amber-200',
                                        str_contains($log->action, 'login') => 'bg-purple-50 text-purple-700 border-purple-200',
                                        str_contains($log->action, 'logout') => 'bg-gray-100 text-gray-600 border-gray-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200'
                                    };
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $actionClass }}">
                                    {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>

                            <!-- Deskripsi -->
                            <td class="py-3.5 px-4">
                                <div class="text-xs font-medium text-gray-900 leading-relaxed">{{ $log->description }}</div>
                                @if($log->ip_address)
                                    <div class="mt-0.5 text-[10px] text-gray-400 font-mono">IP: {{ $log->ip_address }}</div>
                                @endif
                            </td>

                            <!-- Detail Modal Trigger -->
                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                @if(!empty($log->properties))
                                    <button 
                                        type="button" 
                                        @click="selectedLog = {{ json_encode($log) }}; detailModal = true"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 transition cursor-pointer">
                                        Data
                                    </button>
                                @else
                                    <span class="text-gray-300 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-500">
                                <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-700">Belum ada riwayat aktivitas</div>
                                <p class="text-xs text-gray-400 mt-1">Aktivitas pengguna akan tercatat otomatis saat ada perubahan data atau login.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detail Properties JSON -->
    <div x-show="detailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 overflow-y-auto">
        <div @click.outside="detailModal = false" class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Detail Metadata Aktivitas</h3>
                    <p class="text-xs text-gray-500" x-text="selectedLog?.module + ' — ' + selectedLog?.action"></p>
                </div>
                <button type="button" @click="detailModal = false" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <span class="text-xs text-gray-500 font-medium">Deskripsi:</span>
                    <p class="text-xs font-semibold text-gray-800 mt-0.5" x-text="selectedLog?.description"></p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <div>
                        <span class="text-gray-400 block text-[11px]">Waktu:</span>
                        <strong class="text-gray-800" x-text="selectedLog?.created_at"></strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">IP Address:</span>
                        <strong class="text-gray-800" x-text="selectedLog?.ip_address ?? '-'"></strong>
                    </div>
                    <div class="col-span-2">
                        <span class="text-gray-400 block text-[11px]">User Agent:</span>
                        <span class="text-gray-600 text-[11px] break-all" x-text="selectedLog?.user_agent ?? '-'"></span>
                    </div>
                </div>

                <div>
                    <span class="text-xs text-gray-500 font-medium">Data Payload (JSON):</span>
                    <pre class="mt-1 p-3 bg-gray-900 text-emerald-400 rounded-xl text-xs font-mono overflow-x-auto max-h-60" x-text="JSON.stringify(selectedLog?.properties, null, 2)"></pre>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" @click="detailModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
