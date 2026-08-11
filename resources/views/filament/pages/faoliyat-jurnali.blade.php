<x-filament-panels::page>
<style>
.fj-title{font-size:20px;font-weight:800;color:#111827 !important}
.dark .fj-title{color:#f1f5f9 !important}

.fj-filters{display:flex;flex-wrap:wrap;align-items:end;gap:12px;background:#fff;border:1px solid #f1f5f9;border-radius:14px;padding:16px 18px;margin-bottom:18px;box-shadow:0 1px 8px rgba(0,0,0,.04)}
.dark .fj-filters{background:#1f2937;border-color:rgba(255,255,255,.06)}
.fj-field label{display:block;font-size:11px;font-weight:700;color:#6b7280 !important;margin-bottom:4px}
.dark .fj-field label{color:#9ca3af !important}
.fj-field select,.fj-field input{padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12.5px;background:#fff;color:#111827 !important;min-width:170px}
.dark .fj-field select,.dark .fj-field input{background:#111827;border-color:rgba(255,255,255,.1);color:#f1f5f9 !important}
.fj-reset{padding:8px 16px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#6b7280 !important;font-size:12.5px;font-weight:700;cursor:pointer}
.dark .fj-reset{background:transparent;border-color:rgba(255,255,255,.12);color:#9ca3af !important}

.fj-panel{background:#fff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;box-shadow:0 1px 8px rgba(0,0,0,.04)}
.dark .fj-panel{background:#1f2937;border-color:rgba(255,255,255,.06)}

.fj-row{display:flex;align-items:flex-start;gap:12px;padding:12px 18px;border-bottom:1px solid #f3f4f6}
.dark .fj-row{border-color:rgba(255,255,255,.05)}
.fj-row:last-child{border-bottom:none}
.fj-row:hover{background:#f9fafb}
.dark .fj-row:hover{background:rgba(255,255,255,.03)}

.fj-date{font-size:11px;color:#9ca3af !important;font-family:monospace;flex-shrink:0;width:110px;padding-top:2px}
.fj-user{font-size:12.5px;font-weight:700;color:#111827 !important;flex-shrink:0;width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dark .fj-user{color:#f1f5f9 !important}
.fj-action{font-size:11px;font-weight:700;color:#2563eb !important;background:#eff6ff;border-radius:6px;padding:3px 9px;white-space:nowrap;flex-shrink:0;align-self:flex-start}
.fj-action.danger{color:#dc2626 !important;background:#fef2f2}
.dark .fj-action{background:rgba(37,99,235,.15)}
.dark .fj-action.danger{background:rgba(220,38,38,.15)}
.fj-body{flex:1;min-width:0;font-size:12.5px;color:#374151 !important;padding-top:2px}
.dark .fj-body{color:#d1d5db !important}
.fj-proj{font-size:11px;color:#6b7280 !important;margin-top:2px}
.dark .fj-proj{color:#9ca3af !important}

.fj-empty{text-align:center;color:#9ca3af !important;padding:40px;font-size:13px}
</style>

<div style="max-width:1100px;margin:0 auto">
    <div class="fj-title" style="margin-bottom:16px">Faoliyat jurnali</div>

    <div class="fj-filters">
        <div class="fj-field">
            <label>Xodim</label>
            <select wire:model.live="filterUserId">
                <option value="">Hammasi</option>
                @foreach($this->users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fj-field">
            <label>Turi</label>
            <select wire:model.live="filterType">
                <option value="">Hammasi</option>
                <option value="project">Loyiha o'zgarishlari</option>
                <option value="payment">To'lovlar</option>
            </select>
        </div>
        <div class="fj-field">
            <label>Sanadan</label>
            <input type="date" wire:model.live="dateFrom">
        </div>
        <div class="fj-field">
            <label>Sanagacha</label>
            <input type="date" wire:model.live="dateTo">
        </div>
        <button type="button" class="fj-reset" wire:click="resetFilters">Tozalash</button>
    </div>

    <div class="fj-panel">
        @forelse($this->entries as $entry)
            <div class="fj-row">
                <div class="fj-date">{{ $entry['created_at']?->format('d.m.Y H:i') }}</div>
                <div class="fj-user">{{ $entry['user'] }}</div>
                <div class="fj-action {{ $entry['danger'] ? 'danger' : '' }}">{{ $entry['action'] }}</div>
                <div class="fj-body">
                    {{ $entry['description'] }}
                    @if($entry['project_number'])
                        <div class="fj-proj">
                            Loyiha: {{ $entry['project_number'] }}
                            @if($entry['project_id'] && $entry['linkable'])
                                — <a href="{{ route('filament.admin.resources.projects.edit', $entry['project_id']) }}" wire:navigate style="color:#2563eb">ochish</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="fj-empty">Bu filtr bo'yicha yozuv topilmadi</div>
        @endforelse
    </div>
</div>
</x-filament-panels::page>
