<x-layout :title="$page->tr('title')" :description="$page->tr('meta_description')">
    <x-page-hero :title="$page->tr('title')" />

    <section class="section">
        <div class="container-x max-w-4xl">
            {{-- Written by administrators in the rich editor. --}}
            <div class="prose-content text-lg">{!! $page->tr('body') !!}</div>

            @if ($page->attachment)
                <a href="{{ media($page->attachment) }}" target="_blank" rel="noopener" class="btn btn-dark mt-10">
                    <x-glyph name="download" class="size-4" /> {{ __('site.actions.download') }} PDF
                </a>
            @endif
        </div>
    </section>

    @includeWhen(in_array($page->slug, ['about', 'guarantee']), 'partials.cta')
</x-layout>
