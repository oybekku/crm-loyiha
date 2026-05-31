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
.dark .mr-warn-row td{background:#2d1515}
</style>

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

{{-- UMUMIY STATISTIKA --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:18px">
    <div class="mr-stat mr-stat--neutral">
        <div class="mr-stat-num">{{ $projectsTotal }}</div>
        <div class="mr-stat-lbl">To'langan loyihalar</div>
    </div>
    <div class="mr-stat mr-stat--neutral">
        <div class="mr-stat-num">{{ number_format($totalServicesSum, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Xizmatlar jami (so'm)</div>
    </div>
    <div class="mr-stat mr-stat--warn">
        <div class="mr-stat-num">{{ number_format($totalCommissions, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Hodimlar ulushi jami</div>
    </div>
    <div class="mr-stat mr-stat--danger">
        <div class="mr-stat-num">{{ number_format($totalAdvances, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Avanslar jami (so'm)</div>
    </div>
    <div class="mr-stat mr-stat--success">
        <div class="mr-stat-num">{{ number_format($firmIncome, 0, '.', ' ') }}</div>
        <div class="mr-stat-lbl">Firma sof daromadi</div>
    </div>
</div>

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
                <th style="text-align:center">Loyihalar</th>
                <th style="text-align:center">O'z vaqtida</th>
                <th style="text-align:center">Kechikkan</th>
                <th style="text-align:right">Xizmatlar jami</th>
                <th style="text-align:right">Hisoblangan</th>
                <th style="text-align:right;color:#dc2626">Avans olingan</th>
                <th style="text-align:right;color:#16a34a">Qolgan to'lov</th>
                <th style="text-align:right">Firma ulushi</th>
            </tr>
        </thead>
        @php $i = 1; @endphp
        @foreach($userStats as $uid => $stat)
            @php
                $sTotal      = $stat['services_total'];
                $comm        = $stat['commission'];
                $firmPart    = $sTotal - $comm;
                $advTotal    = $stat['advance_total'] ?? 0;
                $netPayable  = $stat['net_payable']   ?? max(0, $comm - $advTotal);
                $ontime      = $stat['ontime_count']  ?? 0;
                $late        = $stat['late_count']    ?? 0;
                $nodate      = count($stat['services']) - $ontime - $late;
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
                                @if($stat['user']->commission_rate)
                                · <span style="color:#2563eb">{{ $stat['user']->commission_rate }}% ulush</span>
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
                    <span style="font-size:16px;font-weight:800;color:#374151">{{ $stat['project_count'] }}</span>
                </td>
                <td style="text-align:center">
                    @if($ontime > 0)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#16a34a;font-size:12px;font-weight:700;border-radius:6px;padding:3px 10px">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        {{ $ontime }}
                    </span>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
                <td style="text-align:center">
                    @if($late > 0)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#fee2e2;color:#dc2626;font-size:12px;font-weight:700;border-radius:6px;padding:3px 10px">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        {{ $late }}
                    </span>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
                <td style="text-align:right;font-weight:600">{{ number_format($sTotal, 0, '.', ' ') }} so'm</td>
                <td style="text-align:right">
                    <span style="font-weight:700;color:#d97706">{{ number_format($comm, 0, '.', ' ') }} so'm</span>
                </td>
                <td style="text-align:right">
                    @if($advTotal > 0)
                    <span style="font-weight:700;color:#dc2626">{{ number_format($advTotal, 0, '.', ' ') }} so'm</span>
                    @else
                    <span style="color:#d1d5db;font-size:12px">—</span>
                    @endif
                </td>
                <td style="text-align:right">
                    <span style="font-weight:700;color:#16a34a">{{ number_format($netPayable, 0, '.', ' ') }} so'm</span>
                </td>
                <td style="text-align:right">
                    <span style="font-weight:600;color:#059669">{{ number_format($firmPart, 0, '.', ' ') }} so'm</span>
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
                                <div style="font-size:10px;color:#9ca3af;margin-top:2px">Jami xizmatlar</div>
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
                            @if($advTotal > 0)
                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#dc2626">{{ number_format($advTotal, 0, '.', ' ') }}</div>
                                <div style="font-size:10px;color:#dc2626;margin-top:2px">Avans olingan (so'm)</div>
                            </div>
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:8px 14px;min-width:130px;text-align:center">
                                <div style="font-size:18px;font-weight:800;color:#16a34a">{{ number_format($netPayable, 0, '.', ' ') }}</div>
                                <div style="font-size:10px;color:#16a34a;margin-top:2px">Qolgan to'lov (so'm)</div>
                            </div>
                            @endif
                        </div>

                        {{-- Avanslar bo'limi --}}
                        <div style="margin-bottom:12px">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                                <span style="font-size:12px;font-weight:700;color:#374151">Avanslar ({{ $stat['advances']->count() }} ta)</span>
                                @if(auth()->user()?->isAdmin())
                                <button wire:click.stop="openAdvanceModal({{ $uid }}, '{{ addslashes($stat['user']->name) }}')"
                                        style="display:inline-flex;align-items:center;gap:5px;background:#dc2626;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:11px;font-weight:600;cursor:pointer">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                                    Avans qo'shish
                                </button>
                                @endif
                            </div>
                            @if($stat['advances']->count() > 0)
                            <div style="background:#fff;border:1px solid #fecaca;border-radius:8px;overflow:hidden">
                                @foreach($stat['advances'] as $adv)
                                <div style="display:flex;align-items:center;gap:12px;padding:8px 12px;border-bottom:1px solid #fef2f2;{{ $loop->last ? 'border-bottom:none' : '' }}">
                                    <div style="flex:1">
                                        <span style="font-size:13px;font-weight:700;color:#dc2626">{{ number_format((float)$adv->amount, 0, '.', ' ') }} so'm</span>
                                        @if($adv->note)
                                        <span style="font-size:11px;color:#6b7280;margin-left:8px">— {{ $adv->note }}</span>
                                        @endif
                                    </div>
                                    <div style="font-size:10px;color:#9ca3af;white-space:nowrap">
                                        {{ $adv->given_at?->format('d.m.Y') }}
                                        @if($adv->giver) · {{ $adv->giver->name }} @endif
                                    </div>
                                    @if(auth()->user()?->isAdmin())
                                    <button wire:click.stop="deleteAdvance({{ $adv->id }})"
                                            wire:confirm="Bu avansni o'chirishni tasdiqlaysizmi?"
                                            style="background:none;border:none;cursor:pointer;color:#fca5a5;padding:2px 4px"
                                            title="O'chirish">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    </button>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div style="font-size:12px;color:#9ca3af;padding:8px 0">Bu oy avans berilmagan</div>
                            @endif
                        </div>

                        {{-- Service detail table --}}
                        <div style="overflow-x:auto;border-radius:8px;border:1px solid #e2e8f0">
                        <table class="mr-detail-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Loyiha</th>
                                    <th>Manzil</th>
                                    <th>Xizmat turi</th>
                                    <th style="text-align:right">Narx</th>
                                    <th style="text-align:center">Foiz</th>
                                    <th style="text-align:right">Ulush</th>
                                    <th style="text-align:center">Muddat</th>
                                    <th style="text-align:center">To'langan sana</th>
                                    <th style="text-align:center">Holat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stat['services'] as $j => $srv)
                                <tr>
                                    <td style="color:#9ca3af;font-size:11px">{{ $j + 1 }}</td>
                                    <td>
                                        <div style="font-weight:600;color:#111827">{{ $srv['owner_name'] }}</div>
                                        <div style="font-size:10px;color:#9ca3af">№{{ $srv['project_number'] }}</div>
                                    </td>
                                    <td style="font-size:11px;color:#6b7280;max-width:160px">{{ $srv['address'] ?? '—' }}</td>
                                    <td>
                                        <span style="display:inline-block;background:#eff6ff;color:#2563eb;font-size:10px;font-weight:600;border-radius:4px;padding:2px 7px">
                                            {{ $srv['service_label'] ?? $srv['service_name'] ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;font-weight:600;white-space:nowrap">
                                        {{ number_format($srv['price'], 0, '.', ' ') }}
                                    </td>
                                    <td style="text-align:center">
                                        @if($srv['rate'] > 0)
                                        <span style="color:#d97706;font-weight:600">{{ $srv['rate'] }}%</span>
                                        @else
                                        <span style="color:#d1d5db">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;white-space:nowrap">
                                        @if($srv['commission'] > 0)
                                        <span style="color:#d97706;font-weight:700">{{ number_format($srv['commission'], 0, '.', ' ') }}</span>
                                        @else
                                        <span style="color:#d1d5db">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;font-size:11px">
                                        @if($srv['deadline_date'])
                                        {{ $srv['deadline_date']->format('d.m.Y') }}
                                        @else
                                        <span style="color:#d1d5db">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;font-size:11px">
                                        @if($srv['paid_at'])
                                        {{ $srv['paid_at']->format('d.m.Y') }}
                                        @else
                                        <span style="color:#d1d5db">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap">
                                        @if(!$srv['deadline_date'])
                                        <span class="badge-nodate">Muddat yo'q</span>
                                        @elseif($srv['is_late'])
                                        <span class="badge-late">Kechikkan {{ $srv['late_days'] }} kun</span>
                                        @else
                                        <span class="badge-ontime">O'z vaqtida</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                <tr style="background:#fafafa;font-weight:700;border-top:2px solid #e2e8f0">
                                    <td colspan="4" style="text-align:right;color:#374151">Jami:</td>
                                    <td style="text-align:right">{{ number_format($sTotal, 0, '.', ' ') }}</td>
                                    <td></td>
                                    <td style="text-align:right;color:#d97706">{{ number_format($comm, 0, '.', ' ') }}</td>
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
            <td colspan="6" style="text-align:right">Jami:</td>
            <td style="text-align:right">{{ number_format($totalServicesSum, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#d97706">{{ number_format($totalCommissions, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#dc2626">{{ number_format($totalAdvances, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#16a34a">{{ number_format($totalCommissions - $totalAdvances, 0, '.', ' ') }} so'm</td>
            <td style="text-align:right;color:#059669">{{ number_format($firmIncome, 0, '.', ' ') }} so'm</td>
        </tr>
        </tbody>
    </table>
    @endif
</div>

{{-- OGOHLANTIRISHLAR --}}
@if($warnings->count() > 0)
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

{{-- AVANS MODAL --}}
@if($showAdvanceModal)
<div style="position:fixed;inset:0;z-index:1300;display:flex;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.5)" wire:click="closeAdvanceModal"></div>
    <div style="position:relative;background:#fff;border-radius:16px;padding:28px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.2)">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <div>
                <div style="font-size:16px;font-weight:800;color:#111827">Avans qo'shish</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px">{{ $advanceUserName }}</div>
            </div>
            <button wire:click="closeAdvanceModal" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div style="margin-bottom:14px">
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">
                Avans summasi (so'm) <span style="color:#dc2626">*</span>
            </label>
            <input type="number" wire:model="advanceAmount" placeholder="Masalan: 1500000"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;outline:none;color:#111827"
                   onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        <div style="margin-bottom:20px">
            <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px">
                Izoh (ixtiyoriy)
            </label>
            <input type="text" wire:model="advanceNote" placeholder="Masalan: May oyi avans"
                   style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:14px;outline:none;color:#111827"
                   onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        <div style="display:flex;gap:10px">
            <button wire:click="saveAdvance"
                    style="flex:1;background:#dc2626;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:700;cursor:pointer">
                Saqlash
            </button>
            <button wire:click="closeAdvanceModal"
                    style="flex:1;background:#f1f5f9;color:#374151;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:600;cursor:pointer">
                Bekor qilish
            </button>
        </div>

    </div>
</div>
@endif

{{-- TO'LIQ MA'LUMOT MODAL --}}
@if($showDetailModal && isset($userStats[$detailUserId]))
@php
    $ds   = $userStats[$detailUserId];
    $dUsr = $ds['user'];
    $dCom = $ds['commission'];
    $dAdv = $ds['advance_total'];
    $dNet = $ds['net_payable'];
    $dSvc = $ds['services'];
    // Loyihalar bo'yicha guruhlash
    $dByProject = collect($dSvc)->groupBy('project_id');
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
            <div style="font-size:11px;color:#6b7280;margin-top:2px">Loyihalar</div>
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
            <div style="font-size:20px;font-weight:800;color:#d97706">{{ number_format($dCom,0,'.',' ') }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">Komissiya (so'm)</div>
        </div>
    </div>

    {{-- Loyihalar jadvali --}}
    <div style="padding:20px;max-height:420px;overflow-y:auto">
        <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:12px">
            Loyihalar bo'yicha batafsil
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:8px 10px;text-align:left;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Loyiha</th>
                    <th style="padding:8px 10px;text-align:left;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Xizmat</th>
                    <th style="padding:8px 10px;text-align:right;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Narxi</th>
                    <th style="padding:8px 10px;text-align:right;font-weight:600;color:#d97706;border-bottom:2px solid #e2e8f0">Komissiya</th>
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Muddat</th>
                    <th style="padding:8px 10px;text-align:center;font-weight:600;color:#475569;border-bottom:2px solid #e2e8f0">Holat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dSvc as $srv)
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:8px 10px">
                        <div style="font-weight:600;color:#111827;font-size:12px">{{ $srv['owner_name'] }}</div>
                        <div style="font-size:10px;color:#9ca3af">{{ $srv['project_number'] }}</div>
                    </td>
                    <td style="padding:8px 10px;color:#374151">{{ $srv['service_label'] ?? $srv['service_name'] }}</td>
                    <td style="padding:8px 10px;text-align:right;font-weight:600;color:#111827">{{ number_format($srv['price'],0,'.',' ') }}</td>
                    <td style="padding:8px 10px;text-align:right;font-weight:700;color:#d97706">{{ number_format($srv['commission'],0,'.',' ') }}</td>
                    <td style="padding:8px 10px;text-align:center;font-size:11px;color:#9ca3af">
                        {{ $srv['deadline_date'] ? $srv['deadline_date']->format('d.m.Y') : '—' }}
                    </td>
                    <td style="padding:8px 10px;text-align:center">
                        @if($srv['is_late'])
                            <span style="background:#fee2e2;color:#dc2626;font-size:10px;font-weight:700;border-radius:4px;padding:2px 7px">{{ $srv['late_days'] }} kun kechikdi</span>
                        @elseif($srv['deadline_date'])
                            <span style="background:#dcfce7;color:#16a34a;font-size:10px;font-weight:700;border-radius:4px;padding:2px 7px">O'z vaqtida</span>
                        @else
                            <span style="background:#f3f4f6;color:#9ca3af;font-size:10px;border-radius:4px;padding:2px 7px">Muddatsiz</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f0fdf4;border-top:2px solid #86efac">
                    <td colspan="2" style="padding:10px;font-weight:700;color:#374151">Jami</td>
                    <td style="padding:10px;text-align:right;font-weight:700">{{ number_format($ds['services_total'],0,'.',' ') }}</td>
                    <td style="padding:10px;text-align:right;font-weight:700;color:#d97706">{{ number_format($dCom,0,'.',' ') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Hisob-kitob --}}
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb;background:#f9fafb;display:flex;justify-content:flex-end;gap:24px;flex-wrap:wrap">
        <div style="text-align:right">
            <div style="font-size:11px;color:#6b7280">Hisoblangan komissiya</div>
            <div style="font-size:16px;font-weight:800;color:#d97706">{{ number_format($dCom,0,'.',' ') }} so'm</div>
        </div>
        @if($dAdv > 0)
        <div style="text-align:right">
            <div style="font-size:11px;color:#6b7280">Avans olingan</div>
            <div style="font-size:16px;font-weight:800;color:#dc2626">- {{ number_format($dAdv,0,'.',' ') }} so'm</div>
        </div>
        @endif
        <div style="text-align:right;padding:8px 16px;background:#dcfce7;border-radius:8px;border:1px solid #86efac">
            <div style="font-size:11px;color:#16a34a;font-weight:600">To'lanishi kerak</div>
            <div style="font-size:20px;font-weight:900;color:#16a34a">{{ number_format($dNet,0,'.',' ') }} so'm</div>
        </div>
    </div>

</div>
</div>
@endif

</x-filament-panels::page>
