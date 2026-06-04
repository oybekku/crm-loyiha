<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('favicon.png'))
            ->brandName('MAKONN.UZ')
            ->colors([
                'primary' => Color::Green,
            ])
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Loyihalar')
                    ->collapsible(),
                \Filament\Navigation\NavigationGroup::make('Loyiha holatlari')
                    ->icon('heroicon-o-funnel')
                    ->collapsible()
                    ->collapsed(),
                \Filament\Navigation\NavigationGroup::make('Xodimlar')
                    ->collapsible(),
                \Filament\Navigation\NavigationGroup::make('Sozlamalar')
                    ->collapsible(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->navigationItems($this->buildStatusNavItems())
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\WelcomeHeroWidget::class,
            ])
            ->renderHook(
                'panels::head.end',
                function () {
                    $ds = \App\Services\DesignSettingsService::class;
                    $s  = $ds::get();

                    $sidebarBg      = $ds::hexToRgba($s['sidebar_color'],      round($s['sidebar_opacity'] / 100, 2));
                    $sidebarText    = $s['sidebar_text_color'];
                    $sidebarActive  = $s['sidebar_active_color'];

                    $sidebarDarkBg     = $ds::hexToRgba($s['sidebar_dark_color'], round($s['sidebar_dark_opacity'] / 100, 2));
                    $sidebarDarkText   = $s['sidebar_dark_text_color'];
                    $sidebarDarkActive = $s['sidebar_dark_active_color'];

                    $headerBg   = $ds::hexToRgba($s['header_color'], round($s['header_opacity'] / 100, 2));
                    $headerText = $s['header_text_color'];

                    $lightBg   = $s['light_mode_bg'];
                    $lightText = $s['light_mode_text_color'];
                    $darkBg    = $s['dark_mode_bg'];
                    $darkText  = $s['dark_mode_text_color'];

                    $lottieUrl    = $s['sidebar_lottie_url'] ?? '';
                    $lottieScript = $lottieUrl
                        ? '<script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.14/dist/dotlottie-wc.js" type="module"></script>'
                        : '';

                    // ── Login sahifasi foni ──
                    $loginBg      = $s['login_bg_image'] ?? '';
                    $loginOpacity = round(($s['login_bg_opacity'] ?? 80) / 100, 2);
                    $loginCard    = $s['login_card_blur']  ?? 'glass';
                    $loginBgCss   = '';
                    if ($loginBg) {
                        $bgUrl = asset('storage/' . $loginBg);
                        $cardBg = match($loginCard) {
                            'glass' => 'background:rgba(255,255,255,0.75)!important;backdrop-filter:blur(16px)!important;-webkit-backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,0.5)!important;',
                            'none'  => 'background:transparent!important;',
                            default => 'background:#ffffff!important;',
                        };
                        $loginBgCss = "
.fi-simple-layout{
    background-image:url('{$bgUrl}')!important;
    background-size:cover!important;
    background-position:center!important;
    background-repeat:no-repeat!important;
    align-items:flex-start!important;
    justify-content:flex-end!important;
    padding:40px 60px!important;
}
.fi-simple-main{
    position:relative!important;
    z-index:1!important;
    background:rgba(255,255,255,0.12)!important;
    backdrop-filter:blur(20px)!important;
    -webkit-backdrop-filter:blur(20px)!important;
    border:1px solid rgba(255,255,255,0.25)!important;
    border-radius:20px!important;
    box-shadow:0 8px 40px rgba(0,0,0,0.4)!important;
    min-width:380px!important;
    max-width:420px!important;
}
.fi-simple-main-ctn{padding:2rem!important;}
.fi-simple-layout .fi-brand-name,
.fi-simple-layout h1,
.fi-simple-layout h2{color:#fff!important;text-shadow:0 1px 4px rgba(0,0,0,0.5)!important;}
.fi-simple-layout label,
.fi-simple-layout .fi-fo-field-wrp-label{color:rgba(255,255,255,0.9)!important;}
.fi-simple-layout .fi-checkbox-label{color:rgba(255,255,255,0.85)!important;}
";
                    }

                    $heroType = $s['hero_anim_type'] ?? 'none';
                    $isDashboard = request()->routeIs('filament.admin.pages.dashboard');
                    $heroBgCss = ($heroType !== 'none' && $isDashboard) ? "
body,.fi-body,.fi-main,.fi-main-ctn,main.fi-main{background:transparent!important;}
.dark body,.dark .fi-body,.dark .fi-main,.dark .fi-main-ctn,.dark main.fi-main{background:transparent!important;}" : '';

                    $animOpacity   = round(($s['sidebar_anim_opacity'] ?? 30) / 100, 2);
                    $animScale     = round(($s['sidebar_anim_scale']   ?? 100) / 100, 2);
                    $animSpeed     = max(0.1, (float)($s['sidebar_anim_speed'] ?? 1));
                    $animLoop      = (int)($s['sidebar_anim_loop'] ?? 0);
                    $animCycleDur  = round(14 / $animSpeed, 1);
                    $animIterCount = $animLoop > 0 ? $animLoop : 'infinite';

                    return $lottieScript . "<style>{$loginBgCss}
/* ─── BESTHOME CRM: Dinamik tema ─── */

/* ── SIDEBAR: Light ── */
.fi-sidebar { background-color: {$sidebarBg} !important; border-right: none !important; }
.fi-sidebar-header { display: none !important; }
.fi-sidebar-footer { background-color: rgba(0,0,0,0.12) !important; border-top: 1px solid rgba(255,255,255,0.1) !important; }
.fi-sidebar-group-label { color: {$sidebarText} !important; opacity: 0.55; font-size: 0.62rem !important; letter-spacing: 0.14em !important; text-transform: uppercase !important; font-weight: 600 !important; }
.fi-sidebar-item-label { color: {$sidebarText} !important; font-weight: 500 !important; opacity: 0.92; }
.fi-sidebar-item-icon { color: {$sidebarText} !important; opacity: 0.65; }
.fi-sidebar-item a:hover, .fi-sidebar-item button:hover { background-color: rgba(0,0,0,0.15) !important; border-radius: 8px !important; }
.fi-sidebar-item a:hover .fi-sidebar-item-label,
.fi-sidebar-item button:hover .fi-sidebar-item-label,
.fi-sidebar-item a:hover .fi-sidebar-item-icon,
.fi-sidebar-item button:hover .fi-sidebar-item-icon { color: {$sidebarText} !important; opacity: 1 !important; }
.fi-sidebar-item-active a, .fi-sidebar-item-active button { background-color: rgba(0,0,0,0.2) !important; border-radius: 8px !important; border-left: 3px solid {$sidebarActive} !important; }
.fi-sidebar-item-active .fi-sidebar-item-label { color: {$sidebarActive} !important; font-weight: 700 !important; opacity: 1 !important; }
.fi-sidebar-item-active .fi-sidebar-item-icon { color: {$sidebarActive} !important; opacity: 1 !important; }

/* ── SIDEBAR: Dark ── */
.dark .fi-sidebar { background-color: {$sidebarDarkBg} !important; }
.dark .fi-sidebar-item-label { color: {$sidebarDarkText} !important; }
.dark .fi-sidebar-item-icon { color: {$sidebarDarkText} !important; opacity: 0.65; }
.dark .fi-brand-name { color: {$sidebarDarkText} !important; }
.dark .fi-sidebar-item-active .fi-sidebar-item-label { color: {$sidebarDarkActive} !important; }
.dark .fi-sidebar-item-active .fi-sidebar-item-icon { color: {$sidebarDarkActive} !important; }

/* ── SIDEBAR: Uy animatsiyasi ── */
.bh-build-wrap {
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    width: 16rem !important;
    height: calc(100vh - 64px) !important;
    pointer-events: none !important;
    z-index: 20 !important;
    overflow: hidden !important;
    animation: bh-wrap-cycle 14s ease-in-out infinite;
}
.bh-build-svg { position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; }
.bh-lottie-wrap {
    opacity: 0.3 !important;
    animation: none !important;
}
.bh-lottie-wrap dotlottie-wc { width: 100%; height: 100%; display: block; }
@media (max-width: 1023px) { .bh-build-wrap { display: none; } }
@keyframes bh-wrap-cycle {
    0%,3%  { opacity: 0; }
    8%     { opacity: 0.3; }
    52%    { opacity: 0.3; }
    62%    { opacity: 0; }
    100%   { opacity: 0; }
}
@keyframes bh-draw {
    0%,1%  { stroke-dashoffset: 1000; }
    32%    { stroke-dashoffset: 0; }
    60%    { stroke-dashoffset: 0; }
    65%    { stroke-dashoffset: 1000; }
    100%   { stroke-dashoffset: 1000; }
}
.bh-p1  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 0.0s; }
.bh-p2  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 0.4s; }
.bh-p3  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 0.8s; }
.bh-p4  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 0.6s; }
.bh-p5  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 0.5s; }
.bh-p6  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 1.0s; }
.bh-p7  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 1.2s; }
.bh-p8  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 1.5s; }
.bh-p9  { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 1.7s; }
.bh-p10 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.0s; }
.bh-p11 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.0s; }
.bh-p12 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.2s; }
.bh-p13 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.5s; }
.bh-p14 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.7s; }
.bh-p15 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.6s; }
.bh-p16 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.4s; }
.bh-p17 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 2.7s; }
.bh-p18 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 3.0s; }
.bh-p19 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 3.3s; }
.bh-p20 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 3.6s; }
.bh-p21 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 3.9s; }
.bh-p22 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 4.2s; }
.bh-p23 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 4.5s; }
.bh-p24 { stroke-dasharray:1000;stroke-dashoffset:1000;animation:bh-draw 14s ease-in-out infinite 4.8s; }

