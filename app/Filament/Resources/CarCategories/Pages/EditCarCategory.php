<?php

namespace App\Filament\Resources\CarCategories\Pages;

use App\Filament\Resources\CarCategories\CarCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarCategory extends EditRecord
{
    protected static string $resource = CarCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
