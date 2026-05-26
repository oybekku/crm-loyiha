<?php

namespace App\Filament\Resources\ServicePriceTierResource\Pages;

use App\Filament\Resources\ServicePriceTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServicePriceTiers extends ListRecords
{
    protected static string $resource = ServicePriceTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Yangi narx qo\'shish'),
        ];
    }
}