/* ── HEADER: full width, sidebar ustida ── */
.fi-topbar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100vw !important;
    z-index: 99 !important;
    color: {$headerText} !important;
}
.fi-topbar nav, .fi-topbar > nav, div.fi-topbar > nav {
    background-color: {$headerBg} !important;
    background: {$headerBg} !important;
    color: {$headerText} !important;
    border-bottom: 1px solid rgba(255,255,255,0.12) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
}
.fi-topbar nav { --tw-ring-color: transparent !important; }

/* Layout header tagidan boshlanadi */
.fi-layout {
    padding-top: 64px !important;
}
.fi-sidebar {
    height: calc(100vh - 64px) !important;
    overflow-y: auto !important;
}
.fi-main-ctn {
    padding-top: 0 !important;
}
.fi-topbar button,
.fi-topbar a,
.fi-topbar span,
.fi-topbar p,
.fi-topbar li,
.fi-topbar label { color: {$headerText} !important; }
.fi-topbar svg,
.fi-topbar svg path { color: {$headerText} !important; fill: currentColor; opacity: 0.85; }
.fi-breadcrumbs-item-label { color: {$headerText} !important; }
.fi-breadcrumbs-separator-icon { color: {$headerText} !important; opacity: 0.5; }

/* ── PAGE HEADER: faqat sarlavha matnini yashir, tugmalar ko'rinsin ── */
.fi-page-header-heading,
.fi-header-heading,
.fi-page-header > .fi-page-header-heading { display: none !important; }

