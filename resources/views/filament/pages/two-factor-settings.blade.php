<x-filament-panels::page>
<div style="max-width:520px;margin:0 auto">

    @if($enabled)
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:16px;padding:28px;text-align:center">
        <div style="font-size:40px;margin-bottom:10px">✅</div>
        <div style="font-size:16px;font-weight:700;color:#15803d;margin-bottom:6px">Google Authenticator yoqilgan</div>
        <div style="font-size:13px;color:#166534;margin-bottom:20px">
            {{ $enabledAt?->format('d.m.Y H:i') }} dan beri kirishda ilovadagi 6 xonali kod so'raladi.
        </div>
        <div style="font-size:12px;color:#6b7280;margin-bottom:8px">O'chirish uchun ilovadagi hozirgi kodni yozing:</div>
        <div style="display:flex;gap:8px;justify-content:center">
            <input type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" wire:model="code" placeholder="000000"
                   style="width:130px;text-align:center;font-size:18px;letter-spacing:4px;padding:8px;border:1px solid #d1d5db;border-radius:8px">
            <button type="button" wire:click="disable" wire:confirm="Google Authenticator o'chirilsinmi?"
                    style="padding:10px 18px;border-radius:8px;border:1px solid #fecaca;background:#fff;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer">
                O'chirish
            </button>
        </div>
        @error('code')<div style="color:#dc2626;font-size:12px;margin-top:8px">{{ $message }}</div>@enderror
    </div>
    @else
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:28px">
        <div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:14px;text-align:center">Google Authenticator'ni ulash</div>

        <ol style="font-size:13px;color:#374151;line-height:1.7;padding-left:20px;margin-bottom:18px;list-style:decimal">
            <li>Telefoningizga <b>Google Authenticator</b> ilovasini o'rnating (Play Market / App Store, bepul).</li>
            <li>Ilovada <b>"+"</b> → <b>"QR kodni skanerlash"</b> ni bosib, pastdagi kodni skaner qiling.</li>
            <li>Ilovada paydo bo'lgan <b>6 xonali kodni</b> pastga yozib, "Yoqish" ni bosing.</li>
        </ol>

        <div wire:ignore style="display:flex;justify-content:center;margin-bottom:12px">
            <div id="totp-qr" data-url="{{ $otpauth }}" style="background:#fff;padding:8px;border-radius:10px;border:1px solid #e5e7eb;line-height:0"></div>
        </div>
        <div style="text-align:center;font-size:11px;color:#6b7280;margin-bottom:18px">
            Skaner qilib bo'lmasa — ilovada "Kalitni kiritish" orqali shu kalitni yozing:<br>
            <code style="font-size:13px;color:#111827;letter-spacing:1px;user-select:all">{{ $secret }}</code>
        </div>

        <div style="display:flex;gap:8px;justify-content:center">
            <input type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" wire:model="code" wire:keydown.enter="enable" placeholder="000000"
                   style="width:140px;text-align:center;font-size:20px;letter-spacing:4px;padding:8px;border:1px solid #d1d5db;border-radius:8px">
            <button type="button" wire:click="enable" wire:loading.attr="disabled"
                    style="padding:10px 22px;border-radius:8px;border:none;background:#16a34a;color:#fff;font-size:14px;font-weight:700;cursor:pointer">
                Yoqish
            </button>
        </div>
        @error('code')<div style="color:#dc2626;font-size:12px;margin-top:8px;text-align:center">{{ $message }}</div>@enderror
    </div>

    {{-- QR kod brauzerda chiziladi (maxfiy kalit tashqi servisga yuborilmaydi) --}}
    <script src="{{ asset('js/qrcode-generator.js') }}"></script>
    <script>
        (function draw() {
            var el = document.getElementById('totp-qr');
            if (!el || typeof qrcode === 'undefined') return setTimeout(draw, 100);
            var qr = qrcode(0, 'M');
            qr.addData(el.dataset.url);
            qr.make();
            el.innerHTML = qr.createSvgTag({ cellSize: 5, margin: 2, scalable: false });
        })();
    </script>
    @endif

</div>
</x-filament-panels::page>
