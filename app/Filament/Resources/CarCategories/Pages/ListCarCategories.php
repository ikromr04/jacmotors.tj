<?php

namespace App\Filament\Resources\CarCategories\Pages;

use App\Filament\Resources\CarCategories\CarCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarCategories extends ListRecords
{
    protected static string $resource = CarCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
