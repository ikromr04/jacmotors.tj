<?php

namespace App\Filament\Resources\Dealers;

use App\Filament\Resources\Dealers\Pages\CreateDealer;
use App\Filament\Resources\Dealers\Pages\EditDealer;
use App\Filament\Resources\Dealers\Pages\ListDealers;
use App\Filament\Support\Translatable;
use App\Models\Dealer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'дилер';

    protected static ?string $pluralModelLabel = 'Дилеры и сервис';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Translatable::tabs(fn (string $locale, bool $default) => [
                TextInput::make("name.{$locale}")->label('Название')->required($default),
                TextInput::make("city.{$locale}")->label('Город')->required($default),
                TextInput::make("address.{$locale}")->label('Адрес'),
                Textarea::make("working_hours.{$locale}")->label('Режим работы')->rows(2)
                    ->placeholder("Пн–Сб: 9:00–18:00\nВс: выходной"),
            ]),
            TextInput::make('phone')->label('Телефон')->tel(),
            TextInput::make('email')->label('E-mail')->email(),
            TextInput::make('lat')->label('Широта')->numeric(),
            TextInput::make('lng')->label('Долгота')->numeric(),
            Textarea::make('map_embed')->label('Ссылка для встраивания карты (Яндекс/Google, src из iframe)')
                ->rows(2)->columnSpanFull(),
            Grid::make(4)->columnSpanFull()->schema([
                Toggle::make('has_sales')->label('Продажи')->default(true)
                    ->helperText('Метка «Продажи» в карточке дилера на сайте.'),
                Toggle::make('has_service')->label('Сервис')->default(true)
                    ->helperText('Метка «Сервис» в карточке дилера. Только такие дилеры предлагаются в форме записи на сервис.'),
                Toggle::make('is_active')->label('Показывать')->default(true)
                    ->helperText('Если выключить, дилер исчезнет со страницы дилеров и из выбора дилера в формах заявок.'),
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Название')->formatStateUsing(fn (Dealer $record) => $record->tr('name', 'ru')),
                TextColumn::make('city')->label('Город')->formatStateUsing(fn (Dealer $record) => $record->tr('city', 'ru')),
                TextColumn::make('phone')->label('Телефон'),
                IconColumn::make('has_sales')->label('Продажи')->boolean(),
                IconColumn::make('has_service')->label('Сервис')->boolean(),
                ToggleColumn::make('is_active')->label('Показывать'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealers::route('/'),
            'create' => CreateDealer::route('/create'),
            'edit' => EditDealer::route('/{record}/edit'),
        ];
    }
}
