<?php

namespace App\Filament\Resources\EmployeeInventoryResource\Pages;

use App\Filament\Resources\EmployeeInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeInventory extends EditRecord
{
    protected static string $resource = EmployeeInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label("O'chirish"),
        ];
    }
}
