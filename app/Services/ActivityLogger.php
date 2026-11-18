<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Catat aktivitas pengguna ke tabel activity_logs.
     *
     * @param  array<string, mixed>|null  $properties
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?array $properties = null,
        ?User $user = null
    ): ActivityLog {
        $currentUser = $user ?? Auth::user();

        return ActivityLog::create([
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser?->name ?? 'Guest/Sistem',
            'role_name' => $currentUser?->role?->label ?? ($currentUser?->isSuperAdmin() ? 'Super Admin' : null),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'properties' => $properties,
        ]);
    }
}