/* ── CONTENT: sidebar yaqin, 20px gap ── */
.fi-main,
.fi-main-ctn > .fi-main,
main.fi-main {
    margin-left: 0 !important;
    margin-right: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    padding: 20px !important;
}
.fi-page {
    padding: 0 !important;
    max-width: none !important;
    margin: 0 !important;
}
.fi-page-content {
    padding: 0 !important;
}

/* ── FON: Light ── */
body, .fi-body, .fi-main, .fi-main-ctn, main.fi-main {
    background-color: {$lightBg} !important;
}
body, .fi-main p, .fi-main span, .fi-main h1, .fi-main h2, .fi-main h3,
.fi-main label, .fi-main td, .fi-main th, .fi-main div {
    color: {$lightText};
}

/* ── FON: Dark ── */
.dark body, .dark .fi-body, .dark .fi-main, .dark .fi-main-ctn, .dark main.fi-main {
    background-color: {$darkBg} !important;
}
.dark .fi-main p, .dark .fi-main span, .dark .fi-main h1, .dark .fi-main h2,
.dark .fi-main h3, .dark .fi-main label, .dark .fi-main td, .dark .fi-main th {
    color: {$darkText} !important;
}

/* ── USER MENU DROPDOWN ── */
.fi-dropdown-panel {
    background-color: #374151 !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.35) !important;
    border-radius: 12px !important;
    overflow: hidden !important;
}
.fi-dropdown-panel *,
.fi-dropdown-list-item-label,
.fi-dropdown-list-item button,
.fi-dropdown-list-item a,
.fi-dropdown-list-item span {
    color: #F3F4F6 !important;
}
.fi-dropdown-list-item button:hover,
.fi-dropdown-list-item a:hover {
    background-color: rgba(255,255,255,0.1) !important;
}
.fi-user-menu-profile-name,
.fi-user-menu-profile-email {
    color: #F3F4F6 !important;
}
/* Theme switcher — yashirilgan */
.fi-color-mode-switcher { display: none !important; }
.fi-dropdown-header {
    background-color: rgba(0,0,0,0.2) !important;
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
}

