<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // The baseline limiter for every API route (enabled in
        // bootstrap/app.php via throttleApi()) — 60 req/min per
        // authenticated user, falling back to per-IP for guests. This
        // project's skeleton never defined it, so enabling the throttle
        // middleware without this would 500 every single API request.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Tighter limit than the general API throttle, specifically for
        // login/register, which are the two endpoints anyone can hit
        // without a token — the obvious target for a credential-stuffing
        // or mass-signup bot. Keyed by IP since there's no authenticated
        // user yet at this point.
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
