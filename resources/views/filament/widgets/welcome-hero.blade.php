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

/* ── Bottom stats — 2 karta, markazlashtirilgan ── */
.bh-bottom {
    display: grid;
    grid-template-columns: repeat(2, minmax(280px, 460px));
    justify-content: center;
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
    .bh-row3  { grid-template-columns: 1fr 1fr 1fr; }
    .bh-row3-recent { grid-column: 1 / -1; }
}
@media(max-width:700px) {
    .bh-bottom { grid-template-columns: 1fr; }
    .bh-row3  { grid-template-columns: 1fr 1fr; }
    /* Mobilda "Statistikani ko'rsatish" tugmasi sarlavha bilan bitta qatorga
       sig'may, .bh-wrap ning overflow:hidden'i tomonidan kesib tashlanardi —
       shu sababli qator pastga o'tkaziladi (wrap) va tugma to'liq kenglikda
       o'z qatoriga chiqariladi. */
    .bh-top-row { flex-wrap: wrap; }
    .bh-eye-btn { width: 100%; justify-content: center; }
    .bh-greeting { font-size: 26px; }
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
            $yMax = $maxCount > 0 ? $maxCount : 1;
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
                <span>{{ $yMax > 0 ? number_format($yMax,0) : '10' }}</span>
                <span>{{ $yMax > 0 ? number_format($yMax/2,0) : '5' }}</span>
                <span>0</span>
            </div>
            <div class="bh-bars" style="flex:1">
                @foreach($monthlyCounts as $i => $cnt)
                @php
                    $h     = max(3, (int)round(($cnt / $yMax) * 78));
                    $isSel = ($i+1) === $selMonth;
                @endphp
                <div class="bh-bar-col {{ $isSel ? 'bh-bar-col--cur' : '' }}"
                     wire:click="selectMonth({{ $i+1 }})"
                     style="cursor:pointer"
                     title="{{ $mLbls[$i] }} {{ $selYear }} — {{ $cnt }} ta loyiha">
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
            Tugallangan ishlar — {{ $monthLabel }}
        </div>
        <div style="font-size:28px;font-weight:900;color:#16a34a;line-height:1">{{ $myStats['done_count'] }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:4px">xizmat</div>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-top:8px" title="Jami ishlaringizdan olishingiz mumkin bo'lgan ulush">
            {{ number_format($myStats['done_sum'], 0, '.', ' ') }} so'm
        </div>
        <div style="font-size:12px;color:#16a34a;margin-top:4px;font-weight:600" title="Mijozlar to'lagan ulushga mutanosib — hozircha real olishingiz mumkin bo'lgan qism">
            Mijoz to'lagan: {{ number_format($myStats['client_paid'], 0, '.', ' ') }} so'm
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

{{-- ── BOTTOM: 3 alohida stat karta (faqat admin/menejer) ── --}}
@if(!$isEmployee)
<div class="bh-bottom bh-secret" :class="{ 'bh-locked': !statShown }">

    {{-- Moliyaviy statistika — 58 tani ham, 52 tani ham, 6 tani ham alohida-alohida ko'rsatadi --}}
    <div class="bh-stat bh-stat--red">
        <div class="bh-stat-head" style="display:flex;align-items:center;gap:8px">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="bh-stat-label">Moliyaviy statistika</div>
            <div class="bh-stat-value" style="margin-left:auto;line-height:1">{{ $statProjects }} ta</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:5px;margin-top:8px;font-size:16px">
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px">Umumiy — {{ $statProjects }} ta</div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Jami summa</span><span style="font-weight:700;color:#374151">{{ number_format($statTotalSum, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Jami to'langan</span><span style="font-weight:700;color:#16a34a">{{ number_format($statPaidSum, 0, '.', ' ') }} so'm</span></div>
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">Jami qarz</span><span style="font-weight:700;color:#dc2626">{{ number_format($statDebt, 0, '.', ' ') }} so'm</span></div>

            @if(count($byServiceType) > 0)
            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-top:7px;border-top:1px dashed #e5e7eb;padding-top:6px">Ishlar turlari bo'yicha</div>
            @foreach($byServiceType as $svc)
            <div style="display:flex;justify-content:space-between"><span style="color:#6b7280">{{ $svc['label'] }} <span style="color:#9ca3af">({{ $svc['count'] }} ta)</span></span><span style="font-weight:700;color:#374151">{{ number_format($svc['total'], 0, '.', ' ') }} so'm</span></div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- Jami summa --}}
    <div class="bh-stat bh-stat--green">
        <div class="bh-stat-head" style="display:flex;align-items:center;gap:8px">
            <div class="bh-stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            @if(!empty($firmReport))
            <div class="bh-stat-label">To'lanishi kerak</div>
            @else
            <div class="bh-stat-label">Jami summa</div>
            @endif
        </div>
        @if(!empty($firmReport))
        <div class="bh-stat-value" style="font-size:24px">{{ number_format($firmReport['toLanishiKerakJami'], 0, '.', ' ') }} <span style="font-size:14px;font-weight:600">so'm</span></div>
        <div class="bh-stat-desc" style="white-space:normal;font-size:16px">{{ $firmReport['toLanganCount'] }} ta loyiha bo'yicha hodimlarga hali to'lanmagan qism</div>
        <div style="margin-top:12px;font-size:11px">
            <div style="display:grid;grid-template-columns:1.3fr .9fr .9fr .9fr;gap:4px;padding-bottom:4px;border-bottom:1px dashed #e5e7eb;font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.3px">
                <span>Hodim</span>
                <span style="text-align:right">Hisob.</span>
                <span style="text-align:right">Kerak</span>
                <span style="text-align:right">To'landi</span>
            </div>
            @foreach($firmReport['employeeComm'] as $emp)
            <div style="display:grid;grid-template-columns:1.3fr .9fr .9fr .9fr;gap:4px;padding:4px 0;border-bottom:1px dashed #f3f4f6">
                <span style="color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">👷 {{ $emp['name'] }}</span>
                <span style="text-align:right;font-weight:600;color:#374151">{{ number_format($emp['commission'], 0, '.', ' ') }}</span>
                <span style="text-align:right;font-weight:700;color:{{ $emp['kerak'] > 0 ? '#d97706' : '#9ca3af' }}">{{ number_format($emp['kerak'], 0, '.', ' ') }}</span>
                <span style="text-align:right;font-weight:600;color:#16a34a">{{ number_format($emp['paid'], 0, '.', ' ') }}</span>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;margin-top:5px;padding-top:5px;border-top:1px dashed #e5e7eb"><span style="color:#065f46;font-weight:600">🏢 Firma</span><span style="font-weight:800;color:#059669">{{ number_format($firmReport['firmaDaromadi'], 0, '.', ' ') }}</span></div>
        </div>
        @else
        <div class="bh-stat-value" style="font-size:26px">{{ number_format($statTotalSum, 0, '.', ' ') }} <span style="font-size:15px;font-weight:600">so'm</span></div>
        <div class="bh-stat-desc" style="font-size:16px">To'langan: {{ number_format($statPaidSum, 0, '.', ' ') }} so'm</div>
        <div class="bh-stat-bar-wrap">
            <div class="bh-stat-bar" style="width:{{ $statPaidPct }}%"></div>
        </div>
        @endif
    </div>

</div>
@endif {{-- /isEmployee --}}

{{-- 💵 BUGUNGI NAQD TUSHUM — oylarga ajratilgan (menejer/admin uchun) --}}
@if(!$isEmployee && count($cashTodayGroups) > 0)
<div x-data="{ cashModalOpen: false, cashModalMonth: null }" style="position:relative;z-index:1;margin-top:16px">
<style>
.ctp-panel{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.dark .ctp-panel{background:transparent;border-color:#334155;box-shadow:none}
.ctp-title{color:#111827;font-size:14px;font-weight:700}
.dark .ctp-title{color:#f1f5f9}
.ctp-total{font-size:12px;color:#16a34a;font-weight:700;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;padding:3px 12px}
.dark .ctp-total{background:#052e1a;border-color:#14532d;color:#4ade80}
.ctp-row{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:#f8fafc;border:1px solid #f1f5f9;cursor:pointer;margin-bottom:8px;transition:background .15s,border-color .15s}
.ctp-row:hover{background:#eff6ff;border-color:#bfdbfe}
.dark .ctp-row{background:#1e293b;border-color:#334155}
.dark .ctp-row:hover{background:#1e3a5f;border-color:#3b82f6}
.ctp-row:last-child{margin-bottom:0}
.ctp-month{font-size:13.5px;font-weight:700;color:#374151}
.dark .ctp-month{color:#e2e8f0}
.ctp-sum{font-size:14px;font-weight:800;color:#2563eb}
.ctp-count{font-size:11px;color:#9ca3af;margin-left:8px;font-weight:600}

.ctp-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:100;display:flex;align-items:center;justify-content:center;padding:20px}
.ctp-modal{background:#fff;border-radius:16px;max-width:560px;width:100%;max-height:80vh;overflow:auto;box-shadow:0 20px 50px rgba(0,0,0,.3)}
.dark .ctp-modal{background:#0f172a;border:1px solid #334155}
.ctp-modal-head{position:sticky;top:0;background:#fff;display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #f1f5f9;border-radius:16px 16px 0 0}
.dark .ctp-modal-head{background:#0f172a;border-color:#334155}
.ctp-modal-title{font-size:16px;font-weight:800;color:#111827}
.dark .ctp-modal-title{color:#f1f5f9}
.ctp-close{background:#f3f4f6;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;color:#6b7280;font-size:16px;line-height:1}
.dark .ctp-close{background:#1e293b;color:#94a3b8}
.ctp-modal-body{padding:10px 22px 22px}
.ctp-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid #f8fafc}
.dark .ctp-item{border-color:#1e293b}
.ctp-item:last-child{border-bottom:none}
.ctp-item-fish{font-size:13.5px;font-weight:700;color:#111827}
.dark .ctp-item-fish{color:#e2e8f0}
.ctp-item-sub{font-size:11.5px;color:#9ca3af;margin-top:2px}
.ctp-item-summa{font-size:14px;font-weight:800;color:#16a34a;white-space:nowrap}

.ctp-warn{font-size:11px;color:#dc2626;font-weight:700;background:#fef2f2;border:1px solid #fecaca;border-radius:20px;padding:3px 12px}
.dark .ctp-warn{background:#3f0d16;border-color:#7f1d1d;color:#f87171}
.ctp-row-unlinked{border-color:#fecaca;background:#fef2f2}
.dark .ctp-row-unlinked{background:#3f0d16;border-color:#7f1d1d}
.ctp-row-unlinked:hover{border-color:#fca5a5}
.ctp-row-warn{font-size:10.5px;color:#dc2626;font-weight:700;margin-left:8px}
.dark .ctp-row-warn{color:#f87171}
.ctp-item-unlinked{background:#fef2f2;border-radius:8px;padding-left:10px;padding-right:10px;margin:0 -10px}
.dark .ctp-item-unlinked{background:#3f0d16}
.ctp-item-fish-warn{color:#dc2626 !important}
.ctp-item-summa-warn{color:#dc2626 !important}
.ctp-item-warn-tag{font-size:10.5px;color:#dc2626;font-weight:700;display:block;margin-top:2px}
.dark .ctp-item-warn-tag{color:#f87171}
</style>

<div class="ctp-panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
        <span class="ctp-title">💵 Bugungi naqd tushum</span>
        <div style="display:flex;align-items:center;gap:8px">
            @if($cashTodayUnlinked > 0)
            <span class="ctp-warn">⚠ {{ number_format($cashTodayUnlinked, 0, '.', ' ') }} so'm bog'lanmagan</span>
            @endif
            <span class="ctp-total">{{ number_format($cashTodayTotal, 0, '.', ' ') }} so'm</span>
        </div>
    </div>
    @foreach($cashTodayGroups as $i => $g)
    <div class="ctp-row {{ $g['unlinkedSum'] > 0 ? 'ctp-row-unlinked' : '' }}" @click="cashModalOpen = true; cashModalMonth = {{ $i }}">
        <span class="ctp-month">
            {{ $g['label'] }} <span class="ctp-count">{{ count($g['items']) }} ta to'lov</span>
            @if($g['unlinkedSum'] > 0)
            <span class="ctp-row-warn">⚠ {{ number_format($g['unlinkedSum'], 0, '.', ' ') }} bog'lanmagan</span>
            @endif
        </span>
        <span class="ctp-sum">{{ number_format($g['sum'], 0, '.', ' ') }} so'm</span>
    </div>
    @endforeach
</div>

{{-- Modal: oy bo'yicha to'liq ro'yxat --}}
<template x-if="cashModalOpen">
<div class="ctp-overlay" @click.self="cashModalOpen = false">
    @foreach($cashTodayGroups as $i => $g)
    <div class="ctp-modal" x-show="cashModalMonth === {{ $i }}" x-cloak>
        <div class="ctp-modal-head">
            <span class="ctp-modal-title">{{ $g['label'] }} — {{ number_format($g['sum'], 0, '.', ' ') }} so'm</span>
            <button class="ctp-close" @click="cashModalOpen = false">✕</button>
        </div>
        <div class="ctp-modal-body">
            @foreach($g['items'] as $it)
            <div class="ctp-item {{ $it['unlinked'] ? 'ctp-item-unlinked' : '' }}">
                <div style="min-width:0">
                    <div class="ctp-item-fish {{ $it['unlinked'] ? 'ctp-item-fish-warn' : '' }}">{{ $it['fish'] }}</div>
                    <div class="ctp-item-sub">
                        {{ $it['sana'] }} &middot; {{ $it['vaqt'] }}
                        @if($it['manager']) &middot; 👤 {{ $it['manager'] }} @endif
                        @if($it['note']) &middot; {{ $it['note'] }} @endif
                    </div>
                    @if($it['unlinked'])
                    <span class="ctp-item-warn-tag">⚠ Summa hisobga bog'lanmagan — Buxgalteriyada ko'rinmaydi</span>
                    @endif
                </div>
                <div class="ctp-item-summa {{ $it['unlinked'] ? 'ctp-item-summa-warn' : '' }}">{{ number_format($it['summa'], 0, '.', ' ') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
</template>
</div>
@endif

{{-- ⏰ DIQQAT TALAB ISHLAR: Kechikkan + Muddati yaqin (xizmat-asosli) --}}
@if($statOverdue > 0 || $statSoon > 0)
<style>
[x-cloak]{display:none!important}
/* "Diqqat talab ishlar" paneli — avval qattiq oq fon bilan yozilgan edi,
   tungi rejimda qora sahifa ustida yorqin oq quti bo'lib chiqib qolardi.
   Shu sababli qora rejim uchun alohida rang beriladi (kunduzgiga tegmasdan). */
.dtj-panel{background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.dark .dtj-panel{background:transparent;border-color:#334155;box-shadow:none}
.dtj-title{color:#111827}
.dark .dtj-title{color:#f1f5f9}
.dtj-muted{color:#9ca3af}
.dark .dtj-muted{color:#64748b}
.dtj-emp{color:#374151}
.dark .dtj-emp{color:#cbd5e1}
.dtj-red-txt{color:#dc2626}
.dark .dtj-red-txt{color:#f87171}
.dtj-amber-txt{color:#d97706}
.dark .dtj-amber-txt{color:#fbbf24}
.dtj-item-sub{color:#6b7280}
.dark .dtj-item-sub{color:#94a3b8}
.dtj-count-red{background:#fef2f2}
.dark .dtj-count-red{background:#4c0519}
.dtj-count-yellow{background:#fffbeb}
.dark .dtj-count-yellow{background:#422006}
.dtj-item-red{background:#fef2f2;border:1px solid #fecaca}
.dark .dtj-item-red{background:#3f0d16;border-color:#7f1d1d}
.dtj-item-yellow{background:#fffbeb;border:1px solid #fde68a}
.dark .dtj-item-yellow{background:#3a2a06;border-color:#78350f}
.dtj-more-red{background:#fef2f2;border:1px solid #fecaca;color:#dc2626}
.dark .dtj-more-red{background:#3f0d16;border-color:#7f1d1d;color:#f87171}
.dtj-more-yellow{background:#fffbeb;border:1px solid #fde68a;color:#d97706}
.dark .dtj-more-yellow{background:#3a2a06;border-color:#78350f;color:#fbbf24}
</style>
<div class="dtj-panel" style="margin-top:16px;border-radius:14px;padding:16px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <svg width="18" height="18" fill="none" stroke="#dc2626" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span class="dtj-title" style="font-size:14px;font-weight:700">Diqqat talab ishlar</span>
        @if($isEmployee)<span class="dtj-muted" style="font-size:11px">(sizning ishlaringiz)</span>@endif
    </div>
    <div style="{{ $isEmployee ? 'display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px' : 'display:flex;flex-direction:column;gap:18px' }}">

        {{-- 🔴 Kechikkan --}}
        <div>
            <div class="dtj-red-txt" style="font-size:12px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                🔴 Kechikkan ishlar <span class="dtj-count-red" style="border-radius:10px;padding:1px 8px">{{ $statOverdue }}</span>
            </div>
            @if($statOverdue === 0)
            <div class="dtj-muted" style="font-size:12px;padding:8px 0">Kechikkan ish yo'q ✓</div>
            @else
                @foreach(($isEmployee ? [['name'=>null,'count'=>count($overdueItems),'items'=>$overdueItems]] : $overdueByEmployee) as $emp)
                @php $vis = $isEmployee ? $emp['items'] : array_slice($emp['items'], 0, 2); $hid = $isEmployee ? [] : array_slice($emp['items'], 2); @endphp
                <div @if(!$isEmployee)x-data="{showAll:false}"@endif style="margin-bottom:6px">
                    @if(!$isEmployee)
                    <div class="dtj-emp" style="font-size:12px;font-weight:700;margin:6px 0 4px">👷 {{ $emp['name'] }} <span class="dtj-count-red dtj-red-txt" style="font-size:10px;font-weight:700;border-radius:10px;padding:1px 7px">{{ $emp['count'] }} ta</span></div>
                    @endif
                    @foreach($vis as $it)
                    <a href="#" wire:click.prevent="openProjectEdit({{ $it['project_id'] }})" class="dtj-item-red" style="display:flex;justify-content:space-between;align-items:center;gap:8px;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                        <div style="min-width:0">
                            <div class="dtj-red-txt" style="font-size:12px;font-weight:700;font-family:monospace">{{ $it['number'] }}</div>
                            <div class="dtj-item-sub" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                        </div>
                        <span style="font-size:10px;font-weight:700;background:#dc2626;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['over_days'] }} kun kech</span>
                    </a>
                    @endforeach
                    @if(count($hid) > 0)
                    <div x-show="showAll" x-cloak>
                        @foreach($hid as $it)
                        <a href="#" wire:click.prevent="openProjectEdit({{ $it['project_id'] }})" class="dtj-item-red" style="display:flex;justify-content:space-between;align-items:center;gap:8px;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                            <div style="min-width:0">
                                <div class="dtj-red-txt" style="font-size:12px;font-weight:700;font-family:monospace">{{ $it['number'] }}</div>
                                <div class="dtj-item-sub" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                            </div>
                            <span style="font-size:10px;font-weight:700;background:#dc2626;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['over_days'] }} kun kech</span>
                        </a>
                        @endforeach
                    </div>
                    <button @click="showAll=!showAll" x-text="showAll ? '▲ Yashirish' : '▾ Batafsil (yana {{ count($hid) }} ta)'" class="dtj-more-red" style="border-radius:6px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;margin-top:2px"></button>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

        {{-- 🟡 Muddati yaqin --}}
        <div>
            <div class="dtj-amber-txt" style="font-size:12px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                🟡 Muddati yaqin (≤3 kun) <span class="dtj-count-yellow" style="border-radius:10px;padding:1px 8px">{{ $statSoon }}</span>
            </div>
            @if($statSoon === 0)
            <div class="dtj-muted" style="font-size:12px;padding:8px 0">Muddati yaqin ish yo'q</div>
            @else
                @foreach(($isEmployee ? [['name'=>null,'count'=>count($soonItems),'items'=>$soonItems]] : $soonByEmployee) as $emp)
                @php $vis = $isEmployee ? $emp['items'] : array_slice($emp['items'], 0, 2); $hid = $isEmployee ? [] : array_slice($emp['items'], 2); @endphp
                <div @if(!$isEmployee)x-data="{showAll:false}"@endif style="margin-bottom:6px">
                    @if(!$isEmployee)
                    <div class="dtj-emp" style="font-size:12px;font-weight:700;margin:6px 0 4px">👷 {{ $emp['name'] }} <span class="dtj-count-yellow dtj-amber-txt" style="font-size:10px;font-weight:700;border-radius:10px;padding:1px 7px">{{ $emp['count'] }} ta</span></div>
                    @endif
                    @foreach($vis as $it)
                    <a href="#" wire:click.prevent="openProjectEdit({{ $it['project_id'] }})" class="dtj-item-yellow" style="display:flex;justify-content:space-between;align-items:center;gap:8px;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                        <div style="min-width:0">
                            <div class="dtj-amber-txt" style="font-size:12px;font-weight:700;font-family:monospace">{{ $it['number'] }}</div>
                            <div class="dtj-item-sub" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                        </div>
                        <span style="font-size:10px;font-weight:700;background:#d97706;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['days_left'] > 0 ? $it['days_left'].' kun qoldi' : 'bugun' }}</span>
                    </a>
                    @endforeach
                    @if(count($hid) > 0)
                    <div x-show="showAll" x-cloak>
                        @foreach($hid as $it)
                        <a href="#" wire:click.prevent="openProjectEdit({{ $it['project_id'] }})" class="dtj-item-yellow" style="display:flex;justify-content:space-between;align-items:center;gap:8px;border-radius:8px;padding:6px 10px;text-decoration:none;margin-bottom:5px">
                            <div style="min-width:0">
                                <div class="dtj-amber-txt" style="font-size:12px;font-weight:700;font-family:monospace">{{ $it['number'] }}</div>
                                <div class="dtj-item-sub" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $it['owner'] }} · {{ $it['service'] }}</div>
                            </div>
                            <span style="font-size:10px;font-weight:700;background:#d97706;color:#fff;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $it['days_left'] > 0 ? $it['days_left'].' kun qoldi' : 'bugun' }}</span>
                        </a>
                        @endforeach
                    </div>
                    <button @click="showAll=!showAll" x-text="showAll ? '▲ Yashirish' : '▾ Batafsil (yana {{ count($hid) }} ta)'" class="dtj-more-yellow" style="border-radius:6px;padding:3px 10px;font-size:11px;font-weight:600;cursor:pointer;margin-top:2px"></button>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

    </div>
</div>
@endif

{{-- 👷 XODIMLAR YUKLAMASI — mutaxassis bo'yicha jami/yakunlangan/jarayonda,
     oylar bo'yicha yig'ma holda, tugma orqali to'liq oylik jadval ochiladi --}}
@if(!$isEmployee && count($employeeWorkload) > 0)
<div x-data="{ ewlOpen: false }" style="position:relative;z-index:1;margin-top:16px">
<style>
.ewl-panel{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.dark .ewl-panel{background:transparent;border-color:#334155;box-shadow:none}
.ewl-title{color:#111827;font-size:14px;font-weight:700}
.dark .ewl-title{color:#f1f5f9}
.ewl-toggle{font-size:12px;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:20px;padding:5px 14px;cursor:pointer}
.dark .ewl-toggle{background:#0c2b52;border-color:#1e40af;color:#93c5fd}
.ewl-table-wrap{overflow-x:auto;margin-top:12px}
.ewl-table{width:100%;border-collapse:collapse;font-size:12.5px;white-space:nowrap}
.ewl-table th{text-align:left;padding:7px 10px;color:#9ca3af;font-weight:600;font-size:10.5px;text-transform:uppercase;letter-spacing:.03em;border-bottom:1px solid #f1f5f9}
.dark .ewl-table th{color:#64748b;border-color:#1e293b}
.ewl-table td{padding:8px 10px;border-bottom:1px solid #f8fafc;color:#374151}
.dark .ewl-table td{border-color:#1e293b;color:#cbd5e1}
.ewl-table tr:last-child td{border-bottom:none}
.ewl-name{font-weight:700;color:#111827}
.dark .ewl-name{color:#e2e8f0}
.ewl-num{text-align:center;font-weight:700;cursor:pointer;border-radius:6px;transition:background .12s}
.ewl-num:hover{background:#f3f4f6}
.dark .ewl-num:hover{background:#1e293b}
.ewl-month{text-align:center;font-variant-numeric:tabular-nums;color:#d1d5db;white-space:nowrap}
.ewl-month .d, .ewl-month .p, .ewl-month .o, .ewl-month .f{cursor:pointer;border-radius:4px;padding:1px 3px}
.ewl-month .d{color:#16a34a;font-weight:700}
.ewl-month .d:hover{background:#dcfce7}
.ewl-month .p{color:#d97706;font-weight:700}
.ewl-month .p:hover{background:#fef3c7}
.ewl-month .o{color:#dc2626;font-weight:700}
.ewl-month .o:hover{background:#fee2e2}
.ewl-month .f{color:#6b7280;font-weight:700}
.ewl-month .f:hover{background:#f3f4f6}
.ewl-month .sep{color:#d1d5db;margin:0 1px}

.ewl-unassigned{margin-top:10px;padding:9px 14px;border-radius:10px;background:#f8fafc;border:1px dashed #e5e7eb;font-size:12.5px;color:#6b7280;cursor:pointer;display:flex;align-items:center;justify-content:space-between}
.dark .ewl-unassigned{background:#1e293b;border-color:#334155;color:#94a3b8}
.ewl-unassigned:hover{background:#f1f5f9}
.dark .ewl-unassigned:hover{background:#243244}
.ewl-unassigned b{color:#374151;font-size:13px}
.dark .ewl-unassigned b{color:#e2e8f0}

.ewl-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:200;display:flex;align-items:center;justify-content:center;padding:20px}
.ewl-modal{background:#fff;border-radius:16px;max-width:520px;width:100%;max-height:80vh;overflow:auto;box-shadow:0 20px 50px rgba(0,0,0,.3)}
.dark .ewl-modal{background:#0f172a;border:1px solid #334155}
.ewl-modal-head{position:sticky;top:0;background:#fff;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9;gap:10px;border-radius:16px 16px 0 0}
.dark .ewl-modal-head{background:#0f172a;border-color:#334155}
.ewl-modal-title{font-size:15px;font-weight:800;color:#111827}
.dark .ewl-modal-title{color:#f1f5f9}
.ewl-modal-close{background:#f3f4f6;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;color:#6b7280;font-size:16px;line-height:1;flex-shrink:0}
.dark .ewl-modal-close{background:#1e293b;color:#94a3b8}
.ewl-modal-body{padding:8px 12px 14px}
.ewl-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 10px;border-radius:10px;text-decoration:none;margin:4px 0}
.ewl-item:hover{background:#f8fafc}
.dark .ewl-item:hover{background:#1e293b}
.ewl-item-seq{font-size:11px;font-weight:800;color:#4338ca;background:#eef2ff;border:1px solid #c7d2fe;border-radius:6px;padding:2px 8px;white-space:nowrap}
.dark .ewl-item-seq{background:#1e1b4b;border-color:#3730a3;color:#a5b4fc}
.ewl-item-owner{font-size:13.5px;font-weight:700;color:#111827;margin-top:3px}
.dark .ewl-item-owner{color:#e2e8f0}
.ewl-item-sub{font-size:11.5px;color:#9ca3af;margin-top:2px}
.ewl-item-status{font-size:10.5px;font-weight:700;border-radius:20px;padding:3px 10px;white-space:nowrap;flex-shrink:0}
.ewl-item-status.done{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.dark .ewl-item-status.done{background:#052e1a;border-color:#14532d;color:#4ade80}
.ewl-item-status.prog{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
.dark .ewl-item-status.prog{background:#3a2a06;border-color:#78350f;color:#fbbf24}
.ewl-item-status.paused{background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb}
.dark .ewl-item-status.paused{background:#1e293b;border-color:#334155;color:#94a3b8}
.ewl-item-status.late{background:#dc2626;color:#fff;border:1px solid #dc2626}
.dark .ewl-item-status.late{background:#dc2626;border-color:#dc2626;color:#fff}
</style>

<div class="ewl-panel">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
        <span class="ewl-title">👷 Xodimlar yuklamasi</span>
        <button type="button" class="ewl-toggle" @click="ewlOpen = !ewlOpen" x-text="ewlOpen ? '▲ Oylarni yashirish' : '▾ Oylar bo\'yicha ko\'rsatish'"></button>
    </div>
    <div class="ewl-table-wrap">
        <table class="ewl-table">
            <thead>
                <tr>
                    <th>Mutaxassis</th>
                    <th style="text-align:center">Jami</th>
                    <th style="text-align:center">Yakunlangan</th>
                    <th style="text-align:center">Jarayonda</th>
                    <th style="text-align:center">Kechikayotgan</th>
                    <th style="text-align:center">Muzlatilgan</th>
                    <template x-if="ewlOpen">
                        <template x-for="m in ['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek']" :key="m">
                            <th style="text-align:center" x-text="m"></th>
                        </template>
                    </template>
                </tr>
            </thead>
            <tbody>
                @foreach($employeeWorkload as $emp)
                <tr>
                    <td class="ewl-name">{{ $emp['name'] }}</td>
                    <td class="ewl-num" wire:click="showWorkloadItems({{ $emp['id'] }}, 'total')">{{ $emp['total'] }}</td>
                    <td class="ewl-num" style="color:#16a34a" wire:click="showWorkloadItems({{ $emp['id'] }}, 'completed')">{{ $emp['completed'] }}</td>
                    <td class="ewl-num" style="color:#d97706" wire:click="showWorkloadItems({{ $emp['id'] }}, 'inprogress')">{{ $emp['inProgress'] }}</td>
                    <td class="ewl-num" style="color:#dc2626" wire:click="showWorkloadItems({{ $emp['id'] }}, 'overdue')">{{ $emp['overdue'] }}</td>
                    <td class="ewl-num" style="color:#6b7280" wire:click="showWorkloadItems({{ $emp['id'] }}, 'paused')">{{ $emp['paused'] }}</td>
                    <template x-if="ewlOpen">
                        <template x-for="(m, idx) in {{ json_encode($emp['monthly']) }}" :key="idx">
                            <td class="ewl-month">
                                <template x-if="m.done === 0 && m.prog === 0 && m.overdue === 0 && m.paused === 0">—</template>
                                <template x-if="m.done > 0 || m.prog > 0 || m.overdue > 0 || m.paused > 0">
                                    <span>
                                        <span class="d" x-text="m.done" @click="$wire.showWorkloadItems({{ $emp['id'] }}, 'month_done', idx + 1)"></span><span class="sep">/</span><span class="p" x-text="m.prog" @click="$wire.showWorkloadItems({{ $emp['id'] }}, 'month_prog', idx + 1)"></span><span class="sep">/</span><span class="o" x-text="m.overdue" @click="$wire.showWorkloadItems({{ $emp['id'] }}, 'month_overdue', idx + 1)"></span><span class="sep">/</span><span class="f" x-text="m.paused" @click="$wire.showWorkloadItems({{ $emp['id'] }}, 'month_paused', idx + 1)"></span>
                                    </span>
                                </template>
                            </td>
                        </template>
                    </template>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($unassignedCount > 0)
    <div class="ewl-unassigned" wire:click="showWorkloadItems(0, 'unassigned')">
        <span>⚠️ Biriktirilmagan ishlar</span>
        <b>{{ $unassignedCount }} ta</b>
    </div>
    @endif
</div>
</div>

{{-- Raqamga bosilganda — aynan qaysi ishlar ekanini ko'rsatuvchi ro'yxat modali.
     Har bir qator loyihaning tahrirlash sahifasiga olib boradi (Loyihalar
     bo'limidagi kabi) — ID qidirib yurish shart bo'lmasin. --}}
@if($workloadModalOpen)
<div class="ewl-overlay" wire:click.self="closeWorkloadModal">
    <div class="ewl-modal">
        <div class="ewl-modal-head">
            <span class="ewl-modal-title">{{ $workloadModalTitle }}</span>
            <button class="ewl-modal-close" wire:click="closeWorkloadModal">✕</button>
        </div>
        <div class="ewl-modal-body">
            @forelse($workloadModalItems as $it)
            <a href="#" wire:click.prevent="openProjectEdit({{ $it['projectId'] }})" class="ewl-item">
                <div style="min-width:0">
                    <span class="ewl-item-seq">№{{ $it['seq'] }}</span>
                    <div class="ewl-item-owner">{{ $it['owner'] }}</div>
                    <div class="ewl-item-sub">{{ $it['service'] }} &middot; {{ $it['date'] }}</div>
                </div>
                @if($it['lateDays'] > 0)
                <span class="ewl-item-status late">{{ $it['lateDays'] }} kun kech</span>
                @else
                <span class="ewl-item-status {{ $it['status'] === 'Tugallangan' ? 'done' : ($it['status'] === 'Muzlatilgan' ? 'paused' : 'prog') }}">{{ $it['status'] }}</span>
                @endif
            </a>
            @empty
            <div style="padding:20px 0;text-align:center;color:#9ca3af;font-size:13px">Ish topilmadi</div>
            @endforelse
        </div>
    </div>
</div>
@endif
@endif

{{-- ROW 3 (4 mini karta + So'nggi loyihalar) olib tashlandi.
     Stil/markup saqlangan: resources/views/partials/_dashboard-row3-reference.blade.php --}}
</div>
