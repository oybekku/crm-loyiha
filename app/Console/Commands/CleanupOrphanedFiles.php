<?php

namespace App\Console\Commands;

use App\Models\ProjectFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedFiles extends Command
{
    protected $signature   = 'files:cleanup';
    protected $description = "Bazada yo'q fayllarni storage dan o'chirish";

    public function handle(): void
    {
        $files   = Storage::disk('public')->allFiles('project-files');
        $dbPaths = ProjectFile::pluck('file_path')->toArray();
        $deleted = 0;

        foreach ($files as $file) {
            if (!in_array($file, $dbPaths)) {
                Storage::disk('public')->delete($file);
                $deleted++;
            }
        }

        $this->info("Tozalandi: {$deleted} ta yetim fayl o'chirildi.");
    }
}
