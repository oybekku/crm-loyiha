<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Auth\Login;
use Livewire\Livewire;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    public function test_login_redirects_to_loyihalar(): void
    {
        $email = 'pwtest_' . uniqid() . '@crm.uz';
        $user = User::create([
            'name'     => 'PW Test',
            'email'    => $email,
            'password' => bcrypt('password123'),
            'role'     => 'admin',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        try {
            // Bu — productiondagi xuddi shu Livewire login oqimi
            Livewire::test(Login::class)
                ->fillForm(['email' => $email, 'password' => 'password123'])
                ->call('authenticate')
                ->assertRedirect(\App\Filament\Pages\KanbanBoard::getUrl());
        } finally {
            $user->forceDelete();
        }
    }
}
