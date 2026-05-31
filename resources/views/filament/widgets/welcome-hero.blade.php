<div class="bh-page">
<style>

.bh-page {
    position: relative;
}


@keyframes bh-grow  { from{height:3px} to{height:var(--h)} }
@keyframes bh-fade  { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }
@keyframes bh-dot   { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.4);opacity:.6} }

.bh-wrap {
    width: 100%;
    background: rgba(255,255,255,0.10);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.30);
    overflow: hidden;
    display: block;
    position: relative;
    z-index: 1;
    animation: bh-fade .5s ease both;
}

/* ─── LEFT ─── */
.bh-left {
    padding: 36px 40px 32px;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.bh-top-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
}

.bh-greeting {
    font-size: 36px;
    font-weight: 800;
    color: #111827;
    line-height: 1.25;
    margin: 0;
    letter-spacing: -.5px;
}
.bh-greeting-blue { color: #111827; }
.bh-greeting-sub {
    font-size: 15px;
    color: #6b7280;
    margin-top: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bh-role-badge {
    background: rgba(0,0,0,0.05);
    color: #374151;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    padding: 3px 14px;
}

/* BestHome logo SVG area */
.bh-logo-area {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.bh-logo-text {
    font-size: 12px;
    font-weight: 800;
    color: #374151;
    letter-spacing: .5px;
}

/* Bar chart */
.bh-chart-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #9ca3af;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.bh-bars {
    display: flex;
    align-items: flex-end;
    gap: 5px;
    height: 90px;
}
.bh-bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}
.bh-bar {
    width: 100%;
    border-radius: 5px 5px 0 0;
    height: var(--h);
    min-height: 3px;
    animation: bh-grow .85s cubic-bezier(.34,1.25,.64,1) both;
    position: relative;
}
.bh-bar-month {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
}
.bh-bar-col--cur .bh-bar-month {
    color: #374151;
    font-weight: 800;
}
/* Y-axis labels */
.bh-chart-yaxis {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 90px;
    margin-right: 6px;
    padding-bottom: 20px;
}
.bh-chart-yaxis span {
    font-size: 11px;
    color: #d1d5db;
    font-weight: 600;
    line-height: 1;
}
.bh-chart-inner {
    display: flex;
    align-items: flex-end;
    gap: 0;
    margin-top: auto;
}

/* ─── RIGHT ─── */
.bh-right {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: transparent;
}

/* Stat cards 2x2 */
.bh-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.bh-card {
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.30);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: background .2s, transform .15s;
    cursor: default;
}
.bh-card:hover {
    background: rgba(255,255,255,0.22);
    transform: translateY(-2px);
}

.bh-card-icon-wrap {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.bh-card-body {}
.bh-card-num {
    font-size: 32px;
    font-weight: 900;
    color: #111827;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.bh-card-name {
    font-size: 13px;
    color: #9ca3af;
    font-weight: 600;
    margin-top: 4px;
}

.bh-card--o, .bh-card--g, .bh-card--b, .bh-card--a {
    border-color: rgba(0,0,0,0.07);
    background: rgba(255,255,255,0.50);
}
.bh-card--o .bh-card-icon-wrap,
.bh-card--g .bh-card-icon-wrap,
.bh-card--b .bh-card-icon-wrap,
.bh-card--a .bh-card-icon-wrap {
    background: rgba(0,0,0,0.05);
    color: #374151;
}

/* Recent projects */
.bh-recent-title {
    font-size: 15px;
    font-weight: 700;
    color: #374151;
    margin: 0 0 10px;
}
.bh-proj-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    background: rgba(255,255,255,0.42);
    border: 1px solid rgba(243,244,246,0.6);
    border-radius: 10px;
    margin-bottom: 7px;
    transition: border-color .15s;
}
.bh-proj-row:last-child { margin-bottom: 0; }
.bh-proj-row:hover { border-color: #e5e7eb; }
.bh-proj-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    animation: bh-dot 2.5s ease-in-out infinite;
}
.bh-proj-info { flex: 1; min-width: 0; }
.bh-proj-name {
    font-size: 14px; font-weight: 700; color: #1f2937;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.bh-proj-owner {
    font-size: 12px; color: #9ca3af;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.bh-proj-badge {
    font-size: 12px; font-weight: 700;
    border-radius: 20px; padding: 3px 12px;
    white-space: nowrap;
}

/* ── Bottom stats ── */
.bh-bottom {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 16px;
    margin-top: 16px;
    position: relative;
    z-index: 1;
}
.bh-stat {
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.30);
    padding: 22px 24px 20px;
    position: relative;
    overflow: hidden;
}

