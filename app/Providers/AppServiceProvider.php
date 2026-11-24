<?php

namespace App\Providers;

use App\Models\ContentLog;
use App\Models\Pillar;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin selalu memiliki akses penuh tanpa batas
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        // Otorisasi dinamis berdasarkan role & permissions pengguna
        Gate::after(function (User $user, string $ability, ?bool $result) {
            if ($result !== null) {
                return $result;
            }

            return $user->hasPermission($ability);
        });

        View::composer('layouts.app', function ($view) {
            $pendingH30 = ContentLog::pendingH30()->get();
            $dueReminderContents = $pendingH30->filter(function ($item) {
                return $item->h30_due_date && $item->h30_due_date->lte(now()->endOfDay());
            })->sortBy('h30_due_date')->values();

            $view->with('pendingH30Count', $pendingH30->count())
                ->with('dueReminderContents', $dueReminderContents)
                ->with('availablePillars', Pillar::allPillars());
        });
    }
}
