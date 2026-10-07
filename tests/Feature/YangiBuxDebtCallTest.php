<?php

namespace Tests\Feature;

use App\Filament\Pages\MijozQarzlari;
use App\Filament\Pages\YangiBux;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class YangiBuxDebtCallTest extends TestCase
{
    use DatabaseTransactions;

    private function debtor(string $name, string $createdAt): Project
    {
        $p = Project::create([
            'owner_name'  => $name,
            'number'      => '#TEST-' . uniqid(),
            'status'      => 'toposyomka',
            'address'     => 'Test',
            'phones'      => [['phone' => '+998901234567'], ['phone' => '+998']],
            'total_price' => 999999999,
            'paid_amount' => 0,
        ]);
        Project::whereKey($p->id)->update(['created_at' => $createdAt]);
        return $p->fresh();
    }

    public function test_debt_tab_phone_comment_and_call_toggle(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

        $p = $this->debtor('TEST Qarzdor', now()->toDateTimeString());

        $c = Livewire::test(YangiBux::class)->call('setTab', 'qarzlar');
        $c->assertOk()->assertSee('TEST QARZDOR')->assertSee('+998 90 123 45 67')
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

    public function test_month_filter_and_manager_page(): void
    {
        $this->debtor('TEST Eski Oy', '2001-03-15 10:00:00');
        $this->debtor('TEST Yangi Oy', '2001-04-15 10:00:00');

        // Menejer: alohida sahifa ochiladi, chap panelda link bor, Yangi bux esa yopiq
        $m = User::create([
            'name' => 'TEST Menejer', 'email' => 'test-m-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'menejer', 'is_active' => true, 'permissions' => [],
        ]);
        $this->actingAs($m);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $this->get('/admin/mijozlar-qarzlari')->assertOk()->assertSee('Mijozlar qarzlari</a>', false);
        $this->assertFalse(YangiBux::canAccess());

        $c = Livewire::test(MijozQarzlari::class);
        $c->assertOk()->assertSee('TEST ESKI OY')->assertSee('TEST YANGI OY')->assertSee('Mart 2001');

        $c->set('debtMonth', '2001-03');
        $c->assertSee('TEST ESKI OY')->assertDontSee('TEST YANGI OY');
        // 15 belgidan uzun FISH qisqartiriladi, to'liq ismi title'da
        $this->debtor('Mamadaliyeva Gulbaxor Talibjanovna', '2001-03-20 10:00:00');
        $c->call('$refresh')->assertSee('MAMADALIYEVA GU…')->assertSeeHtml('title="MAMADALIYEVA GULBAXOR TALIBJANOVNA"');

        $c->set('debtMonth', '');
        $c->assertSee('TEST YANGI OY');

        // Qatorga bosish → tahrirlash oynasi; oynaning amallari (kb-*) shu sahifada ishlaydi
        $p0 = Project::where('owner_name', 'TEST Eski Oy')->first();
        $c->assertSeeHtml("dispatch('open-edit-modal', { id: {$p0->id} })");
        $this->get('/admin/mijozlar-qarzlari')->assertSeeLivewire('project-edit-modal');
        $c->dispatch('kb-move', id: $p0->id, status: 'eskiz_loyiha');
        $this->assertSame('eskiz_loyiha', $p0->fresh()->status);
        $c->dispatch('kb-open-route', id: $p0->id, status: 'x')->assertRedirect();
        // Menejer ham izoh/qo'ng'iroq belgilay oladi
        $p = Project::where('owner_name', 'TEST Eski Oy')->first();
        $c->call('toggleDebtCalled', $p->id);
        $this->assertTrue($p->fresh()->debt_called);

        // Bajaruvchiga yopiq
        $b = User::create([
            'name' => 'TEST Baj', 'email' => 'test-b-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'bajaruvchi', 'is_active' => true, 'permissions' => [],
        ]);
        $this->actingAs($b);
        $this->assertFalse(MijozQarzlari::canAccess());
    }
}