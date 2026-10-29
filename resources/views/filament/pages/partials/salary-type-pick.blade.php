{{--
    To'lov turi tanlagichi — pul NIMA UCHUN berilayotgani: bajarilgan ishlar uchun (komissiya) / oylik maosh (oklad).
    Parametrlar: $model (wire:model), $value (joriy qiymat), $title (ixtiyoriy sarlavha),
                 $due (ixtiyoriy) — ['ishbay' => qarz, 'mamuriy' => qarz]: har variantda "To'lanishi kerak" ko'rinadi.
--}}
@php $due = $due ?? []; $fmtD = fn ($v) => number_format((float) $v, 0, '.', ' '); @endphp
<div style="margin-bottom:12px">
    <div style="font-size:12px;font-weight:600;margin-bottom:5px">{{ $title ?? "Nima uchun to'lanyapti?" }} <span style="color:#dc2626">*</span></div>
    <div style="display:flex;gap:8px">
        @foreach([
            'ishbay'  => ['🛠 Bajarilgan ishlar uchun', "toposyomka, eskiz, ariza ulushi (komissiya)", '#2563eb', '#eff6ff'],
            'mamuriy' => ['📅 Oylik maosh (oklad)', 'har oy belgilangan doimiy maosh, ishga bog\'liq emas', '#7c3aed', '#f5f3ff'],
        ] as $tk => [$tl, $td, $tc, $tbg])
            <label style="flex:1;cursor:pointer;border:2px solid {{ $value === $tk ? $tc : '#e5e7eb' }};background:{{ $value === $tk ? $tbg : 'transparent' }};border-radius:10px;padding:8px 10px;font-size:13px;font-weight:700;color:{{ $value === $tk ? $tc : 'inherit' }}">
                <input type="radio" wire:model.live="{{ $model }}" value="{{ $tk }}" style="display:none">
                {{ $tl }}
                <span style="display:block;font-size:10.5px;font-weight:400;color:#6b7280;margin-top:2px">{{ $td }}</span>
                @if(array_key_exists($tk, $due))
                    <span style="display:block;margin-top:6px;padding-top:6px;border-top:1px dashed #e5e7eb;font-size:11px;font-weight:600;color:#6b7280">
                        @if($due[$tk] > 0)
                            To'lanishi kerak: <b style="color:#dc2626;font-size:13px">{{ $fmtD($due[$tk]) }} so'm</b>
                        @else
                            <span style="color:#16a34a">✓ qarz yo'q</span>
                        @endif
                    </span>
                @endif
            </label>
        @endforeach
    </div>
    @php $other = $value === 'ishbay' ? 'mamuriy' : 'ishbay'; @endphp
    @if($due && ($due[$value] ?? 0) <= 0 && ($due[$other] ?? 0) > 0)
        <div style="font-size:12px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 10px;margin-top:8px">
            ⚠ Bu turda qarz yo'q — bu to'lov <b>"To'lanishi kerak"</b> summasini kamaytirmaydi.
            Qarz <b>{{ $other === 'ishbay' ? 'bajarilgan ishlar uchun' : 'oylik maosh' }}</b> bo'yicha: {{ $fmtD($due[$other]) }} so'm.
            Bonus yoki avans bo'lsa — davom eting.
        </div>
    @elseif($value === 'mamuriy')
        <div style="font-size:11px;color:#6b7280;margin-top:5px">Ish komissiyasiga bog'liq emas — "ortiqcha to'langan" hisoblanmaydi, hisobotda alohida ustunda turadi.</div>
    @endif
</div>
