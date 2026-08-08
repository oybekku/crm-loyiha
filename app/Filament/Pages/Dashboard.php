<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationLabel = 'My Perfect Home';
    protected ?string $heading    = '';
    protected ?string $subheading = '';

    // Xisobchi umumiy Dashboard'ni (moliyaviy vidjetlar bilan) umuman
    // ko'rmasligi kerak — o'zining "Yangi loyihalar" navbatiga yo'naltiriladi.
    public function mount(): void
    {
        if (auth()->user()?->isHisobchi()) {
            $this->redirect(XisobchiQueue::getUrl());
        }
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
