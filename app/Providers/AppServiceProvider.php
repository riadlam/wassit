<?php

namespace App\Providers;

use App\Support\ChatUnreadSummary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

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
        View::composer('components.dashboard-nav', function ($view) {
            $view->with('chatUnread', ChatUnreadSummary::forUser(Auth::user()));
        });
    }
}
