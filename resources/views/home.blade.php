<x-layout :overlay-header="$slides->isNotEmpty()">
    {{-- Hero --}}
    @if ($slides->isNotEmpty())
        <section x-data="heroSlider" class="relative h-[100svh] min-h-[620px] overflow-hidden bg-ink text-white">
            <div x-ref="swiper" class="swiper h-full">
                <div class="swiper-wrapper">
                    @foreach ($slides as $slide)
                        <div class="swiper-slide relative h-full">
                            <picture>
                                @if ($slide->image_mobile)
                                    <source media="(max-width: 767px)" srcset="{{ media($slide->image_mobile) }}">
                                @endif
                                <img
                                    src="{{ media($slide->image) }}"
                                    alt="{{ $slide->tr('title') ?? $slide->car?->name }}"
                                    class="absolute inset-0 size-full object-cover"
                                    @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                >
                            </picture>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/40"></div>

                            <div class="container-x relative flex h-full flex-col justify-end pb-28 md:pb-32">
                                <div class="max-w-2xl">
                                    @if ($title = $slide->tr('title') ?? $slide->car?->name)
                                        <h2 class="h-display">{{ $title }}</h2>
                                    @endif
                                    @if ($subtitle = $slide->tr('subtitle') ?? $slide->car?->tr('tagline'))
                                        <p class="mt-4 text-lg text-white/80 md:text-2xl">{{ $subtitle }}</p>
                                    @endif
                                    <div class="mt-8 flex flex-wrap gap-3">
                                        @if ($url = $slide->url())
                                            <a href="{{ $url }}" class="btn btn-primary">
                                                {{ $slide->tr('button_text') ?? __('site.actions.more') }}
                                                <x-glyph name="arrow-right" class="size-4" />
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-ghost-light" x-on:click="$store.modal.open('test_drive', { car_id: {{ $slide->car_id ?? 'null' }} })">
                                            {{ __('site.actions.test_drive') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="container-x pointer-events-none absolute inset-x-0 bottom-10 z-10">
                <div x-ref="pagination" class="pointer-events-auto !static flex gap-2"></div>
            </div>
        </section>
    @else
        <x-page-hero :title="__('site.brand')" :text="__('site.distributor')" />
    @endif

    {{-- Model range --}}
    @if ($categories->isNotEmpty())
        <section class="section" x-data="{ tab: 'all' }">
            <div class="container-x">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="eyebrow">JAC Motors</p>
                        <h2 class="h-section mt-3">{{ site_text('home.lineup') }}</h2>
                        <p class="mt-4 max-w-xl text-ink/60">{{ site_text('home.lineup_text') }}</p>
                    </div>
                    <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 md:mx-0 md:px-0" role="tablist">
                        <button type="button" role="tab" class="btn shrink-0 !py-2.5" x-bind:class="tab === 'all' ? 'btn-dark' : 'bg-surface hover:bg-line'" x-on:click="tab = 'all'">
                            {{ __('site.actions.all_models') }}
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" role="tab" class="btn shrink-0 !py-2.5" x-bind:class="tab === @js($category->slug) ? 'btn-dark' : 'bg-surface hover:bg-line'" x-on:click="tab = @js($category->slug)">
                                {{ $category->tr('name') }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        @foreach ($category->cars as $car)
                            <x-car-card
                                :car="$car->setRelation('category', $category)"
                                x-show="tab === 'all' || tab === {{ Js::from($category->slug) }}"
                                x-transition.opacity.duration.300ms
                            />
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Services --}}
    <section class="section bg-surface">
        <div class="container-x">
            <h2 class="h-section">{{ site_text('home.services') }}</h2>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['key' => 'test_drive', 'icon' => 'steering', 'modal' => 'test_drive'],
                    ['key' => 'purchase', 'icon' => 'credit-card', 'modal' => 'purchase'],
                    ['key' => 'service', 'icon' => 'wrench', 'modal' => 'service'],
                    ['key' => 'support', 'icon' => 'chat', 'url' => route('support')],
                ] as $service)
                    @php($tag = isset($service['url']) ? 'a' : 'button')
                    <{{ $tag }}
                        @if (isset($service['url'])) href="{{ $service['url'] }}" @else type="button" x-data x-on:click="$store.modal.open('{{ $service['modal'] }}')" @endif
                        class="group flex flex-col rounded-3xl bg-white p-8 text-left transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-black/10"
                    >
                        <span class="grid size-14 place-items-center rounded-2xl bg-brand-50 text-brand transition group-hover:bg-brand group-hover:text-white">
                            <x-glyph :name="$service['icon']" class="size-7" />
                        </span>
                        <span class="mt-8 text-xl font-extrabold">{{ site_text("services.{$service['key']}.title") }}</span>
                        <span class="mt-2 text-sm text-ink/60">{{ site_text("services.{$service['key']}.text") }}</span>
                        <span class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-brand">
                            {{ __('site.actions.more') }} <x-glyph name="arrow-right" class="size-4 transition group-hover:translate-x-1" />
                        </span>
                    </{{ $tag }}>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Promotions --}}
    @if ($promotions->isNotEmpty())
        <section class="section">
            <div class="container-x">
                <div class="flex items-end justify-between gap-6">
                    <h2 class="h-section">{{ site_text('home.promotions') }}</h2>
                    <a href="{{ route('promotions.index') }}" class="link-arrow shrink-0">{{ __('site.actions.view_all') }} <x-glyph name="arrow-right" class="size-4" /></a>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ($promotions as $post)
                        @include('posts.card', ['post' => $post])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- News --}}
    @if ($news->isNotEmpty())
        <section class="section">
            <div class="container-x">
                <div class="flex items-end justify-between gap-6">
                    <h2 class="h-section">{{ site_text('home.news') }}</h2>
                    <a href="{{ route('news.index') }}" class="link-arrow shrink-0">{{ __('site.actions.view_all') }} <x-glyph name="arrow-right" class="size-4" /></a>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ($news as $post)
                        @include('posts.card', ['post' => $post])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Without promotions and news the grey services block is right above. --}}
    @include('partials.cta', ['spaced' => $promotions->isEmpty() && $news->isEmpty()])
</x-layout>
