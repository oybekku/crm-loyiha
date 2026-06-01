<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectService;
use App\Models\ServicePriceTier;
use App\Models\User;
use App\Traits\HasMenuPermission;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class KanbanBoard extends Page
{
    use WithFileUploads, HasMenuPermission;

    protected static string $view = 'filament.pages.kanban-board';
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Loyihalar';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = '';

    public string $filterStatus = '';

    // Xizmat hodim tayinlash modal
    public bool  $showServiceAssignModal = false;
    public int   $serviceAssignProjectId = 0;
    public array $serviceAssignData      = []; // [service_id => [user_id, days]]
    public string $search      = '';  // Kanban qidiruv

    public bool  $showModal = false;
    public int   $step      = 1;

    public string $owner_name        = '';
    public string $proj_title        = '';
    public string $address           = '';
    public string $latitude          = '';
    public string $longitude         = '';
    public array  $phones            = ['+998'];
    public string $description       = '';
    public string $category          = 'turar';
    public array  $assigned_user_ids = [];
    public string $deadline_days     = '';
    public bool   $showDeadlineConfirm   = false;
    public array  $uploadedFiles         = [];

    // Route to department modal
    public bool   $showRouteModal      = false;
    public int    $routeProjectId      = 0;
    public string $routeNewStatus      = '';
    public int    $routeAllocDays      = 3;
    public ?int   $routeAssignedUserId = null;

    public array $services     = [];
    public array $activeSubTab = [];

    // Payment modal state
    public bool   $showPaymentModal     = false;
    public int    $paymentProjectId     = 0;
    public string $paymentAmount        = '';
    public string $paymentDate          = '';
    public string $paymentMethod        = 'naqd';
    public string $paymentNote          = '';
    public bool   $paymentMoveToEskiz   = true;
    public bool   $paymentFromQueue     = false;
    public ?int   $paymentToposyomkaUserId = null;
    public ?int   $paymentEskizUserId      = null;
    public bool   $paymentAmountConfirm    = false;

    // Edit payment modal state
    public bool   $showEditPaymentModal = false;
    public int    $editPaymentId        = 0;
    public string $editPaymentAmount    = '';

    // Area (kv.m) modal state
    public bool   $showAreaModal  = false;
    public string $areaServiceKey = '';
    public string $areaValue      = '';

    // Discount modal state
    public bool   $showDiscountModal  = false;
    public string $discountServiceKey = '';
    public string $discountType       = 'percent'; // percent | fixed
    public string $discountValue      = '';

    // ── Computed: priceTiers (NOT stored in Livewire state → fast) ───────
    private const SUB_SERVICE_ORDER = [
        'toposyomka' => ['toposyomka', 'qoziq', 'qr_kod', 'akt'],
    ];

    #[Computed]
    public function priceTiers(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('price_tiers_grouped', 600, function () {
            $rows   = ServicePriceTier::orderBy('sort_order')->get();
            $result = [];
            foreach ($rows as $row) {
                $result[$row->service_key][$row->sub_service][] = [
                    'id'                => $row->id,
                    'label'             => $row->label,
                    'price'             => (float) $row->price,
                    'sub_service_label' => $row->sub_service_label,
                ];
            }
            foreach (self::SUB_SERVICE_ORDER as $serviceKey => $order) {
                if (!isset($result[$serviceKey])) continue;
                $sorted = [];
                foreach ($order as $sub) {
                    if (isset($result[$serviceKey][$sub])) {
                        $sorted[$sub] = $result[$serviceKey][$sub];
                    }
                }
                foreach ($result[$serviceKey] as $sub => $tiers) {
                    if (!isset($sorted[$sub])) $sorted[$sub] = $tiers;
                }
                $result[$serviceKey] = $sorted;
            }
            return $result;
        });
    }

    // ── Rules ─────────────────────────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'owner_name'       => 'required|min:2',
            'address'          => 'required|min:3',
            'phones.0'         => ['required', 'regex:/^\+998\d{9}$/'],
            'uploadedFiles.*'  => 'file|max:20480|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx',
        ];
    }

    protected function messages(): array
    {
        return [
            'owner_name.required' => 'Egasining ismi kiritilishi shart',
            'owner_name.min'      => "Ismi kamida 2 ta harf bo'lishi kerak",
            'address.required'    => 'Manzil kiritilishi shart',
            'address.min'         => "Manzil kamida 3 ta harf bo'lishi kerak",
            'phones.0.required' => 'Telefon raqam kiritilishi shart',
            'phones.0.regex'    => 'Noto\'g\'ri format. To\'g\'ri: +998901234567 (12 ta raqam)',
        ];
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->filterStatus = request()->get('status', '');
        $this->initServices();
    }

    private function initServices(): void
    {
        $tiers = $this->priceTiers;
        $this->services     = [];
        $this->activeSubTab = [];
        foreach (Project::serviceOptions() as $key => $label) {
            $hasTiers = isset($tiers[$key]);
            $firstSub = $hasTiers ? array_key_first($tiers[$key]) : null;
            $this->services[$key] = [
                'label'            => $label,
                'selected'         => false,
                'price'            => '',
                'has_tiers'        => $hasTiers,
                'selected_tiers'   => [],
                'area_m2'          => '',
                'discount_type'    => 'none',
                'discount_value'   => '',
                'discount_amount'  => '0',
                'final_price'      => '',
                'assigned_user_id' => null,
            ];
            if ($hasTiers && $firstSub) {
                $this->activeSubTab[$key] = $firstSub;
            }
        }

        // Ariza xizmatini avtomatik ravishda birinchi adminga biriktirish
        $adminUser = User::where('role', 'admin')->orderBy('id')->first();
        if (isset($this->services['ariza'])) {
            $this->services['ariza']['assigned_user_id'] = $adminUser?->id;
        }
    }

    // ── Modal ─────────────────────────────────────────────────────────────
    public function openModal(): void
    {
        $user = auth()->user();
        if ($user?->isHisobchi() || $user?->isBajaruvchi()) return;
        $this->reset(['owner_name', 'proj_title', 'address', 'latitude', 'longitude', 'description', 'assigned_user_ids', 'deadline_days', 'showDeadlineConfirm']);
        $this->phones             = ['+998'];
        $this->category           = 'turar';
        $this->uploadedFiles      = [];
        $this->step               = 1;
        $this->showDiscountModal  = false;
        $this->discountServiceKey = '';
        $this->initServices();
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    public function openServiceAssignModal(int $projectId): void
    {
        $this->serviceAssignProjectId = $projectId;
        $project = Project::with('services.assignedUser')->find($projectId);
        $this->serviceAssignData = [];
        foreach ($project->services as $svc) {
            $this->serviceAssignData[$svc->id] = [
                'user_id' => $svc->assigned_user_id,
                'days'    => $svc->deadline_days ?? 7,
            ];
        }
        $this->showServiceAssignModal = true;
    }

    public function saveServiceAssign(): void
    {
        $now = now();
        foreach ($this->serviceAssignData as $svcId => $data) {
            $svc = \App\Models\ProjectService::find($svcId);
            if (!$svc) continue;

            $userId = $data['user_id'] ?: null;
            $days   = max(1, (int)($data['days'] ?? 7));
            $startedAt = $svc->work_started_at;
            if ($userId && !$svc->assigned_user_id) {
                $startedAt = $now;
            }
            if (!$userId) {
                $startedAt = null;
            }

            \Illuminate\Support\Facades\DB::table('project_services')
                ->where('id', $svcId)
                ->update([
                    'assigned_user_id' => $userId,
                    'deadline_days'    => $days,
                    'work_started_at'  => $startedAt,
                ]);

            if ($userId) {
                Project::find($this->serviceAssignProjectId)
                    ?->assignedUsers()->syncWithoutDetaching([$userId]);
            }
        }

        $this->showServiceAssignModal = false;
        $this->dispatch('notify', type: 'success', message: 'Hodimlar biriktirildi!');
    }

    public function closeServiceAssignModal(): void
    {
        $this->showServiceAssignModal = false;
    }


    // ── Phone ─────────────────────────────────────────────────────────────
    public function addPhone(): void
    {
        if (count($this->phones) < 5) $this->phones[] = '+998';
    }

    public function removePhone(int $index): void
    {
        array_splice($this->phones, $index, 1);
        $this->phones = array_values($this->phones);
    }

    // ── Steps ─────────────────────────────────────────────────────────────
    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate();
            $phone = trim($this->phones[0] ?? '');
            if (!preg_match('/^\+998\d{9}$/', $phone)) {
                $this->addError('phones.0', "Noto'g'ri format. To'g'ri: +998901234567 (12 ta raqam)");
                return;
            }
            if (empty($this->deadline_days)) {
                $this->showDeadlineConfirm = true;
                return;
            }
        }
        $this->showDeadlineConfirm = false;
        $this->step++;
    }

    public function nextStepWithoutDeadline(): void
    {
        $this->showDeadlineConfirm = false;
        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function setAddress(string $addr): void
    {
        $this->address = $addr;
    }

    // ── Tiers ─────────────────────────────────────────────────────────────
    public function setSubTab(string $serviceKey, string $subService): void
    {
        $this->activeSubTab[$serviceKey] = $subService;
    }

    private function recalcPrice(string $serviceKey): void
    {
        $tiers = $this->priceTiers;
        $rateTotal = 0;
        foreach ($this->services[$serviceKey]['selected_tiers'] as $sub => $id) {
            foreach ($tiers[$serviceKey][$sub] ?? [] as $tier) {
                if ($tier['id'] === $id) { $rateTotal += $tier['price']; break; }
            }
        }
        $area  = (float)($this->services[$serviceKey]['area_m2'] ?? 0);
        $total = ($area > 0) ? (int) round($rateTotal * $area) : (int) $rateTotal;

        $this->services[$serviceKey]['price']           = (string) $total;
        $this->services[$serviceKey]['discount_type']   = 'none';
        $this->services[$serviceKey]['discount_value']  = '';
        $this->services[$serviceKey]['discount_amount'] = '0';
        $this->services[$serviceKey]['final_price']     = (string) $total;
    }

    public function selectTier(string $serviceKey, string $subService, int $tierId): void
    {
        $this->services[$serviceKey]['selected']                     = true;
        $this->services[$serviceKey]['selected_tiers'][$subService] = $tierId;
        $this->recalcPrice($serviceKey);
    }

    public function deselectTier(string $serviceKey, string $subService): void
    {
        unset($this->services[$serviceKey]['selected_tiers'][$subService]);
        if (empty($this->services[$serviceKey]['selected_tiers'])) {
            $this->services[$serviceKey]['selected']        = false;
            $this->services[$serviceKey]['price']           = '';
            $this->services[$serviceKey]['final_price']     = '';
            $this->services[$serviceKey]['area_m2']         = '';
            $this->services[$serviceKey]['discount_type']   = 'none';
            $this->services[$serviceKey]['discount_value']  = '';
            $this->services[$serviceKey]['discount_amount'] = '0';
        } else {
            $this->recalcPrice($serviceKey);
        }
    }

    // ── Discount ─────────────────────────────────────────────────────────
    public function openDiscountModal(string $key): void
    {
        $this->discountServiceKey = $key;
        $existing = $this->services[$key]['discount_type'] ?? 'none';
        $this->discountType  = ($existing === 'none') ? 'percent' : $existing;
        $this->discountValue = $this->services[$key]['discount_value'] ?? '';
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal  = false;
        $this->discountServiceKey = '';
        $this->discountValue      = '';
    }

    // ── Area (kv.m) modal ─────────────────────────────────────────────────
    public function openAreaModal(string $key): void
    {
        if (!isset($this->services[$key])) return;
        $this->areaServiceKey = $key;
        $this->areaValue      = $this->services[$key]['area_m2'] ?? '';
        $this->showAreaModal  = true;
    }

    public function saveArea(): void
    {
        $key = $this->areaServiceKey;
        if (!isset($this->services[$key])) { $this->showAreaModal = false; return; }
        $area = max(0, (float) str_replace(',', '.', $this->areaValue));
        $this->services[$key]['area_m2'] = $area > 0 ? (string)$area : '';
        $this->recalcPrice($key);
        $this->showAreaModal = false;
    }

    public function closeAreaModal(): void
    {
        $this->showAreaModal  = false;
        $this->areaServiceKey = '';
        $this->areaValue      = '';
    }

    public function applyDiscount(): void
    {
        $key   = $this->discountServiceKey;
        $price = (float) ($this->services[$key]['price'] ?? 0);
        $val   = (float) $this->discountValue;

        $amount = ($this->discountType === 'percent')
            ? round($price * $val / 100)
            : $val;
        $amount = min(max($amount, 0), $price);

        $this->services[$key]['discount_type']   = $this->discountType;
        $this->services[$key]['discount_value']  = (string) $val;
        $this->services[$key]['discount_amount'] = (string) $amount;
        $this->services[$key]['final_price']     = (string) ($price - $amount);

        $this->closeDiscountModal();
    }

    public function removeDiscount(string $key): void
    {
        $this->services[$key]['discount_type']   = 'none';
        $this->services[$key]['discount_value']  = '';
        $this->services[$key]['discount_amount'] = '0';
        $this->services[$key]['final_price']     = $this->services[$key]['price'];
    }

    #[Computed]
    public function discountPreview(): array
    {
        $key = $this->discountServiceKey;
        if (!$key || !isset($this->services[$key])) {
            return ['amount' => 0, 'final' => 0];
        }
        $price  = (float) ($this->services[$key]['price'] ?? 0);
        $val    = (float) $this->discountValue;
        $amount = ($this->discountType === 'percent')
            ? round($price * $val / 100)
            : $val;
        $amount = min(max($amount, 0), $price);
        return ['amount' => $amount, 'final' => $price - $amount];
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

    // ── Payment modal ─────────────────────────────────────────────────────
    public function openPaymentModal(int $projectId, bool $fromQueue = false): void
    {
        $this->paymentProjectId = $projectId;
        $this->paymentAmount    = '';
        $this->paymentDate      = now()->format('Y-m-d');
        $this->paymentMethod    = 'naqd';
        $this->paymentNote      = '';
        $this->paymentFromQueue = $fromQueue;

        $project = Project::with('services')->find($projectId);
        $this->paymentToposyomkaUserId = $project?->services->where('service_name', 'toposyomka')->first()?->assigned_user_id;
        $this->paymentEskizUserId      = $project?->services->where('service_name', 'eskiz_loyiha')->first()?->assigned_user_id;

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal        = false;
        $this->paymentProjectId        = 0;
        $this->paymentToposyomkaUserId = null;
        $this->paymentEskizUserId      = null;
        $this->paymentAmountConfirm    = false;
    }

    public function savePayment(): void
    {
        $project = Project::find($this->paymentProjectId);
        if (!$project) return;

        $hasAmount = filled($this->paymentAmount) && (float)$this->paymentAmount > 0;

        // Summa kiritilmagan — tasdiq so'rash
        if (!$hasAmount && !$this->paymentAmountConfirm) {
            $this->paymentAmountConfirm = true;
            return;
        }

        // Summali yo'l
        if ($hasAmount) {
            $this->validate([
                'paymentAmount' => 'required|numeric|min:1',
                'paymentDate'   => 'required|date',
                'paymentMethod' => 'required|in:naqd,bank,karta',
            ], [
                'paymentAmount.min'    => 'Summa 0 dan katta bo\'lishi kerak',
                'paymentDate.required' => 'Sana kiritilishi shart',
            ]);

            $payment = Payment::create([
                'project_id'   => $project->id,
                'amount'       => (float) $this->paymentAmount,
                'payment_date' => $this->paymentDate,
                'method'       => $this->paymentMethod,
                'note'         => trim($this->paymentNote) ?: null,
                'created_by'   => auth()->id(),
            ]);

            PaymentLog::create([
                'project_id' => $project->id,
                'payment_id' => $payment->id,
                'user_id'    => auth()->id(),
                'action'     => 'created',
                'amount'     => $payment->amount,
                'description'=> number_format($payment->amount, 0, '.', ' ') . " so'm qo'shildi",
            ]);

            $project->updateTotals();
        }

        // Xizmat mas'ullarini saqlash
        $this->applyServiceAssignments($project);

        if ($hasAmount) {
            $validKeys = \App\Models\ProjectStatus::allOrdered()->pluck('key')->toArray();
            if ($this->paymentFromQueue && in_array('tolangan', $validKeys)) {
                $this->logStatusChange($project, 'tolangan');
                $project->update([
                    'status'               => 'tolangan',
                    'payment_requested_at' => null,
                    'payment_requested_by' => null,
                ]);
            } elseif ($this->paymentMoveToEskiz
                && $project->status === 'tolov_jarayonida'
                && in_array('toposyomka', $validKeys)
            ) {
                $this->logStatusChange($project, 'toposyomka');
                $project->update(['status' => 'toposyomka']);
            }
        }

        $this->closePaymentModal();
        $this->dispatch('notify', type: 'success', message: $hasAmount ? "To'lov saqlandi!" : 'Hodimlar biriktirildi!');
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
        $this->editPaymentId     = $paymentId;
        $this->editPaymentAmount = (string)(float)$payment->amount;
        $this->showEditPaymentModal = true;
    }

    public function closeEditPayment(): void
    {
        $this->showEditPaymentModal = false;
        $this->editPaymentId        = 0;
        $this->editPaymentAmount    = '';
    }

    public function saveEditPayment(): void
    {
        $this->validate(
            ['editPaymentAmount' => 'required|numeric|min:1'],
            ['editPaymentAmount.required' => 'Summa kiritilishi shart',
             'editPaymentAmount.min'      => 'Summa 0 dan katta bo\'lishi kerak']
        );

        $payment = Payment::find($this->editPaymentId);
        if (!$payment) return;

        $oldAmount = (float) $payment->amount;
        $newAmount = (float) $this->editPaymentAmount;

        if ($oldAmount === $newAmount) {
            $this->closeEditPayment();
            return;
        }

        $payment->update(['amount' => $newAmount]);

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
        $this->dispatch('notify', type: 'success', message: "To'lov summasi yangilandi!");
    }

    // ── Route to department modal ─────────────────────────────────────────
    public function openRouteModal(int $projectId, string $currentStatus): void
    {
        $this->routeProjectId      = $projectId;
        $this->routeNewStatus      = '';
        $this->routeAllocDays      = 3;
        $project = Project::with('assignedUsers')->find($projectId);
        $this->routeAssignedUserId = $project?->assignedUsers->first()?->id
            ?? $project?->assigned_user_id;
        $this->showRouteModal      = true;
    }

    public function closeRouteModal(): void
    {
        $this->showRouteModal = false;
        $this->routeProjectId = 0;
    }

    public function confirmRoute(): void
    {
        $this->validate([
            'routeNewStatus' => 'required',
            'routeAllocDays' => 'required|integer|min:0|max:365',
        ], [
            'routeNewStatus.required' => 'Bosqichni tanlang',
        ]);

        $project = Project::find($this->routeProjectId);
        if (!$project) return;

        $this->logStatusChange($project, $this->routeNewStatus, $this->routeAllocDays, $this->routeAssignedUserId);

        $update = ['status' => $this->routeNewStatus];
        if ($this->routeAssignedUserId) {
            $update['assigned_user_id'] = $this->routeAssignedUserId;
            $project->assignedUsers()->syncWithoutDetaching([$this->routeAssignedUserId]);
        }
        $project->update($update);

        // Toposyomka / Eskiz loyiha ga yuborilganda xizmat mas'ulini yangilash
        if ($this->routeAssignedUserId && in_array($this->routeNewStatus, ['toposyomka', 'eskiz_loyiha', 'ariza'])) {
            $service = $project->services()->where('service_name', $this->routeNewStatus)->first();
            if ($service) {
                $service->update(['assigned_user_id' => $this->routeAssignedUserId]);
            }
        }

        $this->closeRouteModal();
        $this->dispatch('notify', type: 'success', message: 'Loyiha muvaffaqiyatli yo\'naltirildi!');
    }

    private function logStatusChange(Project $project, string $newStatus, int $allocDays = 0, ?int $assignedUserId = null): void
    {
        // Close current open log
        \App\Models\ProjectStatusLog::where('project_id', $project->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        // Open new log
        \App\Models\ProjectStatusLog::create([
            'project_id'       => $project->id,
            'status'           => $newStatus,
            'entered_at'       => now(),
            'allocated_days'   => $allocDays,
            'assigned_user_id' => $assignedUserId ?? $project->assigned_user_id,
        ]);
    }

    // ── Move project between statuses ────────────────────────────────────
    public function moveProject(int $projectId, string $newStatus): void
    {
        $valid = \App\Models\ProjectStatus::pluck('key')->toArray();
        if (!in_array($newStatus, $valid)) return;

        $project = Project::find($projectId);
        if (!$project) return;

        $this->logStatusChange($project, $newStatus);
        $project->update(['status' => $newStatus]);
    }

    // ── Save ─────────────────────────────────────────────────────────────
    public function createProject(): void
    {
        $user = auth()->user();
        if ($user?->isHisobchi() || $user?->isBajaruvchi()) {
            $this->showModal = false;
            return;
        }
        $this->validate();

        $phones = array_values(
            array_filter(
                array_map(fn($p) => ['phone' => trim($p)], $this->phones),
                fn($p) => strlen($p['phone']) > 4
            )
        );
        if (empty($phones)) $phones = [['phone' => '+998']];

        $primaryUserId = !empty($this->assigned_user_ids) ? $this->assigned_user_ids[0] : null;

        $project = Project::create([
            'owner_name'       => trim($this->owner_name),
            'title'            => trim($this->proj_title) ?: null,
            'address'          => trim($this->address),
            'phones'           => $phones,
            'description'      => trim($this->description) ?: null,
            'category'         => $this->category,
            'status'           => 'yangi',
            'assigned_user_id' => $primaryUserId,
            'deadline_date'    => ($this->deadline_days > 0) ? now()->addDays((int)$this->deadline_days)->toDateString() : null,
        ]);

        if (!empty($this->assigned_user_ids)) {
            $project->assignedUsers()->sync($this->assigned_user_ids);
        }

        // Initial status log
        \App\Models\ProjectStatusLog::create([
            'project_id'       => $project->id,
            'status'           => 'yangi',
            'entered_at'       => now(),
            'allocated_days'   => 0,
            'assigned_user_id' => $primaryUserId,
        ]);

        foreach ($this->services as $key => $srv) {
            $hasTiers = !empty($srv['has_tiers']);
            $included = $hasTiers
                ? !empty($srv['selected_tiers']) && !empty($srv['price'])
                : !empty($srv['selected']);
            if (!$included) continue;

            $price          = (float) ($srv['price'] ?? 0);
            $discountType   = $srv['discount_type'] ?? 'none';
            $discountValue  = (float) ($srv['discount_value']  ?? 0);
            $discountAmount = (float) ($srv['discount_amount'] ?? 0);
            $finalPrice     = ($discountType !== 'none') ? ($price - $discountAmount) : $price;

            ProjectService::create([
                'project_id'       => $project->id,
                'assigned_user_id' => $srv['assigned_user_id'] ?: null,
                'service_name'     => $key,
                'price'            => $price,
                'discount_type'    => $discountType,
                'discount_value'   => $discountValue,
                'final_price'      => max(0, $finalPrice),
            ]);
        }

        $allowedMimes = ['application/pdf','image/jpeg','image/png','image/gif',
            'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        foreach ($this->uploadedFiles as $file) {
            if ($file->getSize() > 20 * 1024 * 1024) continue;
            if (!in_array($file->getMimeType(), $allowedMimes)) continue;
            $path = $file->store('project-files/' . $project->id, 'public');
            ProjectFile::create([
                'project_id' => $project->id,
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'file_type'  => $file->getMimeType(),
                'file_size'  => $file->getSize(),
                'uploaded_by'=> auth()->id(),
            ]);
        }

        $project->updateTotals();
        $this->showModal = false;
        $this->dispatch('project-created');
        $this->dispatch('notify', type: 'success', message: 'Loyiha muvaffaqiyatli yaratildi!');
    }

    // ── View data ─────────────────────────────────────────────────────────
    public function getViewData(): array
    {
        $authUser  = auth()->user();
        $dbStatuses = \App\Models\ProjectStatus::allOrdered();

        $statuses      = [];
        $routeStatuses = [];
        $isPrivileged  = $authUser?->isAdmin() || in_array($authUser?->role, ['menejer']);

        $ds = \App\Services\DesignSettingsService::get();

        foreach ($dbStatuses as $ps) {
            $rawBg   = $ds["kanban_col_{$ps->key}_bg"]      ?? '#1e293b';
            $opacity = max(0, min(100, (int)($ds["kanban_col_{$ps->key}_opacity"] ?? 100))) / 100;
            $text    = $ds["kanban_col_{$ps->key}_text"]    ?? '#f1f5f9';
            $headBg  = \App\Services\DesignSettingsService::hexToRgba($rawBg ?: '#1e293b', $opacity);

            $data = ['label' => $ps->label, 'color' => $ps->color, 'is_archive' => $ps->is_archive, 'head_bg' => $headBg, 'head_text' => $text];

            // Route modal: admin/menejer — barchasi; hodimlar — eskiz_loyiha + tekshirish
            if ($isPrivileged || in_array($ps->key, ['eskiz_loyiha', 'tekshirish'])) {
                $routeStatuses[$ps->key] = $data;
            }

            // Faol ustunlar (is_archive=false) — barcha hodimlar ko'radi;
            // Arxiv ustunlar — faqat admin yoki maxsus ruxsat bo'lsa ko'rinadi
            if (!$ps->is_archive || $authUser?->isAdmin() || $authUser?->hasPermission('kanban_' . $ps->key)) {
                $statuses[$ps->key] = $data;
            }
        }

        // URL ?status= filtri — faqat bitta holat ko'rsatiladi
        if ($this->filterStatus && isset($statuses[$this->filterStatus])) {
            $statuses = [$this->filterStatus => $statuses[$this->filterStatus]];
        }

        $projectQuery = Project::with(['assignedUsers', 'services.assignedUser', 'currentStatusLog', 'payments'])
            ->orderBy('created_at', 'desc');

        // Qidiruv filtri
        if (!empty($this->search)) {
            $q = trim($this->search);
            $projectQuery->where(function ($query) use ($q) {
                $query->where('owner_name', 'like', "%{$q}%")
                      ->orWhere('number', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%");
            });
        }

        if ($authUser && !$authUser->canSeeAllProjects()) {
            if ($authUser->isHisobchi()) {
                $projectQuery->where('status', '!=', 'yangi');
            } elseif (!$authUser->hasPermission('barcha_loyihalar')) {
                // Faqat assignedUsers bo'yicha filtr — ProjectResource bilan bir xil mantiq.
                // Services filtrini olib tashladik: agar admin assignedUsers dan olib tashlasa,
                // hodim loyihani ko'rmasligi kerak.
                $projectQuery->whereHas('assignedUsers', fn($q) => $q->where('users.id', $authUser->id));
            }
        }

        $projects = $projectQuery->get()->groupBy('status');

        $users           = User::orderBy('name')->get();
        $serviceOptions  = Project::serviceOptions();
        $categoryOptions = Project::categoryOptions();

        $priceTiers = $this->priceTiers;

        $paymentQueue = collect();
        if ($authUser?->isHisobchi() || $authUser?->canSeeAllProjects()) {
            $paymentQueue = Project::with(['assignedUsers', 'paymentRequester'])
                ->whereNotNull('payment_requested_at')
                ->orderBy('payment_requested_at', 'asc')
                ->get();
        }

        return compact('statuses', 'routeStatuses', 'projects', 'users', 'serviceOptions', 'categoryOptions', 'priceTiers', 'paymentQueue');
    }
}
