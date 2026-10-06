<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Filament\Support\Translatable;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Основное')->schema(self::main()),
                        Tab::make('Изображения')->schema(self::images()),
                        Tab::make('Цвета')->schema([self::colors()]),
                        Tab::make('Блоки страницы')->schema([self::sections()]),
                        Tab::make('Характеристики')->schema([self::specs()]),
                        Tab::make('SEO')->schema([
                            Translatable::tabs(fn (string $locale) => [
                                TextInput::make("meta_title.{$locale}")->label('Meta title')->maxLength(70),
                                Textarea::make("meta_description.{$locale}")->label('Meta description')->rows(3)->maxLength(170),
                            ]),
                        ]),
                    ]),
            ]);
    }

    private static function main(): array
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(80)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, string $operation) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('URL')
                    ->prefix('/models/')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                Select::make('car_category_id')
                    ->label('Категория')
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->tr('name', 'ru'))
                    ->preload(),
                TextInput::make('price_from')
                    ->label('Цена от, сомони')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
                Grid::make(1)->schema([
                    Toggle::make('is_published')->label('Опубликована')->default(true),
                    Toggle::make('is_featured')->label('Показывать на главной'),
                ])->columnSpan(1),
            ]),
            Translatable::tabs(fn (string $locale) => [
                TextInput::make("tagline.{$locale}")->label('Слоган')->maxLength(120),
                Textarea::make("excerpt.{$locale}")->label('Короткое описание (в карточке)')->rows(2)->maxLength(300),
                RichEditor::make("description.{$locale}")->label('Описание')
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h3', 'undo', 'redo']),
            ], 'Тексты'),
            Grid::make(2)->schema([
                FileUpload::make('brochure')
                    ->label('PDF с комплектациями')
                    ->disk('public')
                    ->directory('cars/brochures')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(30 * 1024)
                    ->downloadable(),
                TextInput::make('tour_url')
                    ->label('Ссылка на 3D-тур салона')
                    ->url()
                    ->maxLength(255),
            ]),
        ];
    }

    private static function images(): array
    {
        return [
            Grid::make(3)->schema([
                self::image('card_image', 'Карточка в каталоге', 'cars/cards')
                    ->helperText('PNG на прозрачном фоне, ~1000×600'),
                self::image('hero_image', 'Главный баннер (десктоп)', 'cars/hero')
                    ->helperText('~1920×900'),
                self::image('hero_image_mobile', 'Главный баннер (мобильный)', 'cars/hero')
                    ->helperText('~750×1100'),
            ]),
            self::image('gallery', 'Галерея', 'cars/gallery')
                ->multiple()
                ->reorderable()
                ->panelLayout('grid'),
        ];
    }

    private static function colors(): Repeater
    {
        return Repeater::make('colors')
            ->hiddenLabel()
            ->addActionLabel('Добавить цвет')
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn (array $state) => $state['name']['ru'] ?? null)
            ->columns(3)
            ->schema([
                Grid::make(1)->schema([
                    TextInput::make('name.ru')->label('Название (RU)')->required(),
                    TextInput::make('name.tg')->label('Название (TJ)'),
                    TextInput::make('name.en')->label('Название (EN)'),
                ]),
                ColorPicker::make('hex')->label('Цвет')->required(),
                self::image('image', 'Фото автомобиля в этом цвете', 'cars/colors')->required(),
            ]);
    }

    private static function sections(): Repeater
    {
        return Repeater::make('sections')
            ->hiddenLabel()
            ->relationship()
            ->orderColumn('sort')
            ->addActionLabel('Добавить блок')
            ->collapsible()
            ->collapsed()
            ->itemLabel(fn (array $state) => trim(($state['label']['ru'] ?? '').' — '.($state['title']['ru'] ?? ''), ' —'))
            ->schema([
                Translatable::tabs(fn (string $locale) => [
                    TextInput::make("label.{$locale}")->label('Надзаголовок (Экстерьер, Интерьер…)'),
                    TextInput::make("title.{$locale}")->label('Заголовок'),
                    Textarea::make("text.{$locale}")->label('Текст')->rows(3),
                ]),
                self::image('image', 'Изображение блока (если нет пунктов)', 'cars/sections'),
                Repeater::make('items')
                    ->label('Пункты с фото')
                    ->addActionLabel('Добавить пункт')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state) => $state['title']['ru'] ?? null)
                    ->columns(2)
                    ->schema([
                        Translatable::tabs(fn (string $locale) => [
                            TextInput::make("title.{$locale}")->label('Название'),
                            Textarea::make("text.{$locale}")->label('Описание')->rows(2),
                        ])->columnSpan(1),
                        self::image('image', 'Фото', 'cars/sections')->columnSpan(1),
                    ]),
            ]);
    }

    private static function specs(): Repeater
    {
        return Repeater::make('specs')
            ->hiddenLabel()
            ->addActionLabel('Добавить характеристику')
            ->reorderable()
            ->columns(4)
            ->schema([
                TextInput::make('group.ru')->label('Группа')->placeholder('Двигатель')->datalist([
                    'Двигатель', 'Трансмиссия', 'Размеры', 'Масса', 'Подвеска', 'Безопасность', 'Комфорт',
                ]),
                TextInput::make('name.ru')->label('Параметр (RU)')->required(),
                TextInput::make('value')->label('Значение')->required(),
                Grid::make(1)->schema([
                    TextInput::make('name.tg')->label('Параметр (TJ)'),
                    TextInput::make('name.en')->label('Параметр (EN)'),
                ]),
            ]);
    }

    private static function image(string $name, string $label, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->maxSize(8 * 1024);
    }
}
