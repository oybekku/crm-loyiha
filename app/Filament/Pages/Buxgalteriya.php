<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\FinancialAccount;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

// Eslatma: SimplePage (login sahifalari andozasi) sinab ko'rildi — u avtomatik
// route ro'yxatiga qo'shilmas ekan (Filamentning HasRoutes xususiyati faqat
// oddiy Page'da bor). Shu sababli oddiy Page qoldirildi, chap navigatsiya
// menyusi esa view faylida CSS orqali shu sahifada yashiriladi.
class Buxgalteriya extends Page
{
    protected static string  $view            = 'filament.pages.buxgalteriya';
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Buxgalteriya';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 12;
    protected static ?string $title           = 'Buxgalteriya';

    // ── Hisob qo'shish/tahrirlash oynasi ──
    public bool   $showAccountModal = false;
    public ?int   $editAccountId    = null;
    public string $formType         = 'karta';
    public string $formName         = '';
    public string $formCardNumber   = '';
    public string $formBankName     = '';
    public string $formExpiryDate   = '';
    public string $formAccountNumber = '';
    public bool   $formIsFavorite   = false;

    // ── Oy/yil filtri (har bir hisobning shu oydagi to'lovlar yig'indisi) ──
    public ?int $bxYear  = null;
    public ?int $bxMonth = null;

