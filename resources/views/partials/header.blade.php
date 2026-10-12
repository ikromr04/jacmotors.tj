@php
    $phone = setting('phone');
    $carsByCategory = $navCars->groupBy(fn ($car) => $car->category?->tr('name') ?? '');
    $links = [
        ['label' => __('site.nav.promotions'), 'url' => route('promotions.index'), 'active' => request()->routeIs('*.promotions.*')],
        ['label' => __('site.nav.service'), 'url' => route('service'), 'active' => request()->routeIs('*.service')],
        ['label' => __('site.nav.dealers'), 'url' => route('dealers'), 'active' => request()->routeIs('*.dealers')],
    ];
    $ownerLinks = [
        ['label' => __('site.nav.manuals'), 'url' => route('manuals')],
        ['label' => __('site.nav.guarantee'), 'url' => route('pages.show', 'guarantee')],
        ['label' => __('site.nav.news'), 'url' => route('news.index')],
        ['label' => __('site.nav.about'), 'url' => route('pages.show', 'about')],
        ['label' => __('site.nav.support'), 'url' => route('support')],
    ];
@endphp

<header
    x-data="header(@js((bool) $overlay))"
    @class([
        'fixed inset-x-0 top-0 z-50 transition-[translate,background-color,color,box-shadow] duration-300',
        'text-white' => $overlay,
        'bg-white/95 text-ink shadow-[0_1px_0_rgba(0,0,0,0.06)] backdrop-blur-md' => ! $overlay,
    ])
    x-bind:class="{
        '-translate-y-full': hidden,
        'bg-white/95 text-ink shadow-[0_1px_0_rgba(0,0,0,0.06)] backdrop-blur-md': solid,
        'text-white': !solid,
    }"
