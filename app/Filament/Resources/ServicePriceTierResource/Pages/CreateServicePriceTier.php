<?php

namespace App\Filament\Resources\ServicePriceTierResource\Pages;

use App\Filament\Resources\ServicePriceTierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServicePriceTier extends CreateRecord
{
    protected static string $resource = ServicePriceTierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
