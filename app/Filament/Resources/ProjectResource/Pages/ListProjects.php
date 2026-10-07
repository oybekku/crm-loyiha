<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Traits\HandlesProjectEditModalActions;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->recordAction('openProjectEditModal')
            // Filtrlar yashirin tugma ichida emas — qidiruv ustida doim ochiq turadi,
            // tanlangan zahoti qo'llanadi ("Qo'llash" tugmasini bosish shart emas)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->deferFilters(false);
    }

    public function openProjectEditModal(string $recordKey): void
    {
        $this->dispatch('open-edit-modal', id: (int) $recordKey);
    }

    // "Tugallandi" bosilganda yuborilgan SMS natijasi — Kanbandagi kabi toast
    // ("Yuborish"/"Hodim" esa traitda — Kanbanga yo'naltiradi)
    public function dehydrate(): void
    {
        $this->flushReadySmsNotifications();
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
