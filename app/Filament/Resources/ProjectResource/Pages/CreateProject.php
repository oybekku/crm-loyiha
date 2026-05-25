<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectFile;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        $files = $this->data['uploaded_files'] ?? [];
        if (empty($files)) {
            return;
        }

        foreach ($files as $filePath) {
            $fullPath = Storage::disk('public')->path($filePath);
            $size = file_exists($fullPath) ? filesize($fullPath) : 0;
            $mime = file_exists($fullPath) ? mime_content_type($fullPath) : null;

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
