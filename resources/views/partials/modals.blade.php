@php
    $modalDealers = \App\Models\Dealer::query()->active()->get();
@endphp

<div x-data>
    @foreach (['callback', 'test_drive', 'purchase', 'service'] as $type)
        <div
            x-cloak
            x-show="$store.modal.name === @js($type)"
            x-on:keydown.escape.window="$store.modal.close()"
            class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-{{ $type }}-title"
        >
            <div
                x-show="$store.modal.name === @js($type)"
                x-transition.opacity
                class="absolute inset-0 bg-ink/60 backdrop-blur-sm"
                x-on:click="$store.modal.close()"
            ></div>

            <div
                x-show="$store.modal.name === @js($type)"
                x-transition:enter="transition ease-out-soft duration-300"
                x-transition:enter-start="translate-y-8 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-trap.noscroll="$store.modal.name === @js($type)"
                @class([
                    'relative max-h-[92dvh] w-full overflow-y-auto rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl sm:p-10',
                    'sm:max-w-md' => $type === 'callback',
                    'sm:max-w-xl' => $type !== 'callback',
                ])
            >
                <button type="button" class="absolute top-4 right-4 grid size-10 place-items-center rounded-full bg-surface transition hover:bg-line" x-on:click="$store.modal.close()" aria-label="{{ __('site.actions.close') }}">
                    <x-glyph name="x" class="size-5" />
                </button>

                <h2 id="modal-{{ $type }}-title" class="pr-10 text-2xl font-extrabold tracking-tight">{{ __("forms.titles.{$type}") }}</h2>
                <p class="mt-2 mb-6 text-sm text-ink/60">{{ __("forms.subtitles.{$type}") }}</p>

                <x-lead-form :type="$type" :cars="$navCars" :dealers="$modalDealers" />
            </div>
        </div>
    @endforeach
</div>
