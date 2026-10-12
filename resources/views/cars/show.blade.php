@php
    $price = $car->price_from ? __('site.car.from', ['price' => number_format($car->price_from, 0, '.', ' ')]) : null;
    $colors = collect($car->colors ?? [])->filter(fn ($color) => filled($color['image'] ?? null))->values();
    $specGroups = collect($car->specs ?? [])->groupBy(fn ($spec) => tr($spec['group'] ?? null) ?? '');
    $sections = $car->sections;
    $gallery = array_values(array_filter($car->gallery ?? []));
    $hero = $car->hero_image ?? $car->card_image;
    $anchors = array_filter([
        'overview' => __('site.car.overview'),
        'features' => $sections->isNotEmpty() ? __('site.car.features') : null,
        'specs' => $specGroups->isNotEmpty() ? __('site.car.specs') : null,
        'gallery' => $gallery ? __('site.car.gallery') : null,
    ]);
    $schema = array_filter([
        // Google expects an offer on every Product, so models without a price are described as a car only.
        '@type' => $car->price_from ? ['Product', 'Car'] : 'Car',
        'name' => $car->name,
        'description' => strip_tags($car->tr('excerpt') ?? $car->tr('tagline') ?? ''),
        'url' => route('cars.show', $car),
        'image' => $hero ? media($hero) : null,
        'brand' => ['@type' => 'Brand', 'name' => 'JAC'],
        'category' => $car->category?->tr('name'),
        'offers' => $car->price_from ? [
            '@type' => 'Offer',
            'price' => $car->price_from,
            'priceCurrency' => 'TJS',
            'url' => route('cars.show', $car),
            'seller' => ['@id' => route('home').'#dealer'],
        ] : null,
    ]);
@endphp

<x-layout
    :schema="[$schema]"
    :title="$car->tr('meta_title') ?? $car->name"
    :description="$car->tr('meta_description') ?? $car->tr('excerpt') ?? $car->tr('tagline')"
    :image="$car->hero_image ?? $car->card_image"
    :image-contain="! $car->hero_image"
    :overlay-header="(bool) $car->hero_image"
