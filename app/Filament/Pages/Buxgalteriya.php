<?php

namespace App\Filament\Pages;

use App\Models\FinancialAccount;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

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

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
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

    public function getViewData(): array
    {
        $accounts = FinancialAccount::withSum('payments', 'amount')
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $byType = [
            'karta' => $accounts->where('type', 'karta')->values(),
            'naqd'  => $accounts->where('type', 'naqd')->values(),
            'bank'  => $accounts->where('type', 'bank')->values(),
        ];

        $totalBalance = (float) $accounts->sum('payments_sum_amount');

        return [
            'byType'       => $byType,
            'totalBalance' => $totalBalance,
            'typeOptions'  => FinancialAccount::typeOptions(),
        ];
    }
}
