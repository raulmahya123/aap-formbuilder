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
        // SSL terminates at Apache/proxy in production. Keep generated form,
        // asset, and redirect URLs on HTTPS so uploads are not downgraded to HTTP.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
