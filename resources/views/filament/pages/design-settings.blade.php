<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-between">
            <x-filament::button
                color="gray"
                wire:click="resetToDefaults"
                wire:confirm="Standart sozlamalarga qaytmoqchimisiz?"
                type="button"
                icon="heroicon-o-arrow-path"
            >
                Standartga qaytarish
            </x-filament::button>

            <x-filament::button
                type="submit"
                size="lg"
                icon="heroicon-o-check"
            >
                Saqlash
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
