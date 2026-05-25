<x-filament-panels::page>

<style>
/* ===== KANBAN ===== */
.kanban-wrap{display:flex;gap:14px;overflow-x:auto;padding-bottom:16px;align-items:flex-start;min-height:200px}
.kanban-col{min-width:280px;max-width:280px;flex-shrink:0}
.col-head{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:8px 8px 0 0;color:#fff;font-weight:700;font-size:13px}
.col-count{background:rgba(255,255,255,.3);border-radius:12px;padding:1px 8px;font-size:11px}
.col-body{background:#f3f4f6;border-radius:0 0 8px 8px;min-height:80px;padding:8px;display:flex;flex-direction:column;gap:8px;transition:background .15s}
.dark .col-body{background:#1f2937}
.col-body.drag-over{background:#dbeafe;outline:2px dashed #3b82f6;outline-offset:-4px}
/* Card */
.p-card{background:#fff;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);cursor:grab;border:2px solid transparent;transition:border-color .15s,box-shadow .15s,opacity .15s}
.dark .p-card{background:#111827}
.p-card:hover{border-color:#3b82f6;box-shadow:0 4px 12px rgba(0,0,0,.12)}
.p-card.dragging{opacity:.4;cursor:grabbing}
/* Move button */
.p-move-btn{position:relative;display:inline-flex;align-items:center;gap:4px;font-size:10px;padding:3px 8px;border-radius:6px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;cursor:pointer;white-space:nowrap}
.p-move-btn:hover{background:#eff6ff;border-color:#93c5fd;color:#2563eb}
.p-move-dropdown{position:absolute;bottom:calc(100% + 4px);left:0;z-index:200;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);min-width:180px;padding:4px}
.p-move-item{display:block;width:100%;text-align:left;padding:6px 10px;font-size:11px;font-weight:500;border-radius:6px;border:none;background:none;cursor:pointer;color:#374151}
.p-move-item:hover{background:#f3f4f6}
.p-num{font-size:11px;color:#6b7280;font-family:monospace}
.p-owner{font-weight:600;font-size:13px;margin:4px 0 2px;color:#111827}
.dark .p-owner{color:#f9fafb}
.p-addr{font-size:11px;color:#6b7280;margin-bottom:6px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.p-services{display:flex;flex-wrap:wrap;gap:3px;margin-bottom:6px}
.p-srv-tag{background:#fef2f2;color:#dc2626;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:500}
.dark .p-srv-tag{background:#7f1d1d;color:#fca5a5}
.p-phone{font-size:11px;color:#6b7280;margin-bottom:6px;display:flex;align-items:center;gap:4px}
.p-money{font-size:12px;margin-bottom:3px}
.p-money-total{color:#2563eb;font-weight:600}
.p-money-paid{color:#6b7280;font-size:11px}
.p-bar-wrap{background:#e5e7eb;border-radius:4px;height:4px;margin-bottom:6px;overflow:hidden}
.dark .p-bar-wrap{background:#374151}
.p-bar{height:4px;border-radius:4px}
.p-footer{display:flex;justify-content:space-between;align-items:center;font-size:10px;color:#9ca3af;margin-top:4px}
/* Top bar */
.kb-topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.kb-title{font-size:18px;font-weight:800;color:#111827}
.dark .kb-title{color:#f9fafb}
.kb-stats{display:flex;gap:8px}
.kb-stat{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:6px 12px;text-align:center}
.dark .kb-stat{background:#1f2937;border-color:#374151}
.kb-stat-num{font-size:16px;font-weight:800;color:#2563eb}
.kb-stat-lbl{font-size:10px;color:#6b7280}
.btn-new{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 16px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s}
.btn-new:hover{background:#1d4ed8}

/* ===== MODAL ===== */
.kb-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;display:flex;align-items:center;justify-content:center;padding:12px}
.kb-modal{background:#fff;border-radius:16px;width:100%;max-width:960px;max-height:94vh;display:flex;flex-direction:column;box-shadow:0 25px 80px rgba(0,0,0,.25);overflow:hidden}
.dark .kb-modal{background:#18181b}
/* Header */
.kb-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb;flex-shrink:0}
.dark .kb-head{border-color:#27272a}
.kb-head h3{font-size:16px;font-weight:700;color:#111827}
.dark .kb-head h3{color:#f4f4f5}
.kb-close{background:none;border:none;cursor:pointer;color:#6b7280;padding:6px;border-radius:6px;line-height:1;font-size:18px;transition:background .15s}
.kb-close:hover{background:#f3f4f6}
/* Steps */
.kb-steps{display:flex;align-items:center;padding:10px 20px;border-bottom:1px solid #e5e7eb;gap:6px;flex-shrink:0;background:#fafafa}
.dark .kb-steps{border-color:#27272a;background:#09090b}
.kb-step-pill{display:flex;align-items:center;gap:6px;padding:5px 14px;border-radius:99px;font-size:13px;font-weight:500;color:#9ca3af;background:transparent}
.kb-step-pill.active{background:#2563eb;color:#fff}
.kb-step-pill.done{background:#dcfce7;color:#16a34a}
.kb-step-line{flex:1;height:1px;background:#e5e7eb}
.dark .kb-step-line{background:#27272a}
/* Body */
.kb-body{flex:1;overflow-y:auto;padding:20px}
.kb-split{display:flex;gap:20px}
.kb-left{flex:1;display:flex;flex-direction:column;gap:14px;min-width:0}
.kb-right{width:380px;flex-shrink:0;display:flex;flex-direction:column;gap:10px}
/* Form elements */
.kb-label{font-size:13px;font-weight:500;color:#374151;margin-bottom:5px;display:block}
.dark .kb-label{color:#d1d5db}
.kb-input{width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;transition:border .15s,box-shadow .15s;background:#fff;color:#111827;box-sizing:border-box}
.dark .kb-input{background:#09090b;border-color:#3f3f46;color:#f4f4f5}
.kb-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.kb-input.error{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1)}
.kb-textarea{resize:vertical;min-height:70px}
select.kb-input{-webkit-appearance:none;-moz-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:34px}
/* Phone row */
.kb-phone-row{display:flex;gap:0;align-items:stretch;margin-bottom:6px}
.kb-phone-input{flex:1;border-radius:8px 0 0 8px;border-right:0}
.kb-phone-add{background:#f3f4f6;border:1px solid #e2e8f0;border-left:none;border-radius:0 8px 8px 0;padding:0 12px;font-size:18px;color:#2563eb;cursor:pointer;line-height:1;font-weight:400}
.dark .kb-phone-add{background:#27272a;border-color:#3f3f46;color:#60a5fa}
.kb-phone-del{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:0 10px;font-size:13px;color:#ef4444;cursor:pointer;margin-left:6px}
/* Address confirmed badge */
.addr-confirmed{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;margin-top:6px}
.dark .addr-confirmed{background:#052e16;border-color:#166534}
.addr-confirmed-txt{font-size:12px;color:#16a34a;display:flex;align-items:center;gap:6px;font-weight:500}
.dark .addr-confirmed-txt{color:#4ade80}
.addr-clear-btn{font-size:12px;color:#dc2626;cursor:pointer;background:none;border:none;padding:0;font-weight:500}
/* Map */
.map-section-label{font-size:12px;font-weight:600;color:#374151;display:flex;align-items:center;gap:5px;margin-bottom:6px}
.dark .map-section-label{color:#d1d5db}
/* Selected location box — blue like reference */
.sel-loc-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px}
.dark .sel-loc-box{background:#1e3a5f;border-color:#1d4ed8}
.sel-loc-title{font-size:13px;font-weight:600;color:#1d4ed8;margin-bottom:4px}
.dark .sel-loc-title{color:#60a5fa}
.sel-loc-addr{font-size:12px;color:#1e40af;margin-bottom:3px;line-height:1.5}
.dark .sel-loc-addr{color:#93c5fd}
.sel-loc-coords{font-size:11px;color:#3b82f6}
/* File upload */
.kb-file-drop{border:2px dashed #d1d5db;border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:all .15s;position:relative}
.kb-file-drop:hover{border-color:#3b82f6;background:#eff6ff}
.dark .kb-file-drop{border-color:#3f3f46}
.dark .kb-file-drop:hover{background:#1e3a5f}
.kb-file-list{margin-top:8px;display:flex;flex-direction:column;gap:4px}
.kb-file-item{display:flex;align-items:center;gap:6px;font-size:11px;background:#f8fafc;border-radius:6px;padding:5px 8px;border:1px solid #e2e8f0}
.dark .kb-file-item{background:#27272a;border-color:#3f3f46}
/* Services */
.srv-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.srv-card{border:2px solid #e5e7eb;border-radius:10px;padding:10px 12px;cursor:pointer;transition:all .15s}
.dark .srv-card{border-color:#3f3f46}
.srv-card:hover{border-color:#93c5fd;background:#eff6ff}
.dark .srv-card:hover{background:#1e3a5f}
.srv-card.selected{border-color:#2563eb;background:#eff6ff}
.dark .srv-card.selected{background:#1e3a5f;border-color:#3b82f6}
.srv-name{font-size:13px;font-weight:600;color:#374151}
.dark .srv-name{color:#e5e7eb}
/* Tier tabs */
.tier-tabs{display:flex;gap:0;overflow-x:auto;border-bottom:1px solid #e5e7eb;margin-bottom:10px;scrollbar-width:none}
.tier-tab{padding:6px 14px;font-size:12px;font-weight:500;cursor:pointer;white-space:nowrap;border-bottom:2px solid transparent;color:#6b7280;background:none;border-top:none;border-left:none;border-right:none;transition:all .15s}
.tier-tab.active{color:#2563eb;border-bottom-color:#2563eb;font-weight:600}
.tier-tab:hover:not(.active){color:#374151;background:#f3f4f6}
/* Tier radio options */
.tier-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.tier-item{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;cursor:pointer;transition:all .15s}
.tier-item:hover{border-color:#93c5fd;background:#f0f9ff}
.tier-item.selected{border-color:#16a34a;background:#f0fdf4}
.tier-radio{width:16px;height:16px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;display:flex;align-items:center;justify-content:center;margin-right:8px}
.tier-item.selected .tier-radio{border-color:#16a34a;background:#16a34a}
.tier-label{font-size:11px;color:#374151;flex:1;line-height:1.3}
.tier-price{font-size:11px;font-weight:700;color:#111827;white-space:nowrap;margin-left:6px}
/* Footer */
.kb-footer{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #e5e7eb;flex-shrink:0}
.dark .kb-footer{border-color:#27272a}
.btn-back{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer}
.dark .btn-back{background:#27272a;color:#f4f4f5;border-color:#3f3f46}
.btn-next{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:600;cursor:pointer}
.btn-next:hover{background:#1d4ed8}
.btn-save{background:#16a34a;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:600;cursor:pointer}
.btn-save:hover{background:#15803d}
/* Confirm */
.confirm-section{background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:12px}
.dark .confirm-section{background:#09090b}
.confirm-title{font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px}
.confirm-row{display:flex;gap:12px;margin-bottom:6px;font-size:13px}
.confirm-key{color:#6b7280;min-width:110px;flex-shrink:0}
.confirm-val{color:#111827;font-weight:500}
.dark .confirm-val{color:#f4f4f5}
/* Map type buttons */
.kb-maptype{border:1px solid #e2e8f0;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:500;cursor:pointer;background:#f3f4f6;color:#374151;transition:all .15s}
.kb-maptype:hover{background:#e5e7eb}
.kb-maptype.active{background:#111827;color:#fff;border-color:#111827}
.dark .kb-maptype{background:#27272a;color:#e5e7eb;border-color:#3f3f46}
.dark .kb-maptype.active{background:#f4f4f5;color:#09090b}
/* Notify */
.kb-notify{position:fixed;bottom:24px;right:24px;background:#16a34a;color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 14px rgba(0,0,0,.2);animation:slideIn .3s ease}
@keyframes slideIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
[x-cloak]{display:none!important}
</style>

{{-- TOP BAR --}}
<div class="kb-topbar">
    <div class="kb-title">BESTHOME CRM</div>
    <div class="kb-stats">
        <div class="kb-stat">
            <div class="kb-stat-num">{{ $projects->flatten()->count() }}</div>
            <div class="kb-stat-lbl">Jami</div>
        </div>
        <div class="kb-stat">
            <div class="kb-stat-num" style="color:#f59e0b">{{ $projects->get('yangi', collect())->count() }}</div>
            <div class="kb-stat-lbl">Yangi</div>
        </div>
        <div class="kb-stat">
            <div class="kb-stat-num" style="color:#10b981">{{ $projects->get('tugallangan', collect())->count() }}</div>
            <div class="kb-stat-lbl">Tugallangan</div>
        </div>
    </div>
    <button class="btn-new" wire:click="openModal">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Yangi loyiha
    </button>
</div>

{{-- KANBAN --}}
<div class="kanban-wrap">
@foreach($statuses as $statusKey => $status)
<div class="kanban-col">
    <div class="col-head" style="background:{{ $status['color'] }}">
        <span>{{ $status['label'] }}</span>
        <span class="col-count">{{ $projects->get($statusKey, collect())->count() }}</span>
    </div>
    <div class="col-body"
         id="col-{{ $statusKey }}"
         ondragover="kbDragOver(event)"
         ondragleave="kbDragLeave(event)"
         ondrop="kbDrop(event,'{{ $statusKey }}')">
        @forelse($projects->get($statusKey, collect()) as $project)
        <div class="p-card"
             draggable="true"
             data-id="{{ $project->id }}"
             ondragstart="kbDragStart(event,{{ $project->id }})"
             ondragend="kbDragEnd(event)"
             onclick="if(!window._kbDragged)window.location='/admin/projects/{{ $project->id }}/edit'"
             >
            @php
                $daysLeft = $project->deadline_days_left;
                $isOverdue = $daysLeft !== null && $daysLeft < 0;
                $currentLog = $project->currentStatusLog;
                $daysInStatus = $currentLog ? (int)$currentLog->entered_at->diffInDays(now()) : 0;
                $allocDays = $currentLog?->allocated_days ?? 0;
                $statusDelay = ($allocDays > 0) ? max(0, $daysInStatus - $allocDays) : 0;
            @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                <span class="p-num"># {{ substr($project->number, 1) }}</span>
                <div style="display:flex;align-items:center;gap:4px">
                    @if($daysLeft !== null)
                        @if($isOverdue)
                        <span style="font-size:9px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px">
                            {{ abs($daysLeft) }} kun kechikdi
                        </span>
                        @elseif($daysLeft === 0)
                        <span style="font-size:9px;font-weight:700;background:#fef3c7;color:#d97706;border-radius:4px;padding:1px 5px">
                            Bugun tugaydi
                        </span>
                        @elseif($daysLeft <= 3)
                        <span style="font-size:9px;font-weight:700;background:#fef3c7;color:#d97706;border-radius:4px;padding:1px 5px">
                            {{ $daysLeft }} kun qoldi
                        </span>
                        @else
                        <span style="font-size:9px;color:#6b7280;background:#f3f4f6;border-radius:4px;padding:1px 5px">
                            {{ $daysLeft }} kun qoldi
                        </span>
                        @endif
                    @endif
                    <span style="font-size:10px;color:#9ca3af">{{ $project->created_at->format('d-M') }}</span>
                </div>
            </div>
            @if($statusDelay > 0)
            <div style="font-size:9px;background:#fee2e2;color:#dc2626;border-radius:4px;padding:2px 6px;margin-bottom:4px;font-weight:600">
                Bu bosqichda {{ $daysInStatus }} kun ({{ $statusDelay }} kun kechikdi)
            </div>
            @elseif($allocDays > 0)
            <div style="font-size:9px;color:#6b7280;background:#f3f4f6;border-radius:4px;padding:2px 6px;margin-bottom:4px">
                Bu bosqichda {{ $daysInStatus }}/{{ $allocDays }} kun
            </div>
            @endif
            <div class="p-owner">{{ $project->owner_name }}</div>
            <div class="p-addr">
                <svg width="11" height="11" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:2px"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                {{ $project->address }}
            </div>
            @if($project->services->count())
            <div class="p-services">
                @foreach($project->services->take(4) as $srv)
                <span class="p-srv-tag">{{ $serviceOptions[$srv->service_name] ?? $srv->service_name }}</span>
                @endforeach
                @if($project->services->count() > 4)
                <span class="p-srv-tag">+{{ $project->services->count() - 4 }}</span>
                @endif
            </div>
            @endif
            @if(!empty($project->phones[0]['phone']))
            <div class="p-phone">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11.5 19.79 19.79 0 012 2.84 2 2 0 014 2.68h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 10.18a16 16 0 006.29 6.29l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                {{ $project->phones[0]['phone'] }}
            </div>
            @endif
            <div class="p-money">
                <span class="p-money-total">{{ number_format($project->total_price, 0, '.', ' ') }} so'm</span>
            </div>
            @if($project->total_price > 0)
            <div class="p-bar-wrap">
                <div class="p-bar" style="width:{{ $project->payment_percent }}%;background:{{ $status['color'] }}"></div>
            </div>
            <div style="font-size:10px;color:#9ca3af">
                To'langan: {{ number_format($project->paid_amount, 0, '.', ' ') }} so'm ({{ $project->payment_percent }}%)
            </div>
            @endif
            <div class="p-footer" style="margin-top:6px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:4px">
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                    @if(auth()->user()?->canSeeAllProjects())
                    <div x-data="{ open: false }" style="position:relative" @click.outside="open=false">
                        <button class="p-move-btn" @click.stop="open=!open" ondragstart="event.stopPropagation();event.preventDefault()">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            O'tkazish
                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="p-move-dropdown" x-show="open" x-cloak>
                            @foreach($statuses as $sk => $st)
                            @if($sk !== $statusKey)
                            <button class="p-move-item"
                                    onclick="event.stopPropagation()"
                                    wire:click.stop="moveProject({{ $project->id }},'{{ $sk }}')"
                                    @click="open=false">
                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $st['color'] }};margin-right:6px;vertical-align:middle"></span>
                                {{ $st['label'] }}
                            </button>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if(auth()->user()?->isHisobchi() || auth()->user()?->canSeeAllProjects())
                    <button class="p-move-btn" style="background:#f0fdf4;border-color:#86efac;color:#16a34a"
                            onclick="event.stopPropagation()"
                            wire:click.stop="openPaymentModal({{ $project->id }})">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        To'lov
                    </button>
                    @endif
                    @if(auth()->user()?->canSeeAllProjects())
                    <button class="p-move-btn" style="background:#eff6ff;border-color:#93c5fd;color:#2563eb"
                            onclick="event.stopPropagation()"
                            wire:click.stop="openRouteModal({{ $project->id }},'{{ $statusKey }}')">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                        Yuborish
                    </button>
                    @endif
                </div>
                @if($project->assignedUser)
                <span style="font-size:10px;color:#6b7280;font-weight:500">{{ $project->assignedUser->name }}</span>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:24px 12px;color:#9ca3af;font-size:12px">
            <div style="font-size:22px;margin-bottom:4px">📭</div>
            Loyihalar yo'q
        </div>
        @endforelse
    </div>
</div>
@endforeach
</div>

{{-- MODAL --}}
<div class="kb-overlay" style="display:{{ $showModal ? 'flex' : 'none' }}" @click.self="$wire.closeModal()">
<div class="kb-modal" @click.stop>

    {{-- Header --}}
    <div class="kb-head">
        <h3>Yangi loyiha yaratish</h3>
        <button class="kb-close" wire:click="closeModal">✕</button>
    </div>

    {{-- Steps (pill style like reference) --}}
    <div class="kb-steps">
        <div class="kb-step-pill {{ $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' }}">
            <span>{{ $step > 1 ? '✓' : '1' }}</span>
            <span>Ma'lumotlar</span>
        </div>
        <div class="kb-step-line"></div>
        <div class="kb-step-pill {{ $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' }}">
            <span>{{ $step > 2 ? '✓' : '2' }}</span>
            <span>Xizmatlar</span>
        </div>
        <div class="kb-step-line"></div>
        <div class="kb-step-pill {{ $step >= 3 ? 'active' : '' }}">
            <span>3</span>
            <span>Tasdiqlash</span>
        </div>
    </div>

    {{-- Body --}}
    <div class="kb-body">

    @if($step === 1)
    <div class="kb-split">

        {{-- Chap: Forma --}}
        <div class="kb-left">

            <div>
                <label class="kb-label">Egasining ismi *</label>
                <input wire:model.live="owner_name"
                       class="kb-input {{ $errors->has('owner_name') ? 'error' : '' }}"
                       placeholder="Loyiha egasi ism-familyasini kiriting...">
                @error('owner_name')<div style="color:#ef4444;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="kb-label">Nomi</label>
                <input wire:model.live="proj_title" class="kb-input" placeholder="Loyiha nomini kiriting...">
            </div>

            <div>
                <label class="kb-label">Manzil *</label>
                <input wire:model.live="address"
                       id="kb-address-input"
                       class="kb-input {{ $errors->has('address') ? 'error' : '' }}"
                       placeholder="Loyiha manzilini kiriting...">
                @error('address')<div style="color:#ef4444;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                @if($address)
                <div class="addr-confirmed">
                    <div class="addr-confirmed-txt">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        Xaritadan tanlangan
                    </div>
                    <button class="addr-clear-btn" wire:click="$set('address','')">Tozalash</button>
                </div>
                @endif
            </div>

            <div>
                <label class="kb-label">Telefon raqamlar</label>
                @foreach($phones as $i => $phone)
                <div class="kb-phone-row" style="margin-bottom:6px">
                    <input wire:model.live="phones.{{ $i }}"
                           class="kb-input kb-phone-input"
                           placeholder="+998 XX XXX XX XX">
                    @if($i === 0 && count($phones) < 5)
                    <button class="kb-phone-add" wire:click="addPhone" title="Raqam qo'shish">+</button>
                    @elseif($i > 0)
                    <button class="kb-phone-del" wire:click="removePhone({{ $i }})">✕</button>
                    @endif
                </div>
                @endforeach
            </div>

            <div>
                <label class="kb-label">Qo'shimcha ma'lumot</label>
                <textarea wire:model.live="description" class="kb-input kb-textarea"
                          placeholder="Loyiha haqida batafsil ma'lumot kiriting..."></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="kb-label">Kategoriya</label>
                    <select wire:model.live="category" class="kb-input">
                        @foreach($categoryOptions as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="kb-label">Mas'ul xodim</label>
                    <select wire:model.live="assigned_user_id" class="kb-input">
                        <option value="">— Tanlanmagan —</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="kb-label">Muddat (srok)</label>
                    <input wire:model="deadline_date" type="date" class="kb-input"
                           min="{{ now()->format('Y-m-d') }}"
                           placeholder="Tugash sanasi">
                </div>
            </div>

            {{-- Fayl yuklash --}}
            <div>
                <label class="kb-label">Hujjatlar yuklash</label>
                <div class="kb-file-drop" onclick="document.getElementById('kb-file-input').click()">
                    <svg width="30" height="30" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;display:block"><path d="M12 3v12m5-5l-5-5-5 5"/><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/></svg>
                    <div style="font-size:13px;color:#4b5563;font-weight:500">Fayllarni tanlang yoki bu yerga tashlang</div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:3px">PDF, Word, Excel, rasm fayllari (maks 20MB)</div>
                    <input id="kb-file-input" type="file" wire:model="uploadedFiles"
                           multiple accept=".pdf,.doc,.docx,.xls,.xlsx,image/*"
                           style="display:none">
                </div>
                <div wire:loading wire:target="uploadedFiles" style="font-size:12px;color:#2563eb;margin-top:6px;display:flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    Yuklanmoqda...
                </div>
                @if(count($uploadedFiles) > 0)
                <div class="kb-file-list">
                    @foreach($uploadedFiles as $file)
                    <div class="kb-file-item">
                        <span style="color:#6b7280">📄</span>
                        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#374151">{{ $file->getClientOriginalName() }}</span>
                        <span style="color:#9ca3af;flex-shrink:0">{{ round($file->getSize()/1024) }} KB</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        {{-- O'ng: Xarita --}}
        <div class="kb-right">
            <div class="map-section-label">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                Xaritadan manzil tanlash
            </div>

            <div wire:ignore>
                <div style="display:flex;gap:6px;margin-bottom:6px">
                    <input id="kb-map-search" type="text" placeholder="Ko'cha, mahalla yoki joy nomini kiriting..."
                           style="flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:7px 11px;font-size:13px;outline:none"
                           onkeydown="if(event.key==='Enter'){kbSearchOnMap();event.preventDefault()}">
                    <button onclick="kbSearchOnMap()" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:13px;cursor:pointer;white-space:nowrap">Qidirish</button>
                </div>
                <div id="modal-map" style="width:100%;height:340px;border-radius:10px;border:1px solid #e2e8f0;background:#f3f4f6;overflow:hidden"></div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <button onclick="kbSetMapType('hybrid')" id="kb-btn-hybrid" class="kb-maptype active">Gibrid</button>
                    <button onclick="kbSetMapType('map')" id="kb-btn-map" class="kb-maptype">Xarita</button>
                    <button onclick="kbSetMapType('sat')" id="kb-btn-sat" class="kb-maptype">Sputnik</button>
                </div>
                <div id="selected-location-box" class="sel-loc-box" style="display:none;margin-top:10px">
                    <div class="sel-loc-title">Tanlangan joylashuv</div>
                    <div class="sel-loc-addr" id="selected-location-text"></div>
                    <div class="sel-loc-coords" id="selected-location-coords"></div>
                </div>
            </div>

            <div id="kb-map-hint" style="display:none;font-size:12px;color:#dc2626;text-align:center;padding:4px 0;font-weight:500">
                Ko'chalar ko'rinsin — xaritani kattalashtiring, keyin bosing
            </div>
            <div style="font-size:11px;color:#9ca3af;text-align:center;padding:4px 0">
                Qidiring → yaqinlashtiring → uyga bosing
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 2 --}}
    @if($step === 2)
    @php
        $selectedCount = count(array_filter($services, fn($s) => !empty($s['selected'])));
        $totalPrice = array_sum(array_map(fn($s) => !empty($s['selected']) ? (float)str_replace([' ',','],'',$s['price']??'0') : 0, $services));
    @endphp
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div style="font-size:15px;font-weight:700;color:#111827">Xizmatlarni tanlang</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px">{{ $selectedCount }} ta xizmat tanlandi</div>
            </div>
            @if($selectedCount > 0)
            <div style="background:#2563eb;color:#fff;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:700">
                {{ number_format($totalPrice, 0, '.', ' ') }} so'm
            </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($services as $key => $srv)
            @php
                $sel      = !empty($srv['selected']);
                $hasTiers = !empty($srv['has_tiers']) && isset($priceTiers[$key]);
                $activeSub = $activeSubTab[$key] ?? (isset($priceTiers[$key]) ? array_key_first($priceTiers[$key]) : null);
                $hasSelectedTiers = !empty($srv['selected_tiers']);
            @endphp

            @if($hasTiers)
            {{-- Tier service: accordion open/close via Alpine (no Livewire round trip) --}}
            <div x-data="{ open: {{ $sel || $hasSelectedTiers ? 'true' : 'false' }} }"
                 :style="({{ $sel || $hasSelectedTiers ? 'true' : 'false' }}) ? 'border:1px solid #86efac;background:#f0fdf4' : ''"
                 style="border:1px solid {{ $hasSelectedTiers ? '#86efac' : '#e5e7eb' }};border-radius:10px;overflow:hidden;background:{{ $hasSelectedTiers ? '#f0fdf4' : '#fff' }};transition:all .15s">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer" @click="open = !open">
                    <div style="display:flex;align-items:center;gap:12px">
                        @if($hasSelectedTiers)
                        <div style="width:22px;height:22px;border-radius:50%;background:#16a34a;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        @else
                        <div style="width:22px;height:22px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0"></div>
                        @endif
                        <span style="font-size:14px;font-weight:600;color:#111827">{{ $srv['label'] }}</span>
                        <span style="font-size:10px;color:#9ca3af;background:#f3f4f6;border-radius:4px;padding:2px 6px">Narxlar</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        @if($hasSelectedTiers && !empty($srv['price']))
                        <span style="font-size:12px;font-weight:700;background:#111827;color:#fff;border-radius:6px;padding:3px 10px">
                            {{ number_format((float)$srv['price'], 0, '.', ' ') }} so'm
                        </span>
                        @endif
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" :style="open ? 'transform:rotate(180deg)' : ''" style="transition:transform .2s"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                <div x-show="open" style="padding:12px 16px;border-top:1px solid #bbf7d0;background:#fff" wire:click.stop x-cloak>
                    @if($activeSub)
                    {{-- Sub-service tabs --}}
                    <div class="tier-tabs">
                        @foreach($priceTiers[$key] as $subKey => $subTiers)
                        @php $subLabel = $subTiers[0]['sub_service_label'] ?? $subKey; @endphp
                        <button class="tier-tab {{ $activeSub === $subKey ? 'active' : '' }}"
                                wire:click.stop="setSubTab('{{ $key }}', '{{ $subKey }}')">
                            {{ $subLabel }}
                            @if(isset($srv['selected_tiers'][$subKey]))
                            <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#16a34a;margin-left:5px;vertical-align:middle"></span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                    {{-- Tier options grid --}}
                    <div class="tier-grid">
                        @foreach($priceTiers[$key][$activeSub] as $tier)
                        @php $tierSel = isset($srv['selected_tiers'][$activeSub]) && $srv['selected_tiers'][$activeSub] === $tier['id']; @endphp
                        <div class="tier-item {{ $tierSel ? 'selected' : '' }}"
                             wire:click.stop="{{ $tierSel ? 'deselectTier(\''.$key.'\', \''.$activeSub.'\')' : 'selectTier(\''.$key.'\', \''.$activeSub.'\', '.$tier['id'].')' }}">
                            <div class="tier-radio">
                                @if($tierSel)<svg width="8" height="8" viewBox="0 0 8 8" fill="#fff"><circle cx="4" cy="4" r="3"/></svg>@endif
                            </div>
                            <span class="tier-label">{{ $tier['label'] }}</span>
                            <span class="tier-price">{{ number_format($tier['price'], 0, '.', ' ') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            @else
            {{-- Non-tier service: simple toggle --}}
            <div style="border:1px solid {{ $sel ? '#86efac' : '#e5e7eb' }};border-radius:10px;overflow:hidden;background:{{ $sel ? '#f0fdf4' : '#fff' }};transition:all .15s">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer"
                     wire:click="$set('services.{{ $key }}.selected', {{ $sel ? 'false' : 'true' }})">
                    <div style="display:flex;align-items:center;gap:12px">
                        @if($sel)
                        <div style="width:22px;height:22px;border-radius:50%;background:#16a34a;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        @else
                        <div style="width:22px;height:22px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0"></div>
                        @endif
                        <span style="font-size:14px;font-weight:600;color:#111827">{{ $srv['label'] }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        @if($sel && !empty($srv['price']))
                        <span style="font-size:12px;font-weight:700;background:#111827;color:#fff;border-radius:6px;padding:3px 10px">
                            {{ number_format((float)str_replace([' ',','],'',$srv['price']), 0, '.', ' ') }} so'm
                        </span>
                        @endif
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s;{{ $sel ? 'transform:rotate(180deg)' : '' }}"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                @if($sel)
                <div style="padding:12px 16px;border-top:1px solid #bbf7d0;background:#fff" wire:click.stop>
                    <label style="font-size:12px;color:#6b7280;font-weight:500;margin-bottom:6px;display:block">Narxini kiriting (so'm)</label>
                    <input wire:model.live="services.{{ $key }}.price"
                           class="kb-input" style="max-width:260px"
                           placeholder="0" type="number" min="0">
                </div>
                @endif
            </div>
            @endif

            @endforeach
        </div>
    </div>
    @endif

    {{-- STEP 3 --}}
    @if($step === 3)
    @php
        $hasTiersSrv = fn($s) => !empty($s['has_tiers']) ? (!empty($s['selected_tiers']) && !empty($s['price'])) : !empty($s['selected']);
        $selectedSrvs = array_filter($services, $hasTiersSrv);
        $totalNoDiscount = array_sum(array_map(fn($s) => (float)($s['price'] ?? 0), $selectedSrvs));
        $totalDiscount   = array_sum(array_map(fn($s) => (float)($s['discount_amount'] ?? 0), $selectedSrvs));
        $totalFinal      = $totalNoDiscount - $totalDiscount;
    @endphp
    <div>
        {{-- Asosiy ma'lumotlar --}}
        <div class="confirm-section">
            <div class="confirm-title">Asosiy ma'lumotlar</div>
            <div class="confirm-row"><span class="confirm-key">Egasi:</span><span class="confirm-val">{{ $owner_name }}</span></div>
            @if($proj_title)<div class="confirm-row"><span class="confirm-key">Nomi:</span><span class="confirm-val">{{ $proj_title }}</span></div>@endif
            <div class="confirm-row"><span class="confirm-key">Manzil:</span><span class="confirm-val">{{ $address }}</span></div>
            <div class="confirm-row"><span class="confirm-key">Kategoriya:</span><span class="confirm-val">{{ $categoryOptions[$category] ?? $category }}</span></div>
            @if($assigned_user_id)<div class="confirm-row"><span class="confirm-key">Mas'ul:</span><span class="confirm-val">{{ $users->find($assigned_user_id)?->name }}</span></div>@endif
            @foreach($phones as $phone)@if(strlen($phone) > 4)
            <div class="confirm-row"><span class="confirm-key">Telefon:</span><span class="confirm-val">{{ $phone }}</span></div>
            @endif@endforeach
            @if(count($uploadedFiles) > 0)<div class="confirm-row"><span class="confirm-key">Fayllar:</span><span class="confirm-val">{{ count($uploadedFiles) }} ta fayl</span></div>@endif
        </div>

        {{-- Xizmatlar with discount --}}
        @if(count($selectedSrvs) > 0)
        <div class="confirm-section">
            <div class="confirm-title" style="margin-bottom:12px">Tanlangan xizmatlar ({{ count($selectedSrvs) }} ta)</div>
            @foreach($selectedSrvs as $key => $srv)
            @php
                $price       = (float)($srv['price'] ?? 0);
                $discType    = $srv['discount_type']   ?? 'none';
                $discAmount  = (float)($srv['discount_amount'] ?? 0);
                $discValue   = $srv['discount_value']  ?? '';
                $finalPrice  = ($discType !== 'none') ? ($price - $discAmount) : $price;
                $hasDiscount = $discType !== 'none' && $discAmount > 0;
            @endphp
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-bottom:10px">
                {{-- Service header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span style="font-size:14px;font-weight:600;color:#111827">{{ $srv['label'] }}</span>
                    @if(!empty($srv['selected_tiers']))
                    <span style="font-size:11px;color:#6b7280;background:#f3f4f6;border-radius:4px;padding:2px 8px">{{ count($srv['selected_tiers']) }} ta tanlangan</span>
                    @endif
                </div>

                {{-- Selected tiers list --}}
                @if(!empty($srv['selected_tiers']) && isset($priceTiers[$key]))
                <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-bottom:10px">
                    @foreach($srv['selected_tiers'] as $subKey => $tierId)
                    @php
                        $tierData = collect($priceTiers[$key][$subKey] ?? [])->firstWhere('id', $tierId);
                    @endphp
                    @if($tierData)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;{{ !$loop->last ? 'border-bottom:1px solid #e5e7eb;margin-bottom:4px' : '' }}">
                        <span style="font-size:12px;color:#374151">{{ $tierData['label'] }}</span>
                        <span style="font-size:12px;font-weight:600;color:#111827">{{ number_format($tierData['price'], 0, '.', ' ') }} so'm</span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif

                {{-- Price row --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #f3f4f6">
                    <span style="font-size:12px;color:#6b7280">Jami:</span>
                    <div style="text-align:right">
                        @if($hasDiscount)
                        <span style="font-size:12px;color:#9ca3af;text-decoration:line-through;margin-right:6px">{{ number_format($price, 0, '.', ' ') }} so'm</span>
                        <span style="font-size:14px;font-weight:700;color:#16a34a">{{ number_format($finalPrice, 0, '.', ' ') }} so'm</span>
                        @else
                        <span style="font-size:14px;font-weight:700;color:#111827">{{ number_format($price, 0, '.', ' ') }} so'm</span>
                        @endif
                    </div>
                </div>

                {{-- Discount row --}}
                <div style="margin-top:10px;background:#f8fafc;border-radius:8px;padding:10px">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="display:flex;align-items:center;gap:6px">
                            <svg width="14" height="14" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01M12 10h.01M8 10h.01M12 14h.01M8 14h.01M12 18h.01M8 18h.01"/></svg>
                            <span style="font-size:13px;font-weight:600;color:#111827">Chegirma</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px">
                            @if($hasDiscount)
                            <span style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;color:#2563eb">
                                {{ $discType === 'percent' ? $discValue.'%' : number_format($discAmount, 0, '.', ' ')." so'm" }}
                            </span>
                            <button wire:click="removeDiscount('{{ $key }}')" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;padding:0;line-height:1" title="Chegirmani o'chirish">✕</button>
                            @else
                            <button wire:click="openDiscountModal('{{ $key }}')"
                                    style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:500;color:#0369a1;cursor:pointer;display:flex;align-items:center;gap:4px">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                                Chegirma qo'llash
                            </button>
                            @endif
                        </div>
                    </div>
                    @if($hasDiscount)
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e5e7eb">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#f59e0b;font-weight:500">
                            <span>Chegirma miqdori:</span>
                            <span>-{{ number_format($discAmount, 0, '.', ' ') }} so'm</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Grand total --}}
            <div style="padding:12px;background:#f8fafc;border-radius:8px">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#6b7280;margin-bottom:4px">
                    <span>Jami (chegirmasiz):</span>
                    <span>{{ number_format($totalNoDiscount, 0, '.', ' ') }} so'm</span>
                </div>
                @if($totalDiscount > 0)
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#f59e0b;font-weight:500;margin-bottom:4px">
                    <span>Jami chegirma:</span>
                    <span>-{{ number_format($totalDiscount, 0, '.', ' ') }} so'm</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#111827;padding-top:8px;border-top:1px solid #e5e7eb">
                    <span>Umumiy summa:</span>
                    <span style="color:#16a34a">{{ number_format($totalFinal, 0, '.', ' ') }} so'm</span>
                </div>
            </div>
        </div>
        @endif

        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px;font-size:13px;color:#166534">
            ✅ Barcha ma'lumotlar to'g'ri bo'lsa, "Saqlash" tugmasini bosing.
        </div>
    </div>
    @endif

    </div>{{-- /kb-body --}}

    <div class="kb-footer">
        <div>
            @if($step > 1)
            <button class="btn-back" wire:click="prevStep">← Orqaga</button>
            @else
            <button class="btn-back" wire:click="closeModal">Bekor qilish</button>
            @endif
        </div>
        <div>
            @if($step < 3)
            <button class="btn-next" wire:click="nextStep" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="nextStep">Keyingisi →</span>
                <span wire:loading wire:target="nextStep">Yuklanmoqda...</span>
            </button>
            @else
            <button class="btn-save" wire:click="createProject" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="createProject">💾 Saqlash</span>
                <span wire:loading wire:target="createProject">Saqlanmoqda...</span>
            </button>
            @endif
        </div>
    </div>

</div>
</div>

{{-- YUBORISH (ROUTE) MODAL --}}
@if($showRouteModal)
@php $routeProj = \App\Models\Project::find($routeProjectId); @endphp
<div style="position:fixed;inset:0;z-index:1300;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5)">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:440px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0">Loyihani yo'naltirish</h3>
            <button wire:click="closeRouteModal" style="border:none;background:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">×</button>
        </div>
        @if($routeProj)
        <div style="background:#f9fafb;border-radius:8px;padding:10px 14px;margin-bottom:18px;font-size:12px;color:#374151">
            <strong>{{ $routeProj->owner_name }}</strong> — {{ $routeProj->address }}
        </div>
        @endif
        <div style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Yuborilayotgan bosqich *</label>
                <select wire:model="routeNewStatus" style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box">
                    <option value="">— Tanlang —</option>
                    @foreach($statuses as $sk => $st)
                    @if($routeProj && $sk !== $routeProj->status)
                    <option value="{{ $sk }}">{{ $st['label'] }}</option>
                    @endif
                    @endforeach
                </select>
                @error('routeNewStatus')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Bu bosqich uchun muddat (kun)</label>
                <input wire:model="routeAllocDays" type="number" min="0" max="365"
                       style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box"
                       placeholder="0 = cheklanmagan">
                <span style="font-size:10px;color:#9ca3af">0 kiritsangiz muddat belgilanmaydi</span>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Mas'ul xodim</label>
                <select wire:model="routeAssignedUserId" style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box">
                    <option value="">— O'zgartirmaslik —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role_name }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px">
            <button wire:click="closeRouteModal"
                    style="flex:1;padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            <button wire:click="confirmRoute"
                    style="flex:2;padding:11px;border-radius:8px;border:none;background:#3b82f6;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Yuborish
            </button>
        </div>
    </div>
</div>
@endif

{{-- TO'LOV MODAL --}}
@if($showPaymentModal)
@php $payProj = \App\Models\Project::with('payments')->find($paymentProjectId); @endphp
<div style="position:fixed;inset:0;z-index:1300;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5)">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0">To'lov qo'shish</h3>
            <button wire:click="closePaymentModal" style="border:none;background:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">×</button>
        </div>

        @if($payProj)
        {{-- Project summary --}}
        <div style="background:#f9fafb;border-radius:10px;padding:12px 16px;margin-bottom:20px">
            <div style="font-size:13px;font-weight:600;color:#111827;margin-bottom:4px">{{ $payProj->owner_name }}</div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:6px">
                <span>Umumiy summa:</span>
                <span style="font-weight:600;color:#111827">{{ number_format($payProj->total_price, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:6px">
                <span>To'langan:</span>
                <span style="font-weight:600;color:#16a34a">{{ number_format($payProj->paid_amount, 0, '.', ' ') }} so'm ({{ $payProj->payment_percent }}%)</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280">
                <span>Qoldiq:</span>
                <span style="font-weight:600;color:#dc2626">{{ number_format($payProj->remaining_amount, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="background:#e5e7eb;border-radius:4px;height:6px;margin-top:8px;overflow:hidden">
                <div style="background:#16a34a;height:100%;width:{{ $payProj->payment_percent }}%;border-radius:4px"></div>
            </div>
        </div>

        {{-- Form --}}
        <div style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Summa (so'm) *</label>
                <input wire:model.live="paymentAmount" type="number" min="1"
                       style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                       placeholder="Masalan: 350000"
                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
                @error('paymentAmount')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
                @if($paymentAmount && $payProj->total_price > 0)
                @php $pct = min(100, round((float)$paymentAmount / (float)$payProj->total_price * 100)); @endphp
                <div style="font-size:11px;color:#6b7280;margin-top:4px">
                    ≈ {{ $pct }}% (jami: {{ number_format($payProj->paid_amount + (float)$paymentAmount, 0, '.', ' ') }} so'm)
                </div>
                @endif
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Sana *</label>
                    <input wire:model="paymentDate" type="date"
                           style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                    @error('paymentDate')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">To'lov usuli</label>
                    <select wire:model="paymentMethod"
                            style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                        <option value="naqd">Naqd pul</option>
                        <option value="bank">Bank o'tkazma</option>
                        <option value="karta">Karta</option>
                    </select>
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Izoh</label>
                <textarea wire:model="paymentNote" rows="2"
                          style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;resize:none;box-sizing:border-box"
                          placeholder="Ixtiyoriy..."></textarea>
            </div>
            @if($payProj && $payProj->status === 'tolov_jarayonida')
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#eff6ff;border-radius:8px;cursor:pointer">
                <input type="checkbox" wire:model="paymentMoveToEskiz" style="width:16px;height:16px;accent-color:#3b82f6">
                <span style="font-size:13px;font-weight:500;color:#1d4ed8">To'lovdan keyin → Eskiz loyiha bo'limiga o'tkazish</span>
            </label>
            @endif
        </div>
        @endif

        <div style="display:flex;gap:10px;margin-top:20px">
            <button wire:click="closePaymentModal"
                    style="flex:1;padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            <button wire:click="savePayment"
                    style="flex:2;padding:11px;border-radius:8px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Saqlash
            </button>
        </div>
    </div>
</div>
@endif

{{-- CHEGIRMA MODAL (inside overlay, above kb-modal) --}}
@if($showDiscountModal)
@php
    $dp = $this->discountPreview;
    $dKey = $discountServiceKey;
    $dPrice = (float)($services[$dKey]['price'] ?? 0);
@endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1200;display:flex;align-items:center;justify-content:center;padding:16px"
     wire:click.self="closeDiscountModal">
<div style="background:#fff;border-radius:16px;width:100%;max-width:440px;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <div style="display:flex;align-items:center;gap:8px">
            <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01M12 10h.01M8 10h.01M12 14h.01M8 14h.01M12 18h.01M8 18h.01"/></svg>
            <span style="font-size:15px;font-weight:700;color:#111827">Chegirma qo'llash</span>
        </div>
        <button wire:click="closeDiscountModal" style="background:none;border:none;cursor:pointer;color:#6b7280;font-size:20px;padding:0;line-height:1">✕</button>
    </div>
    <p style="font-size:12px;color:#6b7280;margin-bottom:16px">{{ $services[$dKey]['label'] ?? '' }} xizmati uchun chegirma belgilang</p>

    {{-- Xizmat narxi --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:13px;font-weight:500;color:#1d4ed8">Xizmat narxi:</span>
        <span style="font-size:13px;font-weight:700;color:#1d4ed8">{{ number_format($dPrice, 0, '.', ' ') }} so'm</span>
    </div>

    {{-- Chegirma turi --}}
    <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:500;color:#374151;margin-bottom:8px;display:block">Chegirma turi</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <button wire:click="$set('discountType','percent')"
                    style="padding:12px;border-radius:10px;border:2px solid {{ $discountType==='percent' ? '#3b82f6' : '#e5e7eb' }};background:{{ $discountType==='percent' ? '#eff6ff' : '#fff' }};cursor:pointer;color:{{ $discountType==='percent' ? '#1d4ed8' : '#374151' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:block;margin:0 auto 4px"><line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                <span style="font-size:12px;font-weight:500">Foizda</span>
            </button>
            <button wire:click="$set('discountType','fixed')"
                    style="padding:12px;border-radius:10px;border:2px solid {{ $discountType==='fixed' ? '#3b82f6' : '#e5e7eb' }};background:{{ $discountType==='fixed' ? '#eff6ff' : '#fff' }};cursor:pointer;color:{{ $discountType==='fixed' ? '#1d4ed8' : '#374151' }}">
                <span style="font-size:18px;font-weight:700;display:block;margin-bottom:4px">so'm</span>
                <span style="font-size:12px;font-weight:500">Summada</span>
            </button>
        </div>
    </div>

    {{-- Chegirma qiymati --}}
    <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;display:block">Chegirma qiymati</label>
        <div style="position:relative">
            <input wire:model.live="discountValue"
                   class="kb-input"
                   style="padding-right:50px"
                   type="number"
                   min="0"
                   max="{{ $discountType==='percent' ? 100 : $dPrice }}"
                   placeholder="{{ $discountType==='percent' ? '0-100' : '0' }}">
            <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:500;color:#6b7280">
                {{ $discountType==='percent' ? '%' : "so'm" }}
            </span>
        </div>
    </div>

    {{-- Preview rows --}}
    @if((float)$discountValue > 0)
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;margin-bottom:8px;display:flex;justify-content:space-between">
        <span style="font-size:13px;font-weight:500;color:#c2410c">Chegirma miqdori:</span>
        <span style="font-size:13px;font-weight:700;color:#c2410c">-{{ number_format($dp['amount'], 0, '.', ' ') }} so'm</span>
    </div>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between">
        <span style="font-size:13px;font-weight:500;color:#166534">Chegirmadan so'ng:</span>
        <span style="font-size:13px;font-weight:700;color:#16a34a">{{ number_format($dp['final'], 0, '.', ' ') }} so'm</span>
    </div>
    @else
    <div style="margin-bottom:16px"></div>
    @endif

    {{-- Buttons --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <button wire:click="closeDiscountModal"
                style="padding:10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;font-size:13px;font-weight:500;color:#374151;cursor:pointer">
            Bekor qilish
        </button>
        <button wire:click="applyDiscount"
                style="padding:10px;border:none;border-radius:8px;background:#2563eb;color:#fff;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            Saqlash
        </button>
    </div>
</div>
</div>
@endif

<div id="kb-notify-box" class="kb-notify" style="display:none"></div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
var kbMap = null;
var kbMarker = null;
var kbWireId = '{{ $this->getId() }}';

function kbSetAddress(addr) {
    if (window.Livewire) {
        try {
            var comp = window.Livewire.find(kbWireId);
            if (comp) { comp.call('setAddress', addr); }
        } catch(e) {
            try {
                var all = window.Livewire.all ? window.Livewire.all() : [];
                for (var i = 0; i < all.length; i++) {
                    try { if (all[i].get('showModal') === true) { all[i].call('setAddress', addr); break; } } catch(e2) {}
                }
            } catch(e3) {}
        }
    }
    var inp = document.getElementById('kb-address-input');
    if (inp) { inp.value = addr; inp.dispatchEvent(new Event('input', { bubbles: true })); }
}

function kbSearchOnMap() {
    var q = document.getElementById('kb-map-search').value.trim();
    if (!q || !kbMap) return;
    var btn = document.querySelector('#kb-map-search + button');
    if (btn) btn.textContent = '...';
    ymaps.geocode(q + ', O\'zbekiston', { results: 1 }).then(function(res) {
        if (btn) btn.textContent = 'Qidirish';
        var obj = res.geoObjects.get(0);
        if (!obj) { alert('Manzil topilmadi. Aniqroq yozing.'); return; }
        kbMap.setCenter(obj.geometry.getCoordinates(), 17);
    }).catch(function() {
        if (btn) btn.textContent = 'Qidirish';
        alert('Qidirishda xatolik');
    });
}

function kbSetMapType(type) {
    if (!kbMap) return;
    var types = { 'hybrid': 'yandex#hybrid', 'map': 'yandex#map', 'sat': 'yandex#satellite' };
    kbMap.setType(types[type] || 'yandex#hybrid');
    document.querySelectorAll('.kb-maptype').forEach(function(b) { b.classList.remove('active'); });
    var ab = document.getElementById('kb-btn-' + type);
    if (ab) ab.classList.add('active');
}

function initKbMap() {
    var container = document.getElementById('modal-map');
    if (!container) return;
    if (kbMap) return;

    kbMap = new ymaps.Map('modal-map', {
        center: [41.2995, 69.2401],
        zoom: 12,
        controls: ['zoomControl']
    });
    kbMap.setType('yandex#hybrid');

    kbMap.events.add('click', function(e) {
        if (kbMap.getZoom() < 16) {
            kbMap.setCenter(e.get('coords'), 17);
            var hint = document.getElementById('kb-map-hint');
            if (hint) { hint.style.display = 'block'; setTimeout(function(){ hint.style.display = 'none'; }, 2500); }
            return;
        }
        var coords = e.get('coords');
        var lat = coords[0], lng = coords[1];

        if (kbMarker) {
            kbMarker.geometry.setCoordinates(coords);
        } else {
            kbMarker = new ymaps.Placemark(coords, {}, { preset: 'islands#blueDotIcon' });
            kbMap.geoObjects.add(kbMarker);
        }

        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&accept-language=uz&addressdetails=1')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var a = d.address || {};
                var parts = [];
                if (a.road && a.house_number) parts.push(a.road + ' ' + a.house_number);
                else if (a.road) parts.push(a.road);
                else if (a.pedestrian) parts.push(a.pedestrian);
                else if (a.neighbourhood || a.suburb) parts.push(a.neighbourhood || a.suburb);
                var city = a.city || a.town || a.village || a.hamlet;
                if (city) parts.push(city);
                if (a.state) parts.push(a.state);
                var addr = parts.length ? parts.join(', ') : (d.display_name || (lat.toFixed(6) + ', ' + lng.toFixed(6)));
                kbSetAddress(addr);
                var box = document.getElementById('selected-location-box');
                if (box) {
                    document.getElementById('selected-location-text').textContent = addr;
                    document.getElementById('selected-location-coords').textContent =
                        'Koordinatalar: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
                    box.style.display = 'block';
                }
            })
            .catch(function() {
                var addr = lat.toFixed(6) + ', ' + lng.toFixed(6);
                kbSetAddress(addr);
            });
    });
}

function loadAndInitMap() {
    if (typeof ymaps !== 'undefined') {
        ymaps.ready(initKbMap);
        return;
    }
    if (!document.getElementById('yandex-maps-api-kb')) {
        var s = document.createElement('script');
        s.id = 'yandex-maps-api-kb';
        s.src = 'https://api-maps.yandex.ru/2.1/?lang=uz_UZ';
        s.onload = function() { ymaps.ready(initKbMap); };
        document.head.appendChild(s);
    } else {
        var t = setInterval(function() {
            if (typeof ymaps !== 'undefined') { clearInterval(t); ymaps.ready(initKbMap); }
        }, 200);
    }
}

document.addEventListener('livewire:initialized', function () {
    Livewire.on('modal-opened', function () {
        if (kbMap) { try { kbMap.destroy(); } catch(e) {} kbMap = null; }
        kbMarker = null;
        var box = document.getElementById('selected-location-box');
        if (box) box.style.display = 'none';
        setTimeout(loadAndInitMap, 250);
    });

    Livewire.on('notify', function (data) {
        var d = Array.isArray(data) ? data[0] : data;
        var box = document.getElementById('kb-notify-box');
        if (!box) return;
        box.textContent = d.message || '';
        box.style.background = d.type === 'success' ? '#16a34a' : '#dc2626';
        box.style.display = 'block';
        setTimeout(function() { box.style.display = 'none'; }, 3500);
    });

    Livewire.on('project-created', function () {
        setTimeout(function() { window.location.reload(); }, 2000);
    });
});

// ── Kanban Drag & Drop ────────────────────────────────────────────────
var kbDragId = null;
window._kbDragged = false;

function kbDragStart(e, id) {
    kbDragId = id;
    window._kbDragged = false;
    e.dataTransfer.effectAllowed = 'move';
    e.currentTarget.classList.add('dragging');
}

function kbDragEnd(e) {
    e.currentTarget.classList.remove('dragging');
    setTimeout(function() { window._kbDragged = false; }, 50);
}

function kbDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    e.currentTarget.classList.add('drag-over');
}

function kbDragLeave(e) {
    if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('drag-over');
    }
}

function kbDrop(e, status) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    if (!kbDragId) return;
    window._kbDragged = true;
    var id = kbDragId;
    kbDragId = null;
    var lwEl = document.querySelector('[wire\\:id]');
    if (lwEl) {
        Livewire.find(lwEl.getAttribute('wire:id')).moveProject(id, status);
    }
}
</script>

</x-filament-panels::page>
