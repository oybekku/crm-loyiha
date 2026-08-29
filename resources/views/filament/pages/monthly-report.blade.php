<x-filament-panels::page>

<style>
/* ════ LIGHT MODE ════ */
.mr-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 8px rgba(0,0,0,.06);margin-bottom:16px;border:1px solid #f1f5f9}
.mr-stat{background:#fff;border-radius:12px;padding:18px 20px;border:1px solid #e5e7eb;text-align:center;border-left:4px solid transparent}
.mr-stat--neutral{border-left-color:#94a3b8}   .mr-stat--neutral .mr-stat-num{color:#374151}
.mr-stat--warn   {border-left-color:#f59e0b}   .mr-stat--warn    .mr-stat-num{color:#d97706}
.mr-stat--danger {border-left-color:#ef4444}   .mr-stat--danger  .mr-stat-num{color:#dc2626}
.mr-stat--success{border-left-color:#22c55e}   .mr-stat--success .mr-stat-num{color:#16a34a}
.mr-stat-num{font-size:22px;font-weight:800;line-height:1.2}
.mr-stat-lbl{font-size:11px;color:#6b7280;margin-top:5px;font-weight:500}
.mr-table{width:100%;border-collapse:collapse;font-size:13px}
.mr-table th{background:#f8fafc;padding:10px 14px;text-align:left;font-weight:700;color:#475569;border-bottom:2px solid #e2e8f0;white-space:nowrap;font-size:12px;letter-spacing:.02em;text-transform:uppercase}
.mr-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;vertical-align:middle}
.mr-table tr:last-child td{border-bottom:none}
.mr-total-row td{font-weight:700;background:#f0fdf4;border-top:2px solid #86efac;color:#15803d}
.mr-emp-row{cursor:pointer;transition:background .12s}
.mr-emp-row:hover td{background:#f0f9ff !important}
.mr-emp-toggle{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#dbeafe;border:none;cursor:pointer;transition:transform .2s;flex-shrink:0}
.mr-detail-table{width:100%;border-collapse:collapse;font-size:12px}
.mr-detail-table th{background:#f8fafc;padding:7px 10px;text-align:left;font-weight:600;color:#64748b;border-bottom:1.5px solid #e2e8f0;white-space:nowrap}
.mr-detail-table td{padding:7px 10px;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle}
.mr-detail-table tr:last-child td{border-bottom:none}
.mr-detail-table tr:hover td{background:#f8fafc}
.badge-ontime{background:#dcfce7;color:#16a34a;font-size:10px;font-weight:700;border-radius:4px;padding:2px 8px;white-space:nowrap}
.badge-late   {background:#fee2e2;color:#dc2626;font-size:10px;font-weight:700;border-radius:4px;padding:2px 8px;white-space:nowrap}
.badge-nodate {background:#f1f5f9;color:#94a3b8;font-size:10px;border-radius:4px;padding:2px 8px;white-space:nowrap}
.badge-pending{background:#fff7ed;color:#ea580c;font-size:10px;font-weight:700;border-radius:4px;padding:2px 8px;white-space:nowrap}
.badge-done   {background:#dcfce7;color:#16a34a;font-size:10px;font-weight:700;border-radius:4px;padding:2px 8px;white-space:nowrap}
.mr-warn-row{background:#fef2f2}

/* ════ DARK MODE ════ */
.dark .mr-card{background:#161b22;border-color:#21262d}
.dark .mr-stat{background:#161b22;border-color:#21262d}
.dark .mr-stat--neutral{border-left-color:#475569} .dark .mr-stat--neutral .mr-stat-num{color:#cbd5e1}
.dark .mr-stat--warn   {border-left-color:#92400e} .dark .mr-stat--warn    .mr-stat-num{color:#fbbf24}
.dark .mr-stat--danger {border-left-color:#7f1d1d} .dark .mr-stat--danger  .mr-stat-num{color:#f87171}
.dark .mr-stat--success{border-left-color:#14532d} .dark .mr-stat--success .mr-stat-num{color:#4ade80}
.dark .mr-stat-lbl{color:#6b7280}
.dark .mr-table th{background:#0d1117;color:#8b949e;border-color:#21262d}
.dark .mr-table td{border-color:#21262d;color:#c9d1d9}
.dark .mr-table tr:hover td{background:#1c2128}
.dark .mr-total-row td{background:#0d2818;border-color:#238636;color:#3fb950}
.dark .mr-emp-row:hover td{background:#1c2128 !important}
.dark .mr-emp-toggle{background:#21262d;color:#58a6ff}
.dark .mr-detail-table th{background:#0d1117;color:#8b949e;border-color:#21262d}
.dark .mr-detail-table td{border-color:#21262d;color:#c9d1d9}
.dark .mr-detail-table tr:hover td{background:#1c2128}
.dark .badge-ontime{background:#0d2818;color:#3fb950}
.dark .badge-late   {background:#2d1515;color:#f87171}
.dark .badge-nodate {background:#21262d;color:#6b7280}
.dark .badge-pending{background:#2a1d05;color:#fb923c}
.dark .badge-done   {background:#0d2818;color:#3fb950}
.dark .mr-warn-row td{background:#2d1515}
</style>

@include('filament.partials.report-tabs')

{{-- FILTER --}}
<div class="mr-card" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:14px 20px">
    <div style="display:flex;align-items:center;gap:8px">
        <svg width="18" height="18" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span style="font-size:14px;font-weight:600;color:#374151">Oy tanlang:</span>
    </div>
    <input type="month" wire:model.live="selectedMonth"
           style="border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 12px;font-size:14px;outline:none;color:#111827;background:#fff">
    <span style="font-size:13px;color:#9ca3af">
        {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}
    </span>
    <div style="margin-left:auto">
        <button wire:click="exportExcel" wire:loading.attr="disabled"
                style="display:inline-flex;align-items:center;gap:7px;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s"
                onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
            <span wire:loading.remove wire:target="exportExcel">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </span>
            <span wire:loading wire:target="exportExcel">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            </span>
            <span wire:loading.remove wire:target="exportExcel">Excel yuklab olish</span>
            <span wire:loading wire:target="exportExcel">Tayyorlanmoqda...</span>
        </button>
        <style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
    </div>
</div>

{{-- ══ YILLIK NORMA JADVALI ══ --}}
<style>
.nrm-wrap{overflow-x:auto}
.nrm-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
.nrm-tbl th{background:#f8fafc;padding:8px 6px;font-weight:700;color:#475569;border-bottom:2px solid #e2e8f0;text-align:center;white-space:nowrap;font-size:11px}
.nrm-tbl th.l{text-align:left;padding-left:12px}
.nrm-tbl td{padding:6px;border-bottom:1px solid #f1f5f9;text-align:center;vertical-align:middle}
.nrm-tbl tr:last-child td{border-bottom:none}
.nrm-emp{display:flex;align-items:center;gap:8px;min-width:150px;text-align:left;padding-left:6px}
.nrm-av{width:26px;height:26px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff}
.nrm-nm{font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap}
.nrm-cell{border-radius:7px;font-weight:800;font-size:12px;padding:7px 4px;min-width:38px;display:inline-block;width:100%}
.nrm-ok{background:#dcfce7;color:#15803d}
.nrm-no{background:#fee2e2;color:#dc2626}
.nrm-na{background:#f1f5f9;color:#94a3b8;font-weight:600}
.nrm-chip{cursor:pointer;font-weight:800;font-size:13px;color:#c2410c;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:5px 12px;display:inline-block;min-width:40px}
.nrm-chip:hover{background:#ffedd5}
.nrm-chip.empty{color:#94a3b8;background:#f8fafc;border-color:#e2e8f0}
.nrm-input{width:52px;text-align:center;font-size:13px;font-weight:800;border:1.5px solid #f97316;border-radius:7px;padding:4px;outline:none;color:#111}
.nrm-save{width:28px;height:28px;border:none;border-radius:7px;background:#16a34a;color:#fff;font-size:14px;cursor:pointer;line-height:1}
.nrm-sum{font-size:11.5px;font-weight:700;white-space:nowrap;border-radius:20px;padding:4px 10px;display:inline-block}
.nrm-sum.good{background:#dcfce7;color:#15803d} .nrm-sum.warn{background:#fef3c7;color:#b45309} .nrm-sum.na{background:#f1f5f9;color:#94a3b8}
.nrm-yr{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:4px 6px}
.nrm-yr button{border:none;background:none;color:#64748b;font-size:16px;cursor:pointer;padding:2px 8px;border-radius:6px}
.nrm-yr b{font-size:14px;font-weight:800;min-width:48px;text-align:center;color:#111827}
/* dark */
.dark .nrm-tbl th{background:#0d1117;color:#8b949e;border-color:#21262d}
.dark .nrm-tbl td{border-color:#21262d}
.dark .nrm-nm{color:#c9d1d9}
.dark .nrm-ok{background:#0d2818;color:#3fb950}
.dark .nrm-no{background:#2d1515;color:#f87171}
.dark .nrm-na{background:#161b22;color:#6b7280}
.dark .nrm-chip{background:#1c1408;color:#fb923c;border-color:#7c2d12}
.dark .nrm-chip.empty{background:#161b22;color:#6b7280;border-color:#21262d}
.dark .nrm-input{background:#0d1117;color:#eee}
.dark .nrm-yr{background:#161b22;border-color:#21262d}
.dark .nrm-yr b{color:#eee}
.dark .nrm-sum.good{background:#0d2818;color:#3fb950} .dark .nrm-sum.warn{background:#2a1d05;color:#fbbf24} .dark .nrm-sum.na{background:#161b22;color:#6b7280}
</style>
@php
    $nrmMonths = ['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'];
    $nrmPalette = ['#f97316','#3b82f6','#8b5cf6','#14b8a6','#eab308','#ec4899','#ef4444','#0ea5e9','#22c55e','#a855f7'];
@endphp
{{-- Hodimlar oylik norma bajarilishi — vaqtincha yashirilgan --}}
@if(false)
<div class="mr-card">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px">
        <div>
            <div style="font-size:16px;font-weight:800;color:#0f172a" class="dark:text-white">📊 Hodimlar oylik norma bajarilishi</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px">Har oy bajarilgan ish soni. <b style="color:#16a34a">Yashil</b> — norma bajarilgan, <b style="color:#dc2626">qizil</b> — bajarilmagan. Normani o'zgartirish uchun raqamga bosing.</div>
        </div>
        <div class="nrm-yr">
            <button wire:click="normYearShift(-1)" title="Oldingi yil">‹</button>
            <b>{{ $normYear }}</b>
            <button wire:click="normYearShift(1)" title="Keyingi yil">›</button>
        </div>
    </div>

    @if(count($normRows) === 0)
        <div style="text-align:center;color:#94a3b8;padding:24px;font-size:13px">Hozircha hodim yo'q.</div>
    @else
    <div class="nrm-wrap">
        <table class="nrm-tbl">
            <thead>
                <tr>
                    <th class="l">Xodim</th>
                    <th>Norma</th>
                    @foreach($nrmMonths as $mn)<th>{{ $mn }}</th>@endforeach
                    <th>Umumiylik</th>
                </tr>
            </thead>
            <tbody>
                @foreach($normRows as $row)
                @php $u = $row['user']; $clr = $nrmPalette[$u->id % count($nrmPalette)]; @endphp
                <tr>
                    <td>
                        <div class="nrm-emp">
                            <div class="nrm-av" style="background:{{ $clr }}">{{ mb_strtoupper(mb_substr($u->name,0,2)) }}</div>
                            <span class="nrm-nm">{{ $u->name }}</span>
                        </div>
                    </td>
                    {{-- Norma (bosilsa tahrirlanadi) --}}
                    <td x-data="{e:false}" @click.outside="e=false">
                        <div x-show="!e" @click="e=true" class="nrm-chip {{ $row['norm']>0 ? '' : 'empty' }}" title="Norma kiritish uchun bosing">
                            {{ $row['norm']>0 ? $row['norm'] : '—' }}
                        </div>
                        <div x-show="e" x-cloak style="display:inline-flex;align-items:center;gap:4px">
                            <input type="number" min="0" class="nrm-input"
                                   wire:model="normEdits.{{ $u->id }}"
                                   @keydown.enter="$wire.saveNorm({{ $u->id }}); e=false">
                            <button class="nrm-save" wire:click="saveNorm({{ $u->id }})" @click="e=false" title="Saqlash">✓</button>
                        </div>
                    </td>
                    {{-- 12 oy --}}
                    @for($m=1;$m<=12;$m++)
                        @php $c = $row['months'][$m]; @endphp
                        <td>
                            @if($c['met'] === null)
                                <span class="nrm-cell nrm-na">{{ $c['count'] }}</span>
                            @elseif($c['met'])
                                <span class="nrm-cell nrm-ok">{{ $c['count'] }}</span>
                            @else
                                <span class="nrm-cell nrm-no">{{ $c['count'] }}</span>
                            @endif
                        </td>
                    @endfor
                    {{-- Umumiylik --}}
                    <td>
                        @if($row['norm'] > 0)
                            <span class="nrm-sum {{ $row['met_count'] >= 6 ? 'good' : 'warn' }}">{{ $row['met_count'] }}/12 oy bajarilgan</span>
                        @else
                            <span class="nrm-sum na">norma belgilanmagan</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endif

{{-- ══ TO'LANISHI KERAK (hodim x oy, yillik qoldiq) ══ --}}
<style>
.pay-due{background:#fef3c7!important;color:#b45309!important;cursor:pointer;border:none}
.pay-due:hover{background:#fde68a!important}
.pay-clear{background:#dcfce7!important;color:#15803d!important}
.nrm-cell-empty{background:transparent!important;border-radius:7px;padding:7px 4px;min-width:38px;display:inline-block;width:100%;box-sizing:border-box}
.pay-all-btn{font-size:11px;font-weight:700;background:#2563eb;color:#fff;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;white-space:nowrap}
.pay-all-btn:hover{background:#1d4ed8}
.dark .pay-due{background:#2a1d05!important;color:#fbbf24!important}
.dark .pay-due:hover{background:#3b2a08!important}
.dark .pay-clear{background:#0d2818!important;color:#3fb950!important}
.toggle-months-btn{font-size:12px;font-weight:700;background:#f3f4f6;color:#374151;border:1.5px solid #e5e7eb;border-radius:8px;padding:6px 13px;cursor:pointer;white-space:nowrap}
.toggle-months-btn:hover{background:#e5e7eb}
.fire-icon-btn{font-size:11px;background:#fee2e2;color:#dc2626;border:none;border-radius:20px;width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;padding:0;line-height:1}
.fire-icon-btn:hover{background:#fecaca}
.dark .fire-icon-btn{background:#2d1515;color:#f87171}
.role-badge{font-size:10px;font-weight:700;border-radius:20px;padding:2px 8px;cursor:pointer;white-space:nowrap;border:none;letter-spacing:.02em}
.role-badge:hover{filter:brightness(0.95)}
.role-admin{background:#ede9fe;color:#6d28d9}
.role-menejer{background:#dbeafe;color:#1d4ed8}
.role-bajaruvchi{background:#f1f5f9;color:#475569}
.role-hisobchi{background:#ccfbf1;color:#0f766e}
.dark .role-admin{background:#2e1065;color:#c4b5fd}
.dark .role-menejer{background:#1e3a8a;color:#93c5fd}
.dark .role-bajaruvchi{background:#1e293b;color:#94a3b8}
.dark .role-hisobchi{background:#0f2e2a;color:#5eead4}
.add-employee-btn{font-size:12px;font-weight:700;background:#0f172a;color:#fff;border:none;border-radius:8px;padding:7px 14px;cursor:pointer;white-space:nowrap;text-decoration:none;display:inline-block}
.add-employee-btn:hover{background:#1e293b}
.departed-btn{font-size:12px;font-weight:700;background:#f3f4f6;color:#374151;border:1.5px solid #e5e7eb;border-radius:8px;padding:6px 13px;cursor:pointer;white-space:nowrap}
.departed-btn:hover{background:#e5e7eb}
.oklad-badge{font-size:10px;font-weight:700;border-radius:20px;padding:2px 8px;cursor:pointer;white-space:nowrap;border:none;letter-spacing:.02em;background:#fdf4ff;color:#a21caf}
.oklad-badge:hover{filter:brightness(0.95)}
.dark .oklad-badge{background:#3b0764;color:#e9d5ff}
.restore-btn{font-size:11px;font-weight:700;background:#dcfce7;color:#15803d;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;white-space:nowrap}
.restore-btn:hover{background:#bbf7d0}
.dark .restore-btn{background:#0d2818;color:#3fb950}
</style>
<div class="mr-card" x-data="{ hideMonths: false }">
    <div style="margin-bottom:12px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div>
            <div style="font-size:16px;font-weight:800;color:#0f172a" class="dark:text-white">💰 To'lanishi kerak</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px">Har hodim uchun oy-oy qoldiq (mijoz to'lagan ulushga mos komissiya + oklad, minus berilgan ish haqi) — {{ $normYear }}-yil. Sariq katakchani bosib, o'sha oy uchun to'lang.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="toggle-months-btn" @click="hideMonths = !hideMonths" x-text="hideMonths ? '👁 Oylarni ko\'rsatish' : '🙈 Oylarni yashirish'"></button>
            <button class="departed-btn" wire:click="openDeparted">🚪 Bo'shagan xodimlar ({{ count($departedRows) }})</button>
            <a href="{{ \App\Filament\Resources\UserResource::getUrl('create') }}" wire:navigate class="add-employee-btn">+ Hodim qo'shish</a>
        </div>
    </div>
    @if(count($payableRows) === 0)
        <div style="text-align:center;color:#94a3b8;padding:24px;font-size:13px">Hodimlar topilmadi.</div>
    @else
    <div class="nrm-wrap">
        <table class="nrm-tbl">
            <thead>
                <tr>
                    <th class="l">Hodim</th>
                    @foreach($payableVisibleMonths as $m)<th x-show="!hideMonths">{{ $nrmMonths[$m-1] }}</th>@endforeach
                    <th>Jami qoldiq</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($payableRows as $row)
                <tr>
                    <td>
                        <div class="nrm-emp" style="min-width:150px;flex-direction:column;align-items:flex-start;gap:3px">
                            <span class="nrm-nm">{{ $row['user']->name }}</span>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center">
                                <button class="role-badge role-{{ $row['user']->role }}" wire:click="openRoleEditor({{ $row['user']->id }})" title="Statusni o'zgartirish">{{ $row['user']->display_title }}</button>
                                <button class="oklad-badge" wire:click="openSalaryBaseEditor({{ $row['user']->id }})" title="Belgilangan oylik (oklad)">{{ $row['user']->base_salary > 0 ? number_format($row['user']->base_salary, 0, '.', ' ') . " so'm" : 'Oklad: 0' }}</button>
                                <button class="fire-icon-btn" wire:click="toggleActive({{ $row['user']->id }})" wire:confirm="{{ $row['user']->name }}ni ishdan bo'shatishni tasdiqlaysizmi? U ro'yxatdan yashiriladi, ma'lumotlari 'Bo'shagan xodimlar' oynasida qoladi." title="Ishdan bo'shatish">🚪</button>
                            </div>
                        </div>
                    </td>
                    @foreach($payableVisibleMonths as $m)
                        @php $cell = $row['months'][$m]; @endphp
                        <td x-show="!hideMonths">
                            @if($cell['remaining'] > 0)
                                <span class="nrm-cell pay-due" wire:click="openSalaryPayModalForMonth({{ $row['user']->id }}, '{{ $cell['month_str'] }}', {{ $cell['remaining'] }})" title="{{ $cell['month_str'] }} uchun to'lash">{{ number_format($cell['remaining'], 0, '.', ' ') }}</span>
                            @elseif($cell['calc'] > 0)
                                <span class="nrm-cell pay-clear" title="To'liq to'langan">0</span>
                            @else
                                <span class="nrm-cell-empty"></span>
                            @endif
                        </td>
                    @endforeach
                    <td><span class="nrm-sum {{ $row['year_remaining']>0 ? 'warn' : 'good' }}">{{ number_format($row['year_remaining'], 0, '.', ' ') }}</span></td>
                    <td style="white-space:nowrap">
                        @if($row['year_remaining'] > 0)
                        <button class="pay-all-btn" wire:click="payAllRemainingForUser({{ $row['user']->id }})" wire:confirm="{{ $row['user']->name }} uchun shu yildagi barcha qoldiq oylarni ({{ number_format($row['year_remaining'], 0, '.', ' ') }} so'm) to'lashni tasdiqlaysizmi?">Hammasini to'la</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- UMUMIY STATISTIKA (yashirilgan) --}}
@if(false)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:18px">
    <div class="mr-stat mr-stat--neutral">
        <div class="mr-stat-num">{{ $projectsTotal }}</div>
        <div class="mr-stat-lbl">Bajarilgan loyihalar</div>
    </div>
    <div class="mr-stat mr-stat--neutral">
        <div class="mr-stat-num">{{ number_format($totalServicesSum, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Tugatilgan ish (so'm)</div>
    </div>
    <div class="mr-stat mr-stat--warn">
        <div class="mr-stat-num">{{ number_format($totalCommissions, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Hodimlar ulushi jami</div>
    </div>
    <div class="mr-stat mr-stat--success">
        <div class="mr-stat-num">{{ number_format($firmIncome, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Firma sof daromadi</div>
    </div>
</div>
@endif

{{-- HODIMLAR JADVALI --}}
<div class="mr-card">
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:14px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Hodimlar hisoboti
        <span style="font-size:12px;font-weight:400;color:#9ca3af">(satr ustiga bosib batafsil ko'rish)</span>
    </div>

    @if(count($userStats) === 0)
    <div style="text-align:center;padding:32px;color:#9ca3af;font-size:13px">
        <div style="font-size:28px;margin-bottom:8px">📊</div>
        Bu oyda xizmatga biriktirilgan hodim topilmadi
    </div>
    @else
    <table class="mr-table">
        <thead>
            <tr>
                <th style="width:28px"></th>
                <th>#</th>
                <th>Hodim</th>
                <th style="text-align:center" title="Jami biriktirilgan loyihalar — tugallangan va hali kutayotgani birga">Loyihalar (jami)</th>
                <th style="text-align:right" title="Barcha biriktirilgan ishlarning narxi — tugallangan va kutayotgani birga">Xizmatlar jami</th>
                <th style="text-align:right" title="Barcha biriktirilgan ishlarning komissiyasi — tugallangan va kutayotgani birga">Hisoblangan</th>
                <th style="text-align:right;color:#dc2626" title="Hodimga mijoz to'lagan ulushidan ko'proq berilgan pul (bo'lsa)">Ortiqcha to'langan</th>
                <th style="text-align:right;color:#16a34a" title="Mijoz to'lagan ulushga mutanosib ochilgan summa — hodimga real to'lash mumkin bo'lgan pul. Mijoz hali to'lamagan qismi bu yerga kirmaydi.">To'lanishi kerak</th>
                <th style="text-align:right;color:#2563eb">To'landi</th>
                <th style="text-align:center;color:#f97316">Kutayotgan</th>
            </tr>
        </thead>
        @php $i = 1; @endphp
        @foreach($userStats as $uid => $stat)
            @php
                $sTotal         = $stat['services_total'];
                $comm           = $stat['commission'];
                $totalComm      = $stat['total_commission'] ?? $comm;
                $clientPayable  = $stat['client_payable']   ?? 0;
                $paidManual     = $stat['paid_manual']   ?? 0;
                $ontime         = $stat['ontime_count']  ?? 0;
                $late           = $stat['late_count']    ?? 0;
                $nodate         = count($stat['services']) - $ontime - $late;
                $pendingCnt     = $stat['pending_count'] ?? 0;
                $pendingSum     = $stat['pending_sum']   ?? 0;
                $effRate        = \App\Services\EmployeePayableService::rateFor($stat['user'], $this->selectedMonth);
            @endphp

            <tbody x-data="{open:false}">

            {{-- Summary row --}}
            <tr class="mr-emp-row" @click="open=!open" style="border-bottom:none">
                <td style="padding:10px 8px 10px 14px">
                    <button class="mr-emp-toggle" :style="open ? 'background:#bfdbfe;transform:rotate(90deg)' : ''">
                        <svg width="12" height="12" fill="none" stroke="#2563eb" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </td>
                <td style="color:#9ca3af">{{ $i++ }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div>
                            <div style="font-weight:600;color:#111827">{{ $stat['user']->name }}</div>
                            <div style="font-size:11px;color:#9ca3af">
                                {{ $stat['user']->role_name ?? ucfirst($stat['user']->role) }}
                                @if(!in_array($stat['user']->role, ['admin', 'menejer']))
                                ·
                                <span wire:click.stop="openRateEditor({{ $uid }})"
                                      style="color:#2563eb;cursor:pointer;border-bottom:1px dashed #93c5fd"
                                      title="O'zgartirish uchun bosing">
                                    {{ rtrim(rtrim(number_format($effRate, 2, '.', ''), '0'), '.') }}% ulush ✎
                                </span>
                                @endif
                            </div>
                        </div>
                        <button wire:click.stop="openDetailModal({{ $uid }})"
                                style="flex-shrink:0;display:inline-flex;align-items:center;gap:4px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            To'liq ma'lumot
                        </button>
                    </div>
                </td>
                <td style="text-align:center">
                    <span style="font-size:16px;font-weight:800;color:#374151">{{ $stat['project_count'] + $pendingCnt }}</span>
                    <div style="font-size:9px;color:#9ca3af;margin-top:1px">
                        @if($pendingCnt > 0)
                        {{ $stat['project_count'] }} tugallangan · {{ $pendingCnt }} kutayotgan
                        @else
                        hammasi tugallangan
                        @endif
                    </div>
                </td>
                <td style="text-align:right;font-weight:600">
                    {{ number_format($sTotal + $pendingSum, 0, '.', ' ') }} so'm
                    @if($pendingCnt > 0)
                    <div style="font-size:9px;color:#9ca3af;margin-top:1px" title="Shundan tugallangan ishlar narxi">tugallangan: {{ number_format($sTotal,0,'.',' ') }}</div>
                    @endif
                </td>
                <td style="text-align:right">
                    <span style="font-weight:700;color:#d97706">{{ number_format($totalComm, 0, '.', ' ') }} so'm</span>
                    @if($pendingCnt > 0)
                    <div style="font-size:9px;color:#9ca3af;margin-top:1px" title="Shundan tugallangan ishlar komissiyasi">tugallangan: {{ number_format($comm,0,'.',' ') }}</div>
                    @endif
                </td>
                {{-- Ortiqcha to'langan — avval "Jarima" (qo'lda kiritiladigan, hech
                     qayerda saqlanmaydigan) maydon bo'lgan, endi buning o'rniga
                     hodimga mijoz to'lagan ulushidan ko'proq berilgan pul avtomatik
                     ko'rsatiladi (rahbariyat buni oldindan ko'rishi uchun). --}}
                <td style="text-align:right">
                    @php $paidTotalRow = $stat['paid_total'] ?? 0; $overpaid = $stat['overpaid'] ?? 0; @endphp
                    @if($overpaid > 0)
                    <span style="font-weight:700;color:#dc2626">{{ number_format($overpaid, 0, '.', ' ') }} so'm</span>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
                {{-- Mijoz to'lagan ulushga mutanosib "to'lanishi kerak" summa — real
                     to'lash mumkin bo'lgan pul, allaqachon to'langani ayirilgan
                     holda (aks holda "Ortiqcha to'langan" bilan bir vaqtda
                     ko'rsatilib chalkashlik tug'dirardi). --}}
                <td style="text-align:right">
                    @php $payableRemaining = $stat['payable_remaining'] ?? max(0, $clientPayable - $paidTotalRow); @endphp
                    @if($payableRemaining > 0)
                    <span style="font-weight:700;color:#16a34a">{{ number_format($payableRemaining, 0, '.', ' ') }} so'm</span>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
                {{-- To'landi (haqiqatda to'langan summa) --}}
                <td style="text-align:right">
                    @if($paidTotalRow > 0)
                    <span style="font-weight:700;color:#2563eb">{{ number_format($paidTotalRow, 0, '.', ' ') }} so'm</span>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
                {{-- Kutayotgan ishlar --}}
                <td style="text-align:center">
                    @if($pendingCnt > 0)
                    <div style="font-size:12px;font-weight:700;color:#f97316">{{ $pendingCnt }} ta</div>
                    <div style="font-size:10px;color:#f97316;font-weight:600">{{ number_format($stat['pending_commission'] ?? 0, 0, '.', ' ') }} so'm</div>
                    <div style="font-size:9px;color:#d1d5db">({{ rtrim(rtrim(number_format($effRate, 2, '.', ''), '0'), '.') }}% ulush)</div>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
            </tr>

            {{-- Detail row --}}
<tr x-show="open" x-cloak style="display:none">
                <td colspan="11" style="padding:0;background:#f8fafc;border-bottom:2px solid #e2e8f0">
                    <div style="padding:12px 16px">
                        {{-- Per-employee mini stats --}}
                        <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap">
                            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#374151">{{ count($stat['services']) }}</div>
                                <div style="font-size:10px;color:#9ca3af;margin-top:2px" title="Kutayotgan (hali tugallanmagan) ishlar bu songa kirmaydi">Tugallangan xizmatlar</div>
                            </div>
                            @if($ontime > 0)
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#16a34a">{{ $ontime }}</div>
                                <div style="font-size:10px;color:#16a34a;margin-top:2px">O'z vaqtida</div>
                            </div>
                            @endif
                            @if($late > 0)
                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#dc2626">{{ $late }}</div>
                                <div style="font-size:10px;color:#dc2626;margin-top:2px">Kechikkan</div>
                            </div>
                            @endif
                            @if($nodate > 0)
                            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#9ca3af">{{ $nodate }}</div>
                                <div style="font-size:10px;color:#9ca3af;margin-top:2px">Muddatsiz</div>
                            </div>
                            @endif
                            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#d97706">{{ number_format($comm, 0, '.', ' ') }}</div>
                                <div style="font-size:10px;color:#d97706;margin-top:2px">Hisoblangan ulush (so'm)</div>
                            </div>
                        </div>

                        {{-- Barcha ishlar (tugallangan + kutayotgan) — bitta ro'yxatda --}}
                        @php $allItems = $stat['all_items'] ?? []; @endphp
                        <div style="overflow-x:auto;border-radius:8px;border:1px solid #e2e8f0">
                        <table class="mr-detail-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Loyiha raqami / FISH</th>
                                    <th>Xizmat turi</th>
                                    <th style="text-align:right">Narx</th>
                                    <th style="text-align:right">Ulush</th>
                                    <th style="text-align:center">Ochilgan</th>
                                    <th style="text-align:center">Tugatilgan</th>
                                    <th style="text-align:center">Holat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allItems as $j => $it)
                                <tr>
                                    <td style="color:#9ca3af;font-size:11px">{{ $j + 1 }}</td>
                                    <td>
                                        <div style="font-weight:700;color:#111827;font-family:monospace;font-size:12px">{{ $it['project_number'] }}</div>
                                        <div style="font-size:12px;color:#374151;margin-top:1px">{{ $it['owner_name'] }}</div>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:600;border-radius:4px;padding:2px 7px">
                                            {{ $it['service_label'] ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;font-weight:600;white-space:nowrap">
                                        {{ number_format($it['price'], 0, '.', ' ') }}
                                    </td>
                                    <td style="text-align:right;white-space:nowrap">
                                        @if($it['share'] > 0)
                                        <span style="color:#d97706;font-weight:700">{{ number_format($it['share'], 0, '.', ' ') }}</span>
                                        @else
                                        <span style="color:#d1d5db">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;font-size:11px;color:#6b7280">
                                        {{ $it['opened_at'] ? \Carbon\Carbon::parse($it['opened_at'])->format('d.m.Y') : '—' }}
                                        @php
                                            $poAt = $it['project_opened_at'] ?? null;
                                            $diffMonth = $poAt && $it['opened_at'] && \Carbon\Carbon::parse($poAt)->format('Y-m') !== \Carbon\Carbon::parse($it['opened_at'])->format('Y-m');
                                        @endphp
                                        @if($diffMonth)
                                        <div style="color:#c026d3;font-size:9px" title="Loyihaning o'zi bu ishdan oldin ochilgan">🔗 loyiha: {{ \Carbon\Carbon::parse($poAt)->format('d.m.Y') }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;font-size:11px;color:#6b7280">
                                        {{ $it['completed_at'] ? \Carbon\Carbon::parse($it['completed_at'])->format('d.m.Y') : '—' }}
                                    </td>
                                    <td style="text-align:center;white-space:nowrap">
                                        @if($it['is_done'])
                                        <span class="badge-done">✓ Tugallandi</span>
                                        @elseif($it['is_late'])
                                        <span class="badge-late">Kechikkan {{ $it['late_days'] }} kun</span>
                                        @elseif(($it['days_left'] ?? null) !== null && $it['days_left'] <= 3)
                                        <span class="badge-pending">{{ $it['days_left'] }} kun qoldi</span>
                                        @else
                                        <span class="badge-pending">Kutayotgan</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                <tr style="background:#fafafa;font-weight:700;border-top:2px solid #e2e8f0">
                                    <td colspan="3" style="text-align:right;color:#374151">Jami ({{ count($allItems) }} ta):</td>
                                    <td style="text-align:right">{{ number_format(collect($allItems)->sum('price'), 0, '.', ' ') }}</td>
                                    <td style="text-align:right;color:#d97706">{{ number_format(collect($allItems)->sum('share'), 0, '.', ' ') }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </td>
            </tr>

        </tbody>

        @endforeach
        <tbody>
        <tr class="mr-total-row">
            <td colspan="4" style="text-align:right">Jami:</td>
            <td style="text-align:right">{{ number_format($totalWorkSum, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#d97706">{{ number_format($totalWorkCommission, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#dc2626">{{ $totalOverpaid > 0 ? number_format($totalOverpaid, 0, '.', ' ') . " so'm" : '—' }}</td>
            <td style="text-align:right;color:#16a34a">{{ number_format($totalPayableRemaining, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#2563eb">{{ number_format($totalPaidOut, 0, '.', ' ') }} so'm</td>
            <td></td>{{-- Kutayotgan --}}
        </tr>
        </tbody>
    </table>
    @endif
</div>

{{-- XODIMLARGA BIRIKTIRILGAN ISHLAR — vaqtincha yashirilgan.
     Faqat LOYIHA shu oyda ochilgan bo'lsa ko'rinadi (ish qachon biriktirilgan/
     tugatilganidan qat'i nazar) — masalan iyunda ochilgan loyihaga iyulda
     biriktirilgan ish, shu jadvalda IYUN oyi hisobotida turadi. --}}
@if(false)
<div class="mr-card">
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Xodimlarga biriktirilgan ishlar
        <span style="font-size:12px;font-weight:400;color:#9ca3af">(satr ustiga bosib ko'ring)</span>
    </div>
    <div style="font-size:12px;color:#9ca3af;margin-bottom:14px">Har bir ish — loyihasi <b>shu oyda ochilgan</b> bo'lsa ko'rinadi, ishning o'zi qachon biriktirilgan/tugatilganidan qat'i nazar.</div>

    @if(count($assignedByEmployee) === 0)
    <div style="text-align:center;color:#9ca3af;padding:24px;font-size:13px">Bu oyda ochilgan loyihalarga biriktirilgan ish yo'q</div>
    @else
    <table class="mr-table" style="width:100%">
        @foreach($assignedByEmployee as $grp)
        <tbody x-data="{open:false}">
        <tr class="mr-emp-row" @click="open=!open">
            <td style="width:28px;padding:10px 8px 10px 14px">
                <button class="mr-emp-toggle" :style="open ? 'background:#bfdbfe;transform:rotate(90deg)' : ''">
                    <svg width="12" height="12" fill="none" stroke="#2563eb" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </td>
            <td>
                <div style="font-weight:600;color:#111827">{{ $grp['user']->name }}</div>
                <div style="font-size:11px;color:#9ca3af">
                    {{ $grp['user']->role_name ?? ucfirst($grp['user']->role) }}
                    @if($grp['user']->commission_rate)
                    · <span style="color:#2563eb">{{ $grp['user']->commission_rate }}% ulush</span>
                    @endif
                </div>
            </td>
            <td style="text-align:center">
                <span style="font-size:16px;font-weight:800;color:#374151">{{ $grp['count'] }}</span>
                <div style="font-size:10px;color:#9ca3af">ish · {{ $grp['unique_projects'] }} loyiha</div>
                @if($grp['duplicate_count'] > 0)
                <div style="font-size:10px;color:#dc2626;font-weight:700" title="Bir xil loyihada bir xil xizmat turi bir necha marta yozilgan">⚠️ {{ $grp['duplicate_count'] }} ta dublikat</div>
                @endif
            </td>
            <td style="text-align:right">{{ number_format($grp['total_price'], 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;padding-right:14px">
                <span style="font-weight:800;color:#d97706;font-size:14px">{{ number_format($grp['total_share'], 0, '.', ' ') }} so'm</span>
                <div style="font-size:10px;color:#9ca3af">jami ulush</div>
            </td>
        </tr>
        <tr x-show="open" x-cloak style="display:none">
            <td colspan="5" style="padding:0;background:#f8fafc;border-bottom:2px solid #e2e8f0">
                <div style="padding:12px 16px;overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead>
                        <tr>
                            <th style="padding:6px 10px;text-align:left;color:#9a3412;font-weight:600">#</th>
                            <th style="padding:6px 10px;text-align:left;color:#9a3412;font-weight:600">Loyiha / FISH</th>
                            <th style="padding:6px 10px;text-align:left;color:#9a3412;font-weight:600">Xizmat</th>
                            <th style="padding:6px 10px;text-align:right;color:#9a3412;font-weight:600">Narx</th>
                            <th style="padding:6px 10px;text-align:right;color:#9a3412;font-weight:600">Ulush</th>
                            <th style="padding:6px 10px;text-align:center;color:#9a3412;font-weight:600">Biriktirilgan</th>
                            <th style="padding:6px 10px;text-align:center;color:#9a3412;font-weight:600">Holat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grp['items'] as $j => $aw)
                        <tr style="border-bottom:1px solid #fef3c7">
                            <td style="padding:6px 10px;color:#9ca3af;font-size:11px">{{ $j + 1 }}</td>
                            <td style="padding:6px 10px">
                                <div style="font-weight:600;font-size:11px;font-family:monospace;color:#374151">{{ $aw['project_number'] }}</div>
                                <div style="font-size:11px;color:#6b7280">{{ $aw['owner_name'] }}</div>
                            </td>
                            <td style="padding:6px 10px;color:#374151">{{ $aw['service_label'] ?? '—' }}</td>
                            <td style="padding:6px 10px;text-align:right;color:#111827">{{ number_format($aw['price'], 0, '.', ' ') }}</td>
                            <td style="padding:6px 10px;text-align:right;font-weight:700;color:#d97706">{{ number_format($aw['share'], 0, '.', ' ') }}</td>
                            <td style="padding:6px 10px;text-align:center;font-size:11px;color:#6b7280">
                                {{ $aw['opened_at'] ? \Carbon\Carbon::parse($aw['opened_at'])->format('d.m.Y') : '—' }}
                            </td>
                            <td style="padding:6px 10px;text-align:center">
                                @if($aw['is_done'])
                                    <span class="badge-done">✓ Tugallandi</span>
                                @elseif($aw['is_late'])
                                    <span class="badge-late">{{ $aw['late_days'] }} kun kechikdi</span>
                                @else
                                    <span class="badge-pending">Kutayotgan</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </td>
        </tr>
        </tbody>
        @endforeach
        <tfoot>
            <tr class="mr-total-row" style="background:#f0fdf4;border-top:2px solid #86efac;font-weight:800">
                <td colspan="3" style="padding:10px 14px;text-align:right;color:#374151">Jami ({{ collect($assignedByEmployee)->sum('count') }} ta ish):</td>
                <td style="padding:10px;text-align:right">{{ number_format(collect($assignedByEmployee)->sum('total_price'), 0, '.', ' ') }} so'm</td>
                <td style="padding:10px 14px;text-align:right;color:#d97706">{{ number_format(collect($assignedByEmployee)->sum('total_share'), 0, '.', ' ') }} so'm</td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>
@endif

{{-- FIRMA HISOBOTI (yashirilgan) --}}
@if(false)
<div class="mr-card">
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
        Firma hisoboti
        <span style="font-size:12px;font-weight:400;color:#9ca3af">{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px">
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#15803d">{{ $projectsTotal }}</div>
            <div style="font-size:11px;color:#16a34a;margin-top:4px;font-weight:500">Bajarilgan loyihalar</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:22px;font-weight:800;color:#111827">{{ number_format($totalServicesSum, 0, '.', ' ') }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:4px;font-weight:500">Tugatilgan ish ({{ $toliqCount + $qismanCount }})</div>
            @php
                $arxivHodimlar = collect($userStats)
                    ->filter(fn($s) => (float)($s['commission'] ?? 0) > 0)
                    ->sortByDesc('commission');
            @endphp
            @if($arxivHodimlar->count() > 0 || $firmIncome > 0)
            <div style="margin-top:8px;border-top:1px dashed #e2e8f0;padding-top:8px;display:flex;flex-direction:column;gap:3px;text-align:left">
                @foreach($arxivHodimlar as $s)
                <div style="display:flex;justify-content:space-between;font-size:11px">
                    <span style="color:#92400e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">👷 {{ $s['user']->name }}</span>
                    <span style="font-weight:700;color:#d97706">{{ number_format($s['commission'], 0, '.', ' ') }}</span>
                </div>
                @endforeach
                <div style="display:flex;justify-content:space-between;font-size:11px;border-top:1px dashed #e2e8f0;padding-top:4px;margin-top:2px">
                    <span style="color:#065f46;font-weight:600">🏢 Firma</span>
                    <span style="font-weight:800;color:#059669">{{ number_format($firmIncome, 0, '.', ' ') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;border-top:2px solid #e2e8f0;padding-top:5px;margin-top:3px">
                    <span style="color:#111827;font-weight:700">Jami</span>
                    <span style="font-weight:800;color:#111827">{{ number_format($toliqTugatilgan, 0, '.', ' ') }}</span>
                </div>
            </div>
            @endif
        </div>
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:22px;font-weight:800;color:#d97706">{{ number_format($totalCommissions, 0, '.', ' ') }}</div>
            <div style="font-size:11px;color:#d97706;margin-top:4px;font-weight:500">Hodimlar ulushi (so'm)</div>
        </div>
        <div style="background:#ecfdf5;border:2px solid #34d399;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:900;color:#059669">{{ number_format($firmIncome, 0, '.', ' ') }}</div>
            <div style="font-size:11px;color:#059669;margin-top:4px;font-weight:700">Firma sof daromadi (so'm)</div>
        </div>
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                <div style="font-size:28px;font-weight:900;color:#ea580c">{{ $pendingProjectsCount }}</div>
                <div style="font-size:12px;color:#ea580c;font-weight:600">Qilinmagan<br>loyihalar</div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:#6b7280">Umumiy summa</span>
                <span style="font-weight:700;color:#374151">{{ number_format($allProjectsSum, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:#6b7280">Qilinmagan jami summa</span>
                <span style="font-weight:700;color:#9a3412">{{ number_format($pendingProjectsSum, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:#6b7280">To'langan</span>
                <span style="font-weight:700;color:#16a34a">{{ number_format($pendingProjectsPaid, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px">
                <span style="color:#6b7280">Qolgan qarz</span>
                <span style="font-weight:700;color:#dc2626">{{ number_format($pendingProjectsDebt, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="background:#e5e7eb;border-radius:4px;height:6px;overflow:hidden">
                <div style="height:100%;background:#f97316;border-radius:4px;width:{{ $pendingProjectsPct }}%"></div>
            </div>
            <div style="font-size:11px;color:#ea580c;margin-top:4px;font-weight:600;text-align:right">{{ $pendingProjectsPct }}% to'langan</div>

            <div style="margin-top:10px;border-top:1px dashed #fed7aa;padding-top:8px">
                <div style="display:flex;justify-content:space-between;font-size:12px">
                    <span style="color:#6b7280">Umumiy loyihalar</span>
                    <span style="font-weight:700;color:#374151">{{ $allProjectsCount }} ta</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Taxminiy taqsimot — alohida karta (yashirilgan) --}}
    @if(false)
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px;margin-top:0">
        <div style="font-size:13px;font-weight:700;color:#92400e;margin-bottom:12px">~ Taxminiy taqsimot (faol loyihalar bo'yicha)</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px;text-align:center">
                <div style="font-size:18px;font-weight:800;color:#d97706">{{ number_format($pendingWorkersShare, 0, '.', ' ') }}</div>
                <div style="font-size:11px;color:#92400e;margin-top:4px">👷 Hodimlar ulushi (so'm)</div>
            </div>
            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:12px;text-align:center">
                <div style="font-size:18px;font-weight:800;color:#059669">{{ number_format($pendingFirmaShare, 0, '.', ' ') }}</div>
                <div style="font-size:11px;color:#065f46;margin-top:4px">🏢 Firma ulushi (so'm)</div>
            </div>
        </div>
        @if(count($pendingWorkerStats) > 0)
        <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:8px">Hodimlar bo'yicha:</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:8px">
            @foreach($pendingWorkerStats as $ws)
            <div style="background:#fff;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px">
                <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:6px">{{ $ws['name'] }}</div>
                <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px">
                    <span style="color:#6b7280">Ulush:</span>
                    <span style="font-weight:600;color:#d97706">{{ number_format($ws['share'], 0, '.', ' ') }}</span>
                </div>
                @if($ws['given'] > 0)
                <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px">
                    <span style="color:#6b7280">Berildi:</span>
                    <span style="font-weight:600;color:#16a34a">{{ number_format($ws['given'], 0, '.', ' ') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;padding-top:4px;border-top:1px dashed #fde68a">
                    <span style="color:#92400e;font-weight:600">Qoldi:</span>
                    <span style="font-weight:700;color:#dc2626">{{ number_format($ws['remaining'], 0, '.', ' ') }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Foiz hisob --}}
    @php
        $commPct  = $totalServicesSum > 0 ? round($totalCommissions / $totalServicesSum * 100, 1) : 0;
        $firmPct  = $totalServicesSum > 0 ? round($firmIncome / $totalServicesSum * 100, 1) : 0;
    @endphp
    <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Hodimlar ulushi %</div>
            <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;margin-bottom:4px">
                <div style="height:100%;background:#f59e0b;width:{{ $commPct }}%;border-radius:4px"></div>
            </div>
            <div style="font-size:12px;font-weight:600;color:#d97706">{{ $commPct }}%</div>
        </div>
        <div>
            <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Firma ulushi %</div>
            <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;margin-bottom:4px">
                <div style="height:100%;background:#10b981;width:{{ $firmPct }}%;border-radius:4px"></div>
            </div>
            <div style="font-size:12px;font-weight:600;color:#059669">{{ $firmPct }}%</div>
        </div>
    </div>

</div>
@endif

{{-- TUGATILGAN ISHLAR RO'YXATI (yashirilgan) --}}
@if(false)
<div class="mr-card">
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:14px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tugatilgan ishlar ro'yxati
        <span style="font-size:12px;font-weight:400;color:#9ca3af">{{ $tugatilganIshlar->count() }} ta ish</span>
    </div>

    @if($tugatilganIshlar->count() > 0)
    <div style="overflow-x:auto">
    <table class="mr-table" style="width:100%">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Loyiha</th>
                <th>Xizmat</th>
                <th>Hodim</th>
                <th style="text-align:right">Narx</th>
                <th style="text-align:right">Komissiya</th>
                <th>Sana</th>
                <th>Holat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tugatilganIshlar as $i => $ish)
            <tr style="cursor:pointer" onclick="window.location='/admin/projects/{{ $ish['project_id'] }}/edit'">
                <td style="color:#9ca3af">{{ $i + 1 }}</td>
                <td>
                    <span style="font-family:monospace;font-weight:700;color:#2563eb">{{ $ish['number'] }}</span>
                    <span style="color:#6b7280">· {{ $ish['owner'] }}</span>
                </td>
                <td>{{ $ish['service'] }}</td>
                <td>{{ $ish['employee'] }}</td>
                <td style="text-align:right;font-weight:600">{{ number_format($ish['price'], 0, '.', ' ') }}</td>
                <td style="text-align:right;color:#d97706;font-weight:600">{{ number_format($ish['commission'], 0, '.', ' ') }}</td>
                <td style="color:#6b7280;white-space:nowrap">{{ $ish['date']?->format('d-M H:i') }}</td>
                <td>
                    @if($ish['is_arxiv'])
                    <span style="font-size:11px;font-weight:700;background:#dcfce7;color:#16a34a;border-radius:6px;padding:2px 8px;white-space:nowrap">✅ {{ $ish['status_label'] }}</span>
                    @else
                    <span style="font-size:11px;font-weight:700;background:#f1f5f9;color:#64748b;border-radius:6px;padding:2px 8px;white-space:nowrap">🔄 {{ $ish['status_label'] }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:700;color:#64748b">
                <td colspan="4" style="text-align:right">✅ To'liq (arxiv):</td>
                <td style="text-align:right">{{ number_format($toliqTugatilgan, 0, '.', ' ') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr style="font-weight:700;color:#64748b">
                <td colspan="4" style="text-align:right">🔄 Qisman (faol):</td>
                <td style="text-align:right">{{ number_format($qismanTugatilgan, 0, '.', ' ') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr style="border-top:2px solid #e5e7eb;font-weight:800;background:#f8fafc">
                <td colspan="4" style="text-align:right">Jami tugatilgan (komissiya):</td>
                <td style="text-align:right;color:#16a34a">{{ number_format($toliqTugatilgan + $qismanTugatilgan, 0, '.', ' ') }}</td>
                <td style="text-align:right;color:#d97706">{{ number_format($totalCommissions, 0, '.', ' ') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    </div>
    @else
    <div style="text-align:center;color:#9ca3af;padding:24px;font-size:13px">Bu oyda tugatilgan ish yo'q</div>
    @endif
</div>
@endif

{{-- OGOHLANTIRISHLAR (yashirilgan) --}}
@if(false && $warnings->count() > 0)
<div class="mr-card" style="border:1.5px solid #fca5a5">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
        <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span style="font-size:14px;font-weight:700;color:#dc2626">Diqqat: To'lov to'liq emas</span>
        <span style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;border-radius:12px;padding:2px 10px">{{ $warnings->count() }} ta</span>
    </div>
    <table class="mr-table">
        <thead>
            <tr>
                <th>Loyiha</th>
                <th>Umumiy summa</th>
                <th>To'langan</th>
                <th style="color:#dc2626">Qoldiq</th>
            </tr>
        </thead>
        <tbody>
            @foreach($warnings as $wp)
            <tr class="mr-warn-row">
                <td>
                    <div style="font-weight:600">{{ $wp->owner_name }}</div>
                    <div style="font-size:11px;color:#9ca3af">{{ $wp->number }} · {{ $wp->address }}</div>
                </td>
                <td>{{ number_format($wp->total_price, 0, '.', ' ') }} so'm</td>
                <td style="color:#16a34a;font-weight:600">{{ number_format($wp->paid_amount, 0, '.', ' ') }} so'm</td>
                <td style="color:#dc2626;font-weight:700">{{ number_format($wp->total_price - $wp->paid_amount, 0, '.', ' ') }} so'm</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- TO'LIQ MA'LUMOT MODAL --}}
@if($showDetailModal && isset($userStats[$detailUserId]))
@php
    $ds   = $userStats[$detailUserId];
    $dUsr      = $ds['user'];
    $dSvc      = $ds['services'];
    $dSalPays  = $ds['salary_pays']  ?? collect();
    $dPaidTotal= $ds['paid_total']   ?? 0;
    $dByProject = collect($dSvc)->groupBy('project_id');
    // Umumiy summa — "Barcha ishlar" jadvalidagi Ulush ustuni jami (tugallangan VA
    // kutayotgan ishlar birga — jadval pastidagi "Jami" qatori bilan bir xil bo'lishi uchun).
    // To'lanishi kerak — "Barcha ishlar" jadvalidagi To'landi ustuni jamiga teng
    // (mijoz loyihaning necha foizini to'lagan bo'lsa, ulushning shuncha qismi
    // "ochilgan" — hodimga aynan shu summa to'lanishi kerak; mijoz ko'proq to'lasa,
    // bu summa ham o'sadi).
    $dUmumiySumma    = collect($ds['all_items'] ?? [])->sum('share');
    $dToLandiJami    = collect($ds['all_items'] ?? [])->sum('share_paid');
    // Allaqachon to'langanini ayirib tashlaymiz — aks holda hodimga ortiqcha
    // to'langan bo'lsa ham (yuqoridagi "Ortiqcha to'langan"), bu yerda yana
    // "to'lash kerak" summasi ko'rsatilib, ikkalasi bir vaqtda chiqib
    // chalkashlik tug'dirardi.
    $dToLanishiKerak = max(0, $dToLandiJami - $dPaidTotal);
@endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:flex-start;justify-content:center;padding:20px;overflow-y:auto">
<div style="background:#fff;border-radius:16px;width:100%;max-width:860px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);color:#fff">
        <div>
            <div style="font-size:18px;font-weight:800">{{ $dUsr->name }}</div>
            <div style="font-size:12px;opacity:.85;margin-top:2px">
                {{ $dUsr->role_name ?? ucfirst($dUsr->role) }} · {{ $dUsr->commission_rate }}% komissiya ·
                {{ \Carbon\Carbon::createFromFormat('Y-m',$selectedMonth)->translatedFormat('F Y') }}
            </div>
        </div>
        <button wire:click="closeDetailModal" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:8px;padding:6px 12px;font-size:20px;cursor:pointer;line-height:1">×</button>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#e5e7eb">
        @php
            $dOntime = $ds['ontime_count'] ?? 0;
            $dLate   = $ds['late_count']   ?? 0;
        @endphp
        <div style="background:#fff;padding:16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#374151">{{ $ds['project_count'] }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px" title="Bu oyda kamida bitta xizmati tugallangan loyihalar soni">Tugallangan loyihalar</div>
        </div>
        <div style="background:#fff;padding:16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#16a34a">{{ $dOntime }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">O'z vaqtida</div>
        </div>
        <div style="background:#fff;padding:16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#dc2626">{{ $dLate }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">Kechikkan</div>
        </div>
        <div style="background:#fff;padding:16px;text-align:center">
            <div style="font-size:20px;font-weight:800;color:#d97706">{{ number_format($dUmumiySumma,0,'.',' ') }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">Komissiya (so'm)</div>
        </div>
    </div>

    {{-- Barcha ishlar (tugallangan + kutayotgan) — bitta ro'yxatda --}}
    @php $dAllItems = $ds['all_items'] ?? []; @endphp
    <div style="padding:20px;max-height:420px;overflow-y:auto">
        <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:12px">
            Barcha ishlar <span style="font-weight:400;color:#9ca3af">({{ count($dAllItems) }} ta — tugallangan va kutayotgan birga)</span>
        </div>
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#9ca3af;border-bottom:2px solid #e2e8f0;width:1%">#</th>
                    <th style="padding:8px 10px;text-align:left;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0;white-space:nowrap">Loyiha</th>
                    <th style="padding:8px 10px;text-align:left;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Xizmat</th>
                    <th style="padding:8px 10px;text-align:right;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0;white-space:nowrap">Narxi</th>
                    <th style="padding:8px 10px;text-align:right;font-weight:600;color:#d97706;border-bottom:2px solid #e2e8f0;white-space:nowrap">Ulush</th>
                    <th style="padding:8px 10px;text-align:right;font-weight:600;color:#16a34a;border-bottom:2px solid #e2e8f0;white-space:nowrap">To'landi</th>
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Ochilgan</th>
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Tugatilgan</th>
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Holat</th>
                    @if(auth()->user()?->isAdmin())
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#16a34a;border-bottom:2px solid #e2e8f0">To'lov</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($dAllItems as $it)
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:8px 10px;text-align:center;color:#9ca3af;font-size:11px;font-weight:600">{{ $loop->iteration }}</td>
                    <td style="padding:8px 10px">
                        <div style="font-weight:600;color:#111827;font-size:12px">{{ $it['owner_name'] }}</div>
                        <div style="font-size:10px;color:#9ca3af">{{ $it['project_number'] }}</div>
                    </td>
                    <td style="padding:8px 10px;color:#374151">{{ $it['service_label'] ?? '—' }}</td>
                    <td style="padding:8px 10px;text-align:right;font-weight:600;color:#111827;white-space:nowrap">{{ number_format($it['price'],0,'.',' ') }}</td>
                    <td style="padding:8px 10px;text-align:right;font-weight:700;color:#d97706;white-space:nowrap">{{ number_format($it['share'],0,'.',' ') }}</td>
                    <td style="padding:8px 10px;text-align:right;font-weight:700;color:#16a34a;white-space:nowrap">
                        @if($it['share_paid'] !== null)
                        {{ number_format($it['share_paid'], 0, '.', ' ') }}
                        @else
                        <span style="color:#d1d5db;font-weight:400">—</span>
                        @endif
                    </td>
                    <td style="padding:8px 10px;text-align:center;font-size:11px;color:#6b7280">
                        {{ $it['opened_at'] ? \Carbon\Carbon::parse($it['opened_at'])->format('d.m.Y') : '—' }}
                        @php
                            $poAt = $it['project_opened_at'] ?? null;
                            $diffMonth = $poAt && $it['opened_at'] && \Carbon\Carbon::parse($poAt)->format('Y-m') !== \Carbon\Carbon::parse($it['opened_at'])->format('Y-m');
                        @endphp
                        @if($diffMonth)
                        <div style="color:#c026d3;font-size:9px" title="Loyihaning o'zi bu ishdan oldin ochilgan">🔗 loyiha: {{ \Carbon\Carbon::parse($poAt)->format('d.m.Y') }}</div>
                        @endif
                    </td>
                    <td style="padding:8px 10px;text-align:center;font-size:11px;color:#6b7280">
                        {{ $it['completed_at'] ? \Carbon\Carbon::parse($it['completed_at'])->format('d.m.Y') : '—' }}
                    </td>
                    <td style="padding:8px 10px;text-align:center">
                        @if($it['is_done'])
                            <span class="badge-done">✓ Tugallandi</span>
                        @elseif($it['is_late'])
                            <span class="badge-late">{{ $it['late_days'] }} kun kechikdi</span>
                        @elseif(($it['days_left'] ?? null) !== null && $it['days_left'] <= 3)
                            <span class="badge-pending">{{ $it['days_left'] }} kun qoldi</span>
                        @else
                            <span class="badge-pending">Kutayotgan</span>
                        @endif
                    </td>
                    @if(auth()->user()?->isAdmin())
                    <td style="padding:8px 10px;text-align:center">
                        @if(!$it['is_done'] && ($it['share'] ?? 0) > 0)
                            @if($it['is_paid'] ?? false)
                            <span style="background:#dcfce7;border:1px solid #86efac;color:#16a34a;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:700;white-space:nowrap">✓ To'langan</span>
                            @else
                            <button wire:click="payServiceShare({{ $it['service_id'] }}, {{ $it['user_id'] }}, {{ $it['share'] }})"
                                    wire:confirm="{{ number_format($it['share'],0,'.',',') }} so'm to'lov yozilsinmi?"
                                    style="background:#eff6ff;border:1px solid #93c5fd;color:#2563eb;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap">
                                To'lash
                            </button>
                            @endif
                        @else
                        <span style="color:#d1d5db;font-size:11px">—</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f0fdf4;border-top:2px solid #86efac">
                    <td colspan="3" style="padding:10px;font-weight:700;color:#374151;white-space:nowrap">Jami</td>
                    <td style="padding:10px;text-align:right;font-weight:700;white-space:nowrap">{{ number_format(collect($dAllItems)->sum('price'),0,'.',' ') }}</td>
                    <td style="padding:10px;text-align:right;font-weight:700;color:#d97706;white-space:nowrap">{{ number_format(collect($dAllItems)->sum('share'),0,'.',' ') }}</td>
                    <td style="padding:10px;text-align:right;font-weight:700;color:#16a34a;white-space:nowrap">{{ number_format(collect($dAllItems)->sum('share_paid'),0,'.',' ') }}</td>
                    <td colspan="{{ auth()->user()?->isAdmin() ? 3 : 2 }}"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    {{-- Hisob-kitob --}}
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb;background:#f9fafb;display:flex;justify-content:flex-end;gap:24px;flex-wrap:wrap">
        <div style="text-align:right">
            <div style="font-size:11px;color:#6b7280">Qilingan ishlari (komissiya)</div>
            <div style="font-size:16px;font-weight:800;color:#d97706">{{ number_format($dUmumiySumma,0,'.',' ') }} so'm</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:11px;color:#6b7280">To'lab berildi</div>
            <div style="font-size:16px;font-weight:800;color:#2563eb">{{ number_format($dPaidTotal,0,'.',' ') }} so'm</div>
        </div>
        @if($dPaidTotal > $dToLandiJami)
        <div style="text-align:right">
            <div style="font-size:11px;color:#dc2626">Ortiqcha to'langan</div>
            <div style="font-size:16px;font-weight:800;color:#dc2626">{{ number_format($dPaidTotal - $dToLandiJami,0,'.',' ') }} so'm</div>
        </div>
        @endif
        <div style="text-align:right;padding:8px 16px;background:#dcfce7;border-radius:8px;border:1px solid #86efac">
            <div style="font-size:11px;color:#16a34a;font-weight:600">To'lanishi kerak</div>
            <div style="font-size:20px;font-weight:900;color:#16a34a">{{ number_format($dToLanishiKerak,0,'.',' ') }} so'm</div>
        </div>
    </div>

    {{-- ISH HAQI TO'LOVLARI --}}
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="font-size:13px;font-weight:700;color:#374151">
                Berilgan ish haqi to'lovlari
                @if($dPaidTotal > 0)
                <span style="background:#dcfce7;color:#16a34a;font-size:11px;border-radius:6px;padding:2px 8px;margin-left:6px">
                    Jami: {{ number_format($dPaidTotal,0,'.',' ') }} so'm
                </span>
                @endif
            </div>
            <button wire:click="openSalaryPayModal({{ $detailUserId }})"
                    style="display:inline-flex;align-items:center;gap:5px;background:#2563eb;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                To'lov qo'shish
            </button>
        </div>

        @if($dSalPays->count() > 0)
        <div style="display:flex;flex-direction:column;gap:6px">
            @foreach($dSalPays as $sp)
            <div style="display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px">
                <div style="font-size:14px;font-weight:700;color:#111827">{{ number_format($sp->amount,0,'.',' ') }} so'm</div>
                <div style="font-size:12px;color:#6b7280">{{ $sp->paid_at->format('d.m.Y') }}</div>
                @if($sp->note)
                <div style="font-size:11px;color:#9ca3af;flex:1">{{ $sp->note }}</div>
                @else
                <div style="flex:1"></div>
                @endif
                @if($sp->giver)
                <div style="font-size:10px;color:#9ca3af">{{ $sp->giver->name }}</div>
                @endif
                <button wire:click="editSalaryPay({{ $sp->id }})"
                        style="background:none;border:1px solid #e5e7eb;border-radius:5px;padding:3px 8px;font-size:11px;cursor:pointer;color:#2563eb">✏️</button>
                <button wire:click="deleteSalaryPay({{ $sp->id }})"
                        wire:confirm="O'chirishni tasdiqlaysizmi?"
                        style="background:none;border:1px solid #fecaca;border-radius:5px;padding:3px 8px;font-size:11px;cursor:pointer;color:#dc2626">🗑</button>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:16px;color:#9ca3af;font-size:13px">Hali to'lov kiritilmagan</div>
        @endif
    </div>

</div>
</div>
@endif

{{-- ISH HAQI TO'LOVI MODAL --}}
@if($showSalaryPayModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px">
<div style="background:#fff;border-radius:14px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.4)">
    <div style="padding:18px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:15px;font-weight:800;color:#111827;margin:0">
            {{ $salaryPayEditId ? 'To\'lovni tahrirlash' : 'To\'lov qo\'shish' }}
        </h3>
        <button wire:click="closeSalaryPayModal" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af">×</button>
    </div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:14px">
        @if($salaryPayMonth)
        <div style="background:#eff6ff;border-radius:8px;padding:8px 12px;font-size:12.5px;font-weight:600;color:#1d4ed8">
            📅 {{ \Carbon\Carbon::createFromFormat('Y-m', $salaryPayMonth)->translatedFormat('F Y') }} oyi uchun
            @if($salaryPayRemaining > 0)
                — qoldiq: {{ number_format($salaryPayRemaining, 0, '.', ' ') }} so'm
            @endif
        </div>
        @endif
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Summa (so'm) *</label>
            <input wire:model="salaryPayAmount" type="number" min="1"
                   placeholder="Masalan: 500000"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;font-weight:600;outline:none;box-sizing:border-box"
                   onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
            @if($salaryPayRemaining > 0)
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">To'liq ({{ number_format($salaryPayRemaining, 0, '.', ' ') }}) yoki kamroq (qisman/avans) summa kiritsangiz ham bo'ladi — qolgan qismi keyingi safar to'lanadi.</div>
            @endif
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Sana *</label>
            <input wire:model="salaryPayDate" type="date"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;box-sizing:border-box">
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Izoh (ixtiyoriy)</label>
            <input wire:model="salaryPayNote" type="text"
                   placeholder="Masalan: Iyun oyi ish haqi"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;box-sizing:border-box">
        </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid #e5e7eb;display:flex;gap:10px">
        <button wire:click="saveSalaryPay"
                style="flex:1;background:#2563eb;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:700;cursor:pointer">
            Saqlash
        </button>
        <button wire:click="closeSalaryPayModal"
                style="flex:1;background:#f3f4f6;color:#374151;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">
            Bekor qilish
        </button>
    </div>
</div>
</div>
@endif

{{-- ULUSH FOIZINI TAHRIRLASH MODAL — tanlangan oydan boshlab qo'llanadi,
     o'tgan oylarga ta'sir qilmaydi (server tomonida ham tekshiriladi). --}}
@if($showRateEditor)
@php $rateEditUser = \App\Models\User::find($rateEditUserId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px" wire:click.self="closeRateEditor">
<div style="background:#fff;border-radius:14px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.4)">
    <div style="padding:18px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:15px;font-weight:800;color:#111827;margin:0">Ulush foizini o'zgartirish</h3>
        <button wire:click="closeRateEditor" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af">×</button>
    </div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:#374151">
            <strong>{{ $rateEditUser?->name }}</strong> uchun yangi foiz
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Foiz (%) *</label>
            <input wire:model="rateEditValue" type="number" min="0" max="100" step="0.01"
                   placeholder="Masalan: 20"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;font-weight:600;outline:none;box-sizing:border-box"
                   onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
            @error('rateEditValue')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
        </div>
        @if($selectedMonth < now()->format('Y-m'))
        <div style="font-size:11.5px;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:9px 12px">
            ⚠ Bu — o'tgan oy. O'tgan oy uchun foizni o'zgartirib bo'lmaydi (o'tgan hisobotlarga ta'sir qilmasligi uchun).
        </div>
        @else
        <div style="font-size:11.5px;color:#166534;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:9px 12px">
            ✓ {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }} dan boshlab qo'llanadi. Bundan oldingi oylar o'zgarmaydi.
        </div>
        @endif
    </div>
    <div style="padding:14px 20px;border-top:1px solid #e5e7eb;display:flex;gap:10px">
        <button wire:click="saveRate" @disabled($selectedMonth < now()->format('Y-m'))
                style="flex:1;background:#2563eb;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:700;cursor:pointer;opacity:{{ $selectedMonth < now()->format('Y-m') ? '.5' : '1' }}">
            Saqlash
        </button>
        <button wire:click="closeRateEditor"
                style="flex:1;background:#f3f4f6;color:#374151;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">
            Bekor qilish
        </button>
    </div>
</div>
</div>
@endif

{{-- HODIM STATUSINI (ROLINI) TAHRIRLASH MODAL — "To'lanishi kerak"
     jadvalidagi rol nishonini bosib ochiladi. --}}
@if($showRoleEditor)
@php $roleEditUser = \App\Models\User::find($roleEditUserId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px" wire:click.self="closeRoleEditor">
<div style="background:#fff;border-radius:14px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.4)">
    <div style="padding:18px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:15px;font-weight:800;color:#111827;margin:0">Status va lavozim</h3>
        <button wire:click="closeRoleEditor" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af">×</button>
    </div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:#374151">
            <strong>{{ $roleEditUser?->name }}</strong> uchun
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Tizim huquqi (status) *</label>
            <select wire:model="roleEditValue"
                    style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;font-weight:600;outline:none;box-sizing:border-box;background:#fff">
                <option value="admin">Admin</option>
                <option value="menejer">Menejer</option>
                <option value="bajaruvchi">Bajaruvchi (Hodim)</option>
                <option value="hisobchi">Hisobchi (Buxgalter)</option>
            </select>
            @error('roleEditValue')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Saytda qanday ruxsatlar bilan ishlashini belgilaydi.</div>
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Lavozim (ixtiyoriy)</label>
            <input wire:model="positionEditValue" type="text" maxlength="50"
                   placeholder="Masalan: Direktor"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;font-weight:600;outline:none;box-sizing:border-box"
                   onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e2e8f0'">
            @error('positionEditValue')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
            <div style="font-size:11px;color:#94a3b8;margin-top:4px">Faqat ko'rinish uchun (jadvalda shu chiqadi) — huquqqa ta'sir qilmaydi. Bo'sh qoldirilsa, status nomi ko'rsatiladi.</div>
        </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid #e5e7eb;display:flex;gap:10px">
        <button wire:click="saveRole"
                style="flex:1;background:#2563eb;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:700;cursor:pointer">
            Saqlash
        </button>
        <button wire:click="closeRoleEditor"
                style="flex:1;background:#f3f4f6;color:#374151;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">
            Bekor qilish
        </button>
    </div>
</div>
</div>
@endif

{{-- BELGILANGAN OYLIK (OKLAD) TAHRIRLASH MODAL --}}
@if($showSalaryBaseEditor)
@php $baseEditUser = \App\Models\User::find($salaryBaseEditUserId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px" wire:click.self="closeSalaryBaseEditor">
<div style="background:#fff;border-radius:14px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.4)">
    <div style="padding:18px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:15px;font-weight:800;color:#111827;margin:0">Belgilangan oylik (oklad)</h3>
        <button wire:click="closeSalaryBaseEditor" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af">×</button>
    </div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:14px">
        <div style="font-size:13px;color:#374151">
            <strong>{{ $baseEditUser?->name }}</strong> uchun har oy avtomatik qo'shiladigan oylik
        </div>
        <div>
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">Oylik (so'm) — ishlamagan oyda ham qo'shiladi</label>
            <input wire:model="salaryBaseEditValue" type="number" min="0"
                   placeholder="Masalan: 2000000 (0 — oklad yo'q)"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;font-weight:600;outline:none;box-sizing:border-box"
                   onfocus="this.style.borderColor='#a21caf'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <div style="font-size:11.5px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:9px 12px">
            ⚠ Bu summa shu yilning BARCHA oylariga (o'tganlariga ham) avtomatik qo'shiladi — "To'lanishi kerak" jadvalidagi qoldiqlar shunga qarab oshadi.
        </div>
    </div>
    <div style="padding:14px 20px;border-top:1px solid #e5e7eb;display:flex;gap:10px">
        <button wire:click="saveSalaryBase"
                style="flex:1;background:#a21caf;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:700;cursor:pointer">
            Saqlash
        </button>
        <button wire:click="closeSalaryBaseEditor"
                style="flex:1;background:#f3f4f6;color:#374151;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">
            Bekor qilish
        </button>
    </div>
</div>
</div>
@endif

{{-- BO'SHAGAN XODIMLAR OYNASI --}}
@if($showDeparted)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px" wire:click.self="closeDeparted">
<div style="background:#fff;border-radius:14px;width:100%;max-width:900px;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.4)">
    <div style="padding:18px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:#fff;z-index:1">
        <h3 style="font-size:15px;font-weight:800;color:#111827;margin:0">🚪 Bo'shagan xodimlar</h3>
        <button wire:click="closeDeparted" style="background:none;border:none;cursor:pointer;font-size:20px;color:#9ca3af">×</button>
    </div>
    <div style="padding:20px">
        @if(count($departedRows) === 0)
            <div style="text-align:center;color:#94a3b8;padding:24px;font-size:13px">Bo'shagan hodimlar yo'q.</div>
        @else
        <div class="nrm-wrap">
            <table class="nrm-tbl">
                <thead>
                    <tr>
                        <th class="l">Hodim</th>
                        @foreach($payableVisibleMonths as $m)<th>{{ $nrmMonths[$m-1] }}</th>@endforeach
                        <th>Jami qoldiq</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departedRows as $row)
                    <tr>
                        <td>
                            <div class="nrm-emp" style="min-width:150px;flex-direction:column;align-items:flex-start;gap:3px">
                                <span class="nrm-nm">{{ $row['user']->name }}</span>
                                <span class="role-badge role-{{ $row['user']->role }}">{{ $row['user']->display_title }}</span>
                            </div>
                        </td>
                        @foreach($payableVisibleMonths as $m)
                            @php $cell = $row['months'][$m]; @endphp
                            <td>
                                @if($cell['remaining'] > 0)
                                    <span class="nrm-cell pay-due" wire:click="openSalaryPayModalForMonth({{ $row['user']->id }}, '{{ $cell['month_str'] }}', {{ $cell['remaining'] }})" title="{{ $cell['month_str'] }} uchun to'lash">{{ number_format($cell['remaining'], 0, '.', ' ') }}</span>
                                @elseif($cell['calc'] > 0)
                                    <span class="nrm-cell pay-clear" title="To'liq to'langan">0</span>
                                @else
                                    <span class="nrm-cell-empty"></span>
                                @endif
                            </td>
                        @endforeach
                        <td><span class="nrm-sum {{ $row['year_remaining']>0 ? 'warn' : 'good' }}">{{ number_format($row['year_remaining'], 0, '.', ' ') }}</span></td>
                        <td><button class="restore-btn" wire:click="toggleActive({{ $row['user']->id }})" wire:confirm="{{ $row['user']->name }}ni tiklaysizmi? U yana asosiy ro'yxatga qaytadi.">Tiklash</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
</div>
@endif

</x-filament-panels::page>
