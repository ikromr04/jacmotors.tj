<?php

namespace App\Filament\Resources\Cars\Tables;

use App\Models\Car;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                ImageColumn::make('card_image')->label('')->disk('public')->imageWidth(96)->imageHeight(56),
                TextColumn::make('name')->label('Модель')->searchable()->weight('bold'),
                TextColumn::make('category.slug')
                    ->label('Категория')
                    ->formatStateUsing(fn (Car $record) => $record->category?->tr('name', 'ru'))
                    ->badge(),
                TextColumn::make('price_from')->label('Цена от')->numeric(thousandsSeparator: ' ')->suffix(' смн'),
                ToggleColumn::make('is_published')->label('Опубликована'),
            ])
            ->filters([
                SelectFilter::make('car_category_id')
                    ->label('Категория')
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->tr('name', 'ru')),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('На сайте')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Car $record) => route('cars.show', ['locale' => 'ru', 'car' => $record]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
