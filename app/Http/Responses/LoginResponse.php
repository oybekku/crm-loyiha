<?php

namespace App\Http\Responses;

use App\Filament\Pages\KanbanBoard;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;

/**
 * Login'dan keyin — Dashboard o'rniga "Loyihalar" (Kanban) sahifasi ochiladi.
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->to(KanbanBoard::getUrl());
    }
}
