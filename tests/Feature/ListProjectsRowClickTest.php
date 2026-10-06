<?php

namespace Tests\Feature;

use App\Filament\Pages\KanbanBoard;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ListProjectsRowClickTest extends TestCase
{
    use DatabaseTransactions;

    public function test_list_row_opens_modal_and_actions_work(): void
    {
        $user = User::where('role', 'admin')->first() ?? User::first();
        $this->actingAs($user);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

        $project = Project::create([
            'owner_name' => 'TEST ListModal',
            'number'     => '#TEST-' . uniqid(),
            'status'     => 'toposyomka',
            'address'    => 'Test address',
            'phones'     => ['+998900000000'],
        ]);

        $c = Livewire::test(ListProjects::class);
        $c->call('loadTable');
        $c->assertOk()->assertSee('TEST ListModal');
        $c->assertSeeHtml("openProjectEditModal(&#039;{$project->id}&#039;)");
        $c->call('openProjectEditModal', (string) $project->id)
          ->assertDispatched('open-edit-modal', id: $project->id);

        $c->dispatch('kb-move', id: $project->id, status: 'yangi_toposyomka');
        $this->assertSame('yangi_toposyomka', $project->fresh()->status);
        $c->dispatch('kb-request-payment', id: $project->id);
        $this->assertNotNull($project->fresh()->payment_requested_at);
        $c->dispatch('kb-open-route', id: $project->id, status: 'x')->assertRedirect();

        $k = Livewire::test(KanbanBoard::class);
        $k->assertOk();
        $k->dispatch('kb-cancel-request', id: $project->id);
        $this->assertNull($project->fresh()->payment_requested_at);
        $k->dispatch('kb-move', id: $project->id, status: 'toposyomka');
        $this->assertSame('toposyomka', $project->fresh()->status);
    }
}