/* ── MOBILE SIDEBAR: topbar tagidan boshlash ── */
@media (max-width: 1023px) {
    .fi-sidebar {
        top: 64px !important;
        height: calc(100vh - 64px) !important;
        max-height: calc(100vh - 64px) !important;
    }
    .fi-sidebar-header {
        display: flex !important;
        padding: 10px 14px !important;
        border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    }
}

/* ── Sidebar yig'ish/kengaytirish ── */
.fi-sidebar { transition: width 0.28s cubic-bezier(.4,0,.2,1) !important; min-width:0 !important; overflow:hidden !important; }
.fi-sidebar.bh-sb-col { width:3.8rem !important; }
.fi-sidebar.bh-sb-col .fi-sidebar-item-label { display:none !important; }
.fi-sidebar.bh-sb-col .fi-sidebar-group-label { display:none !important; }
.fi-sidebar.bh-sb-col .fi-sidebar-item a,
.fi-sidebar.bh-sb-col .fi-sidebar-item button { justify-content:center !important; padding:0.75rem 0 !important; }
.fi-sidebar.bh-sb-col .fi-sidebar-item-icon,
.fi-sidebar.bh-sb-col .fi-sidebar-item-icon svg { width:1.5rem !important; height:1.5rem !important; margin-right:0 !important; opacity:1 !important; }
@media(max-width:1023px){ .fi-sidebar.bh-sb-col { width:100% !important; } }

/* ── Animatsiya sozlamalari ── */
.bh-build-wrap {
    animation-duration: {$animCycleDur}s !important;
    animation-iteration-count: {$animIterCount} !important;
}
[class^='bh-p'] { animation-duration: {$animCycleDur}s !important; }
.bh-build-svg {
    transform: scale({$animScale});
    transform-origin: bottom center;
}
.bh-lottie-wrap {
    opacity: {$animOpacity} !important;
    animation: none !important;
}
.bh-lottie-wrap dotlottie-wc {
    transform: scale({$animScale});
    transform-origin: bottom center;
    width: 100%; height: 100%; display: block;
}
@keyframes bh-wrap-cycle {
    0%,3%  { opacity: 0; }
    8%     { opacity: {$animOpacity}; }
    52%    { opacity: {$animOpacity}; }
    62%    { opacity: 0; }
    100%   { opacity: 0; }
}

