<x-layout :title="__('site.dealers.title')" :description="__('site.dealers.text')">
    <x-page-hero :title="__('site.dealers.title')" :text="__('site.dealers.text')" />

    <section class="section">
        <div class="container-x space-y-8">
            @forelse ($dealers as $dealer)
                <article class="grid overflow-hidden rounded-[2rem] bg-surface lg:grid-cols-5">
                    <div class="p-8 md:p-12 lg:col-span-2">
                        <p class="eyebrow">{{ $dealer->tr('city') }}</p>
                        <h2 class="mt-3 text-2xl font-extrabold tracking-tight md:text-3xl">{{ $dealer->tr('name') }}</h2>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($dealer->has_sales)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold">{{ __('site.dealers.sales') }}</span>
                            @endif
                            @if ($dealer->has_service)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold">{{ __('site.dealers.service') }}</span>
                            @endif
                        </div>

                        <ul class="mt-8 space-y-4 text-sm">
                            @if ($address = $dealer->tr('address'))
                                <li class="flex gap-3"><x-glyph name="map-pin" class="mt-0.5 text-brand" /> {{ $address }}</li>
                            @endif
                            @if ($dealer->phone)
                                <li class="flex gap-3"><x-glyph name="phone" class="mt-0.5 text-brand" />
                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $dealer->phone) }}" class="font-semibold hover:text-brand">{{ $dealer->phone }}</a>
                                </li>
                            @endif
                            @if ($dealer->email)
                                <li class="flex gap-3"><x-glyph name="mail" class="mt-0.5 text-brand" />
                                    <a href="mailto:{{ $dealer->email }}" class="hover:text-brand">{{ $dealer->email }}</a>
                                </li>
                            @endif
                            @if ($hours = $dealer->tr('working_hours'))
                                <li class="flex gap-3"><x-glyph name="clock" class="mt-0.5 text-brand" /> <span class="whitespace-pre-line">{{ $hours }}</span></li>
                            @endif
                        </ul>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <button type="button" class="btn btn-primary" x-data x-on:click="$store.modal.open('test_drive')">{{ __('site.actions.test_drive') }}</button>
                            @if ($dealer->lat && $dealer->lng)
                                <a href="https://yandex.ru/maps/?rtext=~{{ $dealer->lat }},{{ $dealer->lng }}" target="_blank" rel="noopener" class="btn btn-outline">{{ __('site.actions.route') }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="min-h-80 bg-line lg:col-span-3">
                        @if ($dealer->map_embed && Str::startsWith($dealer->map_embed, 'https://'))
                            <iframe src="{{ $dealer->map_embed }}" class="size-full min-h-80" loading="lazy" title="{{ $dealer->tr('name') }}" allowfullscreen></iframe>
                        @elseif ($dealer->lat && $dealer->lng)
                            <iframe
                                src="https://yandex.ru/map-widget/v1/?ll={{ $dealer->lng }},{{ $dealer->lat }}&z=16&pt={{ $dealer->lng }},{{ $dealer->lat }},pm2rdm"
                                class="size-full min-h-80"
                                loading="lazy"
                                title="{{ $dealer->tr('name') }}"
                            ></iframe>
                        @endif
                    </div>
                </article>
            @empty
                <p class="py-16 text-center text-lg text-ink/50">{{ __('site.dealers.empty') }}</p>
            @endforelse
        </div>
    </section>
</x-layout>
