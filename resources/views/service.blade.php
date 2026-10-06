<x-layout :title="__('site.service.title')" :description="__('site.service.text')">
    <x-page-hero :title="__('site.service.title')" :text="__('site.service.text')" :eyebrow="__('site.nav.owners')" />

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight md:text-3xl">{{ __('site.service.booking') }}</h2>
                <p class="mt-3 text-ink/60">{{ __('forms.subtitles.service') }}</p>
                <div class="mt-8 rounded-3xl bg-surface p-6 md:p-8">
                    <x-lead-form type="service" :cars="$navCars" :dealers="$dealers" />
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-extrabold tracking-tight md:text-3xl">{{ __('site.service.regulations') }}</h2>
                <ul class="mt-8 divide-y divide-line border-y border-line">
                    @foreach ($documents as $document)
                        <li>
                            <a href="{{ media($document->file) }}" target="_blank" rel="noopener" class="group flex items-center gap-4 py-4">
                                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-surface text-ink/60 transition group-hover:bg-brand group-hover:text-white">
                                    <x-glyph name="document" />
                                </span>
                                <span class="flex-1 font-semibold">{{ $document->tr('title') }}</span>
                                <x-glyph name="download" class="text-ink/40 transition group-hover:text-brand" />
                            </a>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('pages.show', 'guarantee') }}" class="mt-10 flex items-center gap-5 rounded-3xl bg-ink p-6 text-white transition hover:bg-ink-soft">
                    <x-glyph name="shield" class="size-10 text-brand" />
                    <span class="flex-1">
                        <span class="block text-lg font-bold">{{ __('site.nav.guarantee') }}</span>
                        <span class="text-sm text-white/60">{{ __('site.guarantee.text') }}</span>
                    </span>
                    <x-glyph name="arrow-right" />
                </a>
            </div>
        </div>
    </section>
</x-layout>
