<x-filament-panels::page>
@php
    $fmt = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $initials = fn ($s) => mb_strtoupper(mb_substr(trim((string) $s), 0, 1)) ?: '?';
    $avColors = ['#6366f1', '#8b5cf6', '#0ea5e9', '#f97316', '#ef4444', '#10b981', '#64748b'];
@endphp
<div class="yb">
<style>
.yb{--yb-bg:#f5f6f8;--yb-card:#fff;--yb-bd:#eceef2;--yb-tx:#111827;--yb-mu:#6b7280;--yb-soft:#f3f4f6;
    --yb-green:#16a34a;--yb-red:#dc2626;--yb-blue:#3b82f6;--yb-gold:#b7892f;color:var(--yb-tx)}
.dark .yb{--yb-bg:#0b0f17;--yb-card:#111827;--yb-bd:#1f2937;--yb-tx:#f3f4f6;--yb-mu:#9ca3af;--yb-soft:#1f2937}
.yb *{box-sizing:border-box}
.yb-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px}
.yb-title{font-size:26px;font-weight:800;color:var(--yb-tx)}
.yb-crumb{font-size:12.5px;color:var(--yb-mu);margin-left:14px}
.yb-month{display:flex;align-items:center;gap:4px;background:var(--yb-card);border:1px solid var(--yb-bd);border-radius:10px;padding:4px 6px}
.yb-month button{background:transparent;border:none;width:28px;height:28px;border-radius:6px;cursor:pointer;color:var(--yb-mu);font-size:16px}
.yb-month button:hover{background:var(--yb-soft);color:var(--yb-tx)}
.yb-month span{font-size:13px;font-weight:700;min-width:120px;text-align:center;color:var(--yb-tx)}

.yb-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px}
.yb-tab{border:1px solid var(--yb-bd);background:var(--yb-card);color:var(--yb-tx);padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer}
.yb-tab:hover{background:var(--yb-soft)}
.yb-tab.on{background:#ecdcbc;border-color:#dcc496;color:#5b4212}
.dark .yb-tab.on{background:#5b4212;border-color:#7a5a1e;color:#fbe7c0}
.yb-tab small{font-size:9.5px;color:var(--yb-mu);margin-left:4px}

.yb-card{background:var(--yb-card);border:1px solid var(--yb-bd);border-radius:14px;padding:18px;box-shadow:0 1px 6px rgba(0,0,0,.03)}
.yb-h{font-size:15px;font-weight:800;color:var(--yb-tx);margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.yb-link{font-size:11.5px;font-weight:600;color:var(--yb-mu);background:var(--yb-soft);border:none;border-radius:7px;padding:5px 10px;cursor:pointer;text-decoration:none}
.yb-link:hover{color:var(--yb-tx)}

.yb-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
@media (max-width:1500px){.yb-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:700px){.yb-kpis{grid-template-columns:1fr}}
.yb-kpi{display:flex;gap:12px;align-items:flex-start;padding:16px}
.yb-kpi-ic{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.yb-kpi-l{font-size:12.5px;color:var(--yb-mu)}
.yb-kpi-v{font-size:19px;font-weight:800;color:var(--yb-tx);margin:3px 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.yb-kpi-s{font-size:11px;color:var(--yb-mu)}
.yb-up{color:var(--yb-green) !important;font-weight:700}.yb-down{color:var(--yb-red) !important;font-weight:700}

.yb-row{display:grid;gap:16px;margin-bottom:16px}
.yb-row-3{grid-template-columns:1.6fr 1fr .9fr}
.yb-row-2{grid-template-columns:2.3fr 1fr}
@media (max-width:1100px){.yb-row-3,.yb-row-2{grid-template-columns:1fr}}

/* Ustunli grafik */
.yb-legend{display:flex;gap:14px;font-size:12px;font-weight:500;color:var(--yb-mu)}
.yb-legend i{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:5px}
.yb-chart{display:flex;gap:4px;align-items:stretch;height:220px;padding-left:44px;position:relative}
.yb-grid{position:absolute;left:44px;right:0;top:0;bottom:22px;display:flex;flex-direction:column;justify-content:space-between;pointer-events:none}
.yb-grid div{border-top:1px dashed var(--yb-bd);position:relative}
.yb-grid span{position:absolute;left:-44px;top:-7px;font-size:10px;color:var(--yb-mu);width:40px;text-align:right}
.yb-col{flex:1;display:flex;flex-direction:column;align-items:center;min-width:0}
.yb-bars{flex:1;display:flex;align-items:flex-end;gap:2px;width:100%;justify-content:center}
.yb-bar{width:28%;max-width:12px;border-radius:3px 3px 0 0;min-height:1px}
.yb-col.cur .yb-bars{background:var(--yb-soft);border-radius:6px}
.yb-col:hover .yb-bars{background:var(--yb-soft);border-radius:6px}
.yb-col.cur .yb-col-l{color:var(--yb-tx);font-weight:800}
.yb-col-l{font-size:10.5px;color:var(--yb-mu);height:22px;line-height:22px}

/* Donut */
.yb-donut-wrap{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.yb-donut{position:relative;width:150px;height:150px;flex-shrink:0}
.yb-donut-c{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.yb-donut-c b{font-size:22px;color:var(--yb-tx)}
.yb-donut-c span{font-size:11px;color:var(--yb-mu)}
.yb-dl{flex:1;min-width:140px;display:flex;flex-direction:column;gap:10px;font-size:12.5px}
.yb-dl div{display:flex;align-items:center;gap:8px}
.yb-dl i{width:9px;height:9px;border-radius:50%;flex-shrink:0}
.yb-dl b{margin-left:auto;color:var(--yb-tx)}

/* Hisoblar qoldig'i */
.yb-bal{display:flex;align-items:center;gap:10px;padding:10px 4px;border-bottom:1px solid var(--yb-bd);font-size:12.5px}
.yb-bal:last-child{border-bottom:none}
.yb-bal-ic{width:28px;height:28px;border-radius:8px;background:var(--yb-soft);display:flex;align-items:center;justify-content:center}
.yb-bal b{margin-left:auto;white-space:nowrap}

/* Jadval */
.yb-tbl-wrap{overflow-x:auto}
.yb-tbl{width:100%;border-collapse:collapse;font-size:12.5px}
.yb-tbl th{text-align:left;font-weight:600;color:var(--yb-mu);font-size:11.5px;padding:9px 8px;border-bottom:1px solid var(--yb-bd);white-space:nowrap;background:var(--yb-soft)}
.yb-tbl td{padding:10px 8px;border-bottom:1px solid var(--yb-bd);vertical-align:middle;color:var(--yb-tx)}
.yb-tbl tr:hover td{background:var(--yb-soft)}
.yb-tbl .num{text-align:right;white-space:nowrap;font-weight:700}
.yb-sub{display:block;font-size:10.5px;color:var(--yb-mu);font-weight:400}
.yb-pill{display:inline-block;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700}
.yb-pill.k{background:#dcfce7;color:#15803d}.yb-pill.c{background:#fee2e2;color:#b91c1c}
.yb-pill.ok{background:#dcfce7;color:#15803d}
.dark .yb-pill.k,.dark .yb-pill.ok{background:#14532d;color:#bbf7d0}.dark .yb-pill.c{background:#7f1d1d;color:#fecaca}
.yb-g{color:var(--yb-green) !important}.yb-r{color:var(--yb-red) !important}
.yb-empty{text-align:center;color:var(--yb-mu);padding:26px;font-size:13px}

/* Kichik ro'yxat (TOP 5) */
.yb-li{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:12.5px}
.yb-av{width:30px;height:30px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
.yb-li b{margin-left:auto;white-space:nowrap}
.yb-btn{background:#b7892f;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.yb-btn:hover{background:#9c7324}
.yb-filters{display:flex;gap:6px}
.yb-filters button{border:1px solid var(--yb-bd);background:var(--yb-card);color:var(--yb-tx);padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer}
.yb-filters button.on{background:var(--yb-tx);color:var(--yb-card)}
.yb-soon{text-align:center;padding:50px 20px;color:var(--yb-mu)}
.yb-soon div{font-size:40px;margin-bottom:10px}
.yb-soon b{display:block;color:var(--yb-tx);font-size:16px;margin-bottom:6px}
</style>

@include('filament.partials.report-tabs')

<div class="yb-top">
    <div>
        <span class="yb-title">Buxgalteriya</span>
        <span class="yb-crumb">Bosh sahifa › Yangi bux</span>
    </div>
    <div class="yb-month">
        <button type="button" wire:click="ybChangeMonth(-1)">‹</button>
        <span>📅 {{ $monthLabel }}</span>
        <button type="button" wire:click="ybChangeMonth(1)">›</button>
    </div>
</div>

<div class="yb-tabs">
    @foreach($tabs as $key => $label)
        <button type="button" wire:click="setTab('{{ $key }}')" class="yb-tab {{ $tab === $key ? 'on' : '' }}">
            {{ $label }}@if($key === 'hujjatlar')<small>tez orada</small>@endif
        </button>
    @endforeach
</div>

<div wire:loading.delay.class="opacity-60">
@if($tab === 'asosiy')
    {{-- ── KPI kartalari ── --}}
    <div class="yb-kpis">
        @php
            $kpis = [
                ['l' => 'Jami tushum',    'v' => $income,         'ic' => '↗', 'bg' => '#dcfce7', 'c' => '#16a34a', 'pct' => $incomePct,  'good' => 1],
                ['l' => 'Jami xarajat',   'v' => $expense,        'ic' => '↘', 'bg' => '#fee2e2', 'c' => '#dc2626', 'pct' => $expensePct, 'good' => -1],
                ['l' => 'Sof foyda',      'v' => $profit,         'ic' => '📊', 'bg' => '#dbeafe', 'c' => '#2563eb', 'pct' => $profitPct,  'good' => 1],
                ['l' => 'Olinadigan pul', 'v' => $debtTotal,      'ic' => '👥', 'bg' => '#ffedd5', 'c' => '#ea580c', 'sub' => $debtClients . ' ta mijoz'],
                ['l' => "To'lanadigan pul", 'v' => $staffOwedTotal, 'ic' => '🧾', 'bg' => '#e5e7eb', 'c' => '#475569', 'sub' => $staffOwedPos->count() . ' ta xodim (oylik)'],
            ];
        @endphp
        @foreach($kpis as $k)
            <div class="yb-card yb-kpi">
                <div class="yb-kpi-ic" style="background:{{ $k['bg'] }};color:{{ $k['c'] }}">{{ $k['ic'] }}</div>
                <div style="min-width:0">
                    <div class="yb-kpi-l">{{ $k['l'] }}</div>
                    <div class="yb-kpi-v">{{ $fmt($k['v']) }} so'm</div>
                    <div class="yb-kpi-s">
                        @if(array_key_exists('pct', $k))
                            @if($k['pct'] === null)
                                o'tgan oyda ma'lumot yo'q
                            @else
                                <span class="{{ ($k['pct'] * $k['good']) >= 0 ? 'yb-up' : 'yb-down' }}">{{ $k['pct'] > 0 ? '+' : '' }}{{ $k['pct'] }}%</span> o'tgan oyga nisbatan
                            @endif
                        @else
                            {{ $k['sub'] }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="yb-row yb-row-3">
        {{-- ── Tushum va xarajatlar grafigi ── --}}
        <div class="yb-card">
            <div class="yb-h">
                <span>Tushum va xarajatlar grafigi ({{ $this->ybYear }})</span>
                <div class="yb-legend">
                    <span><i style="background:#22c55e"></i>Tushum</span>
                    <span><i style="background:#ef4444"></i>Xarajat</span>
                    <span><i style="background:#3b82f6"></i>Sof foyda</span>
                </div>
            </div>
            <div class="yb-chart">
                <div class="yb-grid">
                    @foreach([1, .75, .5, .25, 0] as $g)
                        <div><span>{{ $chartMax * $g >= 1e6 ? rtrim(rtrim(number_format($chartMax * $g / 1e6, 1, '.', ''), '0'), '.') . 'M' : ($chartMax * $g >= 1e3 ? round($chartMax * $g / 1e3) . 'K' : 0) }}</span></div>
                    @endforeach
                </div>
                @foreach($chart as $c)
                    <div wire:click="ybSetMonth({{ $c['m'] }})" style="cursor:pointer" class="yb-col {{ $c['m'] === $this->ybMonth ? 'cur' : '' }}"
                         title="{{ $c['label'] }}: tushum {{ $fmt($c['income']) }}, xarajat {{ $fmt($c['expense']) }}, foyda {{ $fmt($c['profit']) }}">
                        <div class="yb-bars">
                            <div class="yb-bar" style="background:#22c55e;height:{{ $c['income'] / $chartMax * 100 }}%"></div>
                            <div class="yb-bar" style="background:#ef4444;height:{{ $c['expense'] / $chartMax * 100 }}%"></div>
                            <div class="yb-bar" style="background:#3b82f6;height:{{ max(0, $c['profit']) / $chartMax * 100 }}%"></div>
                        </div>
                        <div class="yb-col-l">{{ $c['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Tushum manbalari (xizmat turlari) ── --}}
        <div class="yb-card">
            <div class="yb-h"><span>Tushum manbalari (xizmatlar bo'yicha)</span></div>
            @if($donutTotal > 0)
                <div class="yb-donut-wrap">
                    <div class="yb-donut">
                        <svg viewBox="0 0 42 42" width="150" height="150" style="transform:rotate(-90deg)">
                            <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--yb-soft)" stroke-width="6"/>
                            @php $off = 0; @endphp
                            @foreach($donut as $d)
                                <circle cx="21" cy="21" r="15.915" fill="none" stroke="{{ $d['color'] }}" stroke-width="6"
                                        stroke-dasharray="{{ $d['pct'] }} {{ 100 - $d['pct'] }}" stroke-dashoffset="{{ -$off }}"/>
                                @php $off += $d['pct']; @endphp
                            @endforeach
                        </svg>
                        <div class="yb-donut-c">
                            <b>{{ $donutTotal >= 1e6 ? round($donutTotal / 1e6, 1) . 'M' : $fmt($donutTotal) }}</b>
                            <span>so'm</span>
                        </div>
                    </div>
                    <div class="yb-dl">
                        @foreach($donut as $d)
                            <div><i style="background:{{ $d['color'] }}"></i>{{ $d['label'] }}<b>{{ round($d['pct']) }}%</b></div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="yb-empty">Bu oyda tushum yo'q</div>
            @endif
        </div>

        {{-- ── Hisoblar qoldig'i ── --}}
        <div class="yb-card">
            <div class="yb-h"><span>🗂 Hisoblar qoldig'i</span></div>
            @foreach($balances as $b)
                <div class="yb-bal"><span class="yb-bal-ic">{{ $b['icon'] }}</span>{{ $b['label'] }}<b class="{{ $b['value'] < 0 ? 'yb-r' : '' }}">{{ $fmt($b['value']) }} so'm</b></div>
            @endforeach
            <div class="yb-bal" style="font-weight:800"><span class="yb-bal-ic">Σ</span>Jami qoldiq<b>{{ $fmt($balanceTotal) }} so'm</b></div>
            <div class="yb-sub" style="margin-top:6px">Shaxsiy hisoblar kirmaydi · barcha davr uchun</div>
        </div>
    </div>

    <div class="yb-row yb-row-2">
        {{-- ── So'nggi operatsiyalar ── --}}
        <div class="yb-card">
            <div class="yb-h">
                <span>So'nggi operatsiyalar</span>
                <div style="display:flex;gap:8px">
                    <button type="button" class="yb-link" wire:click="setTab('kirim')">Barchasini ko'rish →</button>
                    <a href="{{ \App\Filament\Pages\Buxgalteriya::getUrl() }}" class="yb-btn">＋ Yangi operatsiya</a>
                </div>
            </div>
            @include('filament.pages.partials.yangi-bux-ops', ['rows' => $ops->take(10)])
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="yb-card">
                <div class="yb-h"><span>Mijozlar qarzlari (TOP 5)</span><button type="button" class="yb-link" wire:click="setTab('qarzlar')">Barchasini ko'rish →</button></div>
                @forelse($debts->take(5) as $i => $d)
                    <div class="yb-li">
                        <span class="yb-av" style="background:{{ $avColors[$i % 7] }}">{{ $initials($d['project']->owner_name) }}</span>
                        <div style="min-width:0">{{ $d['project']->owner_name ?: '—' }}<span class="yb-sub">№{{ $d['project']->seq_no }} · {{ \Illuminate\Support\Str::limit($d['project']->address ?? $d['project']->title, 28) }}</span></div>
                        <b class="yb-r">{{ $fmt($d['debt']) }} so'm</b>
                    </div>
                @empty
                    <div class="yb-empty">Qarzdor mijoz yo'q 🎉</div>
                @endforelse
            </div>
            <div class="yb-card">
                <div class="yb-h"><span>Xodimlarga to'lanadigan (TOP 5)</span><button type="button" class="yb-link" wire:click="setTab('oylik')">Barchasini ko'rish →</button></div>
                @forelse($staffOwedPos->take(5) as $i => $s)
                    <div class="yb-li">
                        <span class="yb-av" style="background:{{ $avColors[($i + 3) % 7] }}">{{ $initials($s['user']->name) }}</span>
                        <div style="min-width:0">{{ $s['user']->name }}<span class="yb-sub">{{ $s['user']->position ?? 'Xodim' }}</span></div>
                        <b class="yb-r">{{ $fmt($s['owed']) }} so'm</b>
                    </div>
                @empty
                    <div class="yb-empty">Hammaga to'langan</div>
                @endforelse
            </div>
        </div>
    </div>

@elseif($tab === 'kirim')
    <div class="yb-card">
        <div class="yb-h">
            <span>Kirim-chiqim — {{ $monthLabel }}
                <span class="yb-sub">Kirim: <b class="yb-g">{{ $fmt($income) }}</b> · Chiqim: <b class="yb-r">{{ $fmt($expense) }}</b> · Farq: <b>{{ $fmt($profit) }}</b> so'm</span>
            </span>
            <div class="yb-filters">
                <button type="button" wire:click="$set('opFilter','all')" class="{{ $opFilter === 'all' ? 'on' : '' }}">Barchasi</button>
                <button type="button" wire:click="$set('opFilter','kirim')" class="{{ $opFilter === 'kirim' ? 'on' : '' }}">Kirim</button>
                <button type="button" wire:click="$set('opFilter','chiqim')" class="{{ $opFilter === 'chiqim' ? 'on' : '' }}">Chiqim</button>
            </div>
        </div>
        @include('filament.pages.partials.yangi-bux-ops', ['rows' => $opFilter === 'all' ? $ops : $ops->where('type', $opFilter)->values()])
    </div>

@elseif($tab === 'qarzlar')
    <div class="yb-card">
        <div class="yb-h"><span>Mijozlar qarzlari <span class="yb-sub">{{ $debtClients }} ta loyiha · jami {{ $fmt($debtTotal) }} so'm (bekor qilingan va to'xtatilganlar kirmaydi)</span></span></div>
        <div class="yb-tbl-wrap">
        <table class="yb-tbl">
            <thead><tr><th>#</th><th>№</th><th>Mijoz / Loyiha</th><th>Ochilgan</th><th class="num">Shartnoma</th><th class="num">To'langan</th><th class="num">Qarz</th><th>To'lov %</th></tr></thead>
            <tbody>
            @forelse($debts as $i => $d)
                @php $p = $d['project']; $pc = $p->total_price > 0 ? min(100, round($p->paid_amount / $p->total_price * 100)) : 0; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->seq_no }}</td>
                    <td>{{ $p->owner_name ?: '—' }}<span class="yb-sub">{{ \Illuminate\Support\Str::limit($p->address ?? $p->title, 45) }}</span></td>
                    <td>{{ $p->created_at?->format('d.m.Y') }}</td>
                    <td class="num">{{ $fmt($p->total_price) }}</td>
                    <td class="num yb-g">{{ $fmt($p->paid_amount) }}</td>
                    <td class="num yb-r">{{ $fmt($d['debt']) }}</td>
                    <td style="min-width:90px"><div style="background:var(--yb-soft);border-radius:4px;height:6px"><div style="width:{{ $pc }}%;background:#22c55e;height:6px;border-radius:4px"></div></div><span class="yb-sub">{{ $pc }}%</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="yb-empty">Qarzdor mijoz yo'q</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>

@elseif($tab === 'oylik')
    <div class="yb-row yb-row-2">
        <div class="yb-card">
            <div class="yb-h"><span>Oylik maosh — {{ $monthLabel }} <span class="yb-sub">Oylik hisobotdagi "To'lanishi kerak" bilan bir xil hisob</span></span></div>
            <div class="yb-tbl-wrap">
            <table class="yb-tbl">
                <thead><tr><th>Xodim</th><th class="num">Shu oy hisoblangan</th><th class="num">Shu oy berilgan</th><th class="num">Shu oy qoldiq</th><th class="num">Yil boshidan qarz</th></tr></thead>
                <tbody>
                @forelse($staffOwed as $s)
                    @php $m = $s['month']; @endphp
                    <tr>
                        <td>{{ $s['user']->name }}<span class="yb-sub">{{ $s['user']->position ?? '' }}</span></td>
                        <td class="num">{{ $fmt($m['calc'] ?? 0) }}</td>
                        <td class="num yb-g">{{ $fmt($m['paid'] ?? 0) }}</td>
                        <td class="num {{ ($m['remaining'] ?? 0) > 0 ? 'yb-r' : '' }}">{{ $fmt(max(0, $m['remaining'] ?? 0)) }}</td>
                        <td class="num {{ $s['owed'] > 0 ? 'yb-r' : '' }}">{{ $fmt($s['owed']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="yb-empty">Ma'lumot yo'q</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="yb-card">
            <div class="yb-h"><span>Shu oy uchun berilgan oyliklar</span></div>
            @forelse($salaryPayments as $sp)
                <div class="yb-li">
                    <span class="yb-av" style="background:#10b981">{{ $initials($sp->user?->name) }}</span>
                    <div style="min-width:0">{{ $sp->user?->name ?? '—' }}<span class="yb-sub">{{ $sp->paid_at?->format('d.m.Y') }}@if($sp->giver) · {{ $sp->giver->name }}@endif @if($sp->note) · {{ $sp->note }}@endif</span></div>
                    <b class="yb-g">{{ $fmt($sp->amount) }}</b>
                </div>
            @empty
                <div class="yb-empty">Bu oy uchun oylik berilmagan</div>
            @endforelse
        </div>
    </div>

@elseif($tab === 'hisobotlar')
    <div class="yb-card">
        <div class="yb-h"><span>{{ $this->ybYear }}-yil hisoboti <span class="yb-sub">Tushum — to'lov sanasi bo'yicha; xarajat — Buxgalteriyadagi qoida bo'yicha</span></span></div>
        <div class="yb-tbl-wrap">
        <table class="yb-tbl">
            <thead><tr><th>Oy</th><th class="num">Tushum</th><th class="num">Xarajat</th><th class="num">Sof foyda</th><th class="num">Rentabellik</th></tr></thead>
            <tbody>
            @foreach($chart as $c)
                <tr wire:click="ybSetMonth({{ $c['m'] }})" style="cursor:pointer;{{ $c['m'] === $this->ybMonth ? 'font-weight:700' : '' }}">
                    <td>{{ $c['label'] }}</td>
                    <td class="num yb-g">{{ $fmt($c['income']) }}</td>
                    <td class="num yb-r">{{ $fmt($c['expense']) }}</td>
                    <td class="num {{ $c['profit'] < 0 ? 'yb-r' : '' }}">{{ $fmt($c['profit']) }}</td>
                    <td class="num">{{ $c['income'] > 0 ? round($c['profit'] / $c['income'] * 100) . '%' : '—' }}</td>
                </tr>
            @endforeach
                <tr style="font-weight:800">
                    <td>Jami</td>
                    <td class="num yb-g">{{ $fmt($yearIncome) }}</td>
                    <td class="num yb-r">{{ $fmt($yearExpense) }}</td>
                    <td class="num">{{ $fmt($yearIncome - $yearExpense) }}</td>
                    <td class="num">{{ $yearIncome > 0 ? round(($yearIncome - $yearExpense) / $yearIncome * 100) . '%' : '—' }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

@else
    <div class="yb-card yb-soon">
        <div>📁</div>
        <b>{{ $tabs[$tab] }} — tez orada</b>
        Buxgalteriya hujjatlari (shartnoma, shot-faktura, akt) shu yerda jamlanadi.
    </div>
@endif
</div>
</div>
</x-filament-panels::page>