>
    <div class="container-x flex h-16 items-center gap-6 lg:h-20">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="{{ __('site.brand') }}">
            <x-logo.koinot class="h-8 w-auto lg:h-10" />
            <span class="h-6 w-px bg-current opacity-25 lg:h-7" aria-hidden="true"></span>
            <x-logo.jac class="h-8 w-auto lg:h-9" />
        </a>

        <nav class="hidden flex-1 items-center gap-1 whitespace-nowrap xl:flex" aria-label="{{ __('site.nav.menu') }}">
            {{-- Models mega menu --}}
            <div class="group/mega" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
                <a
                    href="{{ route('cars.index') }}"
                    class="flex items-center gap-1 rounded-full px-4 py-2 text-sm font-semibold transition hover:opacity-70"
                    x-on:focus="open = true"
                >
                    {{ __('site.nav.models') }}
                    <x-glyph name="chevron-down" class="size-4 transition" x-bind:class="open && 'rotate-180'" />
                </a>

                <div
                    x-cloak
                    x-show="open"
                    x-transition.opacity.duration.200ms
                    class="absolute inset-x-0 top-full border-t border-line bg-white text-ink shadow-2xl shadow-black/10"
                >
                    <div class="container-x grid grid-cols-4 gap-8 py-8">
                        @foreach ($carsByCategory as $category => $cars)
                            <div>
                                <p class="eyebrow mb-4">{{ $category }}</p>
                                <ul class="space-y-1">
                                    @foreach ($cars as $car)
                                        <li>
                                            <a href="{{ route('cars.show', $car) }}" class="group/car -mx-2 flex items-center gap-3 rounded-xl p-2 transition hover:bg-surface">
                                                @if ($car->card_image)
                                                    <img src="{{ media($car->card_image) }}" alt="" class="h-10 w-20 object-contain transition group-hover/car:scale-105" loading="lazy">
                                                @endif
                                                <span class="text-sm font-semibold">{{ $car->name }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-line bg-surface">
                        <div class="container-x flex items-center justify-between py-4">
                            <a href="{{ route('cars.index') }}" class="link-arrow">
                                {{ __('site.actions.all_models') }} <x-glyph name="arrow-right" class="size-4" />
                            </a>
                            <button type="button" class="btn btn-primary !py-2.5" x-on:click="$store.modal.open('test_drive')">
                                {{ __('site.actions.test_drive') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" @class(['rounded-full px-4 py-2 text-sm font-semibold transition hover:opacity-70', 'text-brand' => $link['active']])>
                    {{ $link['label'] }}
                </a>
            @endforeach

            <div class="relative" x-data="{ open: false }" x-on:mouseenter="open = true" x-on:mouseleave="open = false">
                <button type="button" class="flex items-center gap-1 rounded-full px-4 py-2 text-sm font-semibold transition hover:opacity-70" x-on:click="open = !open" x-bind:aria-expanded="open">
                    {{ __('site.nav.owners') }}
                    <x-glyph name="chevron-down" class="size-4 transition" x-bind:class="open && 'rotate-180'" />
                </button>
                <div x-cloak x-show="open" x-transition.opacity class="absolute top-full left-0 w-64 pt-2">
                    <ul class="rounded-2xl bg-white p-2 text-ink shadow-xl ring-1 shadow-black/10 ring-black/5">
                        @foreach ($ownerLinks as $link)
                            <li><a href="{{ $link['url'] }}" class="block rounded-xl px-4 py-2.5 text-sm font-medium hover:bg-surface">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>

        <div class="ml-auto flex shrink-0 items-center gap-2 whitespace-nowrap lg:gap-4">
            @if ($phone)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hidden items-center gap-2 text-sm font-bold min-[90rem]:flex">
                    <x-glyph name="phone" class="size-4" /> {{ $phone }}
                </a>
            @endif

            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                <button type="button" class="flex items-center gap-1 rounded-full px-2 py-2 text-sm font-semibold uppercase" x-on:click="open = !open" aria-label="{{ __('site.nav.language') }}">
                    <x-glyph name="globe" class="size-4" />
                    {{ app()->getLocale() === 'tg' ? 'TJ' : app()->getLocale() }}
                </button>
                <ul x-cloak x-show="open" x-transition.opacity class="absolute top-full right-0 mt-2 w-40 rounded-2xl bg-white p-2 text-ink shadow-xl ring-1 shadow-black/10 ring-black/5">
                    @foreach (config('app.locales') as $locale => $name)
                        <li>
                            <a href="{{ localized_url($locale) }}" hreflang="{{ $locale }}" @class(['flex items-center justify-between rounded-xl px-3 py-2 text-sm hover:bg-surface', 'font-bold' => $locale === app()->getLocale()])>
                                {{ $name }}
                                @if ($locale === app()->getLocale())
                                    <x-glyph name="check" class="size-4 text-brand" />
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <button type="button" class="btn btn-primary hidden !py-2.5 md:inline-flex" x-on:click="$store.modal.open('test_drive')">
                {{ __('site.actions.test_drive') }}
            </button>

            <button type="button" class="-mr-2 p-2 xl:hidden" x-on:click="menu = !menu" x-bind:aria-expanded="menu" aria-label="{{ __('site.nav.menu') }}">
                <x-glyph name="menu" class="size-6" x-show="!menu" />
                <x-glyph name="x" class="size-6" x-cloak x-show="menu" />
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-cloak x-show="menu" x-transition.opacity class="absolute inset-x-0 top-full h-[calc(100dvh-4rem)] overflow-y-auto border-t border-line bg-white text-ink lg:h-[calc(100dvh-5rem)] xl:hidden">
        <nav class="container-x space-y-1 py-6">
            <div x-data="{ open: true }">
                <button type="button" class="flex w-full items-center justify-between py-3 text-lg font-bold" x-on:click="open = !open">
                    {{ __('site.nav.models') }}
                    <x-glyph name="chevron-down" class="size-5 transition" x-bind:class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-collapse class="grid grid-cols-2 gap-2 pb-4">
                    @foreach ($navCars as $car)
                        <a href="{{ route('cars.show', $car) }}" class="rounded-2xl bg-surface p-3 text-center">
                            @if ($car->card_image)
                                <img src="{{ media($car->card_image) }}" alt="" class="mx-auto h-14 w-full object-contain" loading="lazy">
                            @endif
                            <span class="mt-1 block text-xs font-semibold">{{ $car->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @foreach ([...$links, ...$ownerLinks] as $link)
                <a href="{{ $link['url'] }}" class="block border-t border-line py-3 text-lg font-bold">{{ $link['label'] }}</a>
            @endforeach

            <div class="grid gap-3 pt-6">
                <button type="button" class="btn btn-primary" x-on:click="menu = false; $store.modal.open('test_drive')">{{ __('site.actions.test_drive') }}</button>
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="btn btn-outline"><x-glyph name="phone" class="size-4" /> {{ $phone }}</a>
                @endif
            </div>
        </nav>
    </div>
</header>
