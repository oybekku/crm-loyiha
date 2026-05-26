<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Traits\HasMenuPermission;
use Filament\Pages\Page;
use Livewire\WithPagination;

class ArxivPage extends Page
{
    use HasMenuPermission, WithPagination;

    protected static string  $view            = 'filament.pages.arxiv';
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arxiv';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $title           = '';

    public string  $filterCategory = '';
    public string  $filterStatus   = '';
    public string  $search         = '';
    public string  $sortField      = 'updated_at';
    public string  $sortDir        = 'desc';
    public ?int    $selectedId     = null;

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

    public function getViewData(): array
    {
        $archiveKeys = \App\Models\ProjectStatus::where('is_archive', true)
            ->pluck('key')->toArray();
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
