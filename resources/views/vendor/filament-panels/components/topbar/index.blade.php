{{--
    BESTHOME CRM: Filament'ning asl topbar/index.blade.php'sidan nusxa — faqat
    guruh dropdown qismi o'zgartirilgan (Vercel.com uslubidagi keng, ko'p
    ustunli menyu uchun). Filament yangilanganda bu faylni asl nusxa bilan
    solishtirib turish kerak (vendor/filament/filament/resources/views/
    components/topbar/index.blade.php).
--}}
@props([
    'navigation',
])

<div
    {{
        $attributes->class([
            'fi-topbar sticky top-0 z-20 overflow-x-clip',
            'fi-topbar-with-navigation' => filament()->hasTopNavigation(),
        ])
    }}
>
    <nav
        class="flex h-16 items-center gap-x-4 bg-white px-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-6 lg:px-8"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START) }}

        @if (filament()->hasNavigation())
            <x-filament::icon-button
                color="gray"
                icon="heroicon-o-bars-3"
                icon-alias="panels::topbar.open-sidebar-button"
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.open()"
                x-show="! $store.sidebar.isOpen"
                @class([
                    'fi-topbar-open-sidebar-btn',
                    'lg:hidden' => (! filament()->isSidebarFullyCollapsibleOnDesktop()) || filament()->isSidebarCollapsibleOnDesktop(),
                ])
            />

            <x-filament::icon-button
                color="gray"
                icon="heroicon-o-x-mark"
                icon-alias="panels::topbar.close-sidebar-button"
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.close()"
                x-show="$store.sidebar.isOpen"
                class="fi-topbar-close-sidebar-btn lg:hidden"
            />
        @endif

        @if (filament()->hasTopNavigation() || (! filament()->hasNavigation()))
            {{--
                BESTHOME CRM: brend NOMI (filial nomi bilan) va filiallar
                orasida almashish tugmalari alohida 'panels::topbar.start'
                render hook orqali chiqadi (AdminPanelProvider.php) — shu
                sabab bu yerda faqat LOGOTIP RASMI (brandLogo, matnsiz)
                chiqariladi, aks holda ikkita bir xil nom chiqib qolardi.
            --}}
            <div class="me-3 hidden lg:flex">
                <x-filament-panels::logo />
            </div>

            @if (filament()->hasTenancy() && filament()->hasTenantMenu())
                <x-filament-panels::tenant-menu class="hidden lg:block" />
            @endif

            @if (filament()->hasNavigation())
                <ul class="me-4 hidden items-center gap-x-1 lg:flex bh-topbar-nav">
                    @foreach ($navigation as $group)
                        @if ($groupLabel = $group->getLabel())
                            @php $groupItems = $group->getItems(); @endphp
                            <li class="bh-nav-group" x-data="{ open: false, closeTimer: null }"
                                x-on:mouseenter="clearTimeout(closeTimer); open = true"
                                x-on:mouseleave="closeTimer = setTimeout(() => open = false, 150)"
                                x-on:click.outside="open = false"
                                x-on:keydown.escape.window="open = false"
                            >
                                <button
                                    type="button"
                                    x-on:click="open = ! open"
                                    :aria-expanded="open"
                                    class="bh-nav-group-btn"
                                    :class="{ 'bh-nav-group-btn-active': open }"
                                >
                                    {{ $groupLabel }}
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="bh-nav-chevron" :class="{ 'bh-nav-chevron-open': open }">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    x-cloak
                                    class="bh-nav-panel {{ count($groupItems) > 5 ? 'bh-nav-panel-wide' : '' }}"
                                >
                                    @foreach ($groupItems as $item)
                                        @php $itemIsActive = $item->isActive(); @endphp
                                        <a
                                            href="{{ $item->getUrl() }}"
                                            @if ($item->shouldOpenUrlInNewTab()) target="_blank" @endif
                                            x-on:click="open = false"
                                            class="bh-nav-item {{ $itemIsActive ? 'bh-nav-item-active' : '' }}"
                                        >
                                            @if ($icon = ($itemIsActive ? ($item->getActiveIcon() ?? $item->getIcon()) : $item->getIcon()))
                                                <x-filament::icon :icon="$icon" class="bh-nav-item-icon" />
                                            @endif
                                            <span>{{ $item->getLabel() }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </li>
                        @else
                            @foreach ($group->getItems() as $item)
                                <x-filament-panels::topbar.item
                                    :active="$item->isActive()"
                                    :active-icon="$item->getActiveIcon()"
                                    :badge="$item->getBadge()"
                                    :badge-color="$item->getBadgeColor()"
                                    :badge-tooltip="$item->getBadgeTooltip()"
                                    :icon="$item->getIcon()"
                                    :should-open-url-in-new-tab="$item->shouldOpenUrlInNewTab()"
                                    :url="$item->getUrl()"
                                >
                                    {{ $item->getLabel() }}
                                </x-filament-panels::topbar.item>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            @endif
        @endif

        <div
            @if (filament()->hasTenancy())
                x-persist="topbar.end.tenant-{{ filament()->getTenant()?->getKey() }}"
            @else
                x-persist="topbar.end"
            @endif
            class="ms-auto flex items-center gap-x-4"
        >
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

            @if (filament()->isGlobalSearchEnabled())
                @livewire(Filament\Livewire\GlobalSearch::class)
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications())
                    @livewire(Filament\Livewire\DatabaseNotifications::class, [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                @endif

                <x-filament-panels::user-menu />
            @endif
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_END) }}
    </nav>
</div>
