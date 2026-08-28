<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<title>Chek — {{ $payment->id }}</title>
@php
    // Chek qog'ozi kengligi — chop etish tugmasi birinchi marta bosilganda
    // brauzerda so'raladi va localStorage'da saqlanadi (App\...\payment-modal.blade.php
    // va kanban-board.blade.php'dagi bhOpenChek() funksiyasi), so'ng shu yerga
    // ?width= parametri sifatida keladi. Noma'lum/berilmagan bo'lsa 80mm.
    $width  = (int) request('width', 80);
    $width  = in_array($width, [58, 80], true) ? $width : 80;
    $narrow = $width === 58;
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { size: {{ $width }}mm auto; margin: 0; }
    html, body {
        width: {{ $width }}mm;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: {{ $narrow ? 10 : 12 }}px;
        line-height: 1.45;
        color: #000;
        background: #fff;
    }
    .chek { padding: {{ $narrow ? '3mm 3mm 6mm' : '4mm 4mm 8mm' }}; }
    .center { text-align: center; }
    .firm { font-size: {{ $narrow ? 13 : 15 }}px; font-weight: 700; letter-spacing: 1px; }
    .sub   { font-size: {{ $narrow ? 9 : 10 }}px; color: #333; margin-top: 1mm; }
    .divider { border-top: 1px dashed #000; margin: {{ $narrow ? '2mm' : '3mm' }} 0; }
    .row { display: flex; justify-content: space-between; gap: 6px; margin-bottom: 1.5mm; }
    .row .label { color: #333; }
    .row .value { font-weight: 700; text-align: right; }
    .big { font-size: {{ $narrow ? 14 : 16 }}px; font-weight: 800; }
    .warn { border: 1px solid #000; padding: 1.5mm; margin-top: 2mm; font-weight: 700; text-align: center; }
    .footer { margin-top: 4mm; font-size: {{ $narrow ? 9 : 10 }}px; text-align: center; color: #333; }
    .qr { margin-top: 3mm; text-align: center; }
    .qr img { width: {{ $narrow ? 18 : 22 }}mm; height: {{ $narrow ? 18 : 22 }}mm; }
    .qr .qr-label { font-size: 9px; color: #333; margin-top: 1mm; }
</style>
</head>
<body>
<div class="chek">
    <div class="center firm">MY PERFECT HOME</div>
    <div class="center sub">MFO 01125 &middot; INN 308515451</div>
    <div class="center sub">To'lov cheki</div>
    <div class="center sub">{{ $payment->payment_date->format('d.m.Y') }}</div>

    <div class="divider"></div>

    <div class="row"><span class="label">Loyiha</span><span class="value">№{{ $project->seq_no ?? $project->number }}</span></div>
    <div class="row"><span class="label">Mijoz</span><span class="value">{{ $project->owner_name }}</span></div>
    @if($project->address)
    <div class="row"><span class="label">Manzil</span><span class="value" style="max-width:{{ $narrow ? 28 : 48 }}mm">{{ $project->address }}</span></div>
    @endif

    <div class="divider"></div>

    <div class="row big"><span class="label">To'lov summasi</span><span class="value">{{ number_format($payment->amount, 0, '.', ' ') }}</span></div>
    <div class="row"><span class="label">To'lov usuli</span><span class="value">{{ \App\Models\Payment::methodOptions()[$payment->method] ?? $payment->method }}</span></div>
    @if($payment->account)
    <div class="row"><span class="label">Hisob</span><span class="value">{{ $payment->account->name }}</span></div>
    @endif
    @if($payment->note)
    <div class="row"><span class="label">Izoh</span><span class="value">{{ $payment->note }}</span></div>
    @endif
    <div class="row"><span class="label">Qabul qildi</span><span class="value">{{ $payment->createdBy?->name ?? '—' }}</span></div>

    @if($project->services->count() > 0)
    <div class="divider"></div>

    <div class="sub" style="font-weight:700;margin-bottom:1.5mm">ISHLAR</div>
    @php
        $svcLabels = \App\Models\Project::serviceOptions();
        $svcOrder  = array_flip(array_keys($svcLabels));
        $sortedServices = $project->services->sortBy(fn ($svc) => $svcOrder[$svc->service_name] ?? 99);
    @endphp
    @foreach($sortedServices as $svc)
    <div class="row"><span class="label">{{ \Illuminate\Support\Str::upper($svcLabels[$svc->service_name] ?? $svc->service_name) }}</span><span class="value">{{ number_format($svc->final_price, 0, '.', ' ') }}</span></div>
    @endforeach
    @endif

    @if(!$payment->account_id)
    <div class="warn">⚠ SUMMA HISOBGA BOG'LANMAGAN</div>
    @endif

    @php
        $checkUrl = route('print.payment.chek.check', [
            'number'    => ltrim($project->number, '#'),
            'paymentId' => $payment->id,
        ]);
        $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=0&data=' . urlencode($checkUrl);
    @endphp
    <div class="qr">
        <img src="{{ $qrSrc }}" alt="QR">
        <div class="qr-label">Chekni tekshirish</div>
    </div>

    <div class="footer">Xaridingiz uchun rahmat!</div>
</div>
<script>
    window.onload = function () {
        window.print();
    };
    window.onafterprint = function () {
        window.close();
    };
</script>
</body>
</html>