>
    {{-- Hero --}}
    @if ($car->hero_image)
        <section class="relative h-[100svh] min-h-[620px] overflow-hidden bg-ink text-white">
            <picture>
                @if ($car->hero_image_mobile)
                    <source media="(max-width: 767px)" srcset="{{ media($car->hero_image_mobile) }}">
                @endif
                <img src="{{ media($car->hero_image) }}" alt="{{ $car->name }}" class="absolute inset-0 size-full object-cover" fetchpriority="high">
            </picture>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-black/40"></div>
            <div class="container-x relative flex h-full flex-col justify-end pb-16 md:pb-24">
                <nav class="mb-6 text-sm text-white/60">
                    <a href="{{ route('cars.index') }}" class="hover:text-white">{{ __('site.nav.models') }}</a>
                    @if ($car->category) <span class="mx-2">/</span> {{ $car->category->tr('name') }} @endif
                </nav>
                <h1 class="h-display">{{ $car->name }}</h1>
                @if ($tagline = $car->tr('tagline'))
                    <p class="mt-4 max-w-2xl text-lg text-white/80 md:text-2xl">{{ $tagline }}</p>
                @endif
                @if ($price)
                    <p class="mt-6 text-2xl font-bold">{{ $price }}</p>
                @endif
                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" class="btn btn-primary" x-data x-on:click="$store.modal.open('purchase', { car_id: {{ $car->id }} })">{{ __('site.actions.request_price') }}</button>
                    <button type="button" class="btn btn-ghost-light" x-data x-on:click="$store.modal.open('test_drive', { car_id: {{ $car->id }} })">{{ __('site.actions.test_drive') }}</button>
                </div>
            </div>
        </section>
    @else
        <x-page-hero :title="$car->name" :text="$car->tr('tagline')" :eyebrow="$car->category?->tr('name')" />
    @endif

    {{-- Sticky sub navigation --}}
    <div class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur-md">
        <div class="container-x flex h-14 items-center gap-6">
            <p class="hidden shrink-0 font-extrabold sm:block">{{ $car->name }}</p>
            <nav class="-mx-4 flex flex-1 gap-1 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                @foreach ($anchors as $id => $label)
                    <a href="#{{ $id }}" class="shrink-0 rounded-full px-3 py-1.5 text-sm font-medium text-ink/70 transition hover:bg-surface hover:text-ink">{{ $label }}</a>
                @endforeach
            </nav>
            <button type="button" class="btn btn-primary hidden shrink-0 !py-2 sm:inline-flex" x-data x-on:click="$store.modal.open('purchase', { car_id: {{ $car->id }} })">
                {{ __('site.actions.request') }}
            </button>
        </div>
    </div>

    {{-- Overview with colour picker --}}
    <section id="overview" class="section scroll-mt-14">
        <div class="container-x grid items-center gap-12 lg:grid-cols-5">
            <div class="lg:col-span-3" x-data="{ active: 0 }">
                <div class="relative aspect-[16/9]">
                    @forelse ($colors as $color)
                        <img
                            src="{{ media($color['image']) }}"
                            alt="{{ $car->name }} — {{ tr($color['name'] ?? null) }}"
                            loading="lazy"
                            class="absolute inset-0 size-full object-contain transition duration-500"
                            x-bind:class="active === {{ $loop->index }} ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                        >
                    @empty
                        @if ($car->card_image)
                            <img src="{{ media($car->card_image) }}" alt="{{ $car->name }}" class="absolute inset-0 size-full object-contain">
                        @endif
                    @endforelse
                </div>

                @if ($colors->count() > 1)
                    <div class="mt-8 flex flex-col items-center gap-3">
                        <div class="flex flex-wrap justify-center gap-3">
                            @foreach ($colors as $color)
                                <button
                                    type="button"
                                    class="size-9 rounded-full border border-black/15 ring-2 ring-offset-4 transition"
                                    style="background: {{ preg_match('/^#[0-9a-f]{3,8}$/i', $color['hex'] ?? '') ? $color['hex'] : '#ccc' }}"
                                    x-bind:class="active === {{ $loop->index }} ? 'ring-ink' : 'ring-transparent hover:ring-line'"
                                    x-on:click="active = {{ $loop->index }}"
                                    aria-label="{{ tr($color['name'] ?? null) }}"
                                    title="{{ tr($color['name'] ?? null) }}"
                                ></button>
                            @endforeach
                        </div>
                        <p class="text-sm font-medium text-ink/60">
                            @foreach ($colors as $color)
                                <span x-show="active === {{ $loop->index }}">{{ tr($color['name'] ?? null) }}</span>
                            @endforeach
                        </p>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-2">
                <p class="eyebrow">{{ __('site.car.overview') }}</p>
                <h2 class="h-section mt-3">{{ $car->name }}</h2>
                @if ($description = $car->tr('description'))
                    <div class="prose-content mt-6">{!! $description !!}</div>
                @elseif ($excerpt = $car->tr('excerpt'))
                    <p class="mt-6 text-ink/70">{{ $excerpt }}</p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($car->brochure)
                        <a href="{{ media($car->brochure) }}" target="_blank" rel="noopener" class="btn btn-dark">
                            <x-glyph name="document" class="size-4" /> {{ __('site.actions.brochure') }}
                        </a>
                    @endif
                    @foreach ($car->documents as $document)
                        <a href="{{ media($document->file) }}" target="_blank" rel="noopener" class="btn btn-outline">
                            <x-glyph name="download" class="size-4" /> {{ __("site.car.{$document->type->value}") }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 360° interior tour, loaded on demand --}}
    @if ($car->tour_url)
        <section class="section pt-0">
            <div class="container-x">
                <div x-data="{ loaded: false }" class="relative aspect-[4/5] overflow-hidden rounded-[2rem] bg-ink sm:aspect-[16/9]">
                    <template x-if="loaded">
                        <iframe src="{{ media($car->tour_url) }}" class="absolute inset-0 size-full" allowfullscreen loading="lazy" title="{{ __('site.car.tour') }}"></iframe>
                    </template>
                    <div x-show="!loaded" class="absolute inset-0 grid place-items-center text-center text-white">
                        @if ($car->hero_image)
                            <img src="{{ media($car->hero_image) }}" alt="" class="absolute inset-0 size-full object-cover opacity-40" loading="lazy">
                        @endif
                        <div class="relative px-6">
                            <x-glyph name="cube" class="mx-auto size-12 text-white/80" />
                            <h2 class="h-section mt-4">{{ __('site.car.tour') }}</h2>
                            <p class="mt-3 text-white/70">{{ __('site.car.tour_text') }}</p>
                            <button type="button" class="btn btn-light mt-8" x-on:click="loaded = true">360°</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Feature sections --}}
    @if ($sections->isNotEmpty())
        <div id="features" class="scroll-mt-14">
            @foreach ($sections as $section)
                @php($items = collect($section->items ?? [])->values())
                <section @class(['section', 'bg-surface' => $loop->odd])>
                    <div class="container-x">
                        <div class="max-w-3xl">
                            @if ($label = $section->tr('label'))
                                <p class="eyebrow">{{ $label }}</p>
                            @endif
                            @if ($title = $section->tr('title'))
                                <h2 class="h-section mt-3">{{ $title }}</h2>
                            @endif
                            @if ($text = $section->tr('text'))
                                <p class="mt-5 text-lg text-ink/60">{{ $text }}</p>
                            @endif
                        </div>

                        @if ($items->isNotEmpty())
                            <div class="mt-10 grid gap-8 lg:grid-cols-12" x-data="{ active: 0 }">
                                <div @class(['lg:col-span-8', 'lg:order-2' => $loop->even])>
                                    <div class="relative aspect-[16/10] overflow-hidden rounded-3xl bg-line">
                                        @foreach ($items as $item)
                                            @if (! empty($item['image']))
                                                <img
                                                    src="{{ media($item['image']) }}"
                                                    alt="{{ tr($item['title'] ?? null) }}"
                                                    loading="lazy"
                                                    class="absolute inset-0 size-full object-cover transition duration-700"
                                                    x-bind:class="active === {{ $loop->index }} ? 'opacity-100' : 'opacity-0'"
                                                >
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <ul @class(['flex flex-col gap-2 lg:col-span-4', 'lg:order-1' => $loop->even])>
                                    @foreach ($items as $item)
                                        <li>
                                            <button
                                                type="button"
                                                class="w-full rounded-2xl p-5 text-left transition"
                                                x-bind:class="active === {{ $loop->index }} ? 'bg-white shadow-xl shadow-black/5' : 'hover:bg-white/60'"
                                                x-on:click="active = {{ $loop->index }}"
                                            >
                                                <span class="flex items-center justify-between gap-4 font-bold">
                                                    {{ tr($item['title'] ?? null) }}
                                                    <x-glyph name="arrow-right" class="size-4 text-brand transition" x-bind:class="active === {{ $loop->index }} ? 'opacity-100' : 'opacity-0'" />
                                                </span>
                                                @if ($itemText = tr($item['text'] ?? null))
                                                    <span x-show="active === {{ $loop->index }}" x-collapse class="block">
                                                        <span class="block pt-2 text-sm text-ink/60">{{ $itemText }}</span>
                                                    </span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif ($section->image)
                            <img src="{{ media($section->image) }}" alt="{{ $section->tr('title') }}" loading="lazy" class="mt-10 w-full rounded-3xl">
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    {{-- Specifications --}}
    @if ($specGroups->isNotEmpty())
        <section id="specs" class="section scroll-mt-14">
            <div class="container-x">
                <h2 class="h-section">{{ __('site.car.specs') }}</h2>
                <div class="mt-10 grid gap-x-12 gap-y-10 lg:grid-cols-2">
                    @foreach ($specGroups as $group => $specs)
                        <div>
                            @if ($group)
                                <h3 class="mb-3 text-lg font-bold">{{ $group }}</h3>
                            @endif
                            <dl class="divide-y divide-line border-y border-line">
                                @foreach ($specs as $spec)
                                    <div class="flex justify-between gap-6 py-3 text-sm">
                                        <dt class="text-ink/60">{{ tr($spec['name'] ?? null) }}</dt>
                                        <dd class="text-right font-semibold">{{ $spec['value'] ?? '' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Gallery --}}
    @if ($gallery)
        <section id="gallery" class="section scroll-mt-14 overflow-hidden bg-surface" x-data="carousel({ desktop: 2 })">
            <div class="container-x">
                <div class="flex items-end justify-between gap-6">
                    <h2 class="h-section">{{ __('site.car.gallery') }}</h2>
                    <div class="flex gap-2">
                        <button type="button" x-ref="prev" class="grid size-12 place-items-center rounded-full bg-white transition hover:bg-ink hover:text-white disabled:opacity-40" aria-label="←"><x-glyph name="arrow-left" /></button>
                        <button type="button" x-ref="next" class="grid size-12 place-items-center rounded-full bg-white transition hover:bg-ink hover:text-white disabled:opacity-40" aria-label="→"><x-glyph name="arrow-right" /></button>
                    </div>
                </div>
                <div x-ref="swiper" class="swiper mt-10 !overflow-visible">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $image)
                            <div class="swiper-slide">
                                <img src="{{ media($image) }}" alt="{{ $car->name }}" loading="lazy" class="aspect-[16/10] w-full rounded-3xl object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Inline request form --}}
    <section class="section">
        <div class="container-x">
            <div class="grid gap-10 overflow-hidden rounded-[2rem] bg-ink p-6 text-white md:p-12 lg:grid-cols-2 lg:gap-16">
                <div class="flex flex-col">
                    <h2 class="h-section">{{ __('site.car.cta_title', ['name' => $car->name]) }}</h2>
                    <p class="mt-4 text-white/70">{{ __('site.car.cta_text') }}</p>
                    @if ($car->card_image)
                        <img src="{{ media($car->card_image) }}" alt="{{ $car->name }}" loading="lazy" class="mt-auto hidden w-full max-w-md pt-10 lg:block">
                    @endif
                </div>
                <div class="self-center rounded-3xl bg-white p-6 text-ink md:p-8" x-data x-init="$nextTick(() => { const s = $el.querySelector('[name=car_id]'); if (s) s.value = '{{ $car->id }}' })">
                    <x-lead-form type="purchase" :cars="$navCars" />
                </div>
            </div>
        </div>
    </section>

    {{-- Related models --}}
    @if ($related->isNotEmpty())
        <section class="section bg-surface">
            <div class="container-x">
                <h2 class="h-section">{{ __('site.car.related') }}</h2>
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-car-card :car="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layout>
