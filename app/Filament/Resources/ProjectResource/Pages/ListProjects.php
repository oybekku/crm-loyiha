<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Pages\KanbanBoard;
use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Traits\HandlesProjectEditModalActions;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListProjects extends ListRecords
{
    use HandlesProjectEditModalActions;

    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.list-projects';

    // Tanlangan davr — loyiha ochilgan oyiga qarab
    public ?int $selYear  = null;
    public ?int $selMonth = null;

    public function mount(): void
    {
        parent::mount();

        $this->selYear  ??= (int) now()->year;
        $this->selMonth ??= (int) now()->month;

        $status = request()->get('status');
        if ($status) {
            $this->tableFilters['status']['value'] = $status;
        }
    }

    public function goMonth(int $delta): void
    {
        $date = \Carbon\Carbon::create($this->selYear, $this->selMonth, 1)->addMonths($delta);
        $this->selYear  = (int) $date->year;
        $this->selMonth = (int) $date->month;
        $this->resetPage();
    }

    public function getMonthLabel(): string
    {
        return \Carbon\Carbon::create($this->selYear, $this->selMonth, 1)->translatedFormat('F Y');
    }

    // Qatorga bosilganda — tahrirlash sahifasiga o'tish o'rniga Kanbandagi
    // kabi "Loyiha ma'lumotini tahrirlash" oynasi (ProjectEditModal) ochiladi.
    public function table(Table $table): Table
    {
        return parent::table($table)
            ->recordUrl(null)
            ->recordAction('openProjectEditModal');
    }

    public function openProjectEditModal(string $recordKey): void
    {
        $this->dispatch('open-edit-modal', id: (int) $recordKey);
    }

    // "Yuborish" va "Hodim" oynalari faqat Kanban doskada bor — o'sha yerga
    // loyiha oynasi ochiq holda o'tkazamiz.
    #[On('kb-open-route')]
    public function kbOpenRoute(int $id, string $status): void
    {
        $this->redirect(KanbanBoard::getUrl(['open' => $id]));
    }

    #[On('kb-open-assign')]
    public function kbOpenAssign(int $id): void
    {
        $this->redirect(KanbanBoard::getUrl(['open' => $id]));
    }

    // "Tugallandi" bosilganda yuborilgan SMS natijasi — Kanbandagi kabi toast
    public function dehydrate(): void
    {
        foreach (Project::$pendingSmsNotifications as $note) {
            $this->dispatch('notify',
                type: $note['ok'] ? 'success' : 'error',
                message: $note['message']
            );
        }
        Project::$pendingSmsNotifications = [];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ?->whereYear('created_at', $this->selYear)
            ->whereMonth('created_at', $this->selMonth);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
