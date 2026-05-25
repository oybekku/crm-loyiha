<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProjectFile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
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
