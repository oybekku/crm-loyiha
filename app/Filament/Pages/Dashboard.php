<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationLabel = 'My Project Home';
    protected ?string $heading    = '';
    protected ?string $subheading = '';

    public function getColumns(): int | array
    {
        return 1;
    }
}
