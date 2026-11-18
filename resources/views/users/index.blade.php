@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">

    <!-- Filter & Action Toolbar -->
    <div class="card p-4 sm:p-5">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Box -->
                <div class="relative min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email pengguna..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Filter Role -->
                <select name="role_id" onchange="this.form.submit()" class="text-xs bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'role_id']))
                    <a href="{{ route('users.index') }}" class="text-xs text-gray-500 hover:text-purple-700 underline font-medium">Reset Filter</a>
                @endif
                <button type="button" onclick="document.getElementById('createUserModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold bg-purple-700 hover:bg-purple-800 text-white rounded-lg shadow-xs transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    <span>Tambah Pengguna</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
        <div class="p-4 sm:px-6 sm:py-4 flex items-center justify-between border-b border-gray-100">
            <div>
                <h3 class="card-title text-base font-bold text-gray-900">Daftar Akun Pengguna</h3>
                <p class="card-subtitle text-xs text-gray-500">
                    Total {{ $users->total() }} akun terdaftar dalam sistem
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Email Akun</th>
                        <th class="py-3 px-4 text-center">Role Penugasan</th>
                        <th class="py-3 px-4 text-center">Status Verifikasi</th>
                        <th class="py-3 px-4 text-center">Tgl Terdaftar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-gray-900 flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-purple-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div>{{ $u->name }}</div>
                                    @if($u->id === auth()->id())
                                        <span class="text-[10px] text-purple-700 font-semibold">(Akun Anda)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-gray-600 font-mono">{{ $u->email }}</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u->role?->name === 'super_admin')
                                    <span class="bg-purple-100 text-purple-800 border border-purple-200 text-xs font-extrabold px-2.5 py-0.5 rounded-full">
                                        🛡️ Super Admin
                                    </span>
                                @elseif($u->role?->name === 'admin_konten')
                                    <span class="bg-teal-50 text-teal-800 border border-teal-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                        Admin Konten
                                    </span>
                                @elseif($u->role?->name === 'editor_konten')
                                    <span class="bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                        Editor Konten
                                    </span>
                                @elseif($u->role?->name === 'viewer')
                                    <span class="bg-gray-100 text-gray-700 border border-gray-300 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        Viewer (Lihat Saja)
                                    </span>
                                @else
                                    <span class="bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        {{ $u->role?->label ?? 'Belum ada role' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u->email_verified_at)
                                    <span class="text-green-700 bg-green-50 px-2 py-0.5 rounded-md font-semibold text-[11px]">Terverifikasi</span>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">Belum</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center text-gray-500 font-mono">
                                {{ $u->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Edit User Button -->
                                    <button type="button" onclick="openEditUserModal({{ json_encode($u) }})" class="p-1.5 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition cursor-pointer" title="Edit Akun & Ubah Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Delete User Button -->
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna \'{{ $u->name }}\'?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Hapus Akun">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                Tidak ada data pengguna yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah User Baru -->
<div id="createUserModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Tambah Akun Pengguna Baru</h3>
            <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="py-4 space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Nama Lengkap" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email *</label>
                <input type="email" name="email" required placeholder="user@dharmagroup.co.id" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Role Akun *</label>
                <select name="role_id" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none cursor-pointer">
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->label }} ({{ $r->name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi (Password) *</label>
                <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-lg shadow-sm cursor-pointer">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div id="editUserModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">Edit Akun Pengguna</h3>
                <p id="editUserSubtitle" class="text-xs text-gray-500 truncate max-w-xs"></p>
            </div>
            <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
        </div>

        <form id="editUserForm" method="POST" class="py-4 space-y-3.5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                <input type="text" id="eu_name" name="name" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Email *</label>
                <input type="email" id="eu_email" name="email" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Role Penugasan *</label>
                <select id="eu_role_id" name="role_id" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none cursor-pointer">
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->label }} ({{ $r->name }})</option>
                    @endforeach
                </select>
                <span class="text-[11px] text-gray-400">Hak akses akun ini akan mengikuti izin yang diatur pada role tersebut.</span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru (Opsional)</label>
                <input type="password" name="password" minlength="8" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-lg shadow-sm cursor-pointer">
                    Perbarui Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        document.getElementById('editUserSubtitle').textContent = user.email;
        document.getElementById('editUserForm').action = '/users/' + user.id;

        document.getElementById('eu_name').value = user.name || '';
        document.getElementById('eu_email').value = user.email || '';
        document.getElementById('eu_role_id').value = user.role_id || '';

        document.getElementById('editUserModal').classList.remove('hidden');
    }
</script>
@endsection
