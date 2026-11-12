<div>
@if($show)
@php $fmt = fn($v) => number_format((float) $v, 0, '.', ' '); @endphp
<div style="position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto"
     wire:click.self="close">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:760px;box-shadow:0 25px 60px rgba(0,0,0,.25);overflow:hidden">

        {{-- Sarlavha --}}
        <div style="padding:18px 22px;background:linear-gradient(135deg,#334155,#0f172a);color:#fff;display:flex;align-items:center;gap:12px">
            <div style="font-size:26px">📦</div>
            <div style="flex:1">
                <div style="font-size:16px;font-weight:800">Loyihani tugallash va Bazaga ko'chirish</div>
                <div style="font-size:12px;opacity:.8;margin-top:2px">{{ count($rows) }} ta loyiha · asosiy saytda butunlay ko'rinmaydi, faqat admin "Baza" bo'limida ko'radi</div>
            </div>
            <button type="button" wire:click="close" style="background:rgba(255,255,255,.12);border:none;color:#fff;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:16px">✕</button>
        </div>

        <div style="padding:18px 22px;display:flex;flex-direction:column;gap:14px;max-height:62vh;overflow-y:auto">

            {{-- Nima bo'ladi --}}
            <div style="font-size:12.5px;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;line-height:1.6">
                ✔ Loyiha Kanban, ro'yxatlar, Arxiv, qarzlar va statistikadan yo'qoladi.<br>
                ✔ Kassa qoldig'i va xodimlar maoshi <b>o'zgarmaydi</b> — hisobotlarda ism o'rniga "📦 BAZA LOYIHASI" yoziladi.<br>
                ✔ Loyiha faqat o'qish uchun qoladi. Hech narsa o'chirilmaydi — "Baza" bo'limidan istalgan vaqt qaytarish mumkin.
            </div>

            @foreach($rows as $p)
            <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
                <div style="padding:10px 14px;background:#f9fafb;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <div style="flex:1;min-width:180px">
                        <div style="font-size:14px;font-weight:700;color:#111827">№{{ $p['seq_no'] }} · <x-fish :name="$p['owner']" /></div>
                        <div style="font-size:11px;color:#6b7280">{{ $p['address'] }}</div>
                    </div>
                    <div style="text-align:right;font-size:12px;color:#374151">
                        To'langan <b style="color:#16a34a">{{ $fmt($p['paid']) }}</b> / {{ $fmt($p['total']) }} so'm
                        <span style="display:inline-block;margin-left:6px;padding:1px 8px;border-radius:10px;font-weight:700;background:{{ $p['pct'] >= 100 ? '#dcfce7' : '#fef3c7' }};color:{{ $p['pct'] >= 100 ? '#166534' : '#92400e' }}">{{ $p['pct'] }}%</span>
                    </div>
                </div>

                @if($p['debt'] > 0 && !$p['cancelled'])
                <div style="padding:8px 14px;background:#fff7ed;border-top:1px solid #fed7aa;font-size:12px;color:#9a3412">
                    ⚠ Mijozning <b>{{ $fmt($p['debt']) }} so'm</b> qarzi bor. Bazaga o'tgach bu qarz hech qayerda ko'rinmaydi (undirilmaydi).
                    Xodimlarga faqat mijoz to'lagan qismga mos ulush beriladi.
                </div>
                @endif
                @if($p['cancelled'])
                <div style="padding:8px 14px;background:#f1f5f9;border-top:1px solid #e2e8f0;font-size:12px;color:#475569">
                    Bekor qilingan loyiha — xodimlarga ulush hisoblanmaydi.
                </div>
                @endif

                @if(count($p['services']))
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead>
                        <tr style="background:#fff;color:#6b7280;text-align:left">
                            <th style="padding:6px 14px;font-weight:600">Xodim / ish</th>
                            <th style="padding:6px 8px;font-weight:600">Holati</th>
                            <th style="padding:6px 8px;font-weight:600;text-align:right">Mijoz to'lagan</th>
                            <th style="padding:6px 8px;font-weight:600;text-align:right">Xodim ulushi</th>
                            <th style="padding:6px 14px;font-weight:600;text-align:center">Ulush</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($p['services'] as $s)
                        @php $on = $s['completed'] || !empty($grant[$s['id']]); @endphp
                        <tr style="border-top:1px solid #f1f5f9;{{ $on ? '' : 'opacity:.55' }}">
                            <td style="padding:7px 14px">
                                <div style="font-weight:600;color:#111827">{{ $s['employee'] ?? "— mas'ul yo'q" }}</div>
                                <div style="color:#6b7280">{{ $s['label'] }} · {{ $fmt($s['price']) }} so'm</div>
                            </td>
                            <td style="padding:7px 8px">
                                @if($s['completed'])
                                    <span style="color:#16a34a;font-weight:600">✔ bajarilgan</span>
                                @else
                                    <span style="color:#d97706;font-weight:600">⏳ tugallanmagan</span>
                                @endif
                            </td>
                            <td style="padding:7px 8px;text-align:right">{{ $s['pct'] }}%</td>
                            <td style="padding:7px 8px;text-align:right;font-weight:700;color:{{ $on ? '#1d4ed8' : '#9ca3af' }}">
                                {{ $s['has_user'] ? $fmt($s['share']) . " so'm" : '—' }}
                            </td>
                            <td style="padding:7px 14px;text-align:center">
                                @if($s['completed'])
                                    <span title="Bajarilgan ish — ulush avvaldan hisoblangan" style="color:#16a34a">✔</span>
                                @elseif($s['selectable'])
                                    <label title="Belgilansa — ish 'bajarildi' deb yopiladi va ulush shu oy maoshiga tushadi" style="cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                        <input type="checkbox" wire:model.live="grant.{{ $s['id'] }}" style="width:16px;height:16px;cursor:pointer">
                                    </label>
                                @else
                                    <span style="color:#9ca3af" title="Mijoz bu ish uchun to'lamagan yoki mas'ul yo'q — ulush yo'q">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @endforeach

            @if(collect($rows)->pluck('services')->flatten(1)->contains('selectable', true))
            <div style="font-size:11.5px;color:#6b7280;line-height:1.5">
                ⏳ <b>Tugallanmagan ish:</b> belgilangan xodimga mijoz to'lagan qismga mos ulush beriladi (masalan 60% to'langan — ulushning 60%).
                Ish "bajarildi" deb yopiladi va ulush <b>{{ now()->translatedFormat('F') }}</b> oyi maoshiga tushadi — o'tgan oylar hisoboti o'zgarmaydi.
                Ishni umuman qilmagan xodimning belgisini olib tashlang.
            </div>
            @endif
        </div>

        {{-- Jami + tasdiqlash --}}
        <div style="padding:14px 22px;border-top:1px solid #e5e7eb;background:#f8fafc">
            <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:12.5px;color:#334155;margin-bottom:12px">
                <span>Tushgan pul: <b style="color:#16a34a">{{ $fmt($totals['paid']) }} so'm</b> <span style="color:#94a3b8">(kassada qoladi)</span></span>
                @if($totals['debt'] > 0)
                <span>Hisobdan chiqadigan qarz: <b style="color:#dc2626">{{ $fmt($totals['debt']) }} so'm</b></span>
                @endif
                <span>Xodimlar ulushi: <b style="color:#1d4ed8">{{ $fmt($totals['share']) }} so'm</b></span>
            </div>

            <div style="max-width:300px">
                @include('partials.action-confirm', ['model' => 'code', 'submit' => 'confirm', 'error' => $codeError, 'hint' => "Tasdiqlash uchun Google Authenticator kodini kiriting"])
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px">
                <button type="button" wire:click="close"
                        style="padding:9px 18px;border-radius:8px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:13px;cursor:pointer">
                    Bekor
                </button>
                <button type="button" wire:click="confirm" wire:loading.attr="disabled" wire:target="confirm"
                        style="padding:9px 18px;border-radius:8px;border:none;background:linear-gradient(135deg,#334155,#0f172a);color:#fff;font-size:13px;font-weight:700;cursor:pointer">
                    <span wire:loading.remove wire:target="confirm">📦 Tugallash va Bazaga ko'chirish</span>
                    <span wire:loading wire:target="confirm">Ko'chirilmoqda...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
