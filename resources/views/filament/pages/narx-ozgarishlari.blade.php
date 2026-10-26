<x-filament-panels::page>
@php
    $rows = $this->entries;
    $S    = $this->stats($rows);
    $fmt  = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $sgn  = fn ($v) => ($v > 0 ? '+' : ($v < 0 ? '−' : '')) . number_format(abs((float) $v), 0, '.', ' ');
@endphp
<style>
.no-title{font-size:20px;font-weight:800;color:#111827 !important}
.dark .no-title{color:#f1f5f9 !important}
.no-sub{font-size:12px;color:#6b7280 !important}
.dark .no-sub{color:#9ca3af !important}

.no-filters{display:flex;flex-wrap:wrap;align-items:end;gap:12px;background:#fff;border:1px solid #f1f5f9;border-radius:14px;padding:16px 18px;margin-bottom:18px;box-shadow:0 1px 8px rgba(0,0,0,.04)}
.dark .no-filters{background:#1f2937;border-color:rgba(255,255,255,.06)}
.no-field label{display:block;font-size:11px;font-weight:700;color:#6b7280 !important;margin-bottom:4px}
.no-field select,.no-field input{padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12.5px;background:#fff;color:#111827 !important;min-width:170px}
.dark .no-field select,.dark .no-field input{background:#111827;border-color:rgba(255,255,255,.1);color:#f1f5f9 !important}
.no-reset{padding:8px 16px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#6b7280 !important;font-size:12.5px;font-weight:700;cursor:pointer}
.dark .no-reset{background:transparent;border-color:rgba(255,255,255,.12);color:#9ca3af !important}

.no-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
@media (max-width:900px){.no-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
.no-stat{background:#fff;border:1px solid #f1f5f9;border-radius:14px;padding:14px 16px;box-shadow:0 1px 8px rgba(0,0,0,.04);border-top:3px solid var(--c)}
.dark .no-stat{background:#1f2937;border-color:rgba(255,255,255,.06);border-top-color:var(--c)}
.no-stat-l{font-size:11px;font-weight:700;color:#6b7280 !important}
.no-stat-v{font-size:22px;font-weight:800;color:var(--c) !important;margin:4px 0 2px;white-space:nowrap}
.no-stat-s{font-size:11px;color:#9ca3af !important}

.no-panel{background:#fff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;box-shadow:0 1px 8px rgba(0,0,0,.04);margin-bottom:18px}
.dark .no-panel{background:#1f2937;border-color:rgba(255,255,255,.06)}
.no-ph{padding:12px 18px;font-size:13px;font-weight:800;color:#111827 !important;border-bottom:1px solid #f3f4f6}
.dark .no-ph{color:#f1f5f9 !important;border-color:rgba(255,255,255,.05)}
.no-tbl{width:100%;border-collapse:collapse;font-size:12.5px}
.no-tbl th{text-align:left;font-size:11px;font-weight:700;color:#6b7280 !important;padding:9px 14px;background:#f9fafb;border-bottom:1px solid #f1f5f9}
.dark .no-tbl th{background:rgba(255,255,255,.03);border-color:rgba(255,255,255,.05)}
.no-tbl td{padding:10px 14px;border-bottom:1px solid #f3f4f6;color:#374151 !important;vertical-align:top}
.dark .no-tbl td{border-color:rgba(255,255,255,.05);color:#d1d5db !important}
.no-tbl tr:last-child td{border-bottom:none}
.no-tbl .num{text-align:right;white-space:nowrap}
.no-day td{background:#f8fafc;font-size:11px;font-weight:800;color:#475569 !important;padding:6px 14px}
.dark .no-day td{background:rgba(255,255,255,.04);color:#cbd5e1 !important}
.no-kind{font-size:11px;font-weight:700;border-radius:6px;padding:3px 8px;white-space:nowrap}
.no-up{color:#b45309 !important}
.no-down{color:#dc2626 !important}
.no-mu{color:#9ca3af !important;font-size:11px}
.no-wrap{overflow-x:auto}
.no-empty{text-align:center;color:#9ca3af !important;padding:40px;font-size:13px}
</style>

<div style="max-width:1200px;margin:0 auto">
    <div style="margin-bottom:16px">
        <div class="no-title">Narx o'zgarishlari</div>
        <div class="no-sub">Narxi bor xizmat narxi yoki chegirmasi o'zgarganda, narxi bor loyihaga xizmat qo'shilganda yoki olib tashlanganda yoziladi. To'lov kiritish va yangi loyiha ochish bu yerga kirmaydi.</div>
    </div>

    <div class="no-filters">
        <div class="no-field">
            <label>Kim o'zgartirgan</label>
            <select wire:model.live="filterUserId">
                <option value="">Hammasi</option>
                @foreach($this->users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="no-field">
            <label>Turi</label>
            <select wire:model.live="filterKind">
                <option value="">Hammasi</option>
                @foreach(\App\Models\PriceChange::KINDS as $k => $l)
                    <option value="{{ $k }}">{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="no-field">
            <label>Sanadan</label>
            <input type="date" wire:model.live="dateFrom">
        </div>
        <div class="no-field">
            <label>Sanagacha</label>
            <input type="date" wire:model.live="dateTo">
        </div>
        <button type="button" class="no-reset" wire:click="resetFilters">Tozalash</button>
    </div>

    {{-- Umumiy statistika --}}
    <div class="no-stats">
        <div class="no-stat" style="--c:#2563eb">
            <div class="no-stat-l">Jami o'zgarishlar</div>
            <div class="no-stat-v">{{ $S['count'] }}</div>
            <div class="no-stat-s">{{ $S['projects'] }} ta loyihada</div>
        </div>
        <div class="no-stat" style="--c:#b45309">
            <div class="no-stat-l">⬆ Oshirildi</div>
            <div class="no-stat-v">+{{ $fmt($S['upSum']) }}</div>
            <div class="no-stat-s">{{ $S['upCount'] }} marta · shundan {{ $S['added'] }} ta xizmat qo'shildi</div>
        </div>
        <div class="no-stat" style="--c:#dc2626">
            <div class="no-stat-l">⬇ Kamaytirildi</div>
            <div class="no-stat-v">−{{ $fmt($S['downSum']) }}</div>
            <div class="no-stat-s">{{ $S['downCount'] }} marta · shundan {{ $S['removed'] }} ta xizmat olib tashlandi</div>
        </div>
        <div class="no-stat" style="--c:{{ $S['net'] < 0 ? '#dc2626' : '#15803d' }}">
            <div class="no-stat-l">Sof farq</div>
            <div class="no-stat-v">{{ $sgn($S['net']) }}</div>
            <div class="no-stat-s">loyihalar jami summasiga ta'siri</div>
        </div>
    </div>

    {{-- Kim qancha o'zgartirgan --}}
    @if($S['byUser']->isNotEmpty())
    <div class="no-panel">
        <div class="no-ph">Kim o'zgartirgan</div>
        <div class="no-wrap">
        <table class="no-tbl">
            <thead><tr><th>Hodim</th><th class="num">Soni</th><th class="num">Oshirgan</th><th class="num">Kamaytirgan</th><th class="num">Oxirgi marta</th></tr></thead>
            <tbody>
            @foreach($S['byUser'] as $u)
                <tr>
                    <td><b>{{ $u['name'] }}</b></td>
                    <td class="num">{{ $u['count'] }}</td>
                    <td class="num no-up">{{ $u['up'] > 0 ? '+' . $fmt($u['up']) : '—' }}</td>
                    <td class="num no-down">{{ $u['down'] > 0 ? '−' . $fmt($u['down']) : '—' }}</td>
                    <td class="num no-mu">{{ $u['last']?->format('d.m.Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Barcha o'zgarishlar — kunlar bo'yicha --}}
    <div class="no-panel">
        <div class="no-ph">Barcha o'zgarishlar</div>
        <div class="no-wrap">
        <table class="no-tbl">
            <thead><tr><th>Vaqt</th><th>Kim</th><th>Loyiha</th><th>Nima</th><th class="num">Eski → yangi narx</th><th class="num">Farq</th><th class="num">Loyiha jami</th></tr></thead>
            <tbody>
            @php $prevDay = null; @endphp
            @forelse($rows as $r)
                @php
                    $day = $r->created_at->format('Y-m-d'); $d = $r->delta();
                    $kindCss = match ($r->kind) { 'added' => 'background:#fef3c7;color:#92400e', 'removed' => 'background:#fee2e2;color:#991b1b', default => 'background:#eff6ff;color:#1d4ed8' };
                @endphp
                @if($day !== $prevDay)
                    <tr class="no-day"><td colspan="7">{{ $r->created_at->format('d.m.Y') }} · {{ $S['byDay'][$day] ?? 0 }} ta o'zgarish</td></tr>
                    @php $prevDay = $day; @endphp
                @endif
                <tr>
                    <td class="no-mu" style="white-space:nowrap">{{ $r->created_at->format('H:i') }}</td>
                    <td style="white-space:nowrap"><b>{{ $r->user?->name ?? "O'chirilgan foydalanuvchi" }}</b></td>
                    <td>
                        @if($r->project_id)
                            <a href="{{ route('filament.admin.resources.projects.edit', $r->project_id) }}" style="color:#2563eb;font-weight:600">{{ $r->project_number }}</a>
                        @else
                            {{ $r->project_number }} <span class="no-mu">(o'chirilgan)</span>
                        @endif
                        <div class="no-mu">{{ $r->owner_name }}</div>
                    </td>
                    <td>
                        <span class="no-kind" style="{{ $kindCss }}">{{ \App\Models\PriceChange::KINDS[$r->kind] ?? $r->kind }}</span>
                        <div style="margin-top:3px">{{ $r->serviceLabel() }}</div>
                    </td>
                    <td class="num">
                        @if($r->kind === 'added') <span class="no-mu">yangi</span> → {{ $fmt($r->new_price) }}
                        @elseif($r->kind === 'removed') {{ $fmt($r->old_price) }} → <span class="no-mu">olib tashlandi</span>
                        @else {{ $fmt($r->old_price) }} → <b>{{ $fmt($r->new_price) }}</b>
                        @endif
                    </td>
                    <td class="num {{ $d > 0 ? 'no-up' : 'no-down' }}" style="font-weight:800">{{ $sgn($d) }}</td>
                    <td class="num no-mu">{{ $fmt($r->old_total) }} → {{ $fmt($r->new_total) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="no-empty">Bu filtr bo'yicha narx o'zgarishi topilmadi</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
</x-filament-panels::page>
