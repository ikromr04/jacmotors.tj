<x-layout :title="__('site.home.lineup')" :description="__('site.home.lineup_text')">
    <x-page-hero :title="__('site.home.lineup')" :text="__('site.home.lineup_text')">
        <div class="mt-8 flex flex-wrap gap-2">
            @foreach ($categories as $category)
                <a href="#{{ $category->slug }}" class="btn bg-white !py-2.5 hover:bg-ink hover:text-white">{{ $category->tr('name') }}</a>
            @endforeach
        </div>
    </x-page-hero>

    @foreach ($categories as $category)
        <section id="{{ $category->slug }}" class="section scroll-mt-20 pb-0 last-of-type:pb-24">
            <div class="container-x">
                <h2 class="text-2xl font-extrabold tracking-tight md:text-3xl">{{ $category->tr('name') }}</h2>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($category->cars as $car)
                        <x-car-card :car="$car->setRelation('category', $category)" />
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    <div class="h-16 md:h-24"></div>
    @include('partials.cta')
</x-layout>
