<?php

namespace App\Providers;

use App\Models\BackPanel\AboutUs;
use App\Models\BackPanel\Service;
use App\Models\BackPanel\SiteSetting;
use App\Models\BackPanel\TeamCategory;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer(['*'], function ($view) {
            $view->with('userProfile', Auth::user());
        });

        // Use Bootstrap for Pagination
        \Illuminate\Pagination\Paginator::useBootstrap();
    }
}
