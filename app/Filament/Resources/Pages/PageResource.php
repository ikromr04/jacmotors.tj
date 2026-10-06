<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\Translatable;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'страница';

    protected static ?string $pluralModelLabel = 'Страницы';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('slug')->label('URL')->required()->disabled(fn (string $operation) => $operation === 'edit')
                ->helperText('Поддерживаются: about, guarantee, privacy'),
            Toggle::make('is_published')->label('Опубликована')->default(true)->inline(false)
                ->helperText('Если выключить, страница перестанет открываться на сайте.'),
            Translatable::tabs(fn (string $locale, bool $default) => [
                TextInput::make("title.{$locale}")->label('Заголовок')->required($default),
                RichEditor::make("body.{$locale}")->label('Текст'),
                Textarea::make("meta_description.{$locale}")->label('Meta description')->rows(2)->maxLength(170),
            ]),
            FileUpload::make('attachment')->label('Прикреплённый файл (PDF)')->disk('public')->directory('pages')
                ->acceptedFileTypes(['application/pdf'])->downloadable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Заголовок')->formatStateUsing(fn (Page $record) => $record->tr('title', 'ru')),
                TextColumn::make('slug')->label('URL')->prefix('/'),
                ToggleColumn::make('is_published')->label('Опубликована'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('На сайте')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Page $record) => url("/ru/{$record->slug}"))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
