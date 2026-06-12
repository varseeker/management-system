<?php

namespace App\Providers;

use App\Models\Borrowing;
use App\Observers\BorrowingObserver;
use App\Support\PendingBorrowingCount;
use Illuminate\Support\Facades\URL;
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
        Borrowing::observe(BorrowingObserver::class);

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $pendingApprovalCount = 0;

            if ($user && in_array($user->role, ['admin', 'owner'], true)) {
                $pendingApprovalCount = PendingBorrowingCount::get();
            }

            $view->with('pendingApprovalCount', $pendingApprovalCount);
        });

        if ($this->app->environment('production')) {
            $appUrl = (string) config('app.url');

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
