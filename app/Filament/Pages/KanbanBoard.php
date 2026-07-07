<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectService;
use App\Models\ProjectStatusLog;
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

    // Tanlangan davr (oy/yil) — loyihalar ochilgan oyiga qarab
    public ?int $kbYear  = null;
    public ?int $kbMonth = null;

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
    public string $mygov_fish        = '';   // MyGOV — kim orqali keldi
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
    public ?int   $paymentToposyomkaUserId  = null;
    public ?int   $paymentEskizUserId       = null;
    public array  $paymentSelectedServices  = []; // tanlangan xizmatlar
    public array  $paymentAdjustments       = []; // service_id => +/- summa (faqat admin)
    public bool   $paymentAmountConfirm    = false;

    // Edit payment modal state
    public bool   $showEditPaymentModal = false;
    public int    $editPaymentId        = 0;
    public string $editPaymentAmount    = '';

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

    // Loyiha ma'lumotini tahrirlash modali ALOHIDA komponentga ko'chirildi:
    // App\Livewire\ProjectEditModal — shu sababli modal ochilganda butun doska
    // qaytadan yuborilmaydi (tezlik). Amal tugmalari kb-* eventlari orqali keladi.

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
        $this->kbYear  ??= (int) now()->year;
        $this->kbMonth ??= (int) now()->month;
        $this->initServices();
    }

    public function kbChangeMonth(int $delta): void
    {
        $date = \Carbon\Carbon::create($this->kbYear, $this->kbMonth, 1)->addMonths($delta);
        $this->kbYear  = (int) $date->year;
        $this->kbMonth = (int) $date->month;
    }

    public function kbSetMonth(int $year, int $month): void
    {
        $this->kbYear  = $year;
        $this->kbMonth = $month;
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
                'custom_price'     => '',
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
        if ($user?->isHisobchi()) return;
        $this->reset(['owner_name', 'proj_title', 'address', 'latitude', 'longitude', 'description', 'mygov_fish', 'assigned_user_ids', 'deadline_days', 'showDeadlineConfirm']);
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

            // work_started_at logikasi:
            // 1. Loyiha hozir o'sha statusda → hozirdan boshlash
            // 2. Loyiha o'sha statusdan o'tib ketgan → status log dan olish
            // 3. Status hali kelmagan → NULL (⌛ kutmoqda)
            $startedAt = $svc->work_started_at; // mavjudni saqlash
            if ($userId && !$startedAt) {
                $project = Project::find($this->serviceAssignProjectId);
                if ($project) {
                    $log = \App\Models\ProjectStatusLog::where('project_id', $project->id)
                        ->where('status', $svc->service_name)
                        ->orderBy('entered_at')
                        ->first();
                    if ($log) {
                        // Status o'tilgan — log dan olish
                        $startedAt = $log->entered_at;
                    } elseif ($project->status === $svc->service_name) {
                        // Hozir shu statusda
                        $startedAt = $now;
                    }
                    // Aks holda NULL qoladi (⌛)
                }
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

    public function markComplete(int $projectId): void
    {
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isMenejer()) return;

        $project = Project::findOrFail($projectId);
        $oldStatus = $project->status;
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

        $this->dispatch('notify', type: 'success', message: 'Loyiha tugallandi!');
    }

    // Zudlik olovini "Qabul qildim" — mas'ul hodim yoki admin/menejer o'chiradi
    public function acceptUrgent(int $projectId): void
    {
        $project = Project::find($projectId);
        if (!$project || !$project->is_urgent) return;

        $u = auth()->user();
        $isAssigned = $u && $project->services()->where('assigned_user_id', $u->id)->exists();
        if (!$u || (!$u->canSeeAllProjects() && !$isAssigned)) {
            $this->dispatch('notify', type: 'error', message: "Ruxsat yo'q");
            return;
        }

        $project->update([
            'is_urgent'          => false,
            'urgent_accepted_at' => now(),
            'urgent_accepted_by' => $u->id,
        ]);

        $this->dispatch('notify', type: 'success', message: "Qabul qilindi — zudlik o'chdi");
    }

    // Zudlik bayrog'i — admin/menejer kartadagi bayroqni bosib yoqadi/o'chiradi
    public function toggleUrgent(int $projectId): void
    {
        $u = auth()->user();
        if (!$u || !$u->canSeeAllProjects()) return;

        $project = Project::find($projectId);
        if (!$project) return;

        $on = !$project->is_urgent;
        $project->update([
            'is_urgent'          => $on,
            'urgent_accepted_at' => $on ? null : $project->urgent_accepted_at,
            'urgent_accepted_by' => $on ? null : $project->urgent_accepted_by,
        ]);

        $this->dispatch('notify', type: 'success', message: $on ? 'Zudlik yoqildi' : "Zudlik o'chirildi");
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


    // ── Service complete toggle (faqat admin) ────────────────────────────
    public function toggleServiceComplete(int $serviceId): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $svc = \App\Models\ProjectService::findOrFail($serviceId);
        $svc->completed_at = $svc->completed_at ? null : now();
        $svc->saveQuietly();
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
        $this->paymentSelectedServices = []; // reset
        $this->paymentAdjustments      = []; // reset

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

            $payment = Payment::create([
                'project_id'   => $project->id,
                'amount'       => (float) $this->paymentAmount,
                'payment_date' => $this->paymentDate,
                'method'       => $this->paymentMethod,
                'note'         => trim($this->paymentNote) ?: null,
                'created_by'   => auth()->id(),
                'services'     => !empty($this->paymentSelectedServices) ? $this->paymentSelectedServices : null,
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
            } elseif ($project->status === 'yangi'
                && in_array('yangi_loyihalar', $validKeys)
            ) {
                $this->logStatusChange($project, 'yangi_loyihalar');
                $project->update(['status' => 'yangi_loyihalar']);
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

    // ── Delete payment (PIN kod bilan) ────────────────────────────────────
    public function openDeletePayment(int $paymentId): void
    {
        $this->deletePaymentId       = $paymentId;
        $this->deletePaymentPin      = '';
        $this->deletePaymentPinError = false;
        $this->showDeletePaymentModal = true;
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
        if ($this->deletePaymentPin !== '2728') {
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
    }

    // ── Xizmat narxini tahrirlash (Joriy narx — PIN kod bilan) ────────────
    public function openServicePrice(int $serviceId): void
    {
        $svc = \App\Models\ProjectService::find($serviceId);
        if (!$svc) return;
        $this->servicePriceId       = $serviceId;
        $this->servicePriceValue    = (string) (float) $svc->final_price;
        $this->servicePricePin      = '';
        $this->servicePricePinError = false;
        $this->showServicePriceModal = true;
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
        if ($this->servicePricePin !== '2728') {
            $this->servicePricePinError = true;
            return;
        }

        $svc = \App\Models\ProjectService::find($this->servicePriceId);
        if (!$svc) {
            $this->closeServicePrice();
            return;
        }

        $newPrice = max(0, (float) str_replace([' ', ','], '', $this->servicePriceValue));
        $svc->update(['final_price' => $newPrice, 'price' => $newPrice]);

        Project::find($svc->project_id)?->updateTotals();

        $this->closeServicePrice();
        $this->dispatch('notify', type: 'success', message: 'Narx yangilandi!');
    }

    // ── Edit modal (ProjectEditModal komponenti) eventlari ────────────────
    // Modaldagi amal tugmalari shu listenerlarni chaqiradi → tegishli modalni ochadi.
    #[\Livewire\Attributes\On('kb-open-payment')]
    public function kbOpenPayment(int $id): void { $this->openPaymentModal($id); }

    #[\Livewire\Attributes\On('kb-open-route')]
    public function kbOpenRoute(int $id, string $status): void { $this->openRouteModal($id, $status); }

    #[\Livewire\Attributes\On('kb-open-assign')]
    public function kbOpenAssign(int $id): void { $this->openServiceAssignModal($id); }

    #[\Livewire\Attributes\On('kb-request-payment')]
    public function kbRequestPayment(int $id): void { $this->requestPayment($id); }

    #[\Livewire\Attributes\On('kb-cancel-request')]
    public function kbCancelRequest(int $id): void { $this->cancelPaymentRequest($id); }

    #[\Livewire\Attributes\On('kb-mark-complete')]
    public function kbMarkComplete(int $id): void { $this->markComplete($id); }

    #[\Livewire\Attributes\On('kb-mark-uncomplete')]
    public function kbMarkUncomplete(int $id): void { $this->markUncomplete($id); }

    #[\Livewire\Attributes\On('kb-move')]
    public function kbMove(int $id, string $status): void { $this->moveProject($id, $status); }

    // Ma'lumot/xizmat o'zgardi — doska qaytadan render bo'lsin (getViewData)
    #[\Livewire\Attributes\On('kb-refresh')]
    public function kbRefresh(): void {}

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

        // Toposyomka / Eskiz loyiha ga yuborilsa — avval "Yangi X" (staging) bo'limiga tushadi
        $targetDept  = $this->routeNewStatus;
        $stagingMap  = ['toposyomka' => 'yangi_toposyomka', 'eskiz_loyiha' => 'yangi_eskiz_loyiha'];
        $finalStatus = $stagingMap[$targetDept] ?? $targetDept;

        $this->logStatusChange($project, $finalStatus, $this->routeAllocDays, $this->routeAssignedUserId);

        $update = ['status' => $finalStatus];
        if ($this->routeAssignedUserId) {
            $update['assigned_user_id'] = $this->routeAssignedUserId;
            $project->assignedUsers()->syncWithoutDetaching([$this->routeAssignedUserId]);
        }
        $project->update($update);

        // Xizmat mas'ulini yangilash — asl bo'lim (toposyomka/eskiz_loyiha) bo'yicha
        if ($this->routeAssignedUserId && in_array($targetDept, ['toposyomka', 'eskiz_loyiha', 'ariza'])) {
            $service = $project->services()->where('service_name', $targetDept)->first();
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
        if ($user?->isHisobchi()) {
            $this->showModal = false;
            return;
        }
        $this->validate();

        // Xizmat narxlari chegaradan oshmasin (decimal(15,2) — maksimum ~9.9 trln). Xato kiritishdan himoya.
        $MAX_PRICE = 99999999999; // ~100 mlrd so'm — real xizmat narxi undan past bo'ladi
        foreach ($this->services as $srv) {
            $p1 = (float) ($srv['custom_price'] ?? 0);
            $p2 = (float) ($srv['price'] ?? 0);
            if ($p1 > $MAX_PRICE || $p2 > $MAX_PRICE) {
                $this->dispatch('notify', type: 'error', message: 'Xizmat narxi juda katta — narxni tekshiring');
                return;
            }
        }

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
            'latitude'         => $this->latitude ? (float)$this->latitude : null,
            'longitude'        => $this->longitude ? (float)$this->longitude : null,
            'phones'           => $phones,
            'description'      => trim($this->description) ?: null,
            'category'         => $this->category,
            'mygov_fish'       => trim($this->mygov_fish) ?: null,
            'status'           => 'yangi',
            'assigned_user_id' => $primaryUserId,
            'deadline_date'    => ($this->deadline_days > 0) ? now()->addDays((int)$this->deadline_days)->toDateString() : null,
        ]);

        // assignedUsers: tanlangan hodimlar + loyiha yaratuvchi (agar hodim bo'lsa)
        $assignIds = $this->assigned_user_ids ?? [];
        $creator   = auth()->user();
        if ($creator && !$creator->isAdmin() && !$creator->isMenejer() && !$creator->isHisobchi()) {
            $assignIds = array_unique(array_merge($assignIds, [$creator->id]));
        }
        if (!empty($assignIds)) {
            $project->assignedUsers()->sync($assignIds);
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
            $hasTiers    = !empty($srv['has_tiers']);
            $customPrice = (float) ($srv['custom_price'] ?? 0);
            $included = $hasTiers
                ? ($customPrice > 0) || (!empty($srv['selected_tiers']) && !empty($srv['price']))
                : !empty($srv['selected']);
            if (!$included) continue;

            // Ixtiyoriy narx berilgan bo'lsa — ustun turadi
            $price          = $customPrice > 0 ? $customPrice : (float) ($srv['price'] ?? 0);
            $discountType   = $customPrice > 0 ? 'none' : ($srv['discount_type'] ?? 'none');
            $discountValue  = $customPrice > 0 ? 0 : (float) ($srv['discount_value']  ?? 0);
            $discountAmount = $customPrice > 0 ? 0 : (float) ($srv['discount_amount'] ?? 0);
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
    /**
     * Avtomatik status ko'chirish:
     *  1. Mas'ul biriktirilmagan toposyomka/eskiz loyihalari → "Yangi X" bo'limida kutadi;
     *     mas'ul biriktirilgach asl bo'limga (toposyomka/eskiz_loyiha) o'tadi.
     *  2. Joriy bo'lim muddatiga (allocated_days) ≤3 kun qolgan loyihalar → "Kechikayotgan" bo'limga.
     */
    protected function reconcileAutoStatuses(): void
    {
        // TEZLIK: har Livewire amalida (modal ochish, saqlash...) emas —
        // eng ko'pi 30 soniyada bir marta ishlaydi. Aks holda yuzlab loyihani
        // har bosishда tekshirish 10 soniya kutishга olib kelardi.
        if (!\Illuminate\Support\Facades\Cache::add('kanban_reconcile_lock', 1, 30)) {
            return;
        }

        // 1) "Yangi X" ↔ asl bo'lim — endi avtomatik EMAS.
        //    Route ("O'tkazish") qilinganda loyiha avval "Yangi X" (staging) ga tushadi
        //    (confirmRoute da), undan asl bo'limga (Toposyomka/Eskiz) QO'LDA suriladi.
        //    Shu sababli mas'ul holatiga qarab avtomatik ko'chirish o'chirildi.

        // 2) Kechikayotgan — joriy bo'lim ishi (xizmat) muddati O'TGANDA (0k/kech).
        //    Kutishdagilar (timer_paused_at) va tekshirishга yuborilganlar (submitted_at — muzlagan) mustasno.
        Project::whereIn('status', ['toposyomka', 'eskiz_loyiha'])
            ->whereNull('timer_paused_at')
            ->with(['services', 'currentStatusLog'])
            ->get()
            ->each(function ($p) {
                // Joriy statusga mos xizmat muddati 0k yoki o'tganmi (days_left <= 0)?
                $svc = $p->services->firstWhere('service_name', $p->status);
                if (!$svc || $svc->days_left === null || $svc->days_left > 0) return;
                $log = $p->currentStatusLog;
                // Xizmat work_started_at/deadline_days o'zgarmaydi — kartada "Nk kech" davom etadi
                $this->switchProjectStatus(
                    $p, 'kechikayotgan',
                    $log?->entered_at, (int) ($log?->allocated_days ?? 0), $log?->assigned_user_id
                );
            });
    }

    protected function switchProjectStatus(Project $p, string $newStatus, $enteredAt = null, ?int $allocatedDays = null, $assignedUserId = null): void
    {
        if ($p->status === $newStatus) return;

        \App\Models\ProjectStatusLog::where('project_id', $p->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        \App\Models\ProjectStatusLog::create([
            'project_id'       => $p->id,
            'status'           => $newStatus,
            'entered_at'       => $enteredAt ?? now(),
            'allocated_days'   => $allocatedDays ?? 0,
            'assigned_user_id' => $assignedUserId,
        ]);

        $p->status = $newStatus;
        $p->saveQuietly();
    }

    /**
     * Kun hisobini to'xtatish/yoqish (soat).
     *  - To'xtatilganda: kun sanalmaydi, kartada soat chiqadi, Kechikayotganga ko'chmaydi.
     *  - Qayta yoqilganda: to'xtab turgan vaqt oldinga suriladi — kun yo'qolmaydi.
     */
    public function toggleTimer(int $projectId): void
    {
        $project = Project::with('currentStatusLog')->find($projectId);
        if (!$project) return;

        if ($project->timer_paused_at) {
            // QAYTA YOQISH — to'xtab turgan vaqtni oldinga suramiz
            $pausedSeconds = (int) $project->timer_paused_at->diffInSeconds(now());

            if ($log = $project->currentStatusLog) {
                $log->update(['entered_at' => $log->entered_at->copy()->addSeconds($pausedSeconds)]);
            }
            foreach ($project->services()->whereNotNull('work_started_at')->get() as $svc) {
                $svc->update([
                    'work_started_at' => \Carbon\Carbon::parse($svc->work_started_at)->addSeconds($pausedSeconds),
                ]);
            }

            $project->update(['timer_paused_at' => null]);
            $this->dispatch('notify', type: 'success', message: 'Vaqt hisobi yoqildi ▶');
        } else {
            // TO'XTATISH — kun hisobi to'xtaydi (kutish, soat chiqadi)
            $project->update(['timer_paused_at' => now()]);
            $this->dispatch('notify', type: 'success', message: "Vaqt hisobi to'xtatildi ⏸ (kutishda)");
        }
    }

    public function getViewData(): array
    {
        $this->reconcileAutoStatuses();

        $authUser  = auth()->user();
        $dbStatuses = \App\Models\ProjectStatus::allOrdered();

        $statuses      = [];
        $routeStatuses = [];
        $isPrivileged  = $authUser?->isAdmin() || in_array($authUser?->role, ['menejer']);

        $ds = \App\Services\DesignSettingsService::get();

        foreach ($dbStatuses as $ps) {
            // Yashirilgan bo'limlar — Kanban'da umuman ko'rsatilmaydi (admin uchun ham)
            if ($ps->is_hidden) continue;

            $rawBg   = $ds["kanban_col_{$ps->key}_bg"]      ?? '#1e293b';
            $opacity = max(0, min(100, (int)($ds["kanban_col_{$ps->key}_opacity"] ?? 100))) / 100;
            $text    = $ds["kanban_col_{$ps->key}_text"]    ?? '#f1f5f9';
            $headBg  = \App\Services\DesignSettingsService::hexToRgba($rawBg ?: '#1e293b', $opacity);

            $data = ['label' => $ps->label, 'color' => $ps->color, 'is_archive' => $ps->is_archive, 'head_bg' => $headBg, 'head_text' => $text];

            // Route modal: admin/menejer — barchasi; hodimlar — eskiz_loyiha + tekshirish
            if ($isPrivileged || in_array($ps->key, ['eskiz_loyiha', 'tekshirish'])) {
                $routeStatuses[$ps->key] = $data;
            }

            // Hodim (bajaruvchi) — o'zi biriktirilgan ish turlari
            // + maxsus "BARCHA loyiha" ruxsati berilgan ustunlar (masalan Tugallangan, MyGOV)
            if ($authUser?->isBajaruvchi()) {
                if (in_array($ps->key, $authUser->kanbanServiceCols())
                    || $authUser->hasPermission('kanban_all_' . $ps->key)) {
                    $statuses[$ps->key] = $data;
                }
                continue;
            }

            // Faol ustunlar (is_archive=false) — barcha hodimlar ko'radi;
            // Arxiv ustunlar — faqat admin yoki maxsus ruxsat bo'lsa ko'rinadi
            if (!$ps->is_archive || $authUser?->isAdmin() || $authUser?->hasPermission('kanban_' . $ps->key)) {
                $statuses[$ps->key] = $data;
            }
        }

        $allStatuses = $statuses; // Tab bar uchun har doim barchasi

        // URL ?status= filtri — faqat bitta holat ko'rsatiladi
        if ($this->filterStatus && isset($statuses[$this->filterStatus])) {
            $statuses = [$this->filterStatus => $statuses[$this->filterStatus]];
        }

        // TEZLIK: faqat kartага kerakli relationlar yuklanadi.
        // 'payments' va 'statusLogs' kartada ishlatilmaydi (paid_amount/percent maydonlardan) —
        // ularni har bosishда yuzlab loyiha uchun yuklash sekinlik sababi edi.
        $projectQuery = Project::with(['assignedUsers', 'services.assignedUser', 'currentStatusLog'])
            ->orderBy('created_at', 'desc');

        // Tanlangan oy/yil — loyiha OCHILGAN (created_at) oyiga qarab.
        // Qidiruv ishlatilganda — davr filtri olib tashlanadi (hammasidan qidiriladi).
        if (empty($this->search)) {
            $projectQuery->whereYear('created_at', $this->kbYear)
                         ->whereMonth('created_at', $this->kbMonth);
        }

        // Qidiruv filtri
        if (!empty($this->search)) {
            $q = trim($this->search);
            $projectQuery->where(function ($query) use ($q) {
                $query->where('owner_name', 'like', "%{$q}%")
                      ->orWhere('number', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%");
            });
        }

        // Maxsus "BARCHA loyiha" ruxsati berilgan ustunlar (sukut bo'yicha hech kimda yo'q)
        $fullViewCols = [];
        if ($authUser && $authUser->isBajaruvchi()) {
            foreach ($dbStatuses as $ps) {
                if ($authUser->hasPermission('kanban_all_' . $ps->key)) $fullViewCols[] = $ps->key;
            }
        }

        if ($authUser && !$authUser->canSeeAllProjects()) {
            if ($authUser->isHisobchi()) {
                $projectQuery->where('status', '!=', 'yangi');
            } elseif (!$authUser->hasPermission('barcha_loyihalar')) {
                // Hodim FAQAT o'zi mas'ul loyihalarni ko'radi (izolyatsiya)
                // + maxsus ruxsat berilgan ustunlardagi BARCHA loyiha (masalan Nursulton: Tugallangan/MyGOV)
                $projectQuery->where(function ($q) use ($authUser, $fullViewCols) {
                    $q->whereHas('services', fn($s) => $s->where('assigned_user_id', $authUser->id));
                    if (!empty($fullViewCols)) {
                        $q->orWhereIn('status', $fullViewCols);
                    }
                });
            }
        }

        $projects = $projectQuery->get()->groupBy('status');

        // MyGOV ustuni oyга bog'liq bo'lmasin — ariza navbati barcha oylar bo'yicha to'liq ko'rinsin.
        // (Loyiha eski oyда ochilgan bo'lsa ham, MyGOV'ga surilsa shu ustunда turadi.)
        if (empty($this->search)) {
            $mygovQuery = Project::with(['assignedUsers', 'services.assignedUser', 'currentStatusLog'])
                ->where('status', 'mygov')
                ->orderBy('created_at', 'desc');
            if ($authUser && !$authUser->canSeeAllProjects() && !$authUser->isHisobchi()
                && !$authUser->hasPermission('barcha_loyihalar')
                && !$authUser->hasPermission('kanban_all_mygov')) {
                $mygovQuery->whereHas('services', fn($q) => $q->where('assigned_user_id', $authUser->id));
            }
            $projects['mygov'] = $mygovQuery->get();
        }

        $users           = User::orderBy('name')->get();
        $serviceOptions  = Project::serviceOptions();
        $categoryOptions = Project::categoryOptions();

        $priceTiers = $this->priceTiers;

        $paymentQueue = collect();
        if ($authUser?->isHisobchi() || $authUser?->canSeeAllProjects()) {
            // To'lov navbati ham tanlangan oyga bog'lanadi (ustunlar bilan bir xil —
            // loyiha OCHILGAN oyiga qarab), shunda eski oy so'rovlari yangi oyga o'tmaydi.
            $paymentQueue = Project::with(['assignedUsers', 'paymentRequester'])
                ->whereNotNull('payment_requested_at')
                ->whereYear('created_at', $this->kbYear)
                ->whereMonth('created_at', $this->kbMonth)
                ->orderBy('payment_requested_at', 'asc')
                ->get();
        }

        // TEZLIK: barcha loyihalar bo'yicha og'ir distinct so'rovi faqat
        // "Yangi loyiha" formasi ochiq bo'lganda ishlaydi (avtocomplete uchun).
        // Aks holda (modal ochish/yopish va h.k.) — skanlanmaydi.
        $existingOwners = $this->showModal
            ? Project::distinct()->orderBy('owner_name')->pluck('owner_name')->filter()->values()
            : collect();

        // MyGOV FISH avtomat-taklif ro'yxati (yangi loyiha formasi ochiq bo'lsa)
        $mygovFishList = $this->showModal
            ? Project::whereNotNull('mygov_fish')->where('mygov_fish', '!=', '')
                ->distinct()->orderBy('mygov_fish')->pluck('mygov_fish')->toArray()
            : [];

        $kbMonthLabel = \Carbon\Carbon::create($this->kbYear, $this->kbMonth, 1)->translatedFormat('F Y');

        // Qidiruv tekis ro'yxati uchun — barcha statuslar belgisi
        $statusMap = $dbStatuses->keyBy('key')->map(fn($s) => ['label' => $s->label, 'color' => $s->color])->toArray();

        return compact('statuses', 'allStatuses', 'routeStatuses', 'projects', 'users', 'serviceOptions', 'categoryOptions', 'priceTiers', 'paymentQueue', 'existingOwners', 'mygovFishList', 'kbMonthLabel', 'statusMap');
    }
}
