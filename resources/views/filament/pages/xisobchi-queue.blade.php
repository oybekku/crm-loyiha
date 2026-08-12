<x-filament-panels::page>
<style>
.xq-panel{background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.07);border-radius:10px;padding:16px 18px}
.dark .xq-panel{background:#1e293b;box-shadow:0 1px 6px rgba(0,0,0,.35)}
.xq-row{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 16px;margin-bottom:10px}
.dark .xq-row{background:#052e1b;border-color:#166534}
.xq-name{color:#111827;font-size:14px;font-weight:700}
.dark .xq-name{color:#f1f5f9}
.xq-num{color:#9ca3af;font-size:11px;font-family:monospace}
.dark .xq-num{color:#64748b}
.xq-label{color:#6b7280;font-size:11px}
.dark .xq-label{color:#94a3b8}
.xq-val{color:#111827;font-size:12.5px;font-weight:600}
.dark .xq-val{color:#e2e8f0}
.xq-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px 20px;margin-top:8px}
.xq-empty{color:#9ca3af;font-size:13px;text-align:center;padding:30px 0}
.dark .xq-empty{color:#64748b}
.xq-btn{background:#0891b2;border:none;color:#fff;font-size:12px;padding:8px 16px;border-radius:6px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
.xq-btn-ready{background:#16a34a}
.xq-tabs{display:flex;gap:8px;margin-bottom:14px}
.xq-tab{background:transparent;border:1px solid #d1d5db;color:#6b7280;font-size:13px;font-weight:700;padding:8px 16px;border-radius:8px;cursor:pointer}
.dark .xq-tab{border-color:#334155;color:#94a3b8}
.xq-tab.active{background:#0891b2;border-color:#0891b2;color:#fff}
#xq-notify-box{display:none;position:fixed;top:16px;right:16px;z-index:9999;color:#fff;font-size:13px;font-weight:600;padding:10px 16px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.2)}
</style>

<div id="xq-notify-box"></div>

@php
$xqTabLabels = [
    'yangi_didox' => 'Yangi Didox',
    'tugallangan' => 'Shot-faktura kerak',
];
@endphp

<div class="xq-tabs">
    <button type="button" wire:click="setTab('yangi_didox')" class="xq-tab @if($tab === 'yangi_didox') active @endif">Yangi Didox</button>
    <button type="button" wire:click="setTab('tugallangan')" class="xq-tab @if($tab === 'tugallangan') active @endif">Shot-faktura kerak</button>
</div>

<div class="xq-panel">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
        <span style="font-size:14px;font-weight:700">{{ $xqTabLabels[$tab] }}</span>
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;border-radius:10px;padding:1px 8px">{{ $projects->count() }} ta</span>
    </div>

    @forelse($projects as $p)
    <div class="xq-row">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div>
                <span class="xq-num">{{ $p->number }}</span>
                <span class="xq-name">&nbsp;{{ $p->owner_name }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;align-items:stretch">
                <a href="{{ route('print.project.didox', $p) }}" target="_blank" class="xq-btn" style="justify-content:center">
                    🔷 DIDOX shartnoma
                </a>
                @if($tab === 'yangi_didox')
                <button type="button"
                        wire:click.stop="markDidoxDone({{ $p->id }})"
                        wire:confirm="Shartnoma tayyor va loyiha Toposyomka navbatiga yuboriladimi?"
                        class="xq-btn xq-btn-ready"
                        style="justify-content:center">
                    ✅ Tayor
                </button>
                @elseif($tab === 'tugallangan')
                <button type="button"
                        wire:click.stop="markInvoiceDone({{ $p->id }})"
                        wire:confirm="Shot-faktura yuborildimi?"
                        class="xq-btn xq-btn-ready"
                        style="justify-content:center">
                    ✅ Tayor
                </button>
                @endif
            </div>
        </div>
        <div class="xq-grid">
            <div>
                <div class="xq-label">Manzil</div>
                <div class="xq-val">{{ $p->address ?: '—' }}</div>
            </div>
            <div>
                <div class="xq-label">Telefon</div>
                <div class="xq-val">
                    @php $phone = collect($p->phones ?? [])->first()['phone'] ?? null; @endphp
                    {{ $phone ?: '—' }}
                </div>
            </div>
            <div>
                <div class="xq-label">Passport seriya</div>
                <div class="xq-val">{{ $p->passport_series ?: '—' }}</div>
            </div>
            <div>
                <div class="xq-label">Berilgan vaqt</div>
                <div class="xq-val">{{ $p->passport_issued_by ?: '—' }}</div>
            </div>
            <div>
                <div class="xq-label">JSHIR/PINFL</div>
                <div class="xq-val">{{ $p->pinfl ?: '—' }}</div>
            </div>
            <div>
                <div class="xq-label">Kelgan sana</div>
                <div class="xq-val">{{ $p->created_at?->format('d.m.Y H:i') }}</div>
            </div>
        </div>
    </div>
    @empty
    <div class="xq-empty">
        @if($tab === 'tugallangan') Hozircha shot-faktura kutayotgan loyiha yo'q
        @elseif($tab === 'yangi_didox') Hozircha "Yangi Didox"ga o'tkazilgan loyiha yo'q
        @else Hozircha yangi loyiha yo'q
        @endif
    </div>
    @endforelse
</div>

<script>
document.addEventListener('livewire:initialized', function () {
    Livewire.on('notify', function (data) {
        var d = Array.isArray(data) ? data[0] : data;
        var box = document.getElementById('xq-notify-box');
        if (!box) return;
        box.textContent = d.message || '';
        box.style.background = d.type === 'success' ? '#16a34a' : '#dc2626';
        box.style.display = 'block';
        setTimeout(function() { box.style.display = 'none'; }, 3500);
    });
});
</script>
</x-filament-panels::page>
