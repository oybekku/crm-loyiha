<?php

namespace App\Livewire;

use App\Models\FinancialAccount;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ProjectStatus;
use App\Models\User;
use App\Services\TelegramOtpService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * To'lov oynasi (qo'shish/tahrirlash/o'chirish + xizmat narxini o'rnatish) —
 * ALOHIDA komponent (App\Livewire\ProjectEditModal namunasida). Shu sababli
 * oyna ochilganda yoki summasi/xizmati o'zgartirilganda butun Kanban doskasi
 * (yuzlab loyiha) qaytadan yuborilmaydi — faqat shu kichik komponent
 * yangilanadi. Muvaffaqiyatli saqlangач KanbanBoard'ga 'kb-payment-saved'
 * eventi yuboriladi — shundagina kartalardagi summa/foiz yangilanadi.
 */
class PaymentModal extends Component
{
    // Payment modal state
    public bool   $showPaymentModal     = false;
    public int    $paymentProjectId     = 0;
    public string $paymentAmount        = '';
    public string $paymentDate          = '';
    public string $paymentMethod        = 'naqd';
    public ?int   $paymentAccountId     = null;
    public string $paymentNote          = '';
    public bool   $paymentMoveToEskiz   = true;
    public bool   $paymentFromQueue     = false;
    public ?int   $paymentToposyomkaUserId  = null;
    public ?int   $paymentEskizUserId       = null;
    public array  $paymentSelectedServices  = []; // tanlangan xizmatlar
    public array  $paymentAdjustments       = []; // service_id => +/- summa (faqat admin)
    public bool   $paymentAmountConfirm    = false;

    // To'lov oynasidagi chegirma bo'limi (tanlangan xizmat(lar) narxiga qo'llanadi)
    public string $payDiscountCategory  = ''; // '', nogiron, pensioner, ijtimoiy, boshqa
    public string $payDiscountCustomPct = '';

    // Edit payment modal state
    public bool   $showEditPaymentModal = false;
    public int    $editPaymentId        = 0;
    public string $editPaymentAmount    = '';
    public string $editPaymentDate      = '';
    public string $editPaymentMethod    = 'naqd';
    public ?int   $editPaymentAccountId = null;
    public array  $editPaymentServices  = []; // tanlangan xizmatlar
    public string $editPaymentPin       = '';
    public bool   $editPaymentPinError  = false;

    // Payment logs (tarix) modal state
    public bool $showPaymentLogsModal = false;
    public int  $paymentLogsProjectId = 0;

    // To'lovni o'chirish (PIN kod bilan)
    public bool   $showDeletePaymentModal = false;
    public int    $deletePaymentId        = 0;
    public string $deletePaymentPin       = '';
    public bool   $deletePaymentPinError  = false;

    // Xizmat narxini tahrirlash (Joriy narx — PIN kod bilan)
    public bool   $showServicePriceModal = false;
    public int    $servicePriceId        = 0;
    public string $servicePriceValue     = '';
    public string $servicePricePin       = '';
    public bool   $servicePricePinError  = false;

    // ── Payment modal ─────────────────────────────────────────────────────
    // Eslatma: 'kb-open-payment' eventi 'id' kaliti bilan yuboriladi
    // (ProjectEditModal va kanban-board.blade.php'dagi tugmalar), shu
    // sababli to'g'ridan-to'g'ri openPaymentModal($projectId)ga bog'lab
    // bo'lmaydi — Livewire event maydonlarini metod parametr NOMI bo'yicha
    // moslashtiradi. Shu uchun nomi mos keluvchi tor "ko'prik" metod kerak.
    #[On('kb-open-payment')]
    public function kbOpenPayment(int $id, bool $fromQueue = false): void
    {
        $this->openPaymentModal($id, $fromQueue);
    }

