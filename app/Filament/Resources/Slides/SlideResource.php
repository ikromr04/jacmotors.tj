<?php

namespace App\Filament\Resources\Slides;

use App\Filament\Resources\Slides\Pages\CreateSlide;
use App\Filament\Resources\Slides\Pages\EditSlide;
use App\Filament\Resources\Slides\Pages\ListSlides;
use App\Filament\Support\Translatable;
use App\Models\Slide;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'слайд';

    protected static ?string $pluralModelLabel = 'Слайдер на главной';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            FileUpload::make('image')->label('Изображение (десктоп)')->helperText('~1920×900')
                ->image()->disk('public')->directory('slides')->maxSize(8 * 1024)->required(),
            FileUpload::make('image_mobile')->label('Изображение (мобильный)')->helperText('~750×1100')
                ->image()->disk('public')->directory('slides')->maxSize(8 * 1024),
            Translatable::tabs(fn (string $locale) => [
                TextInput::make("title.{$locale}")->label('Заголовок'),
                TextInput::make("subtitle.{$locale}")->label('Подзаголовок'),
                TextInput::make("button_text.{$locale}")->label('Текст кнопки')->placeholder('Подробнее'),
            ]),
            Select::make('car_id')->label('Ведёт на модель')->relationship('car', 'name')->preload()
                ->helperText('Или укажите ссылку справа'),
            TextInput::make('link')->label('Ссылка')->maxLength(255),
            Grid::make(2)->schema([
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
                Toggle::make('is_active')->label('Показывать')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageWidth(160)->imageHeight(64),
                TextColumn::make('title')->label('Заголовок')->formatStateUsing(fn (Slide $record) => $record->tr('title', 'ru')),
                TextColumn::make('car.name')->label('Модель'),
                ToggleColumn::make('is_active')->label('Показывать'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSlides::route('/'),
            'create' => CreateSlide::route('/create'),
            'edit' => EditSlide::route('/{record}/edit'),
        ];
    }
}
