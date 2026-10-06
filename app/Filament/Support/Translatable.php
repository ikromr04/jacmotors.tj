<?php

namespace App\Filament\Support;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class Translatable
{
    /**
     * Language tabs for translatable JSON attributes.
     *
     * The closure receives the locale and whether it is the default one,
     * and returns the fields for that tab, e.g. TextInput::make("title.{$locale}").
     */
    public static function tabs(Closure $fields, string $label = 'Переводы'): Tabs
    {
        $default = config('app.locale');

        return Tabs::make($label)
            ->tabs(collect(config('app.locales'))
                ->map(fn (string $name, string $locale) => Tab::make($name)
                    ->schema($fields($locale, $locale === $default)))
                ->values()
                ->all())
            ->columnSpanFull();
    }
}
