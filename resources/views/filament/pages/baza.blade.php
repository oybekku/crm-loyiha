<x-filament-panels::page>
@php $fmt = fn($v) => number_format((float) $v, 0, '.', ' '); @endphp
<style>
    .bz-wrap{display:flex;gap:16px;align-items:flex-start}
    .bz-main{flex:1;min-width:0;background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden}
    .bz-side{width:400px;flex-shrink:0;background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;position:sticky;top:76px;max-height:calc(100vh - 96px);overflow-y:auto}
    .bz-table{width:100%;border-collapse:collapse;font-size:13px}
    .bz-table th{text-align:left;padding:9px 12px;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.03em;background:#f9fafb;border-bottom:1px solid #e5e7eb}
    .bz-table td{padding:10px 12px;border-bottom:1px solid #f3f4f6;vertical-align:top}
    .bz-table tr{cursor:pointer}
    .bz-table tbody tr:hover{background:#f8fafc}
    .bz-table tr.on{background:#eef2ff}
    .bz-sec{font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin:14px 0 6px}
    .bz-row{display:flex;justify-content:space-between;gap:10px;font-size:13px;padding:4px 0;border-bottom:1px dashed #f1f5f9}
    .bz-row span:first-child{color:#6b7280}
    @media (max-width: 1024px){.bz-wrap{flex-direction:column}.bz-side{width:100%;position:static;max-height:none}}
</style>

{{-- Sarlavha --}}
<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px">
    <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#475569,#0f172a);display:flex;align-items:center;justify-content:center;font-size:24px">📦</div>
    <div style="flex:1;min-width:220px">
        <div style="font-size:20px;font-weight:800;color:#0f172a">Baza — eskirgan loyihalar</div>
        <div style="font-size:12.5px;color:#64748b">Asosiy saytda ko'rinmaydi · faqat admin · faqat o'qish uchun · "↩ Asosiyga qaytarish" bilan qaytariladi</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:8px 14px">
            <div style="font-size:11px;color:#6b7280">Loyihalar</div>
            <div style="font-size:17px;font-weight:800;color:#0f172a">{{ $fmt($stats->c ?? 0) }} ta</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:8px 14px">
            <div style="font-size:11px;color:#6b7280">Tushgan pul (kassada)</div>
            <div style="font-size:17px;font-weight:800;color:#16a34a">{{ $fmt($stats->paid ?? 0) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:8px 14px">
            <div style="font-size:11px;color:#6b7280">Hisobdan chiqarilgan qarz</div>
            <div style="font-size:17px;font-weight:800;color:#dc2626">{{ $fmt($stats->debt ?? 0) }}</div>
        </div>
    </div>
</div>

<div class="bz-wrap">
    <div class="bz-main">
        <div style="padding:12px 14px;border-bottom:1px solid #f3f4f6">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Ism, manzil, telefon yoki № bo'yicha qidirish..."
                   style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;box-sizing:border-box">
        </div>
        <div style="overflow-x:auto">
        <table class="bz-table">
            <thead>
                <tr>
                    <th>№</th>
                    <th>Egasi</th>
                    <th style="text-align:right">Narx</th>
                    <th style="text-align:right">To'langan</th>
                    <th>Ochilgan</th>
                    <th>Bazaga</th>
                </tr>
            </thead>
            <tbody>
            @forelse($projects as $p)
                @php $pct = $p->total_price > 0 ? min(100, round($p->paid_amount / $p->total_price * 100)) : 100; @endphp
                <tr wire:click="selectProject({{ $p->id }})" class="{{ $selectedId === $p->id ? 'on' : '' }}">
                    <td style="color:#6b7280;font-weight:600">№{{ $p->seq_no }}</td>
                    <td>
                        <div style="font-weight:700;color:#111827"><x-fish :name="$p->owner_name" /></div>
                        <div style="font-size:11px;color:#9ca3af">{{ \Illuminate\Support\Str::limit($p->address, 50) }}</div>
                    </td>
                    <td style="text-align:right;white-space:nowrap">{{ $fmt($p->total_price) }}</td>
                    <td style="text-align:right;white-space:nowrap">
                        <span style="color:#16a34a;font-weight:600">{{ $fmt($p->paid_amount) }}</span>
                        <div style="font-size:11px;color:{{ $pct >= 100 ? '#16a34a' : '#d97706' }}">{{ $pct }}%</div>
                    </td>
                    <td style="white-space:nowrap;color:#6b7280">{{ $p->created_at?->format('d.m.Y') }}</td>
                    <td style="white-space:nowrap;color:#6b7280">{{ $p->baza_at?->format('d.m.Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;cursor:default">
                    <div style="font-size:30px">📦</div>
                    Bazada hali loyiha yo'q. Loyiha oynasidagi yoki Arxivdagi "📦 Bazaga" tugmasi bilan ko'chiriladi.
                </td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        @if($projects->hasPages())
        <div style="padding:10px 14px">{{ $projects->links() }}</div>
        @endif
    </div>

    @if($selected)
    @php
        $sp   = $selected;
        $snap = $sp->baza_snapshot ?? [];
        $debt = max(0, (float) $sp->total_price - (float) $sp->paid_amount);
    @endphp
    <div class="bz-side">
        <div style="padding:14px 16px;background:linear-gradient(135deg,#475569,#0f172a);color:#fff;display:flex;gap:10px;align-items:flex-start">
            <div style="flex:1">
                <div style="font-size:15px;font-weight:800">{{ $sp->owner_name }}</div>
                <div style="font-size:11px;opacity:.8">№{{ $sp->seq_no }} · {{ $sp->number }}</div>
            </div>
            <button wire:click="selectProject(null)" style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:7px;width:28px;height:28px;cursor:pointer">✕</button>
        </div>
        <div style="padding:4px 16px 16px">
            <div style="margin-top:12px;font-size:12px;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px">
                📦 Bazaga: <b>{{ $sp->baza_at?->format('d.m.Y H:i') }}</b>
                @if($sp->baza_by) · {{ $movers[$sp->baza_by] ?? '—' }} @endif
                @if(!empty($snap['prev_status']))<br>Oldingi bosqich: {{ $statusLabels[$snap['prev_status']] ?? $snap['prev_status'] }}@endif
                @if(!empty($snap['services']))<br>Tugallanmagan {{ count($snap['services']) }} ta ishga ulush ochilgan @endif
            </div>

            <div class="bz-sec">Ma'lumot</div>
            @if($sp->address)<div class="bz-row"><span>Manzil</span><span style="text-align:right">{{ $sp->address }}</span></div>@endif
            @foreach((array) $sp->phones as $ph)
                <div class="bz-row"><span>Telefon</span><span>{{ is_array($ph) ? ($ph['phone'] ?? '') : $ph }}</span></div>
            @endforeach
            <div class="bz-row"><span>Ochilgan</span><span>{{ $sp->created_at?->format('d.m.Y') }}</span></div>
            <div class="bz-row"><span>Bosqich</span><span>{{ $statusLabels[$sp->status] ?? $sp->status }}</span></div>

            <div class="bz-sec">Pul</div>
            <div class="bz-row"><span>Jami narx</span><b>{{ $fmt($sp->total_price) }} so'm</b></div>
            <div class="bz-row"><span>To'langan</span><b style="color:#16a34a">{{ $fmt($sp->paid_amount) }} so'm</b></div>
            @if($debt > 0)<div class="bz-row"><span>Hisobdan chiqarilgan qarz</span><b style="color:#dc2626">{{ $fmt($debt) }} so'm</b></div>@endif

            @if($sp->payments->count())
            <div class="bz-sec">To'lovlar ({{ $sp->payments->count() }} ta)</div>
            @foreach($sp->payments->sortBy('payment_date') as $pay)
                <div class="bz-row">
                    <span>{{ $pay->payment_date?->format('d.m.Y') }} · {{ $pay->account?->name ?: ($methodOptions[$pay->method] ?? $pay->method) }}</span>
                    <b>{{ $fmt($pay->amount) }}</b>
                </div>
            @endforeach
            @endif

            @if($sp->services->count())
            <div class="bz-sec">Xizmatlar</div>
            @foreach($sp->services as $s)
                <div class="bz-row">
                    <span>{{ $s->service_label }} @if($s->assignedUser)· {{ $s->assignedUser->name }}@endif</span>
                    <span>{{ $fmt($s->final_price) }} {!! $s->completed_at ? '<span style="color:#16a34a">✔</span>' : '<span style="color:#d97706">⏳</span>' !!}</span>
                </div>
            @endforeach
            @endif

            @if($sp->files->count())
            <div class="bz-sec">Fayllar ({{ $sp->files->count() }} ta)</div>
            @foreach($sp->files as $f)
                <a href="{{ asset('storage/' . $f->file_path) }}" target="_blank"
                   style="display:block;font-size:12.5px;color:#2563eb;text-decoration:none;padding:3px 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">📎 {{ $f->file_name }}</a>
            @endforeach
            @endif

            @if($sp->description)
            <div class="bz-sec">Izoh</div>
            <div style="font-size:12.5px;color:#374151;background:#f9fafb;border-radius:8px;padding:8px 10px">{{ $sp->description }}</div>
            @endif

            <button wire:click="askRestore({{ $sp->id }})"
                    style="margin-top:16px;width:100%;padding:10px;border-radius:9px;border:1.5px solid #86efac;background:#f0fdf4;color:#15803d;font-size:13px;font-weight:700;cursor:pointer">
                ↩ Asosiyga qaytarish
            </button>
        </div>
    </div>
    @endif
</div>

{{-- Qaytarishni tasdiqlash --}}
@if($restoreId)
<div style="position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="cancelRestore">
    <div style="background:#fff;border-radius:16px;padding:22px 24px;width:100%;max-width:400px;box-shadow:0 25px 60px rgba(0,0,0,.25)">
        <div style="font-size:16px;font-weight:800;color:#111827;margin-bottom:8px">↩ Loyihani asosiy saytga qaytarish</div>
        <div style="font-size:12.5px;color:#475569;line-height:1.55;margin-bottom:12px">
            Loyiha yana Kanban, Arxiv, qarzlar va statistikada ko'rinadi, oldingi bosqichi tiklanadi.
            Ko'chirishda ulush ochilgan tugallanmagan ishlar yana "tugallanmagan" bo'ladi — agar xodimga
            o'sha ulush allaqachon berilgan bo'lsa, u "ortiqcha to'langan" bo'lib ko'rinadi.
        </div>
        @include('partials.action-confirm', ['model' => 'restoreCode', 'submit' => 'confirmRestore', 'error' => $restoreError, 'autofocus' => true])
        <div style="display:flex;gap:8px;margin-top:8px">
            <button wire:click="cancelRestore" style="flex:1;padding:10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:13px;cursor:pointer">Bekor</button>
            <button wire:click="confirmRestore" style="flex:1;padding:10px;border-radius:8px;border:none;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer">↩ Qaytarish</button>
        </div>
    </div>
</div>
@endif
</x-filament-panels::page>
