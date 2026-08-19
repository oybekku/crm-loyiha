<div class="tcp-wrap">
<style>
.tcp-wrap{color:#111827}
.tcp-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px 22px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.tcp-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.tcp-title{font-size:17px;font-weight:800;color:#111827;display:flex;align-items:center;gap:8px}
.tcp-title .tcp-dot{width:9px;height:9px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 4px rgba(22,163,74,.15)}
.tcp-total{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:12px;padding:9px 16px;font-size:14px;font-weight:700}
.tcp-total b{font-size:16px}

.tcp-month{margin-bottom:14px;border:1px solid #f1f5f9;border-radius:12px;overflow:hidden}
.tcp-month:last-child{margin-bottom:0}
.tcp-month-head{display:flex;align-items:center;justify-content:space-between;background:#f8fafc;padding:9px 14px;font-size:13px;font-weight:700;color:#374151}
.tcp-month-sum{color:#2563eb;font-weight:800}

.tcp-table{width:100%;border-collapse:collapse;font-size:13.5px}
.tcp-table th{text-align:left;padding:8px 14px;color:#9ca3af;font-weight:600;font-size:11.5px;text-transform:uppercase;letter-spacing:.03em;border-bottom:1px solid #f1f5f9}
.tcp-table td{padding:9px 14px;border-bottom:1px solid #f8fafc;vertical-align:top}
.tcp-table tr:last-child td{border-bottom:none}
.tcp-fish{font-weight:600;color:#111827}
.tcp-note{color:#9ca3af;font-size:12px;margin-top:2px}
.tcp-summa{font-weight:800;color:#15803d;white-space:nowrap}
.tcp-manager{color:#6b7280}

.tcp-empty{padding:28px 10px;text-align:center;color:#9ca3af;font-size:14px}
</style>

<div class="tcp-card">
    <div class="tcp-head">
        <div class="tcp-title"><span class="tcp-dot"></span> Bugungi naqd tushum</div>
        <div class="tcp-total">Jami: <b>{{ number_format($totalSum, 0, '.', ' ') }}</b> so'm &middot; {{ $totalCount }} ta to'lov</div>
    </div>

    @if(empty($groups))
        <div class="tcp-empty">Bugun hali naqd to'lov kiritilmagan.</div>
    @else
        @foreach($groups as $group)
            <div class="tcp-month">
                <div class="tcp-month-head">
                    <span>{{ $group['label'] }}</span>
                    <span class="tcp-month-sum">{{ number_format($group['sum'], 0, '.', ' ') }} so'm</span>
                </div>
                <table class="tcp-table">
                    <thead>
                        <tr>
                            <th>FISH</th>
                            <th>Sana</th>
                            @if($isAdmin)<th>Menejer</th>@endif
                            <th style="text-align:right">Summa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['items'] as $item)
                            <tr>
                                <td>
                                    <div class="tcp-fish">{{ $item['fish'] }}</div>
                                    @if($item['note'])<div class="tcp-note">{{ $item['note'] }}</div>@endif
                                </td>
                                <td>{{ $item['sana'] }}</td>
                                @if($isAdmin)<td class="tcp-manager">{{ $item['manager'] }}</td>@endif
                                <td class="tcp-summa" style="text-align:right">{{ number_format($item['summa'], 0, '.', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
</div>