    public function openPaymentModal(int $projectId, bool $fromQueue = false): void
    {
        $this->paymentProjectId = $projectId;
        $this->paymentAmount    = '';
        $this->paymentDate      = now()->format('Y-m-d');
        $this->paymentMethod    = 'naqd';
        $this->paymentAccountId = $this->defaultAccountIdFor('naqd');
        $this->paymentNote      = '';
        $this->paymentFromQueue = $fromQueue;

        $project = Project::with('services')->find($projectId);
        $this->paymentToposyomkaUserId = $project?->services->where('service_name', 'toposyomka')->first()?->assigned_user_id;
        $this->paymentEskizUserId      = $project?->services->where('service_name', 'eskiz_loyiha')->first()?->assigned_user_id;
        $this->paymentSelectedServices = []; // reset
        $this->paymentAdjustments      = []; // reset
        $this->payDiscountCategory     = '';
        $this->payDiscountCustomPct    = '';

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal        = false;
        $this->paymentProjectId        = 0;
        $this->paymentToposyomkaUserId = null;
        $this->paymentEskizUserId      = null;
        $this->paymentAmountConfirm    = false;
        $this->payDiscountCategory     = '';
        $this->payDiscountCustomPct    = '';
    }

    // To'lov oynasidagi chegirma — tanlangan xizmat(lar)ga (yoki hech narsa
    // belgilanmagan bo'lsa barcha xizmatlarga) foiz chegirma qo'llaydi.
    // ProjectService modelining "saving" hook'i final_price'ni avtomatik
    // qayta hisoblaydi, shu sababli shu yerda faqat discount_type/value
    // yozib saqlash kifoya. Oyna yopilmaydi — foydalanuvchi davom etadi.
    public function applyPaymentDiscount(): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        $rates = ['nogiron' => 15, 'pensioner' => 10, 'ijtimoiy' => 10];
        $pct = match ($this->payDiscountCategory) {
            'nogiron', 'pensioner', 'ijtimoiy' => $rates[$this->payDiscountCategory],
            'boshqa' => (float) $this->payDiscountCustomPct,
            default  => null,
        };
        if ($pct === null || $pct <= 0) return;

        $project = Project::with('services')->find($this->paymentProjectId);
        if (!$project) return;

        $targets = empty($this->paymentSelectedServices)
            ? $project->services
            : $project->services->whereIn('service_name', $this->paymentSelectedServices);

        foreach ($targets as $svc) {
            $svc->discount_type  = 'percent';
            $svc->discount_value = $pct;
            $svc->save();
        }

