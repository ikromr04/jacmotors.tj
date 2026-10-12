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

if (! function_exists('site_text')) {
    /**
     * Interface text editable in the admin ("texts" setting), falling back to lang/{locale}/site.php.
     */
    function site_text(string $key): string
    {
        $locale = app()->getLocale();

        return filled($text = setting("texts.{$key}.{$locale}")) ? $text : __("site.{$key}");
    }
}

if (! function_exists('locale_prefix')) {
    /**
     * URL prefix of a language: none for the default one, "tj" for Tajik, the language code otherwise.
     */
    function locale_prefix(string $locale): string
    {
        if ($locale === config('app.locale')) {
            return '';
        }

        return config("app.locale_prefixes.{$locale}", $locale);
    }
}

if (! function_exists('localized_url')) {
    /**
     * The current page URL in another locale.
     */
    function localized_url(string $locale): string
    {
        $route = request()->route();
        $name = (string) $route?->getName();

        // Public pages are named after their language: "tg.cars.show" → "cars.show" in $locale.
        if (array_key_exists(Str::before($name, '.'), config('app.locales'))) {
            return route(Str::after($name, '.'), [...$route->parameters(), 'locale' => $locale]);
        }

        return route('home', ['locale' => $locale]);
    }
}
