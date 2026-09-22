<?php

namespace App\Filament\Resources\ContactRequestResource\Pages;

use App\Filament\Resources\ContactRequestResource;
use App\Models\ContactRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageContactRequests extends ManageRecords
{
    protected static string $resource = ContactRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            ContactRequest::TYPE_QONGIROQ => Tab::make("Qayta qo'ng'iroq")
                ->query(fn (Builder $query) => $query->where('type', ContactRequest::TYPE_QONGIROQ))
                ->badge(ContactRequest::where('type', ContactRequest::TYPE_QONGIROQ)->where('is_handled', false)->count() ?: null),

            ContactRequest::TYPE_ZAYAVKA => Tab::make("Sayt orqali buyurtmalar")
                ->query(fn (Builder $query) => $query->where('type', ContactRequest::TYPE_ZAYAVKA))
                ->badge(ContactRequest::where('type', ContactRequest::TYPE_ZAYAVKA)->where('is_handled', false)->count() ?: null),
        ];
    }
}
