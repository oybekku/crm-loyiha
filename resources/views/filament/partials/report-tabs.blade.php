{{--
    Tugma dizayni: Uiverse.io by dexter-st ("torn paper" tugma, burchaklarga
    uchib ketuvchi nuqtalar + hover'da chiziladigan chiziqlar) — MAKONN.UZ
    ko'k palitrasiga moslab qayta rangланgan, "faol tugma" holati qo'shilgan.
--}}
@php
    $dark ??= false;
    $rtCurrent = request()->route()?->getName();

    $rtTabs = [
        [
            'route' => 'filament.admin.pages.dashboard',
            'label' => 'Statistika',
            'icon'  => '📊',
            'show'  => true,
        ],
        [
            'route' => 'filament.admin.pages.monthly-report',
            'label' => 'Oylik hisobot',
            'icon'  => '📅',
            'show'  => (bool) auth()->user()?->isAdmin(),
        ],
        [
            'route' => 'filament.admin.pages.buxgalteriya',
            'label' => 'Buxgalteriya',
            'icon'  => '💳',
            'show'  => \App\Filament\Pages\Buxgalteriya::canAccess(),
        ],
    ];
@endphp

<style>
.rt-tabs{--rt-dot-size:8.4px;--rt-line-weight:1px;--rt-line-distance:.77rem .98rem;--rt-speed:.35s;
    display:flex;gap:4px;margin-bottom:18px;flex-wrap:wrap}

.rt-wrap{position:relative;display:flex;justify-content:center;align-items:center;
    padding:var(--rt-line-distance);background-color:transparent;
    transition:background-color .3s ease-in-out;user-select:none}
.rt-wrap:has(.rt-btn:hover){animation:rt-bg-change calc(var(--rt-speed) * 4) ease-in-out forwards}
@keyframes rt-bg-change{80%{background-color:transparent}100%{background-color:#2563eb22}}

.rt-btn{position:relative;display:inline-flex;align-items:center;gap:8px;
    padding:.84rem 1.54rem;background-color:#fff;background-image:linear-gradient(#fff0,#0000000a);
    border:none;color:#374151;font-family:inherit;font-size:18px;font-weight:700;
    text-decoration:none;border-radius:30% / 200%;cursor:pointer;
    box-shadow:0 0 0 1px #d1d5db,0 1px 1px rgba(3,7,18,.02),0 5px 4px rgba(3,7,18,.04),0 12px 9px rgba(3,7,18,.06);
    transition:background-color .2s ease-in-out,transform .2s ease-in-out,box-shadow .2s ease-in-out,border-radius .3s ease-in-out}
.rt-btn:hover{background-color:#eff6ff;transform:scale(1.05);border-radius:10% / 200%}
.rt-btn:active{transform:scale(.98);border-radius:20% / 200%}

.rt-btn.rt-active{background-color:#2563eb;background-image:linear-gradient(#fff2,#0000000a);color:#fff;
    box-shadow:0 0 0 1px #1d4ed8,0 5px 4px rgba(37,99,235,.15),0 12px 9px rgba(37,99,235,.12)}
.rt-btn.rt-active:hover{background-color:#1d4ed8}

.dark .rt-btn,
.rt-tabs--dark .rt-btn{background-color:transparent;color:#cbd5e1;box-shadow:0 0 0 1px rgba(255,255,255,.18)}
.dark .rt-btn:hover,
.rt-tabs--dark .rt-btn:hover{background-color:rgba(255,255,255,.06)}
.dark .rt-btn.rt-active,
.rt-tabs--dark .rt-btn.rt-active{background-color:#2563eb;color:#fff;box-shadow:0 0 0 1px #3b82f6}

/* Nuqtalar */
.rt-dot{position:absolute;width:var(--rt-dot-size);aspect-ratio:1;border-radius:50%;
    background-color:#2563eb;transition:all .3s ease-in-out;opacity:0}
.rt-wrap:has(.rt-btn:hover) .rt-dot.rt-tl{top:50%;left:20%;animation:rt-move-tl var(--rt-speed) ease-in-out forwards}
@keyframes rt-move-tl{90%{opacity:.6}100%{top:calc(var(--rt-dot-size) * -.5);left:calc(var(--rt-dot-size) * -.5);opacity:1}}
.rt-wrap:has(.rt-btn:hover) .rt-dot.rt-tr{top:50%;right:20%;animation:rt-move-tr var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * .6)}
@keyframes rt-move-tr{80%{opacity:.6}100%{top:calc(var(--rt-dot-size) * -.5);right:calc(var(--rt-dot-size) * -.5);opacity:1}}
.rt-wrap:has(.rt-btn:hover) .rt-dot.rt-br{bottom:50%;right:20%;animation:rt-move-br var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * 1.2)}
@keyframes rt-move-br{80%{opacity:.6}100%{bottom:calc(var(--rt-dot-size) * -.5);right:calc(var(--rt-dot-size) * -.5);opacity:1}}
.rt-wrap:has(.rt-btn:hover) .rt-dot.rt-bl{bottom:50%;left:20%;animation:rt-move-bl var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * 1.8)}
@keyframes rt-move-bl{80%{opacity:.6}100%{bottom:calc(var(--rt-dot-size) * -.5);left:calc(var(--rt-dot-size) * -.5);opacity:1}}

/* Chiziqlar */
.rt-line{position:absolute;transition:all .3s ease-in-out}
.rt-line.rt-h{height:var(--rt-line-weight);width:100%;
    background-image:repeating-linear-gradient(90deg,#0000 0 calc(var(--rt-line-weight) * 2),#93c5fd calc(var(--rt-line-weight) * 2) calc(var(--rt-line-weight) * 4))}
.rt-line.rt-top{top:calc(var(--rt-line-weight) * -.5);transform-origin:top left;transform:rotate(5deg) scaleX(0)}
.rt-wrap:has(.rt-btn:hover) .rt-line.rt-top{animation:rt-draw-top var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * .8)}
@keyframes rt-draw-top{100%{transform:rotate(0deg) scaleX(1)}}
.rt-line.rt-bottom{bottom:calc(var(--rt-line-weight) * -.5);transform-origin:bottom right;transform:rotate(5deg) scaleX(0)}
.rt-wrap:has(.rt-btn:hover) .rt-line.rt-bottom{animation:rt-draw-bottom var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * 2)}
@keyframes rt-draw-bottom{100%{transform:rotate(0deg) scaleX(1)}}
.rt-line.rt-v{width:var(--rt-line-weight);height:100%;
    background-image:repeating-linear-gradient(0deg,#0000 0 calc(var(--rt-line-weight) * 2),#93c5fd calc(var(--rt-line-weight) * 2) calc(var(--rt-line-weight) * 4))}
.rt-line.rt-left{left:calc(var(--rt-line-weight) * -.5);transform-origin:bottom left;transform:rotate(0deg) scaleY(0)}
.rt-wrap:has(.rt-btn:hover) .rt-line.rt-left{animation:rt-draw-left var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * 2.4)}
@keyframes rt-draw-left{100%{transform:rotate(0deg) scaleY(1)}}
.rt-line.rt-right{right:calc(var(--rt-line-weight) * -.5);transform-origin:top right;transform:rotate(5deg) scaleY(0)}
.rt-wrap:has(.rt-btn:hover) .rt-line.rt-right{animation:rt-draw-right var(--rt-speed) ease-in-out forwards;animation-delay:calc(var(--rt-speed) * 1.4)}
@keyframes rt-draw-right{100%{transform:rotate(0deg) scaleY(1)}}
</style>

<div class="rt-tabs {{ $dark ? 'rt-tabs--dark' : '' }}">
    @foreach($rtTabs as $rtTab)
        @continue(!$rtTab['show'])
        @php $rtActive = $rtCurrent === $rtTab['route']; @endphp
        <div class="rt-wrap">
            <a href="{{ route($rtTab['route']) }}" wire:navigate.hover class="rt-btn {{ $rtActive ? 'rt-active' : '' }}">
                <span>{{ $rtTab['icon'] }}</span>
                <span>{{ $rtTab['label'] }}</span>
            </a>
            <div class="rt-dot rt-tl"></div>
            <div class="rt-dot rt-tr"></div>
            <div class="rt-dot rt-br"></div>
            <div class="rt-dot rt-bl"></div>
            <div class="rt-line rt-h rt-top"></div>
            <div class="rt-line rt-h rt-bottom"></div>
            <div class="rt-line rt-v rt-left"></div>
            <div class="rt-line rt-v rt-right"></div>
        </div>
    @endforeach
</div>
