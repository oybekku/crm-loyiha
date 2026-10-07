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
    public string  $opSort    = 'new';   // new — oxirgi qo'shilgan/o'zgargan birinchi | date — sana bo'yicha
    public ?string $opJustSaved = null;  // hozirgina saqlangan qator ("kirim:ID" | "chiqim:ID") — ajratib ko'rsatiladi

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
    public string  $chSalaryType    = '';   // oylik bo'lsa — ishbay | mamuriy (xodimdan avtomatik)
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
    public string $omRole     = '';   // bo'lim = maosh turi: ishbay | mamuriy
    public string $omPosition = '';   // lavozim
    public string $omStatus   = '';   // tolangan | qisman | tolanmagan
    public string $omSearch   = '';

    public bool   $showPayModal = false;
    public ?int   $payUserId    = null;
    public string $payAmount    = '';
    public string $payDate      = '';
    public string $payNote      = '';
    public ?int   $payEditId    = null;
    public float  $payRemaining = 0;      // mijoz to'lagani bo'yicha hozir to'lash mumkin
    public float  $payFullRemaining = 0;  // hisoblangan (barcha ishlar) bo'yicha qoldiq
    public ?int   $payAccountId = null;   // oylik qaysi hisobdan berildi
    public string $paySalaryType = EmployeeSalaryPayment::TYPE_ISHBAY;

    // ── Xodimga xarajat kartasi ochish ──
    public bool   $showNewCardModal = false;
    public ?int   $ncUserId = null;
    public string $ncName   = '';
    public string $ncNumber = '';


    public ?int $historyUserId = null;

    // ── Hisobotlar: katak bosilganda tafsilot (qaysi oy, xarajat|oylik) ──
    public ?int   $repMonth = null;
    public string $repKind  = '';

    public function openRepDetail(int $month, string $kind): void
    {
        if ($month < 1 || $month > 12 || !in_array($kind, [Expense::KIND_XARAJAT, EmployeeSalaryPayment::TYPE_ISHBAY, EmployeeSalaryPayment::TYPE_MAMURIY], true)) return;
        $this->repMonth = $month;
        $this->repKind  = $kind;
    }

    // Hisobotlar: 👁 bilan yashirilgan oylar (Jamiga kirmaydi). Bazada UMUMIY
    // saqlanadi (yil bo'yicha) — bir admin yashirsa, hamma adminlarda bir xil.
    private function repHiddenKey(): string
    {
        return 'yangi_bux.hidden_months.' . $this->ybYear;
    }

    private function repHiddenMonths(): array
    {
        return array_values(array_map('intval', (array) \App\Models\AppSetting::get($this->repHiddenKey(), [])));
    }

    // Hisobotlar: yig'ilgan ustunlar — hamma adminlarda bir xil (bazada umumiy)
    public const REP_COLS = ['loyiha', 'tushum', 'xarajat', 'ishbay', 'mamuriy', 'chiqim', 'foyda', 'qoldiq'];

    private function repColsHidden(): array
    {
        return array_values(array_intersect(self::REP_COLS, (array) \App\Models\AppSetting::get('yangi_bux.hidden_cols', [])));
    }

    public function toggleRepCol(string $col): void
    {
        if (!auth()->user()?->isAdmin() || !in_array($col, self::REP_COLS, true)) return;
        $h = $this->repColsHidden();
        $h = in_array($col, $h, true) ? array_diff($h, [$col]) : array_merge($h, [$col]);
        \App\Models\AppSetting::put('yangi_bux.hidden_cols', array_values($h));
    }

    public function toggleRepMonth(int $month): void
    {
        if (!auth()->user()?->isAdmin() || $month < 1 || $month > 12) return;
        $h = $this->repHiddenMonths();
        $h = in_array($month, $h, true) ? array_diff($h, [$month]) : array_merge($h, [$month]);
        sort($h);
        \App\Models\AppSetting::put($this->repHiddenKey(), array_values($h));
    }

    // ── Oydan oyga o'tkazma (masalan: avgust Naqd ortig'i → sentabr Karta) ──
    public bool    $showMt     = false;
    public string  $mtFromYm   = '';
    public ?int    $mtFromAcc  = null;
    public string  $mtToYm     = '';
    public ?int    $mtToAcc    = null;
    public string  $mtAmount   = '';
    public string  $mtComment  = '';

    public function openMonthTransfer(?int $fromMonth = null): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->resetValidation();
        $from = Carbon::create($this->ybYear, $fromMonth ?: $this->ybMonth, 1);
        $this->mtFromYm  = $from->format('Y-m');
        $this->mtToYm    = now()->format('Y-m') > $this->mtFromYm ? now()->format('Y-m') : $from->copy()->addMonth()->format('Y-m');
        $this->mtFromAcc = $this->mtToAcc = null;
        $this->mtAmount  = $this->mtComment = '';
        $this->showMt    = true;
    }

    public function saveMonthTransfer(): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->mtAmount = str_replace([' ', ','], ['', '.'], $this->mtAmount);
        $acc = 'required|exists:financial_accounts,id,user_id,NULL';
        $this->validate([
            'mtFromYm'  => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'mtToYm'    => ['required', 'regex:/^\d{4}-\d{2}$/', 'different:mtFromYm'],
            'mtFromAcc' => $acc,
            'mtToAcc'   => $acc,
            'mtAmount'  => 'required|numeric|min:1',
        ], [
            'mtToYm.different'   => 'Boshqa oyni tanlang (bir oy ichida o\'tkazma uchun Buxgalteriyadagi "Pul o\'tkazish")',
            'mtFromAcc.required' => 'Qaysi hamyondan',
            'mtToAcc.required'   => 'Qaysi hamyonga',
            'mtAmount.required'  => 'Summani kiriting',
        ]);
        [$fy, $fm] = array_map('intval', explode('-', $this->mtFromYm));
        $avail = (float) ($this->monthAccountBalances($fy, $fm)[$this->mtFromAcc] ?? 0);
        if ((float) $this->mtAmount > $avail + 0.01) {
            $this->addError('mtAmount', $this->monthName($fy, $fm) . ' — bu hamyonda faqat ' . number_format(max(0, $avail), 0, '.', ' ') . " so'm bor");
            return;
        }
        \App\Models\AccountTransfer::create([
            'from_account_id' => $this->mtFromAcc,
            'to_account_id'   => $this->mtToAcc,
            'amount'          => (float) $this->mtAmount,
            'transfer_date'   => now()->toDateString(),
            'month'           => $this->mtFromYm,
            'to_month'        => $this->mtToYm,
            'comment'         => trim($this->mtComment) ?: 'Oydan oyga o\'tkazma',
            'created_by'      => auth()->id(),
        ]);
        $this->showMt = false;
        Notification::make()->title("O'tkazildi")->body(number_format((float) $this->mtAmount, 0, '.', ' ') . " so'm: " . $this->mtFromYm . ' → ' . $this->mtToYm)->success()->send();
    }

    public function deleteMonthTransfer(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $t = \App\Models\AccountTransfer::find($id);
        if (!$t || !$t->is_cross_month) return;
        $t->delete();
        Notification::make()->title("O'tkazma bekor qilindi")->warning()->send();
    }

    /** Yil bo'yicha har oyning oydan-oyga o'tkazmalar sofi (kelgan − ketgan) */
    private function crossMonthNet(int $year): array
    {
        $net = array_fill(1, 12, 0.0);
        \App\Models\AccountTransfer::whereNotNull('to_month')->get()->filter->is_cross_month->each(function ($t) use (&$net, $year) {
            $from = $t->month ?: $t->transfer_date->format('Y-m');
            if (str_starts_with($from, $year . '-'))         $net[(int) substr($from, 5, 2)] -= (float) $t->amount;
            if (str_starts_with($t->to_month, $year . '-'))  $net[(int) substr($t->to_month, 5, 2)] += (float) $t->amount;
        });
        return $net;
    }

    // Hisobotlar: "Sof foyda" bosilganda — o'sha oy puli qaysi hamyonda qancha
    public ?int $walletMonth = null;
    // "Sof foyda" bosilganda — hisob-kitob oynasi
    public ?int $profitMonth = null;

    public function openProfit(int $month): void
    {
        if ($month >= 1 && $month <= 12) $this->profitMonth = $month;
    }

    public function closeProfit(): void
    {
        $this->profitMonth = null;
    }

    public function openWallet(int $month): void
    {
        if ($month >= 1 && $month <= 12) $this->walletMonth = $month;
    }

    public function closeWallet(): void
    {
        $this->walletMonth = null;
    }

    public function closeRepDetail(): void
    {
        $this->repMonth = null;
        $this->repKind  = '';
    }

    // Tafsilotdan Kirim-chiqim tabiga o'sha oy + tur filtri bilan o'tish
    public function repToOps(): void
    {
        if (!$this->repMonth) return;
        $this->ybMonth = $this->repMonth;
        $this->opResetFilters();
        $this->opKind = $this->repKind === Expense::KIND_XARAJAT ? Expense::KIND_XARAJAT : Expense::KIND_OYLIK;
        $this->closeRepDetail();
        $this->tab = 'kirim';
    }

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

    private function markChiqim(?int $expenseId): void
    {
        $this->opJustSaved = $expenseId ? 'chiqim:' . $expenseId : null;
    }

    public function opResetFilters(): void
    {
        $this->opFilter = 'chiqim';
        $this->opFrom = $this->opTo = null;
        $this->opMethod = $this->opProject = $this->opUser = $this->opSearch = $this->opKind = '';
    }

    // PaymentModal (to'lov qo'shildi/tahrirlandi/o'chirildi) — sahifani yangilash
    #[On('kb-payment-saved')]
    public function onPaymentSaved(): void
    {
        $id = Payment::orderByDesc('updated_at')->orderByDesc('id')->value('id');
        $this->opJustSaved = $id ? 'kirim:' . $id : null;
    }

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
            $this->chSalaryType    = $e->kind === Expense::KIND_OYLIK ? $e->salary_type
                : EmployeeSalaryPayment::typeForUser($e->responsible);
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
            $this->chSalaryType    = '';
            $this->chRecurringId   = null;
        }
        $this->showChiqimModal = true;
    }

    // Xodim tanlanganda — oylik turi xodimning standart turidan olinadi
    public function updatedChResponsibleId($value): void
    {
        $this->chSalaryType = $value ? EmployeeSalaryPayment::typeForUser(User::find($value)) : '';
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
        // O'tkazma: chiqqan oyda (month) manbadan ayiriladi, kirgan oyda (to_month) qo'shiladi
        $trIn  = \App\Models\AccountTransfer::intoMonth($ym)->selectRaw('to_account_id a, SUM(amount) s')->groupBy('to_account_id')->pluck('s', 'a');
        $trOut = \App\Models\AccountTransfer::outOfMonth($ym)->selectRaw('from_account_id a, SUM(amount) s')->groupBy('from_account_id')->pluck('s', 'a');

        $res = [];
        foreach ($in->keys()->merge($out->keys())->merge($trIn->keys())->merge($trOut->keys())->unique() as $id) {
            $res[$id] = (float) ($in[$id] ?? 0) + (float) ($trIn[$id] ?? 0) - (float) ($out[$id] ?? 0) - (float) ($trOut[$id] ?? 0);
        }
        return $res;
    }

    /**
     * Oy puli hamyonlar bo'yicha: har bir hisobga shu oy loyihalaridan tushgan
     * kirim − shu oyga yozilgan chiqim ± shu oy o'tkazmalari (monthAccountBalances
     * bilan bir xil qoida). Hisobi ko'rsatilmagan eski yozuvlar alohida qatorda.
     * Barcha qatorlar qoldig'i yig'indisi = shu oyning "Qoldiq"i (oydan oyga o'tkazmalar bilan).
     */
    private function monthWalletBreakdown(int $year, int $month): array
    {
        $ym = sprintf('%04d-%02d', $year, $month);
        $key = fn ($v) => $v === null ? 'none' : (int) $v;
        $in  = $this->paymentsOfMonth($year, $month)->selectRaw('account_id, SUM(amount) s')->groupBy('account_id')->get()->mapWithKeys(fn ($r) => [$key($r->account_id) => (float) $r->s]);
        $out = Expense::where($this->expenseScope($year, $month))->selectRaw('account_id, SUM(amount) s')->groupBy('account_id')->get()->mapWithKeys(fn ($r) => [$key($r->account_id) => (float) $r->s]);
        // O'tkazma: chiqqan oyda (month) manbadan ayiriladi, kirgan oyda (to_month) qo'shiladi
        $trIn  = \App\Models\AccountTransfer::intoMonth($ym)->selectRaw('to_account_id a, SUM(amount) s')->groupBy('to_account_id')->pluck('s', 'a');
        $trOut = \App\Models\AccountTransfer::outOfMonth($ym)->selectRaw('from_account_id a, SUM(amount) s')->groupBy('from_account_id')->pluck('s', 'a');

        $accounts = FinancialAccount::with('owner:id,name')->get()->keyBy('id');
        $rows = [];
        foreach ($in->keys()->merge($out->keys())->merge($trIn->keys())->merge($trOut->keys())->unique() as $id) {
            $a = $id === 'none' ? null : $accounts->get($id);
            $r = [
                'name'  => $id === 'none' ? "Hisobi ko'rsatilmagan" : ($a?->name ?: (FinancialAccount::typeOptions()[$a?->type] ?? '#' . $id)),
                'type'  => $a?->type,
                'group' => $id === 'none' ? 'none' : ($a?->user_id ? 'person' : ($a?->is_personal ? 'personal' : 'company')),
                'in'    => (float) ($in[$id] ?? 0),
                'out'   => (float) ($out[$id] ?? 0),
                'tr'    => (float) ($trIn[$id] ?? 0) - (float) ($trOut[$id] ?? 0),
            ];
            $r['net'] = $r['in'] - $r['out'] + $r['tr'];
            if (abs($r['in']) + abs($r['out']) + abs($r['tr']) > 0) $rows[] = $r;
        }
        $order = ['company' => 0, 'personal' => 1, 'person' => 2, 'none' => 3];
        usort($rows, fn ($x, $y) => [$order[$x['group']], -$x['net']] <=> [$order[$y['group']], -$y['net']]);
        return $rows;
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
            'chSalaryType'    => ($newSalary || $attach) ? 'required|in:' . implode(',', array_keys(EmployeeSalaryPayment::typeOptions())) : 'nullable',
            'chDate'          => $this->chiqimId ? 'required|date' : [
                'required', 'date',
                'after_or_equal:' . Carbon::create($this->ybYear, $this->ybMonth, 1)->format('Y-m-d'),
                'before_or_equal:' . Carbon::create($this->ybYear, $this->ybMonth, 1)->endOfMonth()->format('Y-m-d'),
            ],
            'chComment'       => ($newSalary ? 'nullable' : 'required') . '|string|max:255',
            'chAmount'        => 'required|numeric|min:1',
            'chAccountId'     => 'required|exists:financial_accounts,id,user_id,NULL',
            // Xarajatda xodim ixtiyoriy — tanlanmasa firma (umumiy) xarajati
            'chResponsibleId' => ($this->chKind === Expense::KIND_OYLIK ? 'required' : 'nullable') . '|exists:users,id',
            'chProjectId'     => 'nullable|exists:projects,id',
            'chFile'          => 'nullable|file|max:8192|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
        ], [
            'chKind.required'      => 'Chiqim turini tanlang: Xarajat yoki Oylik / avans',
            'chDate.after_or_equal'  => 'Sana ochiq turgan oy ichida bo\'lishi kerak — chiqim shu oy pulidan yechiladi',
            'chDate.before_or_equal' => 'Sana ochiq turgan oy ichida bo\'lishi kerak — chiqim shu oy pulidan yechiladi',
            'chSalaryMonth.required' => 'Qaysi oy uchun ekanini tanlang',
            'chSalaryType.required'  => "Oylik turini tanlang: Ishbay yoki Ma'muriy",
            'chComment.required'   => 'Tavsif kiriting',
            'chAmount.required'    => 'Summani kiriting',
            'chAccountId.required' => "Qaysi hisobdan to'langanini tanlang",
            'chResponsibleId.required' => 'Xodimni tanlang (kimga oylik / avans berildi)',
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
                'salary_type' => $this->chSalaryType,
                'paid_at'  => $this->chDate,
                'note'     => trim($this->chComment) ?: null,
                'given_by' => auth()->id(),
            ], null, $this->chSalaryType === EmployeeSalaryPayment::TYPE_ISHBAY && $remaining > 0 && $amount < $remaining, $this->chAccountId);
            // Qo'shimcha maydonlar (loyiha, izoh, hujjat) — bog'liq xarajat qatoriga
            $this->markChiqim(Expense::where('salary_payment_id', $payment?->id)->value('id'));
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
            $this->markChiqim($old->id);
            if ($attach) {
                \App\Services\SalaryPaymentService::attachExpense($old->fresh(), (int) $this->chResponsibleId, $this->chSalaryMonth, $this->chSalaryType);
                Notification::make()->title('Oylik / avansga aylantirildi')->body("Xodim to'lovlariga (Oylik maosh, Oylik hisobot) qo'shildi. Chiqim ikki marta hisoblanmaydi.")->success()->send();
                $this->closeChiqim();
                return;
            }
            Notification::make()->title('Chiqim yangilandi')->success()->send();
        } else {
            $this->markChiqim(Expense::create($data + ['created_by' => auth()->id(), 'recurring_expense_id' => $this->chRecurringId])->id);
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

    /**
     * Oylik hisobot qoidasi: To'lanishi kerak = mijoz to'lagan ulushga mutanosib
     * ochilgan komissiya (+ oklad) − berilgan ishbay oylik; oshib ketsa — Ortiqcha
     * to'langan. Ma'muriy to'lovlar ortiqchaga kirmaydi (ma'muriy xodim okladi
     * ma'muriy to'lovlardan yopiladi). $calc = yearGrid 'calc' (ochilgan komissiya + oklad).
     * @return array{0: float, 1: float} [to'lanishi kerak, ortiqcha to'langan]
     */
    private static function payableSplit(User $u, float $calc, Collection $pays): array
    {
        $paidMam = (float) $pays->filter(fn ($p) => $p->type === EmployeeSalaryPayment::TYPE_MAMURIY)->sum('amount');
        $paidIsh = (float) $pays->sum('amount') - $paidMam;
        $base    = (float) ($u->base_salary ?? 0);
        if (EmployeeSalaryPayment::typeForUser($u) === EmployeeSalaryPayment::TYPE_MAMURIY) {
            $comm = $calc - $base;
            return [max(0, $comm - $paidIsh) + max(0, $base - $paidMam), max(0, $paidIsh - $comm)];
        }
        return [max(0, $calc - $paidIsh), max(0, $paidIsh - $calc)];
    }

    /** Tanlangan oy uchun "To'lanishi kerak" (Oylik maosh jadvalidagi bilan bir xil). */
    private function remainingFor(int $userId): float
    {
        $row = collect(EmployeePayableService::yearGrid($this->ybYear))->firstWhere('user.id', $userId);
        if (!$row) return 0;
        $pays = EmployeeSalaryPayment::with('user')->where('user_id', $userId)->where('month', $this->ym())->get();
        return self::payableSplit($row['user'], (float) ($row['months'][$this->ybMonth]['calc'] ?? 0), $pays)[0];
    }

    /** Hisoblangan (oklad + shu oy barcha ishlari ulushi) bo'yicha qoldiq */
    private function fullRemainingFor(int $userId): float
    {
        $u = User::find($userId);
        if (!$u) return 0;
        $work = EmployeePayableService::workSummaryForMonth($this->ybYear, $this->ybMonth);
        $paid = (float) EmployeeSalaryPayment::where('user_id', $userId)->where('month', $this->ym())->sum('amount');
        return max(0, (float) ($u->base_salary ?? 0) + (float) ($work[$userId]['total'] ?? 0) - $paid);
    }

    public function openPay(?int $userId = null): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->resetValidation();
        $this->payEditId    = null;
        $this->payUserId    = $userId;
        $this->payRemaining = $userId ? $this->remainingFor($userId) : 0;
        $this->payFullRemaining = $userId ? $this->fullRemainingFor($userId) : 0;
        $this->payAmount    = $this->payRemaining > 0 ? number_format($this->payRemaining, 0, '.', ' ') : '';
        $this->payDate      = now()->format('Y-m-d');
        $this->payNote      = '';
        $this->payAccountId = null;   // har safar admin o'zi tanlaydi (naqd yoki karta)
        $this->paySalaryType = EmployeeSalaryPayment::typeForUser($userId ? User::find($userId) : null);
        $this->showPayModal = true;
    }

    // Modalda xodim tanlanganda — qoldiq va oylik turini avtomatik qo'yish
    public function updatedPayUserId($value): void
    {
        if ($this->payEditId) return;
        $this->paySalaryType = EmployeeSalaryPayment::typeForUser($value ? User::find($value) : null);
        $this->payRemaining = $value ? $this->remainingFor((int) $value) : 0;
        $this->payFullRemaining = $value ? $this->fullRemainingFor((int) $value) : 0;
        $this->payAmount    = $this->payRemaining > 0 ? number_format($this->payRemaining, 0, '.', ' ') : '';
    }

    // Xodimning standart maosh turi (Ishbay ↔ Ma'muriy). Turi yozilmagan eski
    // to'lovlar shu turdan olinadi; turi yozilgan to'lovlar o'zgarmaydi.
    public function toggleUserSalaryType(int $userId): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $u = User::find($userId);
        if (!$u) return;
        $new = $u->salary_type === EmployeeSalaryPayment::TYPE_MAMURIY ? null : EmployeeSalaryPayment::TYPE_MAMURIY;
        $u->update(['salary_type' => $new]);
        Notification::make()->title("{$u->name} — " . ($new ? "Ma'muriy oylik" : 'Ishbay oylik'))->success()->send();
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
        $this->paySalaryType = $p->type;
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
            'paySalaryType' => 'required|in:' . implode(',', array_keys(EmployeeSalaryPayment::typeOptions())),
        ], [
            'payUserId.required'    => 'Xodimni tanlang',
            'payAmount.required'    => 'Summani kiriting',
            'payAccountId.required' => 'Qaysi hisobdan berilganini tanlang',
        ]);

        $amount = (float) $this->payAmount;
        $month  = $this->payEditId ? (EmployeeSalaryPayment::find($this->payEditId)?->month ?? $this->ym()) : $this->ym();
        $savedPay = \App\Services\SalaryPaymentService::save([
            'user_id'  => $this->payUserId,
            'month'    => $month,
            'amount'   => $amount,
            'salary_type' => $this->paySalaryType,
            'paid_at'  => $this->payDate,
            'note'     => trim($this->payNote) ?: null,
            'given_by' => auth()->id(),
        ], $this->payEditId, $this->payRemaining > 0 && $amount < $this->payRemaining, $this->payAccountId);

        $this->markChiqim(Expense::where('salary_payment_id', $savedPay?->id)->value('id'));
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
            $i + 1, $r['user']->name, $r['position'], $r['role'], $r['base'], $r['workTotal'], $r['overpaid'], $r['remaining'], $r['paid'],
            $r['pendingCount'] ? $r['pendingCount'] . ' ta · ' . number_format($r['pendingComm'], 0, '.', ' ') : '', $r['statusLabel'], $r['paidAt']?->format('d.m.Y') ?? '',
        ])->all();
        $data[] = ['', 'Jami', '', '', $rows->sum('base'), $rows->sum('workTotal'), $rows->sum('overpaid'), $rows->sum('remaining'), $rows->sum('paid'), number_format($rows->sum('pendingComm'), 0, '.', ' '), '', ''];

        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
            public function headings(): array
            {
                return ['#', 'Xodim', 'Lavozim', "Bo'lim", 'Asosiy maosh', 'Hisoblangan', "Ortiqcha to'langan", "To'lanishi kerak", "To'landi", 'Kutayotgan', "To'lov holati", "To'lov sanasi"];
            }
        };

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'oylik-maosh-' . $this->ym() . '.xlsx');
    }

    public const ROLE_LABELS = ['admin' => 'Rahbariyat', 'menejer' => 'Menejerlar', 'bajaruvchi' => 'Ijrochilar', 'hisobchi' => 'Buxgalteriya'];

    /** Tanlangan oy bo'yicha har bir xodim qatori (filtrlarsiz). */
    private function salaryRows(array $grid): Collection
    {
        $ym = $this->ym();
        $pays = EmployeeSalaryPayment::with('user')->where('month', $ym)->get()->groupBy('user_id');
        // Ish hajmi — Oylik hisobotdagi "Hisoblangan" bilan bir xil
        $work = EmployeePayableService::workSummaryForMonth($this->ybYear, $this->ybMonth);

        return collect($grid)->map(function ($row) use ($pays, $work) {
            $u    = $row['user'];
            $cell = $row['months'][$this->ybMonth] ?? ['calc' => 0, 'paid' => 0, 'remaining' => 0];
            $base = (float) ($u->base_salary ?? 0);
            // Jami summa = oklad + HISOBLANGAN (shu oy loyihalaridagi barcha ishlar
            // ulushi — Oylik hisobotdagi kabi). Mijoz to'lagan qismga to'g'ri keladigani
            // ($earned) alohida — "hozir to'lash mumkin" ma'lumoti sifatida.
            $workTotal = (float) ($work[$u->id]['total'] ?? 0);
            $earned    = max(0, (float) $cell['calc'] - $base);
            $total = $base + $workTotal;
            $paid  = (float) $cell['paid'];
            [$kerak, $ortiqcha] = self::payableSplit($u, (float) $cell['calc'], $pays->get($u->id, collect()));
            $status = $total <= 0 && $paid <= 0 ? 'yoq' : ($kerak > 0 ? ($paid > 0 ? 'qisman' : 'tolanmagan') : 'tolangan');
            return [
                'user'        => $u,
                'position'    => $u->position ?: '—',
                // Bo'lim = maosh turi (ishbay — toposyomka/ariza/eskiz; ma'muriy — firma foydasidan)
                'role'        => EmployeeSalaryPayment::typeOptions()[EmployeeSalaryPayment::typeForUser($u)],
                'roleKey'     => EmployeeSalaryPayment::typeForUser($u),
                'base'        => $base,
                'extra'       => $workTotal,
                'earned'      => $earned,
                'payable'     => max(0, (float) $cell['calc'] - $paid),   // mijoz to'lagani bo'yicha hozir to'lash mumkin
                'total'       => $total,
                'paid'        => $paid,
                'remaining'   => $kerak,      // To'lanishi kerak (Oylik hisobotdagi kabi)
                'overpaid'    => $ortiqcha,   // Ortiqcha to'langan (faqat ishbay to'lovlar)
                'pendingCount'=> (int) ($work[$u->id]['pending_count'] ?? 0),
                'pendingComm' => (float) ($work[$u->id]['pending_comm'] ?? 0),
                'status'      => $status,
                'statusLabel' => ['tolangan' => "To'langan", 'qisman' => 'Qisman', 'tolanmagan' => "To'lanmagan", 'yoq' => 'Hisoblanmagan'][$status],
                'paidAt'      => $pays->get($u->id)?->max('paid_at'),
                'payCount'    => $pays->get($u->id)?->count() ?? 0,
                'work'        => $work[$u->id] ?? null,
                'workTotal'   => $workTotal,
            ];
        })->sortBy(fn ($r) => $r['roleKey'] === EmployeeSalaryPayment::TYPE_MAMURIY ? 1 : 0)->values();
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
                'touched' => ($p->updated_at ?? $p->created_at)?->format('Y-m-d H:i:s') . sprintf('%010d', $p->id),
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
                    ->orWhere(fn ($u2) => $u2->whereNull('responsible_id')->whereNull('category')->where('created_by', $f['user'])));
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
                'touched' => ($e->updated_at ?? $e->created_at)?->format('Y-m-d H:i:s') . sprintf('%010d', $e->id),
                'type'    => 'chiqim',
                'who'     => $e->project?->owner_name ?: ($e->user?->name ?: 'Xarajat'),
                'who_sub' => $e->project ? ('№' . $e->project->seq_no . ' · ' . ($e->project->address ?: $e->project->title)) : ($e->user ? 'Xodim oyligi' : null),
                'desc'    => $e->comment ?: '—',
                'note'    => $e->note,
                'amount'  => (float) $e->amount,
                'method'  => $e->account?->name ?: (FinancialAccount::typeOptions()[$e->account?->type] ?? '—'),
                'mtype'   => $e->account?->type,
                // Yangi xarajatda xodim tanlanmagan bo'lsa — firma xarajati
                'user'    => $e->responsible?->name ?? ($e->category && !$e->user_id ? '🏢 Firma' : $e->createdBy?->name),
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

        // "new" — oxirgi qo'shilgan/o'zgartirilgan birinchi (yangi yozuv ro'yxat boshida
        // turadi, uni qidirish shart emas); "date" — operatsiya sanasi bo'yicha.
        return $rows->sortByDesc(($f['sort'] ?? 'date') === 'new' ? 'touched' : 'sort')->values();
    }

    /** Qarzdor loyihalar (bekor qilinmagan, to'xtatilmagan), eng katta qarzdan boshlab. */
    // ── Mijozlar qarzlari: izoh va "telefon qilindi" belgisi ──────────────
    // Query builder orqali yoziladi — Project model hodisalari (status log,
    // SMS va h.k.) bu oddiy belgilar uchun ishga tushmasin.
    public function saveDebtComment(int $projectId, ?string $comment): void
    {
        if (!static::canAccess()) return;
        $comment = trim((string) $comment);
        Project::whereKey($projectId)->update(['debt_comment' => $comment !== '' ? mb_substr($comment, 0, 2000) : null]);
        $this->dispatch('notify', type: 'success', message: 'Izoh saqlandi');
    }

    public function toggleDebtCalled(int $projectId): void
    {
        if (!static::canAccess()) return;
        $p = Project::find($projectId, ['id', 'debt_called']);
        if (!$p) return;

        if ($p->debt_called) {
            // "Qilinmadi"ga qaytariladi — oxirgi qo'ng'iroq sanasi esa saqlanib qoladi
            Project::whereKey($projectId)->update(['debt_called' => false]);
        } else {
            Project::whereKey($projectId)->update([
                'debt_called'    => true,
                'debt_called_at' => now(),
                'debt_called_by' => auth()->id(),
            ]);
            $this->dispatch('notify', type: 'success', message: 'Telefon qilindi deb belgilandi');
        }
    }

    private function clientDebts(): Collection
    {
        return Project::excludePaused()
            ->where('status', '!=', 'bekor_qilingan')
            ->whereColumn('total_price', '>', 'paid_amount')
            ->with('debtCalledBy:id,name')
            ->get(['id', 'seq_no', 'owner_name', 'title', 'address', 'total_price', 'paid_amount', 'created_at', 'status',
                   'phones', 'debt_comment', 'debt_called', 'debt_called_at', 'debt_called_by'])
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
                $wk = EmployeePayableService::workSummaryForMonth($d->year, $d->month);
                $calc = $pd = 0.0;
                foreach ($grids[$d->year] as $r) {
                    $c   = $r['months'][$d->month];
                    $due = (float) ($r['user']->base_salary ?? 0) + (float) ($wk[$r['user']->id]['total'] ?? 0);
                    $calc += $due;
                    $pd   += min($c['paid'], $due);
                }
                $six[] = ['label' => $uzMonths[$d->month - 1], 'paid' => $pd, 'unpaid' => max(0, $calc - $pd), 'cur' => $k === 0];
            }

            // To'langan oylik turlar bo'yicha: ishbay / ma'muriy
            $paidAll  = (float) $all->sum('paid');
            $posDonut = [];
            foreach ([EmployeeSalaryPayment::TYPE_ISHBAY => '#2563eb', EmployeeSalaryPayment::TYPE_MAMURIY => '#7c3aed'] as $tk => $tc) {
                $sum = (float) $all->where('roleKey', $tk)->sum('paid');
                if ($sum > 0) $posDonut[] = ['label' => EmployeeSalaryPayment::typeOptions()[$tk], 'value' => $sum, 'pct' => $paidAll > 0 ? $sum / $paidAll * 100 : 0, 'color' => $tc];
            }

            $lastPay = EmployeeSalaryPayment::with('user')->orderByDesc('paid_at')->orderByDesc('id')->first();

            $salary = [
                'rows'       => $rows->values(),
                'staffCount' => $all->count(),
                'roleCount'  => $all->pluck('roleKey')->unique()->count(),
                'paidAll'    => $paidAll,
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
        $salaryByMonth  = array_fill(1, 12, 0.0);   // shundan oylik / avans (ikkala tur)
        $mamuriyByMonth = array_fill(1, 12, 0.0);   // shundan ma'muriy oylik (direktor, admin...)
        Expense::with(['salaryPayment:id,salary_type', 'user:id,salary_type', 'responsible:id,salary_type'])
            ->where(function ($q) use ($year) {
                $q->where('month', 'like', $year . '-%')
                  ->orWhere(fn ($q2) => $q2->whereNull('month')->whereYear('expense_date', $year));
            })->get(['id', 'amount', 'month', 'expense_date', 'category', 'user_id', 'responsible_id', 'salary_payment_id'])
            ->each(function ($e) use (&$expenseByMonth, &$salaryByMonth, &$mamuriyByMonth) {
                $m = $e->month ? (int) substr($e->month, 5, 2) : (int) $e->expense_date->month;
                $expenseByMonth[$m] += (float) $e->amount;
                if ($e->kind === Expense::KIND_OYLIK) {
                    $salaryByMonth[$m] += (float) $e->amount;
                    if ($e->salary_type === EmployeeSalaryPayment::TYPE_MAMURIY) $mamuriyByMonth[$m] += (float) $e->amount;
                }
            });
        $crossNet = $this->crossMonthNet($year);
        $chart = [];
        $monthNames = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = [
                'label'   => $monthNames[$m - 1],
                'm'       => $m,
                'income'  => $incomeByMonth[$m],
                'expense' => $expenseByMonth[$m],
                'salary'  => $salaryByMonth[$m],
                'ishbay'  => $salaryByMonth[$m] - $mamuriyByMonth[$m],
                'mamuriy' => $mamuriyByMonth[$m],
                'other'   => $expenseByMonth[$m] - $salaryByMonth[$m],
                'profit'  => $incomeByMonth[$m] - $expenseByMonth[$m],
                'qoldiq'  => $incomeByMonth[$m] - $expenseByMonth[$m] + ($crossNet[$m] ?? 0),
                'cross'   => $crossNet[$m] ?? 0,
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
            'user' => $this->opUser, 'project' => trim($this->opProject), 'search' => trim($this->opSearch), 'sort' => $this->opSort,
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

        // Oylik "to'lanishi kerak" — shu oy loyihalaridagi bajarilgan ishlar
        // uchun mijoz TO'LIQ to'lasa beriladigan komissiya + oklad. Tizim
        // ishlamagan (birinchi loyihadan oldingi) va hali kelmagan oylar — yo'q.
        // Ishbay "kerak" = hisoblangan (har kimning ishlari) + ishbay xodimlar okladi;
        // Ma'muriy "kerak" = ma'muriy xodimlar okladi (belgilangan bo'lsa).
        $isMam = fn ($u) => EmployeeSalaryPayment::typeForUser($u) === EmployeeSalaryPayment::TYPE_MAMURIY;
        // Ishbay "kerak" = Oylik hisobotdagi "Hisoblangan" (tugallangan + kutayotgan ishlar)
        $workCache = [];
        $dueParts = function (array $r, int $m) use ($isMam, $year, &$workCache): array {
            $workCache[$m] ??= EmployeePayableService::workSummaryForMonth($year, $m);
            $base = (float) ($r['user']->base_salary ?? 0);
            $cell = $r['months'][$m] ?? ['full' => 0, 'calc' => 0];
            $mam  = $isMam($r['user']);
            return [
                'ishbay'        => (float) ($workCache[$m][$r['user']->id]['total'] ?? 0) + ($mam ? 0 : $base),
                'ishbay_earned' => (float) $cell['calc'] - $base + ($mam ? 0 : $base),
                'mamuriy'       => $mam ? $base : 0.0,
            ];
        };
        $salaryDue = array_fill(1, 12, null);
        $mamuriyDue = array_fill(1, 12, null);
        if ($this->tab === 'hisobotlar') {
            $firstYm = Project::min('created_at');
            $firstYm = $firstYm ? Carbon::parse($firstYm)->format('Y-m') : null;
            $nowYm   = now()->format('Y-m');
            for ($m = 1; $m <= 12; $m++) {
                $ymM = sprintf('%04d-%02d', $year, $m);
                if (!$firstYm || $ymM < $firstYm || $ymM > $nowYm) continue;
                $salaryDue[$m]  = (float) collect($grid)->sum(fn ($r) => $dueParts($r, $m)['ishbay']);
                $mamuriyDue[$m] = (float) collect($grid)->sum(fn ($r) => $dueParts($r, $m)['mamuriy']);
            }

            // Sof foyda — shu oyda ochilgan loyihalar bo'yicha (Dashboard'dagi kabi,
            // to'xtatilgan va bekor qilinganlarsiz). Faqat ISHBAY oylik ayriladi,
            // ma'muriy oylik kirmaydi.
            //   to'liq  = loyihalar summasi − xarajat − ishbay oylik (hisoblangan)
            //   hozirgi = tushgan pul      − xarajat − ishbay oylik (mijoz to'lagani bo'yicha
            //             — xodimga berilgan-berilmaganidan qat'i nazar, chunki u ham foyda emas)
            // To'xtatilgan / bekor qilingan loyihalar endi to'lamaydi — "to'liq" ga faqat
            // ular to'lagan qismi kiradi (tushgan puli baribir tushumda bor).
            $active = "timer_paused_at IS NULL AND status <> 'bekor_qilingan'";
            $proj = Project::whereYear('created_at', $year)
                ->selectRaw("MONTH(created_at) m, SUM(CASE WHEN $active THEN 1 ELSE 0 END) c, SUM(CASE WHEN $active THEN 0 ELSE 1 END) oc,
                             SUM(CASE WHEN $active THEN total_price ELSE paid_amount END) s, SUM(CASE WHEN $active THEN 0 ELSE paid_amount END) os,
                             SUM(CASE WHEN timer_paused_at IS NULL THEN 1 ELSE 0 END) ac, SUM(CASE WHEN timer_paused_at IS NULL THEN total_price ELSE 0 END) asum,
                             SUM(CASE WHEN timer_paused_at IS NULL AND status = 'bekor_qilingan' THEN total_price ELSE 0 END) csum,
                             SUM(CASE WHEN $active THEN GREATEST(total_price - paid_amount, 0) ELSE 0 END) debt")
                ->groupBy('m')->get()->keyBy('m');
            foreach ($chart as &$cr) {
                $m = $cr['m'];
                $cr['projCount']  = (int) ($proj[$m]->c ?? 0);
                $cr['projSum']    = (float) ($proj[$m]->s ?? 0);
                $cr['projOther']  = (int) ($proj[$m]->oc ?? 0);       // to'xtatilgan/bekor (faqat to'lagani)
                $cr['projOtherSum'] = (float) ($proj[$m]->os ?? 0);
                // "Loyihalar summasi" ustuni — Dashboard'dagi kabi (to'xtatilganlarsiz, bekor qilinganlar bilan)
                $cr['allCount']   = (int) ($proj[$m]->ac ?? 0);
                $cr['allSum']     = (float) ($proj[$m]->asum ?? 0);
                $cr['cancelSum']  = (float) ($proj[$m]->csum ?? 0);
                $cr['debtSum']    = (float) ($proj[$m]->debt ?? 0);
                $cr['ishbayDue']  = (float) ($salaryDue[$m] ?? 0);
                $cr['ishbayEarned'] = $salaryDue[$m] === null ? $cr['ishbay']
                    : (float) collect($grid)->sum(fn ($r) => $dueParts($r, $m)['ishbay_earned']);
                $cr['fullProfit'] = $cr['projSum'] - $cr['other'] - $cr['ishbayDue'];
                $cr['curProfit']  = $cr['income'] - $cr['other'] - $cr['ishbayEarned'];
            }
            unset($cr);
        }

        // Katak bosilganda — o'sha oy xarajatlari / oyliklari ro'yxati
        $repDetail = null;
        if ($this->tab === 'hisobotlar' && $this->repMonth && $this->repKind) {
            $rm = $this->repMonth;
            $isSalary = $this->repKind !== Expense::KIND_XARAJAT;
            $list = Expense::with(['project:id,seq_no,owner_name', 'responsible:id,name,salary_type', 'user:id,name,salary_type', 'account:id,name,type', 'recurringExpense:id,name', 'salaryPayment:id,salary_type'])
                ->where($this->expenseScope($year, $rm))->ofKind($isSalary ? Expense::KIND_OYLIK : Expense::KIND_XARAJAT)
                ->orderByDesc('amount')->get();
            if ($isSalary) $list = $list->filter(fn ($e) => $e->salary_type === $this->repKind)->values();
            $repDetail = [
                'title' => $fullMonths[$rm - 1] . ' ' . $year . ' — ' . ['xarajat' => 'Xarajatlar', 'ishbay' => 'Ishbay oylik', 'mamuriy' => "Ma'muriy oylik"][$this->repKind],
                'list'  => $list,
                'total' => (float) $list->sum('amount'),
            ];
            if (!$isSalary) {
                // Turlar bo'yicha qisqacha: doimiy to'lov nomi / loyiha xarajati / boshqa
                $repDetail['groups'] = $list->groupBy(fn ($e) => $e->recurringExpense?->name ?? ($e->project_id ? 'Loyiha xarajatlari' : 'Boshqa xarajatlar'))
                    ->map(fn ($g, $k) => ['label' => $k, 'sum' => (float) $g->sum('amount'), 'count' => $g->count()])
                    ->sortByDesc('sum')->values();
            } else {
                // Har bir xodim: kerak (to'liq) / mijoz to'lagani bo'yicha / berilgan
                $ish = $this->repKind === EmployeeSalaryPayment::TYPE_ISHBAY;
                $givenBy = $list->groupBy(fn ($e) => $e->user_id ?: $e->responsible_id)->map(fn ($g) => (float) $g->sum('amount'));
                $repDetail['staff'] = collect($grid)->map(function ($r) use ($dueParts, $rm, $ish, $givenBy) {
                    $p = $dueParts($r, $rm);
                    return [
                        'user'   => $r['user'],
                        'full'   => $ish ? $p['ishbay'] : $p['mamuriy'],
                        'earned' => $ish ? $p['ishbay_earned'] : $p['mamuriy'],
                        'given'  => (float) ($givenBy[$r['user']->id] ?? 0),
                    ];
                })->filter(fn ($x) => $x['full'] > 0 || $x['given'] > 0);
                // Ishdan bo'shagan (gridda yo'q) xodimga berilgan oylik ham ko'rinsin
                $inGrid = $repDetail['staff']->pluck('user.id')->all();
                foreach ($givenBy as $uid => $sum) {
                    if (!$uid || in_array($uid, $inGrid)) continue;
                    $u = User::find($uid);
                    if ($u) $repDetail['staff']->push(['user' => $u, 'full' => 0.0, 'earned' => 0.0, 'given' => $sum]);
                }
                $repDetail['staff'] = $repDetail['staff']->map(fn ($x) => $x + ['left' => max(0, $x['full'] - $x['given'])])
                  ->sortByDesc(fn ($x) => [$x['full'], $x['given']])->values();
                $repDetail['due'] = $ish ? $salaryDue[$rm] : $mamuriyDue[$rm];
            }
        }

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
            'salaryDue'    => $salaryDue,
            'mamuriyDue'   => $mamuriyDue,
            'mamuriySpent' => $mamuriyByMonth[$month] ?? 0.0,
            'yearMamuriy'  => array_sum($mamuriyByMonth),
            'repDetail'    => $repDetail,
            'repColsHidden'=> $this->tab === 'hisobotlar' ? $this->repColsHidden() : [],
            'repHidden'    => $this->tab === 'hisobotlar' ? $this->repHiddenMonths() : [],
            'profitInfo'   => $this->tab === 'hisobotlar' && $this->profitMonth ? ($chart[$this->profitMonth - 1] + ['title' => $fullMonths[$this->profitMonth - 1] . ' ' . $year]) : null,
            'mtFromBal'    => $this->showMt && $this->mtFromYm ? $this->monthAccountBalances(...array_map('intval', explode('-', $this->mtFromYm))) : [],
            'mtToBal'      => $this->showMt && $this->mtToYm ? $this->monthAccountBalances(...array_map('intval', explode('-', $this->mtToYm))) : [],
            'wallet'       => $this->tab === 'hisobotlar' && $this->walletMonth ? [
                'm'     => $this->walletMonth,
                'cross' => \App\Models\AccountTransfer::with(['fromAccount', 'toAccount', 'createdBy'])->whereNotNull('to_month')
                    ->where(fn ($q) => $q->outOfMonth(sprintf('%04d-%02d', $year, $this->walletMonth))->orWhere('to_month', sprintf('%04d-%02d', $year, $this->walletMonth)))
                    ->orderByDesc('id')->get()->filter->is_cross_month->values(),
                'title' => $fullMonths[$this->walletMonth - 1] . ' ' . $year,
                'rows'  => $this->monthWalletBreakdown($year, $this->walletMonth),
                'profit'=> $incomeByMonth[$this->walletMonth] - $expenseByMonth[$this->walletMonth],
            ] : null,
            'salaryPayments' => EmployeeSalaryPayment::with(['user', 'giver'])
                ->where('month', sprintf('%04d-%02d', $year, $month))
                ->orderByDesc('paid_at')->get(),
        ];
    }
}
