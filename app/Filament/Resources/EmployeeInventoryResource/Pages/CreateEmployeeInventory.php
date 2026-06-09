<?php

namespace App\Filament\Resources\EmployeeInventoryResource\Pages;

use App\Filament\Resources\EmployeeInventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeInventory extends CreateRecord
{
    protected static string $resource = EmployeeInventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['given_by'] = auth()->id();
        return $data;
    }
}
