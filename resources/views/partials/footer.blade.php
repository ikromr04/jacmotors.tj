@php
    $phone = setting('phone');
    $email = setting('email');
    $socials = array_filter((array) setting('socials', []));
@endphp

<footer class="bg-ink text-white">
    <div class="container-x grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <x-logo.jac class="h-10 w-auto" />
            <p class="mt-6 max-w-sm text-sm text-white/60">{{ __('site.distributor') }}</p>
            <x-logo.koinot class="mt-6 h-12 w-auto text-white/70" />
        </div>

        <div class="lg:col-span-2">
            <p class="mb-4 text-sm font-bold">{{ __('site.nav.models') }}</p>
            <ul class="space-y-2 text-sm text-white/60">
                @foreach ($navCars->take(8) as $car)
                    <li><a href="{{ route('cars.show', $car) }}" class="transition hover:text-white">{{ $car->name }}</a></li>
                @endforeach
                <li><a href="{{ route('cars.index') }}" class="text-white transition hover:text-brand">{{ __('site.actions.all_models') }} →</a></li>
            </ul>
        </div>

        <div class="lg:col-span-2">
            <p class="mb-4 text-sm font-bold">{{ __('site.footer.buyers') }}</p>
            <ul class="space-y-2 text-sm text-white/60">
                <li><a href="{{ route('promotions.index') }}" class="transition hover:text-white">{{ __('site.nav.promotions') }}</a></li>
                <li><a href="{{ route('dealers') }}" class="transition hover:text-white">{{ __('site.nav.dealers') }}</a></li>
                <li><a href="{{ route('service') }}" class="transition hover:text-white">{{ __('site.nav.service') }}</a></li>
                <li><a href="{{ route('manuals') }}" class="transition hover:text-white">{{ __('site.nav.manuals') }}</a></li>
                <li><a href="{{ route('pages.show', 'guarantee') }}" class="transition hover:text-white">{{ __('site.nav.guarantee') }}</a></li>
            </ul>
        </div>

        <div class="lg:col-span-2">
            <p class="mb-4 text-sm font-bold">{{ __('site.footer.company') }}</p>
            <ul class="space-y-2 text-sm text-white/60">
                <li><a href="{{ route('pages.show', 'about') }}" class="transition hover:text-white">{{ __('site.nav.about') }}</a></li>
                <li><a href="{{ route('news.index') }}" class="transition hover:text-white">{{ __('site.nav.news') }}</a></li>
                <li><a href="{{ route('support') }}" class="transition hover:text-white">{{ __('site.nav.support') }}</a></li>
            </ul>
        </div>

        <div class="lg:col-span-2">
            <p class="mb-4 text-sm font-bold">{{ __('site.support.contacts') }}</p>
            <ul class="space-y-3 text-sm">
                @if ($phone)
                    <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="text-lg font-bold transition hover:text-brand">{{ $phone }}</a></li>
                @endif
                @if ($email)
                    <li><a href="mailto:{{ $email }}" class="text-white/60 transition hover:text-white">{{ $email }}</a></li>
                @endif
                @if ($address = tr(setting('address')))
                    <li class="text-white/60">{{ $address }}</li>
                @endif
                @if ($hours = tr(setting('working_hours')))
                    <li class="text-white/60">{{ $hours }}</li>
                @endif
            </ul>

            @if ($socials)
                <div class="mt-6 flex gap-2">
                    @foreach ($socials as $network => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-full bg-white/10 transition hover:bg-brand" aria-label="{{ ucfirst($network) }}">
                            <x-glyph :name="$network" class="size-5" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-x flex flex-col gap-2 py-6 text-xs text-white/40 sm:flex-row sm:justify-between">
            <p>© {{ date('Y') }} {{ __('site.brand') }}. {{ __('site.footer.rights') }}</p>
            <a href="{{ route('pages.show', 'privacy') }}" class="transition hover:text-white">{{ __('site.footer.privacy') }}</a>
        </div>
    </div>
</footer>
