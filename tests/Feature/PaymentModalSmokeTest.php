<?php

namespace Tests\Feature;

use App\Livewire\PaymentModal;
use App\Filament\Pages\KanbanBoard;
use App\Models\FinancialAccount;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentModalSmokeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_add_edit_delete_cycle_works(): void
    {
        $user = User::where('role', 'admin')->first() ?? User::first();
        $this->actingAs($user);

        $project = Project::create([
            'owner_name' => 'TEST PaymentModalSmokeTest',
            'number'     => '#TEST-' . uniqid(),
            'status'     => 'toposyomka',
            'address'    => 'Test address',
            'phones'     => ['+998900000000'],
        ]);
        $svc = ProjectService::create([
            'project_id'   => $project->id,
            'service_name' => 'toposyomka',
            'price'        => 500000,
            'final_price'  => 500000,
        ]);
        $acc = FinancialAccount::where('type', 'naqd')->first();

        $c = Livewire::test(PaymentModal::class);
        $c->call('openPaymentModal', $project->id, false);
        $c->assertSet('showPaymentModal', true);
        $c->assertSet('paymentProjectId', $project->id);

        $c->set('paymentAmount', '1000');
        $c->set('paymentSelectedServices', [$svc->service_name]);
        $c->set('paymentDate', now()->format('Y-m-d'));
        if ($acc) $c->set('paymentAccountId', $acc->id);
        $c->call('savePayment', true);
        $c->assertHasNoErrors();
        $c->assertDispatched('kb-payment-saved');
        $c->assertDispatched('print-receipt');

        $payment = Payment::where('project_id', $project->id)->where('amount', 1000)->latest('id')->firstOrFail();

        $c2 = Livewire::test(PaymentModal::class);
        $c2->call('openEditPayment', $payment->id);
        $c2->assertSet('showEditPaymentModal', true);
        $c2->set('editPaymentAmount', '2000');
        $c2->call('saveEditPayment');
        $c2->assertHasNoErrors();
        $c2->assertDispatched('kb-payment-saved');
        $payment->refresh();
        $this->assertEquals(2000.0, (float) $payment->amount);

        $c3 = Livewire::test(PaymentModal::class);
        $c3->call('openDeletePayment', $payment->id);
        $c3->assertSet('showDeletePaymentModal', true);
        $c3->call('confirmDeletePayment');
        $c3->assertDispatched('kb-payment-saved');
        $this->assertNull(Payment::find($payment->id));

        $kb = Livewire::test(KanbanBoard::class);
        $kb->call('kbPaymentSaved');
        $kb->assertOk();
    }

    // ProjectEditModal va kanban-board.blade.php'dagi tugmalar 'kb-open-payment'
    // eventini {id, fromQueue} shaklida yuboradi (parametr nomi $projectId
    // emas, $id) — Livewire #[On] atributi metod parametrlarini event
    // maydonlari NOMI bo'yicha bog'laydi, shu sababli ko'prik metod ($id nomi
    // bilan) shart. Bu metod nomi/imzosi noto'g'ri o'zgartirilsa aynan shu
    // test qulaydi (2026-08-27 productionda aynan shu sabab bilan buzilgan edi).
    public function test_kb_open_payment_bridge_matches_dispatched_event_shape(): void
    {
        $user = User::where('role', 'admin')->first() ?? User::first();
        $this->actingAs($user);

        $project = Project::create([
            'owner_name' => 'TEST kb-open-payment bridge',
            'number'     => '#TEST-' . uniqid(),
            'status'     => 'tolov_jarayonida',
            'address'    => 'Test address',
            'phones'     => ['+998900000000'],
        ]);

        $c = Livewire::test(PaymentModal::class);
        $c->call('kbOpenPayment', id: $project->id, fromQueue: true);
        $c->assertSet('showPaymentModal', true);
        $c->assertSet('paymentProjectId', $project->id);
        $c->assertSet('paymentFromQueue', true);
    }
}
