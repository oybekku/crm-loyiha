<div>
@php $authUser = auth()->user(); @endphp
@if($authUser)

{{-- ─── O'ng chetdagi LENTA ─── --}}
@if(!$show)
<button type="button" wire:click="openBalance" title="Mening balansim"
    style="position:fixed;right:0;top:42%;transform:translateY(-50%);z-index:1200;
           background:linear-gradient(135deg,#4c1d18,#7c2d12);color:#fde68a;border:none;
           border-radius:10px 0 0 10px;padding:14px 8px;cursor:pointer;box-shadow:-3px 4px 16px rgba(0,0,0,.25);
           writing-mode:vertical-rl;text-orientation:mixed;font-size:12px;font-weight:700;letter-spacing:.05em;
           display:flex;align-items:center;gap:6px">
    💳 Mening balansim
</button>
@endif

{{-- ─── MODAL ─── --}}
@if($show)
<style>@keyframes bhBalSlide{from{transform:translateX(100%)}to{transform:translateX(0)}}</style>
<div style="position:fixed;inset:0;background:rgba(0,0,0,.18);z-index:1500;display:flex;align-items:stretch;justify-content:flex-end" wire:click.self="closeBalance">
    <div style="background:#f8fafc;width:100%;max-width:470px;height:100vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:-14px 0 44px rgba(0,0,0,.28);animation:bhBalSlide .25s cubic-bezier(.4,0,.2,1)" wire:click.stop>

        {{-- HEADER --}}
        <div style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding:16px 22px;background:#fff;border-bottom:1px solid #eef2f7">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:17px;font-weight:700;color:#111827">💳 Mening balansim</span>
                <span style="font-size:10px;font-weight:700;color:#92400e;background:#fef3c7;border-radius:5px;padding:2px 7px">TEST REJIMIDA</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                @if($canSeeOthers)
                <select wire:model.live="viewUserId" style="padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;outline:none;max-width:220px">
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}{{ $emp->id === $authUser->id ? ' (men)' : '' }}</option>
                    @endforeach
                </select>
                @endif
                <button wire:click="closeBalance" style="background:#f3f4f6;border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;color:#6b7280;font-size:15px">✕</button>
            </div>
        </div>

        {{-- BODY (scroll) --}}
        <div style="flex:1;overflow-y:auto;padding:18px 22px">

            {{-- Karta + Hisobot (panel tor — ustma-ust) --}}
            <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:16px">

                {{-- Virtual karta --}}
                <div style="background:linear-gradient(135deg,#0d0d0f,#1a1a1d 55%,#232326);border-radius:16px;padding:22px;color:#e2e8f0;position:relative;overflow:hidden;min-height:185px;box-shadow:0 12px 30px rgba(0,0,0,.4)">
                    <div style="position:absolute;right:-30px;top:-30px;width:140px;height:140px;background:rgba(255,255,255,.06);border-radius:50%"></div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start">
                        <div style="font-size:22px;font-style:italic;font-weight:700;opacity:.85">BV</div>
                        <div style="font-size:10px;letter-spacing:.12em;opacity:.7">VIRTUAL HISOB</div>
                    </div>
                    <div style="font-size:19px;letter-spacing:.18em;margin:26px 0 18px;font-family:monospace">5665 0122 **** {{ str_pad((string)($d['user_id'] % 10000), 4, '0', STR_PAD_LEFT) }}</div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-end">
                        <div>
                            <div style="font-size:10px;opacity:.6;margin-bottom:2px">Balans</div>
                            <div style="font-size:24px;font-weight:800">{{ number_format($d['balance'], 0, '.', ' ') }} <span style="font-size:13px;opacity:.7">so'm</span></div>
                        </div>
                        <div style="font-size:10px;opacity:.6;text-align:right">Jami ishlab topilgan<br><span style="font-size:13px;opacity:.9;font-weight:700">{{ number_format($d['earned'], 0, '.', ' ') }}</span></div>
                    </div>
                </div>

                {{-- Hisobot --}}
                <div style="background:#fff;border:1px solid #eef2f7;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:10px">
                    <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px">Hisobot</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:11px 13px">
                            <div style="font-size:11px;color:#b45309;font-weight:600">Jarayonda</div>
                            <div style="font-size:17px;font-weight:800;color:#92400e;margin-top:3px">{{ number_format($d['pending'], 0, '.', ' ') }} <span style="font-size:11px;font-weight:600">so'm</span></div>
                        </div>
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:11px 13px">
                            <div style="font-size:11px;color:#16a34a;font-weight:600">To'langan (olingan)</div>
                            <div style="font-size:17px;font-weight:800;color:#15803d;margin-top:3px">{{ number_format($d['withdrawn'], 0, '.', ' ') }} <span style="font-size:11px;font-weight:600">so'm</span></div>
                        </div>
                    </div>
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:11px 13px;display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <div style="font-size:11px;color:#dc2626;font-weight:600">Qarzdorlik (firma qarzi)</div>
                            <div style="font-size:19px;font-weight:800;color:#b91c1c;margin-top:3px">{{ number_format($d['balance'], 0, '.', ' ') }} <span style="font-size:11px;font-weight:600">so'm</span></div>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;text-align:right">Olinishi mumkin<br>bo'lgan qoldiq</div>
                    </div>
                </div>
            </div>

            {{-- Tranzaksiyalar --}}
            <div style="background:#fff;border:1px solid #eef2f7;border-radius:14px;overflow:hidden">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:13px 16px;border-bottom:1px solid #f1f5f9">
                    <span style="font-size:14px;font-weight:700;color:#111827">Tranzaksiyalar</span>
                    <span style="font-size:12px;color:#6b7280">Soni: <b style="color:#111827">{{ $d['txn_count'] }}</b></span>
                </div>

                @if($d['txn_count'] === 0)
                <div style="padding:30px;text-align:center;color:#9ca3af;font-size:13px">Hali tranzaksiya yo'q</div>
                @else
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:13px">
                    <thead>
                        <tr style="background:#f8fafc;color:#6b7280;font-size:11px;text-transform:uppercase;letter-spacing:.03em">
                            <th style="text-align:left;padding:9px 16px;font-weight:600">Tranzaksiya</th>
                            <th style="text-align:left;padding:9px 10px;font-weight:600">Ish</th>
                            <th style="text-align:left;padding:9px 10px;font-weight:600">Sana</th>
                            <th style="text-align:left;padding:9px 10px;font-weight:600">Status</th>
                            <th style="text-align:right;padding:9px 16px;font-weight:600">Summa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($d['txns'] as $t)
                        <tr style="border-top:1px solid #f1f5f9">
                            <td style="padding:10px 16px">
                                <div style="display:flex;align-items:center;gap:9px">
                                    <span style="flex-shrink:0;width:24px;height:24px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;
                                        background:{{ $t['dir']==='in' ? '#dcfce7' : '#fee2e2' }};color:{{ $t['dir']==='in' ? '#16a34a' : '#dc2626' }}">{{ $t['dir']==='in' ? '↓' : '↑' }}</span>
                                    <div>
                                        <div style="font-weight:600;color:#374151">{{ $t['owner'] }}</div>
                                        @if($t['number'])<div style="font-size:11px;color:#9ca3af">{{ $t['number'] }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:10px 10px">
                                <span style="font-size:11px;font-weight:600;background:#eef2ff;color:#4338ca;border-radius:5px;padding:2px 8px">{{ $t['service'] }}</span>
                            </td>
                            <td style="padding:10px 10px;color:#6b7280;font-size:12px;white-space:nowrap">{{ $t['date'] ? \Illuminate\Support\Carbon::parse($t['date'])->format('d.m.Y') : '—' }}</td>
                            <td style="padding:10px 10px">
                                @php
                                    $stColor = match($t['status']) {
                                        'tasdiqlangan'   => ['#dcfce7','#15803d'],
                                        'jarayonda'      => ['#fef3c7','#b45309'],
                                        'yechib olingan' => ['#e0e7ff','#4338ca'],
                                        default          => ['#f3f4f6','#6b7280'],
                                    };
                                @endphp
                                <span style="font-size:11px;font-weight:600;border-radius:5px;padding:2px 8px;background:{{ $stColor[0] }};color:{{ $stColor[1] }}">{{ $t['status'] }}</span>
                            </td>
                            <td style="padding:10px 16px;text-align:right;font-weight:700;color:{{ $t['dir']==='in' ? '#16a34a' : '#dc2626' }};white-space:nowrap">
                                {{ $t['dir']==='in' ? '+' : '−' }}{{ number_format($t['amount'], 0, '.', ' ') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            </div>

        </div>{{-- /body --}}
    </div>
</div>
@endif

@endif
</div>