{$heroBgCss}
</style>";
                }
            )
            ->renderHook(
                'panels::body.start',
                function () {
                    // Faqat bosh sahifada ko'rsat
                    if (! request()->routeIs('filament.admin.pages.dashboard')) return '';

                    $ds  = \App\Services\DesignSettingsService::class;
                    $s   = $ds::get();
                    $heroType = $s['hero_anim_type'] ?? 'none';
                    if ($heroType === 'none') return '';

                    $opacity = round(($s['hero_anim_opacity'] ?? 65) / 100, 2);
                    $baseStyle = "position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:-1;pointer-events:none;opacity:{$opacity};";

                    if ($heroType === 'video') {
                        $vUrl     = $s['hero_anim_video_url'] ?? '/videos/exelentex-bg.mp4';
                        $isExt    = str_starts_with($vUrl, 'http');
                        $vSrc     = $isExt ? $vUrl : asset(ltrim($vUrl, '/'));
                        $vExt     = strtolower(pathinfo($vUrl, PATHINFO_EXTENSION));
                        $vMime    = $vExt === 'webm' ? 'video/webm' : 'video/mp4';
                        $vSrcAlt  = $vExt === 'webm'
                            ? asset(ltrim(preg_replace('/\.webm$/i', '.mp4', $vUrl), '/'))
                            : asset(ltrim(preg_replace('/\.mp4$/i', '.webm', $vUrl), '/'));
                        $vMimeAlt = $vExt === 'webm' ? 'video/mp4' : 'video/webm';
                        return "<video id=\"bh-global-bg\" style=\"{$baseStyle}object-fit:cover;\" autoplay muted loop playsinline preload=\"none\"><source src=\"{$vSrc}\" type=\"{$vMime}\"><source src=\"{$vSrcAlt}\" type=\"{$vMimeAlt}\"></video>";
                    }

                    if ($heroType === 'lottie') {
                        $lUrl   = $s['hero_anim_lottie_url'] ?? '';
                        $lSpeed = max(0.1, (float)($s['hero_anim_speed'] ?? 1));
                        if (!$lUrl) return '';
                        return "<div id=\"bh-global-bg\" style=\"{$baseStyle}\"><dotlottie-wc src=\"{$lUrl}\" speed=\"{$lSpeed}\" loop autoplay style=\"width:100%;height:100%;\"></dotlottie-wc></div>";
                    }

                    if ($heroType === 'css') {
                        $cssCode = $s['hero_anim_css_code'] ?? '';
                        if (!$cssCode) return '';
                        return "<style>{$cssCode}</style><div id=\"bh-global-bg\" class=\"bh-css-anim\" style=\"{$baseStyle}\"></div>";
                    }

                    return '';
                }
            )
            ->renderHook(
                'panels::sidebar.footer',
                function () {
                    return <<<'HTML'
<div style="position:relative;z-index:30;border-top:1px solid rgba(128,128,128,0.18);">
    <button onclick="bhTgl()" id="bh-tgl-btn"
        style="width:100%;display:flex;align-items:center;gap:10px;padding:11px 16px;border:none;background:transparent;cursor:pointer;color:inherit;font-size:12px;font-weight:600;transition:opacity .15s;overflow:hidden;white-space:nowrap;opacity:0.7;"
        onmouseover="this.style.opacity='1'"
        onmouseout="this.style.opacity='0.7'">
        <svg id="bh-tgl-icon" style="flex-shrink:0;transition:transform 0.3s ease;width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M11 19l-7-7 7-7"/><path d="M18 19l-7-7 7-7"/>
        </svg>
        <span id="bh-tgl-lbl">Yig'ish</span>
    </button>
</div>
<script>
(function(){
    var K='bh_sb_col';

    /* Tooltip */
    var tip=document.createElement('div');
    tip.style.cssText='position:fixed;background:#1e293b;color:#f1f5f9;padding:5px 13px;border-radius:8px;font-size:12px;font-weight:600;white-space:nowrap;pointer-events:none;z-index:99999;opacity:0;transition:opacity .15s;box-shadow:0 4px 16px rgba(0,0,0,.35);border:1px solid rgba(255,255,255,0.08);display:none';
    document.body.appendChild(tip);

    function showTip(el, text){
        tip.textContent=text;
        tip.style.display='block';
        var r=el.getBoundingClientRect();
        tip.style.top=(r.top+r.height/2-14)+'px';
        tip.style.left=(r.right+10)+'px';
        requestAnimationFrame(function(){ tip.style.opacity='1'; });
    }
    function hideTip(){
        tip.style.opacity='0';
        setTimeout(function(){ if(tip.style.opacity==='0') tip.style.display='none'; },160);
    }

    function initTooltips(){
        document.querySelectorAll('.fi-sidebar-item a,.fi-sidebar-item button').forEach(function(el){
            if(el._bhTip) return;
            el._bhTip=true;
            var lbl=el.querySelector('.fi-sidebar-item-label');
            if(!lbl) return;
            var txt=lbl.textContent.trim();
            el.addEventListener('mouseenter',function(){ if(document.querySelector('.fi-sidebar').classList.contains('bh-sb-col')) showTip(el,txt); });
            el.addEventListener('mouseleave', hideTip);
        });
    }

    function updateBtn(col){
        var icon=document.getElementById('bh-tgl-icon');
        var lbl=document.getElementById('bh-tgl-lbl');
        if(icon) icon.style.transform=col?'rotate(180deg)':'rotate(0deg)';
        if(lbl)  lbl.style.display=col?'none':'inline';
    }

    function apply(col){
        var sb=document.querySelector('.fi-sidebar');
        if(!sb) return;
        sb.classList.toggle('bh-sb-col',!!col);
        updateBtn(col);
        setTimeout(initTooltips,120);
    }

    window.bhTgl=function(){
        var col=!document.querySelector('.fi-sidebar').classList.contains('bh-sb-col');
        localStorage.setItem(K,col?'1':'0');
        apply(col);
    };

    function init(){ apply(localStorage.getItem(K)==='1'); }
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init);
    else setTimeout(init,60);
    document.addEventListener('livewire:navigated',function(){ setTimeout(init,60); });
})();
</script>
HTML;
                }
            )
            ->renderHook(
                'panels::topbar.start',
                function () {
                    $s     = \App\Services\DesignSettingsService::get();
                    $color = $s['header_text_color'];
                    return "<a href=\"/admin\" wire:navigate style=\"color:{$color};font-weight:800;font-size:0.95rem;letter-spacing:0.07em;padding:0 1.25rem;white-space:nowrap;flex-shrink:0;text-decoration:none;\">MAKONN.UZ</a>";
                }
            )
            ->renderHook(
                'panels::body.end',
                function () {
                    $html = '<script src="' . asset('js/map-picker.js') . '?v=11" defer></script>';
                    if (auth()->check()) {
                        $html .= \Illuminate\Support\Facades\Blade::render('@livewire(\'message-notifier\')');
                    }
                    return $html;
                }
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function buildStatusNavItems(): array
    {
        $statuses = [
            'yangi'            => "Ariza",
            'tolov_jarayonida' => "To'lov jarayonida",
            'yangi_loyihalar'  => "Yangi loyihalar",
            'toposyomka'       => 'Toposyomka',
            'eskiz_loyiha'     => 'Eskiz loyiha',
            'tekshirish'       => 'Tekshirish',
            'tolangan'         => "To'langan",
            'tugallangan'      => 'Tugallangan',
            'taqdim_etilgan'   => 'Taqdim etilgan',
            'bekor_qilingan'   => 'Bekor qilingan',
        ];

        $items = [];
        $sort  = 1;
        foreach ($statuses as $key => $label) {
            $items[] = NavigationItem::make($label)
                ->url('/admin/kanban-board?status=' . $key)
                ->group('Loyiha holatlari')
                ->sort($sort++)
                ->isActiveWhen(fn () => request()->get('status') === $key
                    && request()->routeIs('filament.admin.pages.kanban-board'));
        }

        return $items;
    }
}
