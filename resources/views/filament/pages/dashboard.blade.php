<x-filament-panels::page>
    @include('filament.partials.report-tabs')
    @livewire(\App\Filament\Widgets\WelcomeHeroWidget::class)
    @livewire('project-edit-modal')
</x-filament-panels::page>
