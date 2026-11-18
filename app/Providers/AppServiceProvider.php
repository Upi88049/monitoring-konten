<?php

namespace App\Providers;

use App\Models\ContentLog;
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
            $view->with('pendingH30Count', ContentLog::pendingH30()->count());
        });
    }
}
