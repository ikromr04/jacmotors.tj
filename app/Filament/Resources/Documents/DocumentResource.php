<?php

namespace App\Filament\Resources\Documents;

use App\Enums\DocumentType;
use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Support\Translatable;
use App\Models\Document;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'документ';

    protected static ?string $pluralModelLabel = 'Руководства и регламенты';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('type')->label('Тип')->options(DocumentType::class)->required(),
            Select::make('car_id')->label('Модель')->relationship('car', 'name')->preload(),
            Translatable::tabs(fn (string $locale, bool $default) => [
                TextInput::make("title.{$locale}")->label('Название')->required($default),
            ]),
            FileUpload::make('file')->label('PDF-файл')->disk('public')->directory('documents')
                ->acceptedFileTypes(['application/pdf'])->maxSize(50 * 1024)->downloadable()->required(),
            FileUpload::make('image')->label('Изображение')->image()->disk('public')->directory('documents'),
            TextInput::make('sort')->label('Порядок')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageWidth(80)->imageHeight(48),
                TextColumn::make('title')->label('Название')->formatStateUsing(fn (Document $record) => $record->tr('title', 'ru')),
                TextColumn::make('type')->label('Тип')->badge(),
                TextColumn::make('car.name')->label('Модель'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Тип')->options(DocumentType::class),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
