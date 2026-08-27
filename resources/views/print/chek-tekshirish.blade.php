<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chek tekshirish — {{ $payment->id }}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;background:linear-gradient(160deg,#1f2937,#0f172a);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px;color:#e5e7eb}
    .card{background:#fff;color:#111827;border-radius:18px;max-width:380px;width:100%;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,.5)}
    .top{padding:26px 24px;text-align:center;color:#fff;background:linear-gradient(135deg,#059669,#10b981)}
    .top .icon{font-size:46px;line-height:1}
    .top .st{font-size:20px;font-weight:800;margin-top:8px}
    .top .sum{font-size:32px;font-weight:900;margin-top:4px}
    .body{padding:20px 24px}
    .row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px;gap:12px}
    .row:last-child{border-bottom:none}
    .row .k{color:#6b7280;white-space:nowrap}
    .row .v{font-weight:700;color:#111827;text-align:right}
    .foot{text-align:center;padding:14px;font-size:12px;color:#9ca3af;background:#f8fafc}
</style>
</head>
<body>
<div class="card">
    <div class="top">
        <div class="icon">✅</div>
        <div class="st">Chek tasdiqlangan</div>
        <div class="sum">{{ number_format($payment->amount, 0, '.', ' ') }} so'm</div>
    </div>
    <div class="body">
        <div class="row"><span class="k">Loyiha raqami</span><span class="v">№{{ $project->seq_no ?? $project->number }}</span></div>
        <div class="row"><span class="k">Mijoz</span><span class="v">{{ $project->owner_name }}</span></div>
        <div class="row"><span class="k">To'lov usuli</span><span class="v">{{ \App\Models\Payment::methodOptions()[$payment->method] ?? $payment->method }}</span></div>
        <div class="row"><span class="k">Sana</span><span class="v">{{ $payment->payment_date->format('d.m.Y') }}</span></div>
        <div class="row"><span class="k">Qabul qildi</span><span class="v">{{ $payment->createdBy?->name ?? '—' }}</span></div>
    </div>
    <div class="foot">MY PERFECT HOME — chek tekshirish tizimi</div>
</div>
</body>
</html>
