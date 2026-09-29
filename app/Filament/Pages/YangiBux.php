<?php

namespace App\Filament\Pages;

use App\Models\EmployeeSalaryPayment;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Payment;
use App\Models\Project;
use App\Models\RecurringExpense;
use App\Services\EmployeePayableService;
use Carbon\Carbon;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

// "Yangi bux" — Buxgalteriyaning yangi (dashboard ko'rinishidagi) varianti.
// Yangi jadval YO'Q — hammasi mavjud jadvallarda, shuning uchun eski
// Buxgalteriya, Kanban (loyiha to'lov oynasi) va shu sahifa doim sinxron:
//  - Kirim  = payments (loyiha to'lovi). Qo'shish/tahrirlash Kanban'dagi
//    aynan o'sha App\Livewire\PaymentModal orqali (komissiya, xizmat
//    taqsimoti, to'lov logi, chek — hammasi bir xil ishlaydi).
//  - Chiqim = expenses (eski Buxgalteriyadagi "Xarajatlar" bilan bir jadval).
class YangiBux extends Page
{
    use WithFileUploads;

    protected static string  $view            = 'filament.pages.yangi-bux';
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Yangi bux';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 13;
    protected static ?string $title           = 'Yangi bux';
    protected static ?string $slug            = 'yangi-bux';
    protected ?string $heading    = '';
    protected ?string $subheading = '';

    #[Url]
    public string $tab = 'asosiy';
    public ?int   $ybYear  = null;
    public ?int   $ybMonth = null;
    // ── Kirim-chiqim tabidagi filtrlar ──
    public string  $opFilter  = 'chiqim'; // all | kirim | chiqim — sukut bo'yicha faqat chiqim
    public ?string $opFrom    = null;    // bo'sh = tanlangan oy to'liq
    public ?string $opTo      = null;
    public string  $opMethod  = '';      // naqd | bank | karta
    public string  $opProject = '';      // mijoz ismi / № bo'yicha
    public string  $opUser    = '';      // mas'ul (user id)
    public string  $opSearch  = '';
    public string  $opKind    = '';      // '' | xarajat | oylik

    // ── "Kirim qo'shish" — avval loyiha tanlanadi, keyin PaymentModal ochiladi ──
    public bool   $showKirimPicker = false;
    public string $kirimSearch     = '';

    // ── "Chiqim qo'shish"/tahrirlash oynasi ──
    public bool    $showChiqimModal = false;
    public ?int    $chiqimId        = null;
    public string  $chDate          = '';
    public ?int    $chProjectId     = null;
    public string  $chProjectSearch = '';
    public string  $chComment       = '';
    public string  $chAmount        = '';
    public ?int    $chAccountId     = null;
    public ?int    $chResponsibleId = null;
    public string  $chNote          = '';
    public $chFile = null;
    public ?string $chExistingFile  = null;
    public string  $chKind          = '';   // majburiy: 'xarajat' | 'oylik' (oylik/avans)
    public string  $chSalaryMonth   = '';   // oylik bo'lsa — qaysi oy uchun (Y-m)
    public ?int    $chRecurringId   = null; // doimiy to'lov (arenda, svet...) orqali ochilgan bo'lsa

    // Doimiy to'lov shabloni (qo'shish / tahrirlash)
    public bool    $showRecModal = false;
    public ?int    $recId        = null;
    public string  $recName      = '';
    public string  $recAmount    = '';
    public bool    $recFixed     = true;
    public ?int    $recAccountId = null;
    public ?string $recDueDay    = null;

    // ── Oylik maosh tabi ──
    public string $omRole     = '';   // bo'lim = tizim roli
    public string $omPosition = '';   // lavozim
    public string $omStatus   = '';   // tolangan | qisman | tolanmagan
    public string $omSearch   = '';

    public bool   $showPayModal = false;
    public ?int   $payUserId    = null;
    public string $payAmount    = '';
    public string $payDate      = '';
    public string $payNote      = '';
    public ?int   $payEditId    = null;
    public float  $payRemaining = 0;
    public ?int   $payAccountId = null;   // oylik qaysi hisobdan berildi

    // ── Xodimga xarajat kartasi ochish ──
    public bool   $showNewCardModal = false;
    public ?int   $ncUserId = null;
    public string $ncName   = '';
    public string $ncNumber = '';


    public ?int $historyUserId = null;

