<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chegirma tekshirish — {{ $code }}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;background:linear-gradient(160deg,#1f2937,#0f172a);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px;color:#e5e7eb}
    .card{background:#fff;color:#111827;border-radius:18px;max-width:380px;width:100%;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,.5)}
    .top{padding:26px 24px;text-align:center;color:#fff}
    .top.ok{background:linear-gradient(135deg,#059669,#10b981)}
    .top.expired{background:linear-gradient(135deg,#b91c1c,#ef4444)}
    .top .icon{font-size:46px;line-height:1}
    .top .st{font-size:20px;font-weight:800;margin-top:8px}
    .top .disc{font-size:40px;font-weight:900;margin-top:4px}
    .body{padding:20px 24px}
    .row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px}
    .row:last-child{border-bottom:none}
    .row .k{color:#6b7280}
    .row .v{font-weight:700;color:#111827;text-align:right}
    .foot{text-align:center;padding:14px;font-size:12px;color:#9ca3af;background:#f8fafc}
</style>
</head>
<body>
<div class="card">
    <div class="top {{ $active ? 'ok' : 'expired' }}">
        <div class="icon">{{ $active ? '✅' : '⛔' }}</div>
        <div class="st">{{ $active ? 'Chegirma AMALDA' : 'Muddati TUGAGAN' }}</div>
        <div class="disc">−{{ $discount }}%</div>
    </div>
    <div class="body">
        <div class="row"><span class="k">Mijoz</span><span class="v">{{ $client }}</span></div>
        <div class="row"><span class="k">Loyiha raqami</span><span class="v">{{ $number }}</span></div>
        <div class="row"><span class="k">Ochilgan sana</span><span class="v">{{ $openedAt }}</span></div>
        <div class="row"><span class="k">Amal qiladi</span><span class="v" style="color:{{ $active ? '#059669' : '#dc2626' }}">{{ $validUntil }} gacha</span></div>
        @if(!$active)
        <div class="row"><span class="k">Holat</span><span class="v" style="color:#dc2626">Muddat o'tib ketgan</span></div>
        @endif
    </div>
    <div class="foot">MAKONN.UZ — chegirma tekshirish tizimi</div>
</div>
</body>
</html>
