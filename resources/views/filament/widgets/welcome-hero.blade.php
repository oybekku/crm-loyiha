<div class="bh-page" x-data="{ statShown: false }">
<style>

.bh-page {
    position: relative;
}

/* ── Maxfiy statistika: xira (blur) holat + ochish ── */
.bh-secret { transition: filter .35s ease; }
.bh-secret.bh-locked {
    filter: blur(11px);
    pointer-events: none;
    user-select: none;
}
.bh-eye-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb;
    border-radius: 9px; padding: 7px 14px; font-size: 13px; font-weight: 700;
    cursor: pointer; white-space: nowrap; transition: background .15s;
}
.bh-eye-btn:hover { background: #dbeafe; }


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

            {{-- Statistikani ko'rsatish/yashirish (maxfiy raqamlar xira turadi) --}}
            <button type="button" class="bh-eye-btn" @click="statShown = !statShown"
                    x-text="statShown ? '🙈 Yashirish' : '👁 Statistikani ko\'rsatish'"
                    title="Maxfiy moliyaviy raqamlarni ochish/yashirish">👁 Statistikani ko'rsatish</button>

        </div>

        {{-- Bar chart --}}
        @php
            $mLbls  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
            $mColors = array_fill(0, 12, 'linear-gradient(180deg,#9ca3af,#6b7280)');
            $yMax = $maxIncome > 0 ? $maxIncome : 1;
        @endphp
        <div class="bh-secret" :class="{ 'bh-locked': !statShown }">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <div class="bh-chart-label" style="margin:0">Loyiha dinamikasi &middot; oyni tanlang</div>
            <div style="display:flex;align-items:center;gap:8px">
                <button wire:click="changeYear(-1)" style="background:rgba(0,0,0,.05);border:none;border-radius:6px;width:24px;height:24px;cursor:pointer;font-size:14px;color:#374151;line-height:1">‹</button>
                <span style="font-size:13px;font-weight:700;color:#374151;min-width:38px;text-align:center">{{ $selYear }}</span>
                <button wire:click="changeYear(1)" style="background:rgba(0,0,0,.05);border:none;border-radius:6px;width:24px;height:24px;cursor:pointer;font-size:14px;color:#374151;line-height:1">›</button>
            </div>
        </div>
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
                    $isSel = ($i+1) === $selMonth;
                @endphp
                <div class="bh-bar-col {{ $isSel ? 'bh-bar-col--cur' : '' }}"
                     wire:click="selectMonth({{ $i+1 }})"
                     style="cursor:pointer"
                     title="{{ $mLbls[$i] }} {{ $selYear }} — tanlash">
                    <div class="bh-bar"
                         style="--h:{{$h}}px;background:{{ $isSel ? 'linear-gradient(180deg,#3b82f6,#2563eb)' : $mColors[$i] }};opacity:{{ $isSel ? '1' : '.5' }};animation-delay:{{$i*.05}}s;">
                    </div>
                    <span class="bh-bar-month" style="{{ $isSel ? 'color:#2563eb;font-weight:700' : '' }}">{{ $mLbls[$i] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        </div>{{-- /bh-secret: grafik --}}

    </div>


</div>

{{-- ── SHAXSIY STATISTIKA (bajaruvchi uchun) ── --}}
@if($isEmployee && $myStats)
<div class="bh-secret" :class="{ 'bh-locked': !statShown }" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;position:relative;z-index:1">

    {{-- Bu oy tugallangan --}}
    <div style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.30);border-radius:12px;padding:20px 24px">
        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Bu oy tugallangan ishlar
        </div>
        <div style="font-size:28px;font-weight:900;color:#16a34a;line-height:1">{{ $myStats['done_count'] }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:4px">xizmat</div>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-top:8px">
            {{ number_format($myStats['done_sum'], 0, '.', ' ') }} so'm
        </div>
    </div>

    {{-- Qolgan ishlar --}}
    <div style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.30);border-radius:12px;padding:20px 24px">
        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="#f97316" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Qolgan (jarayondagi) ishlar
        </div>
        <div style="font-size:28px;font-weight:900;color:#f97316;line-height:1">{{ $myStats['pending_count'] }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:4px">xizmat</div>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-top:8px">
            {{ number_format($myStats['pending_sum'], 0, '.', ' ') }} so'm
        </div>
    </div>

</div>
@endif

{{-- ── Tanlangan davr yorlig'i ── --}}
@if(!$isEmployee)
<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;position:relative;z-index:1">
    <span style="font-size:13px;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:5px 14px">📅 {{ $monthLabel }}</span>
    <span style="font-size:11px;color:#9ca3af">— shu oyda ochilgan loyihalar hisoboti</span>
</div>
@endif

{{-- ── BOTTOM: 4 alohida stat karta (faqat admin/menejer) ── --}}
@if(!$isEmployee)
<div class="bh-bottom bh-secret" :class="{ 'bh-locked': !statShown }">

    {{-- Jami loyihalar --}}
    <div class="bh-stat bh-stat--blue">
        <div class="bh-stat-head" style="display:flex;align-items:center;gap:8px">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="bh-stat-label">Jami loyihalar</div>
            <div class="bh-stat-value" style="margin-left:auto;line-height:1"
                 x-data="{n:0}"
                 x-init="setTimeout(()=>{let t={{ $statProjects }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},300)"
                 x-text="n">0</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:3px;margin-top:10px;font-size:13px;color:#6b7280">
            <div>Yangi: <strong style="color:#374151">{{ $statYangi }}</strong></div>
            <div>Jarayonda: <strong style="color:#374151">{{ $statJarayon }}</strong></div>
            <div>Tugallangan: <strong style="color:#374151">{{ $statDone }}</strong></div>
            <div>Vaqti o'tgan: <strong style="color:{{ $statOverdue > 0 ? '#dc2626' : '#374151' }}">{{ $statOverdue }}</strong></div>
        </div>
    </div>

    {{-- Qilinmagan loyihalar --}}
    <div class="bh-stat bh-stat--red">
        <div class="bh-stat-head" style="display:flex;align-items:center;gap:8px">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="bh-stat-label">Qilinmagan loyihalar</div>
            <div class="bh-stat-value" style="margin-left:auto;line-height:1">{{ $statPendingCount }}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:3px;margin-top:10px;font-size:12.5px">
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Umumiy summa</span><span style="font-weight:700;color:#374151">{{ number_format($statTotalSum, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Qilinmagan jami summa</span><span style="font-weight:700;color:#9a3412">{{ number_format($statPendingSum, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">To'langan</span><span style="font-weight:700;color:#16a34a">{{ number_format($statPendingPaid, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Qolgan qarz</span><span style="font-weight:700;color:#dc2626">{{ number_format($statPendingDebt, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between;border-top:1px dashed #e5e7eb;margin-top:3px;padding-top:5px"><span style="color:#6b7280">Tugallangan summasi</span><span style="font-weight:700;color:#16a34a">{{ number_format($statDoneSum, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Umumiy loyihalar</span><span style="font-weight:700;color:#374151">{{ $statProjects }} ta</span></div>
        </div>
    </div>

    {{-- Jami summa --}}
    <div class="bh-stat bh-stat--green">
        <div class="bh-stat-head" style="display:flex;align-items:center;gap:8px">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            @if(!empty($firmReport))
            <div class="bh-stat-label">To'langan to'liq</div>
            <div class="bh-stat-value" style="margin-left:auto;line-height:1">{{ $firmReport['toLanganCount'] }}</div>
            @else
            <div class="bh-stat-label">Jami summa</div>
            @endif
        </div>
        @if(!empty($firmReport))
        <div style="display:flex;flex-direction:column;gap:3px;margin-top:12px;font-size:12.5px">
            @foreach($firmReport['employeeComm'] as $emp)
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">👷 {{ $emp['name'] }}</span><span style="font-weight:700;color:#d97706">{{ number_format($emp['commission'], 0, '.', ' ') }}</span></div>
            @endforeach
            <div style="display:flex;justify-content:space-between;border-top:1px dashed #e5e7eb;margin-top:3px;padding-top:5px"><span style="color:#065f46;font-weight:600">🏢 Firma</span><span style="font-weight:800;color:#059669">{{ number_format($firmReport['firmaDaromadi'], 0, '.', ' ') }}</span></div>
        </div>
        @else
        <div class="bh-stat-value" style="font-size:26px">{{ number_format($statTotalSum, 0, '.', ' ') }} <span style="font-size:15px;font-weight:600">so'm</span></div>
        <div class="bh-stat-desc">To'langan: {{ number_format($statPaidSum, 0, '.', ' ') }} so'm</div>
        <div class="bh-stat-bar-wrap">
            <div class="bh-stat-bar" style="width:{{ $statPaidPct }}%"></div>
        </div>
        @endif
    </div>

    {{-- Vaqti o'tgan loyihalar --}}
    <div class="bh-stat bh-stat--purple" x-data="{open:false}">
        <div class="bh-stat-head">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="bh-stat-label">Vaqti o'tgan</div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div class="bh-stat-value"
                 x-data="{n:0}"
                 x-init="setTimeout(()=>{let t={{ $statOverdue }},d=900,s=Date.now(),iv=setInterval(()=>{let p=Math.min((Date.now()-s)/d,1);n=Math.floor((1-Math.pow(1-p,3))*t);if(p>=1){n=t;clearInterval(iv);}},16);},900)"
                 x-text="n">0</div>
        </div>
        <div class="bh-stat-desc">Muddati o'tib ketgan loyihalar</div>
    </div>

</div>
@endif {{-- /isEmployee --}}

{{-- ⏰ DIQQAT TALAB ISHLAR: Kechikkan + Muddati yaqin (xizmat-asosli) --}}
@if($statOverdue > 0 || $statSoon > 0)
<style>[x-cloak]{display:none!important}</style>
<div style="margin-top:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span style="font-size:14px;font-weight:700;color:#111827">Diqqat talab ishlar</span>
        @if($isEmployee)<span style="font-size:11px;color:#9ca3af">(sizning ishlaringiz)</span>@endif
    </div>
    <div style="{{ $isEmployee ? 'display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px' : 'display:flex;flex-direction:column;gap:18px' }}">

        {{-- 🔴 Kechikkan --}}
        <div>
            <div style="font-size:12px;font-weight:700;color:#dc2626;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                🔴 Kechikkan ishlar <span style="background:#fef2f2;border-radius:10px;padding:1px 8px">{{ $statOverdue }}</span>
            </div>
            @if($statOverdue === 0)
            <div style="font-size:12px;color:#9ca3af;padding:8px 0">Kechikkan ish yo'q ✓</div>
            @else
                @foreach(($isEmployee ? [['name'=>null,'count'=>count($overdueItems),'items'=>$overdueItems]] : $overdueByEmployee) as $emp)
                @php $vis = $isEmployee ? $emp['items'] : array_slice($emp['items'], 0, 2); $hid = $isEmployee ? [] : array_slice($emp['items'], 2); @endphp
                <div @if(!$isEmployee)x-data="{showAll:false}"@endif style="margin-bottom:6px">
                    @if(!$isEmployee)
                    <div style="font-size:12px;font-weight:700;color:#374151;margin:6px 0 4px">👷 {{ $emp['name'] }} <span style="font-size:10px;font-weight:700;background:#fef2f2;color:#dc2626;border-radius:10px;padding:1px 7px">{{ $emp['count'] }} ta</span></div>
                    @endif
                    @foreach($vis as $it)
                    <a href="/admin/projects/{{ $it['project_id'] }}/edit" style="display:flex;justify-content:space-between;align-items:center;gap:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                        <div style="min-width:0">
                            <div style="font-size:12px;font-weight:700;color:#dc2626;font-family:monospace">{{ $it['number'] }}</div>
                            <div style="font-size:10px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                        </div>
                        <span style="font-size:10px;font-weight:700;background:#dc2626;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['over_days'] }} kun kech</span>
                    </a>
                    @endforeach
                    @if(count($hid) > 0)
                    <div x-show="showAll" x-cloak>
                        @foreach($hid as $it)
                        <a href="/admin/projects/{{ $it['project_id'] }}/edit" style="display:flex;justify-content:space-between;align-items:center;gap:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                            <div style="min-width:0">
                                <div style="font-size:12px;font-weight:700;color:#dc2626;font-family:monospace">{{ $it['number'] }}</div>
                                <div style="font-size:10px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                            </div>
                            <span style="font-size:10px;font-weight:700;background:#dc2626;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['over_days'] }} kun kech</span>
                        </a>
                        @endforeach
                    </div>
                    <button @click="showAll=!showAll" x-text="showAll ? '▲ Yashirish' : '▾ Batafsil (yana {{ count($hid) }} ta)'" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;margin-top:2px"></button>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

        {{-- 🟡 Muddati yaqin --}}
        <div>
            <div style="font-size:12px;font-weight:700;color:#d97706;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                🟡 Muddati yaqin (≤3 kun) <span style="background:#fffbeb;border-radius:10px;padding:1px 8px">{{ $statSoon }}</span>
            </div>
            @if($statSoon === 0)
            <div style="font-size:12px;color:#9ca3af;padding:8px 0">Muddati yaqin ish yo'q</div>
            @else
                @foreach(($isEmployee ? [['name'=>null,'count'=>count($soonItems),'items'=>$soonItems]] : $soonByEmployee) as $emp)
                @php $vis = $isEmployee ? $emp['items'] : array_slice($emp['items'], 0, 2); $hid = $isEmployee ? [] : array_slice($emp['items'], 2); @endphp
                <div @if(!$isEmployee)x-data="{showAll:false}"@endif style="margin-bottom:6px">
                    @if(!$isEmployee)
                    <div style="font-size:12px;font-weight:700;color:#374151;margin:6px 0 4px">👷 {{ $emp['name'] }} <span style="font-size:10px;font-weight:700;background:#fffbeb;color:#d97706;border-radius:10px;padding:1px 7px">{{ $emp['count'] }} ta</span></div>
                    @endif
                    @foreach($vis as $it)
                    <a href="/admin/projects/{{ $it['project_id'] }}/edit" style="display:flex;justify-content:space-between;align-items:center;gap:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                        <div style="min-width:0">
                            <div style="font-size:12px;font-weight:700;color:#d97706;font-family:monospace">{{ $it['number'] }}</div>
                            <div style="font-size:10px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                        </div>
                        <span style="font-size:10px;font-weight:700;background:#d97706;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['days_left'] > 0 ? $it['days_left'].' kun qoldi' : 'bugun' }}</span>
                    </a>
                    @endforeach
                    @if(count($hid) > 0)
                    <div x-show="showAll" x-cloak>
                        @foreach($hid as $it)
                        <a href="/admin/projects/{{ $it['project_id'] }}/edit" style="display:flex;justify-content:space-between;align-items:center;gap:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                            <div style="min-width:0">
                                <div style="font-size:12px;font-weight:700;color:#d97706;font-family:monospace">{{ $it['number'] }}</div>
                                <div style="font-size:10px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                            </div>
                            <span style="font-size:10px;font-weight:700;background:#d97706;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['days_left'] > 0 ? $it['days_left'].' kun qoldi' : 'bugun' }}</span>
                        </a>
                        @endforeach
                    </div>
                    <button @click="showAll=!showAll" x-text="showAll ? '▲ Yashirish' : '▾ Batafsil (yana {{ count($hid) }} ta)'" style="background:#fffbeb;border:1px solid #fde68a;color:#d97706;border-radius:6px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;margin-top:2px"></button>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

    </div>
</div>
@endif

{{-- ROW 3 (4 mini karta + So'nggi loyihalar) olib tashlandi.
     Stil/markup saqlangan: resources/views/partials/_dashboard-row3-reference.blade.php --}}
</div>
