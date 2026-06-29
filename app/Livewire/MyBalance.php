<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\BalanceService;
use Livewire\Component;

/**
 * "Mening balansim" — o'ng chetdagi lenta bosilganda ochiladigan oyna.
 * Har bir foydalanuvchi o'z balansini ko'radi; admin/menejer istalgan xodimni tanlay oladi.
 */
class MyBalance extends Component
{
    public bool $show = false;
    public int  $viewUserId = 0;

    public function mount(): void
    {
        $this->viewUserId = (int) (auth()->id() ?? 0);
    }

    public function openBalance(): void
    {
        $this->show = true;
    }

    public function closeBalance(): void
    {
        $this->show = false;
    }

    public function render()
    {
        $me = auth()->user();

        // Admin/menejer — boshqa xodimni ham ko'ra oladi
        $canSeeOthers = $me && in_array($me->role, ['admin', 'menejer']);
        if (!$canSeeOthers) {
            $this->viewUserId = (int) auth()->id();
        }

        $employees = $canSeeOthers
            ? User::whereIn('role', ['bajaruvchi', 'admin', 'menejer'])->orderBy('name')->get(['id', 'name'])
            : collect();

        // Faqat modal ochiq bo'lsa hisoblaymiz (har sahifada ortiqcha so'rov bo'lmasligi uchun)
        $data = $this->show
            ? BalanceService::forUser($this->viewUserId)
            : ['user_id' => 0, 'user_name' => '', 'rate' => 0, 'earned' => 0, 'pending' => 0,
               'withdrawn' => 0, 'balance' => 0, 'txns' => [], 'txn_count' => 0];

        return view('livewire.my-balance', [
            'd'            => $data,
            'canSeeOthers' => $canSeeOthers,
            'employees'    => $employees,
        ]);
    }
}
