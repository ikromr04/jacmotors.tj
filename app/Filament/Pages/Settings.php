<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Система';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $title = 'Настройки сайта';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::values());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Контакты')->columns(2)->schema([
                    TextInput::make('phone')->label('Телефон (основной)')->tel(),
                    TextInput::make('email')->label('E-mail для клиентов')->email(),
                    TextInput::make('address.ru')->label('Адрес (RU)'),
                    TextInput::make('address.tg')->label('Адрес (TJ)'),
                    TextInput::make('address.en')->label('Адрес (EN)'),
                    TextInput::make('working_hours.ru')->label('Режим работы (RU)'),
                    TextInput::make('working_hours.tg')->label('Режим работы (TJ)'),
                    TextInput::make('working_hours.en')->label('Режим работы (EN)'),
                ]),
                Section::make('Заявки')->schema([
                    TextInput::make('lead_emails')
                        ->label('Куда отправлять уведомления о заявках')
                        ->helperText('Несколько адресов — через запятую')
                        ->placeholder('info@jacmotors.tj, sales@jacmotors.tj'),
                ]),
                Section::make('Соцсети')->columns(2)->schema([
                    TextInput::make('socials.instagram')->label('Instagram')->url(),
                    TextInput::make('socials.facebook')->label('Facebook')->url(),
                    TextInput::make('socials.telegram')->label('Telegram')->url(),
                    TextInput::make('socials.youtube')->label('YouTube')->url(),
                    TextInput::make('socials.tiktok')->label('TikTok')->url(),
                    TextInput::make('whatsapp')->label('WhatsApp (номер)')->tel(),
                ]),
                Section::make('Аналитика')->columns(2)->schema([
                    TextInput::make('google_analytics_id')->label('Google Analytics ID')->placeholder('G-XXXXXXX'),
                    TextInput::make('yandex_metrika_id')->label('Яндекс.Метрика ID')->numeric(),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Сохранить')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        Setting::put($this->form->getState());

        Notification::make()->success()->title('Настройки сохранены')->send();
    }
}
