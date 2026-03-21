<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (env('VERCEL_STORAGE_PATH')) {
            $this->app->useStoragePath(env('VERCEL_STORAGE_PATH'));
        }
    }

    public function boot(): void
    {
        //
    }
}
