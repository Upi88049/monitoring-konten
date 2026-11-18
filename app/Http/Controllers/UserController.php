<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Tampilkan daftar akun pengguna dan role yang ditugaskan.
     */
    public function index(Request $request): View
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        $users = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $roles = Role::orderBy('id', 'asc')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Daftarkan akun pengguna baru dan tentukan rolenya.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'email_verified_at' => now(),
        ]);

        ActivityLogger::log(
            action: 'user_create',
            module: 'Manajemen Pengguna',
            description: "Menambahkan pengguna baru: '{$newUser->name}' ({$newUser->email}) dengan role '{$newUser->role?->label}'.",
            properties: ['user_id' => $newUser->id, 'email' => $newUser->email, 'role' => $newUser->role?->label]
        );

        return redirect()->route('users.index')
            ->with('success', "Akun pengguna '{$validated['name']}' berhasil dibuat.");
    }

    /**
     * Perbarui data profil akun dan penugasan role pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $newRole = Role::findOrFail($validated['role_id']);

        // Cegah Super Admin mencabut role Super Admin akunnya sendiri jika hanya tersisa 1 Super Admin
        if ($user->id === auth()->id() && $user->isSuperAdmin() && $newRole->name !== 'super_admin') {
            $superAdminCount = User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Anda tidak dapat menurunkan role akun sendiri karena merupakan satu-satunya Super Admin di sistem.');
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        ActivityLogger::log(
            action: 'user_update',
            module: 'Manajemen Pengguna',
            description: "Memperbarui data akun pengguna: '{$user->name}' ({$user->email}) - Role: {$user->role?->label}.",
            properties: ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role?->label]
        );

        return redirect()->route('users.index')
            ->with('success', "Data akun pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        if ($user->isSuperAdmin()) {
            $superAdminCount = User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Tidak dapat menghapus akun ini karena merupakan satu-satunya Super Admin di sistem.');
            }
        }

        $name = $user->name;
        $email = $user->email;
        $user->delete();

        ActivityLogger::log(
            action: 'user_delete',
            module: 'Manajemen Pengguna',
            description: "Menghapus akun pengguna: '{$name}' ({$email}).",
            properties: ['deleted_user' => $name, 'email' => $email]
        );

        return redirect()->route('users.index')
            ->with('success', "Akun pengguna '{$name}' berhasil dihapus.");
    }
}
