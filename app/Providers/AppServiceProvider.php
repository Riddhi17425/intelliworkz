<?php

namespace App\Providers;

use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\Content;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Content::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // sidebar badge in the admin layout
        View::composer('admin.layouts.app', fn ($view) => $view->with('unreadInquiries', ContactInquiry::unread()->count()));

        // Authorization: editors manage page content & read inquiries; administrators also delete,
        // change company settings and manage users.
        Gate::define('manage-users', fn (User $u) => $u->isAdmin());
        Gate::define('manage-settings', fn (User $u) => $u->isAdmin());
        Gate::define('delete-content', fn (User $u) => $u->isAdmin());

        // Public contact / lead forms: a handful of submissions per minute per visitor
        RateLimiter::for('inquiries', fn (Request $r) => [
            Limit::perMinute(5)->by($r->ip()),
            Limit::perDay(40)->by($r->ip()),
        ]);
    }
}
