<?php

namespace App\Providers;

use App\Models\Car;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
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
        // route('cars.show', $car) resolves to the page in the current language ("ru.cars.show", "tg.cars.show"...);
        // pass 'locale' to link to another one.
        URL::resolveMissingNamedRoutesUsing(function (string $name, mixed $parameters, bool $absolute): ?string {
            $parameters = Arr::wrap($parameters);
            $locale = Arr::pull($parameters, 'locale') ?? app()->getLocale();
            $locale = array_key_exists($locale, config('app.locales')) ? $locale : config('app.locale');

            return Route::has("{$locale}.{$name}") ? URL::route("{$locale}.{$name}", $parameters, $absolute) : null;
        });

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
