<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    /**
     * Translated value of a JSON attribute for the given (or current) locale,
     * falling back to the default locale and then to any filled value.
     */
    public function tr(string $attribute, ?string $locale = null): ?string
    {
        return tr($this->getAttribute($attribute), $locale);
    }
}
