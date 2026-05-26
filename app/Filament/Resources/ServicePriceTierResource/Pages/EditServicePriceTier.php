<?php

namespace App\Filament\Resources\ServicePriceTierResource\Pages;

use App\Filament\Resources\ServicePriceTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServicePriceTier extends EditRecord
{
    protected static string $resource = ServicePriceTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label("O'chirish"),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
