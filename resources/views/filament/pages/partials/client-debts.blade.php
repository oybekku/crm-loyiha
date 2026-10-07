{{--
    Mijozlar qarzlari jadvali — Yangi bux ("Mijozlar qarzlari" tabi) va alohida
    "Mijozlar qarzlari" sahifasi (MijozQarzlari) shu partialni ishlatadi.
    Sahifada @livewire('project-edit-modal') bo'lishi kerak (qatorga bosish).
    Ma'lumot: HandlesClientDebts::clientDebtsViewData()
    ($debtRows, $debtMonths, $debtRowsSum, $debtAllSum, $debtAllCount)
--}}
@php
    $cdFmt = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $cdPhone = function ($ph) {
        $dg = preg_replace('/\D/', '', $ph);
        return strlen($dg) === 12
            ? '+' . substr($dg, 0, 3) . ' ' . substr($dg, 3, 2) . ' ' . substr($dg, 5, 3) . ' ' . substr($dg, 8, 2) . ' ' . substr($dg, 10, 2)
            : $ph;
    };
    $cdMonthLabel = $this->debtMonth !== '' ? ($debtMonths[$this->debtMonth]['label'] ?? '') : '';
@endphp
<div class="cd">
<style>
.cd{--cd-card:#fff;--cd-bd:#eceef2;--cd-tx:#111827;--cd-mu:#6b7280;--cd-soft:#f3f4f6;color:var(--cd-tx)}
.dark .cd{--cd-card:#111827;--cd-bd:#1f2937;--cd-tx:#f3f4f6;--cd-mu:#9ca3af;--cd-soft:#1f2937}
.cd *{box-sizing:border-box}
/* Barcha yozuvlar boshlang'ich o'lchamdan ~19% kattaroq (+15%, +15%, -10%) */
.cd-card{background:var(--cd-card);border:1px solid var(--cd-bd);border-radius:14px;padding:18px;box-shadow:0 1px 6px rgba(0,0,0,.03)}
.cd-h{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.cd-title{font-size:17.8px;font-weight:800}
.cd-sub{display:block;font-size:13px;font-weight:400;color:var(--cd-mu);margin-top:2px}
.cd-filter{display:flex;align-items:center;gap:8px;font-size:14.85px;color:var(--cd-mu)}
.cd-filter select{font-size:15.5px;font-weight:600;padding:7px 32px 7px 10px;border:1px solid var(--cd-bd);border-radius:8px;background-color:var(--cd-card);color:var(--cd-tx);min-width:240px}
.cd-filter select option{background:var(--cd-card);color:var(--cd-tx)}
.cd-wrap{overflow-x:auto}
.cd-tbl{width:100%;border-collapse:collapse;font-size:14.85px}
.cd-tbl th{font-size:13px;font-weight:600;color:var(--cd-mu);text-align:left;padding:9px 10px;background:var(--cd-soft);white-space:nowrap}
.cd-tbl td{padding:8px 10px;border-top:1px solid var(--cd-bd);vertical-align:middle}
.cd-row{cursor:pointer}
.cd-tbl tr:hover td{background:#ecfdf5}
.dark .cd-tbl tr:hover td{background:rgba(16,185,129,.08)}
.cd-tbl .num{text-align:right;white-space:nowrap}
.cd-g{color:#16a34a;font-weight:600}.cd-r{color:#dc2626;font-weight:700}
.cd-empty{text-align:center;color:var(--cd-mu);padding:28px !important}
.cd-in{width:100%;font-size:14.3px;padding:5px 8px;border:1px solid var(--cd-bd);border-radius:6px;background:transparent;color:inherit}
.cd-call{font-size:13px;font-weight:700;padding:4px 10px;border-radius:999px;cursor:pointer;border:1px solid #d1d5db;background:transparent;color:#6b7280;white-space:nowrap}
.cd-call.on{border-color:#16a34a;background:#16a34a;color:#fff}
</style>

<div class="cd-card">
    <div class="cd-h">
        <div class="cd-title">
            Mijozlar qarzlari{{ $cdMonthLabel ? ' — ' . $cdMonthLabel : '' }}
            <span class="cd-sub">
                {{ $debtRows->count() }} ta loyiha · jami {{ $cdFmt($debtRowsSum) }} so'm
                @if($this->debtMonth !== '') (umumiy: {{ $debtAllCount }} ta · {{ $cdFmt($debtAllSum) }} so'm) @endif
                · bekor qilingan va to'xtatilganlar kirmaydi
            </span>
        </div>
        <label class="cd-filter">
            Oy:
            <select wire:model.live="debtMonth">
                <option value="">Umumiy — barcha oylar ({{ $debtAllCount }} ta · {{ $cdFmt($debtAllSum) }})</option>
                @foreach($debtMonths as $ym => $m)
                    <option value="{{ $ym }}">{{ $m['label'] }} ({{ $m['count'] }} ta · {{ $cdFmt($m['sum']) }})</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="cd-wrap">
    <table class="cd-tbl">
        <thead><tr><th>#</th><th>№</th><th>Mijoz (FISH)</th><th>Telefon</th><th>Izoh</th><th>Qo'ng'iroq</th><th>Ochilgan</th><th class="num">Shartnoma</th><th class="num">To'langan</th><th class="num">Qarz</th><th>To'lov %</th></tr></thead>
        <tbody>
        @forelse($debtRows as $i => $d)
            @php
                $p  = $d['project'];
                $pc = $p->total_price > 0 ? min(100, round($p->paid_amount / $p->total_price * 100)) : 0;
                // Telefonlar: ['phone' => '+998...'] ro'yxati — bo'sh/"+998" qoldiqlari tashlanadi
                $phones = collect($p->phones ?? [])->map(fn ($x) => trim(is_array($x) ? ($x['phone'] ?? '') : (string) $x))
                    ->filter(fn ($x) => strlen(preg_replace('/\D/', '', $x)) > 4)->values();
                $callTip = $p->debt_called_at
                    ? "Oxirgi qo'ng'iroq: " . $p->debt_called_at->format('d.m.Y H:i') . ($p->debtCalledBy ? ' · ' . $p->debtCalledBy->name : '')
                    : 'Hali telefon qilinmagan';
            @endphp
            {{-- Qatorga bosilsa loyiha tahrirlash oynasi (izoh/tugma/telefon bosilganda emas) --}}
            <tr wire:key="debt-{{ $p->id }}" class="cd-row"
                @click="if (!$event.target.closest('button,a,input,select,textarea,label')) $wire.dispatch('open-edit-modal', { id: {{ $p->id }} })">
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->seq_no }}</td>
                {{-- FISH: hammasi katta harfda, 15 belgidan keyin qisqartiriladi (to'liq ismi — sichqoncha ustiga olib borganda) --}}
                @php $fish = mb_strtoupper(trim((string) $p->owner_name)); @endphp
                <td style="font-weight:600;white-space:nowrap" title="{{ $fish }}">{{ $fish !== '' ? \Illuminate\Support\Str::limit($fish, 15, '…') : '—' }}</td>
                <td style="white-space:nowrap">
                    @forelse($phones as $k => $ph)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $ph) }}" style="display:block;color:inherit;text-decoration:none;{{ $k ? 'font-size:13px;opacity:.7' : 'font-weight:600' }}">{{ $cdPhone($ph) }}</a>
                    @empty
                        <span class="cd-sub">—</span>
                    @endforelse
                </td>
                <td style="min-width:180px">
                    <input type="text" class="cd-in" value="{{ $p->debt_comment }}" placeholder="Izoh yozing…" maxlength="2000"
                           wire:change="saveDebtComment({{ $p->id }}, $event.target.value)"
                           @keydown.enter="$event.target.blur()"
                           title="{{ $p->debt_comment }}">
                </td>
                <td>
                    <button type="button" class="cd-call {{ $p->debt_called ? 'on' : '' }}" wire:click="toggleDebtCalled({{ $p->id }})" title="{{ $callTip }}">
                        {{ $p->debt_called ? '✓ Qilindi' : '☎ Qilinmadi' }}
                    </button>
                </td>
                <td>{{ $p->created_at?->format('d.m.Y') }}</td>
                <td class="num">{{ $cdFmt($p->total_price) }}</td>
                <td class="num cd-g">{{ $cdFmt($p->paid_amount) }}</td>
                <td class="num cd-r">{{ $cdFmt($d['debt']) }}</td>
                <td style="min-width:90px"><div style="background:var(--cd-soft);border-radius:4px;height:6px"><div style="width:{{ $pc }}%;background:#22c55e;height:6px;border-radius:4px"></div></div><span class="cd-sub">{{ $pc }}%</span></td>
            </tr>
        @empty
            <tr><td colspan="11" class="cd-empty">Qarzdor mijoz yo'q</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
</div>
