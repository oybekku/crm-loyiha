<?php

namespace App\Filament\Pages;

use App\Models\AccountTransfer;
use App\Models\Expense;
use App\Models\FinancialAccount;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

// Eslatma: SimplePage (login sahifalari andozasi) sinab ko'rildi — u avtomatik
// route ro'yxatiga qo'shilmas ekan (Filamentning HasRoutes xususiyati faqat
// oddiy Page'da bor). Shu sababli oddiy Page qoldirildi, chap navigatsiya
// menyusi esa view faylida CSS orqali shu sahifada yashiriladi.
class Buxgalteriya extends Page
{
    use WithFileUploads;

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
    public bool   $formIsPersonal   = false;

    // ── Karta foni (rasm yuklash) ──
    public $bgUpload = null;
    public ?int $bgUploadAccountId = null;

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

    // ── Pul o'tkazish oynasi (hisoblar orasida) ──
    public bool   $showTransferModal = false;
    public ?int   $editTransferId    = null;
    public ?int   $transferFromId    = null;
    public ?int   $transferToId      = null;
    public string $transferAmount    = '';
    public string $transferComment   = '';
    public string $transferDate      = '';

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
            $this->formIsPersonal    = (bool) $acc->is_personal;
        } else {
            $this->formType          = 'karta';
            $this->formName          = '';
            $this->formCardNumber    = '';
            $this->formBankName      = '';
            $this->formExpiryDate    = '';
            $this->formAccountNumber = '';
            $this->formIsFavorite    = false;
            $this->formIsPersonal    = false;
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
            'is_personal'    => $this->formIsPersonal,
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

        $acc = FinancialAccount::find($id);
        if ($acc?->background_image) {
            Storage::disk('public')->delete($acc->background_image);
        }
        FinancialAccount::whereKey($id)->delete();
        Notification::make()->title('Hisob o\'chirildi')->warning()->send();
    }

    public function toggleFavorite(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $acc = FinancialAccount::find($id);
        if ($acc) $acc->update(['is_favorite' => !$acc->is_favorite]);
    }

    // ── Karta foni (rasm) ────────────────────────────────────────────────────
    public function setBgTarget(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;
        $this->bgUploadAccountId = $id;
    }

    public function updatedBgUpload(): void
    {
        if (!auth()->user()?->isAdmin() || !$this->bgUploadAccountId) return;

        $this->validate([
            'bgUpload' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $acc = FinancialAccount::find($this->bgUploadAccountId);
        if (!$acc) return;

        if ($acc->background_image) {
            Storage::disk('public')->delete($acc->background_image);
        }

        $path = $this->bgUpload->store('account-bg', 'public');
        $acc->update(['background_image' => $path]);

        $this->bgUpload           = null;
        $this->bgUploadAccountId  = null;
        Notification::make()->title('Fon rasm yangilandi')->success()->send();
    }

    public function removeAccountBackground(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $acc = FinancialAccount::find($id);
        if (!$acc) return;

        if ($acc->background_image) {
            Storage::disk('public')->delete($acc->background_image);
            $acc->update(['background_image' => null]);
        }
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

    // ── Pul o'tkazish (hisoblar orasida) ────────────────────────────────────
    public function openTransferModal(?int $id = null): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->editTransferId = $id;

        if ($id) {
            $t = AccountTransfer::find($id);
            if (!$t) return;
            $this->transferFromId  = $t->from_account_id;
            $this->transferToId    = $t->to_account_id;
            $this->transferAmount  = (string) $t->amount;
            $this->transferComment = (string) $t->comment;
            $this->transferDate    = $t->transfer_date->format('Y-m-d');
        } else {
            $this->transferFromId  = null;
            $this->transferToId    = null;
            $this->transferAmount  = '';
            $this->transferComment = '';
            $isCurrentMonth        = $this->bxYear === (int) now()->year && $this->bxMonth === (int) now()->month;
            $this->transferDate    = $isCurrentMonth
                ? now()->format('Y-m-d')
                : \Carbon\Carbon::create($this->bxYear, $this->bxMonth, 1)->format('Y-m-d');
        }

        $this->showTransferModal = true;
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->editTransferId    = null;
    }

    public function saveTransfer(): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->validate([
            'transferFromId' => 'required|exists:financial_accounts,id|different:transferToId',
            'transferToId'   => 'required|exists:financial_accounts,id',
            'transferAmount' => 'required|numeric|min:0.01',
            'transferDate'   => 'required|date',
        ], [
            'transferFromId.different' => "Qaysidan va qaysiga bir xil hisob bo'lishi mumkin emas",
        ]);

        $data = [
            'from_account_id' => $this->transferFromId,
            'to_account_id'   => $this->transferToId,
            'amount'          => (float) $this->transferAmount,
            'comment'         => trim($this->transferComment) ?: null,
            'transfer_date'   => $this->transferDate,
        ];

        if ($this->editTransferId) {
            AccountTransfer::whereKey($this->editTransferId)->update($data);
            Notification::make()->title("O'tkazma yangilandi")->success()->send();
        } else {
            $data['created_by'] = auth()->id();
            AccountTransfer::create($data);
            Notification::make()->title("O'tkazma bajarildi")->success()->send();
        }

        $this->closeTransferModal();
    }

    public function deleteTransfer(int $id): void
    {
        if (!auth()->user()?->isAdmin()) return;

        AccountTransfer::whereKey($id)->delete();
        Notification::make()->title("O'tkazma o'chirildi")->warning()->send();
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
            ->withSum(['transfersIn as transfers_in_sum_amount' => function ($q) use ($year, $month) {
                $q->whereYear('transfer_date', $year)->whereMonth('transfer_date', $month);
            }], 'amount')
            ->withSum(['transfersOut as transfers_out_sum_amount' => function ($q) use ($year, $month) {
                $q->whereYear('transfer_date', $year)->whereMonth('transfer_date', $month);
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

        $totalSpent = (float) $accounts->sum('expenses_sum_amount');

        // "Jami balans" — faqat haqiqiy (shaxsiy/xarajat deb belgilanmagan) hisoblar
        // yig'indisi. Shaxsiy hisoblarga o'tkazilgan pul xarajat sifatida hisoblanib,
        // manba hisobning (demak — Jami balansning ham) kamayishiga sabab bo'ladi,
        // lekin shaxsiy hisobning o'zi umumiy summaga qo'shilmaydi.
        $totalBalance = (float) $accounts->where('is_personal', false)->sum(fn ($a) => $a->balance);
        $bxMonthLabel = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');

        $transfers = AccountTransfer::with(['fromAccount', 'toAccount'])
            ->whereYear('transfer_date', $year)
            ->whereMonth('transfer_date', $month)
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get();
        $totalTransferred = (float) $transfers->sum('amount');

        $expenses = Expense::with('account')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        return [
            'byType'           => $byType,
            'totalBalance'     => $totalBalance,
            'totalSpent'       => $totalSpent,
            'totalTransferred' => $totalTransferred,
            'typeOptions'      => FinancialAccount::typeOptions(),
            'bxMonthLabel'     => $bxMonthLabel,
            'expenses'         => $expenses,
            'transfers'        => $transfers,
            'allAccounts'      => FinancialAccount::orderBy('name')->get(),
        ];
    }
}
