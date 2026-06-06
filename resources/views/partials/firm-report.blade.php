{{-- Firma hisoboti bloki — Dashboard va Oylik hisobot uchun umumiy.
     $fr — App\Services\FirmReportService::forMonth() natijasi --}}
@php $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $fr['month'])->translatedFormat('F Y'); @endphp
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;margin-bottom:16px;position:relative;z-index:1">
    <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
        Firma hisoboti
        <span style="font-size:12px;font-weight:400;color:#9ca3af">{{ $monthLabel }}</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">

        {{-- To'langan loyihalar --}}
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#15803d">{{ $fr['toLanganCount'] }}</div>
            <div style="font-size:11px;color:#16a34a;margin-top:4px;font-weight:500">To'langan loyihalar</div>
        </div>

        {{-- Jami tushum + taqsimot (hodimlar / firma) --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:22px;font-weight:800;color:#111827">{{ number_format($fr['jamiTushum'], 0, '.', ' ') }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:4px;font-weight:500">Jami tushum (so'm)</div>
            <div style="margin-top:8px;border-top:1px dashed #e2e8f0;padding-top:8px;display:flex;flex-direction:column;gap:3px">
                <div style="display:flex;justify-content:space-between;font-size:11px">
                    <span style="color:#92400e">👷 Hodimlar</span>
                    <span style="font-weight:700;color:#d97706">{{ number_format($fr['hodimlarUlushi'], 0, '.', ' ') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px">
                    <span style="color:#065f46">🏢 Firma</span>
                    <span style="font-weight:700;color:#059669">{{ number_format($fr['firmaDaromadi'], 0, '.', ' ') }}</span>
                </div>
            </div>
        </div>

        {{-- Hodimlar ulushi + qaysi hodim qancha --}}
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:22px;font-weight:800;color:#d97706">{{ number_format($fr['hodimlarUlushi'], 0, '.', ' ') }}</div>
            <div style="font-size:11px;color:#d97706;margin-top:4px;font-weight:500">Hodimlar ulushi (so'm)</div>
            @if(count($fr['employeeComm']) > 0)
            <div style="margin-top:8px;border-top:1px dashed #fde68a;padding-top:8px;display:flex;flex-direction:column;gap:3px;text-align:left">
                @foreach($fr['employeeComm'] as $emp)
                <div style="display:flex;justify-content:space-between;font-size:11px;gap:6px">
                    <span style="color:#92400e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $emp['name'] }}</span>
                    <span style="font-weight:700;color:#b45309;white-space:nowrap">{{ number_format($emp['commission'], 0, '.', ' ') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Firma sof daromadi --}}
        <div style="background:#ecfdf5;border:2px solid #34d399;border-radius:10px;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:900;color:#059669">{{ number_format($fr['firmaDaromadi'], 0, '.', ' ') }}</div>
            <div style="font-size:11px;color:#059669;margin-top:4px;font-weight:700">Firma sof daromadi (so'm)</div>
        </div>

        {{-- Qilinmagan + Umumiy loyihalar --}}
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                <div style="font-size:28px;font-weight:900;color:#ea580c">{{ $fr['pendingCount'] }}</div>
                <div style="font-size:12px;color:#ea580c;font-weight:600">Qilinmagan<br>loyihalar</div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:#6b7280">Qilinmagan jami summa</span>
                <span style="font-weight:700;color:#9a3412">{{ number_format($fr['pendingSum'], 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px">
                <span style="color:#6b7280">To'langan</span>
                <span style="font-weight:700;color:#16a34a">{{ number_format($fr['pendingPaid'], 0, '.', ' ') }} so'm</span>
            </div>
            <div style="background:#e5e7eb;border-radius:4px;height:6px;overflow:hidden">
                <div style="height:100%;background:#f97316;border-radius:4px;width:{{ $fr['pendingPct'] }}%"></div>
            </div>
            <div style="font-size:11px;color:#ea580c;margin-top:4px;font-weight:600;text-align:right">{{ $fr['pendingPct'] }}% to'langan</div>

            <div style="margin-top:10px;border-top:1px dashed #fed7aa;padding-top:8px">
                <div style="display:flex;justify-content:space-between;font-size:12px">
                    <span style="color:#6b7280">Umumiy loyihalar</span>
                    <span style="font-weight:700;color:#374151">{{ $fr['allProjectsCount'] }} ta</span>
                </div>
            </div>
        </div>
    </div>
</div>
