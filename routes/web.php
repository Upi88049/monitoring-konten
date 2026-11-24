<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContentImportExportController;
use App\Http\Controllers\ContentLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard KPI Analytics (Sub Menu 1 & Sub Menu 2)
    Route::get('/', [DashboardController::class, 'index'])->name('home')->middleware('can:dashboard.view');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:dashboard.view');
    Route::get('/dashboard/performa-tren', [DashboardController::class, 'overview'])->name('dashboard.overview')->middleware('can:dashboard.view');
    Route::get('/dashboard/komposisi-efektivitas', [DashboardController::class, 'composition'])->name('dashboard.composition')->middleware('can:dashboard.view');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index')->middleware('can:content_logs.view');

    // Master Log Konten (Granular Authorization)
    Route::get('/content-logs', [ContentLogController::class, 'index'])->name('content-logs.index')->middleware('can:content_logs.view');
    Route::post('/content-logs', [ContentLogController::class, 'store'])->name('content-logs.store')->middleware('can:content_logs.create');
    Route::get('/content-logs/{contentLog}', [ContentLogController::class, 'show'])->name('content-logs.show')->middleware('can:content_logs.view');
    Route::put('/content-logs/{contentLog}', [ContentLogController::class, 'update'])->name('content-logs.update')->middleware('can:content_logs.edit');
    Route::delete('/content-logs/{contentLog}', [ContentLogController::class, 'destroy'])->name('content-logs.destroy')->middleware('can:content_logs.delete');
    Route::post('/content-logs/{contentLog}/metrics', [ContentLogController::class, 'updateMetrics'])->name('content-logs.metrics')->middleware('can:content_logs.edit');
    Route::post('/pillars', [ContentLogController::class, 'storePillar'])->name('pillars.store')->middleware('can:content_logs.create');

    // Integrasi Data (Import & Export)
    Route::post('/import/upload', [ContentImportExportController::class, 'upload'])->name('import.upload')->middleware('can:import.create');
    Route::post('/import/default', [ContentImportExportController::class, 'importOriginal'])->name('import.default')->middleware('can:import.create');
    Route::get('/export/csv', [ContentImportExportController::class, 'exportCsv'])->name('export.csv')->middleware('can:export.view');

    // Riwayat Audit Trail (Log Aktivitas)
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('can:activity_logs.view');

    // Manajemen Role & Hak Akses (Super Admin)
    Route::resource('roles', RoleController::class)->except(['show'])->middleware('can:roles.manage');

    // Manajemen Pengguna (Super Admin)
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit'])->middleware('can:users.manage');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
