{{-- ── Doimiy to'lovlar (arenda, svet, wi-fi...) — tanlangan oy holati ──
     To'lov oddiy chiqim sifatida yoziladi (Chiqim oynasi shablondan to'ldiriladi). --}}
@php
    $fmt = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $R = $recurring;
    // Ixcham ko'rinish: muhimlari tepada (muddati o'tgan → to'lanmagan → to'langan),
    // dastlab faqat 2 tasi ko'rinadi, qolgani tugma bilan ochiladi.
    $recRows = $R['rows']->sortBy(fn ($x) => $x['paid'] ? 2 : ($x['overdue'] ? 0 : 1))->values();
    $recShow = 2;
    $recMore = max(0, $recRows->count() - $recShow);
@endphp
<div class="yb-card" style="margin-bottom:16px"
     x-data="{ open: (() => { try { return localStorage.getItem('yb-rec-open') === '1' } catch (e) { return false } })(),
               toggle() { this.open = !this.open; try { localStorage.setItem('yb-rec-open', this.open ? '1' : '0') } catch (e) {} } }">
    <div class="yb-h">
        <span>🔁 Doimiy to'lovlar — {{ $monthLabel }}
            @if($R['rows']->isNotEmpty())
                <span class="yb-pill {{ $R['paidCount'] === $R['rows']->count() ? 'ok' : 'c' }}" style="margin-left:6px">{{ $R['paidCount'] }} / {{ $R['rows']->count() }} to'landi</span>
            @endif
        </span>
        <button type="button" class="yb-btn" wire:click="openRec">＋ Doimiy to'lov qo'shish</button>
    </div>

    @forelse($recRows as $i => $x)
        @php $r = $x['r']; @endphp
        <div class="yb-rec {{ $x['paid'] ? 'paid' : ($x['overdue'] ? 'late' : '') }}" wire:key="rec-{{ $r->id }}" @if($i >= $recShow) x-show="open" x-cloak @endif>
            <span class="yb-rec-ic">{{ $x['paid'] ? '✅' : ($x['overdue'] ? '🔴' : '⏳') }}</span>
            <div class="yb-rec-n">
                <b>{{ $r->name }}</b>
                <span class="yb-sub">
                    @if($x['paid'])
                        {{ $x['paidDate']?->format('d.m.Y') }} to'langan{{ $x['count'] > 1 ? ' · ' . $x['count'] . ' marta' : '' }}
                    @elseif($x['overdue'])
                        muddati o'tdi ({{ $x['due']->format('d.m') }} gacha edi)
                    @elseif($x['due'])
                        {{ $x['due']->format('d.m') }} gacha
                    @else
                        to'lanmagan
                    @endif
                    @if(!$r->is_active) · ro'yxatdan olingan @endif
                </span>
            </div>
            <div class="yb-rec-v">
                @if($x['paid'])
                    <a href="#" wire:click.prevent="openChiqim({{ $x['expenseId'] }})" title="To'langan summani tahrirlash">{{ $fmt($x['paidSum']) }}</a>
                    @if((float) $r->amount > 0 && abs($x['paidSum'] - (float) $r->amount) >= 1)
                        <span class="yb-sub">reja: {{ $fmt($r->amount) }}</span>
                    @endif
                @else
                    {{ $r->is_fixed ? '' : '~ ' }}{{ $fmt($r->amount) }}
                @endif
            </div>
            <div class="yb-rec-a">
                @if(!$x['paid'] && $r->is_active)
                    <button type="button" class="yb-btn" style="background:#dc2626;padding:6px 12px" wire:click="payRecurring({{ $r->id }})">To'lash</button>
                @elseif($x['paid'])
                    <button type="button" class="yb-act" wire:click="openChiqim({{ $x['expenseId'] }})" title="To'langan summani tahrirlash">✎</button>
                @endif
                @if($r->is_active)
                    <button type="button" class="yb-act" wire:click="openRec({{ $r->id }})" title="Nomi / oylik summasini tahrirlash">⚙</button>
                @endif
            </div>
        </div>
    @empty
        <div class="yb-empty">Hali doimiy to'lov qo'shilmagan. "＋ Doimiy to'lov qo'shish" tugmasi bilan arenda, svet, wi-fi kabilarni kiriting.</div>
    @endforelse

    @if($recMore > 0)
        <button type="button" class="yb-rec-more" @click="toggle()">
            <span x-show="!open">▾ Yana {{ $recMore }} tasini ko'rsatish</span>
            <span x-show="open" x-cloak>▴ Yig'ish</span>
        </button>
    @endif

    @if($R['rows']->isNotEmpty())
        <div class="yb-rec-t">
            Jami: <b>{{ $fmt($R['total']) }}</b> ·
            to'landi: <b class="yb-g">{{ $fmt($R['paid']) }}</b> ·
            qoldi: <b class="{{ $R['left'] > 0 ? 'yb-r' : '' }}">{{ $fmt($R['left']) }}</b> so'm
        </div>
    @endif
</div>
