@extends('layouts.app')

@section('title', 'Manajemen Role & Hak Akses')
@section('page_title', 'Manajemen Role & Hak Akses')

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-purple-100 text-purple-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </span>
                <h2 class="text-lg font-bold text-gray-900">Daftar Role Akun & Hak Akses Fitur</h2>
            </div>
            <p class="mt-1 text-xs text-gray-500">
                Atur kewenangan akses untuk setiap peran pengguna di aplikasi. Super Admin dapat menentukan apakah suatu role hanya berstatus <em>View Only (Hanya Lihat)</em>, dapat <em>Tambah</em>, atau dapat <em>Edit</em> dan <em>Hapus</em> data.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Role Baru</span>
            </a>
        </div>
    </div>

    <!-- Role Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach($roles as $role)
            <div class="card p-5 flex flex-col justify-between border-t-4 {{ $role->name === 'super_admin' ? 'border-t-purple-600' : ($role->name === 'admin_konten' ? 'border-t-teal-600' : ($role->name === 'editor_konten' ? 'border-t-amber-500' : 'border-t-gray-400')) }}">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $role->is_system ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $role->is_system ? 'System Role' : 'Custom Role' }}
                        </span>
                        <span class="text-xs font-semibold text-gray-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            {{ $role->users_count }} Akun
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-gray-900 tracking-tight">{{ $role->label }}</h3>
                    <code class="text-[11px] font-mono text-purple-700 font-semibold bg-purple-50 px-1.5 py-0.5 rounded">{{ $role->name }}</code>
                    
                    <p class="mt-2.5 text-xs text-gray-600 min-h-[38px] leading-relaxed">
                        {{ $role->description ?: 'Tidak ada deskripsi.' }}
                    </p>

                    <!-- Permissions Summary Badges -->
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Cakupan Izin:</div>
                        <div class="flex flex-wrap gap-1">
                            @if($role->name === 'super_admin')
                                <span class="bg-purple-100 text-purple-800 text-[10px] font-bold px-2 py-0.5 rounded-md">
                                    ★ Full Access (Seluruh Fitur)
                                </span>
                            @else
                                @php
                                    $canView = $role->permissions->contains(fn($p) => str_contains($p->name, '.view'));
                                    $canCreate = $role->permissions->contains(fn($p) => str_contains($p->name, '.create'));
                                    $canEdit = $role->permissions->contains(fn($p) => str_contains($p->name, '.edit'));
                                    $canDelete = $role->permissions->contains(fn($p) => str_contains($p->name, '.delete'));
                                @endphp
                                @if($canView)
                                    <span class="bg-teal-50 text-teal-800 text-[10px] font-semibold px-2 py-0.5 rounded">👁️ View</span>
                                @endif
                                @if($canCreate)
                                    <span class="bg-green-50 text-green-800 text-[10px] font-semibold px-2 py-0.5 rounded">➕ Tambah</span>
                                @endif
                                @if($canEdit)
                                    <span class="bg-purple-50 text-purple-800 text-[10px] font-semibold px-2 py-0.5 rounded">✏️ Edit</span>
                                @endif
                                @if($canDelete)
                                    <span class="bg-red-50 text-red-800 text-[10px] font-semibold px-2 py-0.5 rounded">🗑️ Hapus</span>
                                @endif
                                @if(! $canCreate && ! $canEdit && ! $canDelete && $canView)
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        🔒 View Only
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                    <a href="{{ route('roles.edit', $role) }}" class="flex-1 text-center py-2 px-3 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                        Atur Hak Akses & Matriks
                    </a>

                    @if(! $role->is_system && $role->name !== 'super_admin')
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role \'{{ $role->label }}\'?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Quick Info Banner -->
    <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-100 flex items-start gap-3">
        <svg class="w-5 h-5 text-purple-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="text-xs text-purple-900 leading-relaxed">
            <strong>Panduan Hak Akses:</strong>
            Perubahan hak akses pada role akan langsung berlaku secara <em>real-time</em> kepada seluruh akun pengguna yang ditugaskan ke role tersebut. Akun dengan role <strong>Super Admin</strong> selalu memiliki akses penuh ke setiap modul dan tidak dapat dibatasi.
        </div>
    </div>

</div>
@endsection
