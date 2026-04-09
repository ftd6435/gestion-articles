<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
        Blade::if('access', function (string $key, string $ability = 'view') {
            $user = Auth::user();
            if (!$user) {
                return false;
            }

            /** @var \App\Models\User $user */
            return $user->canAccess($key, $ability);
        });
    }
}
