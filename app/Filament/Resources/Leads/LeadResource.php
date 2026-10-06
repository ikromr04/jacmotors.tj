<?php

namespace App\Filament\Resources\Leads;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Продажи';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

    public static function getNavigationBadge(): ?string
    {
        $count = Lead::query()->where('status', LeadStatus::New)->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Данные клиента')->columnSpan(2)->columns(2)->schema([
                TextEntry::make('type')->label('Тип')->badge(),
                TextEntry::make('created_at')->label('Получена')->dateTime('d.m.Y H:i'),
                TextEntry::make('name')->label('Имя')->placeholder('—'),
                TextEntry::make('phone')->label('Телефон')
                    ->url(fn (Lead $record) => 'tel:'.preg_replace('/[^\d+]/', '', $record->phone)),
                TextEntry::make('email')->label('E-mail')->placeholder('—'),
                TextEntry::make('car.name')->label('Модель')->placeholder('—'),
                TextEntry::make('dealer_name')->label('Дилер')->placeholder('—')
                    ->state(fn (Lead $record) => $record->dealer?->tr('name', 'ru')),
                TextEntry::make('city')->label('Город')->placeholder('—'),
                TextEntry::make('subject')->label('Тема')->placeholder('—'),
                TextEntry::make('payment_method')->label('Способ оплаты')->placeholder('—'),
                TextEntry::make('preferred_date')->label('Желаемая дата')->date('d.m.Y')->placeholder('—'),
                TextEntry::make('locale')->label('Язык сайта')->formatStateUsing(fn (?string $state) => strtoupper((string) $state)),
                TextEntry::make('message')->label('Сообщение')->placeholder('—')->columnSpanFull(),
                TextEntry::make('page_url')->label('Страница')->placeholder('—')->columnSpanFull()
                    ->url(fn (Lead $record) => $record->page_url)->openUrlInNewTab(),
                TextEntry::make('utm')->label('UTM')->placeholder('—')->columnSpanFull()
                    ->state(fn (Lead $record) => $record->utm ? collect($record->utm)->map(fn ($v, $k) => "{$k}={$v}")->implode(', ') : null),
            ]),
            Section::make('Обработка')->columnSpan(1)->schema([
                Select::make('status')->label('Статус')->options(LeadStatus::class)->required(),
                Textarea::make('manager_note')->label('Комментарий менеджера')->rows(6),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('type')->label('Тип')->badge(),
                TextColumn::make('name')->label('Имя')->searchable(),
                TextColumn::make('phone')->label('Телефон')->searchable(),
                TextColumn::make('car.name')->label('Модель'),
                SelectColumn::make('status')->label('Статус')->options(LeadStatus::class)->selectablePlaceholder(false),
            ])
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(LeadStatus::class),
                SelectFilter::make('type')->label('Тип')->options(LeadType::class),
                SelectFilter::make('car_id')->label('Модель')->relationship('car', 'name'),
            ])
            ->recordActions([EditAction::make()->label('Открыть')])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
