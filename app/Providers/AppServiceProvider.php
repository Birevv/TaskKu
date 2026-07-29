<?php

namespace App\Providers;

use App\Models\User;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        FilamentTimezone::set(function (): string {
            $user = Auth::user();

            return $user instanceof User
                ? ($user->settings?->timezone ?? 'Asia/Jakarta')
                : 'Asia/Jakarta';
        });
    }
}
