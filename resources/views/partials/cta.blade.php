@php($phone = setting('phone'))

<section class="section pt-0">
    <div class="container-x">
        <div class="relative overflow-hidden rounded-[2rem] bg-brand px-6 py-14 text-white md:px-16 md:py-20">
            <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-32 left-1/3 size-96 rounded-full bg-black/10 blur-3xl"></div>

            <div class="relative grid items-center gap-8 lg:grid-cols-2">
                <div>
                    <h2 class="h-section">{{ __('forms.titles.purchase') }}</h2>
                    <p class="mt-4 max-w-lg text-white/80">{{ __('forms.subtitles.purchase') }}</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <button type="button" class="btn btn-light" x-data x-on:click="$store.modal.open('purchase')">{{ __('site.actions.request') }}</button>
                    <button type="button" class="btn btn-ghost-light" x-data x-on:click="$store.modal.open('test_drive')">{{ __('site.actions.test_drive') }}</button>
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="btn btn-ghost-light"><x-glyph name="phone" class="size-4" /> {{ $phone }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
