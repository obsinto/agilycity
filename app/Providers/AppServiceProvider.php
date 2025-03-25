<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;

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
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        Carbon::setLocale(config('app.locale'));
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
    }
}
