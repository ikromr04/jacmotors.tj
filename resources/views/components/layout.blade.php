@props([
    'title' => null,
    'description' => null,
    // Social preview: a file on the public disk (or an absolute URL); the branded default otherwise.
    'image' => null,
    // Fit the whole image with margins instead of cropping it (cut-out car pictures).
    'imageContain' => false,
    'type' => 'website',
    'publishedAt' => null,
    'overlayHeader' => false,
    // Keep the page out of search results (error pages).
    'noindex' => false,
    // Extra schema.org nodes for this page, published next to the dealer description.
    'schema' => [],
])

@php
    $siteName = __('site.brand');
    $fullTitle = $title ? "{$title} — {$siteName}" : $siteName.' — '.__('site.distributor');
    $description = Str::limit(strip_tags($description ?? __('site.distributor')), 160);
    $ogImage = \App\Support\OgImage::url($image, $imageContain);
    $ogLocales = ['ru' => 'ru_RU', 'tg' => 'tg_TJ', 'en' => 'en_US'];
    // Tracking parameters and ?page=1 must not create duplicates; later list pages are pages of their own.
    $page = (int) request()->query('page');
    $canonical = $page > 1 ? url()->current().'?page='.$page : url()->current();

    $dealer = array_filter([
        '@type' => 'AutoDealer',
        '@id' => route('home').'#dealer',
        'name' => $siteName,
        'description' => __('site.distributor'),
        'url' => route('home'),
        'logo' => asset('favicon/apple-touch-icon.png'),
        'image' => asset('images/og-default.jpg'),
        'telephone' => setting('phone'),
        'email' => setting('email'),
        'address' => tr(setting('address')),
        'brand' => ['@type' => 'Brand', 'name' => 'JAC'],
        'sameAs' => array_values(array_filter((array) setting('socials', []))) ?: null,
    ]);
    $structuredData = ['@context' => 'https://schema.org', '@graph' => [$dealer, ...$schema]];
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
        @if ($noindex)
            <meta name="robots" content="noindex, follow">
        @else
            <link rel="canonical" href="{{ $canonical }}">
            @foreach (config('app.locales') as $locale => $name)
                <link rel="alternate" hreflang="{{ $locale }}" href="{{ localized_url($locale) }}">
            @endforeach
            <link rel="alternate" hreflang="x-default" href="{{ localized_url(config('app.locale')) }}">
        @endif

        <meta property="og:type" content="{{ $type }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $title ?? $siteName }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="{{ \App\Support\OgImage::WIDTH }}">
        <meta property="og:image:height" content="{{ \App\Support\OgImage::HEIGHT }}">
        <meta property="og:image:alt" content="{{ $title ?? $siteName }}">
        <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'ru_RU' }}">
        @foreach (array_diff_key($ogLocales, [app()->getLocale() => true]) as $ogLocale)
            <meta property="og:locale:alternate" content="{{ $ogLocale }}">
        @endforeach
        @if ($type === 'article' && $publishedAt)
            <meta property="article:published_time" content="{{ $publishedAt->toAtomString() }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title ?? $siteName }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        {{-- Same set and order as the previous site: browsers that take the last icon get the .ico. --}}
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

        {{ $head ?? '' }}

        @if (app()->isProduction() && $gaId = setting('google_analytics_id'))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag() { dataLayer.push(arguments); }
                gtag('js', new Date());
                gtag('config', @js($gaId));
            </script>
        @endif
        @if (app()->isProduction() && $ymId = setting('yandex_metrika_id'))
            <script>
                window.ymId = @js((int) $ymId);
                (function (m, e, t, r, i, k, a) {
                    m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
                    m[i].l = 1 * new Date();
                    for (var j = 0; j < document.scripts.length; j++) {
                        if (document.scripts[j].src === r) { return; }
                    }
                    k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
                })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');
                ym(window.ymId, 'init', { clickmap: true, trackLinks: true, accurateTrackBounce: true, webvisor: true });
            </script>
        @endif
    </head>

    <body class="flex min-h-screen flex-col">
        @if (app()->isProduction() && $ymId = setting('yandex_metrika_id'))
            <noscript><div><img src="https://mc.yandex.ru/watch/{{ (int) $ymId }}" style="position:absolute; left:-9999px;" alt=""></div></noscript>
        @endif
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
