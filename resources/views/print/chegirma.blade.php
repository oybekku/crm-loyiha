<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chegirma flayeri — {{ $project->number }}</title>
@php
    // ── Firma kontaktlari (DOIMIY — bir marta shu yerda o'zgartiriladi) ──
    $firm = [
        'name'      => 'KUSHMANOV ELYORDAN',
        'phone'     => '+998 77 091 91 01',
        'telegram'  => '@Kushmanov_Elyordan',
        'instagram' => '@kushmanov.elyordan',
    ];

    $discount   = 7;                                   // doim 7%
    $client     = $project->owner_name;                // mijoz ismi (o'zgaradi)
    $code       = ltrim($project->number, '#');        // loyiha raqami
    $validUntil = $project->created_at->copy()->addMonth();   // ochilgan sana + 1 oy
    $checkUrl   = route('chegirma.check', $code);      // QR ulanadigan tekshirish sahifasi
    $qrSrc      = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=0&data=' . urlencode($checkUrl);
@endphp
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{background:#525659;font-family:'Georgia','Times New Roman',serif}

    .sheet{width:210mm;margin:0 auto;background:#fff;padding:8mm 0;display:flex;flex-direction:column;align-items:center;gap:6mm}

    /* ── Bitta flayer (720×344 px ≈ 190×91 mm) ── */
    .flyer{position:relative;width:720px;height:344px;overflow:hidden;border-radius:6px;box-shadow:0 1px 4px rgba(0,0,0,.25)}
    .flyer img.bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
    .flyer .layer{position:absolute;inset:0}

    .gold{color:#e6c374;text-shadow:0 1px 2px rgba(0,0,0,.55)}
    .gold-strong{color:#f0d089;text-shadow:0 2px 4px rgba(0,0,0,.6)}

    /* Matn joylashuvi (flayer ichida absolute) */
    .f-client   {position:absolute;left:36px;top:26px;font-size:25px;font-weight:700;letter-spacing:.04em;max-width:440px;line-height:1.1;text-transform:uppercase}
    .f-firm     {position:absolute;right:30px;top:24px;text-align:right;font-size:12px;font-weight:600;line-height:1.3;letter-spacing:.05em;opacity:.92;text-transform:uppercase}
    .f-discount {position:absolute;left:46px;top:96px;font-size:74px;font-weight:800;line-height:1;letter-spacing:-.02em}
    .f-slogan   {position:absolute;left:52px;top:188px;font-size:21px;font-style:italic;line-height:1.25;max-width:330px}
    .f-valid    {position:absolute;left:52px;bottom:46px;font-size:12px;font-weight:600;letter-spacing:.02em}
    .f-contacts {position:absolute;left:52px;bottom:20px;font-size:11px;font-weight:600;display:flex;gap:14px;align-items:center;flex-wrap:wrap}
    .f-contacts span{display:inline-flex;align-items:center;gap:3px}

    /* QR plastinka (o'ng-past) */
    .f-qr   {position:absolute;right:38px;bottom:46px;width:62px;height:62px;background:#fff;border-radius:5px;padding:3px}
    .f-qr img{width:100%;height:100%;display:block}
    .f-code {position:absolute;right:30px;bottom:24px;font-size:10px;font-weight:700;letter-spacing:.08em;color:#3a2c12}

    /* Toolbar */
    .no-print{position:sticky;top:0;z-index:20;background:#1f2937;display:flex;gap:12px;justify-content:center;align-items:center;padding:11px}
    .no-print button{border:none;border-radius:7px;padding:9px 22px;font-size:14px;font-weight:700;cursor:pointer}

    @media print{
        body{background:#fff}
        .no-print{display:none!important}
        .sheet{padding:6mm 0;gap:5mm;width:auto}
        .flyer{box-shadow:none;border-radius:0}
        body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    }
    @page{size:A4 portrait;margin:6mm}
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="background:#22c55e;color:#fff">🖨 Chop etish</button>
    <button onclick="window.close()" style="background:#374151;color:#fff">✕ Yopish</button>
    <span style="color:#9ca3af;font-size:13px">A4 — 3 ta bir xil flayer (kesib bering)</span>
</div>

<div class="sheet">
    @for($i = 0; $i < 3; $i++)
    <div class="flyer">
        <img class="bg" src="{{ asset('images/chegirma-bg.png') }}?v=2" alt="">
        <div class="layer">
            {{-- Mijoz ismi --}}
            <div class="f-client gold-strong">{{ $client }}</div>
            {{-- Chegirma --}}
            <div class="f-discount gold-strong">−{{ $discount }}%</div>
            {{-- Slogan --}}
            <div class="f-slogan gold">Ўз яқинларингиз<br>ҳақида қайғуринг</div>
            {{-- Amal muddati --}}
            <div class="f-valid gold">АМАЛ ҚИЛИШ МУДДАТИ: {{ $validUntil->format('d.m.Y') }} гача</div>
            {{-- Kontaktlar --}}
            <div class="f-contacts gold">
                <span>📞 {{ $firm['phone'] }}</span>
                <span>✈ {{ $firm['telegram'] }}</span>
                <span>◎ {{ $firm['instagram'] }}</span>
            </div>
            {{-- QR + kod --}}
            <div class="f-qr"><img src="{{ $qrSrc }}" alt="QR"></div>
            <div class="f-code">KF-{{ $code }}</div>
        </div>
    </div>
    @endfor
</div>

</body>
</html>
