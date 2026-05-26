<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        // Admin roli uchun default ruxsatlar shart emas
        if (!$record->isAdmin() && empty($record->permissions)) {
            $record->update(['permissions' => User::defaultPermissions()]);
        }
    }
}
