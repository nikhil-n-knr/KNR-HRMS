<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
        \App\Models\BugTicket::class => \App\Policies\BugTicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->roles->contains('name', 'Super Admin') ? true : null;
        });

        // Dynamic permission check
        // Resolves abilities passed to `can:` middleware or `@can` blade directives
        Gate::before(function ($user, $ability) {
            // First pass check for Super Admin
            if ($user->roles->contains('name', 'Super Admin')) {
                return true;
            }

            // If the ability looks like a granular permission (e.g. 'module.sub.action')
            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability) ? true : null;
            }
            
            return null; // Fallthrough to policies if not matched
        });
    }
}
