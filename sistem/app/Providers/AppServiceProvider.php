<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerPolicies();
        //check that app is local
        // if ($this->app->isLocal()) {
        //     //if local register your services you require for development
        //     $this->app->register('Barryvdh\Debugbar\ServiceProvider');
        // } else {
        //     //else register your services you require for production
        //     $this->app['request']->server->set('HTTPS', true);
        // }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('superadmin', function ($user) {
            return $user->role === 'superadmin';
        });
    }
}
