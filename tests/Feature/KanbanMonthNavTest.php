<?php

namespace Tests\Feature;

use App\Filament\Pages\KanbanBoard;
use App\Models\User;
use App\Services\EmployeePayableService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KanbanMonthNavTest extends TestCase
{
    use DatabaseTransactions;

    public function test_month_arrows_are_plain_links_keeping_filters(): void
    {
        $this->actingAs(User::where('role', 'admin')->first());
        $r = $this->get('/admin/kanban-board?status=toposyomka&year=2026&month=1')->assertOk();
        // Oldingi oy — yil chegarasidan o'tadi, status saqlanadi
        $r->assertSee('kanban-board?status=toposyomka&amp;year=2025&amp;month=12', false)
          ->assertSee('kanban-board?status=toposyomka&amp;year=2026&amp;month=2', false)
          ->assertDontSee('wire:click="kbChangeMonth', false);
    }

    public function test_rate_cache_matches_direct_query_and_flushes(): void
    {
        $u = User::first();
        EmployeePayableService::flushRateCache();
        $a = EmployeePayableService::rateFor($u, '2026-09');
        $this->assertSame($a, EmployeePayableService::rateFor($u, '2026-09'));

        \App\Models\UserCommissionRate::create(['user_id' => $u->id, 'rate' => 7.5, 'effective_month' => '2026-09']);
        $this->assertSame(7.5, EmployeePayableService::rateFor($u, '2026-09')); // saqlanganda xotira tozalandi
    }
}