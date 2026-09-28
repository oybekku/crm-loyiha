<?php

namespace App\Filament\Pages;

use App\Models\EmployeeSalaryPayment;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Payment;
use App\Models\Project;
use App\Services\EmployeePayableService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

// "Yangi bux" — Buxgalteriyaning yangi (dashboard ko'rinishidagi) varianti.
// Faqat O'QIYDI: to'lovlar, xarajatlar, hisoblar va oyliklar mavjud
// jadvallardan olinadi, hech qanday ma'lumot yozilmaydi/o'zgartirilmaydi.
// Yangi xarajat/o'tkazma qo'shish hali eski Buxgalteriya sahifasida.
class YangiBux extends Page
{
    protected static string  $view            = 'filament.pages.yangi-bux';
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Yangi bux';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 13;
    protected static ?string $title           = 'Yangi bux';
    protected static ?string $slug            = 'yangi-bux';
    protected ?string $heading    = '';
    protected ?string $subheading = '';

    public string $tab = 'asosiy';
    public ?int   $ybYear  = null;
    public ?int   $ybMonth = null;
    // Kirim-chiqim tabidagi filtr: all | kirim | chiqim
    public string $opFilter = 'all';

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
    }

    // Grafikdagi oy ustuni bosilganda — o'sha oyga o'tish
    public function ybSetMonth(int $month): void
    {
        if ($month >= 1 && $month <= 12) $this->ybMonth = $month;
    }

    // ── Yordamchi so'rovlar ─────────────────────────────────────────────────

    // Tushum — REAL pul kelgan sana (payment_date) bo'yicha.
    private function incomeFor(int $year, int $month): float
    {
        return (float) Payment::whereYear('payment_date', $year)->whereMonth('payment_date', $month)->sum('amount');
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

    /** Oy bo'yicha operatsiyalar (Kirim = to'lov, Chiqim = xarajat), sana bo'yicha kamayish tartibida. */
    private function operations(int $year, int $month): Collection
    {
        $methods = Payment::methodOptions();

        $kirim = Payment::with(['project', 'account', 'createdBy'])
            ->whereYear('payment_date', $year)->whereMonth('payment_date', $month)
            ->get()
            ->map(fn (Payment $p) => [
                'date'    => $p->payment_date,
                'sort'    => $p->payment_date?->format('Y-m-d') . sprintf('%010d', $p->id),
                'type'    => 'kirim',
                'who'     => $p->project?->owner_name ?: ('Loyiha #' . ($p->project?->seq_no ?? $p->project_id)),
                'who_sub' => $p->project?->title ?: $p->project?->address,
                'desc'    => $p->note ?: 'Loyiha to\'lovi',
                'amount'  => (float) $p->amount,
                'method'  => $p->account?->name ?: ($methods[$p->method] ?? $p->method),
                'user'    => $p->createdBy?->name,
            ]);

        $chiqim = Expense::with(['account', 'createdBy', 'user'])
            ->where($this->expenseScope($year, $month))
            ->get()
            ->map(fn (Expense $e) => [
                'date'    => $e->expense_date,
                'sort'    => $e->expense_date?->format('Y-m-d') . sprintf('%010d', $e->id),
                'type'    => 'chiqim',
                'who'     => $e->user?->name ?: 'Xarajat',
                'who_sub' => $e->user ? 'Xodim' : null,
                'desc'    => $e->comment ?: '—',
                'amount'  => (float) $e->amount,
                'method'  => $e->account?->name ?: (FinancialAccount::typeOptions()[$e->account?->type] ?? '—'),
                'user'    => $e->createdBy?->name,
            ]);

        return $kirim->concat($chiqim)->sortByDesc('sort')->values();
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

        // 12 oylik grafik (tanlangan yil)
        $incomeByMonth = array_fill(1, 12, 0.0);
        Payment::whereYear('payment_date', $year)->get(['amount', 'payment_date'])
            ->each(function ($p) use (&$incomeByMonth) { $incomeByMonth[(int) $p->payment_date->month] += (float) $p->amount; });
        $expenseByMonth = array_fill(1, 12, 0.0);
        Expense::where(function ($q) use ($year) {
                $q->where('month', 'like', $year . '-%')
                  ->orWhere(fn ($q2) => $q2->whereNull('month')->whereYear('expense_date', $year));
            })->get(['amount', 'month', 'expense_date'])
            ->each(function ($e) use (&$expenseByMonth) {
                $m = $e->month ? (int) substr($e->month, 5, 2) : (int) $e->expense_date->month;
                $expenseByMonth[$m] += (float) $e->amount;
            });
        $chart = [];
        $monthNames = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = [
                'label'   => $monthNames[$m - 1],
                'm'       => $m,
                'income'  => $incomeByMonth[$m],
                'expense' => $expenseByMonth[$m],
                'profit'  => $incomeByMonth[$m] - $expenseByMonth[$m],
            ];
        }
        $chartMax = max(1, ...array_map(fn ($c) => max($c['income'], $c['expense'], $c['profit']), $chart));

        // Tushum manbalari (xizmat turlari bo'yicha) — to'lovdagi service_split'dan.
        $svcLabels = Project::serviceOptions();
        $sources = [];
        Payment::whereYear('payment_date', $year)->whereMonth('payment_date', $month)
            ->get(['amount', 'service_split'])
            ->each(function ($p) use (&$sources) {
                $split = is_array($p->service_split) ? array_filter($p->service_split, fn ($v) => $v > 0) : [];
                $splitSum = array_sum($split);
                foreach ($split as $svc => $v) $sources[$svc] = ($sources[$svc] ?? 0) + (float) $v;
                $rest = (float) $p->amount - $splitSum;
                if ($rest > 0.009) $sources['_boshqa'] = ($sources['_boshqa'] ?? 0) + $rest;
            });
        arsort($sources);
        $srcColors = ['#3b82f6', '#22c55e', '#f59e0b', '#a78bfa', '#ef4444', '#14b8a6'];
        $srcTotal = array_sum($sources);
        $donut = [];
        $i = 0;
        foreach ($sources as $key => $val) {
            $donut[] = [
                'label' => $key === '_boshqa' ? 'Boshqa' : ($svcLabels[$key] ?? $key),
                'value' => $val,
                'pct'   => $srcTotal > 0 ? $val / $srcTotal * 100 : 0,
                'color' => $srcColors[$i++ % count($srcColors)],
            ];
        }

        // Hisoblar qoldig'i — barcha vaqt bo'yicha (shaxsiy hisoblar jamiga kirmaydi).
        $accounts = FinancialAccount::withSum('payments as payments_sum_amount', 'amount')
            ->withSum('expenses as expenses_sum_amount', 'amount')
            ->withSum('transfersIn as transfers_in_sum_amount', 'amount')
            ->withSum('transfersOut as transfers_out_sum_amount', 'amount')
            ->where('is_personal', false)
            ->get();
        $balances = [
            ['label' => 'Kassa (naqd)',      'icon' => '💵', 'value' => (float) $accounts->where('type', 'naqd')->sum(fn ($a) => $a->balance)],
            ['label' => 'Bank hisobraqami',  'icon' => '🏦', 'value' => (float) $accounts->where('type', 'bank')->sum(fn ($a) => $a->balance)],
            ['label' => 'Plastik karta',     'icon' => '💳', 'value' => (float) $accounts->where('type', 'karta')->sum(fn ($a) => $a->balance)],
        ];
        $balanceTotal = array_sum(array_column($balances, 'value'));

        $ops = $this->operations($year, $month);

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
            'profitPct'    => self::pct($profit, $pProfit),
            'debtTotal'    => (float) $debts->sum('debt'),
            'debtClients'  => $debts->count(),
            'debts'        => $debts,
            'staffOwed'    => $staffOwed,
            'staffOwedPos' => $staffOwedPositive,
            'staffOwedTotal' => (float) $staffOwedPositive->sum('owed'),
            'chart'        => $chart,
            'chartMax'     => $chartMax,
            'donut'        => $donut,
            'donutTotal'   => $srcTotal,
            'balances'     => $balances,
            'balanceTotal' => $balanceTotal,
            'ops'          => $ops,
            'yearIncome'   => $yearIncome,
            'yearExpense'  => $yearExpense,
            'salaryPayments' => EmployeeSalaryPayment::with(['user', 'giver'])
                ->where('month', sprintf('%04d-%02d', $year, $month))
                ->orderByDesc('paid_at')->get(),
        ];
    }
}
