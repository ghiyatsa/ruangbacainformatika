<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Register any application services.
     * Horizon is only active when the queue driver is Redis.
     */
    public function register(): void
    {
        if (config('queue.default') !== 'redis') {
            return;
        }

        $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);

        parent::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('queue.default') !== 'redis') {
            return;
        }

        parent::boot();
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return in_array(optional($user)->email, [
                //
            ]);
        });
    }
}
