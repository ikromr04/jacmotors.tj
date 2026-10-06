@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'overlayHeader' => false,
])

@php
    $siteName = __('site.brand');
    $fullTitle = $title ? "{$title} — {$siteName}" : $siteName.' — '.__('site.distributor');
    $description = Str::limit(strip_tags($description ?? __('site.distributor')), 160);
    $ogImage = $image ?: asset('favicon/apple-touch-icon.png');
    $ogLocales = ['ru' => 'ru_RU', 'tg' => 'tg_TJ', 'en' => 'en_US'];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="format-detection" content="telephone=no">
        <meta name="theme-color" content="#e60012">

        <title>{{ $fullTitle }}</title>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ url()->current() }}">
        @foreach (config('app.locales') as $locale => $name)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ localized_url($locale) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ localized_url(config('app.locale')) }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $title ?? $siteName }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'ru_RU' }}">
        <meta name="twitter:card" content="summary_large_image">

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{ $head ?? '' }}

        @if ($gaId = setting('google_analytics_id'))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag() { dataLayer.push(arguments); }
                gtag('js', new Date());
                gtag('config', @js($gaId));
            </script>
        @endif
        @if ($ymId = setting('yandex_metrika_id'))
            <script>
                window.ymId = @js((int) $ymId);
                (function (m, e, t, r, i, k, a) {
                    m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
                    m[i].l = 1 * new Date();
                    k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
                })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
                ym(window.ymId, 'init', { clickmap: true, trackLinks: true, accurateTrackBounce: true, webvisor: true });
            </script>
        @endif
    </head>

    <body class="flex min-h-screen flex-col">
        <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-white">
            {{ __('site.nav.skip') }}
        </a>

        @include('partials.header', ['overlay' => $overlayHeader])

        <main id="main" @class(['flex-1', 'pt-16 lg:pt-20' => ! $overlayHeader])>
            {{ $slot }}
        </main>

        @include('partials.footer')
        @include('partials.modals')

        <button
            type="button"
            class="btn btn-primary fixed right-4 bottom-4 z-40 size-14 !p-0 shadow-xl shadow-brand/30 md:right-6 md:bottom-6"
            aria-label="{{ __('site.actions.callback') }}"
            x-data
            x-on:click="$store.modal.open('callback')"
        >
            <span class="absolute inset-0 animate-ping rounded-full bg-brand/40 [animation-duration:2.5s]"></span>
            <x-glyph name="phone" class="relative size-6" />
        </button>
    </body>
</html>
