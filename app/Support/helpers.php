<?php

use App\Models\Setting;
use Illuminate\Support\Str;

if (! function_exists('tr')) {
    /**
     * Pick the value for the current locale from a ['ru' => ..., 'tg' => ..., 'en' => ...] array.
     */
    function tr(mixed $value, ?string $locale = null): ?string
    {
        if (! is_array($value)) {
            return $value === null ? null : (string) $value;
        }

        $locale ??= app()->getLocale();

        foreach ([$locale, config('app.fallback_locale')] as $candidate) {
            if (filled($value[$candidate] ?? null)) {
                return $value[$candidate];
            }
        }

        return collect($value)->first(fn ($item) => filled($item));
    }
}

if (! function_exists('media')) {
    /**
     * Public URL for a stored file; absolute URLs are returned as is.
     */
    function media(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('localized_url')) {
    /**
     * The current page URL in another locale.
     */
    function localized_url(string $locale): string
    {
        $route = request()->route();

        if ($route && $route->getName() && array_key_exists('locale', $route->parameters())) {
            return route($route->getName(), array_merge($route->parameters(), ['locale' => $locale]));
        }

        return route('home', ['locale' => $locale]);
    }
}
