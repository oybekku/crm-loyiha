<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectService;
use App\Models\ServicePriceTier;
use App\Models\User;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class KanbanBoard extends Page
{
    use WithFileUploads;

    protected static string $view = 'filament.pages.kanban-board';
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Loyihalar';
    protected static ?string $navigationGroup = 'Loyihalar';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = '';

    public bool  $showModal = false;
    public int   $step      = 1;

    public string $owner_name       = '';
    public string $proj_title       = '';
    public string $address          = '';
    public array  $phones           = ['+998'];
    public string $description      = '';
    public string $category         = 'turar';
    public ?int   $assigned_user_id = null;
    public string $deadline_date    = '';
    public array  $uploadedFiles    = [];

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
    }

    // ── Rules ─────────────────────────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'owner_name' => 'required|min:2',
            'address'    => 'required|min:3',
        ];
    }

    protected function messages(): array
    {
        return [
            'owner_name.required' => 'Egasining ismi kiritilishi shart',
            'owner_name.min'      => "Ismi kamida 2 ta harf bo'lishi kerak",
            'address.required'    => 'Manzil kiritilishi shart',
            'address.min'         => "Manzil kamida 3 ta harf bo'lishi kerak",
        ];
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────
    public function mount(): void
    {
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
                'label'           => $label,
                'selected'        => false,
                'price'           => '',
                'has_tiers'       => $hasTiers,
                'selected_tiers'  => [],
                'discount_type'   => 'none',
                'discount_value'  => '',
                'discount_amount' => '0',
                'final_price'     => '',
            ];
            if ($hasTiers && $firstSub) {
                $this->activeSubTab[$key] = $firstSub;
            }
        }
    }

    // ── Modal ─────────────────────────────────────────────────────────────
    public function openModal(): void
    {
        $this->reset(['owner_name', 'proj_title', 'address', 'description', 'assigned_user_id', 'deadline_date']);
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
        if ($this->step === 1) $this->validate();
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
        $total = 0;
        foreach ($this->services[$serviceKey]['selected_tiers'] as $sub => $id) {
            foreach ($tiers[$serviceKey][$sub] ?? [] as $tier) {
                if ($tier['id'] === $id) { $total += $tier['price']; break; }
            }
        }
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

    // ── Payment modal ─────────────────────────────────────────────────────
    public function openPaymentModal(int $projectId): void
    {
        $this->paymentProjectId = $projectId;
        $this->paymentAmount    = '';
        $this->paymentDate      = now()->format('Y-m-d');
        $this->paymentMethod    = 'naqd';
        $this->paymentNote      = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentProjectId = 0;
    }

    public function savePayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentDate'   => 'required|date',
            'paymentMethod' => 'required|in:naqd,bank,karta',
        ], [
            'paymentAmount.required' => 'Summa kiritilishi shart',
            'paymentAmount.min'      => 'Summa 0 dan katta bo\'lishi kerak',
            'paymentDate.required'   => 'Sana kiritilishi shart',
        ]);

        $project = \App\Models\Project::find($this->paymentProjectId);
        if (!$project) return;

        \App\Models\Payment::create([
            'project_id'   => $project->id,
            'amount'       => (float) $this->paymentAmount,
            'payment_date' => $this->paymentDate,
            'method'       => $this->paymentMethod,
            'note'         => trim($this->paymentNote) ?: null,
            'created_by'   => auth()->id(),
        ]);

        $project->updateTotals();

        if ($this->paymentMoveToEskiz && $project->status === 'tolov_jarayonida') {
            $this->logStatusChange($project, 'eskiz_loyiha');
            $project->update(['status' => 'eskiz_loyiha']);
        }

        $this->closePaymentModal();
        $this->dispatch('notify', type: 'success', message: "To'lov saqlandi!");
    }

    // ── Route to department modal ─────────────────────────────────────────
    public function openRouteModal(int $projectId, string $currentStatus): void
    {
        $this->routeProjectId      = $projectId;
        $this->routeNewStatus      = '';
        $this->routeAllocDays      = 3;
        $this->routeAssignedUserId = Project::find($projectId)?->assigned_user_id;
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
        }
        $project->update($update);

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
        $valid = ['yangi','tolov_jarayonida','tekshirish','tolangan','tugallangan','taqdim_etilgan','bekor_qilingan'];
        if (!in_array($newStatus, $valid)) return;

        $project = Project::find($projectId);
        if (!$project) return;

        $this->logStatusChange($project, $newStatus);
        $project->update(['status' => $newStatus]);
    }

    // ── Save ─────────────────────────────────────────────────────────────
    public function createProject(): void
    {
        $this->validate();

        $phones = array_values(
            array_filter(
                array_map(fn($p) => ['phone' => trim($p)], $this->phones),
                fn($p) => strlen($p['phone']) > 4
            )
        );
        if (empty($phones)) $phones = [['phone' => '+998']];

        $project = Project::create([
            'owner_name'       => trim($this->owner_name),
            'title'            => trim($this->proj_title) ?: null,
            'address'          => trim($this->address),
            'phones'           => $phones,
            'description'      => trim($this->description) ?: null,
            'category'         => $this->category,
            'status'           => 'yangi',
            'assigned_user_id' => $this->assigned_user_id ?: null,
            'deadline_date'    => $this->deadline_date ?: null,
        ]);

        // Initial status log
        \App\Models\ProjectStatusLog::create([
            'project_id'       => $project->id,
            'status'           => 'yangi',
            'entered_at'       => now(),
            'allocated_days'   => 0,
            'assigned_user_id' => $this->assigned_user_id ?: null,
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
                'project_id'     => $project->id,
                'service_name'   => $key,
                'price'          => $price,
                'discount_type'  => $discountType,  // 'none' | 'percent' | 'fixed'
                'discount_value' => $discountValue,
                'final_price'    => max(0, $finalPrice),
            ]);
        }

        foreach ($this->uploadedFiles as $file) {
            $path = $file->store('project-files', 'public');
            ProjectFile::create([
                'project_id' => $project->id,
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'file_type'  => $file->getMimeType(),
                'file_size'  => $file->getSize(),
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
        $statuses = [
            'yangi'            => ['label' => 'Yangi',              'color' => '#3b82f6'],
            'tolov_jarayonida' => ['label' => "To'lov jarayonida",  'color' => '#f59e0b'],
            'eskiz_loyiha'     => ['label' => 'Eskiz loyiha',       'color' => '#8b5cf6'],
            'tekshirish'       => ['label' => 'Tekshirish',         'color' => '#6366f1'],
            'tolangan'         => ['label' => "To'langan",          'color' => '#10b981'],
            'tugallangan'      => ['label' => 'Tugallangan',        'color' => '#6b7280'],
            'taqdim_etilgan'   => ['label' => 'Taqdim etilgan',     'color' => '#0ea5e9'],
            'bekor_qilingan'   => ['label' => 'Bekor qilingan',     'color' => '#ef4444'],
        ];

        $projectQuery = Project::with(['assignedUser', 'services', 'currentStatusLog'])
            ->orderBy('created_at', 'desc');

        $user = auth()->user();
        if ($user && !$user->canSeeAllProjects()) {
            $projectQuery->where(function ($q) use ($user) {
                $q->where('assigned_user_id', $user->id);
                if ($user->isHisobchi()) {
                    $q->orWhere('status', 'tolov_jarayonida');
                }
            });
        }

        $projects = $projectQuery->get()->groupBy('status');

        $users           = User::orderBy('name')->get();
        $serviceOptions  = Project::serviceOptions();
        $categoryOptions = Project::categoryOptions();

        $priceTiers = $this->priceTiers;

        return compact('statuses', 'projects', 'users', 'serviceOptions', 'categoryOptions', 'priceTiers');
    }
}