.bh-stat-head {
    display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
}
.bh-stat-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bh-stat--blue   .bh-stat-icon,
.bh-stat--green  .bh-stat-icon,
.bh-stat--red    .bh-stat-icon,
.bh-stat--purple .bh-stat-icon { background: rgba(0,0,0,0.05); color: #374151; }

.bh-stat-label {
    font-size: 13px; font-weight: 700; color: #6b7280;
    text-transform: uppercase; letter-spacing: .4px;
}
.bh-stat-value {
    font-size: 32px; font-weight: 900; line-height: 1.1;
    white-space: nowrap; font-variant-numeric: tabular-nums;
}
.bh-stat--blue   .bh-stat-value,
.bh-stat--green  .bh-stat-value,
.bh-stat--red    .bh-stat-value,
.bh-stat--purple .bh-stat-value { color: #111827; }

.bh-stat-desc {
    font-size: 13px; color: #9ca3af; margin-top: 6px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.bh-stat-bar-wrap {
    margin-top: 10px; background: #f3f4f6;
    border-radius: 4px; height: 4px; overflow: hidden;
}
.bh-stat-bar {
    height: 100%; border-radius: 4px;
    background: linear-gradient(90deg,#9ca3af,#d1d5db);
    transition: width 1s ease;
}

/* ── Row 3: mini kartalar + so'nggi loyihalar ── */
.bh-row3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1.8fr;
    gap: 16px;
    margin-top: 16px;
    position: relative;
    z-index: 1;
    padding-bottom: 6px;
}
.bh-row3-recent {
    background: rgba(255,255,255,0.12);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.30);
    padding: 18px 20px;
    overflow: hidden;
}

@media(max-width:1100px) {
    .bh-bottom { grid-template-columns: 1fr 1fr; }
    .bh-row3  { grid-template-columns: 1fr 1fr 1fr; }
    .bh-row3-recent { grid-column: 1 / -1; }
}
@media(max-width:700px) {
    .bh-bottom { grid-template-columns: 1fr; }
    .bh-row3  { grid-template-columns: 1fr 1fr; }
}
</style>


<div class="bh-wrap">

    {{-- ── LEFT ── --}}
    <div class="bh-left">

        <div class="bh-top-row">
            {{-- Greeting --}}
            <div>
                <h1 class="bh-greeting">
                    Xush kelibsiz, {{ $userName }}.<br>
                    <span class="bh-greeting-blue">Loyihalaringizni</span><br>
                    boshqarish vaqti
                </h1>
                <div class="bh-greeting-sub">
                    Bugungi kun
                    @if($userRole)
                    <span class="bh-role-badge">{{ $userRole }}</span>
                    @endif
                </div>
            </div>

        </div>

        {{-- Bar chart --}}
        @php
            $mLbls  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
            $mColors = array_fill(0, 12, 'linear-gradient(180deg,#9ca3af,#6b7280)');
            $yMax = $maxIncome > 0 ? $maxIncome : 1;
        @endphp
        <div class="bh-chart-label">Loyihani boshqarish dinamikasi &middot; {{ now()->year }}</div>
        <div style="display:flex;gap:0;align-items:flex-end;">
            <div class="bh-chart-yaxis">
                <span>{{ $yMax > 0 ? number_format($yMax/1000000,0).'M' : '40' }}</span>
                <span>{{ $yMax > 0 ? number_format($yMax/2000000,0).'M' : '20' }}</span>
                <span>0</span>
            </div>
            <div class="bh-bars" style="flex:1">
                @foreach($monthlyIncome as $i => $inc)
                @php
                    $h     = max(3, (int)round(($inc / $yMax) * 78));
                    $isCur = ($i+1) === $currentMonth;
                @endphp
                <div class="bh-bar-col {{ $isCur ? 'bh-bar-col--cur' : '' }}">
                    <div class="bh-bar"
                         style="--h:{{$h}}px;background:{{$mColors[$i]}};opacity:{{ $isCur ? '1' : '.5' }};animation-delay:{{$i*.05}}s;">
                    </div>
                    <span class="bh-bar-month">{{ $mLbls[$i] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>


</div>

{{-- ── BOTTOM: 4 alohida stat karta ── --}}
<div class="bh-bottom">

    {{-- Jami loyihalar --}}
    <div class="bh-stat bh-stat--blue">
        <div class="bh-stat-head">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="bh-stat-label">Jami loyihalar</div>
        </div>
        <div class="bh-stat-value"
             x-data="{n:0}"
             x-init="setTimeout(()=>{let t={{ $statProjects }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},300)"
             x-text="n">0</div>
        <div class="bh-stat-desc">Yangi: {{ $statYangi }} &nbsp;·&nbsp; Jarayonda: {{ $statJarayon }} &nbsp;·&nbsp; Tugallangan: {{ $statDone }}</div>
    </div>

    {{-- Jami summa --}}
    <div class="bh-stat bh-stat--green">
        <div class="bh-stat-head">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div class="bh-stat-label">Jami summa</div>
        </div>
        <div class="bh-stat-value" style="font-size:26px">{{ number_format($statTotalSum, 0, '.', ' ') }} <span style="font-size:15px;font-weight:600">so'm</span></div>
        <div class="bh-stat-desc">To'langan: {{ number_format($statPaidSum, 0, '.', ' ') }} so'm</div>
        <div class="bh-stat-bar-wrap">
            <div class="bh-stat-bar" style="width:{{ $statPaidPct }}%"></div>
        </div>
    </div>

    {{-- Qolgan qarz --}}
    <div class="bh-stat bh-stat--red">
        <div class="bh-stat-head">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="bh-stat-label">Qolgan qarz</div>
        </div>
        <div class="bh-stat-value" style="font-size:20px">{{ number_format($statDebt, 0, '.', ' ') }} <span style="font-size:13px;font-weight:600">so'm</span></div>
        <div class="bh-stat-desc">{{ $statPaidPct }}% to'langan</div>
        <div class="bh-stat-bar-wrap">
            <div class="bh-stat-bar" style="width:{{ $statPaidPct }}%"></div>
        </div>
    </div>

    {{-- Vaqti o'tgan loyihalar --}}
    <div class="bh-stat bh-stat--purple">
        <div class="bh-stat-head">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="bh-stat-label">Vaqti o'tgan</div>
        </div>
        <div class="bh-stat-value"
             x-data="{n:0}"
             x-init="setTimeout(()=>{let t={{ $statOverdue }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},900)"
             x-text="n">0</div>
        <div class="bh-stat-desc">Muddati o'tib ketgan loyihalar</div>
    </div>

</div>

{{-- ── ROW 3: 4 mini karta + so'nggi loyihalar ── --}}
<div class="bh-row3">

    {{-- Jami loyihalar --}}
    <div class="bh-card bh-card--o">
        <div class="bh-card-icon-wrap">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="3 9 9 9 9 21 15 21 15 9 21 9"/>
            </svg>
        </div>
        <div class="bh-card-body">
            <div class="bh-card-num"
                 x-data="{n:0}"
                 x-init="setTimeout(()=>{let t={{ $totalCount }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},300)"
                 x-text="n">0</div>
            <div class="bh-card-name">Jami</div>
        </div>
    </div>

    {{-- Tugallangan --}}
    <div class="bh-card bh-card--g">
        <div class="bh-card-icon-wrap">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="bh-card-body">
            <div class="bh-card-num"
                 x-data="{n:0}"
                 x-init="setTimeout(()=>{let t={{ $doneCount }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},450)"
                 x-text="n">0</div>
            <div class="bh-card-name">Tugallangan</div>
        </div>
    </div>

    {{-- Faol --}}
    <div class="bh-card bh-card--b">
        <div class="bh-card-icon-wrap">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </div>
        <div class="bh-card-body">
            <div class="bh-card-num"
                 x-data="{n:0}"
                 x-init="setTimeout(()=>{let t={{ $activeCount }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},600)"
                 x-text="n">0</div>
            <div class="bh-card-name">Faol</div>
        </div>
    </div>

    {{-- Yangi --}}
    <div class="bh-card bh-card--a">
        <div class="bh-card-icon-wrap">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="bh-card-body">
            <div class="bh-card-num"
                 x-data="{n:0}"
                 x-init="setTimeout(()=>{let t={{ $yangiCount }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},750)"
                 x-text="n">0</div>
            <div class="bh-card-name">Yangi</div>
        </div>
    </div>

    {{-- So'nggi loyihalar --}}
    @if($recentProjects->count())
    <div class="bh-row3-recent">
        <p class="bh-recent-title">So'nggi loyihalar</p>
        @foreach($recentProjects as $rp)
        @php
            $scLabel = match($rp->status) {
                'yangi'            => 'Yangi',
                'tolov_jarayonida' => "To'lovda",
                'toposyomka'       => 'Toposyomka',
                'eskiz_loyiha'     => 'Eskiz',
                'tekshirish'       => 'Tekshiruv',
                'tugallangan'      => 'Tugallangan',
                default            => ucfirst($rp->status),
            };
        @endphp
        <div class="bh-proj-row">
            <span class="bh-proj-dot" style="background:#9ca3af"></span>
            <div class="bh-proj-info">
                <div class="bh-proj-name">{{ $rp->number ?: '—' }}</div>
                <div class="bh-proj-owner">{{ $rp->owner_name ?: 'Mijoz noma\'lum' }}</div>
            </div>
            <span class="bh-proj-badge" style="background:rgba(0,0,0,0.05);color:#374151">{{ $scLabel }}</span>
        </div>
        @endforeach
    </div>
    @endif

</div>
</div>