        $this->payDiscountCategory  = '';
        $this->payDiscountCustomPct = '';
        $this->dispatch('notify', type: 'success', message: "Chegirma qo'llandi");
        $this->dispatch('kb-payment-saved');
    }

    // To'lov usuli o'zgarsa — avval tanlangan hisob boshqa turga tegishli bo'lib
    // qolmasligi uchun tozalaymiz (masalan "Karta"dan "Naqd"ga o'tilsa).
    public function updatedPaymentMethod(): void
    {
        $this->paymentAccountId = $this->defaultAccountIdFor($this->paymentMethod);
    }

    // To'lov usuliga mos yagona hisob bo'lsa — avtomatik shuni tanlaydi
    // (bir nechta hisob bo'lsa, admin o'zi tanlaydi — null qaytaradi).
    private function defaultAccountIdFor(string $type): ?int
    {
        $accounts = FinancialAccount::where('type', $type)->pluck('id');
        return $accounts->count() === 1 ? $accounts->first() : null;
    }

    /**
     * Bitta to'lov summasini loyihaning xizmatlariga ketma-ket (waterfall)
     * taqsimlaydi: $selectedServices ichidagi xizmatlar birinchi navbatda,
     * priority tartibida (Toposyomka -> Eskiz loyiha -> Ariza) to'liq
     * qoplanguncha; qolgan summa bo'lsa, loyihaning QOLGAN xizmatlariga
     * (belgilanmagan bo'lsa ham) shu tartibda o'tadi. Shu bilan xodim
     * yolg'iz bitta katakchani belgilab qo'ysa ham, summa o'sha xizmat
     * narxidan oshib ketganda ortig'i "yo'qolib" (bitta xizmatga ortiqcha
     * yozilib) qolmaydi.
     *
     * $excludePaymentId — tahrirlash paytida shu to'lovning ESKI hissasini
     * "allaqachon to'langan" hisobidan chiqarib turadi (aks holda to'lov
     * o'zi-o'ziga to'sqinlik qilib, taqsimot noto'g'ri chiqadi).
     *
     * @return array<string, float> service_name => summa
     */
    private static function computeWaterfallSplit(Project $project, array $selectedServices, float $amount, ?int $excludePaymentId = null): array
    {
        if ($amount <= 0 || empty($selectedServices)) {
            return [];
        }

        $project->loadMissing(['services', 'payments']);
        if ($excludePaymentId) {
            $project->setRelation(
                'payments',
                $project->payments->reject(fn ($p) => $p->id === $excludePaymentId)->values()
            );
        }

        $priorityOrder    = array_keys(Project::serviceOptions());
        $selectedOrdered  = array_values(array_filter($priorityOrder, fn ($sn) => in_array($sn, $selectedServices, true)));
        $restOrdered      = array_values(array_filter($priorityOrder, fn ($sn) => !in_array($sn, $selectedServices, true)));
        $orderedServices  = array_merge($selectedOrdered, $restOrdered);

        $servicesByName = $project->services->keyBy('service_name');
        $remaining       = $amount;
        $split           = [];

        foreach ($orderedServices as $sn) {
            if ($remaining <= 0) break;
            $svc = $servicesByName->get($sn);
            if (!$svc) continue;
            $already = \App\Services\EmployeePayableService::paidAmountForService($svc, $project);
            $left    = max(0, (float) $svc->final_price - $already);
            if ($left <= 0) continue;
            $take = min($remaining, $left);
            $split[$sn] = round($take, 2);
            $remaining -= $take;
        }

        // Loyihaning barcha xizmatlari to'liq to'langan bo'lsa ham yana pul
        // kirsa (masalan ortiqcha to'lov) — pul yo'qolib ketmasin, oxirgi
        // tanlangan (yoki oxirgi) xizmatga qo'shib qo'yamiz.
        if ($remaining > 0) {
            $fallback = !empty($selectedOrdered) ? end($selectedOrdered) : (end($priorityOrder) ?: null);
            if ($fallback) {
                $split[$fallback] = round(($split[$fallback] ?? 0) + $remaining, 2);
            }
        }

        return $split;
    }

    // $keepOpen=true bo'lsa — oyna yopilmaydi, faqat summa/izoh tozalanadi
    // (admin ketma-ket bir nechta to'lov kiritishi yoki boshqa o'zgarish
    // qilishi uchun). Oddiy "Saqlash" tugmasi $keepOpen=false bilan chaqiradi.
    public function savePayment(bool $keepOpen = false): void
    {
        $project = Project::find($this->paymentProjectId);
        if (!$project) return;

        $hasAmount    = filled($this->paymentAmount) && (float)$this->paymentAmount > 0;
        $newPaymentId = null;

        // Summa kiritilmagan — tasdiq so'rash
        if (!$hasAmount && !$this->paymentAmountConfirm) {
            $this->paymentAmountConfirm = true;
            return;
        }

        // Summali yo'l
        if ($hasAmount) {
            // Tanlangan to'lov usuliga mos hisob(lar) mavjud bo'lsa — qaysi
            // hisobga tushgani albatta tanlanishi shart, aks holda to'lov
            // "muallaq" (account_id=null) saqlanib, Buxgalteriyada butunlay
            // ko'rinmay qoladi (pul yo'qolib qolganday tuyuladi).
            $accountRequired = FinancialAccount::where('type', $this->paymentMethod)->exists();

            $this->validate([
                'paymentAmount'    => 'required|numeric|min:1',
                'paymentDate'      => 'required|date',
                'paymentMethod'    => 'required|in:naqd,bank,karta',
                'paymentAccountId' => $accountRequired ? 'required|exists:financial_accounts,id' : 'nullable',
            ], [
                'paymentAmount.min'       => 'Summa 0 dan katta bo\'lishi kerak',
                'paymentDate.required'    => 'Sana kiritilishi shart',
                'paymentAccountId.required' => "Qaysi hisobga tushganini tanlang — aks holda pul Buxgalteriyada ko'rinmaydi",
            ]);

            // Kamida bitta xizmat tanlanishi shart — aks holda summa hech
            // qaysi xizmatga (demak hech qaysi hodimga) bog'lanmay qoladi va
            // komissiya hisobida "yo'qolib" ketadi.
            if (empty($this->paymentSelectedServices)) {
                $this->addError('paymentSelectedServices',
                    "Qaysi xizmat uchun to'lov ekanini belgilang — aks holda summa hech qaysi hodimga bog'lanmaydi");
                return;
            }

            // Ortiqcha to'lovni bloklash — to'lov ish summasidan oshmasligi kerak
            $ishSummasi = (float) $project->services()->sum('final_price');
            $tolangan   = (float) $project->payments()->sum('amount');
            $qoldiq     = $ishSummasi - $tolangan;
            if ($ishSummasi > 0 && ((float) $this->paymentAmount) > $qoldiq) {
                $this->addError('paymentAmount',
                    "Summa oshib ketdi! Ish summasi: " . number_format($ishSummasi, 0, '.', ' ')
                    . " so'm, qolgan: " . number_format(max(0, $qoldiq), 0, '.', ' ') . " so'm");
                return;
            }

            // Bir nechta xizmat tanlangan bo'lsa — summani ketma-ket (waterfall)
            // taqsimlaymiz: birinchi xizmat (Toposyomka -> Eskiz loyiha -> Ariza
            // tartibida) to'liq qoplangунча, keyin qolgan summa navbatdagiga
            // o'tadi. Eski (narx nisbati bo'yicha proporsional) usul o'rniga.
            // Summani ketma-ket (waterfall) taqsimlaymiz: birinchi xizmat
            // (Toposyomka -> Eskiz loyiha -> Ariza tartibida) to'liq
            // qoplanguncha, keyin qolgan summa navbatdagiga o'tadi — hattoki
            // faqat bitta xizmat belgilangan bo'lsa ham, agar summa o'sha
            // xizmat narxidan oshib ketsa, ortig'i loyihaning boshqa (hali
            // to'liq to'lanmagan) xizmatiga avtomatik o'tadi. Shu bilan
            // xodim yolg'iz bitta katakchani belgilab qo'ysa ham, pul
            // "yo'qolib" (bitta xizmatga ortiqcha yozilib) qolmaydi.
            $serviceSplit = self::computeWaterfallSplit(
                $project, $this->paymentSelectedServices, (float) $this->paymentAmount
            );
            $touchedServices = array_keys(array_filter($serviceSplit, fn ($v) => $v > 0));
            $finalServices   = !empty($touchedServices)
                ? array_values(array_unique(array_merge($this->paymentSelectedServices, $touchedServices)))
                : $this->paymentSelectedServices;

            $payment = Payment::create([
                'project_id'    => $project->id,
                'amount'        => (float) $this->paymentAmount,
                'payment_date'  => $this->paymentDate,
                'method'        => $this->paymentMethod,
                'account_id'    => $this->paymentAccountId ?: null,
                'note'          => trim($this->paymentNote) ?: null,
                'created_by'    => auth()->id(),
                'services'      => !empty($finalServices) ? $finalServices : null,
                'service_split' => !empty($serviceSplit) ? $serviceSplit : null,
            ]);

            PaymentLog::create([
                'project_id' => $project->id,
                'payment_id' => $payment->id,
                'user_id'    => auth()->id(),
                'action'     => 'created',
                'amount'     => $payment->amount,
                'description'=> number_format($payment->amount, 0, '.', ' ') . " so'm qo'shildi",
            ]);

            $newPaymentId = $payment->id;

            $project->updateTotals();
        }

        // Xizmat mas'ullarini saqlash
        $this->applyServiceAssignments($project);

        if ($hasAmount) {
            $validKeys = ProjectStatus::allOrdered()->pluck('key')->toArray();
            if ($this->paymentFromQueue && in_array('tolangan', $validKeys)) {
                Project::logStatusChange($project, 'tolangan');
                $project->update([
                    'status'               => 'tolangan',
                    'payment_requested_at' => null,
                    'payment_requested_by' => null,
                ]);
            } elseif ($project->status === 'yangi'
                && in_array('yangi_loyihalar', $validKeys)
            ) {
                Project::logStatusChange($project, 'yangi_loyihalar');
                $project->update(['status' => 'yangi_loyihalar']);
            } elseif ($this->paymentMoveToEskiz
                && $project->status === 'tolov_jarayonida'
                && in_array('toposyomka', $validKeys)
            ) {
                Project::logStatusChange($project, 'toposyomka');
                $project->update(['status' => 'toposyomka']);
            }
        }

        $this->dispatch('kb-payment-saved');

        if ($keepOpen) {
            $this->paymentAmount       = '';
            $this->paymentNote         = '';
            $this->paymentAmountConfirm = false;
            $this->dispatch('notify', type: 'success', message: $hasAmount ? "To'lov saqlandi!" : 'Hodimlar biriktirildi!');
            if (!empty($newPaymentId)) $this->dispatch('print-receipt', paymentId: $newPaymentId);
            return;
        }

        $this->closePaymentModal();
        $this->dispatch('notify', type: 'success', message: $hasAmount ? "To'lov saqlandi!" : 'Hodimlar biriktirildi!');
        if (!empty($newPaymentId)) $this->dispatch('print-receipt', paymentId: $newPaymentId);
    }

    public function cancelPaymentAmountConfirm(): void
    {
        $this->paymentAmountConfirm = false;
    }

    private function applyServiceAssignments(Project $project): void
    {
        $assignIds = array_values(array_filter([
            $this->paymentToposyomkaUserId,
            $this->paymentEskizUserId,
        ]));
        if ($assignIds) {
            $project->assignedUsers()->syncWithoutDetaching($assignIds);
        }
        if ($this->paymentToposyomkaUserId) {
            $project->services()->where('service_name', 'toposyomka')
                ->update(['assigned_user_id' => $this->paymentToposyomkaUserId]);
        }
        if ($this->paymentEskizUserId) {
            $project->services()->where('service_name', 'eskiz_loyiha')
                ->update(['assigned_user_id' => $this->paymentEskizUserId]);
        }
        if ($assignIds) {
            $names = User::whereIn('id', $assignIds)->pluck('name')->join(', ');
            PaymentLog::create([
                'project_id'  => $project->id,
                'user_id'     => auth()->id(),
                'action'      => 'employee_assigned',
                'description' => "Hodim biriktirildi: {$names}",
            ]);
        }
    }

    // ── Edit payment ──────────────────────────────────────────────────────
    public function openEditPayment(int $paymentId): void
    {
        $payment = Payment::find($paymentId);
        if (!$payment) return;
        $this->editPaymentId        = $paymentId;
        $this->editPaymentAmount    = (string)(float)$payment->amount;
        $this->editPaymentDate      = $payment->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->editPaymentMethod    = $payment->method ?: 'naqd';
        $this->editPaymentAccountId = $payment->account_id;
        $this->editPaymentServices  = $payment->services ?? [];
        $this->editPaymentPin       = '';
        $this->editPaymentPinError  = false;
        $this->showEditPaymentModal = true;

        TelegramOtpService::sendOtp(
            auth()->user(), 'edit_payment',
            "Summani tahrirlash: " . number_format((float) $payment->amount, 0, '.', ' ') . " so'm (" . ($payment->project?->owner_name ?? '—') . ")"
        );
    }

    public function closeEditPayment(): void
    {
        $this->showEditPaymentModal = false;
        $this->editPaymentId        = 0;
        $this->editPaymentAmount    = '';
        $this->editPaymentDate      = '';
        $this->editPaymentMethod    = 'naqd';
        $this->editPaymentAccountId = null;
        $this->editPaymentServices  = [];
        $this->editPaymentPin       = '';
        $this->editPaymentPinError  = false;
    }

    // To'lov usuli o'zgarsa — eski hisob boshqa turga tegishli bo'lib
    // qolmasligi uchun tozalaymiz (create oynasidagi bilan bir xil mantiq).
    public function updatedEditPaymentMethod(): void
    {
        $this->editPaymentAccountId = $this->defaultAccountIdFor($this->editPaymentMethod);
    }

    public function saveEditPayment(): void
    {
        if (!TelegramOtpService::verifyOtp(auth()->user(), $this->editPaymentPin, 'edit_payment')) {
            $this->editPaymentPinError = true;
            return;
        }

        $accountRequired = FinancialAccount::where('type', $this->editPaymentMethod)->exists();

        $this->validate(
            [
                'editPaymentAmount'    => 'required|numeric|min:1',
                'editPaymentDate'      => 'required|date',
                'editPaymentMethod'    => 'required|in:naqd,bank,karta',
                'editPaymentAccountId' => $accountRequired ? 'required|exists:financial_accounts,id' : 'nullable',
            ],
            ['editPaymentAmount.required'    => 'Summa kiritilishi shart',
             'editPaymentAmount.min'         => 'Summa 0 dan katta bo\'lishi kerak',
             'editPaymentDate.required'      => 'Sana kiritilishi shart',
             'editPaymentAccountId.required' => "Qaysi hisobga tushganini tanlang — aks holda pul Buxgalteriyada ko'rinmaydi"]
        );

        $payment = Payment::find($this->editPaymentId);
        if (!$payment) return;

        $oldAmount = (float) $payment->amount;
        $newAmount = (float) $this->editPaymentAmount;

        $accountChanged = $payment->method !== $this->editPaymentMethod
            || $payment->account_id !== $this->editPaymentAccountId;

        $dateChanged = $payment->payment_date?->format('Y-m-d') !== $this->editPaymentDate;

        $newServices     = !empty($this->editPaymentServices) ? array_values($this->editPaymentServices) : null;
        $servicesChanged = ($payment->services ?? null) !== $newServices;

        if ($oldAmount === $newAmount && !$accountChanged && !$dateChanged && !$servicesChanged) {
            $this->closeEditPayment();
            return;
        }

        // Summa yoki xizmatlar o'zgargan bo'lsa — taqsimotni waterfall
        // usulida QAYTA hisoblaymiz (shu to'lovning eski hissasi "allaqachon
        // to'langan" hisobiga qo'shilib, o'zi-o'ziga to'sqinlik qilmasligi
        // uchun computeWaterfallSplit() shu to'lovni loyihaning to'lovlar
        // ro'yxatidan vaqtincha chiqarib turadi).
        $newSplit = $payment->service_split;
        if (($servicesChanged || $oldAmount !== $newAmount) && !empty($newServices)) {
            $editProject = Project::with(['services', 'payments'])->find($payment->project_id);
            $newSplit = $editProject
                ? self::computeWaterfallSplit($editProject, $newServices, $newAmount, $payment->id)
                : null;
        }

        $payment->update([
            'amount'        => $newAmount,
            'payment_date'  => $this->editPaymentDate,
            'method'        => $this->editPaymentMethod,
            'account_id'    => $this->editPaymentAccountId,
            'services'      => $newServices,
            'service_split' => !empty($newSplit) ? $newSplit : null,
        ]);

        PaymentLog::create([
            'project_id'  => $payment->project_id,
            'payment_id'  => $payment->id,
            'user_id'     => auth()->id(),
            'action'      => 'edited',
            'amount'      => $newAmount,
            'old_amount'  => $oldAmount,
            'description' => number_format($oldAmount, 0, '.', ' ') . " → " . number_format($newAmount, 0, '.', ' ') . " so'm",
        ]);

        $this->closeEditPayment();
        $this->dispatch('notify', type: 'success', message: "To'lov yangilandi!");
        $this->dispatch('kb-payment-saved');
    }

    // ── Delete payment (Telegram tasdiqlash kodi bilan) ─────────────────────
    public function openDeletePayment(int $paymentId): void
    {
        $this->deletePaymentId       = $paymentId;
        $this->deletePaymentPin      = '';
        $this->deletePaymentPinError = false;
        $this->showDeletePaymentModal = true;
        $pmt = Payment::find($paymentId);
        TelegramOtpService::sendOtp(
            auth()->user(), 'delete_payment',
            "To'lovni o'chirish: " . ($pmt ? number_format((float)$pmt->amount, 0, '.', ' ') . " so'm (" . ($pmt->project?->owner_name ?? '—') . ")" : "#{$paymentId}")
        );
    }

    public function closeDeletePayment(): void
    {
        $this->showDeletePaymentModal = false;
        $this->deletePaymentId        = 0;
        $this->deletePaymentPin       = '';
        $this->deletePaymentPinError  = false;
    }

    public function confirmDeletePayment(): void
    {
        if (!TelegramOtpService::verifyOtp(auth()->user(), $this->deletePaymentPin, 'delete_payment')) {
            $this->deletePaymentPinError = true;
            return;
        }

        $payment = Payment::find($this->deletePaymentId);
        if (!$payment) {
            $this->closeDeletePayment();
            return;
        }

        $amount    = (float) $payment->amount;
        $projectId = $payment->project_id;

        PaymentLog::create([
            'project_id'  => $projectId,
            'payment_id'  => $payment->id,
            'user_id'     => auth()->id(),
            'action'      => 'deleted',
            'amount'      => 0,
            'old_amount'  => $amount,
            'description' => number_format($amount, 0, '.', ' ') . " so'm to'lov o'chirildi",
        ]);

        $payment->delete();

        $project = Project::find($projectId);
        $project?->updateTotals();

        $this->closeDeletePayment();
        $this->dispatch('notify', type: 'success', message: "To'lov o'chirildi!");
        $this->dispatch('kb-payment-saved');
    }

    // ── To'lovlar tarixi (kim, qachon, qancha o'zgargani) ───────────────────
    public function openPaymentLogs(int $projectId): void
    {
        $this->paymentLogsProjectId = $projectId;
        $this->showPaymentLogsModal = true;
    }

    public function closePaymentLogsModal(): void
    {
        $this->showPaymentLogsModal = false;
        $this->paymentLogsProjectId = 0;
    }

    // ── Xizmat narxini tahrirlash (Joriy narx — Telegram tasdiqlash kodi bilan) ──
    public function openServicePrice(int $serviceId): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        $svc = ProjectService::find($serviceId);
        if (!$svc) return;
        $this->servicePriceId       = $serviceId;
        $this->servicePriceValue    = (string) (float) $svc->final_price;
        $this->servicePricePin      = '';
        $this->servicePricePinError = false;
        $this->showServicePriceModal = true;
        $svcLabel = Project::serviceOptions()[$svc->service_name] ?? $svc->service_name;
        TelegramOtpService::sendOtp(
            auth()->user(), 'service_price',
            "Narx o'zgartirish: {$svcLabel} ({$svc->project?->owner_name})"
        );
    }

    public function closeServicePrice(): void
    {
        $this->showServicePriceModal = false;
        $this->servicePriceId        = 0;
        $this->servicePriceValue     = '';
        $this->servicePricePin       = '';
        $this->servicePricePinError  = false;
    }

    public function saveServicePrice(): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        if (!TelegramOtpService::verifyOtp(auth()->user(), $this->servicePricePin, 'service_price')) {
            $this->servicePricePinError = true;
            return;
        }

        $svc = ProjectService::find($this->servicePriceId);
        if (!$svc) {
            $this->closeServicePrice();
            return;
        }

        $newPrice = max(0, (float) str_replace([' ', ','], '', $this->servicePriceValue));
        $svc->update(['final_price' => $newPrice, 'price' => $newPrice]);

        Project::find($svc->project_id)?->updateTotals();

        $this->closeServicePrice();
        $this->dispatch('notify', type: 'success', message: 'Narx yangilandi!');
        $this->dispatch('kb-payment-saved');
    }

    public function render()
    {
        $paymentAccounts = ($this->showPaymentModal || $this->showEditPaymentModal)
            ? FinancialAccount::orderBy('sort_order')->orderBy('name')->get()
            : collect();

        $users = ($this->showPaymentModal)
            ? User::orderBy('name')->get()
            : collect();

        return view('livewire.payment-modal', compact('paymentAccounts', 'users'));
    }
}
