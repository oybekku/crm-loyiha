@php $fmt = fn ($v) => number_format((float) $v, 0, '.', ' '); @endphp
<div class="yb-card">
    <div class="yb-h">
        <span>👤 Xodimlar kartalari <span class="yb-sub">xarajatlar uchun ajratilgan pul · {{ $monthLabel }}</span></span>
        <div style="display:flex;gap:6px">
            <button type="button" class="yb-link" wire:click="openNewCard" title="Xodimga xarajat kartasi ochish">＋ Karta ochish</button>
            <button type="button" class="yb-btn" style="padding:6px 12px" wire:click="openTransfer">⇄ Pul ajratish</button>
        </div>
    </div>
    @forelse($staffCards as $sc)
        <div class="yb-staff">
            <div style="min-width:0">
                <b>{{ $sc['account']->name ?: $sc['label'] }}</b>
                <span class="yb-sub">egasi: {{ $sc['account']->owner?->name ?? '—' }}@if($sc['account']->card_number) · {{ $sc['account']->card_number }}@endif</span>
            </div>
            <div class="yb-staff-n"><span class="yb-sub">Ajratildi</span><b>{{ $fmt($sc['given']) }}</b></div>
            <div class="yb-staff-n"><span class="yb-sub">Sarflandi</span><b class="yb-r">{{ $fmt($sc['spent']) }}</b></div>
            <div class="yb-staff-n"><span class="yb-sub">Kartada qoldi</span><b class="{{ $sc['balance'] < 0 ? 'yb-r' : 'yb-g' }}">{{ $fmt($sc['balance']) }}</b></div>
            <button type="button" class="yb-act" title="Shu kartaga pul ajratish" wire:click="openTransfer({{ $sc['account']->id }})">＋</button>
        </div>
    @empty
        <div class="yb-empty">Xarajat kartasi yo'q. "＋ Karta ochish" orqali xodimga karta oching.</div>
    @endforelse
    <div class="yb-sub" style="margin-top:8px">Pul ajratish — xarajat emas, o'tkazma. Umumiy xarajatga faqat kartadan sarflangan chiqimlar kiradi.</div>
</div>
