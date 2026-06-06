<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProjects extends ListRecords
{
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
