@props(['title', 'text' => null, 'eyebrow' => null])

<section class="bg-surface">
    <div class="container-x py-14 md:py-20">
        @if ($eyebrow)
            <p class="eyebrow mb-4">{{ $eyebrow }}</p>
        @endif
        <h1 class="h-section max-w-4xl">{{ $title }}</h1>
        @if ($text)
            <p class="mt-5 max-w-2xl text-lg text-ink/60">{{ $text }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
