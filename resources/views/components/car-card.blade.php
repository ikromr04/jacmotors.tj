@props(['car'])

<article {{ $attributes->class('group relative flex flex-col overflow-hidden rounded-3xl bg-surface p-6 transition duration-300 hover:bg-white hover:shadow-2xl hover:shadow-black/10') }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            @if ($car->category)
                <p class="text-xs font-semibold tracking-wider text-ink/50 uppercase">{{ $car->category->tr('name') }}</p>
            @endif
            <h3 class="mt-1 text-xl font-extrabold tracking-tight">
                <a href="{{ route('cars.show', $car) }}" class="after:absolute after:inset-0">{{ $car->name }}</a>
            </h3>
        </div>
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-white text-ink transition duration-300 group-hover:bg-brand group-hover:text-white">
            <x-glyph name="arrow-right" class="size-4" />
        </span>
    </div>

    {{-- pointer-events-none: let clicks on the photo reach the card link stretched over the card. --}}
    <div class="pointer-events-none relative my-6 aspect-[16/9]">
        @if ($car->card_image)
            <img
                src="{{ media($car->card_image) }}"
                alt="{{ $car->name }}"
                loading="lazy"
                class="absolute inset-0 size-full object-contain transition duration-500 ease-out-soft group-hover:scale-105"
            >
        @endif
    </div>

    <div class="mt-auto flex items-end justify-between gap-4">
        <div>
            @if ($tagline = $car->tr('tagline'))
                <p class="text-sm text-ink/60">{{ $tagline }}</p>
            @endif
            <p class="mt-1 text-sm font-bold">
                {{ $car->price_from ? __('site.car.from', ['price' => number_format($car->price_from, 0, '.', ' ')]) : __('site.car.price_on_request') }}
            </p>
        </div>
        <button
            type="button"
            class="relative z-10 text-xs font-semibold text-brand underline-offset-4 hover:underline"
            x-data
            x-on:click="$store.modal.open('test_drive', { car_id: {{ $car->id }} })"
        >
            {{ __('site.services.test_drive.title') }}
        </button>
    </div>
</article>
