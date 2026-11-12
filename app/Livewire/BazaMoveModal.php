<?php

namespace App\Livewire;

use App\Services\ActionConfirmService;
use App\Services\BazaService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * "Loyihani tugallash va Bazaga ko'chirish" oynasi — admin uchun.
 * Loyiha oynasidan (bitta) va Arxiv sahifasidan (bir nechta tanlangan)
 * `baza-open-move` hodisasi bilan ochiladi. Panelning har sahifasida
 * (body.end render hook) bitta nusxada turadi.
 */
class BazaMoveModal extends Component
{
    public bool  $show      = false;
    public array $ids       = [];
    public array $grant     = [];   // service_id => bool (tugallanmagan ishga ulush berilsinmi)
    public string $code     = '';
    public bool  $codeError = false;

    #[On('baza-open-move')]
    public function open(array $ids): void
    {
        if (!auth()->user()?->isAdmin()) return;

        $this->ids       = array_values(array_unique(array_map('intval', $ids)));
        $this->code      = '';
        $this->codeError = false;
        $this->grant     = [];

        // Sukut: mijoz biror narsa to'lagan, xodimi bor tugallanmagan ishlar — belgilangan
        foreach (BazaService::preview($this->ids) as $p) {
            foreach ($p['services'] as $s) {
                if ($s['selectable']) $this->grant[$s['id']] = true;
            }
        }

        $this->show = !empty($this->ids);
    }

    public function close(): void
    {
        $this->reset(['show', 'ids', 'grant', 'code', 'codeError']);
    }

    public function confirm(): void
    {
        $user = auth()->user();
        if (!$user?->isAdmin() || empty($this->ids)) return;

        if (!ActionConfirmService::verify($user, $this->code)) {
            $this->codeError = true;
            return;
        }

        $grantIds = array_keys(array_filter($this->grant));
        $moved    = BazaService::move($this->ids, $grantIds, $user);

        $this->close();
        $this->dispatch('notify', type: 'success', message: "📦 {$moved} ta loyiha Bazaga ko'chirildi");
        // Loyiha oynasi yopilsin, sahifalar (Kanban, Arxiv, ro'yxat) yangilansin
        $this->dispatch('baza-moved');
        $this->dispatch('kb-refresh');
    }

    public function render()
    {
        $rows = $this->show ? BazaService::preview($this->ids) : [];

        $totals = ['paid' => 0.0, 'debt' => 0.0, 'share' => 0.0];
        foreach ($rows as $p) {
            $totals['paid'] += $p['paid'];
            $totals['debt'] += $p['debt'];
            foreach ($p['services'] as $s) {
                if ($s['completed'] || !empty($this->grant[$s['id']])) $totals['share'] += $s['share'];
            }
        }

        return view('livewire.baza-move-modal', [
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
