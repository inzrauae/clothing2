<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Support\StoreCatalog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Older MySQL / MariaDB on shared hosting: keep indexed strings within key limits
        Schema::defaultStringLength(191);

        // Strong passwords for every admin account: 10+ characters with letters and numbers
        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        // Generate https:// links once HTTPS is enforced
        if (config('security.force_https')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('admin.partials.pagination');

        // Owners and admins manage settings & team; staff handle day-to-day work
        Gate::define('manage', fn (User $user) => $user->canManage());

        View::composer('layouts.store', function ($view) {
            $view->with('catalog', StoreCatalog::build())
                ->with('assetVersion', config('app.asset_version'));
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('nav', [
                'pending' => Order::query()->where('status', 'pending')->count(),
                'unread' => Message::query()->where('is_read', false)->count(),
            ])->with('assetVersion', config('app.asset_version'));
        });
    }
}
