@php
    // A 404 may come before SetLocale has run: take the language from the /tj or /en prefix, Russian otherwise.
    $locale = collect(array_keys(config('app.locales')))
        ->first(fn ($locale) => locale_prefix($locale) !== '' && locale_prefix($locale) === request()->segment(1))
        ?? config('app.locale');
    app()->setLocale($locale);
@endphp

<x-layout :title="__('site.errors.404')" noindex>
    <section class="section">
        <div class="container-x py-16 text-center">
            <p class="text-8xl font-extrabold text-brand md:text-9xl">404</p>
            <h1 class="h-section mt-6">{{ __('site.errors.404') }}</h1>
            <p class="mt-4 text-ink/60">{{ __('site.errors.404_text') }}</p>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-primary">{{ __('site.actions.back_home') }}</a>
                <a href="{{ route('cars.index') }}" class="btn btn-outline">{{ __('site.actions.all_models') }}</a>
            </div>
        </div>
    </section>
</x-layout>
