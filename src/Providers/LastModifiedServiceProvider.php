<?php

declare(strict_types=1);

namespace Kudashevs\LaravelLastModified\Providers;

use Illuminate\Support\ServiceProvider;

class LastModifiedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/last-modified.php' => config_path('last-modified.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/last-modified.php', 'last-modified');
    }
}
