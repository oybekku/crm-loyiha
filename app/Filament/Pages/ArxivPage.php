<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Services\ArxivBackupService;
use App\Traits\HasMenuPermission;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ArxivPage extends Page
{
    use HasMenuPermission, WithPagination, WithFileUploads;

    protected static string  $view            = 'filament.pages.arxiv';
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arxiv';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $title           = '';

    public string  $filterCategory = '';
    public string  $filterStatus   = '';
    public string  $search         = '';
    public string  $sortField      = 'updated_at';
    public string  $sortDir        = 'desc';
    public ?int    $selectedId     = null;

    // Backup/Restore
    public array   $checkedIds      = [];
    public bool    $showImportModal = false;
    public string  $importConflict  = 'skip';
    public ?string $importResult    = null;
    public array   $importPreview   = [];
    public         $backupFile      = null; // uploaded zip

    public const ARCHIVE_STATUSES = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];

    public const SERVICE_LABELS = [
        'toposyomka'          => 'Toposyomka',
        'geologiya'           => 'Geologiya',
        'eskiz_loyiha'        => 'Eskiz loyiha',
        'texnik_korik'        => "Texnik ko'rik",
        'ariza'               => 'Ariza',
        'konstruksiya'        => 'Konstruksiya (K/R)',
        'arxitektura'         => 'Arxitektura',
        'smeta'               => 'Smeta',
        'ichki_dizayn'        => 'Ichki dizayn (Interyer)',
        'tashqi_dizayn'       => 'Tashqi dizayn',
        'mualliflik_nazorati' => 'Mualliflik nazorati',
        'laboratoriya'        => 'Laboratoriya',
        'kadastr'             => 'Kadastr',
        'maxsus_xizmat'       => 'Maxsus xizmat',
    ];

    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }

    public function setCategory(string $cat): void
    {
        $this->filterCategory = ($this->filterCategory === $cat) ? '' : $cat;
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->filterStatus = ($this->filterStatus === $status) ? '' : $status;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowed = ['owner_name', 'updated_at', 'created_at', 'total_price', 'status'];
        if (!in_array($field, $allowed)) return;

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'desc';
        }
        $this->resetPage();
    }

    public function selectProject(?int $id): void
    {
        $this->selectedId = ($this->selectedId === $id) ? null : $id;
    }

    public function toggleCheck(int $id): void
    {
        if (in_array($id, $this->checkedIds)) {
            $this->checkedIds = array_values(array_diff($this->checkedIds, [$id]));
        } else {
            $this->checkedIds[] = $id;
        }
    }

    public function selectAllVisible(array $ids): void
    {
        $this->checkedIds = array_unique(array_merge($this->checkedIds, $ids));
    }

    public function clearChecked(): void
    {
        $this->checkedIds = [];
    }

    public function exportSelected(): mixed
    {
        if (!auth()->user()?->isAdmin()) return null;
        if (empty($this->checkedIds)) {
            Notification::make()->title("Hech narsa tanlanmagan")->warning()->send();
            return null;
        }
        return $this->doExport($this->checkedIds);
    }

    public function exportAll(): mixed
    {
        if (!auth()->user()?->isAdmin()) return null;
        $archiveKeys = \App\Models\ProjectStatus::allOrdered()
            ->where('is_archive', true)->pluck('key')->toArray();
        if (empty($archiveKeys)) $archiveKeys = self::ARCHIVE_STATUSES;
        $ids = Project::whereIn('status', $archiveKeys)->pluck('id')->toArray();
        if (empty($ids)) {
            Notification::make()->title("Arxivda loyiha yo'q")->warning()->send();
            return null;
        }
        return $this->doExport($ids);
    }

    private function doExport(array $ids): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $zipPath  = ArxivBackupService::export($ids);
        $filename = 'arxiv-backup-' . now()->format('Y-m-d') . '.zip';
        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->importPreview   = [];
        $this->importResult    = null;
        $this->backupFile      = null;
        $this->importConflict  = 'skip';
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
    }

    public function previewImport(): void
    {
        if (!$this->backupFile) return;
        $path = $this->backupFile->getRealPath();
        try {
            $result = ArxivBackupService::importPreview($path);
            $this->importPreview = $result['preview'];
            Notification::make()->title("Fayl o'qildi: {$result['total']} ta loyiha")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title("Xato: " . $e->getMessage())->danger()->send();
        }
    }

    public function runImport(): void
    {
        if (!auth()->user()?->isAdmin() || !$this->backupFile) return;
        $path = $this->backupFile->getRealPath();
        try {
            $result = ArxivBackupService::import($path, $this->importConflict);
            $this->importResult = "✅ Import tugadi: {$result['imported']} ta yangi, {$result['updated']} ta yangilandi, {$result['skipped']} ta o'tkazib yuborildi.";
            $this->importPreview = [];
            Notification::make()->title("Import muvaffaqiyatli!")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title("Import xatosi: " . $e->getMessage())->danger()->send();
        }
    }

    public function getViewData(): array
    {
        $archiveKeys = \App\Models\ProjectStatus::allOrdered()
            ->where('is_archive', true)
            ->pluck('key')
            ->toArray();
        if (empty($archiveKeys)) {
            $archiveKeys = self::ARCHIVE_STATUSES;
        }

        $allowed = ['owner_name', 'updated_at', 'created_at', 'total_price', 'status'];
        $sortField = in_array($this->sortField, $allowed) ? $this->sortField : 'updated_at';
        $sortDir   = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $query = Project::with(['assignedUsers', 'services', 'payments', 'files'])
            ->whereIn('status', $archiveKeys)
            ->orderBy($sortField, $sortDir);

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if (trim($this->search) !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('owner_name', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%")
                  ->orWhere('number', 'like', "%{$s}%");
            });
        }

        $total    = $query->count();
        $projects = $query->paginate(25);

        $archiveStatuses = \App\Models\ProjectStatus::where('is_archive', true)
            ->orderBy('sort_order')->get();

        $selectedProject = $this->selectedId
            ? Project::with(['assignedUsers', 'services', 'payments', 'files'])
                ->find($this->selectedId)
            : null;

        return [
            'projects'        => $projects,
            'total'           => $total,
            'statusOptions'   => $archiveStatuses->pluck('label', 'key')->toArray(),
            'categoryOptions' => Project::categoryOptions(),
            'selectedProject' => $selectedProject,
        ];
    }
}
