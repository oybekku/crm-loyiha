<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectFile;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function mapAddressSelected(string $address, string $lat = '', string $lng = ''): void
    {
        $data = $this->data;
        $data['address'] = $address;
        if ($lat !== '') $data['latitude']  = $lat;
        if ($lng !== '') $data['longitude'] = $lng;
        $this->data = $data;

        $this->dispatch('bh-fill-address', address: $address);
    }

    protected function afterCreate(): void
    {
        $files     = $this->data['uploaded_files'] ?? [];
        $projectId = $this->record->id;
        if (empty($files)) return;

        foreach ($files as $filePath) {
            // tmp papkadan project papkasiga ko'chirish
            $newPath = 'project-files/' . $projectId . '/' . basename($filePath);
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->move($filePath, $newPath);
            }
            $fullPath = Storage::disk('public')->path($newPath);
            $size     = file_exists($fullPath) ? filesize($fullPath) : 0;
            $mime     = file_exists($fullPath)
                ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath)
                : null;

            ProjectFile::create([
                'project_id'  => $projectId,
                'file_name'   => basename($filePath),
                'file_path'   => $newPath,
                'file_type'   => $mime,
                'file_size'   => $size,
                'category'    => 'hujjat',
                'uploaded_by' => auth()->id(),
            ]);
        }
    }
}
