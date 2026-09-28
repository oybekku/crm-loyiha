<x-filament-panels::page>
@php
    $fmt = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $initials = fn ($s) => mb_strtoupper(mb_substr(trim((string) $s), 0, 1)) ?: '?';
    $avColors = ['#6366f1', '#8b5cf6', '#0ea5e9', '#f97316', '#ef4444', '#10b981', '#64748b'];
    $curYear  = $this->ybYear;
    $curMonth = $this->ybMonth;
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

/* Kirim-chiqim: filtrlar, modallar */
.yb-fbar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.yb-f{display:flex;flex-direction:column;gap:4px}
.yb-f label{font-size:11.5px;color:var(--yb-mu);font-weight:600}
.yb-f input,.yb-f select,.yb-in{border:1px solid var(--yb-bd);background:var(--yb-card);color:var(--yb-tx);border-radius:8px;padding:8px 10px;font-size:12.5px;outline:none;width:100%}
.yb-f input:focus,.yb-f select:focus,.yb-in:focus{border-color:#93c5fd;box-shadow:0 0 0 3px #dbeafe}
.yb-f select{min-width:120px}
.yb-ov{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:60;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;overflow:auto}
.yb-modal{background:var(--yb-card);color:var(--yb-tx);border-radius:14px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden}
.yb-modal-h{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;font-weight:800;font-size:15px;color:#111827}
.dark .yb-modal-h{filter:brightness(.9)}
.yb-modal-h button{border:none;background:none;font-size:22px;cursor:pointer;color:#6b7280;line-height:1}
.yb-modal-b{padding:16px 18px}
.yb-fld{margin-bottom:12px;position:relative}
.yb-fld>label{display:block;font-size:12px;font-weight:600;color:var(--yb-mu);margin-bottom:4px}
.yb-fld>label i{color:#dc2626;font-style:normal}
.yb-grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media (max-width:520px){.yb-grid2{grid-template-columns:1fr}}
.yb-err{color:#dc2626;font-size:11px;margin-top:3px}
.yb-pick{display:flex;justify-content:space-between;gap:10px;padding:9px 10px;border-radius:8px;cursor:pointer;font-size:12.5px}
.yb-pick:hover{background:var(--yb-soft)}
.yb-dd{position:absolute;left:0;right:0;top:100%;z-index:5;background:var(--yb-card);border:1px solid var(--yb-bd);border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.12);max-height:220px;overflow:auto}
.yb-upl{display:inline-flex;align-items:center;gap:6px;border:1px dashed var(--yb-bd);border-radius:8px;padding:8px 14px;font-size:12.5px;cursor:pointer;background:var(--yb-soft)}
.yb-file{display:flex;gap:8px;align-items:center;font-size:12.5px}
.yb-file a:last-child{color:#dc2626;font-weight:700}
.yb-b2{flex:1;padding:11px;border-radius:9px;border:1px solid var(--yb-bd);background:var(--yb-card);color:var(--yb-tx);font-weight:700;font-size:13px;cursor:pointer}
.yb-b2.red{background:#dc2626;border-color:#dc2626;color:#fff}
.yb-b2.red:hover{background:#b91c1c}
.yb-act{width:28px;height:28px;border-radius:7px;border:1px solid var(--yb-bd);background:var(--yb-card);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:var(--yb-mu)}
.yb-act:hover{background:var(--yb-soft);color:var(--yb-tx)}
.yb-act.del:hover{background:#fee2e2;color:#dc2626}
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
        @include('filament.pages.partials.yangi-bux-chart', ['chartTitle' => 'Tushum va xarajatlar grafigi', 'showProfit' => true])

        @include('filament.pages.partials.yangi-bux-donut', ['donutTitle' => "Tushum manbalari (xizmatlar bo'yicha)"])

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
                    <button type="button" wire:click="setTab('kirim')" class="yb-btn">＋ Yangi operatsiya</button>
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
    <div class="yb-kpis" style="grid-template-columns:repeat(3,minmax(0,1fr))">
        @php
            $kpis3 = [
                ['l' => 'Kirimlar (jami)',  'v' => $income,  'ic' => '↓', 'bg' => '#dcfce7', 'c' => '#16a34a', 'pct' => $incomePct,  'good' => 1],
                ['l' => 'Chiqimlar (jami)', 'v' => $expense, 'ic' => '↗', 'bg' => '#fee2e2', 'c' => '#dc2626', 'pct' => $expensePct, 'good' => -1],
                ['l' => 'Sof foyda',        'v' => $profit,  'ic' => '◔', 'bg' => '#dbeafe', 'c' => '#2563eb', 'pct' => $profitPct,  'good' => 1],
            ];
        @endphp
        @foreach($kpis3 as $k)
            <div class="yb-card yb-kpi">
                <div class="yb-kpi-ic" style="background:{{ $k['bg'] }};color:{{ $k['c'] }};font-weight:800">{{ $k['ic'] }}</div>
                <div style="min-width:0">
                    <div class="yb-kpi-l">{{ $k['l'] }} · {{ $monthLabel }}</div>
                    <div class="yb-kpi-v">{{ $fmt($k['v']) }} so'm</div>
                    <div class="yb-kpi-s">
                        @if($k['pct'] === null) o'tgan oyda ma'lumot yo'q
                        @else <span class="{{ ($k['pct'] * $k['good']) >= 0 ? 'yb-up' : 'yb-down' }}">{{ $k['pct'] > 0 ? '+' : '' }}{{ $k['pct'] }}%</span> o'tgan oyga nisbatan
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="yb-row" style="grid-template-columns:1.7fr 1fr">
        @include('filament.pages.partials.yangi-bux-chart', ['chartTitle' => 'Kirim va chiqimlar dinamikasi', 'showProfit' => false])
        @include('filament.pages.partials.yangi-bux-donut', ['donutTitle' => "To'lovlar manbalari (xizmatlar bo'yicha)"])
    </div>

    <div class="yb-card">
        {{-- Filtrlar --}}
        <div class="yb-fbar">
            <div class="yb-f">
                <label>Sana oralig'i</label>
                <div style="display:flex;gap:4px;align-items:center">
                    <input type="date" wire:model.live="opFrom" value="{{ $opFrom }}">
                    <span style="color:var(--yb-mu)">—</span>
                    <input type="date" wire:model.live="opTo" value="{{ $opTo }}">
                </div>
            </div>
            <div class="yb-f">
                <label>Tur</label>
                <select wire:model.live="opFilter"><option value="all">Barchasi</option><option value="kirim">Kirim</option><option value="chiqim">Chiqim</option></select>
            </div>
            <div class="yb-f">
                <label>To'lov turi</label>
                <select wire:model.live="opMethod"><option value="">Barchasi</option>@foreach($methodOptions as $mk => $ml)<option value="{{ $mk }}">{{ $ml }}</option>@endforeach</select>
            </div>
            <div class="yb-f">
                <label>Loyiha / Mijoz</label>
                <input type="text" wire:model.live.debounce.400ms="opProject" placeholder="Ism yoki №">
            </div>
            <div class="yb-f">
                <label>Mas'ul</label>
                <select wire:model.live="opUser"><option value="">Barchasi</option>@foreach($staffUsers as $su)<option value="{{ $su->id }}">{{ $su->name }}</option>@endforeach</select>
            </div>
            <div class="yb-f" style="flex:1;min-width:160px">
                <label>&nbsp;</label>
                <input type="search" wire:model.live.debounce.400ms="opSearch" placeholder="🔍 Qidirish...">
            </div>
        </div>

        <div class="yb-h" style="margin-top:14px">
            <span>
                @if($opFrom || $opTo) {{ $opFrom ? \Carbon\Carbon::parse($opFrom)->format('d.m.Y') : '…' }} — {{ $opTo ? \Carbon\Carbon::parse($opTo)->format('d.m.Y') : '…' }} @else {{ $monthLabel }} @endif
                <span class="yb-sub">{{ $opsFiltered->count() }} ta operatsiya ·
                    Kirim: <b class="yb-g">{{ $fmt($opsFiltered->where('type', 'kirim')->sum('amount')) }}</b> ·
                    Chiqim: <b class="yb-r">{{ $fmt($opsFiltered->where('type', 'chiqim')->sum('amount')) }}</b> so'm
                    @if($opFrom || $opTo || $opFilter !== 'all' || $opMethod || $opProject || $opUser || $opSearch)
                        · <a href="#" wire:click.prevent="opResetFilters" style="color:#2563eb">filtrlarni tozalash</a>
                    @endif
                </span>
            </span>
            <div style="display:flex;gap:8px">
                <button type="button" class="yb-btn" style="background:#16a34a" wire:click="openKirim">＋ Kirim qo'shish</button>
                <button type="button" class="yb-btn" style="background:#dc2626" wire:click="openChiqim">＋ Chiqim qo'shish</button>
            </div>
        </div>
        @include('filament.pages.partials.yangi-bux-ops', ['rows' => $opsFiltered, 'actions' => true])
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

{{-- ── Kirim qo'shish: loyiha tanlash ── --}}
@if($showKirimPicker)
    <div class="yb-ov" wire:click.self="$set('showKirimPicker', false)">
        <div class="yb-modal">
            <div class="yb-modal-h" style="background:#dcfce7">Kirim qo'shish — loyihani tanlang <button type="button" wire:click="$set('showKirimPicker', false)">×</button></div>
            <div class="yb-modal-b">
                <input type="search" class="yb-in" wire:model.live.debounce.300ms="kirimSearch" placeholder="Mijoz ismi, №, manzil yoki telefon" autofocus>
                <div style="margin-top:10px;max-height:360px;overflow:auto">
                    @forelse($kirimProjects as $kp)
                        <div class="yb-pick" wire:click="pickKirimProject({{ $kp->id }})">
                            <div style="min-width:0"><b>№{{ $kp->seq_no }} · {{ $kp->owner_name ?: '—' }}</b><span class="yb-sub">{{ \Illuminate\Support\Str::limit($kp->address ?: $kp->title, 50) }}</span></div>
                            <span class="yb-sub" style="text-align:right;white-space:nowrap">
                                @php $dbt = max(0, $kp->total_price - $kp->paid_amount); @endphp
                                @if($dbt > 0) <b class="yb-r">qarz {{ $fmt($dbt) }}</b> @else <span class="yb-g">to'langan</span> @endif
                            </span>
                        </div>
                    @empty
                        <div class="yb-empty">Topilmadi</div>
                    @endforelse
                </div>
                <div class="yb-sub" style="margin-top:10px">Loyiha tanlangach, Kanban'dagi o'sha to'lov oynasi ochiladi — komissiya, chek va loyiha qarzi avtomatik hisoblanadi.</div>
            </div>
        </div>
    </div>
@endif

{{-- ── Chiqim qo'shish / tahrirlash ── --}}
@if($showChiqimModal)
    <div class="yb-ov" wire:click.self="closeChiqim">
        <div class="yb-modal">
            <div class="yb-modal-h" style="background:#fee2e2">{{ $chiqimId ? 'Chiqimni tahrirlash' : "Chiqim qo'shish" }} <button type="button" wire:click="closeChiqim">×</button></div>
            <div class="yb-modal-b">
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>Sana <i>*</i></label>
                        <input type="date" class="yb-in" wire:model="chDate">
                        @error('chDate')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld" style="position:relative">
                        <label>Loyiha / Mijoz</label>
                        @if($chProject)
                            <div class="yb-in" style="display:flex;justify-content:space-between;gap:6px"><span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">№{{ $chProject->seq_no }} · {{ $chProject->owner_name }}</span><a href="#" wire:click.prevent="$set('chProjectId', null)" style="color:#dc2626">×</a></div>
                        @else
                            <input type="search" class="yb-in" wire:model.live.debounce.300ms="chProjectSearch" placeholder="Ixtiyoriy — qidirish">
                            @if($chProjects->isNotEmpty())
                                <div class="yb-dd">
                                    @foreach($chProjects as $cp)
                                        <div class="yb-pick" wire:click="$set('chProjectId', {{ $cp->id }})"><div>№{{ $cp->seq_no }} · {{ $cp->owner_name }}<span class="yb-sub">{{ \Illuminate\Support\Str::limit($cp->address, 40) }}</span></div></div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="yb-fld">
                    <label>Tavsif <i>*</i></label>
                    <input type="text" class="yb-in" wire:model="chComment" placeholder="Masalan: Qurilish materiali xarid qilindi">
                    @error('chComment')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Summa (so'm) <i>*</i></label>
                    <input type="text" inputmode="numeric" class="yb-in" wire:model="chAmount" placeholder="0"
                           x-data x-on:input="let v=$el.value.replace(/\D/g,'');$el.value=v.replace(/\B(?=(\d{3})+(?!\d))/g,' ')">
                    @error('chAmount')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>To'lov turi (hisob) <i>*</i></label>
                        <select class="yb-in" wire:model="chAccountId">
                            <option value="">— tanlang —</option>
                            @foreach($allAccounts->groupBy('type') as $t => $accs)
                                <optgroup label="{{ \App\Models\FinancialAccount::typeOptions()[$t] ?? $t }}">
                                    @foreach($accs as $a)<option value="{{ $a->id }}">{{ $a->name ?: (\App\Models\FinancialAccount::typeOptions()[$t] ?? $t) }}</option>@endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('chAccountId')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld">
                        <label>Mas'ul</label>
                        <select class="yb-in" wire:model="chResponsibleId">
                            <option value="">—</option>
                            @foreach($staffUsers as $su)<option value="{{ $su->id }}">{{ $su->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="yb-fld">
                    <label>Hujjat (ixtiyoriy) — chek, faktura</label>
                    @if($chFile)
                        <div class="yb-file">📎 {{ $chFile->getClientOriginalName() }} <a href="#" wire:click.prevent="removeChiqimFile">×</a></div>
                    @elseif($chExistingFile)
                        <div class="yb-file">📎 <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($chExistingFile) }}" target="_blank">{{ basename($chExistingFile) }}</a> <a href="#" wire:click.prevent="removeChiqimFile">×</a></div>
                    @else
                        <label class="yb-upl">📁 Fayl tanlash<input type="file" wire:model="chFile" style="display:none" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx"></label>
                        <span wire:loading wire:target="chFile" class="yb-sub">yuklanmoqda…</span>
                    @endif
                    @error('chFile')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Qo'shimcha ma'lumot</label>
                    <textarea class="yb-in" rows="3" wire:model="chNote" placeholder="Kerak bo'lsa izoh yozing..."></textarea>
                </div>
                <div style="display:flex;gap:10px;margin-top:6px">
                    <button type="button" class="yb-b2" wire:click="closeChiqim">Bekor qilish</button>
                    <button type="button" class="yb-b2 red" wire:click="saveChiqim" wire:loading.attr="disabled" wire:target="saveChiqim,chFile">Saqlash</button>
                </div>
            </div>
        </div>
    </div>
@endif

@livewire('payment-modal')
<div id="kb-notify-box" style="display:none;position:fixed;top:20px;right:20px;z-index:100000;color:#fff;padding:12px 18px;border-radius:10px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2)"></div>
@script
<script>
    Livewire.on('notify', (data) => {
        const d = Array.isArray(data) ? data[0] : data;
        const box = document.getElementById('kb-notify-box');
        if (!box) return;
        box.textContent = d.message || '';
        box.style.background = d.type === 'success' ? '#16a34a' : '#dc2626';
        box.style.display = 'block';
        setTimeout(() => box.style.display = 'none', 3500);
    });
    Livewire.on('print-receipt', (data) => {
        const d = Array.isArray(data) ? data[0] : data;
        if (d && d.paymentId && window.bhOpenChek) window.bhOpenChek(d.paymentId);
    });
</script>
@endscript
</div>
</x-filament-panels::page>
