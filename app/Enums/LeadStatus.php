<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::InProgress => 'В работе',
            self::Done => 'Обработана',
            self::Rejected => 'Отклонена',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'danger',
            self::InProgress => 'warning',
            self::Done => 'success',
            self::Rejected => 'gray',
        };
    }
}
