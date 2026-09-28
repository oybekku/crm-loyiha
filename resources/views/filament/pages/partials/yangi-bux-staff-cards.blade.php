@php $fmt = fn ($v) => number_format((float) $v, 0, '.', ' '); @endphp
<div class="yb-card">
    <div class="yb-h">
        <span>👤 Xodimlar bo'yicha xarajatlar <span class="yb-sub">{{ $monthLabel }} · pul hisobi emas, xarajatdagi "kim uchun" belgisi bo'yicha</span></span>
        <button type="button" class="yb-link" wire:click="openNewCard" title="Yana bir xodimni ko'rsatish">＋ Xodim qo'shish</button>
    </div>
    @forelse($staffCards as $sc)
        <div class="yb-staff">
            <div style="min-width:0">
                <b>{{ $sc['account']->name }}</b>
                <span class="yb-sub">{{ $sc['account']->owner?->name ?? '—' }} · {{ $sc['count'] }} ta xarajat</span>
            </div>
            <div class="yb-sub" style="text-align:right">
                @foreach(array_slice($sc['by'], 0, 3, true) as $lbl => $sum)
                    <span style="white-space:nowrap;margin-left:10px">{{ $lbl }}: <b>{{ $fmt($sum) }}</b></span>
                @endforeach
            </div>
            <div class="yb-staff-n"><span class="yb-sub">Jami</span><b class="yb-r">− {{ $fmt($sc['spent']) }}</b></div>
        </div>
    @empty
        <div class="yb-empty">Hali xodim qo'shilmagan. "＋ Xodim qo'shish" orqali qo'shing.</div>
    @endforelse
</div>
