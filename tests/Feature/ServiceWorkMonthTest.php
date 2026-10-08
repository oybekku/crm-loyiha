<?php

namespace Tests\Feature;

use App\Livewire\ProjectEditModal;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\User;
use App\Models\UserCommissionRate;
use App\Services\EmployeePayableService as E;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

/** Iyunda ochilgan loyihaga keyinroq (joriy oyda) qo'shilgan Ariza — joriy oy oyligiga tushadi. */
class ServiceWorkMonthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_later_added_service_goes_to_its_own_month(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $worker = User::create([
            'name' => 'TEST Arizachi', 'email' => 'test-wm-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'role' => 'bajaruvchi', 'is_active' => true, 'commission_rate' => 20,
        ]);
        $now = now()->format('Y-m');
        // Joriy oydan boshlab boshqa foiz — Ariza shu foiz bilan hisoblanishi kerak
        UserCommissionRate::create(['user_id' => $worker->id, 'rate' => 10, 'effective_month' => $now]);

        $p = Project::create(['owner_name' => 'TEST Umarov Oybek', 'number' => '#TEST-' . uniqid(), 'status' => 'eskiz_loyiha',
            'address' => 'T', 'phones' => [['phone' => '+998901234567']]]);
        Project::whereKey($p->id)->update(['created_at' => '2001-06-15 10:00:00']);

        // Loyiha bilan birga ochilgan xizmat — loyiha oyi
        $topo = ProjectService::create(['project_id' => $p->id, 'service_name' => 'toposyomka', 'price' => 1000000,
            'assigned_user_id' => $worker->id, 'completed_at' => now()]);
        $this->assertSame('2001-06', $topo->fresh()->work_month);

        // Tahrirlash oynasidan keyinroq qo'shilgan Ariza — joriy oy
        Livewire::test(ProjectEditModal::class)->call('openEditInfoModal', $p->id)
            ->set('ei_newSvcType', 'ariza')->set('ei_newSvcPrice', '500000')->set('ei_newSvcUser', $worker->id)
            ->call('eiAddService')->assertHasNoErrors();
        $ariza = ProjectService::where('project_id', $p->id)->where('service_name', 'ariza')->firstOrFail();
        $this->assertSame($now, $ariza->work_month);
        $ariza->update(['completed_at' => now()]);

        // Oylik: Ariza — joriy oyda (10% foiz bilan), iyunda emas
        [$y, $m] = array_map('intval', explode('-', $now));
        $cur = E::workSummaryForMonth($y, $m)[$worker->id] ?? null;
        $this->assertNotNull($cur);
        $this->assertEquals(50000, $cur['done_comm']);           // 500 000 * 10%
        $jun = E::workSummaryForMonth(2001, 6)[$worker->id] ?? null;
        $this->assertEquals(200000, $jun['done_comm']);           // faqat Toposyomka: 1 000 000 * 20%
        $this->assertSame(1, E::statsForUser($worker, $now)['done_services']);

        // Kanban/ro'yxat: loyiha ikkala oyda ham ko'rinadi, boshqa oyda emas
        $this->assertTrue(Project::visibleInMonth(2001, 6)->whereKey($p->id)->exists());
        $this->assertTrue(Project::visibleInMonth($y, $m)->whereKey($p->id)->exists());
        $this->assertFalse(Project::visibleInMonth(2001, 7)->whereKey($p->id)->exists());
    }
}