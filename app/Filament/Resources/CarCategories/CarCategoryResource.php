<?php

namespace App\Filament\Resources\CarCategories;

use App\Filament\Resources\CarCategories\Pages\CreateCarCategory;
use App\Filament\Resources\CarCategories\Pages\EditCarCategory;
use App\Filament\Resources\CarCategories\Pages\ListCarCategories;
use App\Filament\Support\Translatable;
use App\Models\CarCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CarCategoryResource extends Resource
{
    protected static ?string $model = CarCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'категория';

    protected static ?string $pluralModelLabel = 'Категории';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('slug')->label('Код (латиницей)')->required()->alphaDash()->unique(ignoreRecord: true),
            TextInput::make('sort')->label('Порядок')->numeric()->default(0),
            Translatable::tabs(fn (string $locale, bool $default) => [
                TextInput::make("name.{$locale}")->label('Название')->required($default),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Название')->formatStateUsing(fn (CarCategory $record) => $record->tr('name', 'ru')),
                TextColumn::make('slug')->label('Код'),
                TextColumn::make('cars_count')->label('Моделей')->counts('cars'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarCategories::route('/'),
            'create' => CreateCarCategory::route('/create'),
            'edit' => EditCarCategory::route('/{record}/edit'),
        ];
    }
}
