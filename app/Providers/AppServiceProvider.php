<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        Order::observe(OrderObserver::class);

        $this->configureRateLimits();
    }

    /**
     * Public forms are throttled per IP.
     *
     * Placing an order takes stock off the shelf, so a flood of fake orders
     * would show the shop as sold out while nothing was really sold. The hourly
     * limit is the one that stops that; the per-minute limit stops a fast script.
     */
    private function configureRateLimits(): void
    {
        RateLimiter::for('checkout', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perHour(15)->by($request->ip()),
        ]);

        RateLimiter::for('contact', fn (Request $request) => [
            Limit::perMinute(2)->by($request->ip()),
            Limit::perHour(10)->by($request->ip()),
        ]);

        // Generous: adding to a cart is harmless, it just shouldn't be scripted.
        RateLimiter::for('cart', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        // Order lookup needs a number *and* a phone; throttling stops anyone
        // brute-forcing that pair to read someone else's address.
        RateLimiter::for('track', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(30)->by($request->ip()),
        ]);
    }
}
