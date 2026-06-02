<div x-data="{
    combined: '',
    init() {
        // Record dan to'g'ridan qiymat o'qish
        @php
            $record = $getRecord();
            $initLat = $record?->latitude ? number_format((float)$record->latitude, 6, '.', '') : '';
            $initLng = $record?->longitude ? number_format((float)$record->longitude, 6, '.', '') : '';
        @endphp
        @if($initLat && $initLng)
        this.combined = '{{ $initLat }}, {{ $initLng }}';
        @endif

        // bh-fill-address kabi hodisa orqali yangilash
        window.addEventListener('bh-fill-coords', (e) => {
            if (e.detail?.lat && e.detail?.lng) {
                this.combined = e.detail.lat + ', ' + e.detail.lng;
            }
        });
    },
    onInput() {
        var parts = this.combined.split(',');
        if (parts.length !== 2) return;
        var lat = parseFloat(parts[0].trim());
        var lng = parseFloat(parts[1].trim());
        if (isNaN(lat) || isNaN(lng)) return;
        setTimeout(() => {
            this.setHidden('fp-lat-input', lat.toFixed(6));
            this.setHidden('fp-lng-input', lng.toFixed(6));
        }, 0);
    },
    setHidden(id, val) {
        var el = document.getElementById(id);
        if (!el) return;
        var setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
        setter.call(el, val);
        el.dispatchEvent(new Event('input', { bubbles: true }));
    },
    copy() {
        if (!this.combined) return;
        navigator.clipboard.writeText(this.combined).then(() => {
            var btn = this.$refs.copyBtn;
            btn.textContent = '✓ Nusxalandi!';
            btn.style.color = '#16a34a';
            setTimeout(() => { btn.textContent = 'Nusxalash'; btn.style.color = ''; }, 2000);
        });
    }
}" x-init="init()">
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>
        Koordinatalar
        <span style="font-size:11px;color:#9ca3af;font-weight:400">(xaritadan avtomatik to'ldiriladi)</span>
    </label>
    <div style="display:flex;gap:8px;align-items:center">
        <input
            x-model="combined"
            x-on:input.debounce.600ms="onInput()"
            type="text"
            placeholder="Masalan: 41.299800, 69.240100"
            style="flex:1;border:1px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px;font-family:monospace;outline:none;transition:border-color .15s"
            onfocus="this.style.borderColor='#2563eb'"
            onblur="this.style.borderColor='#d1d5db'">
        <button
            type="button"
            x-ref="copyBtn"
            x-on:click="copy()"
            style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;white-space:nowrap">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Nusxalash
        </button>
    </div>
</div>
