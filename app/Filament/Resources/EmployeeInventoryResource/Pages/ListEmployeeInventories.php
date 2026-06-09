<?php

namespace App\Filament\Resources\EmployeeInventoryResource\Pages;

use App\Filament\Resources\EmployeeInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeInventories extends ListRecords
{
    protected static string $resource = EmployeeInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Yangi inventar'),
        ];
    }
}
