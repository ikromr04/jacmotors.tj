<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeadType: string implements HasLabel
{
    case Callback = 'callback';
    case TestDrive = 'test_drive';
    case Purchase = 'purchase';
    case Service = 'service';
    case Feedback = 'feedback';

    public function getLabel(): string
    {
        return match ($this) {
            self::Callback => 'Обратный звонок',
            self::TestDrive => 'Тест-драйв',
            self::Purchase => 'Заявка на покупку',
            self::Service => 'Запись на сервис',
            self::Feedback => 'Обращение в поддержку',
        };
    }
}
