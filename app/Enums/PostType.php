<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PostType: string implements HasLabel
{
    case News = 'news';
    case Promotion = 'promotion';

    public function getLabel(): string
    {
        return match ($this) {
            self::News => 'Новость',
            self::Promotion => 'Акция',
        };
    }
}
