<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Policies\BookPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Observers\BookObserver;
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
        // Register Model Observers
        Book::observe(BookObserver::class);

        // Register Policies
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);

        // IP-4: log every email sent through the queue, with its delivery time.
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Notifications\Events\NotificationSent::class,
            [\App\Integrations\Email\LogNotificationDelivery::class, 'handleSent']
        );
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Notifications\Events\NotificationFailed::class,
            [\App\Integrations\Email\LogNotificationDelivery::class, 'handleFailed']
        );

        // API Rate Limiting Tiers (Lab 6)
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            if (!$user) {
                return Limit::perMinute(30)->by($request->ip()); // Visitors
            }

            if ($user->isAdmin()) {
                return Limit::perMinute(1000)->by($user->id); // Admin
            }

            return Limit::perMinute(60)->by($user->id); // Buyers and sellers
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
