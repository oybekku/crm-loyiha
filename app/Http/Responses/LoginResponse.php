<?php

namespace App\Http\Responses;

use App\Filament\Pages\KanbanBoard;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

/**
 * Login'dan keyin — Dashboard o'rniga "Loyihalar" (Kanban) sahifasi ochiladi.
 *
 * Eslatma: qaytish turini belgilamaymiz — Filament login Livewire ichida
 * ishlagani uchun redirect() Livewire'ning Redirector'ini qaytaradi
 * (Illuminate\Http\RedirectResponse emas).
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Login'dan keyin ochiladigan birinchi sahifada bir martalik
        // "zagruzka" animatsiyasi ko'rsatilsin (AdminPanelProvider'dagi
        // panels::body.start render hook shu bayroqni o'qib, darhol
        // o'chiradi — shu bilan faqat shu bir sahifada ko'rinadi).
        session(['bh_show_splash' => true]);

        return redirect()->intended(KanbanBoard::getUrl());
    }
}
