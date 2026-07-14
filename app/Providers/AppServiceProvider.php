<?php

namespace App\Providers;

use App\Models\BackPanel\AboutUs;
use App\Models\BackPanel\Service;
use App\Models\BackPanel\SiteSetting;
use App\Models\BackPanel\TeamCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('ApiResponse', function () {
            return new \App\Helpers\ApiResponse();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['*'], function ($view) {
            $view->with('userProfile', Auth::user());

            $orgLogo = null;
            $orgName = null;

            if (Auth::check()) {
                $userOrg = DB::table('userorganizations as uo')
                    ->join('organizations as o', 'o.id', '=', 'uo.orgid')
                    ->where('uo.userid', Auth::id())
                    ->select('o.logo', 'o.name')
                    ->first();

                $orgLogo = $userOrg->logo ?? null;
                $orgName = $userOrg->name ?? null;
            }

            $view->with('orgLogo', $orgLogo);
            $view->with('orgName', $orgName);
        });

        \Illuminate\Pagination\Paginator::useBootstrap();
    }
}
