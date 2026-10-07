<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\RequestCache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');

        // Scoped singleton for per-request in-memory cache
        $this->app->scoped(RequestCache::class, function () {
            return new RequestCache();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Auto-flush RequestCache at the end of each request/job to prevent memory leakage across cycles
        $this->app->terminating(function () {
            if ($this->app->resolved(RequestCache::class)) {
                $this->app->make(RequestCache::class)->flush();
            }
        });
    }
}
