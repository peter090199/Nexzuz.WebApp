<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\DataAccess\ClientsDAL;
use App\Business\ClientsBAL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ClientsDAL::class, function ($app) {
            return new ClientsDAL();
        });

        $this->app->bind(ClientsBAL::class, function ($app) {
            return new ClientsBAL($app->make(ClientsDAL::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
