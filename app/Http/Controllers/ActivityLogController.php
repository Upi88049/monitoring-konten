<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan riwayat audit trail log aktivitas pengguna.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user.role')->latest();

        // 1. Filter pencarian teks
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // 2. Filter modul
        if ($request->filled('module')) {
            $query->byModule($request->input('module'));
        }

        // 3. Filter aksi
        if ($request->filled('action')) {
            $query->byAction($request->input('action'));
        }

        // 4. Filter pengguna
        if ($request->filled('user_id')) {
            $query->byUser($request->input('user_id'));
        }

        // 5. Filter tanggal mulai
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        // 6. Filter tanggal akhir
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $logs = $query->paginate(20)->withQueryString();

        // Data untuk opsi dropdown filter
        $modules = ActivityLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::orderBy('name')->get();

        return view('activity-logs.index', compact('logs', 'modules', 'actions', 'users'));
    }
}
