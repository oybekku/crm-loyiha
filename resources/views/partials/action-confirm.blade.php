{{--
    Muhim amalni Google Authenticator kodi bilan tasdiqlash (ActionConfirmService).
    Parametrlar: $model (wire:model), $submit (Enter bosilganda chaqiriladigan metod),
                 $error (bool — noto'g'ri kod), $hint (ixtiyoriy izoh).
--}}
@if(\App\Services\ActionConfirmService::required())
    <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">🔐 Google Authenticator kodi</label>
    @if(!\App\Services\ActionConfirmService::canConfirm(auth()->user()))
        <div style="font-size:13px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-bottom:8px">
            Bu amal uchun Google Authenticator kodi kerak. Avval <a href="{{ \App\Services\ActionConfirmService::setupUrl() }}" style="color:#4338ca;font-weight:600">ilovani ulang</a>.
        </div>
    @else
        @if(!empty($hint))<div style="font-size:13px;color:#6b7280;margin-bottom:10px">{{ $hint }}</div>@endif
        <input type="text" inputmode="numeric" autocomplete="one-time-code" wire:model="{{ $model }}"
               wire:keydown.enter="{{ $submit }}"
               style="width:100%;border:1.5px solid {{ $error ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:10px 14px;font-size:18px;letter-spacing:6px;text-align:center;outline:none;margin-bottom:8px;box-sizing:border-box"
               placeholder="······" maxlength="6" autofocus>
        @if($error)
            <div style="font-size:12px;color:#ef4444;margin-bottom:10px">❌ Noto'g'ri yoki eskirgan kod — ilovadagi yangi kodni kiriting</div>
        @endif
    @endif
@endif