    public const TABS = [
        'asosiy'    => "Asosiy ko'rinish",
        'kirim'     => 'Kirim-chiqim',
        'qarzlar'   => 'Mijozlar qarzlari',
        'oylik'     => 'Oylik maosh',
        'hisobotlar'=> 'Hisobotlar',
        // 'hujjatlar' => 'Hujjatlar',  // keyingi bosqichda
    ];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $this->ybYear  ??= (int) now()->year;
        $this->ybMonth ??= (int) now()->month;
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, self::TABS)) $this->tab = $tab;
    }

    public function ybChangeMonth(int $delta): void
    {
        $d = Carbon::create($this->ybYear, $this->ybMonth, 1)->addMonths($delta);
        $this->ybYear  = (int) $d->year;
        $this->ybMonth = (int) $d->month;
        $this->opFrom = $this->opTo = null;
    }

    // Grafikdagi oy ustuni bosilganda — o'sha oyga o'tish
    public function ybSetMonth(int $month): void
    {
        if ($month >= 1 && $month <= 12) $this->ybMonth = $month;
        $this->opFrom = $this->opTo = null;
    }

    public function opResetFilters(): void
    {
        $this->opFilter = 'chiqim';
        $this->opFrom = $this->opTo = null;
        $this->opMethod = $this->opProject = $this->opUser = $this->opSearch = $this->opKind = '';
    }

    // PaymentModal (to'lov qo'shildi/tahrirlandi/o'chirildi) — sahifani yangilash
    #[On('kb-payment-saved')]
    public function onPaymentSaved(): void {}

    // ── Kirim qo'shish ──────────────────────────────────────────────────────
    public function openKirim(): void
    {
        $this->kirimSearch = '';
        $this->showKirimPicker = true;
    }

    public function pickKirimProject(int $projectId): void
    {
        $this->showKirimPicker = false;
        $this->dispatch('kb-open-payment', id: $projectId);
    }

    public function editKirim(int $paymentId): void
    {
        $this->dispatch('kb-edit-payment', id: $paymentId);
    }

    // ── Chiqim qo'shish / tahrirlash ────────────────────────────────────────
    public function openChiqim(?int $id = null): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->resetValidation();
        $this->chiqimId = null;
        $this->chFile = null;
        $this->chProjectSearch = '';

        if ($id) {
            $e = Expense::find($id);
            // Oylikdan avtomatik yozilgan qator — Oylik hisobot orqali boshqariladi
            if (!$e || $e->is_auto) return;
            $this->chiqimId        = $e->id;
            $this->chDate          = $e->expense_date->format('Y-m-d');
            $this->chProjectId     = $e->project_id;
            $this->chComment       = (string) $e->comment;
            $this->chAmount        = (string) (float) $e->amount;
            $this->chAccountId     = $e->account_id;
            $this->chResponsibleId = $e->responsible_id;
            $this->chNote          = (string) $e->note;
            $this->chExistingFile  = $e->attachment;
            $this->chKind          = $e->kind;
            $this->chSalaryMonth   = $e->month ?: $e->expense_date->format('Y-m');
            $this->chRecurringId   = $e->recurring_expense_id;
        } else {
            $isCur = $this->ybYear === (int) now()->year && $this->ybMonth === (int) now()->month;
            $this->chDate          = $isCur ? now()->format('Y-m-d') : Carbon::create($this->ybYear, $this->ybMonth, 1)->format('Y-m-d');
            $this->chProjectId     = null;
            $this->chComment       = '';
            $this->chAmount        = '';
            $this->chAccountId     = null;
            $this->chResponsibleId = null;   // har safar xodim tanlanishi shart
            $this->chNote          = '';
            $this->chExistingFile  = null;
            $this->chKind          = '';          // turi har safar tanlanishi shart
            $this->chSalaryMonth   = $this->ym();
            $this->chRecurringId   = null;
        }
        $this->showChiqimModal = true;
    }

    // ── Doimiy to'lovlar (arenda, svet, wi-fi...) ──────────────────────────
    // "To'lash" — oddiy Chiqim oynasi, maydonlari shablondan to'ldirilgan.
    public function payRecurring(int $id): void
    {
        $r = RecurringExpense::find($id);
        if (!$r) return;
        $this->openChiqim();
        if (!$this->showChiqimModal) return;
        $this->chKind        = Expense::KIND_XARAJAT;
        $this->chRecurringId = $r->id;
        $this->chComment     = $r->name . ' — ' . $this->monthName($this->ybYear, $this->ybMonth);
        $this->chAmount      = (float) $r->amount > 0 ? number_format((float) $r->amount, 0, '', ' ') : '';
        $this->chAccountId   = $r->account_id;
    }

    public function openRec(?int $id = null): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->resetValidation();
        $r = $id ? RecurringExpense::find($id) : null;
        $this->recId        = $r?->id;
        $this->recName      = (string) $r?->name;
        $this->recAmount    = $r && (float) $r->amount > 0 ? number_format((float) $r->amount, 0, '', ' ') : '';
        $this->recFixed     = $r ? $r->is_fixed : true;
        $this->recAccountId = $r?->account_id;
        $this->recDueDay    = $r?->due_day ? (string) $r->due_day : null;
        $this->showRecModal = true;
    }

    public function saveRec(): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->recAmount = str_replace([' ', ','], ['', '.'], $this->recAmount);
        if ($this->recDueDay === '') $this->recDueDay = null;
        $this->validate([
            'recName'      => 'required|string|max:100',
            'recAmount'    => 'required|numeric|min:0',
            'recAccountId' => 'nullable|exists:financial_accounts,id,user_id,NULL',
            'recDueDay'    => 'nullable|integer|min:1|max:31',
        ], [
            'recName.required'   => 'Nomini kiriting (masalan: Arenda)',
            'recAmount.required' => 'Summani kiriting',
        ]);
        $data = [
            'name'       => trim($this->recName),
            'amount'     => (float) $this->recAmount,
            'is_fixed'   => $this->recFixed,
            'account_id' => $this->recAccountId,
            'due_day'    => $this->recDueDay ? (int) $this->recDueDay : null,
        ];
        if ($this->recId && ($r = RecurringExpense::find($this->recId))) {
            $r->update($data);
            Notification::make()->title("Doimiy to'lov yangilandi")->success()->send();
        } else {
            RecurringExpense::create($data + ['sort_order' => (int) RecurringExpense::max('sort_order') + 1]);
            Notification::make()->title("Doimiy to'lov qo'shildi")->success()->send();
        }
        $this->showRecModal = false;
    }

    // Ro'yxatdan olib tashlash — oldin yozilgan chiqimlar o'z joyida qoladi.
    public function deleteRec(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;
        RecurringExpense::whereKey($id)->update(['is_active' => false]);
        $this->showRecModal = false;
        Notification::make()->title("Doimiy to'lov ro'yxatdan olib tashlandi")->body("Oldin to'langan chiqimlar o'z joyida qoladi.")->warning()->send();
    }

    private function monthName(int $year, int $month): string
    {
        return ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'][$month - 1] . ' ' . $year;
    }

    /**
     * Har bir hisobning TANLANGAN OY puli: shu oy loyihalaridan shu hisobga
     * tushgan kirim (Yangi bux qoidasi) + shu oy o'tkazmalari kelgan − ketgan
     * − shu oyga yozilgan chiqimlar. Chiqim qaysi oy ochiq turgan bo'lsa,
     * o'sha oy pulidan yechiladi.
     */
    private function monthAccountBalances(int $year, int $month): array
    {
        $ym = sprintf('%04d-%02d', $year, $month);
        $in = $this->paymentsOfMonth($year, $month)->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) s')->groupBy('account_id')->pluck('s', 'account_id');
        $out = Expense::where($this->expenseScope($year, $month))->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) s')->groupBy('account_id')->pluck('s', 'account_id');
        $trScope = fn ($q) => $q->where('month', $ym)
            ->orWhere(fn ($q2) => $q2->whereNull('month')->whereYear('transfer_date', $year)->whereMonth('transfer_date', $month));
        $trIn  = \App\Models\AccountTransfer::where($trScope)->selectRaw('to_account_id a, SUM(amount) s')->groupBy('to_account_id')->pluck('s', 'a');
        $trOut = \App\Models\AccountTransfer::where($trScope)->selectRaw('from_account_id a, SUM(amount) s')->groupBy('from_account_id')->pluck('s', 'a');

        $res = [];
        foreach ($in->keys()->merge($out->keys())->merge($trIn->keys())->merge($trOut->keys())->unique() as $id) {
            $res[$id] = (float) ($in[$id] ?? 0) + (float) ($trIn[$id] ?? 0) - (float) ($out[$id] ?? 0) - (float) ($trOut[$id] ?? 0);
        }
        return $res;
    }

    /** Tanlangan oy uchun doimiy to'lovlar holati */
    private function recurringStatus(int $year, int $month): array
    {
        $paid = Expense::whereNotNull('recurring_expense_id')
            ->whereYear('expense_date', $year)->whereMonth('expense_date', $month)
            ->orderBy('expense_date')->get(['id', 'recurring_expense_id', 'amount', 'expense_date'])
            ->groupBy('recurring_expense_id');

        $today    = now()->startOfDay();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();
        $rows = RecurringExpense::where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $paid->keys()))
            ->orderBy('sort_order')->orderBy('id')->get()
            ->map(function (RecurringExpense $r) use ($paid, $year, $month, $today, $monthEnd) {
                $list   = $paid->get($r->id, collect());
                $isPaid = $list->isNotEmpty();
                $due    = $r->due_day ? Carbon::create($year, $month, min($r->due_day, $monthEnd->day)) : null;
                return [
                    'r'         => $r,
                    'paid'      => $isPaid,
                    'paidSum'   => (float) $list->sum('amount'),
                    'paidDate'  => $list->last()?->expense_date,
                    'expenseId' => $list->last()?->id,
                    'count'     => $list->count(),
                    'due'       => $due,
                    'overdue'   => !$isPaid && $due && $today->gt($due),
                ];
            });

        return [
            'rows'      => $rows,
            'paidCount' => $rows->where('paid', true)->count(),
            'total'     => $rows->sum(fn ($x) => $x['paid'] ? $x['paidSum'] : (float) $x['r']->amount),
            'paid'      => $rows->sum('paidSum'),
            'left'      => $rows->where('paid', false)->sum(fn ($x) => (float) $x['r']->amount),
        ];
    }

    public function closeChiqim(): void
    {
        $this->showChiqimModal = false;
        $this->chFile = null;
    }

    public function removeChiqimFile(): void
    {
        $this->chFile = null;
        $this->chExistingFile = null;
    }

    public function saveChiqim(): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->chAmount = str_replace([' ', ','], ['', '.'], $this->chAmount);
        // Yangi "Oylik / avans" — oylik tizimiga (xodim to'lovlari) ham yoziladi
        $newSalary = !$this->chiqimId && $this->chKind === Expense::KIND_OYLIK;
        // Mavjud oddiy chiqim "Oylik / avans" qilinsa — xodim to'loviga aylanadi
        $oldCh  = $this->chiqimId ? Expense::find($this->chiqimId) : null;
        $attach = $oldCh && !$oldCh->salary_payment_id && $this->chKind === Expense::KIND_OYLIK;
        $this->validate([
            'chKind'          => 'required|in:' . Expense::KIND_XARAJAT . ',' . Expense::KIND_OYLIK,
            'chSalaryMonth'   => ($newSalary || $attach) ? ['required', 'regex:/^\d{4}-\d{2}$/'] : 'nullable',
            'chDate'          => $this->chiqimId ? 'required|date' : [
                'required', 'date',
                'after_or_equal:' . Carbon::create($this->ybYear, $this->ybMonth, 1)->format('Y-m-d'),
                'before_or_equal:' . Carbon::create($this->ybYear, $this->ybMonth, 1)->endOfMonth()->format('Y-m-d'),
            ],
            'chComment'       => ($newSalary ? 'nullable' : 'required') . '|string|max:255',
            'chAmount'        => 'required|numeric|min:1',
            'chAccountId'     => 'required|exists:financial_accounts,id,user_id,NULL',
            'chResponsibleId' => 'required|exists:users,id',
            'chProjectId'     => 'nullable|exists:projects,id',
            'chFile'          => 'nullable|file|max:8192|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
        ], [
            'chKind.required'      => 'Chiqim turini tanlang: Xarajat yoki Oylik / avans',
            'chDate.after_or_equal'  => 'Sana ochiq turgan oy ichida bo\'lishi kerak — chiqim shu oy pulidan yechiladi',
            'chDate.before_or_equal' => 'Sana ochiq turgan oy ichida bo\'lishi kerak — chiqim shu oy pulidan yechiladi',
            'chSalaryMonth.required' => 'Qaysi oy uchun ekanini tanlang',
            'chComment.required'   => 'Tavsif kiriting',
            'chAmount.required'    => 'Summani kiriting',
            'chAccountId.required' => "Qaysi hisobdan to'langanini tanlang",
            'chResponsibleId.required' => 'Xarajatni kim qilganini (xodimni) tanlang',
        ]);

        $old = $this->chiqimId ? Expense::find($this->chiqimId) : null;
        if ($old?->is_auto) return;

        $attachment = $this->chExistingFile;
        if ($this->chFile) {
            $attachment = $this->chFile->store('expense-docs', 'public');
        }
        // Eski fayl almashtirilgan yoki olib tashlangan bo'lsa — o'chiramiz
        if ($old?->attachment && $old->attachment !== $attachment) {
            Storage::disk('public')->delete($old->attachment);
        }

        if ($newSalary) {
            $amount    = (float) $this->chAmount;
            [$sy, $sm] = explode('-', $this->chSalaryMonth);
            $row       = collect(EmployeePayableService::yearGrid((int) $sy))->firstWhere('user.id', $this->chResponsibleId);
            $remaining = (float) ($row['months'][(int) $sm]['remaining'] ?? 0);
            $payment = \App\Services\SalaryPaymentService::save([
                'user_id'  => $this->chResponsibleId,
                'month'    => $this->chSalaryMonth,
                'amount'   => $amount,
                'paid_at'  => $this->chDate,
                'note'     => trim($this->chComment) ?: null,
                'given_by' => auth()->id(),
            ], null, $remaining > 0 && $amount < $remaining, $this->chAccountId);
            // Qo'shimcha maydonlar (loyiha, izoh, hujjat) — bog'liq xarajat qatoriga
            Expense::where('salary_payment_id', $payment?->id)->update([
                'responsible_id' => $this->chResponsibleId,
                'project_id'     => $this->chProjectId,
                'note'           => trim($this->chNote) ?: null,
                'attachment'     => $attachment,
            ]);
            Notification::make()->title("Oylik / avans yozildi")->body("Xodim to'lovlariga (Oylik hisobot) va tanlangan hisobdan chiqimga yozildi.")->success()->send();
            $this->closeChiqim();
            return;
        }

        $data = [
            'category'       => $this->chKind,
            'expense_date'   => $this->chDate,
            'project_id'     => $this->chProjectId,
            'comment'        => trim($this->chComment),
            'amount'         => (float) $this->chAmount,
            'account_id'     => $this->chAccountId,
            'responsible_id' => $this->chResponsibleId,
            'note'           => trim($this->chNote) ?: null,
            'attachment'     => $attachment,
        ];

        if ($old) {
            $old->update($data);
            if ($attach) {
                \App\Services\SalaryPaymentService::attachExpense($old->fresh(), (int) $this->chResponsibleId, $this->chSalaryMonth);
                Notification::make()->title('Oylik / avansga aylantirildi')->body("Xodim to'lovlariga (Oylik maosh, Oylik hisobot) qo'shildi. Chiqim ikki marta hisoblanmaydi.")->success()->send();
                $this->closeChiqim();
                return;
            }
            Notification::make()->title('Chiqim yangilandi')->success()->send();
        } else {
            Expense::create($data + ['created_by' => auth()->id(), 'recurring_expense_id' => $this->chRecurringId]);
            Notification::make()->title("Chiqim qo'shildi")->success()->send();
        }
        $this->closeChiqim();
    }

    // ── Xodimga xarajat kartasi ochish ──────────────────────────────────────
    // Xodim kartasi — PUL HISOBI EMAS, faqat ko'rinish: shu oyda o'sha xodimga
    // qilingan xarajatlar (xarajatdagi "kim uchun" belgisi) shu kartada jamlanadi.
    // Belgi tizimda har bir xodim uchun yuritiladi; karta faqat kimni ekranda
    // ko'rsatishni belgilaydi.
    public function openNewCard(): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->resetValidation();
        $this->ncUserId = null;
        $this->ncName = $this->ncNumber = '';
        $this->showNewCardModal = true;
    }

    public function updatedNcUserId($value): void
    {
        $name = $value ? User::whereKey($value)->value('name') : null;
        $this->ncName = $name ? "{$name} — xarajatlar" : '';
    }

    public function saveNewCard(): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->validate([
            'ncUserId' => 'required|exists:users,id',
            'ncName'   => 'required|string|max:120',
        ], ['ncUserId.required' => 'Xodimni tanlang', 'ncName.required' => 'Karta nomini yozing']);

        if (FinancialAccount::where('user_id', $this->ncUserId)->exists()) {
            $this->addError('ncUserId', 'Bu xodimda xarajat kartasi allaqachon bor');
            return;
        }

        FinancialAccount::create([
            'type'         => 'karta',
            'name'         => trim($this->ncName),
            'card_number'  => trim($this->ncNumber) ?: null,
            'user_id'      => $this->ncUserId,
            // Eski Buxgalteriyada ham kompaniya "Jami balans"iga kirmasin va
            // ikkinchi bo'limda tursin (shaxsiy kartalar bilan bir xil qoida).
            'is_personal'  => true,
            'is_secondary' => true,
        ]);
        $this->showNewCardModal = false;
        Notification::make()->title('Xodim kartasi qo\'shildi')->success()->send();
    }

    // ── Oylik maosh ─────────────────────────────────────────────────────────
    private function ym(): string
    {
        return sprintf('%04d-%02d', $this->ybYear, $this->ybMonth);
    }

    /** Tanlangan oy uchun xodim qoldig'i (Oylik hisobotdagi yearGrid bilan bir xil). */
    private function remainingFor(int $userId): float
    {
        $row = collect(EmployeePayableService::yearGrid($this->ybYear))->firstWhere('user.id', $userId);
        return (float) ($row['months'][$this->ybMonth]['remaining'] ?? 0);
    }

    public function openPay(?int $userId = null): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->resetValidation();
        $this->payEditId    = null;
        $this->payUserId    = $userId;
        $this->payRemaining = $userId ? $this->remainingFor($userId) : 0;
        $this->payAmount    = $this->payRemaining > 0 ? number_format($this->payRemaining, 0, '.', ' ') : '';
        $this->payDate      = now()->format('Y-m-d');
        $this->payNote      = '';
        $this->payAccountId = null;   // har safar admin o'zi tanlaydi (naqd yoki karta)
        $this->showPayModal = true;
    }

    // Modalda xodim tanlanganda — qoldiqni avtomatik qo'yish
    public function updatedPayUserId($value): void
    {
        if ($this->payEditId) return;
        $this->payRemaining = $value ? $this->remainingFor((int) $value) : 0;
        $this->payAmount    = $this->payRemaining > 0 ? number_format($this->payRemaining, 0, '.', ' ') : '';
    }

    public function editPay(int $paymentId): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $p = EmployeeSalaryPayment::find($paymentId);
        if (!$p) return;
        $this->resetValidation();
        $this->payEditId    = $p->id;
        $this->payUserId    = $p->user_id;
        $this->payAmount    = number_format((float) $p->amount, 0, '.', ' ');
        $this->payDate      = $p->paid_at->format('Y-m-d');
        $this->payNote      = (string) $p->note;
        $this->payRemaining = 0;
        $this->payAccountId = Expense::where('salary_payment_id', $p->id)->value('account_id');
        $this->historyUserId = null;
        $this->showPayModal = true;
    }

    public function savePay(): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->payAmount = str_replace([' ', ','], '', $this->payAmount);
        $this->validate([
            'payUserId' => 'required|exists:users,id',
            'payAmount' => 'required|numeric|min:1',
            'payDate'   => 'required|date',
            'payAccountId' => 'required|exists:financial_accounts,id,user_id,NULL',
        ], [
            'payUserId.required'    => 'Xodimni tanlang',
            'payAmount.required'    => 'Summani kiriting',
            'payAccountId.required' => 'Qaysi hisobdan berilganini tanlang',
        ]);

        $amount = (float) $this->payAmount;
        $month  = $this->payEditId ? (EmployeeSalaryPayment::find($this->payEditId)?->month ?? $this->ym()) : $this->ym();
        \App\Services\SalaryPaymentService::save([
            'user_id'  => $this->payUserId,
            'month'    => $month,
            'amount'   => $amount,
            'paid_at'  => $this->payDate,
            'note'     => trim($this->payNote) ?: null,
            'given_by' => auth()->id(),
        ], $this->payEditId, $this->payRemaining > 0 && $amount < $this->payRemaining, $this->payAccountId);

        $this->showPayModal = false;
        Notification::make()->title('Maosh saqlandi')->body("Oylik hisobotga va tanlangan hisobdan xarajat sifatida yozildi.")->success()->send();
    }

    public function deletePay(int $paymentId): void
    {
        if (!auth()->user()?->isAdmin()) return;
        \App\Services\SalaryPaymentService::delete($paymentId);
        Notification::make()->title("To'lov o'chirildi")->warning()->send();
    }

    public function exportSalary()
    {
        if (!auth()->user()?->isAdmin()) return null;
        $rows = $this->salaryRows(EmployeePayableService::yearGrid($this->ybYear));
        $data = $rows->map(fn ($r, $i) => [
            $i + 1, $r['user']->name, $r['position'], $r['role'], $r['base'], $r['extra'], $r['total'], $r['paid'],
            $r['remaining'], $r['statusLabel'], $r['paidAt']?->format('d.m.Y') ?? '',
        ])->all();
        $data[] = ['', 'Jami', '', '', $rows->sum('base'), $rows->sum('extra'), $rows->sum('total'), $rows->sum('paid'), $rows->sum('remaining'), '', ''];

        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
            public function headings(): array
            {
                return ['#', 'Xodim', 'Lavozim', "Bo'lim", 'Asosiy maosh', "Qo'shimcha (komissiya)", 'Jami summa', "To'langan", 'Qoldiq', "To'lov holati", "To'lov sanasi"];
            }
        };

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'oylik-maosh-' . $this->ym() . '.xlsx');
    }

    public const ROLE_LABELS = ['admin' => 'Rahbariyat', 'menejer' => 'Menejerlar', 'bajaruvchi' => 'Ijrochilar', 'hisobchi' => 'Buxgalteriya'];

    /** Tanlangan oy bo'yicha har bir xodim qatori (filtrlarsiz). */
    private function salaryRows(array $grid): Collection
    {
        $ym = $this->ym();
        $pays = EmployeeSalaryPayment::where('month', $ym)->get()->groupBy('user_id');

        return collect($grid)->map(function ($row) use ($pays) {
            $u    = $row['user'];
            $cell = $row['months'][$this->ybMonth] ?? ['calc' => 0, 'paid' => 0, 'remaining' => 0];
            $base = (float) ($u->base_salary ?? 0);
            $total = (float) $cell['calc'];
            $paid  = (float) $cell['paid'];
            $status = $total <= 0 && $paid <= 0 ? 'yoq' : ($paid >= $total ? 'tolangan' : ($paid > 0 ? 'qisman' : 'tolanmagan'));
            return [
                'user'        => $u,
                'position'    => $u->position ?: '—',
                'role'        => self::ROLE_LABELS[$u->role] ?? ucfirst((string) $u->role),
                'roleKey'     => $u->role,
                'base'        => $base,
                'extra'       => max(0, $total - $base),
                'total'       => $total,
                'paid'        => $paid,
                'remaining'   => max(0, $total - $paid),
                'status'      => $status,
                'statusLabel' => ['tolangan' => "To'langan", 'qisman' => 'Qisman', 'tolanmagan' => "To'lanmagan", 'yoq' => 'Hisoblanmagan'][$status],
                'paidAt'      => $pays->get($u->id)?->max('paid_at'),
                'payCount'    => $pays->get($u->id)?->count() ?? 0,
            ];
        })->values();
    }

    public function deleteChiqim(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $e = Expense::find($id);
        if (!$e || $e->is_auto) return;
        if ($e->attachment) Storage::disk('public')->delete($e->attachment);
        $e->delete();
        Notification::make()->title("Chiqim o'chirildi")->warning()->send();
    }

    // ── Yordamchi so'rovlar ─────────────────────────────────────────────────

    // Tushum — loyiha OCHILGAN oy (projects.created_at) bo'yicha, asosiy oynadagi
    // statistika bilan bir xil: iyul loyihasiga sentabrda to'lov kelsa ham u
    // iyul oyida ko'rinadi. Har oy faqat o'z loyihalarining pulini ko'rsatadi.
    private function paymentsOfMonth(int $year, int $month)
    {
        return Payment::whereHas('project', fn ($q) => $q->whereYear('created_at', $year)->whereMonth('created_at', $month));
    }

    private function incomeFor(int $year, int $month): float
    {
        return (float) $this->paymentsOfMonth($year, $month)->sum('amount');
    }

    // Xarajat — Buxgalteriya bilan bir xil qoida: oylikdan avtomatik yozilgan
    // qatorlar `month` bo'yicha, qo'lda kiritilganlari expense_date bo'yicha.
    private function expenseScope(int $year, int $month): \Closure
    {
        $ym = sprintf('%04d-%02d', $year, $month);
        return function ($q) use ($year, $month, $ym) {
            $q->where('month', $ym)
                ->orWhere(function ($q2) use ($year, $month) {
                    $q2->whereNull('month')->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
                });
        };
    }

    private function expenseFor(int $year, int $month): float
    {
        return (float) Expense::where($this->expenseScope($year, $month))->sum('amount');
    }

    private static function pct(float $cur, float $prev): ?float
    {
        if ($prev == 0.0) return null;
        return round(($cur - $prev) / abs($prev) * 100, 1);
    }

    /**
     * Operatsiyalar (Kirim = to'lov, Chiqim = xarajat), sana bo'yicha kamayish tartibida.
     * $from/$to berilmasa — tanlangan oy (xarajatlar Buxgalteriyadagi oy qoidasi bilan).
     */
    private function operations(int $year, int $month, array $f = []): Collection
    {
        $methods = Payment::methodOptions();
        $from = $f['from'] ?? null;
        $to   = $f['to'] ?? null;
        $type = $f['type'] ?? 'all';
        $projectFilter = function ($x, string $pv) {
            $num = ltrim($pv, '№#');
            $x->where('owner_name', 'like', "%$pv%")->orWhere('seq_no', ctype_digit($num) ? (int) $num : -1);
        };

        $kirim = collect();
        if ($type !== 'chiqim' && empty($f['kind'])) {
            if ($from || $to) {
                $q = Payment::with(['project', 'account', 'createdBy']);
                if ($from) $q->whereDate('payment_date', '>=', $from);
                if ($to)   $q->whereDate('payment_date', '<=', $to);
            } else {
                $q = $this->paymentsOfMonth($year, $month)->with(['project', 'account', 'createdBy']);
            }
            if (!empty($f['method'])) $q->where('method', $f['method']);
            if (!empty($f['user']))   $q->where('created_by', $f['user']);
            if (!empty($f['project'])) $q->whereHas('project', fn ($pq) => $projectFilter($pq, $f['project']));
            $kirim = $q->get()->map(fn (Payment $p) => [
                'id'      => $p->id,
                'date'    => $p->payment_date,
                'sort'    => $p->payment_date?->format('Y-m-d') . sprintf('%010d', $p->id),
                'type'    => 'kirim',
                'who'     => $p->project?->owner_name ?: ('Loyiha #' . ($p->project?->seq_no ?? $p->project_id)),
                'who_sub' => $p->project ? ('№' . $p->project->seq_no . ' · ' . ($p->project->address ?: $p->project->title)) : null,
                'desc'    => $p->note ?: "Loyiha to'lovi",
                'note'    => null,
                'amount'  => (float) $p->amount,
                'method'  => $p->account?->name ?: ($methods[$p->method] ?? $p->method),
                'mtype'   => $p->account?->type ?? $p->method,
                'user'    => $p->createdBy?->name,
                'locked'  => false,
                'file'    => null,
            ]);
        }

        $chiqim = collect();
        if ($type !== 'kirim') {
            $q = Expense::with(['account', 'createdBy', 'user', 'project', 'responsible']);
            if ($from || $to) {
                if ($from) $q->whereDate('expense_date', '>=', $from);
                if ($to)   $q->whereDate('expense_date', '<=', $to);
            } else {
                $q->where($this->expenseScope($year, $month));
            }
            if (!empty($f['method'])) $q->whereHas('account', fn ($aq) => $aq->where('type', $f['method']));
            if (!empty($f['kind']))   $q->ofKind($f['kind']);
            if (!empty($f['user'])) {
                $q->where(fn ($uq) => $uq->where('responsible_id', $f['user'])
                    ->orWhere(fn ($u2) => $u2->whereNull('responsible_id')->where('created_by', $f['user'])));
            }
            if (!empty($f['project'])) {
                $pv = $f['project'];
                $q->where(fn ($pq) => $pq->whereHas('project', fn ($x) => $projectFilter($x, $pv))
                    ->orWhereHas('user', fn ($x) => $x->where('name', 'like', "%$pv%")));
            }
            $chiqim = $q->get()->map(fn (Expense $e) => [
                'id'      => $e->id,
                'date'    => $e->expense_date,
                'sort'    => $e->expense_date?->format('Y-m-d') . sprintf('%010d', $e->id),
                'type'    => 'chiqim',
                'who'     => $e->project?->owner_name ?: ($e->user?->name ?: 'Xarajat'),
                'who_sub' => $e->project ? ('№' . $e->project->seq_no . ' · ' . ($e->project->address ?: $e->project->title)) : ($e->user ? 'Xodim oyligi' : null),
                'desc'    => $e->comment ?: '—',
                'note'    => $e->note,
                'amount'  => (float) $e->amount,
                'method'  => $e->account?->name ?: (FinancialAccount::typeOptions()[$e->account?->type] ?? '—'),
                'mtype'   => $e->account?->type,
                'user'    => $e->responsible?->name ?? $e->createdBy?->name,
                'locked'  => $e->is_auto,
                'kind'    => $e->kind,
                'file'    => $e->attachment,
            ]);
        }

        $rows = $kirim->concat($chiqim);
        if (!empty($f['search'])) {
            $needle = mb_strtolower($f['search']);
            $digits = preg_replace('/\D/', '', $f['search']);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['who'] . ' ' . $r['who_sub'] . ' ' . $r['desc'] . ' ' . $r['note'] . ' ' . $r['method']), $needle)
                || ($digits !== '' && str_contains((string) (int) $r['amount'], $digits)));
        }

        return $rows->sortByDesc('sort')->values();
    }

    /** Qarzdor loyihalar (bekor qilinmagan, to'xtatilmagan), eng katta qarzdan boshlab. */
    private function clientDebts(): Collection
    {
        return Project::excludePaused()
            ->where('status', '!=', 'bekor_qilingan')
            ->whereColumn('total_price', '>', 'paid_amount')
            ->get(['id', 'seq_no', 'owner_name', 'title', 'address', 'total_price', 'paid_amount', 'created_at', 'status'])
            ->map(fn ($p) => [
                'project' => $p,
                'debt'    => max(0, (float) $p->total_price - (float) $p->paid_amount),
            ])
            ->filter(fn ($r) => $r['debt'] > 0)
            ->sortByDesc('debt')
            ->values();
    }

    public function getViewData(): array
    {
        if (!array_key_exists($this->tab, self::TABS)) $this->tab = 'asosiy';

        $year  = $this->ybYear;
        $month = $this->ybMonth;
        $prev  = Carbon::create($year, $month, 1)->subMonth();

        $income  = $this->incomeFor($year, $month);
        $expense = $this->expenseFor($year, $month);
        $profit  = $income - $expense;
        $pIncome  = $this->incomeFor($prev->year, $prev->month);
        $pExpense = $this->expenseFor($prev->year, $prev->month);
        $pProfit  = $pIncome - $pExpense;

        $debts = $this->clientDebts();

        // Xodimlarga to'lanadigan — Oylik hisobotdagi "To'lanishi kerak" bilan
        // bir xil manba (yearGrid), tanlangan oygacha bo'lgan qoldiqlar.
        $grid = EmployeePayableService::yearGrid($year);
        $staffOwed = collect($grid)->map(function ($row) use ($month) {
            $rem = 0.0;
            foreach ($row['months'] as $m => $cell) {
                if ($m <= $month) $rem += max(0, (float) $cell['remaining']);
            }
            return ['user' => $row['user'], 'owed' => $rem, 'month' => $row['months'][$month] ?? null];
        })->values();
        $staffOwedPositive = $staffOwed->filter(fn ($r) => $r['owed'] > 0)->sortByDesc('owed')->values();

        // ── Oylik maosh tabi ──
        $salary = null;
        if ($this->tab === 'oylik') {
            $all = $this->salaryRows($grid);
            // Hisoblanmagan (oklad ham, komissiya ham yo'q) xodimlar KPI'ga kirmaydi
            $counted = $all->where('status', '!=', 'yoq');
            $fund    = (float) $counted->sum('total');
            $paidSum = (float) $counted->sum(fn ($r) => min($r['paid'], $r['total']));

            $rows = $all;
            if ($this->omRole !== '')     $rows = $rows->where('roleKey', $this->omRole);
            if ($this->omPosition !== '') $rows = $rows->where('position', $this->omPosition);
            if ($this->omStatus !== '')   $rows = $rows->where('status', $this->omStatus);
            if (trim($this->omSearch) !== '') {
                $n = mb_strtolower(trim($this->omSearch));
                $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['user']->name . ' ' . $r['position']), $n));
            }

            // So'nggi 6 oy (yil chegarasidan o'tsa — o'tgan yil gridi ham)
            $grids = [$year => $grid];
            $six = [];
            $uzMonths = ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'];
            for ($k = 5; $k >= 0; $k--) {
                $d = Carbon::create($year, $month, 1)->subMonths($k);
                $grids[$d->year] ??= EmployeePayableService::yearGrid($d->year);
                $calc = $pd = 0.0;
                foreach ($grids[$d->year] as $r) {
                    $c = $r['months'][$d->month];
                    $calc += $c['calc'];
                    $pd   += min($c['paid'], $c['calc']);
                }
                $six[] = ['label' => $uzMonths[$d->month - 1], 'paid' => $pd, 'unpaid' => max(0, $calc - $pd), 'cur' => $k === 0];
            }

            // Maosh fondi lavozimlar bo'yicha
            $byPos = $counted->groupBy('position')->map(fn ($g) => (float) $g->sum('total'))->filter()->sortDesc();
            $posDonut = [];
            $i = 0;
            foreach ($byPos as $pos => $sum) {
                $posDonut[] = ['label' => $pos, 'value' => $sum, 'pct' => $fund > 0 ? $sum / $fund * 100 : 0, 'color' => ['#3b82f6', '#22c55e', '#f59e0b', '#a78bfa', '#ec4899', '#14b8a6'][$i++ % 6]];
            }

            $lastPay = EmployeeSalaryPayment::with('user')->orderByDesc('paid_at')->orderByDesc('id')->first();

            $salary = [
                'rows'       => $rows->values(),
                'staffCount' => $all->count(),
                'roleCount'  => $all->pluck('roleKey')->unique()->count(),
                'fund'       => $fund,
                'paid'       => $paidSum,
                'unpaid'     => max(0, $fund - $paidSum),
                'paidPct'    => $fund > 0 ? round($paidSum / $fund * 100) : 0,
                'paidPeople' => $counted->where('status', 'tolangan')->count(),
                'unpaidPeople' => $counted->whereIn('status', ['qisman', 'tolanmagan'])->count(),
                'countedPeople' => $counted->count(),
                'six'        => $six,
                'sixMax'     => max(1, ...array_map(fn ($s) => $s['paid'] + $s['unpaid'], $six)),
                'posDonut'   => $posDonut,
                'lastPay'    => $lastPay,
                'positions'  => $all->pluck('position')->unique()->sort()->values(),
                'history'    => $this->historyUserId
                    ? EmployeeSalaryPayment::with('giver')->where('user_id', $this->historyUserId)->where('month', $this->ym())->orderByDesc('paid_at')->get()
                    : collect(),
                'historyUser'=> $this->historyUserId ? User::find($this->historyUserId) : null,
            ];
        }

        // 12 oylik grafik (tanlangan yil)
        $incomeByMonth = array_fill(1, 12, 0.0);
        Payment::with('project:id,created_at')
            ->whereHas('project', fn ($q) => $q->whereYear('created_at', $year))
            ->get(['id', 'project_id', 'amount'])
            ->each(function ($p) use (&$incomeByMonth) { $incomeByMonth[(int) $p->project->created_at->month] += (float) $p->amount; });
        $expenseByMonth = array_fill(1, 12, 0.0);
        $salaryByMonth  = array_fill(1, 12, 0.0);   // shundan oylik / avans
        Expense::where(function ($q) use ($year) {
                $q->where('month', 'like', $year . '-%')
                  ->orWhere(fn ($q2) => $q2->whereNull('month')->whereYear('expense_date', $year));
            })->get(['amount', 'month', 'expense_date', 'category', 'user_id'])
            ->each(function ($e) use (&$expenseByMonth, &$salaryByMonth) {
                $m = $e->month ? (int) substr($e->month, 5, 2) : (int) $e->expense_date->month;
                $expenseByMonth[$m] += (float) $e->amount;
                if ($e->kind === Expense::KIND_OYLIK) $salaryByMonth[$m] += (float) $e->amount;
            });
        $chart = [];
        $monthNames = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = [
                'label'   => $monthNames[$m - 1],
                'm'       => $m,
                'income'  => $incomeByMonth[$m],
                'expense' => $expenseByMonth[$m],
                'salary'  => $salaryByMonth[$m],
                'other'   => $expenseByMonth[$m] - $salaryByMonth[$m],
                'profit'  => $incomeByMonth[$m] - $expenseByMonth[$m],
            ];
        }
        $chartMax = max(1, ...array_map(fn ($c) => max($c['income'], $c['expense'], $c['profit']), $chart));

        // Shu oyda REAL tushgan to'lovlar (payment_date) — qaysi oyda ochilgan
        // loyihaga tegishli ekaniga qarab bo'lingan (masalan: Iyun 20M, Iyul 30M...).
        $fullMonths = ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'];
        $byProjMonth = [];
        Payment::with('project:id,created_at')
            ->whereYear('payment_date', $year)->whereMonth('payment_date', $month)
            ->get(['id', 'project_id', 'amount'])
            ->each(function ($p) use (&$byProjMonth) {
                $key = $p->project?->created_at?->format('Y-m') ?? '_nomalum';
                $byProjMonth[$key] = ($byProjMonth[$key] ?? 0) + (float) $p->amount;
            });
        ksort($byProjMonth);
        $pmColors = ['#3b82f6', '#22c55e', '#f59e0b', '#a78bfa', '#ef4444', '#14b8a6', '#ec4899', '#84cc16', '#f97316', '#06b6d4', '#8b5cf6', '#64748b'];
        $pmTotal = array_sum($byProjMonth);
        $pmDonut = [];
        $i = 0;
        foreach ($byProjMonth as $key => $val) {
            if ($key === '_nomalum') {
                $label = "Loyihasi o'chirilgan";
            } else {
                [$ky, $km] = array_map('intval', explode('-', $key));
                $label = $fullMonths[$km - 1] . ($ky !== $year ? ' ' . $ky : '') . ' loyihalari';
            }
            $pmDonut[] = [
                'label' => $label,
                'value' => $val,
                'pct'   => $pmTotal > 0 ? $val / $pmTotal * 100 : 0,
                'color' => $pmColors[$i++ % count($pmColors)],
            ];
        }

        // Hisoblar qoldig'i — barcha vaqt bo'yicha (shaxsiy hisoblar jamiga kirmaydi).
        $allAccountsSum = FinancialAccount::with('owner:id,name')
            ->withSum('payments as payments_sum_amount', 'amount')
            ->withSum('expenses as expenses_sum_amount', 'amount')
            ->withSum('transfersIn as transfers_in_sum_amount', 'amount')
            ->withSum('transfersOut as transfers_out_sum_amount', 'amount')
            ->orderBy('type')->orderBy('name')
            ->get();
        $accountBalances = $allAccountsSum->mapWithKeys(fn ($a) => [$a->id => (float) $a->balance]);
        $monthAccountBalances = $this->monthAccountBalances($year, $month);
        $accounts = $allAccountsSum->where('is_personal', false)->whereNull('user_id');

        // Xodim kartalari (egasi belgilangan hisoblar) — pul hisobi emas: shu oyda
        // o'sha xodimga qilingan xarajatlar (eski Buxgalteriya bilan bir xil servis).
        $personCardAccs = $allAccountsSum->filter(fn ($a) => $a->user_id)->values();
        $personSpend = \App\Services\PersonExpenseService::forMonth($year, $month, $personCardAccs->pluck('user_id')->all());
        $staffCards = $personCardAccs->map(fn ($a) => [
            'account' => $a,
            'spent'   => $personSpend[$a->user_id]['total'] ?? 0.0,
            'count'   => $personSpend[$a->user_id]['count'] ?? 0,
            'by'      => $personSpend[$a->user_id]['by'] ?? [],
        ])->values();
        $balances = [
            ['label' => 'Kassa (naqd)',      'icon' => '💵', 'value' => (float) $accounts->where('type', 'naqd')->sum(fn ($a) => $a->balance)],
            ['label' => 'Bank hisobraqami',  'icon' => '🏦', 'value' => (float) $accounts->where('type', 'bank')->sum(fn ($a) => $a->balance)],
            ['label' => 'Plastik karta',     'icon' => '💳', 'value' => (float) $accounts->where('type', 'karta')->sum(fn ($a) => $a->balance)],
        ];
        $balanceTotal = array_sum(array_column($balances, 'value'));

        $ops = $this->operations($year, $month);
        $opsFiltered = $this->tab === 'kirim' ? $this->operations($year, $month, [
            'type' => $this->opFilter, 'from' => $this->opFrom, 'to' => $this->opTo, 'method' => $this->opMethod, 'kind' => $this->opKind,
            'user' => $this->opUser, 'project' => trim($this->opProject), 'search' => trim($this->opSearch),
        ]) : collect();

        $projectResults = function (string $term) {
            $term = trim($term);
            $q = Project::query()->select(['id', 'seq_no', 'owner_name', 'address', 'title', 'total_price', 'paid_amount'])
                ->where('status', '!=', 'bekor_qilingan');
            if ($term !== '') {
                $num = ltrim($term, '№#');
                $q->where(fn ($x) => $x->where('owner_name', 'like', "%$term%")->orWhere('address', 'like', "%$term%")
                    ->orWhere('phones', 'like', "%$term%")->orWhere('seq_no', ctype_digit($num) ? (int) $num : -1));
            }
            return $q->orderByDesc('id')->limit(12)->get();
        };

        // Hisobotlar tabi — yil bo'yicha jadval
        $yearIncome  = array_sum($incomeByMonth);
        $yearExpense = array_sum($expenseByMonth);

        return [
            'tabs'         => self::TABS,
            'monthLabel'   => ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'][$month - 1] . ' ' . $year,
            'income'       => $income,
            'expense'      => $expense,
            'profit'       => $profit,
            'incomePct'    => self::pct($income, $pIncome),
            'expensePct'   => self::pct($expense, $pExpense),
            'salarySpent'  => $salaryByMonth[$month] ?? 0.0,
            'otherSpent'   => $expense - ($salaryByMonth[$month] ?? 0.0),
            'yearSalary'   => array_sum($salaryByMonth),
            'profitPct'    => self::pct($profit, $pProfit),
            'debtTotal'    => (float) $debts->sum('debt'),
            'debtClients'  => $debts->count(),
            'debts'        => $debts,
            'staffOwed'    => $staffOwed,
            'staffOwedPos' => $staffOwedPositive,
            'staffOwedTotal' => (float) $staffOwedPositive->sum('owed'),
            'chart'        => $chart,
            'chartMax'     => $chartMax,
            'pmDonut'      => $pmDonut,
            'pmTotal'      => $pmTotal,
            'balances'     => $balances,
            'staffCards'   => $staffCards,
            'accountBalances' => $accountBalances,
            'monthAccountBalances' => $monthAccountBalances,
            'mainAccounts' => $allAccountsSum->where('is_personal', false)->whereNull('user_id')->values(),
            'balanceTotal' => $balanceTotal,
            'ops'          => $ops,
            'opsFiltered'  => $opsFiltered,
            'kirimProjects'=> $this->showKirimPicker ? $projectResults($this->kirimSearch) : collect(),
            'chProjects'   => $this->showChiqimModal && trim($this->chProjectSearch) !== '' ? $projectResults($this->chProjectSearch) : collect(),
            'chProject'    => $this->chProjectId ? Project::find($this->chProjectId, ['id', 'seq_no', 'owner_name', 'address']) : null,
            'allAccounts'  => $allAccountsSum->values(),
            'staffUsers'   => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'recurring'    => $this->tab === 'kirim' ? $this->recurringStatus($year, $month) : null,
            'chRecurring'  => $this->showChiqimModal && $this->chRecurringId ? RecurringExpense::find($this->chRecurringId) : null,
            'methodOptions'=> Payment::methodOptions(),
            'salary'       => $salary,
            'payStaff'     => $this->showPayModal ? User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'position']) : collect(),
            'yearIncome'   => $yearIncome,
            'yearExpense'  => $yearExpense,
            'salaryPayments' => EmployeeSalaryPayment::with(['user', 'giver'])
                ->where('month', sprintf('%04d-%02d', $year, $month))
                ->orderByDesc('paid_at')->get(),
        ];
    }
}
