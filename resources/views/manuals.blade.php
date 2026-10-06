<x-layout :title="__('site.manuals.title')" :description="__('site.manuals.text')">
    <x-page-hero :title="__('site.manuals.title')" :text="__('site.manuals.text')" :eyebrow="__('site.nav.owners')" />

    <section class="section">
        <div class="container-x grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($documents as $document)
                <a href="{{ media($document->file) }}" target="_blank" rel="noopener" class="group flex flex-col rounded-3xl bg-surface p-6 transition hover:bg-white hover:shadow-2xl hover:shadow-black/10">
                    <div class="relative aspect-[16/10]">
                        @if ($image = $document->image ?? $document->car?->card_image)
                            <img src="{{ media($image) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-contain transition duration-500 group-hover:scale-105">
                        @endif
                    </div>
                    <p class="mt-4 font-extrabold">{{ $document->tr('title') }}</p>
                    <span class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand">
                        <x-glyph name="download" class="size-4" /> {{ __('site.actions.download') }} PDF
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-layout>
