<?php

namespace Tests\Feature;

use App\Filament\Pages\YangiBux;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class YangiBuxDebtCallTest extends TestCase
{
    use DatabaseTransactions;

    public function test_debt_tab_phone_comment_and_call_toggle(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

        $p = Project::create([
            'owner_name'  => 'TEST Qarzdor',
            'number'      => '#TEST-' . uniqid(),
            'status'      => 'toposyomka',
            'address'     => 'Test',
            'phones'      => [['phone' => '+998901234567'], ['phone' => '+998']],
            'total_price' => 999999999,
            'paid_amount' => 0,
        ]);

        $c = Livewire::test(YangiBux::class)->call('setTab', 'qarzlar');
        $c->assertOk()->assertSee('TEST Qarzdor')->assertSee('+998 90 123 45 67')
          ->assertSee('Qilinmadi')->assertSee("Hali telefon qilinmagan");

        $c->call('saveDebtComment', $p->id, '  Ertaga to\'laydi  ');
        $this->assertSame("Ertaga to'laydi", $p->fresh()->debt_comment);

        $c->call('toggleDebtCalled', $p->id);
        $f = $p->fresh();
        $this->assertTrue($f->debt_called);
        $this->assertNotNull($f->debt_called_at);
        $this->assertSame($admin->id, $f->debt_called_by);
        $c->assertSee('Qilindi')->assertSee("Oxirgi qo&#039;ng&#039;iroq: " . $f->debt_called_at->format('d.m.Y H:i'), false);

        // Qaytarilganda sana saqlanib qoladi
        $c->call('toggleDebtCalled', $p->id);
        $this->assertFalse($p->fresh()->debt_called);
        $this->assertNotNull($p->fresh()->debt_called_at);
    }
}