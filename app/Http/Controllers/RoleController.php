<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Tampilkan daftar role akun beserta ringkasan anggotanya.
     */
    public function index(): View
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('id', 'asc')
            ->get();

        $permissionsByGroup = Permission::all()->groupBy('feature_group');

        return view('roles.index', compact('roles', 'permissionsByGroup'));
    }

    /**
     * Form tambah role baru.
     */
    public function create(): View
    {
        $permissionsByGroup = Permission::all()->groupBy('feature_group');

        return view('roles.create', compact('permissionsByGroup'));
    }

    /**
     * Simpan role baru beserta hak aksesnya.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => strtolower($validated['name']),
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        ActivityLogger::log(
            action: 'role_create',
            module: 'Manajemen Role',
            description: "Membuat role baru: '{$role->label}' ({$role->name}).",
            properties: ['role_id' => $role->id, 'role_name' => $role->name]
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->label}' berhasil dibuat.");
    }

    /**
     * Tampilkan form matriks hak akses untuk role tertentu.
     */
    public function edit(Role $role): View
    {
        $permissionsByGroup = Permission::all()->groupBy('feature_group');
        $assignedPermissionIds = $role->permissions->pluck('id')->all();

        return view('roles.edit', compact('role', 'permissionsByGroup', 'assignedPermissionIds'));
    }

    /**
     * Perbarui data role dan matriks hak aksesnya.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $rules = [
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];

        if (! $role->is_system && $request->has('name')) {
            $rules['name'] = ['required', 'string', 'max:50', 'alpha_dash', 'unique:roles,name,'.$role->id];
        }

        $validated = $request->validate($rules);

        $role->label = $validated['label'];
        $role->description = $validated['description'] ?? null;
        if (! $role->is_system && isset($validated['name'])) {
            $role->name = strtolower($validated['name']);
        }
        $role->save();

        // Jika bukan super_admin, sinkronkan permissions sesuai pilihan form
        if ($role->name !== 'super_admin') {
            $role->permissions()->sync($validated['permissions'] ?? []);
        } else {
            // Super Admin selalu memiliki seluruh permission
            $allPermissions = Permission::pluck('id')->all();
            $role->permissions()->sync($allPermissions);
        }

        ActivityLogger::log(
            action: 'role_update',
            module: 'Manajemen Role',
            description: "Memperbarui hak akses role: '{$role->label}'.",
            properties: ['role_id' => $role->id, 'role_name' => $role->name, 'permissions_count' => count($validated['permissions'] ?? [])]
        );

        return redirect()->route('roles.index')
            ->with('success', "Hak akses untuk role '{$role->label}' berhasil diperbarui.");
    }

    /**
     * Hapus role kustom.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system || $role->name === 'super_admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Role sistem bawaan tidak dapat dihapus.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', "Role '{$role->label}' tidak dapat dihapus karena masih digunakan oleh {$role->users()->count()} pengguna.");
        }

        $label = $role->label;
        $role->permissions()->detach();
        $role->delete();

        ActivityLogger::log(
            action: 'role_delete',
            module: 'Manajemen Role',
            description: "Menghapus role: '{$label}'.",
            properties: ['role_name' => $label]
        );

        return redirect()->route('roles.index')
            ->with('success', "Role '{$label}' berhasil dihapus.");
    }
}
