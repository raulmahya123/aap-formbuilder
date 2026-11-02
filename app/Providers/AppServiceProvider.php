<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        \Illuminate\Pagination\Paginator::defaultView('pagination.standard');
        // SSL terminates at Apache/proxy in production. Keep generated form,
        // asset, and redirect URLs on HTTPS there, but don't send local dev
        // requests to HTTPS when `artisan serve` only speaks plain HTTP.
        $host = request()->getHost();
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.test');

        if ($this->app->environment('production') && ! $isLocalHost) {
            URL::forceScheme('https');
        }
    }
}