    // ── Xarajat qo'shish/tahrirlash oynasi ──
    public bool   $showExpenseModal = false;
    public ?int   $editExpenseId    = null;
    public ?int   $expAccountId     = null;
    public string $expAmount        = '';
    public string $expComment       = '';
    public string $expDate          = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $this->bxYear  ??= (int) now()->year;
        $this->bxMonth ??= (int) now()->month;
    }

    public function bxChangeMonth(int $delta): void
    {
        $date = \Carbon\Carbon::create($this->bxYear, $this->bxMonth, 1)->addMonths($delta);
        $this->bxYear  = (int) $date->year;
        $this->bxMonth = (int) $date->month;
    }

    public function openAccountModal(?int $id = null): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->editAccountId = $id;

        if ($id) {
            $acc = FinancialAccount::find($id);
            if (!$acc) return;
            $this->formType          = $acc->type;
            $this->formName          = (string) $acc->name;
            $this->formCardNumber    = (string) $acc->card_number;
            $this->formBankName      = (string) $acc->bank_name;
            $this->formExpiryDate    = (string) $acc->expiry_date;
            $this->formAccountNumber = (string) $acc->account_number;
            $this->formIsFavorite    = (bool) $acc->is_favorite;
        } else {
            $this->formType          = 'karta';
            $this->formName          = '';
            $this->formCardNumber    = '';
            $this->formBankName      = '';
            $this->formExpiryDate    = '';
            $this->formAccountNumber = '';
            $this->formIsFavorite    = false;
        }

        $this->showAccountModal = true;
    }

    public function closeAccountModal(): void
    {
        $this->showAccountModal = false;
        $this->editAccountId    = null;
    }

    public function saveAccount(): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->validate([
            'formType' => 'required|in:karta,naqd,bank',
        ]);

        $data = [
            'type'           => $this->formType,
            'name'           => trim($this->formName) ?: null,
            'card_number'    => trim($this->formCardNumber) ?: null,
            'bank_name'      => trim($this->formBankName) ?: null,
            'expiry_date'    => trim($this->formExpiryDate) ?: null,
            'account_number' => trim($this->formAccountNumber) ?: null,
            'is_favorite'    => $this->formIsFavorite,
        ];

        if ($this->editAccountId) {
            FinancialAccount::whereKey($this->editAccountId)->update($data);
            Notification::make()->title('Hisob yangilandi')->success()->send();
        } else {
            FinancialAccount::create($data);
            Notification::make()->title('Hisob qo\'shildi')->success()->send();
        }

        $this->closeAccountModal();
    }

    public function deleteAccount(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;

        FinancialAccount::whereKey($id)->delete();
        Notification::make()->title('Hisob o\'chirildi')->warning()->send();
    }

    public function toggleFavorite(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $acc = FinancialAccount::find($id);
        if ($acc) $acc->update(['is_favorite' => !$acc->is_favorite]);
    }

    // ── Xarajatlar (rasxodlar) ──────────────────────────────────────────────
    public function openExpenseModal(?int $id = null): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->editExpenseId = $id;

        if ($id) {
            $exp = Expense::find($id);
            if (!$exp) return;
            $this->expAccountId = $exp->account_id;
            $this->expAmount    = (string) $exp->amount;
            $this->expComment   = (string) $exp->comment;
            $this->expDate      = $exp->expense_date->format('Y-m-d');
        } else {
            $this->expAccountId = null;
            $this->expAmount    = '';
            $this->expComment   = '';
            $isCurrentMonth     = $this->bxYear === (int) now()->year && $this->bxMonth === (int) now()->month;
            $this->expDate      = $isCurrentMonth
                ? now()->format('Y-m-d')
                : \Carbon\Carbon::create($this->bxYear, $this->bxMonth, 1)->format('Y-m-d');
        }

        $this->showExpenseModal = true;
    }

    public function closeExpenseModal(): void
    {
        $this->showExpenseModal = false;
        $this->editExpenseId    = null;
    }

    public function saveExpense(): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->validate([
            'expAccountId' => 'required|exists:financial_accounts,id',
            'expAmount'    => 'required|numeric|min:0.01',
            'expDate'      => 'required|date',
        ]);

        $data = [
            'account_id'   => $this->expAccountId,
            'amount'       => (float) $this->expAmount,
            'comment'      => trim($this->expComment) ?: null,
            'expense_date' => $this->expDate,
        ];

        if ($this->editExpenseId) {
            Expense::whereKey($this->editExpenseId)->update($data);
            Notification::make()->title('Xarajat yangilandi')->success()->send();
        } else {
            $data['created_by'] = auth()->id();
            Expense::create($data);
            Notification::make()->title('Xarajat qo\'shildi')->success()->send();
        }

        $this->closeExpenseModal();
    }

    public function deleteExpense(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;

        Expense::whereKey($id)->delete();
        Notification::make()->title('Xarajat o\'chirildi')->warning()->send();
    }

    public function getViewData(): array
    {
        $year  = $this->bxYear;
        $month = $this->bxMonth;

        // Dashboarddagi "loyiha ochilgan oyi" mantig'i bilan bir xil bo'lishi uchun —
        // to'lov sanasi emas, balki shu to'lov tegishli LOYIHANING ochilgan (created_at)
        // oyi bo'yicha filtrlanadi. Shu bilan ikkala sahifadagi summalar mos keladi.
        // Xarajatlar esa loyihaga bog'liq emas — o'zining sanasi (expense_date) bo'yicha.
        $accounts = FinancialAccount::withSum(['payments as payments_sum_amount' => function ($q) use ($year, $month) {
                $q->whereHas('project', function ($pq) use ($year, $month) {
                    $pq->whereYear('created_at', $year)->whereMonth('created_at', $month);
                });
            }], 'amount')
            ->withSum(['expenses as expenses_sum_amount' => function ($q) use ($year, $month) {
                $q->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
            }], 'amount')
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $byType = [
            'karta' => $accounts->where('type', 'karta')->values(),
            'naqd'  => $accounts->where('type', 'naqd')->values(),
            'bank'  => $accounts->where('type', 'bank')->values(),
        ];

        $totalIncome  = (float) $accounts->sum('payments_sum_amount');
        $totalSpent   = (float) $accounts->sum('expenses_sum_amount');
        $totalBalance = $totalIncome - $totalSpent;
        $bxMonthLabel = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $expenses = Expense::with('account')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        return [
            'byType'       => $byType,
            'totalBalance' => $totalBalance,
            'totalSpent'   => $totalSpent,
            'typeOptions'  => FinancialAccount::typeOptions(),
            'bxMonthLabel' => $bxMonthLabel,
            'expenses'     => $expenses,
            'allAccounts'  => FinancialAccount::orderBy('name')->get(),
        ];
    }
}
