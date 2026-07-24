<x-filament-panels::page>
<div style="max-width:480px;margin:0 auto">

    @if(!$configured)
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:18px 20px;color:#b91c1c;font-size:13px">
        ⚠️ Telegram bot hali sozlanmagan. Server administratori <code>.env</code> faylida
        <code>TELEGRAM_BOT_TOKEN</code> va <code>TELEGRAM_BOT_USERNAME</code> qiymatlarini kiritishi kerak.
    </div>
    @elseif($linked)
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:16px;padding:28px;text-align:center">
        <div style="font-size:40px;margin-bottom:10px">✅</div>
        <div style="font-size:16px;font-weight:700;color:#15803d;margin-bottom:6px">Telegram bog'langan</div>
        <div style="font-size:13px;color:#166534;margin-bottom:20px">Endi muhim amallarni tasdiqlash kodlari shu Telegram akkauntga keladi.</div>
        <button wire:click="unlink" wire:confirm="Bog'lanishni bekor qilasizmi? Shundan keyin tasdiqlash kodlarini ololmaysiz."
                style="padding:10px 20px;border-radius:8px;border:1px solid #fecaca;background:#fff;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer">
            Bog'lanishni bekor qilish
        </button>
    </div>
    @else
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;text-align:center">
        <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:6px">Telegramni bog'lang</div>
        <div style="font-size:13px;color:#6b7280;margin-bottom:20px">
            Muhim amallarni (o'chirish, narx o'zgartirish) tasdiqlash uchun endi qattiq kod o'rniga
            Telegramingizga bir martalik kod yuboriladi.
        </div>

        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=0&data={{ urlencode($linkUrl) }}"
             alt="QR" style="border-radius:10px;margin-bottom:16px">

        <div>
            <a href="{{ $linkUrl }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:9px;background:#26a5e4;color:#fff;text-decoration:none;font-size:13px;font-weight:700">
                📱 Telegram botni ochish
            </a>
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-top:14px">
            Telefoningizda QR kodni skanerlang yoki yuqoridagi tugmani bosing → botga "Start" bosing.
        </div>
    </div>
    @endif

</div>
</x-filament-panels::page>
