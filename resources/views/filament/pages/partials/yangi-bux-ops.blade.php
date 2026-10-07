@php
    $actions ??= false; $justSaved ??= null;
    // $collapse berilsa — dastlab shuncha qator ko'rinadi, qolgani tugma bilan ochiladi
    $collapse ??= null;
    $opsMore = $collapse ? max(0, count($rows) - $collapse) : 0;
@endphp
<div @if($opsMore) x-data="{ open: (() => { try { return localStorage.getItem('yb-ops-open') === '1' } catch (e) { return false } })(),
                             toggle() { this.open = !this.open; try { localStorage.setItem('yb-ops-open', this.open ? '1' : '0') } catch (e) {} } }" @endif>
<div class="yb-tbl-wrap">
<table class="yb-tbl">
    <thead><tr><th>#</th><th>Sana</th><th>Tur</th><th>Mijoz / Loyiha</th><th>Tavsif</th><th class="num">Kirim (so'm)</th><th class="num">Chiqim (so'm)</th><th>To'lov turi</th><th>Mas'ul</th><th>Holat</th>@if($actions)<th>Hujjat</th><th style="text-align:right">Amallar</th>@endif</tr></thead>
    <tbody>
    @forelse($rows as $i => $r)
        @php $isJust = $justSaved && $justSaved === $r['type'] . ':' . $r['id']; @endphp
        <tr @class(['yb-just' => $isJust]) wire:key="op-{{ $r['type'] }}-{{ $r['id'] }}" @if($opsMore && $i >= $collapse && !$isJust) x-show="open" x-cloak @endif>
            <td>{{ $i + 1 }}@if($isJust)<span class="yb-just-tag">yangi</span>@endif</td>
            <td style="white-space:nowrap">{{ $r['date']?->format('d.m.Y') }}</td>
            <td>@if($r['type'] === 'kirim')<span class="yb-pill k">Kirim</span>@elseif(($r['kind'] ?? '') === 'oylik')<span class="yb-pill o">Oylik</span>@else<span class="yb-pill c">Xarajat</span>@endif</td>
            <td>@if(!empty($r['who_client']))<x-fish :name="$r['who']" />@else{{ $r['who'] }}@endif @if($r['who_sub'])<span class="yb-sub">{{ \Illuminate\Support\Str::limit($r['who_sub'], 30) }}</span>@endif</td>
            <td>{{ \Illuminate\Support\Str::limit($r['desc'], 40) }}@if(!empty($r['note']))<span class="yb-sub">{{ \Illuminate\Support\Str::limit($r['note'], 50) }}</span>@endif</td>
            <td class="num yb-g">{{ $r['type'] === 'kirim' ? number_format($r['amount'], 0, '.', ' ') : '—' }}</td>
            <td class="num yb-r">{{ $r['type'] === 'chiqim' ? number_format($r['amount'], 0, '.', ' ') : '—' }}</td>
            <td>{{ $r['method'] }}</td>
            <td>{{ $r['user'] ?? '—' }}</td>
            <td><span class="yb-pill ok">Bajarildi</span></td>
            @if($actions)
                <td>
                    @if($r['type'] === 'kirim')
                        <a href="#" onclick="event.preventDefault();window.bhOpenChek && bhOpenChek({{ $r['id'] }})" title="Chek" style="font-size:15px;text-decoration:none">🧾</a>
                    @elseif($r['file'])
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($r['file']) }}" target="_blank" title="Hujjat" style="font-size:15px;text-decoration:none">📎</a>
                    @else <span style="color:var(--yb-mu)">—</span>
                    @endif
                </td>
                <td style="text-align:right;white-space:nowrap">
                    @if($r['type'] === 'kirim')
                        <button type="button" class="yb-act" title="Tahrirlash (loyiha to'lov oynasi)" wire:click="editKirim({{ $r['id'] }})">✏️</button>
                    @elseif($r['locked'])
                        <span class="yb-act" title="Oylikdan avtomatik yozilgan — Oylik hisobot orqali o'zgartiriladi" style="cursor:help">🔒</span>
                    @else
                        <button type="button" class="yb-act" title="Tahrirlash" wire:click="openChiqim({{ $r['id'] }})">✏️</button>
                        <button type="button" class="yb-act del" title="O'chirish" wire:click="deleteChiqim({{ $r['id'] }})" wire:confirm="Bu chiqimni o'chirasizmi? Eski Buxgalteriyadan ham o'chadi.">🗑</button>
                    @endif
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="{{ $actions ? 12 : 10 }}" class="yb-empty">Bu oyda operatsiya yo'q</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@if($opsMore)
    <button type="button" class="yb-rec-more" @click="toggle()">
        <span x-show="!open">▾ Yana {{ $opsMore }} tasini ko'rsatish</span>
        <span x-show="open" x-cloak>▴ Yig'ish</span>
    </button>
@endif
</div>
