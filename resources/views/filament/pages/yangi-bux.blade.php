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
.yb-cell-link{cursor:pointer;border-bottom:1px dashed currentColor;padding-bottom:1px}
.yb-cell-link:hover{opacity:.75}
/* Hisobotlar: yig'iladigan ustunlar */
.yb-rep th.yb-colh{padding:0 !important;vertical-align:bottom}
.yb-rep th.yb-colh button{all:unset;box-sizing:border-box;display:flex;align-items:center;justify-content:flex-end;gap:6px;width:100%;padding:9px 8px;cursor:pointer;border-radius:6px;transition:background .15s}
.yb-rep th.yb-colh button:hover{background:var(--yb-bd)}
.yb-rep th.yb-colh button:hover .yb-colh-c{opacity:1;color:var(--cc)}
.yb-rep .yb-colh-l{border-bottom:2px solid var(--cc);padding-bottom:2px}
.yb-rep .yb-colh-c{display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;font-size:11px;opacity:.35;transition:opacity .15s}
.yb-rep th.yb-colh.cx .yb-colh-l{opacity:.45;border-bottom-style:dashed}
.yb-rep th.yb-colh.cx .yb-colh-c{opacity:.9}
.yb-rep tr.yb-zero-row .yb-cell-link{pointer-events:none;border-bottom-color:transparent}
.yb-eye{border:none;background:none;cursor:pointer;font-size:13px;padding:0 4px 0 0;opacity:.45;vertical-align:middle}
.yb-eye:hover{opacity:1}
.yb-eye.off{opacity:.9}

