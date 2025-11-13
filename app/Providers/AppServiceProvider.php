<?php

namespace App\Providers;

use App\Http\Middleware\EncryptCookies;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['request']->server->set('HTTPS','on');
        Paginator::useBootstrapFour();
        EncryptCookies::except('theme-dark');
    }
}
