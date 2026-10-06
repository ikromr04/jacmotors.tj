<?php

namespace App\Providers;

use App\Models\Car;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
        RateLimiter::for('leads', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perDay(30)->by($request->ip()),
        ]);

        // Header menu and lead forms need the model list on every page.
        View::composer(['partials.*', 'home', 'cars.*', 'service', 'support'], function ($view) {
            $view->with('navCars', once(fn () => Car::query()->published()->with('category')->get()));
        });
    }
}