.yb-work{display:block;margin:3px 0 0 32px;font-size:10.5px;line-height:1.35;color:var(--yb-mu);font-weight:400;white-space:normal}
.yb-work b{color:#d97706;font-weight:700}
.yb-tbl tr.yb-just td{background:#fef9c3 !important;animation:ybJust 1.2s ease-in-out 2}
.dark .yb-tbl tr.yb-just td{background:rgba(234,179,8,.18) !important}
@keyframes ybJust{50%{background:#fde047}}
.yb-just-tag{display:inline-block;margin-left:4px;padding:0 6px;border-radius:999px;background:#eab308;color:#fff;font-size:9.5px;font-weight:800;vertical-align:middle}
.yb-stype{display:inline-block;margin-left:6px;padding:1px 7px;border-radius:999px;font-size:10.5px;font-weight:700;cursor:pointer;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;vertical-align:middle}
.yb-stype.mam{background:#f5f3ff;color:#7c3aed;border-color:#ddd6fe}
.yb-tbl tr.yb-grp td{background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:13px;padding:9px 8px;border-top:2px solid #bfdbfe}
.yb-tbl tr.yb-grp.mam td{background:#f5f3ff;color:#6d28d9;border-top-color:#ddd6fe}
.yb-tbl tr.yb-grp td span{font-weight:400;font-size:11px;color:var(--yb-mu);margin-left:6px}
.dark .yb-tbl tr.yb-grp td{background:rgba(37,99,235,.15)}
.dark .yb-tbl tr.yb-grp.mam td{background:rgba(124,58,237,.15)}
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
/* Filament (Tailwind forms) select'ga o'q rasmini qo'yadi — "background"
   qisqa yozuvi uni butun kenglik bo'ylab takrorlatib yuborardi. */
.yb-f select,select.yb-in{background-repeat:no-repeat !important;background-position:right .5rem center !important;background-size:1.25em 1.25em !important;padding-right:2rem !important;appearance:none;-webkit-appearance:none}
/* Oyna yuqoridagi qora menyu (topbar) ustida turishi kerak */
.yb-ov{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:1000;display:flex;align-items:flex-start;justify-content:center;padding:90px 16px 40px;overflow:auto}
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
.yb-prog{margin-bottom:14px;font-size:12.5px}
.yb-prog>div:first-child{display:flex;justify-content:space-between;margin-bottom:5px}
.yb-prog-bar{height:8px;border-radius:6px;background:var(--yb-soft);overflow:hidden}
.yb-prog-bar i{display:block;height:100%;border-radius:6px}
.yb-prog small{display:block;text-align:right;color:var(--yb-mu);font-size:11px;margin-top:2px}
.yb-next{display:flex;gap:12px;align-items:center;border-top:1px solid var(--yb-bd);padding-top:14px;margin-top:6px}
.yb-next b{display:block;color:var(--yb-tx)}
.yb-act:disabled{opacity:.35;cursor:default}
.yb-staff{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(0,2fr) auto;gap:10px;align-items:center;padding:10px 4px;border-bottom:1px solid var(--yb-bd);font-size:12.5px}
.yb-staff:last-of-type{border-bottom:none}
.yb-staff b{color:var(--yb-tx)}
.yb-staff-n{text-align:right;white-space:nowrap}
@media (max-width:700px){.yb-staff{grid-template-columns:1fr 1fr}}
/* Doimiy to'lovlar */
.yb-rec{display:grid;grid-template-columns:26px minmax(0,1fr) auto auto;gap:10px;align-items:center;padding:10px 6px;border-bottom:1px solid var(--yb-bd);font-size:13px}
.yb-rec.late{background:#fef2f2}
.dark .yb-rec.late{background:#3b1414}
.yb-rec-ic{font-size:15px;text-align:center}
.yb-rec-n b{color:var(--yb-tx)}
.yb-rec-v{text-align:right;white-space:nowrap;font-weight:800;color:var(--yb-tx)}
.yb-rec-v a{color:var(--yb-tx);text-decoration:underline dotted}
.yb-rec.paid .yb-rec-v a{color:var(--yb-green)}
.yb-rec-a{display:flex;gap:6px;justify-content:flex-end;align-items:center;min-width:120px}
.yb-rec-more{display:block;width:100%;margin-top:6px;padding:8px;border:1px dashed var(--yb-bd);border-radius:9px;background:transparent;color:#2563eb;font-size:12.5px;font-weight:700;cursor:pointer}
.yb-rec-more:hover{background:var(--yb-soft)}
.yb-rec-t{padding-top:12px;font-size:13px;color:var(--yb-mu);text-align:right}
.yb-rec-t b{color:var(--yb-tx)}
@media (max-width:600px){.yb-rec{grid-template-columns:22px minmax(0,1fr) auto}.yb-rec-a{grid-column:2/-1;min-width:0}}
.yb-kind{display:flex;gap:8px}
.yb-kind-opt{flex:1;text-align:center;padding:10px;border-radius:9px;border:1.5px solid var(--yb-bd);cursor:pointer;font-size:13px;font-weight:700;color:var(--yb-mu);background:var(--yb-card)}
.yb-kind-opt.on.xarajat{border-color:#dc2626;background:#fef2f2;color:#b91c1c}
.yb-kind-opt.on.oylik{border-color:#d97706;background:#fffbeb;color:#b45309}
.yb-pill.o{background:#fef3c7;color:#b45309}
.dark .yb-pill.o{background:#78350f;color:#fde68a}
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

        @include('filament.pages.partials.yangi-bux-donut', [
            'donutTitle'  => $monthLabel . " davomida tushgan to'lovlar (loyiha oylari bo'yicha)",
            'donut'       => $pmDonut,
            'donutTotal'  => $pmTotal,
            'showAmounts' => true,
        ])

        {{-- ── Hisoblar qoldig'i ── --}}
        <div class="yb-card">
            <div class="yb-h"><span>🗂 Hisoblar qoldig'i</span></div>
            @foreach($balances as $b)
                <div class="yb-bal"><span class="yb-bal-ic">{{ $b['icon'] }}</span>{{ $b['label'] }}<b class="{{ $b['value'] < 0 ? 'yb-r' : '' }}">{{ $fmt($b['value']) }} so'm</b></div>
            @endforeach
            <div class="yb-bal" style="font-weight:800"><span class="yb-bal-ic">Σ</span>Jami qoldiq<b>{{ $fmt($balanceTotal) }} so'm</b></div>
            <div class="yb-sub" style="margin-top:6px">Kompaniya hisoblari · xodimlar kartalari kirmaydi · barcha davr uchun</div>
        </div>
    </div>

    <div style="margin-bottom:16px">@include('filament.pages.partials.yangi-bux-staff-cards')</div>

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
                        <div style="min-width:0"><x-fish :name="$d['project']->owner_name" /><span class="yb-sub">№{{ $d['project']->seq_no }} · {{ \Illuminate\Support\Str::limit($d['project']->address ?? $d['project']->title, 28) }}</span></div>
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
    <div class="yb-kpis" style="grid-template-columns:repeat(4,minmax(0,1fr))">
        @php
            $kpis3 = [
                ['l' => 'Kirimlar',       'v' => $income,      'ic' => '↓', 'bg' => '#dcfce7', 'c' => '#16a34a', 'pct' => $incomePct,  'good' => 1],
                ['l' => 'Xarajatlar',     'v' => $otherSpent,  'ic' => '↗', 'bg' => '#fee2e2', 'c' => '#dc2626', 'sub' => 'oylik va avanslarsiz'],
                ['l' => 'Oylik / avans',  'v' => $salarySpent, 'ic' => '💵', 'bg' => '#fef3c7', 'c' => '#b45309', 'sub' => 'ishbay ' . $fmt($salarySpent - $mamuriySpent) . " · ma'muriy " . $fmt($mamuriySpent)],
                ['l' => 'Sof foyda',      'v' => $profit,      'ic' => '◔', 'bg' => '#dbeafe', 'c' => '#2563eb', 'pct' => $profitPct,  'good' => 1],
            ];
        @endphp
        @foreach($kpis3 as $k)
            <div class="yb-card yb-kpi">
                <div class="yb-kpi-ic" style="background:{{ $k['bg'] }};color:{{ $k['c'] }};font-weight:800">{{ $k['ic'] }}</div>
                <div style="min-width:0">
                    <div class="yb-kpi-l">{{ $k['l'] }} · {{ $monthLabel }}</div>
                    <div class="yb-kpi-v">{{ $fmt($k['v']) }} so'm</div>
                    <div class="yb-kpi-s">
                        @if(isset($k['sub'])) {{ $k['sub'] }}
                        @elseif($k['pct'] === null) o'tgan oyda ma'lumot yo'q
                        @else <span class="{{ ($k['pct'] * $k['good']) >= 0 ? 'yb-up' : 'yb-down' }}">{{ $k['pct'] > 0 ? '+' : '' }}{{ $k['pct'] }}%</span> o'tgan oyga nisbatan
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="yb-row" style="grid-template-columns:1.7fr 1fr">
        @include('filament.pages.partials.yangi-bux-chart', ['chartTitle' => 'Kirim va chiqimlar dinamikasi', 'showProfit' => false, 'split' => true])
        @include('filament.pages.partials.yangi-bux-donut', [
            'donutTitle'  => $monthLabel . " davomida tushgan to'lovlar (loyiha oylari bo'yicha)",
            'donut'       => $pmDonut,
            'donutTotal'  => $pmTotal,
            'showAmounts' => true,
        ])
    </div>

    @include('filament.pages.partials.yangi-bux-recurring')

    {{-- "Xodimlar bo'yicha xarajatlar" Kirim-chiqim tabida yashirilgan (admin so'rovi) — Asosiy ko'rinishda qoldi --}}

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
                <label>Chiqim turi</label>
                <select wire:model.live="opKind"><option value="">Barchasi</option><option value="xarajat">Xarajat</option><option value="oylik">Oylik / avans</option></select>
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
            <div class="yb-f">
                <label>Tartib</label>
                <select wire:model.live="opSort"><option value="new">Oxirgi qo'shilgan birinchi</option><option value="date">Sana bo'yicha</option></select>
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
                    @if($opFrom || $opTo || $opFilter !== 'chiqim' || $opMethod || $opProject || $opUser || $opSearch || $opKind)
                        · <a href="#" wire:click.prevent="opResetFilters" style="color:#2563eb">filtrlarni tozalash</a>
                    @endif
                </span>
            </span>
            <div style="display:flex;gap:8px">
                <button type="button" class="yb-btn" style="background:#16a34a" wire:click="openKirim">＋ Kirim qo'shish</button>
                <button type="button" class="yb-btn" style="background:#dc2626" wire:click="openChiqim">＋ Chiqim qo'shish</button>
            </div>
        </div>
        @include('filament.pages.partials.yangi-bux-ops', ['rows' => $opsFiltered, 'actions' => true, 'justSaved' => $opJustSaved, 'collapse' => 4])
    </div>

@elseif($tab === 'qarzlar')
    @include('filament.pages.partials.client-debts')

@elseif($tab === 'oylik')
    @php $S = $salary; @endphp
    <div class="yb-kpis" style="grid-template-columns:repeat(4,minmax(0,1fr))">
        <div class="yb-card yb-kpi">
            <div class="yb-kpi-ic" style="background:#dcfce7;color:#16a34a">👥</div>
            <div style="min-width:0"><div class="yb-kpi-l">Jami xodimlar</div><div class="yb-kpi-v">{{ $S['staffCount'] }} nafar</div><div class="yb-kpi-s">{{ $S['roleCount'] }} ta bo'lim</div></div>
        </div>
        <div class="yb-card yb-kpi">
            <div class="yb-kpi-ic" style="background:#dbeafe;color:#2563eb">👛</div>
            <div style="min-width:0"><div class="yb-kpi-l">Jami maosh fondi</div><div class="yb-kpi-v">{{ $fmt($S['fund']) }} so'm</div><div class="yb-kpi-s">{{ $monthLabel }} uchun · oklad + komissiya</div></div>
        </div>
        <div class="yb-card yb-kpi">
            <div class="yb-kpi-ic" style="background:#ffedd5;color:#ea580c">◔</div>
            <div style="min-width:0"><div class="yb-kpi-l">To'langan</div><div class="yb-kpi-v">{{ $fmt($S['paid']) }} so'm</div><div class="yb-kpi-s"><span class="yb-up">{{ $S['paidPct'] }}%</span> · {{ $S['paidPeople'] }} nafar</div></div>
        </div>
        <div class="yb-card yb-kpi">
            <div class="yb-kpi-ic" style="background:#fee2e2;color:#dc2626">⏰</div>
            <div style="min-width:0"><div class="yb-kpi-l">To'lanmagan</div><div class="yb-kpi-v">{{ $fmt($S['unpaid']) }} so'm</div><div class="yb-kpi-s"><span class="yb-down">{{ 100 - $S['paidPct'] }}%</span> · {{ $S['unpaidPeople'] }} nafar</div></div>
        </div>
    </div>

    <div class="yb-row yb-row-3">
        {{-- So'nggi 6 oy --}}
        <div class="yb-card">
            <div class="yb-h">
                <span>Oylik maosh dinamikasi (so'nggi 6 oy)</span>
                <div class="yb-legend"><span><i style="background:#3b82f6"></i>To'langan</span><span><i style="background:#ef4444"></i>To'lanmagan</span></div>
            </div>
            <div class="yb-chart">
                <div class="yb-grid">
                    @foreach([1, .75, .5, .25, 0] as $g)
                        <div><span>{{ $S['sixMax'] * $g >= 1e6 ? rtrim(rtrim(number_format($S['sixMax'] * $g / 1e6, 1, '.', ''), '0'), '.') . 'M' : ($S['sixMax'] * $g >= 1e3 ? round($S['sixMax'] * $g / 1e3) . 'K' : 0) }}</span></div>
                    @endforeach
                </div>
                @foreach($S['six'] as $c)
                    <div class="yb-col {{ $c['cur'] ? 'cur' : '' }}" title="{{ $c['label'] }}: to'langan {{ $fmt($c['paid']) }}, to'lanmagan {{ $fmt($c['unpaid']) }}">
                        <div class="yb-bars" style="flex-direction:column;justify-content:flex-end;align-items:center;gap:0">
                            <div style="width:42%;max-width:44px;background:#ef4444;border-radius:4px 4px 0 0;height:{{ $c['unpaid'] / $S['sixMax'] * 100 }}%"></div>
                            <div style="width:42%;max-width:44px;background:#3b82f6;height:{{ $c['paid'] / $S['sixMax'] * 100 }}%;{{ $c['unpaid'] > 0 ? '' : 'border-radius:4px 4px 0 0' }}"></div>
                        </div>
                        <div class="yb-col-l">{{ $c['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Lavozimlar bo'yicha --}}
        @include('filament.pages.partials.yangi-bux-donut', ['donutTitle' => "To'langan oylik turlar bo'yicha", 'donut' => $S['posDonut'], 'donutTotal' => $S['paidAll']])

        {{-- To'lov holati --}}
        <div class="yb-card">
            <div class="yb-h"><span>To'lov holati</span></div>
            @php $cp = max(1, $S['countedPeople']); @endphp
            <div class="yb-prog">
                <div><span>To'langan xodimlar</span><b>{{ $S['paidPeople'] }} nafar</b></div>
                <div class="yb-prog-bar"><i style="width:{{ $S['paidPeople'] / $cp * 100 }}%;background:#22c55e"></i></div>
                <small>{{ round($S['paidPeople'] / $cp * 100) }}%</small>
            </div>
            <div class="yb-prog">
                <div><span>To'lanmagan / qisman</span><b>{{ $S['unpaidPeople'] }} nafar</b></div>
                <div class="yb-prog-bar"><i style="width:{{ $S['unpaidPeople'] / $cp * 100 }}%;background:#ef4444"></i></div>
                <small>{{ round($S['unpaidPeople'] / $cp * 100) }}%</small>
            </div>
            @if($S['lastPay'])
                <div class="yb-next">
                    <span class="yb-bal-ic" style="width:40px;height:40px;background:#dbeafe">📅</span>
                    <div><span class="yb-sub">Oxirgi to'lov</span><b style="font-size:16px">{{ $S['lastPay']->paid_at->format('d.m.Y') }}</b><span class="yb-sub">{{ $S['lastPay']->user?->name }} · {{ $fmt($S['lastPay']->amount) }} so'm</span></div>
                </div>
            @endif
        </div>
    </div>

    <div class="yb-card">
        <div class="yb-fbar">
            <div class="yb-f">
                <label>Oy</label>
                <div class="yb-month" style="padding:2px 4px"><button type="button" wire:click="ybChangeMonth(-1)">‹</button><span style="min-width:100px">{{ $monthLabel }}</span><button type="button" wire:click="ybChangeMonth(1)">›</button></div>
            </div>
            <div class="yb-f">
                <label>Bo'lim</label>
                <select wire:model.live="omRole"><option value="">Barchasi</option>@foreach(\App\Models\EmployeeSalaryPayment::typeOptions() as $rk => $rl)<option value="{{ $rk }}">{{ $rl }}</option>@endforeach</select>
            </div>
            <div class="yb-f">
                <label>Lavozim</label>
                <select wire:model.live="omPosition"><option value="">Barchasi</option>@foreach($S['positions'] as $pos)<option value="{{ $pos }}">{{ $pos }}</option>@endforeach</select>
            </div>
            <div class="yb-f">
                <label>Holat</label>
                <select wire:model.live="omStatus"><option value="">Barchasi</option><option value="tolangan">To'langan</option><option value="qisman">Qisman</option><option value="tolanmagan">To'lanmagan</option></select>
            </div>
            <div class="yb-f" style="flex:1;min-width:160px"><label>&nbsp;</label><input type="search" wire:model.live.debounce.400ms="omSearch" placeholder="🔍 Xodimni qidirish..."></div>
            <button type="button" class="yb-btn" style="background:#16a34a;padding:10px 16px" wire:click="openPay">＋ Maosh to'lash</button>
            <button type="button" class="yb-b2" style="flex:none;padding:9px 14px" wire:click="exportSalary">⬇ Hisobot (Excel)</button>
        </div>

        <div class="yb-tbl-wrap" style="margin-top:14px">
        <table class="yb-tbl">
            <thead><tr><th>#</th><th>Xodim</th><th>Lavozim</th><th>Bo'lim</th><th class="num">Asosiy maosh</th><th class="num" style="color:#d97706" title="Shu oy loyihalaridagi barcha ishlar ulushi — tugallangan va kutayotgan birga">Hisoblangan</th><th class="num" style="color:#dc2626" title="Xodimga mijoz to'lagan ulushidan ko'proq berilgan ishbay pul (ma'muriy oylik kirmaydi)">Ortiqcha to'langan</th><th class="num" style="color:#16a34a" title="Mijoz to'lagan ulushga mutanosib ochilgan summa (+ oklad) − berilgani. Mijoz hali to'lamagan qismi kirmaydi.">To'lanishi kerak</th><th class="num" style="color:#2563eb">To'landi</th><th class="num" style="color:#f97316">Kutayotgan</th><th>To'lov holati</th><th>To'lov sanasi</th><th style="text-align:right">Amallar</th></tr></thead>
            <tbody>
            @php $grpPrev = null; $grpNo = 0; @endphp
            @forelse($S['rows'] as $r)
                @if($r['roleKey'] !== $grpPrev)
                    @php
                        $grpPrev = $r['roleKey']; $grpNo = 0;
                        $gRows = $S['rows']->where('roleKey', $r['roleKey']);
                        $gMam = $r['roleKey'] === 'mamuriy';
                    @endphp
                    <tr class="yb-grp {{ $gMam ? 'mam' : '' }}">
                        <td colspan="5">
                            {{ $gMam ? "🏢 Ma'muriy oylik" : '🛠 Ishbay oylik' }}
                            <span>{{ $gMam ? "direktor, admin, menejer, buxgalter — firma foydasidan" : "toposyomka, ariza, eskiz loyiha — ishdan (komissiya)" }} · {{ $gRows->count() }} nafar
                                </span>
                        </td>
                        <td class="num" style="color:#d97706">{{ $fmt($gRows->sum('workTotal')) }}</td>
                        <td class="num" style="color:#dc2626">{{ $gRows->sum('overpaid') > 0 ? $fmt($gRows->sum('overpaid')) : '—' }}</td>
                        <td class="num" style="color:#16a34a">{{ $gRows->sum('remaining') > 0 ? $fmt($gRows->sum('remaining')) : '—' }}</td>
                        <td class="num" style="color:#2563eb">{{ $fmt($gRows->sum('paid')) }}</td>
                        <td class="num" style="color:#f97316">{{ $gRows->sum('pendingComm') > 0 ? $fmt($gRows->sum('pendingComm')) : '—' }}</td>
                        <td colspan="3"></td>
                    </tr>
                @endif
                @php $grpNo++; $isMam = $r['roleKey'] === 'mamuriy'; @endphp
                <tr>
                    <td>{{ $grpNo }}</td>
                    <td style="white-space:nowrap"><span class="yb-av" style="display:inline-flex;width:26px;height:26px;font-size:11px;margin-right:6px;vertical-align:middle;background:{{ $avColors[$r['user']->id % 7] }}">{{ $initials($r['user']->name) }}</span><b>{{ $r['user']->name }}</b>
                        @if($w = $r['work'])
                            <span class="yb-work">{{ $w['done_projects'] + $w['pending_count'] }} ta ish · {{ $w['done_projects'] }} tugallangan @if($w['pending_count'] > 0) · {{ $w['pending_count'] }} kutayotgan @endif</span>
                        @endif
                    </td>
                    <td>{{ $r['position'] }}</td>
                    <td><span class="yb-stype {{ $isMam ? 'mam' : '' }}" style="margin-left:0" wire:click="toggleUserSalaryType({{ $r['user']->id }})"
                              wire:confirm="{{ $r['user']->name }} — {{ $isMam ? 'ISHBAY' : "MA'MURIY" }} guruhiga o'tkazilsinmi? Turi tanlanmagan eski to'lovlari ham shu turga o'tadi."
                              title="Bosib boshqa guruhga o'tkazing">{{ $isMam ? "🏢 Ma'muriy" : '🛠 Ishbay' }}</span></td>
                    <td class="num" style="font-weight:500">{{ $r['base'] > 0 ? $fmt($r['base']) : '—' }}</td>
                    <td class="num" style="color:#d97706">
                        {{ $r['workTotal'] > 0 ? $fmt($r['workTotal']) : '—' }}
                        @if($r['work'] && $r['work']['pending_count'] > 0)<span class="yb-sub">tugallangan: {{ $fmt($r['work']['done_comm']) }}</span>@endif
                    </td>
                    <td class="num" style="color:#dc2626">{{ $r['overpaid'] > 0 ? $fmt($r['overpaid']) : '—' }}</td>
                    <td class="num" style="color:#16a34a">{{ $r['remaining'] > 0 ? $fmt($r['remaining']) : '—' }}</td>
                    <td class="num" style="color:#2563eb">{{ $r['paid'] > 0 ? $fmt($r['paid']) : '—' }}</td>
                    <td class="num" style="color:#f97316">
                        @if($r['pendingCount'] > 0) {{ $r['pendingCount'] }} ta <span class="yb-sub" style="color:#f97316">{{ $fmt($r['pendingComm']) }}</span> @else — @endif
                    </td>
                    <td style="white-space:nowrap">
                        @switch($r['status'])
                            @case('tolangan') <span class="yb-pill ok">✔ To'langan</span> @break
                            @case('qisman') <span class="yb-pill" style="background:#fef3c7;color:#b45309">◐ Qisman</span> @break
                            @case('tolanmagan') <span class="yb-pill c">⏰ To'lanmagan</span> @break
                            @default <span class="yb-sub">hisoblanmagan</span>
                        @endswitch
                    </td>
                    <td>{{ $r['paidAt']?->format('d.m.Y') ?? '—' }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        @if($r['remaining'] > 0)
                            <button type="button" class="yb-act" title="Maosh to'lash" wire:click="openPay({{ $r['user']->id }})">💵</button>
                        @endif
                        <button type="button" class="yb-act" title="Shu oydagi to'lovlar (tahrirlash/o'chirish)" wire:click="$set('historyUserId', {{ $r['user']->id }})" @disabled(!$r['payCount'])>⋯</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="yb-empty">Xodim topilmadi</td></tr>
            @endforelse
            </tbody>
            @if($S['rows']->isNotEmpty())
                <tfoot><tr style="font-weight:800;background:var(--yb-soft)">
                    <td colspan="4" style="padding:11px 8px">Jami:</td>
                    <td class="num">{{ $fmt($S['rows']->sum('base')) }}</td>
                    <td class="num" style="color:#d97706">{{ $fmt($S['rows']->sum('workTotal')) }}</td>
                    <td class="num" style="color:#dc2626">{{ $fmt($S['rows']->sum('overpaid')) }}</td>
                    <td class="num" style="color:#16a34a">{{ $fmt($S['rows']->sum('remaining')) }}</td>
                    <td class="num" style="color:#2563eb">{{ $fmt($S['rows']->sum('paid')) }}</td>
                    <td class="num" style="color:#f97316">{{ $fmt($S['rows']->sum('pendingComm')) }}</td>
                    <td colspan="3"></td>
                </tr></tfoot>
            @endif
        </table>
        </div>
        <div class="yb-sub" style="margin-top:8px">Ustunlar Oylik hisobot bilan bir xil: Hisoblangan — shu oy loyihalaridagi barcha ishlar ulushi; To'lanishi kerak — mijoz to'lagan ulushga mutanosib ochilgan summa (+ oklad) − berilgani; Ortiqcha to'langan — ochilgan ulushdan ko'p berilgan ishbay pul (ma'muriy oylik kirmaydi); Kutayotgan — hali tugallanmagan ishlar. Berilgan maosh Buxgalteriya xarajatlariga avtomatik yoziladi.</div>
    </div>

    {{-- Shu oydagi to'lovlar tarixi --}}
    @if($S['historyUser'])
        @teleport('body')
        <div class="yb yb-ov" x-data="{ d: false }" @mousedown.self="d = true" @mouseup.self="if (d) $wire.$set('historyUserId', null); d = false">
            <div class="yb-modal">
                <div class="yb-modal-h" style="background:#dbeafe">{{ $S['historyUser']->name }} — {{ $monthLabel }} to'lovlari <button type="button" wire:click="$set('historyUserId', null)">×</button></div>
                <div class="yb-modal-b">
                    @forelse($S['history'] as $h)
                        <div class="yb-li" style="border-bottom:1px solid var(--yb-bd)">
                            <div style="min-width:0">{{ $h->paid_at->format('d.m.Y') }} · <b class="yb-g">{{ $fmt($h->amount) }} so'm</b><span class="yb-sub">{{ $h->giver?->name }}@if($h->note) · {{ \Illuminate\Support\Str::limit(preg_replace('/^svc:\d+\|/', '', $h->note), 60) }}@endif</span></div>
                            <span style="margin-left:auto;white-space:nowrap">
                                <button type="button" class="yb-act" title="Tahrirlash" wire:click="editPay({{ $h->id }})">✏️</button>
                                <button type="button" class="yb-act del" title="O'chirish" wire:click="deletePay({{ $h->id }})" wire:confirm="Bu to'lovni o'chirasizmi? Oylik hisobot va Buxgalteriyadan ham o'chadi.">🗑</button>
                            </span>
                        </div>
                    @empty
                        <div class="yb-empty">To'lov yo'q</div>
                    @endforelse
                </div>
            </div>
        </div>
        @endteleport
    @endif

@elseif($tab === 'hisobotlar')
    <div class="yb-card">
        <div class="yb-h"><span>{{ $this->ybYear }}-yil hisoboti <span class="yb-sub">Tushum — to'lov sanasi bo'yicha; xarajat — Buxgalteriyadagi qoida bo'yicha. Xarajat yoki oylik summasini bosing — ro'yxati ochiladi.</span></span></div>
        <div class="yb-tbl-wrap">
                    @php
                // Yig'iladigan ustunlar (tartib jadvaldagi bilan bir xil). Yig'ilgani hamma adminlarda bir xil.
                $repCols = [
                    'loyiha'  => ["Loyihalar summasi", "Shu oyda ochilgan loyihalarning shartnoma summasi (Dashboard'dagi kabi). Ostida: bekor qilingan va mijozlar qarzi", '#0f766e'],
                    'tushum'  => ["Tushum (kelgan pul)", "Shu oy loyihalari uchun mijozlardan haqiqatda kelgan pul", '#15803d'],
                    'xarajat' => ["Xarajat", "Oylik va avanslarsiz chiqimlar — bosing: ro'yxat", '#dc2626'],
                    'ishbay'  => ["Ishbay oylik: kerak / to'langan", "Toposyomka, ariza, eskiz ishlaridan. Kerak — shu oy loyihalaridagi barcha ishlar ulushi (Oylik hisobotdagi 'Hisoblangan'). To'langan — haqiqatda berilgan.", '#b45309'],
                    'mamuriy' => ["Ma'muriy oylik", "Direktor, admin, buxgalter va h.k. — ishga bog'liq emas, firma daromadidan", '#7c3aed'],
                    'chiqim'  => ["Jami chiqim", "Xarajat + barcha oyliklar", '#dc2626'],
                    'foyda'   => ["Sof foyda: to'liq / hozirgi", "To'liq — loyihalar summasi − xarajat − ishbay oylik (hisoblangan); Hozirgi — tushgan pul − xarajat − ishbay oylik (mijoz to'lagani bo'yicha). Ma'muriy oylik kirmaydi.", '#15803d'],
                    'qoldiq'  => ["Qoldiq", "Tushum − jami chiqim: shu oy puli hamyonlarda qancha qoldi (bosing — Naqd, Karta, Bank)", '#2563eb'],
                ];
                $colX = array_values(array_intersect(array_keys($repCols), $repColsHidden));
                // Yashirilgan ustun / oy — qatori va ustuni joyida qoladi, faqat qiymatlar 0
                // ko'rsatiladi va "Jami"ga ham 0 bo'lib kiradi (bazadagi ma'lumot o'zgarmaydi).
                $colFields = [
                    'loyiha'  => ['allSum', 'allCount', 'cancelSum', 'debtSum'],
                    'tushum'  => ['income'],
                    'xarajat' => ['other'],
                    'ishbay'  => ['ishbay', 'ishbayDue', 'ishbayEarned'],
                    'mamuriy' => ['mamuriy'],
                    'chiqim'  => ['expense'],
                    'foyda'   => ['fullProfit', 'curProfit', 'projCount', 'projSum'],
                    'qoldiq'  => ['qoldiq', 'cross'],
                ];
                $hid = array_map('intval', $repHidden);
                $chartV = array_map(function ($c) use ($hid, $colX, $colFields) {
                    $c['hidden'] = in_array($c['m'], $hid, true);
                    foreach ($colFields as $ck => $fields) {
                        if ($c['hidden'] || in_array($ck, $colX, true)) foreach ($fields as $fk) $c[$fk] = 0;
                    }
                    return $c;
                }, $chart);
                $colIdx = array_flip(array_keys($repCols));
            @endphp
            @if($colX)
                <style>
                    @foreach($colX as $ck)
                        .yb-rep td:nth-child({{ $colIdx[$ck] + 2 }}) .yb-cell-link{pointer-events:none;border-bottom-color:transparent}
                    @endforeach
                </style>
            @endif
        <table class="yb-tbl yb-rep">
            <thead><tr><th>Oy</th>
                @foreach($repCols as $ck => [$cl, $ct, $cc])
                    @php $isX = in_array($ck, $colX, true); @endphp
                    <th class="num yb-colh {{ $isX ? 'cx' : '' }}" title="{{ $isX ? $cl . ' — yashirilgan, ko\'rsatish uchun bosing' : $ct }}">
                        <button type="button" wire:click="toggleRepCol('{{ $ck }}')" style="--cc:{{ $cc }}">
                            <span class="yb-colh-l">{{ $cl }}</span><span class="yb-colh-c" title="{{ $isX ? 'Ko\'rsatish' : 'Yashirish (qiymatlar 0 bo\'ladi)' }}">{{ $isX ? '🙈' : '👁' }}</span>
                        </button>
                    </th>
                @endforeach
            </tr></thead>
            <tbody>
            @foreach($chartV as $c)
                @php
                    $isHid = $c['hidden'];
                    $due  = $isHid || in_array('ishbay', $colX, true) ? null : ($salaryDue[$c['m']] ?? null);
                    $mDue = $isHid || in_array('mamuriy', $colX, true) ? null : ($mamuriyDue[$c['m']] ?? null);
                @endphp
                <tr wire:click="ybSetMonth({{ $c['m'] }})" @class(['yb-zero-row' => $isHid]) style="cursor:pointer;{{ $c['m'] === $this->ybMonth ? 'font-weight:700' : '' }}">
                    <td style="white-space:nowrap"><button type="button" @class(['yb-eye', 'off' => $isHid]) wire:click.stop="toggleRepMonth({{ $c['m'] }})" title="{{ $isHid ? 'Ko\'rsatish' : 'Bu oyni yashirish (qiymatlar 0 bo\'ladi)' }}">{{ $isHid ? '🙈' : '👁' }}</button> {{ $c['label'] }}</td>
                    <td class="num" style="color:#0f766e">
                        {{ $fmt($c['allSum']) }}
                        @if($c['allCount'])<span class="yb-sub">{{ $c['allCount'] }} ta @if($c['cancelSum'] > 0) · bekor {{ $fmt($c['cancelSum']) }} @endif @if($c['debtSum'] > 0) · qarz {{ $fmt($c['debtSum']) }} @endif</span>@endif
                    </td>
                    <td class="num yb-g">{{ $fmt($c['income']) }}</td>
                    <td class="num yb-r"><span class="yb-cell-link" wire:click.stop="openRepDetail({{ $c['m'] }}, 'xarajat')" title="Xarajatlar ro'yxati">{{ $fmt($c['other']) }}</span></td>
                    <td class="num" style="color:#b45309">
                        <span class="yb-cell-link" wire:click.stop="openRepDetail({{ $c['m'] }}, 'ishbay')" title="Ishbay oylik ro'yxati">
                            @if($due !== null && $due > 0)<span style="color:var(--yb-mu);font-weight:600">{{ $fmt($due) }}</span> / @endif{{ $fmt($c['ishbay']) }}
                        </span>
                        @if($due !== null && $due > $c['ishbay'])
                            <span class="yb-sub">qoldiq {{ $fmt($due - $c['ishbay']) }}</span>
                        @endif
                    </td>
                    <td class="num" style="color:#7c3aed">
                        <span class="yb-cell-link" wire:click.stop="openRepDetail({{ $c['m'] }}, 'mamuriy')" title="Ma'muriy oylik ro'yxati">
                            @if($mDue !== null && $mDue > 0)<span style="color:var(--yb-mu);font-weight:600">{{ $fmt($mDue) }}</span> / @endif{{ $fmt($c['mamuriy']) }}
                        </span>
                    </td>
                    <td class="num yb-r">{{ $fmt($c['expense']) }}</td>
                    <td class="num">
                        @if($c['projSum'] != 0 || $c['income'] != 0 || $c['expense'] != 0)
                            <span class="yb-cell-link" wire:click.stop="openProfit({{ $c['m'] }})" title="Hisob-kitobni ko'rish">
                                <span style="color:{{ $c['fullProfit'] < 0 ? '#dc2626' : '#15803d' }}">{{ $c['fullProfit'] < 0 ? '−' : '' }}{{ $fmt(abs($c['fullProfit'])) }}</span>
                                / <span style="color:{{ $c['curProfit'] < 0 ? '#dc2626' : 'inherit' }}">{{ $c['curProfit'] < 0 ? '−' : '' }}{{ $fmt(abs($c['curProfit'])) }}</span>
                            </span>
                            <span class="yb-sub">{{ $c['projCount'] }} ta loyiha</span>
                        @else 0 @endif
                    </td>
                    <td class="num {{ $c['qoldiq'] < 0 ? 'yb-r' : '' }}">
                        @if($c['income'] != 0 || $c['expense'] != 0 || $c['cross'] != 0)<span class="yb-cell-link" wire:click.stop="openWallet({{ $c['m'] }})" title="Qaysi hamyonda qancha — Naqd, Karta, Bank">{{ $c['qoldiq'] < 0 ? '−' : '' }}{{ $fmt(abs($c['qoldiq'])) }}</span>@else{{ $fmt($c['qoldiq']) }}@endif
                        @if($c['cross'] != 0)<span class="yb-sub" title="Boshqa oylarga / oylardan o'tkazilgan pul">{{ $c['cross'] > 0 ? '⇠ +' : '⇢ −' }}{{ $fmt(abs($c['cross'])) }} o'tkazma</span>@endif
                    </td>
                </tr>
            @endforeach
                @php
                    $sum = fn ($k) => array_sum(array_column($chartV, $k));
                    $yearDue = in_array('ishbay', $colX, true) ? 0 : array_sum(array_map(fn ($c) => $c['hidden'] ? 0 : (float) ($salaryDue[$c['m']] ?? 0), $chartV));
                    $yIncome = $sum('income'); $yExpense = $sum('expense'); $yIsh = $sum('ishbay');
                    $yFull = $sum('fullProfit'); $yCur = $sum('curProfit');
                @endphp
                <tr style="font-weight:800">
                    <td>Jami @if($hid)<span class="yb-sub">{{ count($hid) }} oy yashirilgan</span>@endif</td>
                    <td class="num" style="color:#0f766e">{{ $fmt($sum('allSum')) }}<span class="yb-sub">{{ $sum('allCount') }} ta · qarz {{ $fmt($sum('debtSum')) }}</span></td>
                    <td class="num yb-g">{{ $fmt($yIncome) }}</td>
                    <td class="num yb-r">{{ $fmt($sum('other')) }}</td>
                    <td class="num" style="color:#b45309">
                        @if($yearDue > 0)<span style="color:var(--yb-mu);font-weight:600">{{ $fmt($yearDue) }}</span> / @endif{{ $fmt($yIsh) }}
                        @if($yearDue > $yIsh)<span class="yb-sub">qoldiq {{ $fmt($yearDue - $yIsh) }}</span>@endif
                    </td>
                    <td class="num" style="color:#7c3aed">{{ $fmt($sum('mamuriy')) }}</td>
                    <td class="num yb-r">{{ $fmt($yExpense) }}</td>
                    <td class="num"><span style="color:{{ $yFull < 0 ? '#dc2626' : '#15803d' }}">{{ $yFull < 0 ? '−' : '' }}{{ $fmt(abs($yFull)) }}</span> / {{ $yCur < 0 ? '−' : '' }}{{ $fmt(abs($yCur)) }}<span class="yb-sub">{{ $sum('projCount') }} ta loyiha</span></td>
                    <td class="num">{{ $fmt($sum('qoldiq')) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    {{-- Yil hisoboti / Hodimlar — har hodim × oy: to'langan / to'lash kerak / ortiqcha (YangiBux $staffYear) --}}
    @if($staffYear)
    @php $SY = $staffYear; @endphp
    <div class="yb-card" style="margin-top:16px">
        <div class="yb-h"><span>{{ $this->ybYear }}-yil hisoboti / Hodimlar
            <span class="yb-sub">Har oy: <b class="yb-g">to'langan</b> · <b class="yb-r">to'lash kerak</b> · <b style="color:#ea580c">ortiqcha to'langan</b> — Oylik maosh tabidagi qoida bo'yicha</span></span></div>
        <div class="yb-tbl-wrap">
        <table class="yb-tbl yb-staffyear">
            <thead><tr>
                <th>Hodim</th>
                @foreach($SY['months'] as $m)<th class="num">{{ ['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'][$m - 1] }}</th>@endforeach
                <th class="num">Jami</th>
            </tr></thead>
            <tbody>
            @forelse($SY['rows'] as $r)
                <tr>
                    <td style="white-space:nowrap"><b>{{ $r['user']->name }}</b>@if(!$r['active'])<span class="yb-sub">ishdan bo'shagan</span>@endif</td>
                    @foreach($SY['months'] as $m)
                        @php $c = $r['cells'][$m]; @endphp
                        <td class="num yb-sy-cell">
                            <span class="yb-g">{{ $c['paid'] ? $fmt($c['paid']) : '—' }}</span>
                            @if($c['kerak'] > 0)<span class="yb-r">{{ $fmt($c['kerak']) }}</span>@endif
                            @if($c['ortiq'] > 0)<span style="color:#ea580c">+{{ $fmt($c['ortiq']) }}</span>@endif
                        </td>
                    @endforeach
                    <td class="num yb-sy-cell" style="background:var(--yb-soft)">
                        <span class="yb-g">{{ $fmt($r['sum']['paid']) }}</span>
                        @if($r['sum']['kerak'] > 0)<span class="yb-r">{{ $fmt($r['sum']['kerak']) }}</span>@endif
                        @if($r['sum']['ortiq'] > 0)<span style="color:#ea580c">+{{ $fmt($r['sum']['ortiq']) }}</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($SY['months']) + 2 }}" class="yb-empty">Bu yil hodimlarga to'lov yoki hisoblangan summa yo'q</td></tr>
            @endforelse
                <tr style="font-weight:800;background:var(--yb-soft)">
                    <td>Jami</td>
                    @foreach($SY['months'] as $m)
                        @php $t = $SY['tot'][$m] ?? ['paid' => 0, 'kerak' => 0, 'ortiq' => 0]; @endphp
                        <td class="num yb-sy-cell">
                            <span class="yb-g">{{ $fmt($t['paid']) }}</span>
                            @if($t['kerak'] > 0)<span class="yb-r">{{ $fmt($t['kerak']) }}</span>@endif
                            @if($t['ortiq'] > 0)<span style="color:#ea580c">+{{ $fmt($t['ortiq']) }}</span>@endif
                        </td>
                    @endforeach
                    <td class="num yb-sy-cell">
                        <span class="yb-g">{{ $fmt($SY['grand']['paid']) }}</span>
                        @if($SY['grand']['kerak'] > 0)<span class="yb-r">{{ $fmt($SY['grand']['kerak']) }}</span>@endif
                        @if($SY['grand']['ortiq'] > 0)<span style="color:#ea580c">+{{ $fmt($SY['grand']['ortiq']) }}</span>@endif
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
        <style>.yb-staffyear .yb-sy-cell span{display:block;line-height:1.35;white-space:nowrap}.yb-staffyear .yb-sy-cell .yb-r,.yb-staffyear .yb-sy-cell span[style]{font-size:11px;font-weight:700}</style>
    </div>
    @endif

    @if($profitInfo)
        @php $P = $profitInfo; @endphp
        @teleport('body')
        <div class="yb yb-ov" x-data="{ d: false }" @mousedown.self="d = true" @mouseup.self="if (d) $wire.closeProfit(); d = false">
            <div class="yb-modal" style="max-width:640px">
                <div class="yb-modal-h" style="background:#dcfce7">{{ $P['title'] }} — sof foyda <button type="button" wire:click="closeProfit">×</button></div>
                <div class="yb-modal-b">
                    <div class="yb-tbl-wrap">
                    <table class="yb-tbl">
                        <thead><tr><th></th><th class="num">To'liq<span class="yb-sub">mijozlar to'liq to'lasa</span></th><th class="num">Hozirgi<span class="yb-sub">tushgan pul bo'yicha</span></th></tr></thead>
                        <tbody>
                            <tr><td><b>Loyihalar</b> <span class="yb-sub">{{ $P['projCount'] }} ta loyiha shu oyda ochilgan @if($P['projOther']) · + {{ $P['projOther'] }} ta to'xtatilgan/bekor — faqat to'lagani ({{ $fmt($P['projOtherSum']) }}) @endif</span></td>
                                <td class="num yb-g">{{ $fmt($P['projSum']) }}<span class="yb-sub">loyihalar summasi</span></td>
                                <td class="num yb-g">{{ $fmt($P['income']) }}<span class="yb-sub">tushgan pul</span></td></tr>
                            <tr><td>− Xarajat</td><td class="num yb-r">{{ $fmt($P['other']) }}</td><td class="num yb-r">{{ $fmt($P['other']) }}</td></tr>
                            <tr><td>− Ishbay oylik <span class="yb-sub">toposyomka, ariza, eskiz</span></td>
                                <td class="num yb-r">{{ $fmt($P['ishbayDue']) }}<span class="yb-sub">hisoblangan</span></td>
                                <td class="num yb-r">{{ $fmt($P['ishbayEarned']) }}<span class="yb-sub">mijoz to'lagani bo'yicha · berilgan {{ $fmt($P['ishbay']) }}</span></td></tr>
                        </tbody>
                        <tfoot><tr style="font-weight:800;background:var(--yb-soft)">
                            <td style="font-size:14px">= Sof foyda</td>
                            <td class="num" style="font-size:15px;color:{{ $P['fullProfit'] < 0 ? '#dc2626' : '#15803d' }}">{{ $P['fullProfit'] < 0 ? '−' : '' }}{{ $fmt(abs($P['fullProfit'])) }}</td>
                            <td class="num" style="font-size:15px;color:{{ $P['curProfit'] < 0 ? '#dc2626' : 'inherit' }}">{{ $P['curProfit'] < 0 ? '−' : '' }}{{ $fmt(abs($P['curProfit'])) }}</td>
                        </tr></tfoot>
                    </table>
                    </div>
                    @if($P['projSum'] > $P['income'])
                        <div class="yb-sub" style="font-size:12px;margin-top:10px">Farq <b>{{ $fmt($P['fullProfit'] - $P['curProfit']) }}</b> — mijozlar hali to'lamagan <b>{{ $fmt($P['projSum'] - $P['income']) }}</b> so'm (qarz) hisobidan; ular to'lagach, hozirgi foyda to'liq foydaga yetadi.</div>
                    @endif
                    <div class="yb-sub" style="font-size:12px;margin-top:6px">Ma'muriy oylik ({{ $fmt($P['mamuriy']) }}) bu hisobga kirmaydi — u "Qoldiq" ustunida (hamyonlardagi pul) hisobga olingan.</div>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    @if($wallet)
        @teleport('body')
        <div class="yb yb-ov" x-data="{ d: false }" @mousedown.self="d = true" @mouseup.self="if (d) $wire.closeWallet(); d = false">
            <div class="yb-modal" style="max-width:760px">
                <div class="yb-modal-h" style="background:#dbeafe">{{ $wallet['title'] }} — qoldiq hamyonlar bo'yicha <button type="button" wire:click="closeWallet">×</button></div>
                <div class="yb-modal-b" style="max-height:72vh;overflow:auto">
                    <div class="yb-sub" style="font-size:11.5px;margin-bottom:10px">Har bir hamyon: shu oy loyihalaridan tushgan kirim − shu oyga yozilgan chiqim ± hamyonlar orasidagi o'tkazmalar. Qoldiqlar yig'indisi = jadvaldagi "Qoldiq" (tushum − jami chiqim).</div>
                    @php
                        $wIc = ['naqd' => '💵', 'karta' => '💳', 'bank' => '🏦'];
                        $wGroups = ['company' => 'Kompaniya hamyonlari', 'personal' => 'Shaxsiy hisoblar (ulush)', 'person' => 'Xodim kartalari', 'none' => 'Boshqa'];
                        $wPrev = null;
                    @endphp
                    <div class="yb-tbl-wrap">
                    <table class="yb-tbl">
                        <thead><tr><th>Hamyon</th><th class="num">Kirim</th><th class="num">Chiqim</th><th class="num" title="Hamyonlar orasidagi o'tkazmalar (+ kelgan, − ketgan)">O'tkazma</th><th class="num">Qoldiq</th></tr></thead>
                        <tbody>
                        @forelse($wallet['rows'] as $w)
                            @if($w['group'] !== $wPrev && count(array_unique(array_column($wallet['rows'], 'group'))) > 1)
                                @php $wPrev = $w['group']; @endphp
                                <tr class="yb-grp"><td colspan="5">{{ $wGroups[$w['group']] }}</td></tr>
                            @endif
                            <tr>
                                <td><b>{{ $wIc[$w['type']] ?? ($w['group'] === 'none' ? '❔' : '👛') }} {{ $w['name'] }}</b>
                                    @if($w['group'] === 'none')<span class="yb-sub">eski yozuvlar — qaysi hamyondan ekani belgilanmagan</span>@endif</td>
                                <td class="num yb-g">{{ $w['in'] ? $fmt($w['in']) : '—' }}</td>
                                <td class="num yb-r">{{ $w['out'] ? $fmt($w['out']) : '—' }}</td>
                                <td class="num" style="color:var(--yb-mu)">{{ $w['tr'] ? ($w['tr'] > 0 ? '+' : '−') . $fmt(abs($w['tr'])) : '—' }}</td>
                                <td class="num {{ $w['net'] < 0 ? 'yb-r' : '' }}" style="font-size:14px">{{ $w['net'] < 0 ? '−' : '' }}{{ $fmt(abs($w['net'])) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="yb-empty">Bu oyda harakat yo'q</td></tr>
                        @endforelse
                        </tbody>
                        <tfoot><tr style="font-weight:800;background:var(--yb-soft)">
                            <td>Jami (qoldiq)</td>
                            <td class="num yb-g">{{ $fmt(array_sum(array_column($wallet['rows'], 'in'))) }}</td>
                            <td class="num yb-r">{{ $fmt(array_sum(array_column($wallet['rows'], 'out'))) }}</td>
                            <td class="num" style="color:var(--yb-mu)">{{ $fmt(array_sum(array_column($wallet['rows'], 'tr'))) }}</td>
                            <td class="num" style="font-size:15px">{{ $fmt(array_sum(array_column($wallet['rows'], 'net'))) }}</td>
                        </tr></tfoot>
                    </table>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0 8px">
                        <b>⇄ Oydan oyga o'tkazmalar</b>
                        <button type="button" class="yb-btn" wire:click="openMonthTransfer({{ $wallet['m'] }})">⇄ Boshqa oyga o'tkazish</button>
                    </div>
                    @forelse($wallet['cross'] as $t)
                        @php $tFrom = $t->month ?: $t->transfer_date->format('Y-m'); $isOut = $tFrom === sprintf('%04d-%02d', $this->ybYear, $wallet['m']); @endphp
                        <div class="yb-li" style="border-bottom:1px solid var(--yb-bd);gap:10px">
                            <span class="yb-pill {{ $isOut ? 'c' : 'k' }}" style="white-space:nowrap">{{ $isOut ? 'ketdi' : 'keldi' }}</span>
                            <div style="min-width:0;flex:1">
                                <b>{{ \Carbon\Carbon::createFromFormat('Y-m', $tFrom)->translatedFormat('F') }} · {{ $t->fromAccount?->name }}</b> → <b>{{ \Carbon\Carbon::createFromFormat('Y-m', $t->to_month)->translatedFormat('F') }} · {{ $t->toAccount?->name }}</b>
                                <span class="yb-sub">{{ $t->transfer_date->format('d.m.Y') }} · {{ $t->createdBy?->name }}@if($t->comment) · {{ $t->comment }}@endif</span>
                            </div>
                            <b class="{{ $isOut ? 'yb-r' : 'yb-g' }}" style="white-space:nowrap">{{ $isOut ? '−' : '+' }}{{ $fmt($t->amount) }}</b>
                            <button type="button" class="yb-act del" title="Bekor qilish" wire:click="deleteMonthTransfer({{ $t->id }})" wire:confirm="Bu o'tkazmani bekor qilasizmi? Pul o'z oyiga qaytadi.">🗑</button>
                        </div>
                    @empty
                        <div class="yb-sub" style="font-size:12px">Bu oyda oydan oyga o'tkazma yo'q. Ortiqcha pulni keyingi oyning Naqd yoki Kartasiga o'tkazish uchun yuqoridagi tugmani bosing.</div>
                    @endforelse
                </div>
            </div>
        </div>
        @endteleport
    @endif

    @if($repDetail)
        @php $sal = $repKind !== 'xarajat'; $mam = $repKind === 'mamuriy'; $sc = $mam ? '#7c3aed' : '#b45309'; @endphp
        @teleport('body')
        <div class="yb yb-ov" x-data="{ d: false }" @mousedown.self="d = true" @mouseup.self="if (d) $wire.closeRepDetail(); d = false">
            <div class="yb-modal" style="max-width:820px">
                <div class="yb-modal-h" style="background:{{ $mam ? '#ede9fe' : ($sal ? '#fef3c7' : '#fee2e2') }}">{{ $repDetail['title'] }} <button type="button" wire:click="closeRepDetail">×</button></div>
                <div class="yb-modal-b" style="max-height:72vh;overflow:auto">
                    @if($sal)
                        <div class="yb-sub" style="font-size:11.5px;margin-bottom:8px">
                            @if($mam) Direktor, admin, buxgalter va h.k. — ishga bog'liq emas, firma daromadidan beriladi. "Kerak" = belgilangan oklad.
                            @else Toposyomka, ariza, eskiz ishlaridan. "Kerak" = shu oy loyihalaridagi barcha ishlar ulushi (Oylik hisobotdagi "Hisoblangan"), "Mijoz to'lagani bo'yicha" = hozir to'lash mumkin bo'lgan qism.
                            @endif
                        </div>
                        @if(($repDetail['due'] ?? 0) > 0)
                            <div style="font-size:12.5px;margin-bottom:10px">
                                To'lanishi kerak: <b>{{ $fmt($repDetail['due']) }}</b> ·
                                to'langan: <b style="color:{{ $sc }}">{{ $fmt($repDetail['total']) }}</b> ·
                                qoldiq: <b class="yb-r">{{ $fmt(max(0, $repDetail['due'] - $repDetail['total'])) }}</b>
                            </div>
                        @endif
                        <div class="yb-tbl-wrap">
                        <table class="yb-tbl">
                            <thead><tr><th>Xodim</th>
                                <th class="num">{{ $mam ? 'Oklad (kerak)' : 'Kerak (hisoblangan)' }}</th>
                                @unless($mam)<th class="num" title="Mijozlar hozirgacha to'lagan qismiga to'g'ri keladigan oylik">Mijoz to'lagani bo'yicha</th>@endunless
                                <th class="num">Berilgan</th><th class="num">Qoldiq</th></tr></thead>
                            <tbody>
                            @forelse($repDetail['staff'] as $s)
                                <tr>
                                    <td>{{ $s['user']->name }}@if(!$s['user']->is_active)<span class="yb-sub">ishdan bo'shagan</span>@endif</td>
                                    <td class="num">{{ $s['full'] > 0 ? $fmt($s['full']) : '—' }}</td>
                                    @unless($mam)<td class="num" style="color:var(--yb-mu)">{{ $fmt($s['earned']) }}</td>@endunless
                                    <td class="num" style="color:{{ $sc }}">{{ $fmt($s['given']) }}</td>
                                    <td class="num {{ $s['left'] > 0 ? 'yb-r' : '' }}">{{ $s['full'] > 0 ? $fmt($s['left']) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="yb-empty">Ma'lumot yo'q</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        </div>
                        <div style="font-weight:800;margin:16px 0 6px">Berilgan to'lovlar ({{ $repDetail['list']->count() }} ta)</div>
                    @elseif(count($repDetail['groups']) > 1)
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
                            @foreach($repDetail['groups'] as $g)
                                <span style="background:var(--yb-soft);border:1px solid var(--yb-bd);border-radius:999px;padding:4px 10px;font-size:12px">{{ $g['label'] }} · <b class="yb-r">{{ $fmt($g['sum']) }}</b> <span style="color:var(--yb-mu)">({{ $g['count'] }})</span></span>
                            @endforeach
                        </div>
                    @endif

                    <div class="yb-tbl-wrap">
                    <table class="yb-tbl">
                        <thead><tr><th>Sana</th><th>{{ $sal ? 'Xodim' : 'Sabab' }}</th><th>{{ $sal ? 'Izoh' : 'Loyiha / xodim' }}</th><th>Hisob</th><th class="num">Summa</th></tr></thead>
                        <tbody>
                        @forelse($repDetail['list'] as $e)
                            <tr>
                                <td style="white-space:nowrap">{{ $e->expense_date?->format('d.m.Y') }}</td>
                                @if($sal)
                                    <td>{{ $e->user?->name ?? $e->responsible?->name ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit(preg_replace('/^svc:\d+\|/', '', (string) $e->comment), 60) ?: '—' }}</td>
                                @else
                                    <td>{{ $e->comment ?: '—' }}@if($e->note)<span class="yb-sub">{{ \Illuminate\Support\Str::limit($e->note, 80) }}</span>@endif</td>
                                    <td>
                                        @if($e->project)№{{ $e->project->seq_no }} · {{ $e->project->owner_name }}@endif
                                        <span class="yb-sub">{{ $e->responsible?->name ?? ($e->category ? '🏢 Firma' : '') }}</span>
                                    </td>
                                @endif
                                <td style="font-size:12px;color:var(--yb-mu)">{{ $e->account?->name ?? '—' }}</td>
                                <td class="num {{ $sal ? '' : 'yb-r' }}" @if($sal) style="color:{{ $sc }}" @endif>{{ $fmt($e->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="yb-empty">Bu oyda yozuv yo'q</td></tr>
                        @endforelse
                        </tbody>
                        @if($repDetail['list']->isNotEmpty())
                            <tfoot><tr style="font-weight:800"><td colspan="4">Jami</td><td class="num">{{ $fmt($repDetail['total']) }}</td></tr></tfoot>
                        @endif
                    </table>
                    </div>
                    <div style="margin-top:12px;text-align:right">
                        <button type="button" class="yb-btn" wire:click="repToOps">Kirim-chiqimda ochish →</button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif

@else
    <div class="yb-card yb-soon">
        <div>📁</div>
        <b>{{ $tabs[$tab] }} — tez orada</b>
        Buxgalteriya hujjatlari (shartnoma, shot-faktura, akt) shu yerda jamlanadi.
    </div>
@endif
</div>

{{-- ── Oydan oyga o'tkazma ── --}}
@if($showMt)
    @teleport('body')
    <div class="yb yb-ov">{{-- ma'lumot kiritiladi — tashqariga bosilganda yopilmaydi --}}
        <div class="yb-modal" style="max-width:520px">
            <div class="yb-modal-h" style="background:#dbeafe">⇄ Oydan oyga o'tkazish <button type="button" wire:click="$set('showMt', false)">×</button></div>
            <div class="yb-modal-b">
                @php
                    $mtAccs = $allAccounts->filter(fn ($a) => !$a->is_personal && !$a->user_id);
                    $mtName = fn ($ym) => $ym ? \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') : '';
                @endphp
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>Qaysi oydan <i>*</i></label>
                        <input type="month" class="yb-in" wire:model.live="mtFromYm">
                        @error('mtFromYm')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld">
                        <label>Qaysi oyga <i>*</i></label>
                        <input type="month" class="yb-in" wire:model.live="mtToYm">
                        @error('mtToYm')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>Qaysi hamyondan <i>*</i></label>
                        <select class="yb-in" wire:model.live="mtFromAcc">
                            <option value="">— tanlang —</option>
                            @foreach($mtAccs as $a)<option value="{{ $a->id }}">{{ $a->name }} · {{ $fmt($mtFromBal[$a->id] ?? 0) }}</option>@endforeach
                        </select>
                        @if($mtFromAcc)<div class="yb-sub" style="margin-top:4px">{{ $mtName($mtFromYm) }} puli: <b class="{{ ($mtFromBal[$mtFromAcc] ?? 0) > 0 ? 'yb-g' : 'yb-r' }}">{{ $fmt($mtFromBal[$mtFromAcc] ?? 0) }} so'm</b></div>@endif
                        @error('mtFromAcc')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld">
                        <label>Qaysi hamyonga <i>*</i></label>
                        <select class="yb-in" wire:model.live="mtToAcc">
                            <option value="">— tanlang —</option>
                            @foreach($mtAccs as $a)<option value="{{ $a->id }}">{{ $a->name }} · {{ $fmt($mtToBal[$a->id] ?? 0) }}</option>@endforeach
                        </select>
                        @if($mtToAcc)<div class="yb-sub" style="margin-top:4px">{{ $mtName($mtToYm) }} puli: <b class="{{ ($mtToBal[$mtToAcc] ?? 0) < 0 ? 'yb-r' : 'yb-g' }}">{{ $fmt($mtToBal[$mtToAcc] ?? 0) }} so'm</b></div>@endif
                        @error('mtToAcc')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="yb-fld">
                    <label>Summa (so'm) <i>*</i></label>
                    <input type="text" inputmode="numeric" class="yb-in" wire:model="mtAmount" placeholder="0"
                           x-data x-on:input="let v=$el.value.replace(/\D/g,'');$el.value=v.replace(/\B(?=(\d{3})+(?!\d))/g,' ')">
                    @error('mtAmount')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Izoh</label>
                    <input type="text" class="yb-in" wire:model="mtComment" placeholder="Masalan: avgust ortig'i sentabrga">
                </div>
                <div class="yb-sub" style="margin-bottom:10px">Pul {{ $mtName($mtFromYm) ?: 'tanlangan oy' }} hamyonidan ayirilib, {{ $mtName($mtToYm) ?: 'tanlangan oy' }} hamyoniga qo'shiladi. Hamyonlarning umumiy qoldig'i va sof foyda o'zgarmaydi — faqat oylar "Qoldiq"i.</div>
                <div style="display:flex;gap:10px">
                    <button type="button" class="yb-b2" wire:click="$set('showMt', false)">Bekor qilish</button>
                    <button type="button" class="yb-b2" style="background:#2563eb;border-color:#2563eb;color:#fff" wire:click="saveMonthTransfer" wire:loading.attr="disabled">O'tkazish</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
@endif

{{-- ── Kirim qo'shish: loyiha tanlash ── --}}
@if($showKirimPicker)
    @teleport('body')
    <div class="yb yb-ov">{{-- ma'lumot kiritiladi — tashqariga bosilganda yopilmaydi (matn belgilab sichqoncha tashqarida qo'yilsa ham) --}}
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
    @endteleport
@endif

{{-- ── Chiqim qo'shish / tahrirlash ── --}}
@if($showChiqimModal)
    @teleport('body')
    <div class="yb yb-ov">{{-- ma'lumot kiritiladi — tashqariga bosilganda yopilmaydi (matn belgilab sichqoncha tashqarida qo'yilsa ham) --}}
        <div class="yb-modal">
            <div class="yb-modal-h" style="background:#fee2e2">{{ $chiqimId ? 'Chiqimni tahrirlash' : "Chiqim qo'shish" }} <button type="button" wire:click="closeChiqim">×</button></div>
            <div class="yb-modal-b">
                @if($chRecurring)
                    <div class="yb-sub" style="background:#eff6ff;color:#1d4ed8;border-radius:8px;padding:8px 10px;margin-bottom:12px;font-size:12px">🔁 Doimiy to'lov: <b>{{ $chRecurring->name }}</b> — summani kerak bo'lsa o'zgartiring (masalan, svet har oy har xil).</div>
                @endif
                <div class="yb-fld">
                    <label>Chiqim turi <i>*</i></label>
                    <div class="yb-kind">
                        @foreach(\App\Models\Expense::kindOptions() as $kk => $kl)
                            <label class="yb-kind-opt {{ $chKind === $kk ? 'on' : '' }} {{ $kk }}">
                                <input type="radio" wire:model.live="chKind" value="{{ $kk }}" style="display:none">
                                {{ $kk === 'oylik' ? '💵' : '💼' }} {{ $kl }}
                            </label>
                        @endforeach
                    </div>
                    @error('chKind')<div class="yb-err">{{ $message }}</div>@enderror
                    @if($chKind === 'oylik' && !$chiqimId)
                        <div class="yb-sub" style="margin-top:6px">Xodim to'lovlariga (Oylik hisobot) ham yoziladi — xodimning "to'lanishi kerak" summasi kamayadi.</div>
                    @elseif($chKind === 'oylik' && $chiqimId)
                        <div class="yb-sub" style="margin-top:6px">Saqlansa — tanlangan xodimning oylik/avans to'loviga aylanadi (Oylik maosh va Oylik hisobotda ko'rinadi). Summa va hisob o'zgarmaydi.</div>
                    @endif
                </div>
                @if($chKind === 'oylik')
                    <div class="yb-fld">
                        <label>Qaysi oy uchun <i>*</i></label>
                        <input type="month" class="yb-in" wire:model="chSalaryMonth">
                        @error('chSalaryMonth')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    @include('filament.pages.partials.salary-type-pick', ['model' => 'chSalaryType', 'value' => $chSalaryType])
                    @error('chSalaryType')<div class="yb-err" style="margin-top:-8px;margin-bottom:10px">{{ $message }}</div>@enderror
                @endif
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>{{ $chKind === 'oylik' && !$chiqimId ? "To'lov sanasi" : 'Sana' }} <i>*</i></label>
                        <input type="date" class="yb-in" wire:model="chDate" @unless($chiqimId) min="{{ sprintf('%04d-%02d-01', $curYear, $curMonth) }}" max="{{ \Carbon\Carbon::create($curYear, $curMonth, 1)->endOfMonth()->format('Y-m-d') }}" @endunless>
                        @unless($chiqimId)<div class="yb-sub" style="margin-top:3px">{{ $monthLabel }} pulidan yechiladi</div>@endunless
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
                    <label>Tavsif @if(!($chKind === 'oylik' && !$chiqimId))<i>*</i>@else (ixtiyoriy)@endif</label>
                    <input type="text" class="yb-in" wire:model="chComment" placeholder="{{ $chKind === 'oylik' ? 'Masalan: avans' : 'Masalan: Qurilish materiali xarid qilindi' }}">
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
                        <label>Qaysi hisobdan (pul qayerdan chiqdi) <i>*</i></label>
                        @include('filament.pages.partials.yangi-bux-account-select', ['model' => 'chAccountId', 'accountsList' => $allAccounts, 'selectedId' => $chAccountId, 'monthMode' => true])
                        @error('chAccountId')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld">
                        <label>{{ $chKind === 'oylik' ? 'Xodim (kimga)' : 'Kim uchun / kim qildi' }} @if($chKind === 'oylik')<i>*</i>@else(ixtiyoriy)@endif</label>
                        <select class="yb-in" wire:model.live="chResponsibleId">
                            <option value="">{{ $chKind === 'oylik' ? '—' : '🏢 Firma (umumiy xarajat)' }}</option>
                            @foreach($staffUsers as $su)<option value="{{ $su->id }}">{{ $su->name }}</option>@endforeach
                        </select>
                        @error('chResponsibleId')<div class="yb-err">{{ $message }}</div>@enderror
                        @if($chKind !== 'oylik')<div class="yb-sub" style="margin-top:4px">Xodim tanlanmasa — firma xarajati. Balansga ta'sir qilmaydi.</div>@endif
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
    @endteleport
@endif

{{-- ── Doimiy to'lov qo'shish / tahrirlash ── --}}
@if($showRecModal)
    @teleport('body')
    <div class="yb yb-ov">{{-- ma'lumot kiritiladi — tashqariga bosilganda yopilmaydi (matn belgilab sichqoncha tashqarida qo'yilsa ham) --}}
        <div class="yb-modal" style="max-width:440px">
            <div class="yb-modal-h" style="background:#dbeafe">{{ $recId ? "Doimiy to'lovni tahrirlash" : "Doimiy to'lov qo'shish" }} <button type="button" wire:click="$set('showRecModal', false)">×</button></div>
            <div class="yb-modal-b">
                <div class="yb-fld">
                    <label>Nomi <i>*</i></label>
                    <input type="text" class="yb-in" wire:model="recName" placeholder="Masalan: Arenda, Svet, Wi-Fi">
                    @error('recName')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Har oylik summa (so'm) <i>*</i></label>
                    <input type="text" inputmode="numeric" class="yb-in" wire:model="recAmount" placeholder="0"
                           x-data x-on:input="let v=$el.value.replace(/\D/g,'');$el.value=v.replace(/\B(?=(\d{3})+(?!\d))/g,' ')">
                    @error('recAmount')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <div class="yb-kind">
                        <label class="yb-kind-opt {{ $recFixed ? 'on xarajat' : '' }}"><input type="radio" wire:model.live="recFixed" value="1" style="display:none"> Aniq summa <span class="yb-sub">arenda, wi-fi</span></label>
                        <label class="yb-kind-opt {{ !$recFixed ? 'on xarajat' : '' }}"><input type="radio" wire:model.live="recFixed" value="0" style="display:none"> Taxminiy (~) <span class="yb-sub">svet, gaz</span></label>
                    </div>
                </div>
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>Qaysi hisobdan (odatda)</label>
                        @include('filament.pages.partials.yangi-bux-account-select', ['model' => 'recAccountId', 'accountsList' => $allAccounts, 'selectedId' => $recAccountId, 'noBalance' => true])
                        @error('recAccountId')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld">
                        <label>Har oyning nechanchi sanasigacha</label>
                        <input type="number" min="1" max="31" class="yb-in" wire:model="recDueDay" placeholder="Masalan: 5">
                        @error('recDueDay')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="yb-sub" style="margin-bottom:10px">Summani o'zgartirish keyingi to'lovlarga ta'sir qiladi. Oldin to'langan chiqimlar o'zgarmaydi.</div>
                <div style="display:flex;gap:10px;margin-top:6px">
                    @if($recId)
                        <button type="button" class="yb-b2" style="flex:0 0 auto;color:#dc2626" wire:click="deleteRec({{ $recId }})" wire:confirm="Ro'yxatdan olib tashlansinmi? Oldin to'langan chiqimlar o'chmaydi.">O'chirish</button>
                    @endif
                    <button type="button" class="yb-b2" wire:click="$set('showRecModal', false)">Bekor qilish</button>
                    <button type="button" class="yb-b2 red" style="background:#2563eb;border-color:#2563eb" wire:click="saveRec" wire:loading.attr="disabled" wire:target="saveRec">Saqlash</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
@endif

{{-- ── Maosh to'lash / tahrirlash ── --}}
@if($showPayModal)
    @teleport('body')
    <div class="yb yb-ov">{{-- ma'lumot kiritiladi — tashqariga bosilganda yopilmaydi (matn belgilab sichqoncha tashqarida qo'yilsa ham) --}}
        <div class="yb-modal" style="max-width:440px">
            <div class="yb-modal-h" style="background:#dcfce7">{{ $payEditId ? "To'lovni tahrirlash" : "Maosh to'lash — " . $monthLabel }} <button type="button" wire:click="$set('showPayModal', false)">×</button></div>
            <div class="yb-modal-b">
                <div class="yb-fld">
                    <label>Xodim <i>*</i></label>
                    <select class="yb-in" wire:model.live="payUserId" @disabled($payEditId)>
                        <option value="">— tanlang —</option>
                        @foreach($payStaff as $ps)<option value="{{ $ps->id }}">{{ $ps->name }}{{ $ps->position ? ' — ' . $ps->position : '' }}</option>@endforeach
                    </select>
                    @error('payUserId')<div class="yb-err">{{ $message }}</div>@enderror
                    @if(!$payEditId && $payUserId)
                        <div class="yb-sub" style="margin-top:4px">{{ $monthLabel }} uchun qoldiq (hisoblangan bo'yicha): <b class="{{ $payFullRemaining > 0 ? 'yb-r' : 'yb-g' }}">{{ $fmt($payFullRemaining) }} so'm</b></div>
                        <div class="yb-sub">shundan mijoz to'lagani bo'yicha hozir to'lash mumkin: <b class="{{ $payRemaining > 0 ? 'yb-r' : 'yb-g' }}">{{ $fmt($payRemaining) }} so'm</b></div>
                    @endif
                </div>
                @include('filament.pages.partials.salary-type-pick', ['model' => 'paySalaryType', 'value' => $paySalaryType])
                <div class="yb-grid2">
                    <div class="yb-fld">
                        <label>Summa (so'm) <i>*</i></label>
                        <input type="text" inputmode="numeric" class="yb-in" wire:model="payAmount"
                               x-data x-on:input="let v=$el.value.replace(/\D/g,'');$el.value=v.replace(/\B(?=(\d{3})+(?!\d))/g,' ')">
                        @error('payAmount')<div class="yb-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="yb-fld">
                        <label>To'lov sanasi <i>*</i></label>
                        <input type="date" class="yb-in" wire:model="payDate">
                    </div>
                </div>
                <div class="yb-fld">
                    <label>Qaysi hisobdan berildi <i>*</i></label>
                    @include('filament.pages.partials.yangi-bux-account-select', ['model' => 'payAccountId', 'accountsList' => $allAccounts, 'selectedId' => $payAccountId, 'monthMode' => true])
                    @error('payAccountId')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Izoh</label>
                    <input type="text" class="yb-in" wire:model="payNote" placeholder="Masalan: avans">
                </div>
                <div class="yb-sub" style="margin-bottom:10px">Qoldiqdan kam summa — "qisman avans" deb yoziladi. Oylik hisobotga va tanlangan hisobdan xarajat sifatida yoziladi.</div>
                <div style="display:flex;gap:10px">
                    <button type="button" class="yb-b2" wire:click="$set('showPayModal', false)">Bekor qilish</button>
                    <button type="button" class="yb-b2" style="background:#16a34a;border-color:#16a34a;color:#fff" wire:click="savePay" wire:loading.attr="disabled">Saqlash</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
@endif

{{-- ── Xodimga xarajat kartasi ochish ── --}}
@if($showNewCardModal)
    @teleport('body')
    <div class="yb yb-ov">{{-- ma'lumot kiritiladi — tashqariga bosilganda yopilmaydi (matn belgilab sichqoncha tashqarida qo'yilsa ham) --}}
        <div class="yb-modal" style="max-width:440px">
            <div class="yb-modal-h" style="background:#f3f4f6">Xodimni ko'rsatish (xarajatlar kartasi) <button type="button" wire:click="$set('showNewCardModal', false)">×</button></div>
            <div class="yb-modal-b">
                <div class="yb-fld">
                    <label>Xodim <i>*</i></label>
                    <select class="yb-in" wire:model.live="ncUserId">
                        <option value="">— tanlang —</option>
                        @foreach($staffUsers as $su)<option value="{{ $su->id }}">{{ $su->name }}</option>@endforeach
                    </select>
                    @error('ncUserId')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Karta nomi <i>*</i></label>
                    <input type="text" class="yb-in" wire:model="ncName" placeholder="Masalan: Nursaid — xarajatlar">
                    @error('ncName')<div class="yb-err">{{ $message }}</div>@enderror
                </div>
                <div class="yb-fld">
                    <label>Karta raqami (ixtiyoriy)</label>
                    <input type="text" class="yb-in" wire:model="ncNumber" placeholder="8600 ....">
                </div>
                <div class="yb-sub" style="margin-bottom:10px">Bu pul hisobi emas — shu xodimga qilingan xarajatlar ("kim uchun" belgisi) har oy shu kartada jamlanib ko'rinadi. Balanslarga ta'sir qilmaydi.</div>
                <div style="display:flex;gap:10px">
                    <button type="button" class="yb-b2" wire:click="$set('showNewCardModal', false)">Bekor qilish</button>
                    <button type="button" class="yb-b2" style="background:#111827;border-color:#111827;color:#fff" wire:click="saveNewCard">Qo'shish</button>
                </div>
            </div>
        </div>
    </div>
    @endteleport
@endif

@livewire('payment-modal')
{{-- Mijozlar qarzlari: qatorga bosilganda ochiladigan loyiha tahrirlash oynasi --}}
@livewire('project-edit-modal')
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
