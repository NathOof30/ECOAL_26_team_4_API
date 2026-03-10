<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Criteria;
use App\Models\Item;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\CollectionPolicy;
use App\Policies\CriteriaPolicy;
use App\Policies\ItemPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Collection::class, CollectionPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Criteria::class, CriteriaPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
