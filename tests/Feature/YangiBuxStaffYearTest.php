<?php

namespace Tests\Feature;

use App\Filament\Pages\YangiBux;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class YangiBuxStaffYearTest extends TestCase
{
    use DatabaseTransactions;

    public function test_staff_year_table_matches_salary_tab(): void
    {
        $this->actingAs(User::where('role', 'admin')->first());
        $c = Livewire::test(YangiBux::class)->call('setTab', 'hisobotlar')->assertOk()
            ->assertSee('-yil hisoboti / Hodimlar');

        $sy = $c->viewData('staffYear');
        $this->assertIsArray($sy);
        $m = (int) now()->month;
        if (!in_array($m, $sy['months'], true)) $this->markTestSkipped('joriy oy hisobotda yo\'q');

        // Joriy oy "to'lash kerak / ortiqcha" — Oylik maosh tabidagi bilan bir xil
        $salary = Livewire::test(YangiBux::class)->call('setTab', 'oylik')->viewData('salary');
        $rows = collect($salary['rows'] ?? []);
        $checked = 0;
        foreach ($sy['rows'] as $r) {
            $s = $rows->first(fn ($x) => $x['user']->id === $r['user']->id);
            if (!$s) continue;
            $this->assertEqualsWithDelta($s['remaining'], $r['cells'][$m]['kerak'], 0.01, $r['user']->name . ' kerak');
            $this->assertEqualsWithDelta($s['overpaid'], $r['cells'][$m]['ortiq'], 0.01, $r['user']->name . ' ortiqcha');
            $this->assertEqualsWithDelta($s['paid'], $r['cells'][$m]['paid'], 0.01, $r['user']->name . ' tolangan');
            $checked++;
        }
        fwrite(STDERR, "\n[staffYear] tekshirilgan hodimlar: $checked\n");
    }
}