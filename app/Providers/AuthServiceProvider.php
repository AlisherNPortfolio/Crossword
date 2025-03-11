<?php

namespace App\Providers;

use App\Models\Competition;
use App\Models\Crossword;
use App\Models\User;
use App\Policies\CompetitionPolicy;
use App\Policies\CrosswordPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Crossword::class, CrosswordPolicy::class);
        Gate::policy(Competition::class, CompetitionPolicy::class);

        Gate::define('access-dashboard', function (User $user) {
            return $user->hasPermission('dashboard.access');
        });
    }
}
