<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FishDisplayTest extends TestCase
{
    use DatabaseTransactions;

    public function test_fish_component_uppercases_and_truncates(): void
    {
        $this->assertSame('MAMADALIYEVA GU…', Project::fishShort('Mamadaliyeva  Gulbaxor Talibjanovna'));
        $this->assertSame('ISOQOV', Project::fishShort(' isoqov '));
        $this->assertSame('—', Project::fishShort(null));

        $html = Blade::render('<x-fish :name="$n" />', ['n' => 'Matnazarova Anastasiya']);
        $this->assertStringContainsString('MATNAZAROVA ANA…', $html);
        $this->assertStringContainsString('title="MATNAZAROVA ANASTASIYA"', $html);
        $this->assertStringContainsString('font-size:14.85px', $html);
    }

    public function test_list_pages_render_with_uppercase_fish(): void
    {
        $this->actingAs(User::where('role', 'admin')->first());
        Project::create([
            'owner_name' => 'Testov Fishjon Uzunismovich', 'number' => '#TEST-' . uniqid(),
            'status' => 'tugallangan', 'address' => 'Test', 'phones' => [['phone' => '+998901234567']],
            'total_price' => 1000, 'paid_amount' => 1000,
        ]);

        foreach (['/admin/arxiv-page', '/admin/monthly-report', '/admin/yangi-bux?tab=kirim', '/admin/buxgalteriya', '/admin/xisobchi-queue', '/admin/projects', '/admin/mijozlar-qarzlari'] as $url) {
            $r = $this->get($url);
            $this->assertContains($r->status(), [200], "$url -> " . $r->status());
        }
        $this->get('/admin/arxiv-page')->assertOk()->assertSee('TESTOV FISHJON…');
    }
}