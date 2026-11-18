@extends('layouts.app')

@section('title', 'Atur Hak Akses Role: ' . $role->label)
@section('page_title', 'Atur Hak Akses Role')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-700 hover:text-purple-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Daftar Role</span>
        </a>
    </div>

    @if($role->name === 'super_admin')
        <!-- Special Banner for Super Admin -->
        <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 flex items-start gap-3">
            <span class="text-xl">🛡️</span>
            <div>
                <h4 class="text-xs font-bold text-purple-900">Peran Sistem: Super Admin</h4>
                <p class="text-xs text-purple-700 mt-0.5">
                    Role ini memiliki hak akses penuh tanpa batas ke seluruh modul sistem secara permanen. Pengaturan matriks permissions terkunci demi menjaga integritas sistem.
                </p>
            </div>
        </div>
    @endif

    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card p-6 space-y-6">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-gray-900">Pengaturan Role: {{ $role->label }}</h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $role->is_system ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $role->is_system ? 'System Role' : 'Custom Role' }}
                    </span>
                </div>
                <p class="text-xs text-gray-500">Sesuaikan nama role dan centang izin fitur (apakah hanya View Only, bisa Tambah, Edit, atau Hapus).</p>
            </div>

            <!-- Role Identity Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="label" class="block text-xs font-semibold text-gray-700 mb-1">Nama Tampilan Role *</label>
                    <input type="text" id="label" name="label" value="{{ old('label', $role->label) }}" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>

                <div>
                    <label for="name" class="block text-xs font-semibold text-gray-700 mb-1">Kode Unik / Slug</label>
                    <input type="text" id="name" name="name" value="{{ $role->name }}" {{ $role->is_system ? 'readonly' : '' }} class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg font-mono {{ $role->is_system ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:ring-2 focus:ring-purple-500' }}">
                    <span class="text-[11px] text-gray-400">{{ $role->is_system ? 'Kode role sistem bawaan tidak dapat diubah.' : 'Hanya huruf kecil, angka, dan underscore.' }}</span>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Role</label>
                    <textarea id="description" name="description" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">{{ old('description', $role->description) }}</textarea>
                </div>
            </div>

            <!-- Permission Matrix Section -->
            <div class="pt-4 border-t border-gray-100 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <span>Matriks Hak Akses Fitur (Permissions)</span>
                        </h3>
                        <p class="text-xs text-gray-500">Tentukan apakah role ini berstatus View Only, atau memiliki izin Tambah / Edit / Hapus.</p>
                    </div>

                    @if($role->name !== 'super_admin')
                        <!-- Preset Quick Action Buttons -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="applyPreset('all')" class="px-2.5 py-1 text-[11px] font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg transition cursor-pointer">
                                ✓ Pilih Semua
                            </button>
                            <button type="button" onclick="applyPreset('view_only')" class="px-2.5 py-1 text-[11px] font-semibold bg-teal-50 text-teal-800 hover:bg-teal-100 rounded-lg transition cursor-pointer">
                                👁️ Preset: Hanya Lihat (View Only)
                            </button>
                            <button type="button" onclick="applyPreset('editor')" class="px-2.5 py-1 text-[11px] font-semibold bg-amber-50 text-amber-800 hover:bg-amber-100 rounded-lg transition cursor-pointer">
                                ✏️ Preset: Editor (Lihat, Tambah, Edit)
                            </button>
                            <button type="button" onclick="applyPreset('none')" class="px-2.5 py-1 text-[11px] font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                                ✕ Kosongkan
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Matrix Table -->
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 min-w-[220px]">Modul & Fitur</th>
                                <th class="py-3 px-4 min-w-[280px]">Penjelasan Izin</th>
                                <th class="py-3 px-4 text-center w-28">Tipe Aksi</th>
                                <th class="py-3 px-4 text-center w-24">Status Akses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($permissionsByGroup as $groupName => $perms)
                                <tr class="bg-purple-50/40">
                                    <td colspan="4" class="py-2.5 px-4 font-extrabold text-purple-900 text-xs uppercase tracking-wider flex items-center justify-between">
                                        <span>📁 {{ $groupName }}</span>
                                        @if($role->name !== 'super_admin')
                                            <button type="button" onclick="toggleGroup('{{ Str::slug($groupName) }}')" class="text-[11px] text-purple-700 hover:underline font-semibold normal-case cursor-pointer">
                                                Pilih Semua Grup Ini
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                @foreach($perms as $perm)
                                    @php
                                        $isChecked = $role->name === 'super_admin' || in_array($perm->id, $assignedPermissionIds, true);
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition group-row-{{ Str::slug($groupName) }}">
                                        <td class="py-3 px-4 font-semibold text-gray-900 pl-8">
                                            {{ $perm->label }}
                                        </td>
                                        <td class="py-3 px-4 text-gray-500">
                                            {{ $perm->description ?: '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($perm->action === 'view')
                                                <span class="bg-teal-50 text-teal-800 border border-teal-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    👁️ View Only
                                                </span>
                                            @elseif($perm->action === 'create')
                                                <span class="bg-green-50 text-green-800 border border-green-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    ➕ Tambah
                                                </span>
                                            @elseif($perm->action === 'edit')
                                                <span class="bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    ✏️ Edit
                                                </span>
                                            @elseif($perm->action === 'delete')
                                                <span class="bg-red-50 text-red-800 border border-red-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    🗑️ Hapus
                                                </span>
                                            @else
                                                <span class="bg-gray-100 text-gray-800 border border-gray-300 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                    ⚙️ Kelola
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($role->name === 'super_admin')
                                                <input type="checkbox" checked disabled class="w-4 h-4 text-purple-600 rounded border-gray-300 cursor-not-allowed">
                                                <input type="hidden" name="permissions[]" value="{{ $perm->id }}">
                                            @else
                                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                    {{ $isChecked ? 'checked' : '' }}
                                                    data-action="{{ $perm->action }}"
                                                    data-group="{{ Str::slug($groupName) }}"
                                                    class="perm-checkbox w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500 cursor-pointer">
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition">
                    Kembali
                </a>
                <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-lg shadow-sm transition cursor-pointer">
                    Simpan Perubahan Hak Akses
                </button>
            </div>
        </div>
    </form>

</div>

@if($role->name !== 'super_admin')
<script>
    function applyPreset(preset) {
        const checkboxes = document.querySelectorAll('.perm-checkbox');
        checkboxes.forEach(cb => {
            const action = cb.dataset.action;
            if (preset === 'all') {
                cb.checked = true;
            } else if (preset === 'none') {
                cb.checked = false;
            } else if (preset === 'view_only') {
                cb.checked = (action === 'view');
            } else if (preset === 'editor') {
                cb.checked = (action === 'view' || action === 'create' || action === 'edit');
            }
        });
    }

    function toggleGroup(groupSlug) {
        const checkboxes = document.querySelectorAll(`.perm-checkbox[data-group="${groupSlug}"]`);
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
    }
</script>
@endif
@endsection
