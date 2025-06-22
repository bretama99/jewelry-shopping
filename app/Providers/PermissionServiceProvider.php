<?php
// File: app/Providers/PermissionServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use App\Models\Role;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom Blade directives
        $this->registerBladeDirectives();

        // Register Gates for permissions
        $this->registerGates();
    }

    /**
     * Register custom Blade directives for permissions
     */
    protected function registerBladeDirectives(): void
    {
        // @canPermission directive
        Blade::directive('canPermission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$permission})): ?>";
        });

        Blade::directive('endcanPermission', function () {
            return '<?php endif; ?>';
        });

        // @hasRole directive
        Blade::directive('hasRole', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        Blade::directive('endhasRole', function () {
            return '<?php endif; ?>';
        });

        // @hasAnyRole directive
        Blade::directive('hasAnyRole', function ($roles) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$roles})): ?>";
        });

        Blade::directive('endhasAnyRole', function () {
            return '<?php endif; ?>';
        });

        // @admin directive
        Blade::directive('admin', function () {
            return '<?php if(auth()->check() && auth()->user()->canAccessAdmin()): ?>';
        });

        Blade::directive('endadmin', function () {
            return '<?php endif; ?>';
        });

        // @superadmin directive
        Blade::directive('superadmin', function () {
            return '<?php if(auth()->check() && auth()->user()->isSuperAdmin()): ?>';
        });

        Blade::directive('endsuperadmin', function () {
            return '<?php endif; ?>';
        });

        // @role directive with parameters
        Blade::directive('role', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>";
        });

        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Register Gates for all available permissions
     */
    protected function registerGates(): void
    {
        // Define gates for each permission
        $permissions = Role::getAvailablePermissions();

        foreach ($permissions as $permission => $description) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }

        // Define role-based gates
        Gate::define('admin', function ($user) {
            return $user->canAccessAdmin();
        });

        Gate::define('super-admin', function ($user) {
            return $user->isSuperAdmin();
        });

        // Before hook - Super admins can do everything
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });
    }
}
