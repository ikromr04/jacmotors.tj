{{--
    AJAX lead form. Fields depend on the lead type:
    callback — phone only; test_drive / purchase / service — name, phone, model (+ date / payment);
    feedback — name, phone, email, subject, message.
--}}
@props(['type', 'cars' => collect(), 'dealers' => collect(), 'dark' => false])

@php
    $needsCar = in_array($type, ['test_drive', 'purchase', 'service'], true);
    $field = fn (string $name) => "error('{$name}') && 'input-error'";
@endphp

<div x-data="leadForm(@js($type))" {{ $attributes }}>
    <div x-show="sent" x-cloak class="py-10 text-center">
        <div class="mx-auto grid size-16 place-items-center rounded-full bg-green-50 text-green-600">
            <x-glyph name="check" class="size-8" />
        </div>
        <p class="mt-6 text-2xl font-extrabold">{{ __('forms.success_title') }}</p>
        <p class="mt-2 text-ink/60">{{ __('forms.success') }}</p>
    </div>

    <form
        x-show="!sent"
        x-on:submit.prevent="submit"
        action="{{ route('leads.store') }}"
        method="POST"
        novalidate
        data-failure="{{ __('forms.error') }}"
        data-too-many="{{ __('forms.too_many') }}"
        class="grid gap-4 sm:grid-cols-2"
    >
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="locale" value="{{ app()->getLocale() }}">
        {{-- Honeypot --}}
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        @if ($type !== 'callback')
            <label class="field">
                <span class="field-label">{{ __('forms.name') }} *</span>
                <input type="text" name="name" class="input" autocomplete="name" maxlength="120" x-bind:class="{{ $field('name') }}">
                <span class="field-error" x-text="error('name')"></span>
            </label>
        @endif

        <label @class(['field', 'sm:col-span-2' => $type === 'callback'])>
            <span class="field-label">{{ __('forms.phone') }} *</span>
            <input type="tel" name="phone" class="input" autocomplete="tel" placeholder="+992 __ ___ __ __" inputmode="tel" maxlength="20" x-bind:class="{{ $field('phone') }}">
            <span class="field-error" x-text="error('phone')"></span>
        </label>

        @if ($needsCar)
            <label class="field">
                <span class="field-label">{{ __('forms.car') }} *</span>
                <select name="car_id" class="input" x-ref="car" x-bind:class="{{ $field('car_id') }}">
                    <option value="">{{ __('forms.choose_car') }}</option>
                    @foreach ($cars as $car)
                        <option value="{{ $car->id }}">{{ $car->name }}</option>
                    @endforeach
                </select>
                <span class="field-error" x-text="error('car_id')"></span>
            </label>
        @endif

        @if (in_array($type, ['test_drive', 'service'], true))
            <label class="field">
                <span class="field-label">{{ __('forms.date') }}</span>
                <input type="date" name="preferred_date" class="input" min="{{ today()->toDateString() }}" x-bind:class="{{ $field('preferred_date') }}">
                <span class="field-error" x-text="error('preferred_date')"></span>
            </label>
        @endif

        @if ($type === 'purchase')
            <label class="field">
                <span class="field-label">{{ __('forms.payment_method') }}</span>
                <select name="payment_method" class="input">
                    <option value="">—</option>
                    @foreach (__('forms.payment_methods') as $method)
                        <option>{{ $method }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if ($dealers->count() > 1 && in_array($type, ['test_drive', 'purchase', 'service'], true))
            <label class="field sm:col-span-2">
                <span class="field-label">{{ __('forms.dealer') }}</span>
                <select name="dealer_id" class="input">
                    <option value="">{{ __('forms.choose_dealer') }}</option>
                    @foreach ($dealers as $dealer)
                        <option value="{{ $dealer->id }}">{{ $dealer->tr('city') }} — {{ $dealer->tr('name') }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if ($type === 'feedback')
            <label class="field">
                <span class="field-label">{{ __('forms.email') }}</span>
                <input type="email" name="email" class="input" autocomplete="email" maxlength="120" x-bind:class="{{ $field('email') }}">
                <span class="field-error" x-text="error('email')"></span>
            </label>
            <label class="field">
                <span class="field-label">{{ __('forms.subject') }} *</span>
                <select name="subject" class="input" x-bind:class="{{ $field('subject') }}">
                    <option value="">{{ __('forms.choose_subject') }}</option>
                    @foreach (__('forms.subjects') as $subject)
                        <option>{{ $subject }}</option>
                    @endforeach
                </select>
                <span class="field-error" x-text="error('subject')"></span>
            </label>
            <label class="field sm:col-span-2">
                <span class="field-label">{{ __('forms.message') }} *</span>
                <textarea name="message" rows="4" class="input resize-y" maxlength="2000" x-bind:class="{{ $field('message') }}"></textarea>
                <span class="field-error" x-text="error('message')"></span>
            </label>
        @endif

        <p x-show="failure" x-cloak x-text="failure" class="rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand sm:col-span-2"></p>

        <button type="submit" class="btn btn-primary w-full sm:col-span-2" x-bind:disabled="sending">
            <svg x-show="sending" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            <span x-text="sending ? @js(__('forms.sending')) : @js(__('forms.submit'))">{{ __('forms.submit') }}</span>
        </button>

        <p class="text-center text-xs text-ink/50 sm:col-span-2">
            {!! __('forms.consent', ['link' => '<a href="'.route('pages.show', 'privacy').'" target="_blank" class="underline hover:text-brand">'.e(__('forms.consent_link')).'</a>']) !!}
        </p>
    </form>
</div>
