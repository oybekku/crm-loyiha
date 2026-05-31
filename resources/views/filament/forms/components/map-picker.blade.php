{{-- Alpine: server dan kelgan manzilni inputga yozadi --}}
<div x-data
     x-on:bh-fill-address.window="
         var addr = $event.detail.address;
         var setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
         document.querySelectorAll('input').forEach(function(el) {
             var m = el.getAttribute('wire:model') || el.getAttribute('wire:model.defer') || el.getAttribute('wire:model.live') || '';
             if (m === 'data.address') {
                 setter.call(el, addr);
                 el.dispatchEvent(new Event('input', {bubbles:true}));
             }
         });
     "></div>

<div wire:ignore style="width:100%"
     data-lat="{{ old('data.latitude', $getRecord()?->latitude ?? '') }}"
     data-lng="{{ old('data.longitude', $getRecord()?->longitude ?? '') }}">
    <div style="display:flex;gap:6px;margin-bottom:8px">
        <input id="bh-yandex-url" type="text"
               placeholder="Yandex Maps havolasini joylashtiring..."
               style="flex:1;border:1px solid #d1d5db;border-radius:8px;padding:6px 11px;font-size:13px;outline:none">
        <button type="button" onclick="bhLoadYandexUrl()"
                style="background:#e53e3e;color:#fff;border:none;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:5px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M21 10.5c0-3.87-3.13-7-7-7A7 7 0 0 0 9.13 14.5L3 20.5l1.5 1.5 6.01-6.01A7 7 0 0 0 21 10.5zm-7 5a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg>
            Yuklash
        </button>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <p style="margin:0;font-size:12px;color:#6b7280;">
            Yoki xaritaga bosing — manzil va koordinatalar avtomatik to'ldiriladi
        </p>
        <button id="bh-locate-btn" type="button"
            onclick="bhLocateMe()"
            style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;font-size:12px;font-weight:600;color:#fff;background:#059669;border:none;border-radius:8px;cursor:pointer;transition:background .15s;"
            onmouseover="this.style.background='#047857'"
            onmouseout="this.style.background='#059669'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>
            </svg>
            Mening joyim
        </button>
    </div>
    <div id="yandex-map-container"
         style="width:100%;height:320px;border-radius:8px;border:1px solid #d1d5db;position:relative;">
        <div id="bh-locate-status" style="display:none;position:absolute;top:8px;left:50%;transform:translateX(-50%);z-index:9999;background:rgba(0,0,0,0.7);color:#fff;padding:6px 14px;border-radius:20px;font-size:12px;pointer-events:none;"></div>
    </div>
</div>
