<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->favicon(asset('favicon.png'))
            ->brandName(fn () => self::tenantBrandName())
            ->brandLogo(asset('images/makonn-mark.png'))
            ->brandLogoHeight('2rem')
            ->topNavigation()
            ->colors([
                'primary' => Color::Green,
            ])
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Loyihalar')
                    ->collapsible(),
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
            // "Loyiha holatlari" endi yuqori navigatsiyada emas — alohida
            // chap panelda ko'rsatiladi (pastdagi renderHook, statusRailHtml()).
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\WelcomeHeroWidget::class,
            ])
            // Brauzer avtomat tarjimasi (Google/Yandex) Livewire DOM'ini buzmasin — o'chiramiz
            ->renderHook(
                'panels::head.end',
                fn () => '<meta name="google" content="notranslate"><meta name="yandex" content="notranslate">',
            )
            // Filament v3.3.0'ning tayyor CSS to'plamida "theme-switcher" (kunduzgi/
            // tungi rejim) tugmasining hover/rang stillari yo'q ekan (paket ichidagi
            // nomuvofiqlik) — shu sababli sichqoncha ustiga kelganda tugma oq/bo'sh
            // bo'lib qolardi. Shu yerda qo'lda tiklaymiz.
            ->renderHook(
                'panels::head.end',
                fn () => '<style>
                    .fi-theme-switcher-btn{background:transparent}
                    .fi-theme-switcher-btn svg{color:#9ca3af}
                    .fi-theme-switcher-btn:hover,.fi-theme-switcher-btn:focus-visible{background-color:#f9fafb}
                    .fi-theme-switcher-btn:hover svg,.fi-theme-switcher-btn:focus-visible svg{color:#6b7280}
                    .fi-theme-switcher-btn.fi-active{background-color:#f9fafb}
                    .dark .fi-theme-switcher-btn svg{color:#6b7280}
                    .dark .fi-theme-switcher-btn:hover,.dark .fi-theme-switcher-btn:focus-visible{background-color:rgba(255,255,255,.05)}
                    .dark .fi-theme-switcher-btn:hover svg,.dark .fi-theme-switcher-btn:focus-visible svg{color:#9ca3af}
                    .dark .fi-theme-switcher-btn.fi-active{background-color:rgba(255,255,255,.05)}
                </style>',
            )
            ->renderHook(
                'panels::head.end',
                function () {
                    $ds = \App\Services\DesignSettingsService::class;
                    $s  = $ds::get();

                    $sidebarBg      = $ds::hexToRgba($s['sidebar_color'],      round($s['sidebar_opacity'] / 100, 2));
                    $sidebarText    = $s['sidebar_text_color'];
                    $sidebarActive  = $s['sidebar_active_color'];
                    $sidebarActiveBg = $ds::hexToRgba($sidebarActive, 0.12);

                    $sidebarDarkBg     = $ds::hexToRgba($s['sidebar_dark_color'], round($s['sidebar_dark_opacity'] / 100, 2));
                    $sidebarDarkText   = $s['sidebar_dark_text_color'];
                    $sidebarDarkActive = $s['sidebar_dark_active_color'];
                    $sidebarDarkActiveBg = $ds::hexToRgba($sidebarDarkActive, 0.14);

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
.fi-sidebar-item-active a, .fi-sidebar-item-active button { background-color: {$sidebarActiveBg} !important; border-radius: 8px !important; border-left: none !important; }
.fi-sidebar-item-active .fi-sidebar-item-label { color: {$sidebarActive} !important; font-weight: 600 !important; opacity: 1 !important; }
.fi-sidebar-item-active .fi-sidebar-item-icon { color: {$sidebarActive} !important; opacity: 1 !important; }

/* ── SIDEBAR: Dark ── */
.dark .fi-sidebar { background-color: {$sidebarDarkBg} !important; }
.dark .fi-sidebar-item-label { color: {$sidebarDarkText} !important; }
.dark .fi-sidebar-item-icon { color: {$sidebarDarkText} !important; opacity: 0.65; }
.dark .fi-brand-name { color: {$sidebarDarkText} !important; }
.dark .fi-sidebar-item-active a, .dark .fi-sidebar-item-active button { background-color: {$sidebarDarkActiveBg} !important; border-left: none !important; }
.dark .fi-sidebar-item-active .fi-sidebar-item-label { color: {$sidebarDarkActive} !important; font-weight: 600 !important; }
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

/* ── TOP NAV: guruh dropdown'lari (Vercel.com uslubida — keng, toza panel) ── */
.bh-nav-group { position: relative; list-style: none; }
.bh-nav-group-btn {
    display: flex; align-items: center; gap: 5px;
    padding: 8px 14px; border-radius: 8px;
    font-size: 0.875rem; font-weight: 500;
    color: {$headerText}; background: transparent; border: none; cursor: pointer;
    transition: background-color .15s ease;
}
.bh-nav-group-btn:hover,
.bh-nav-group-btn-active { background-color: rgba(255,255,255,0.07); }
.bh-nav-chevron { width: 14px; height: 14px; opacity: 0.55; transition: transform .15s ease; }
.bh-nav-chevron-open { transform: rotate(180deg); }
.bh-nav-panel {
    position: absolute; top: calc(100% + 8px); left: 0; z-index: 50;
    min-width: 230px; padding: 8px;
    display: flex; flex-direction: column; gap: 1px;
    background: #16181f; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;
    box-shadow: 0 24px 48px -12px rgba(0,0,0,0.55);
}
.bh-nav-panel-wide {
    min-width: 440px;
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px 8px;
}
.bh-nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 8px;
    font-size: 0.875rem; font-weight: 500;
    color: #d4d4d8; text-decoration: none;
    transition: background-color .12s ease, color .12s ease;
}
.bh-nav-item:hover { background-color: rgba(255,255,255,0.07); color: #fff; }
.bh-nav-item-active { color: {$sidebarDarkActive}; }
.bh-nav-item-icon { width: 18px; height: 18px; opacity: 0.65; flex-shrink: 0; }
@media (max-width: 1023px) { .bh-nav-group, .bh-topbar-nav { display: none !important; } }

/* Guruhsiz topbar elementlari (masalan bitta sahifa, dropdown emas) —
   Filamentning standart 'hover:bg-gray-50' foni majburiy oq matn bilan
   birga matnni ko'rinmas qilib qo'yardi (oq fonda oq matn). */
.fi-topbar-item-button:hover,
.fi-topbar-item-button:focus-visible { background-color: rgba(255,255,255,0.07) !important; }
.fi-topbar-item-button.bg-gray-50 { background-color: rgba(255,255,255,0.1) !important; }

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
/* Panel ichida select (Choices.js) ochilganda uning o'z ro'yxati
   overflow:hidden tomonidan kesib tashlanmasin — filtr panellaridagi
   oy kabi tanlovlar ochilganda ro'yxat butunlay ko'rinishi kerak. */
.fi-dropdown-panel:has(.choices.is-open) {
    overflow: visible !important;
}
.fi-dropdown-panel *,
.fi-dropdown-list-item-label,
.fi-dropdown-list-item button,
.fi-dropdown-list-item a,
.fi-dropdown-list-item span {
    color: #F3F4F6 !important;
}
/* Dropdown panel ichidagi filtr input/select maydonlari — matn oq bo'lgani
   uchun fonini ham qorong'i qilamiz, aks holda kunduzgi rejimda oq fonda
   oq matn ko'rinmay qoladi (Filtrlar panelidagi sana/oy tanlovlari). */
.fi-dropdown-panel input,
.fi-dropdown-panel select,
.fi-dropdown-panel .fi-input,
.fi-dropdown-panel .fi-select-input,
.fi-dropdown-panel .choices__inner,
.fi-dropdown-panel .choices__list--dropdown,
.fi-dropdown-panel .choices__list--dropdown .choices__item,
.fi-dropdown-panel .choices__list--dropdown .choices__input {
    background-color: #1f2937 !important;
    color: #F3F4F6 !important;
}
.fi-dropdown-panel .choices__list--dropdown .choices__item.is-highlighted {
    background-color: rgba(255,255,255,0.1) !important;
}
.fi-dropdown-panel input::placeholder {
    color: #9ca3af !important;
}
/* Sana tanlash kalendari (Filamentning o'z komponenti, fi-fo-date-time-picker-panel) —
   tashqi panel doim qorong'i qilib qo'yilgan, lekin bu ichki kichik panel
   o'zining kunduzgi rejim uchun mo'ljallangan OQ fonini saqlab qolardi —
   matn esa (yuqoridagi qoida bilan) oq rangga majburlangani sabab
   oq fonda oq matn bo'lib, kun raqamlari deyarli ko'rinmay qolgan edi. */
.fi-dropdown-panel .fi-fo-date-time-picker-panel {
    background-color: #1f2937 !important;
}
.fi-dropdown-panel .fi-fo-date-time-picker-panel [class*='bg-gray-50'] {
    background-color: rgba(255,255,255,0.1) !important;
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
            // Login'dan keyingi birinchi sahifada bir martalik "zagruzka"
            // animatsiyasi — LoginResponse shu sessiya bayrog'ini o'rnatadi,
            // biz uni bu yerda o'qib DARHOL o'chiramiz (session()->pull),
            // shu bilan faqat shu bitta sahifa ochilishida ko'rinadi.
            ->renderHook(
                'panels::body.start',
                function () {
                    if (! session()->pull('bh_show_splash')) {
                        return '';
                    }

                    return <<<'HTML'
<div id="bh-splash" style="position:fixed;inset:0;z-index:999999;background:#fff;display:flex;align-items:center;justify-content:center;flex-direction:column;transition:opacity .5s ease">
    <svg width="588" height="364" viewBox="0 0 640 360" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M50,300 L580,300" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw .6s ease forwards"/>
        <path d="M50,300 C330,300 560,295 560,290" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .50s"/>
        <path d="M50,300 C320,300 540,270 540,240" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .55s"/>
        <path d="M50,300 C298,300 500,245 500,190" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .60s"/>
        <path d="M50,300 C276,300 460,220 460,140" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .65s"/>
        <path d="M50,300 C254,300 420,195 420,90" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .70s"/>
        <path d="M50,300 C232,300 380,175 380,50" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .75s"/>
        <path d="M50,300 C166,300 260,160 260,20" stroke="#111" stroke-width="3" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:bh-splash-draw 1.2s ease forwards .80s"/>
    </svg>
    <div style="margin-top:4px;font-family:inherit;font-size:26px;font-weight:300;letter-spacing:.04em;color:#111;opacity:0;animation:bh-splash-text 0.6s ease forwards 2.1s">MPH Architecture</div>
</div>
<style>
@keyframes bh-splash-draw { to { stroke-dashoffset:0; } }
@keyframes bh-splash-text { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
</style>
<script>
setTimeout(function () {
    var el = document.getElementById('bh-splash');
    if (!el) return;
    el.style.opacity = '0';
    setTimeout(function () { el.remove(); }, 500);
}, 2800);
</script>
HTML;
                }
            )
            ->renderHook(
                'panels::body.start',
                fn () => $this->statusRailHtml()
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
                    $name  = e(self::tenantBrandName());
                    $logoUrl = e(asset('images/makonn-mark.png'));
                    $logo  = "<a href=\"/admin\" wire:navigate style=\"display:flex;align-items:center;flex-shrink:0;\"><img src=\"{$logoUrl}\" alt=\"{$name} logo\" style=\"height:2rem;\"></a>";
                    $brand = "<a href=\"/admin\" wire:navigate style=\"color:{$color};font-weight:800;font-size:0.95rem;letter-spacing:0.07em;padding:0 1.25rem;white-space:nowrap;flex-shrink:0;text-decoration:none;\">{$name}</a>";

                    // Boshqa shahar (tenant) saytlariga o'tish — "Loyihalar"/
                    // "Sozlamalar" bilan bir xil uslubdagi ochiladigan ro'yxat
                    // ("Shaharlar"). Joriy host o'zi shu ro'yxatda bo'lsa,
                    // o'ziga havola ko'rsatilmaydi.
                    $currentHost = request()->getHost();
                    $cityItems   = '';
                    foreach (config('tenants', []) as $host => $tenant) {
                        if ($host === $currentHost) {
                            continue;
                        }
                        $label = e($tenant['label'] ?? $host);
                        $url   = e('https://' . $host . '/admin');
                        $cityItems .= "<a href=\"{$url}\" class=\"bh-nav-item\"><span>{$label}</span></a>";
                    }

                    $citiesDropdown = '';
                    if ($cityItems !== '') {
                        $citiesDropdown = <<<HTML
<div class="bh-nav-group" style="display:inline-flex;align-items:center;" x-data="{ open: false, closeTimer: null }"
    x-on:mouseenter="clearTimeout(closeTimer); open = true"
    x-on:mouseleave="closeTimer = setTimeout(() => open = false, 150)"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button type="button" x-on:click="open = ! open" :aria-expanded="open" class="bh-nav-group-btn" :class="{ 'bh-nav-group-btn-active': open }">
        Shaharlar
        <svg viewBox="0 0 20 20" fill="currentColor" class="bh-nav-chevron" :class="{ 'bh-nav-chevron-open': open }">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>
    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak class="bh-nav-panel">
        {$cityItems}
    </div>
</div>
HTML;
                    }

                    return $brand . $logo . $citiesDropdown;
                }
            )
            ->renderHook(
                'panels::body.end',
                function () {
                    $html = '<script src="' . asset('js/map-picker.js') . '?v=11" defer></script>';
                    // Chekni chop etishdan oldin qog'oz kengligini (58/80mm) bir marta
                    // ikkita aniq tugma bilan so'rab, shu brauzerda eslab qoladi — chek
                    // shablonini shu kenglikka moslab beradi (print/chek.blade.php).
                    // Oldin oddiy prompt() ishlatilgan edi, lekin "80" oldindan
                    // to'ldirilgan bo'lib, o'qimasdan OK bosilsa noto'g'ri saqlanardi —
                    // shu sabab ikkita katta tugmali oynaga o'zgartirildi.
                    $html .= <<<'HTML'
<script>
window.bhOpenChek = function (paymentId) {
    var w = localStorage.getItem('bh_chek_width');
    if (w === '58' || w === '80') {
        window.open('/print/payment/' + paymentId + '/chek?width=' + w, '_blank', 'width=380,height=600');
        return;
    }
    var ov = document.createElement('div');
    ov.style.cssText = 'position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center';
    ov.innerHTML =
        '<div style="background:#fff;border-radius:14px;padding:26px 28px;max-width:340px;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,.3)">' +
            '<div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:6px">Chek printeringiz qog\'oz kengligi?</div>' +
            '<div style="font-size:12.5px;color:#6b7280;margin-bottom:18px">Bir marta tanlaysiz, shu kompyuterda eslab qoladi</div>' +
            '<div style="display:flex;gap:10px;justify-content:center">' +
                '<button data-w="58" style="flex:1;padding:14px 0;border-radius:10px;border:1.5px solid #d1d5db;background:#fff;font-size:14px;font-weight:700;cursor:pointer;color:#111827">58 mm<br><span style="font-weight:400;font-size:11px;color:#9ca3af">kichik qog\'oz</span></button>' +
                '<button data-w="80" style="flex:1;padding:14px 0;border-radius:10px;border:1.5px solid #d1d5db;background:#fff;font-size:14px;font-weight:700;cursor:pointer;color:#111827">80 mm<br><span style="font-weight:400;font-size:11px;color:#9ca3af">katta qog\'oz</span></button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(ov);
    ov.querySelectorAll('button').forEach(function (btn) {
        btn.onclick = function () {
            var chosen = btn.getAttribute('data-w');
            localStorage.setItem('bh_chek_width', chosen);
            ov.remove();
            window.open('/print/payment/' + paymentId + '/chek?width=' + chosen, '_blank', 'width=380,height=600');
        };
    });
};
// "⚙" tugmasi — chek kengligi tanlovini tozalab, darhol qaytadan so'raydi
// (noto'g'ri tanlangan bo'lsa yoki printer almashtirilganda ishlatiladi).
window.bhChangeChekWidth = function (paymentId) {
    localStorage.removeItem('bh_chek_width');
    window.bhOpenChek(paymentId);
};
</script>
HTML;
                    if (auth()->check()) {
                        $html .= \Illuminate\Support\Facades\Blade::render('@livewire(\'message-notifier\')');
                        $html .= \Illuminate\Support\Facades\Blade::render('@livewire(\'my-balance\')');
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

    /**
     * Poddomenga qarab shahar nomi (masalan andijon.makonn.uz -> "Andijon"),
     * config/tenants.php'dagi 'label' bo'yicha. Topilmasa asosiy brend nomi.
     */
    private static function tenantBrandName(): string
    {
        $tenant = config('tenants')[request()->getHost()] ?? null;

        return $tenant['label'] ?? 'MAKONN.UZ';
    }

    // "Loyiha holatlari" — yuqori navigatsiyaga o'tilgandan keyin ham
    // alohida, doimiy ko'rinadigan chap panelda qoladi (tez-tez ishlatiladigan,
    // uzun ro'yxat bo'lgani uchun dropdown'dan ko'ra shu qulayroq).
    private function statusRailHtml(): string
    {
        if (! auth()->check()) {
            return '';
        }

        // Bo'limlar bazadan o'qiladi — doska (kanban) bilan aynan bir xil bo'lishi uchun
        $statuses = [];
        try {
            $statuses = \App\Models\ProjectStatus::allOrdered()
                ->where('is_hidden', false)
                ->pluck('label', 'key')
                ->toArray();
        } catch (\Throwable $e) {
            // Baza tayyor bo'lmagan holat (migratsiya/o'rnatishdan oldin)
        }

        // Hodim (bajaruvchi) — faqat o'z ish bo'limlari ko'rinadi
        $user = auth()->user();
        if ($user && $user->isBajaruvchi()) {
            $allowedCols = $user->kanbanServiceCols();
            $statuses = array_filter($statuses, fn ($label, $key) => in_array($key, $allowedCols), ARRAY_FILTER_USE_BOTH);
        }

        // "Loyihalar" (Kanban board) — panelning eng tepasida, "Loyiha
        // holatlari" ro'yxatidan (Ariza va h.k.) yuqorida turadi.
        $projectLinks = '';
        if (\App\Filament\Pages\KanbanBoard::canAccess()) {
            $href = e(\App\Filament\Pages\KanbanBoard::getUrl());
            $projectLinks = "<a href=\"{$href}\" wire:navigate class=\"bsr-item bsr-item-top\" data-exact-href=\"{$href}\">Loyihalar</a>";
        }

        if (empty($statuses) && $projectLinks === '') {
            return '';
        }

        $s = \App\Services\DesignSettingsService::get();
        $railBg         = $s['sidebar_color'];
        $railText       = $s['sidebar_text_color'];
        $railActive     = $s['sidebar_active_color'];
        $railDarkBg     = $s['sidebar_dark_color'];
        $railDarkText   = $s['sidebar_dark_text_color'];
        $railDarkActive = $s['sidebar_dark_active_color'];

        $items = '';
        foreach ($statuses as $key => $label) {
            $key   = e($key);
            $label = e($label);
            $items .= "<a href=\"/admin/kanban-board?status={$key}\" data-status-key=\"{$key}\" wire:navigate class=\"bsr-item\">{$label}</a>";
        }

        $topBlock = '';
        if ($projectLinks !== '') {
            $topBlock = "<nav class=\"bsr-top-nav\">{$projectLinks}</nav><div class=\"bsr-divider\"></div>";
        }
        $statusBlock = '';
        if (! empty($statuses)) {
            $statusBlock = "<div class=\"bsr-title\">Loyiha holatlari</div><nav>{$items}</nav>";
        }

        return <<<HTML
<aside id="bh-status-rail" class="bh-status-rail">
    {$topBlock}
    {$statusBlock}
</aside>
<style>
.bh-status-rail{position:fixed;top:64px;left:0;width:200px;height:calc(100vh - 64px);overflow-y:auto;padding:16px 10px;z-index:30;background:{$railBg};}
.bh-status-rail .bsr-title{font-size:.62rem;letter-spacing:.14em;text-transform:uppercase;font-weight:600;color:{$railText};opacity:.55;padding:0 10px 10px;}
.bh-status-rail .bsr-item{display:block;padding:8px 12px;border-radius:8px;font-size:.85rem;font-weight:500;color:{$railText};text-decoration:none;margin-bottom:2px;opacity:.85;}
.bh-status-rail .bsr-item:hover{background:rgba(0,0,0,0.08);opacity:1;}
.bh-status-rail .bsr-item.active{background:rgba(0,0,0,0.10);color:{$railActive};font-weight:700;opacity:1;}
.bh-status-rail .bsr-top-nav{margin-bottom:4px;}
.bh-status-rail .bsr-item-top{font-weight:600;}
.bh-status-rail .bsr-divider{height:1px;background:{$railText}22;margin:8px 10px 12px;}
.dark .bh-status-rail{background:{$railDarkBg};}
.dark .bh-status-rail .bsr-title,
.dark .bh-status-rail .bsr-item{color:{$railDarkText};}
.dark .bh-status-rail .bsr-item:hover{background:rgba(255,255,255,0.06);}
.dark .bh-status-rail .bsr-item.active{background:rgba(255,255,255,0.08);color:{$railDarkActive};}
.dark .bh-status-rail .bsr-divider{background:{$railDarkText}22;}
@media(max-width:1023px){.bh-status-rail{display:none;}}
@media(min-width:1024px){
    .fi-main,.fi-main-ctn > .fi-main,main.fi-main{padding-left:220px!important;}
}
</style>
<script>
(function(){
    function markActive(){
        var url = new URL(window.location.href);
        var key = url.searchParams.get('status');
        var onBoard = /\/admin\/kanban-board/.test(url.pathname);
        document.querySelectorAll('.bh-status-rail .bsr-item[data-status-key]').forEach(function(el){
            el.classList.toggle('active', onBoard && el.dataset.statusKey === key);
        });
        document.querySelectorAll('.bh-status-rail .bsr-item-top').forEach(function(el){
            var linkPath = el.getAttribute('href').split('?')[0];
            el.classList.toggle('active', !onBoard && url.pathname === linkPath);
        });
    }
    markActive();
    document.addEventListener('livewire:navigated', markActive);
})();
</script>
HTML;
    }
}
