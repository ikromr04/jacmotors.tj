<?php

namespace App\Filament\Resources\Posts;

use App\Enums\PostType;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Support\Translatable;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'публикация';

    protected static ?string $pluralModelLabel = 'Акции и новости';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Select::make('type')->label('Тип')->options(PostType::class)->default(PostType::Promotion)->required(),
            TextInput::make('slug')->label('URL')->required()->alphaDash()->unique(ignoreRecord: true),
            FileUpload::make('image')->label('Обложка')->image()->disk('public')->directory('posts')->maxSize(8 * 1024),
            Translatable::tabs(fn (string $locale, bool $default) => [
                TextInput::make("title.{$locale}")->label('Заголовок')->required($default)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, string $operation) use ($default) {
                        if ($default && $operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                Textarea::make("excerpt.{$locale}")->label('Анонс')->rows(2),
                RichEditor::make("body.{$locale}")->label('Текст'),
            ]),
            Grid::make(3)->columnSpanFull()->schema([
                DateTimePicker::make('published_at')->label('Дата публикации')->default(now()),
                DatePicker::make('ends_at')->label('Акция действует до')
                    ->visible(fn ($get) => $get('type') === PostType::Promotion || $get('type') === PostType::Promotion->value),
                Toggle::make('is_published')->label('Опубликовано')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageWidth(80)->imageHeight(48),
                TextColumn::make('title')->label('Заголовок')->formatStateUsing(fn (Post $record) => $record->tr('title', 'ru'))->wrap(),
                TextColumn::make('type')->label('Тип')->badge(),
                TextColumn::make('published_at')->label('Опубликовано')->dateTime('d.m.Y')->sortable(),
                TextColumn::make('ends_at')->label('До')->date('d.m.Y'),
                ToggleColumn::make('is_published')->label('Вкл.'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Тип')->options(PostType::class),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
