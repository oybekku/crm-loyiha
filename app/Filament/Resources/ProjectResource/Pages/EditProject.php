<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectFile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public function mapAddressSelected(string $address, string $lat = '', string $lng = ''): void
    {
        $data = $this->data;
        $data['address'] = $address;
        if ($lat !== '') $data['latitude']  = $lat;
        if ($lng !== '') $data['longitude'] = $lng;
        $this->data = $data;

        // Browser eventini yuboramiz — Alpine.js map-picker da ushlaydi
        $this->dispatch('bh-fill-address', address: $address);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('paymentLogs')
                ->label("Ma'lumotlar")
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn() => auth()->user()?->isAdmin())
                ->modalHeading(fn() => "To'lov tarixi — " . $this->record->owner_name)
                ->modalContent(fn() => view('filament.modals.payment-logs', ['project' => $this->record->load('paymentLogs.user', 'payments')]))
                ->modalFooterActions([])
                ->modalWidth('2xl'),

            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        // AssignedUsers dan olib tashlangan hodimlarni services dan ham tozalaymiz
        $newUserIds = collect($this->data['assignedUsers'] ?? [])->map(fn($id) => (int) $id)->toArray();
        $oldUserIds = $this->record->assignedUsers()->pluck('users.id')->toArray();
        $removedIds = array_diff($oldUserIds, $newUserIds);

        if (!empty($removedIds)) {
            $this->record->services()
                ->whereIn('assigned_user_id', $removedIds)
                ->update(['assigned_user_id' => null]);
        }
    }

    protected function afterSave(): void
    {
        $files = $this->data['uploaded_files'] ?? [];
        if (empty($files)) {
            return;
        }

        foreach ($files as $filePath) {
            if ($this->record->files()->where('file_path', $filePath)->exists()) {
                continue;
            }

            $fullPath = Storage::disk('public')->path($filePath);
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            $mime = file_exists($fullPath)
                ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath)
                : null;

            ProjectFile::create([
                'project_id'  => $this->record->id,
                'file_name'   => basename($filePath),
                'file_path'   => $filePath,
                'file_type'   => $mime,
                'file_size'   => $size,
                'category'    => 'hujjat',
                'uploaded_by' => auth()->id(),
            ]);
        }
    }
}
