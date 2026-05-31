<x-filament-panels::page>
<style>
.arx-layout{display:flex;gap:0;align-items:flex-start}
.arx-main{flex:1;min-width:0;transition:all .25s}
.arx-sidebar{width:380px;flex-shrink:0;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;position:sticky;top:80px;max-height:calc(100vh - 100px);overflow-y:auto;animation:slideIn .2s ease}
.dark .arx-sidebar{background:#18181b;border-color:#27272a}
@keyframes slideIn{from{opacity:0;transform:translateX(12px)}to{opacity:1;transform:translateX(0)}}
.arx-wrap{background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden}
.dark .arx-wrap{background:#18181b;border-color:#27272a}
.arx-topbar{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6;gap:12px;flex-wrap:wrap}
.dark .arx-topbar{border-color:#27272a}
.arx-filters{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.arx-filter-btn{padding:6px 16px;border-radius:20px;border:1.5px solid #e5e7eb;background:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;color:#374151;white-space:nowrap}
.dark .arx-filter-btn{background:#27272a;border-color:#3f3f46;color:#d4d4d8}
.arx-filter-btn:hover{border-color:#93c5fd;background:#eff6ff}
.dark .arx-filter-btn:hover{background:#1e3a5f}
.arx-filter-btn.active-cat{background:#16a34a;border-color:#16a34a;color:#fff}
.arx-filter-btn.active-status{background:#6b7280;border-color:#6b7280;color:#fff}
.arx-search{position:relative}
.arx-search input{padding:7px 12px 7px 34px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;width:220px;background:#fff;color:#111827;outline:none;transition:border .15s}
.dark .arx-search input{background:#27272a;border-color:#3f3f46;color:#f3f4f6}
.arx-search input:focus{border-color:#3b82f6}
.arx-search svg{position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af}
.arx-titlebar{display:flex;align-items:center;gap:10px;padding:14px 20px;border-bottom:1px solid #f3f4f6}
.dark .arx-titlebar{border-color:#27272a}
.arx-title{font-size:15px;font-weight:700;color:#111827}
.dark .arx-title{color:#f3f4f6}
.arx-count{font-size:12px;background:#f3f4f6;color:#6b7280;border-radius:20px;padding:2px 10px;font-weight:600}
.dark .arx-count{background:#27272a;color:#a1a1aa}
.arx-table{width:100%;border-collapse:collapse}
.arx-table th{padding:10px 14px;text-align:left;font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #f3f4f6;white-space:nowrap;background:#fafafa;user-select:none}
.dark .arx-table th{border-color:#27272a;background:#1c1c1f;color:#71717a}
.arx-table th.sortable{cursor:pointer}
.arx-table th.sortable:hover{color:#3b82f6;background:#f0f7ff}
.dark .arx-table th.sortable:hover{color:#60a5fa;background:#1e3a5f}
.arx-table th.sort-active{color:#2563eb}
.dark .arx-table th.sort-active{color:#60a5fa}
.sort-icon{display:inline-block;margin-left:4px;font-size:10px;vertical-align:middle}
.arx-table td{padding:12px 14px;font-size:13px;color:#374151;vertical-align:middle;border-bottom:1px solid #f9fafb}
.dark .arx-table td{color:#d4d4d8;border-color:#27272a}
.arx-table tr:last-child td{border-bottom:none}
.arx-table tbody tr{cursor:pointer;transition:background .1s}
.arx-table tbody tr:hover td{background:#f0f7ff}
.dark .arx-table tbody tr:hover td{background:#1e2d3f}
.arx-table tbody tr.row-active td{background:#eff6ff;border-color:#bfdbfe}
.dark .arx-table tbody tr.row-active td{background:#1e3a5f;border-color:#1d4ed8}
.arx-no{font-size:12px;color:#9ca3af;font-weight:600;text-align:center}
.arx-avatar{width:32px;height:32px;border-radius:50%;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0}
.arx-owner{display:flex;align-items:center;gap:9px}
.arx-owner-name{font-weight:600;font-size:13px;color:#111827}
.dark .arx-owner-name{color:#f3f4f6}
.arx-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;white-space:nowrap}
.arx-price{font-weight:700;font-size:13px;color:#111827;white-space:nowrap}
.dark .arx-price{color:#f3f4f6}
.arx-discount{font-size:11px;color:#16a34a;font-weight:600;margin-top:2px}
.arx-admin-name{font-weight:600;font-size:13px;color:#111827}
.dark .arx-admin-name{color:#f3f4f6}
.arx-admin-email{font-size:11px;color:#9ca3af;margin-top:1px}
.arx-date{font-size:12px;color:#6b7280;white-space:nowrap}
.arx-service-tag{font-size:12px;color:#374151;margin-bottom:2px;white-space:nowrap}
.dark .arx-service-tag{color:#d4d4d8}
.arx-phone{font-size:12px;color:#374151;white-space:nowrap;margin-bottom:1px}
.dark .arx-phone{color:#d4d4d8}
.arx-empty{padding:64px 20px;text-align:center;color:#9ca3af;font-size:13px}
.arx-pagination{padding:16px 20px;border-top:1px solid #f3f4f6}
.dark .arx-pagination{border-color:#27272a}
/* Sidebar styles */
.sb-header{padding:16px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:#fff;z-index:2}
.dark .sb-header{border-color:#27272a;background:#18181b}
.sb-title{font-size:15px;font-weight:700;color:#111827}
.dark .sb-title{color:#f3f4f6}
.sb-close{width:28px;height:28px;border-radius:6px;border:1px solid #e5e7eb;background:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;transition:all .1s}
.sb-close:hover{background:#f3f4f6;color:#111827}
.dark .sb-close{border-color:#3f3f46;color:#a1a1aa}
.dark .sb-close:hover{background:#27272a}
.sb-body{padding:16px 18px;display:flex;flex-direction:column;gap:16px}
.sb-section{font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.sb-row{display:flex;justify-content:space-between;align-items:flex-start;padding:6px 0;border-bottom:1px solid #f9fafb;font-size:13px}
.dark .sb-row{border-color:#27272a}
.sb-label{color:#6b7280}
.sb-value{font-weight:600;color:#111827;text-align:right;max-width:60%}
.dark .sb-value{color:#f3f4f6}
.sb-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
.sb-chip{display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;background:#f3f4f6;color:#374151;margin:2px}
.dark .sb-chip{background:#27272a;color:#d4d4d8}
.sb-progress-bar{height:6px;border-radius:3px;background:#e5e7eb;overflow:hidden;margin-top:6px}
.dark .sb-progress-bar{background:#3f3f46}
.sb-progress-fill{height:100%;border-radius:3px;transition:width .3s}
.sb-pay-stat{display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-top:4px}
.sb-phone-link{display:flex;align-items:center;gap:6px;padding:7px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#374151;font-size:13px;font-weight:500;transition:background .1s}
.sb-phone-link:hover{background:#eff6ff;border-color:#bfdbfe}
.dark .sb-phone-link{background:#1c1c1f;border-color:#27272a;color:#d4d4d8}
.dark .sb-phone-link:hover{background:#1e3a5f}
.sb-file-link{display:flex;align-items:center;gap:8px;padding:7px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;text-decoration:none;color:#374151;font-size:12px}
.dark .sb-file-link{background:#1c1c1f;border-color:#27272a;color:#d4d4d8}
.sb-file-link:hover{background:#eff6ff;border-color:#bfdbfe}
@media(max-width:900px){
.arx-layout{flex-direction:column}
.arx-sidebar{width:100%;position:static;max-height:none}
}
</style>

@php
$serviceLabels = \App\Filament\Pages\ArxivPage::SERVICE_LABELS;
$catLabel = $filterCategory ? ($categoryOptions[$filterCategory] ?? '') : '';

// Sort icon helper
$sortIcon = function(string $field) use ($sortField, $sortDir): string {
    if ($sortField !== $field) return '<span class="sort-icon" style="opacity:.3">⇅</span>';
    return '<span class="sort-icon">' . ($sortDir === 'asc' ? '↑' : '↓') . '</span>';
};

// Archive-only status badge colors
$archiveBadgeColors = [
    'tugallangan'    => ['background:#f3f4f6','color:#374151'],
    'taqdim_etilgan' => ['background:#e0f2fe','color:#0369a1'],
    'bekor_qilingan' => ['background:#fee2e2','color:#dc2626'],
];
$getBadgeStyle = function(string $status) use ($archiveBadgeColors): string {
    $c = $archiveBadgeColors[$status] ?? ['background:#f3f4f6','color:#374151'];
    return implode(';', $c);
};
@endphp

<div class="arx-layout" style="gap:16px">

    {{-- Main table panel --}}
    <div class="arx-main">
    <div class="arx-wrap">

        {{-- Top bar: filters + search --}}
        <div class="arx-topbar">
            <div class="arx-filters">
                {{-- Category filters --}}
                @foreach($categoryOptions as $key => $label)
                <button wire:click="setCategory('{{ $key }}')"
                        class="arx-filter-btn {{ $filterCategory === $key ? 'active-cat' : '' }}">
                    {{ $label }}
                </button>
                @endforeach

                <div style="width:1px;height:20px;background:#e5e7eb;margin:0 4px"></div>

                {{-- Archive status filters only --}}
                @foreach($statusOptions as $key => $label)
                <button wire:click="setStatus('{{ $key }}')"
                        class="arx-filter-btn {{ $filterStatus === $key ? 'active-status' : '' }}"
                        style="{{ $filterStatus === $key ? $getBadgeStyle($key).';border-color:transparent' : '' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="arx-search">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Qidirish...">
            </div>
        </div>

        {{-- Title bar --}}
        <div class="arx-titlebar">
            <div style="width:10px;height:10px;border-radius:50%;background:#6b7280;flex-shrink:0"></div>
            <span class="arx-title">
                Arxivlangan{{ $catLabel ? ' '.mb_strtolower($catLabel) : '' }} loyihalar
            </span>
            <span class="arx-count">{{ number_format($total, 0, '.', ' ') }} ta loyiha</span>
            <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
                @if(!empty($checkedIds))
                <span style="font-size:12px;color:#6b7280">{{ count($checkedIds) }} ta tanlandi</span>
                <button wire:click="clearChecked"
                        style="font-size:11px;color:#9ca3af;background:none;border:none;cursor:pointer">Tozalash</button>
                @endif
            </div>
        </div>

        {{-- Backup tugmalari --}}
        @if(auth()->user()?->isAdmin())
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:#f8fafc;border-bottom:1px solid #e5e7eb;flex-wrap:wrap">
            <div class="dark" style="font-size:12px;font-weight:600;color:#6b7280">📦 Zaxira:</div>
            <button wire:click="exportSelected" wire:loading.attr="disabled"
                    style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Tanlanganlarni yuklab olish
            </button>
            <button wire:click="exportAll" wire:loading.attr="disabled"
                    style="display:inline-flex;align-items:center;gap:6px;background:#059669;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Butun arxivni yuklab olish
            </button>
            <button wire:click="openImportModal"
                    style="display:inline-flex;align-items:center;gap:6px;background:#7c3aed;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Tiklash (import)
            </button>
            <span wire:loading style="font-size:12px;color:#6b7280">Tayyorlanmoqda...</span>
        </div>
        @endif

        {{-- Table --}}
        <div style="overflow-x:auto">
            <table class="arx-table">
                <thead>
                    <tr>
                        <th style="width:36px;text-align:center">
                            <input type="checkbox"
                                   @change="$wire.call('selectAllVisible', {{ json_encode($projects->pluck('id')->toArray()) }})"
                                   style="cursor:pointer">
                        </th>
                        <th style="width:46px;text-align:center">No</th>
                        <th wire:click="sortBy('owner_name')"
                            class="sortable {{ $sortField==='owner_name'?'sort-active':'' }}">
                            Egasi {!! $sortIcon('owner_name') !!}
                        </th>
                        <th wire:click="sortBy('status')"
                            class="sortable {{ $sortField==='status'?'sort-active':'' }}">
                            Bosqich {!! $sortIcon('status') !!}
                        </th>
                        <th wire:click="sortBy('total_price')"
                            class="sortable {{ $sortField==='total_price'?'sort-active':'' }}">
                            Narx {!! $sortIcon('total_price') !!}
                        </th>
                        <th>Admin</th>
                        <th wire:click="sortBy('updated_at')"
                            class="sortable {{ $sortField==='updated_at'?'sort-active':'' }}">
                            Sana {!! $sortIcon('updated_at') !!}
                        </th>
                        <th>Xizmatlar</th>
                        <th>Telefonlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $i => $project)
                    @php
                        $origPrice  = $project->services->sum('price');
                        $finalPrice = (float) $project->total_price;
                        $discountPct = ($origPrice > 0 && $origPrice > $finalPrice)
                            ? round(($origPrice - $finalPrice) / $origPrice * 100, 2)
                            : 0;
                        $isSelected = $selectedId === $project->id;
                    @endphp
                    <tr wire:click="selectProject({{ $project->id }})"
                        class="{{ $isSelected ? 'row-active' : '' }}">
                        <td style="text-align:center;padding:8px" onclick="event.stopPropagation()">
                            <input type="checkbox"
                                   wire:click.stop="toggleCheck({{ $project->id }})"
                                   @checked(in_array($project->id, $checkedIds))
                                   style="width:15px;height:15px;cursor:pointer">
                        </td>
                        <td class="arx-no">{{ $projects->firstItem() + $i }}</td>

                        {{-- Egasi --}}
                        <td>
                            <div class="arx-owner">
                                <div class="arx-avatar">{{ mb_strtoupper(mb_substr($project->owner_name, 0, 1)) }}</div>
                                <div>
                                    <div class="arx-owner-name">{{ $project->owner_name }}</div>
                                    @if($project->number)
                                    <div style="font-size:11px;color:#9ca3af;font-family:monospace">{{ $project->number }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Bosqich --}}
                        <td>
                            <span class="arx-badge" style="{{ $getBadgeStyle($project->status) }}">
                                {{ $statusOptions[$project->status] ?? $project->status }}
                            </span>
                        </td>

                        {{-- Narx --}}
                        <td>
                            <div class="arx-price">{{ number_format($finalPrice, 0, '.', ' ') }} so'm</div>
                            @if($discountPct > 0)
                            <div class="arx-discount">{{ $discountPct }}% chegirma</div>
                            @endif
                        </td>

                        {{-- Hodimlar --}}
                        <td>
                            @if($project->assignedUsers->count())
                            <div class="arx-admin-name">{{ $project->assignedUsers->pluck('name')->join(', ') }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:12px">—</span>
                            @endif
                        </td>

                        {{-- Sana --}}
                        <td>
                            <div class="arx-date">{{ $project->updated_at->translatedFormat('d-M, H:i') }}</div>
                        </td>

                        {{-- Xizmatlar --}}
                        <td>
                            @forelse($project->services as $svc)
                            <div class="arx-service-tag">{{ $serviceLabels[$svc->service_name] ?? $svc->service_name }}</div>
                            @empty
                            <span style="color:#d1d5db;font-size:12px">—</span>
                            @endforelse
                        </td>

                        {{-- Telefonlar --}}
                        <td>
                            @foreach((array)$project->phones as $ph)
                            <div class="arx-phone">{{ $ph['phone'] ?? $ph }}</div>
                            @endforeach
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="arx-empty">
                            <div style="font-size:32px;margin-bottom:8px">📁</div>
                            Arxivda loyihalar yo'q
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
        <div class="arx-pagination">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
    </div>

    {{-- Sidebar detail panel --}}
    @if($selectedProject)
    @php
        $sp       = $selectedProject;
        $spPaid   = (float) $sp->paid_amount;
        $spTotal  = (float) $sp->total_price;
        $spPct    = $spTotal > 0 ? min(100, round($spPaid / $spTotal * 100)) : 0;
        $spColor  = $spPct >= 100 ? '#16a34a' : ($spPct >= 50 ? '#f59e0b' : '#ef4444');
        $spDebt   = max(0, $spTotal - $spPaid);
    @endphp
    <div class="arx-sidebar">
        {{-- Header --}}
        <div class="sb-header">
            <div>
                <div class="sb-title">{{ $sp->owner_name }}</div>
                @if($sp->number)
                <div style="font-size:11px;color:#9ca3af;font-family:monospace;margin-top:2px">{{ $sp->number }}</div>
                @endif
            </div>
            <button class="sb-close" wire:click="selectProject(null)">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="sb-body">

            {{-- Status badge --}}
            <div>
                <div class="sb-section">Holat</div>
                <span class="sb-badge" style="{{ $getBadgeStyle($sp->status) }}">
                    {{ $statusOptions[$sp->status] ?? $sp->status }}
                </span>
                @if($sp->category)
                <span class="sb-badge" style="background:#f0fdf4;color:#16a34a;margin-left:6px">
                    {{ $categoryOptions[$sp->category] ?? $sp->category }}
                </span>
                @endif
            </div>

            {{-- Basic info --}}
            <div>
                <div class="sb-section">Ma'lumot</div>
                @if($sp->address)
                <div class="sb-row">
                    <span class="sb-label">Manzil</span>
                    <span class="sb-value">{{ $sp->address }}</span>
                </div>
                @endif
                @if($sp->assignedUsers->count())
                <div class="sb-row">
                    <span class="sb-label">Hodimlar</span>
                    <span class="sb-value">{{ $sp->assignedUsers->pluck('name')->join(', ') }}</span>
                </div>
                @endif
                <div class="sb-row">
                    <span class="sb-label">Yaratilgan</span>
                    <span class="sb-value">{{ $sp->created_at->format('d.m.Y') }}</span>
                </div>
                <div class="sb-row">
                    <span class="sb-label">O'zgartirilgan</span>
                    <span class="sb-value">{{ $sp->updated_at->format('d.m.Y') }}</span>
                </div>
            </div>

            {{-- Payment progress --}}
            <div>
                <div class="sb-section">To'lov</div>
                <div class="sb-row" style="border:none;padding-bottom:0">
                    <span class="sb-label">Jami narx</span>
                    <span class="sb-value">{{ number_format($spTotal, 0, '.', ' ') }} so'm</span>
                </div>
                <div class="sb-row" style="border:none;padding-top:2px;padding-bottom:0">
                    <span class="sb-label">To'langan</span>
                    <span class="sb-value" style="color:#16a34a">{{ number_format($spPaid, 0, '.', ' ') }} so'm</span>
                </div>
                @if($spDebt > 0)
                <div class="sb-row" style="padding-top:2px">
                    <span class="sb-label">Qoldiq</span>
                    <span class="sb-value" style="color:#ef4444">{{ number_format($spDebt, 0, '.', ' ') }} so'm</span>
                </div>
                @endif
                <div class="sb-progress-bar">
                    <div class="sb-progress-fill" style="width:{{ $spPct }}%;background:{{ $spColor }}"></div>
                </div>
                <div class="sb-pay-stat">
                    <span>{{ $spPct }}% to'langan</span>
                    @if($sp->payments->count())
                    <span>{{ $sp->payments->count() }} ta to'lov</span>
                    @endif
                </div>
            </div>

            {{-- Services --}}
            @if($sp->services->count())
            <div>
                <div class="sb-section">Xizmatlar</div>
                <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px">
                    @foreach($sp->services as $svc)
                    <span class="sb-chip">{{ $serviceLabels[$svc->service_name] ?? $svc->service_name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Phone numbers --}}
            @if(!empty($sp->phones))
            <div>
                <div class="sb-section">Telefonlar</div>
                <div style="display:flex;flex-direction:column;gap:6px;margin-top:4px">
                    @foreach((array)$sp->phones as $ph)
                    @php $num = $ph['phone'] ?? $ph; @endphp
                    <a href="tel:{{ $num }}" class="sb-phone-link">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.54a16 16 0 0 0 6 6l.91-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        {{ $num }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Files --}}
            @if($sp->files->count())
            <div>
                <div class="sb-section">Fayllar ({{ $sp->files->count() }} ta)</div>
                <div style="display:flex;flex-direction:column;gap:5px;margin-top:4px">
                    @foreach($sp->files->take(6) as $file)
                    @php
                        $furl = asset('storage/' . $file->file_path);
                        $ext  = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                        $ficon = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? '🖼️'
                               : ($ext === 'pdf' ? '📄'
                               : (in_array($ext, ['doc','docx']) ? '📝'
                               : (in_array($ext, ['xls','xlsx']) ? '📊' : '📎')));
                        $fsize = $file->file_size ? round($file->file_size / 1024) . ' KB' : '';
                        $ftarget = in_array($ext, ['pdf','jpg','jpeg','png','gif','webp']) ? '_blank' : '_self';
                        $fdl = in_array($ext, ['doc','docx','xls','xlsx']) ? 'download' : '';
                    @endphp
                    <a href="{{ $furl }}" target="{{ $ftarget }}" {{ $fdl }} class="sb-file-link">
                        <span>{{ $ficon }}</span>
                        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $file->file_name }}</span>
                        @if($fsize)<span style="color:#9ca3af;font-size:11px;flex-shrink:0">{{ $fsize }}</span>@endif
                    </a>
                    @endforeach
                    @if($sp->files->count() > 6)
                    <div style="font-size:12px;color:#9ca3af;text-align:center;padding-top:4px">+{{ $sp->files->count() - 6 }} ta fayl</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Description --}}
            @if($sp->description)
            <div>
                <div class="sb-section">Qo'shimcha</div>
                <div style="font-size:13px;color:#374151;line-height:1.5;background:#f9fafb;padding:10px;border-radius:8px;border:1px solid #f3f4f6">
                    {{ $sp->description }}
                </div>
            </div>
            @endif

            {{-- Edit link --}}
            <div style="padding-bottom:4px">
                <a href="{{ route('filament.admin.resources.projects.edit', $sp) }}"
                   style="display:block;text-align:center;padding:9px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;color:#2563eb;font-size:13px;font-weight:600;text-decoration:none;transition:background .1s"
                   onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                    Loyihani tahrirlash →
                </a>
            </div>

        </div>
    </div>
    @endif

</div>
{{-- IMPORT MODAL --}}
@if($showImportModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px">
<div style="background:#fff;border-radius:16px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3)">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #e5e7eb">
        <h3 style="font-size:16px;font-weight:800;color:#111827">📦 Arxivni tiklash (Import)</h3>
        <button wire:click="closeImportModal" style="background:none;border:none;cursor:pointer;font-size:22px;color:#9ca3af">×</button>
    </div>

    <div style="padding:20px;display:flex;flex-direction:column;gap:16px">

        {{-- Fayl yuklash --}}
        <div>
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px">
                ZIP faylni tanlang (avval yuklagan zaxira fayl):
            </label>
            <input type="file" wire:model="backupFile" accept=".zip"
                   style="width:100%;border:2px dashed #d1d5db;border-radius:8px;padding:10px;font-size:13px;cursor:pointer">
            <div wire:loading wire:target="backupFile" style="font-size:12px;color:#6b7280;margin-top:4px">Yuklanmoqda...</div>
        </div>

        {{-- Konflikt strategiya --}}
        <div>
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px">
                Mavjud loyiha bo'lsa:
            </label>
            <div style="display:flex;gap:12px">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">
                    <input type="radio" wire:model="importConflict" value="skip"> O'tkazib yuborish
                </label>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">
                    <input type="radio" wire:model="importConflict" value="overwrite"> Ustiga yozish
                </label>
            </div>
        </div>

        {{-- Preview tugmasi --}}
        <button wire:click="previewImport" wire:loading.attr="disabled"
                style="background:#f3f4f6;color:#374151;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer">
            Ko'rib chiqish (preview)
        </button>

        {{-- Preview natija --}}
        @if(!empty($importPreview))
        <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;max-height:250px;overflow-y:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:7px 10px;text-align:left;font-weight:600;color:#6b7280">Raqam</th>
                        <th style="padding:7px 10px;text-align:left;font-weight:600;color:#6b7280">Egasi</th>
                        <th style="padding:7px 10px;text-align:center;font-weight:600;color:#6b7280">Fayllar</th>
                        <th style="padding:7px 10px;text-align:center;font-weight:600;color:#6b7280">Holat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($importPreview as $row)
                    <tr style="border-top:1px solid #f1f5f9">
                        <td style="padding:6px 10px;font-family:monospace;font-size:11px">{{ $row['number'] }}</td>
                        <td style="padding:6px 10px;font-weight:600">{{ $row['owner_name'] }}</td>
                        <td style="padding:6px 10px;text-align:center;color:#6b7280">{{ $row['files'] }}</td>
                        <td style="padding:6px 10px;text-align:center">
                            @if($row['exists'])
                            <span style="background:#fef3c7;color:#d97706;font-size:10px;font-weight:700;border-radius:4px;padding:2px 7px">Mavjud</span>
                            @else
                            <span style="background:#dcfce7;color:#16a34a;font-size:10px;font-weight:700;border-radius:4px;padding:2px 7px">Yangi</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Import tugmasi --}}
        <button wire:click="runImport" wire:loading.attr="disabled"
                wire:confirm="Importni boshlashni tasdiqlaysizmi? Bu amalni qaytarib bo'lmaydi."
                style="background:#7c3aed;color:#fff;border:none;border-radius:8px;padding:10px;font-size:13px;font-weight:700;cursor:pointer;width:100%">
            ✅ Importni boshlash
        </button>
        @endif

        {{-- Import natija --}}
        @if($importResult)
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px;font-size:13px;color:#15803d;font-weight:600">
            {{ $importResult }}
        </div>
        @endif

    </div>
</div>
</div>
@endif

</x-filament-panels::page>
