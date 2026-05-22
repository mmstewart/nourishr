<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

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
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Fortify::confirmPasswordsUsing(function ($user, $password) {
            if (is_null($user->password)) {
                return true; // auto-confirm for Google OAuth users
            }

            return app(\Illuminate\Contracts\Hashing\Hasher::class)->check($password, $user->password);
        });

        // https://medium.com/@segunibidokun/difference-between-carbon-and-carbonimmutable-0c9689518773
        CarbonImmutable::macro('inUserTimezone', function () {
            return $this->tz(auth()->user()?->timezone ?? config('app.timezone_display'));
        });
    }
}
