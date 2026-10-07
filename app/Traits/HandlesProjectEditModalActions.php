<?php

namespace App\Traits;

use App\Models\Project;
use App\Models\ProjectStatusLog;
use Livewire\Attributes\On;

/**
 * ProjectEditModal (loyiha tahrirlash oynasi) amal tugmalari kb-* eventlarini
 * yuboradi. Oyna joylashgan sahifa (Kanban doska, Loyihalar ro'yxati) shu
 * trait orqali ularni qabul qiladi — mantiq bitta joyda turadi.
 *
 * "Yuborish" (kb-open-route) va "Hodim" (kb-open-assign) sahifaning o'z
 * modallariga bog'liq — ularni har bir sahifa o'zi yozadi.
 */
trait HandlesProjectEditModalActions
{
    #[On('kb-request-payment')]
    public function kbRequestPayment(int $id): void { $this->requestPayment($id); }

    #[On('kb-cancel-request')]
    public function kbCancelRequest(int $id): void { $this->cancelPaymentRequest($id); }

    // PaymentModal komponentida to'lov saqlangan/tahrirlangan/o'chirilgan yoki
    // narx o'zgargandan keyin yuboriladi — summa/foizni yangilash uchun
    // sahifani qayta chizishga majburlaydi (bo'sh metod — chaqirilishning
    // o'zi Livewire'ni re-render qilishga yetarli).
    #[On('kb-payment-saved')]
    public function kbPaymentSaved(): void {}

    #[On('kb-mark-complete')]
    public function kbMarkComplete(int $id): void { $this->markComplete($id); }

    #[On('kb-mark-uncomplete')]
    public function kbMarkUncomplete(int $id): void { $this->markUncomplete($id); }

    #[On('kb-move')]
    public function kbMove(int $id, string $status): void { $this->moveProject($id, $status); }

    // Ma'lumot/xizmat o'zgardi — sahifa qaytadan render bo'lsin
    #[On('kb-refresh')]
    public function kbRefresh(): void {}

    // "Yuborish" va "Hodim" oynalari faqat Kanban doskada bor — boshqa sahifalarda
    // o'sha yerga loyiha oynasi ochiq holda o'tkazamiz. KanbanBoard bu ikkala
    // metodni o'zining oynalarini ochadigan qilib qayta yozgan.
    #[On('kb-open-route')]
    public function kbOpenRoute(int $id, string $status): void
    {
        $this->redirect(\App\Filament\Pages\KanbanBoard::getUrl(['open' => $id]));
    }

    #[On('kb-open-assign')]
    public function kbOpenAssign(int $id): void
    {
        $this->redirect(\App\Filament\Pages\KanbanBoard::getUrl(['open' => $id]));
    }

    // "Tugallandi" bosilganda yuborilgan SMS natijasi — toast qilib chiqarish
    // (sahifaning dehydrate() metodidan chaqiriladi)
    protected function flushReadySmsNotifications(): void
    {
        foreach (Project::$pendingSmsNotifications as $note) {
            $this->dispatch('notify',
                type: $note['ok'] ? 'success' : 'error',
                message: $note['message']
            );
        }
        Project::$pendingSmsNotifications = [];
    }

    public function markComplete(int $projectId): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        $project = Project::findOrFail($projectId);
        $project->status = 'tugallangan';
        $project->saveQuietly();

        ProjectStatusLog::where('project_id', $projectId)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        ProjectStatusLog::create([
            'project_id' => $projectId,
            'status'     => 'tugallangan',
            'entered_at' => now(),
            'changed_by' => auth()->id(),
        ]);

        // Loyiha tugallanganda — barcha xizmatlar ham tugatilgan deb belgilanadi
        // (hodim tugatilgan ishlari/komissiya hisobiga tushishi uchun)
        $project->services()->whereNull('completed_at')->update(['completed_at' => now()]);

        // Egasiga "loyiha tayyor" SMS (saveQuietly bo'lgani uchun model hodisasi
        // yonmaydi — shu sababli bu yerda aniq chaqiramiz). Natija dehydrate()da chiqadi.
        $project->sendReadySms();

        $this->dispatch('notify', type: 'success', message: 'Loyiha tugallandi!');
    }

    public function markUncomplete(int $projectId): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        $project = Project::findOrFail($projectId);
        $project->status = 'tolangan';
        $project->saveQuietly();

        ProjectStatusLog::where('project_id', $projectId)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        ProjectStatusLog::create([
            'project_id' => $projectId,
            'status'     => 'tolangan',
            'entered_at' => now(),
            'changed_by' => auth()->id(),
        ]);

        // Jarayonga qaytarilganda — xizmatlar "tugatilmagan" holatga qaytadi
        $project->services()->update(['completed_at' => null]);

        $this->dispatch('notify', type: 'info', message: 'Loyiha jarayonga qaytarildi!');
    }

    // ── Payment request (admin/menejer → kassir) ──────────────────────────
    public function requestPayment(int $projectId): void
    {
        if (!auth()->user()?->canSeeAllProjects()) return;

        $project = Project::find($projectId);
        if (!$project) return;

        $project->update([
            'payment_requested_at' => now(),
            'payment_requested_by' => auth()->id(),
        ]);

        $this->dispatch('notify', type: 'success', message: "Loyiha kassirga to'lovga yuborildi!");
    }

    public function cancelPaymentRequest(int $projectId): void
    {
        $project = Project::find($projectId);
        if (!$project) return;

        $project->update([
            'payment_requested_at' => null,
            'payment_requested_by' => null,
        ]);

        $this->dispatch('notify', type: 'info', message: "To'lov so'rovi bekor qilindi");
    }

    // ── Move project between statuses ────────────────────────────────────
    public function moveProject(int $projectId, string $newStatus): void
    {
        $valid = \App\Models\ProjectStatus::pluck('key')->toArray();
        if (!in_array($newStatus, $valid)) return;

        $project = Project::find($projectId);
        if (!$project) return;

        Project::logStatusChange($project, $newStatus);
        $update = ['status' => $newStatus];
        if ($newStatus === 'yangi_didox') {
            $update['is_didox'] = true;
            if (!$project->didox_added_at) {
                $update['didox_added_at'] = now();
                $update['didox_added_by'] = auth()->id();
            }
        }
        $project->update($update);
    }
}
