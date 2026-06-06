{{--
    ESLATMA / REFERENCE: Dashboard pastki qatori (4 mini karta + So'nggi loyihalar).
    Asosiy menyudan olib tashlandi (boshqa ma'lumot bilan almashtirish uchun).
    Stil va tuzilma kelajakda qayta ishlatish uchun shu yerda saqlanadi.
    Kerakli o'zgaruvchilar: $totalCount, $doneCount, $activeCount, $yangiCount, $recentProjects
--}}

{{-- ── ROW 3: 4 mini karta + so'nggi loyihalar (faqat admin/menejer) ── --}}
@if(!$isEmployee)
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
@endif {{-- /isEmployee row3 --}}
