@php
    $phone = setting('phone');
    $email = setting('email');
@endphp

<x-layout :title="site_text('support.title')" :description="site_text('support.text')">
    <x-page-hero :title="site_text('support.title')" :text="site_text('support.text')" />

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-3">
            <div class="rounded-3xl bg-surface p-6 md:p-10 lg:col-span-2">
                <x-lead-form type="feedback" />
            </div>

            <aside>
                <h2 class="text-2xl font-extrabold tracking-tight">{{ site_text('support.contacts') }}</h2>
                <ul class="mt-6 space-y-5">
                    @if ($phone)
                        <li class="flex gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand"><x-glyph name="phone" /></span>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="self-center text-lg font-bold hover:text-brand">{{ $phone }}</a>
                        </li>
                    @endif
                    @if ($email)
                        <li class="flex gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand"><x-glyph name="mail" /></span>
                            <a href="mailto:{{ $email }}" class="self-center font-semibold hover:text-brand">{{ $email }}</a>
                        </li>
                    @endif
                    @foreach ($dealers as $dealer)
                        <li class="flex gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand"><x-glyph name="map-pin" /></span>
                            <span class="text-sm">
                                <span class="block font-bold">{{ $dealer->tr('city') }} — {{ $dealer->tr('name') }}</span>
                                <span class="text-ink/60">{{ $dealer->tr('address') }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </section>
</x-layout>
