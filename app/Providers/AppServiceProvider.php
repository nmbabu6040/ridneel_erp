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
    }
}
