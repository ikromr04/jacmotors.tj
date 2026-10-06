<?php

namespace App\Filament\Pages;

use App\Filament\Support\Translatable;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Editable interface texts of the site pages. Stored in the "texts" setting with the same
 * keys as lang/{locale}/site.php; an empty field falls back to the language file.
 *
 * @property-read Schema $form
 */
class SiteTexts extends Page
{
    /**
     * Keys (relative to lang/{locale}/site.php) that can be edited here.
     *
     * @var list<string>
     */
    public const KEYS = [
        'home.lineup', 'home.lineup_text', 'home.services', 'home.promotions', 'home.news',
        'services.test_drive.title', 'services.test_drive.text', 'services.purchase.title', 'services.purchase.text',
        'services.service.title', 'services.service.text', 'services.support.title', 'services.support.text',
        'support.title', 'support.text', 'support.contacts',
    ];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Тексты сайта';

    protected static ?string $title = 'Тексты сайта';

    protected static ?string $slug = 'texts';

    public ?array $data = [];

    public function mount(): void
    {
        $stored = Setting::get('texts', []);
        $data = [];

        // Show the current texts, so the manager edits them instead of starting from empty fields.
        foreach (self::KEYS as $key) {
            foreach (array_keys(config('app.locales')) as $locale) {
                data_set($data, "{$key}.{$locale}", data_get($stored, "{$key}.{$locale}") ?: __("site.{$key}", [], $locale));
            }
        }

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Translatable::tabs(fn (string $locale) => [
                    Section::make('Главная страница')->collapsible()->schema([
                        Section::make('Модельный ряд')->schema([
                            TextInput::make("home.lineup.{$locale}")->label('Заголовок'),
                            Textarea::make("home.lineup_text.{$locale}")->label('Текст под заголовком')->rows(2),
                        ]),
                        Section::make('Сервисы для вас')->schema([
                            TextInput::make("home.services.{$locale}")->label('Заголовок'),
                            ...collect(['test_drive' => 'Тест-драйв', 'purchase' => 'Покупка', 'service' => 'Сервис', 'support' => 'Поддержка'])
                                ->map(fn (string $label, string $service) => Fieldset::make("Карточка «{$label}»")->schema([
                                    TextInput::make("services.{$service}.title.{$locale}")->label('Название'),
                                    Textarea::make("services.{$service}.text.{$locale}")->label('Текст')->rows(2),
                                ]))
                                ->values()
                                ->all(),
                        ]),
                        Section::make('Заголовки других блоков')->columns(2)->schema([
                            TextInput::make("home.promotions.{$locale}")->label('Акции'),
                            TextInput::make("home.news.{$locale}")->label('Новости'),
                        ]),
                    ]),
                    Section::make('Служба поддержки')->collapsible()->schema([
                        TextInput::make("support.title.{$locale}")->label('Заголовок страницы')
                            ->helperText('Пункт меню «Служба поддержки» меняется отдельно и остаётся прежним.'),
                        Textarea::make("support.text.{$locale}")->label('Текст под заголовком')->rows(2),
                        TextInput::make("support.contacts.{$locale}")->label('Заголовок блока контактов')
                            ->helperText('Этот же заголовок выводится над контактами в подвале сайта.'),
                    ]),
                ], 'Языки'),
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
        Setting::put(['texts' => $this->form->getState()]);

        Notification::make()->success()->title('Тексты сохранены')->send();
    }
}
