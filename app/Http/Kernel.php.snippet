<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * Every @can('permission.key') / $user->can('permission.key') call
         * anywhere in the app (Blade, controllers, form requests) is routed
         * through our Role->permissions JSON check. This is what makes the
         * @can directives in the sidebar/views work without defining a
         * Policy class per model — appropriate for a permission-key based
         * system rather than a per-model-action ACL.
         */
        Gate::before(function ($user, string $ability) {
            return $user->hasPermission($ability) ?: null;
        });

        /**
         * IMPORTANT: SetLocale (the bn/en language-switch middleware)
         * is NOT registered here. An earlier version of this file tried
         * to auto-register it via $kernel->pushMiddlewareToGroup(), but
         * that method only exists on Laravel 10's old-style Kernel —
         * Laravel 11/12 removed it when middleware registration moved
         * to bootstrap/app.php, so calling it there throws "Call to
         * undefined method ...Kernel::pushMiddlewareToGroup()" (exactly
         * the error this caused). There is no reliable way to register
         * middleware from inside a service provider's boot() that works
         * identically across Laravel 10 AND 11/12, so this really does
         * need the one manual step below — see bootstrap/app.php.snippet
         * (Laravel 11/12) or app/Http/Kernel.php.snippet (Laravel 10).
         */
    }
}
