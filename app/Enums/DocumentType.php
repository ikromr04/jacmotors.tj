<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasLabel
{
    case Manual = 'manual';
    case Regulation = 'regulation';

    public function getLabel(): string
    {
        return match ($this) {
            self::Manual => 'Руководство по эксплуатации',
            self::Regulation => 'Регламент ТО',
        };
    }
}
