<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Qabul arizasi — {{ $project->number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #000; background: #fff; }

    .page { width: 210mm; margin: 0 auto; padding: 0; }
    .copy1, .copy2 { width: 100%; min-height: 297mm; padding: 10mm 12mm 8mm; display: flex; flex-direction: column; box-sizing: border-box; }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 3px solid #1d4ed8;
    }
    .header-left h1 { font-size: 24px; font-weight: 900; color: #1d4ed8; margin-bottom: 3px; letter-spacing: -0.5px; }
    .header-left .order-num { font-size: 13px; color: #444; display: flex; align-items: center; gap: 8px; }
    .header-right { text-align: right; font-size: 13px; line-height: 1.7; }
    .header-right strong { font-size: 15px; display: block; margin-bottom: 2px; }

    .num-badge {
        display: inline-block;
        background: #1d4ed8;
        color: #fff;
        padding: 3px 12px;
        border-radius: 5px;
        font-weight: 800;
        font-size: 15px;
        letter-spacing: 0.05em;
    }

    /* Main table */
    .main-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .main-table td {
        border: 1.5px solid #c8d0db;
        padding: 5px 10px;
        vertical-align: middle;
    }
    .main-table .label {
        width: 34%;
        font-weight: 700;
        background: #f0f4f8;
        font-size: 13px;
        color: #374151;
    }
    .main-table .value { font-size: 14px; color: #111; }
    .main-table .qr-cell {
        width: 110px;
        text-align: center;
        vertical-align: middle;
        background: #fff;
        border-left: 1.5px solid #c8d0db;
    }

    .qr-wrap { text-align: center; padding: 6px; }
    .qr-wrap img { width: 96px; height: 96px; display: block; margin: 0 auto 5px; }
    .qr-wrap p { font-size: 10px; color: #666; line-height: 1.4; }

    /* Conditions */
    .conditions {
        border: 1.5px solid #c8d0db;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 8px;
        font-size: 12px;
        line-height: 1.6;
        color: #222;
        background: #fafbfc;
    }
    .conditions h3 {
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 5px;
        color: #1d4ed8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .conditions ol { padding-left: 18px; }
    .conditions li { margin-bottom: 3px; }

    /* Page break */
    .page-break { page-break-before: always; break-before: page; }
    .copy-divider {
        border: none; border-top: 2px dashed #9ca3af;
        margin: 12px 0 10px;
    }
    .copy-label {
        text-align: center; font-size: 11px; color: #9ca3af;
        margin: -32px 0 20px; background: #fff;
        display: inline-block; padding: 0 12px;
        position: relative; left: 50%; transform: translateX(-50%);
    }

    /* Signatures */
    .signatures {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        margin-top: 42px;
        padding-top: 6px;
    }
    .sig-block { flex: 1; position: relative; min-height: 110px; }
    .stamp-img {
        position: absolute;
        top: -45px;
        left: 19px;
        width: 280px;
        opacity: 0.92;
        pointer-events: none;
        z-index: 1;
    }
    .sig-title { font-size: 13px; font-weight: 700; margin-bottom: 35px; }
    .sig-line { border-bottom: 1.5px solid #000; margin-bottom: 6px; position: relative; z-index: 2; }
    .sig-label { font-size: 11px; color: #666; position: relative; z-index: 2; }
    .sig-name { font-size: 14px; font-weight: 700; margin-top: 4px; position: relative; z-index: 2; }
    .sig-right { text-align: right; }

    .footer-date {
        font-size: 11px;
        color: #666;
        margin-top: 12px;
        text-align: center;
        border-top: 1px solid #e5e7eb;
        padding-top: 8px;
    }

    /* Language hidden */
    .lang-uz, .lang-ru { display: none; }
    .lang-uz.active, .lang-ru.active { display: block; }

    /* Mijoz nusxasi — ramkasiz, sof matn ko'rinishi */
    .conditions-plain {
        border: none;
        border-radius: 0;
        background: transparent;
        padding: 0;
    }

    /* Mijoz nusxasidagi katta BILDIRISHNOMA sarlavhasi */
    .bild-title {
        font-size: 24px;
        font-weight: 800;
        color: #1e3a5f;
        text-align: center;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .bild-title-line {
        border: none;
        border-top: 2px solid #cbd5e1;
        margin-bottom: 16px;
    }

    /* Mijoz imzosi — sudrab (drag) joylashtiriladigan, o'lchami o'zgartiriladigan rasm */
    .client-sig-wrap {
        position: absolute;
        top: 10px;
        left: 60px;
        width: 240px;
        cursor: move;
        z-index: 2;
    }
    .client-sig-img { width: 100%; display: block; filter: contrast(1.3); pointer-events: none; }
    .sig-rsz {
        position: absolute; right: -8px; bottom: -8px; width: 16px; height: 16px;
        border-radius: 50%; background: #2563eb; border: 2px solid #fff;
        cursor: nwse-resize; z-index: 3;
    }
    .sig-btn {
        margin-top: 10px; padding: 6px 14px; border-radius: 6px;
        border: 1px solid #93c5fd; background: #eff6ff; color: #1d4ed8;
        font-size: 12px; font-weight: 700; cursor: pointer;
    }

    /* Mijoz imzo chizish oynasi (to'liq ekran) */
    .sign-ov { position: fixed; inset: 0; background: #fff; z-index: 300; display: none; flex-direction: column; }
    .sign-box { background: #fff; width: 100%; height: 100%; display: flex; flex-direction: column; }
    .sign-hd { font-size: 16px; font-weight: 700; color: #111; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; flex-shrink: 0; }
    .sign-hd span { font-weight: 400; color: #9ca3af; font-size: 12px; }
    #signCanvas { border: none; background: #fff; touch-action: none; cursor: crosshair; display: block; flex: 1; width: 100%; min-height: 0; }
    .sign-actions { display: flex; gap: 10px; align-items: center; padding: 12px 16px; border-top: 1px solid #e5e7eb; flex-shrink: 0; }
    .sign-actions button { border: none; border-radius: 8px; padding: 13px 22px; font-size: 15px; font-weight: 700; cursor: pointer; }
    .s-clear { background: #f1f5f9; color: #475569; }
    .s-cancel { background: #e5e7eb; color: #374151; }
    .s-done { background: #16a34a; color: #fff; }

    /* Ko'rinish rejimi — ekranda ham, chop etishda ham bir xil (WYSIWYG):
       "Mijoz nusxasi" tanlansa, mijoz shu yerda o'z imzosini qo'ya oladi,
       keyin xohlagan payt "Chop etish" bosiladi. */
    .copy2 { display: none; }
    body.view-mijoz .copy1 { display: none; }
    body.view-mijoz .copy2 { display: flex; flex-direction: column; }
    .view-btn.active { outline: 2px solid #fff; outline-offset: -2px; }

    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { padding: 10mm 12mm; }
        .no-print { display: none !important; }
        .sign-ov { display: none !important; }
    }

    @page { size: A4; margin: 0; }
</style>
</head>
<body>

<!-- Toolbar -->
<div class="no-print" style="text-align:center;padding:10px 16px;background:#1e40af;border-bottom:1px solid #1d4ed8;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
    <!-- Language selector -->
    <div style="display:flex;gap:4px;background:rgba(255,255,255,0.15);border-radius:7px;padding:3px;">
        <button id="btn-uz" onclick="setLang('uz')"
            style="background:#fff;color:#1d4ed8;border:none;padding:6px 18px;border-radius:5px;font-size:13px;font-weight:700;cursor:pointer;">
            🇺🇿 O'zbek
        </button>
        <button id="btn-ru" onclick="setLang('ru')"
            style="background:transparent;color:#fff;border:none;padding:6px 18px;border-radius:5px;font-size:13px;font-weight:600;cursor:pointer;">
            🇷🇺 Русский
        </button>
    </div>
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <button id="btn-view-firma" class="view-btn" onclick="viewMode('firma')" style="background:#fff;color:#1d4ed8;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        👁 Firma nusxasi
    </button>
    <button id="btn-view-mijoz" class="view-btn" onclick="viewMode('mijoz')" style="background:#22c55e;color:#fff;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        👁 Mijoz nusxasi
    </button>
    <button onclick="window.print()" style="background:#fbbf24;color:#78350f;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        🖨 Chop etish
    </button>
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <button onclick="window.open('{{ route('print.project.obloshka', $project) }}?qavat=1','_blank')" style="background:#fff;color:#1d4ed8;border:none;padding:8px 18px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        ▦ Bir qavat
    </button>
    <button onclick="window.open('{{ route('print.project.obloshka', $project) }}?qavat=2','_blank')" style="background:#fff;color:#1d4ed8;border:none;padding:8px 18px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        ▦ Ikki qavat
    </button>
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <a href="{{ route('print.project.shartnoma', ['project' => $project, 'lang' => 'ru']) }}" style="background:#fff;color:#1d4ed8;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">📄 Shartnoma RU</a>
    <a href="{{ route('print.project.shartnoma', ['project' => $project, 'lang' => 'uz']) }}" style="background:#fff;color:#15803d;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">📄 Shartnoma UZ</a>
    <a href="{{ route('print.project.rozilik', $project) }}" style="background:#fff;color:#7c3aed;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">📝 Rozilik xati</a>
    <button onclick="window.open('{{ route('print.project.chegirma', $project) }}','_blank')" style="background:#fff;color:#b45309;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">🎟 Chegirma</button>
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <button onclick="window.close()" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:8px 18px;border-radius:6px;font-size:14px;cursor:pointer;">
        ✕ Yopish
    </button>
</div>

<div class="page">

<div class="copy1">
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <h1 id="title-text">Qabul arizasi</h1>
            <div class="order-num">
                <span id="label-ariza">Ariza</span>
                <span class="num-badge">{{ $project->number }}</span>
                &nbsp;&nbsp;{{ $project->created_at->format('d.m.Y') }}
            </div>
        </div>
        <div class="header-right">
            <strong>BESTHOME CRM</strong>
            +998 99 468 19 91<br>
            {{ now()->format('d.m.Y H:i') }}
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <table class="main-table">
        <tr>
            <td class="label" id="lbl-client">Mijoz (F.I.Sh)</td>
            <td class="value">
                <strong>{{ $project->owner_name }}</strong>
                @if($project->phones)
                    @foreach($project->phones as $phone)
                        &nbsp;&nbsp;{{ is_array($phone) ? ($phone['phone'] ?? '') : $phone }}@if(!$loop->last),@endif
                    @endforeach
                @endif
            </td>
            <td class="qr-cell" rowspan="9">
                <div class="qr-wrap">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ urlencode(route('track.project', ltrim($project->number, '#'))) }}" alt="QR">
                    <p id="qr-text">Buyurtma raqamini<br>skanerlang</p>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label" id="lbl-type">Loyiha turi</td>
            <td class="value">
                @php
                    $cats = ['turar'=>'Turar-joy','tijorat'=>'Tijorat binosi','qishloq'=>'Qishloq qurilishi','sanoat'=>'Sanoat binosi','boshqa'=>'Boshqa'];
                    $catsRu = ['turar'=>'Жилое здание','tijorat'=>'Коммерческое здание','qishloq'=>'Сельское строительство','sanoat'=>'Промышленное здание','boshqa'=>'Другое'];
                @endphp
                <span class="lang-uz active">{{ $cats[$project->category] ?? $project->category }}</span>
                <span class="lang-ru">{{ $catsRu[$project->category] ?? $project->category }}</span>
            </td>
        </tr>
        <tr>
            <td class="label" id="lbl-name">Loyiha nomi</td>
            <td class="value">{{ $project->title ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-address">Ob'ekt manzili</td>
            <td class="value">{{ $project->address }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-worker">Mas'ul xodim</td>
            <td class="value">{{ $project->assignedUsers->pluck('name')->join(', ') ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-deadline">Taxminiy muddat</td>
            <td class="value">{{ $project->deadline_date ? $project->deadline_date->format('d.m.Y') : '—' }}</td>
        </tr>
        @if($project->services && $project->services->count() > 0)
            @php
                $totalPrice = (float) $project->total_price;
                $paidAmount = (float) $project->paid_amount;
                // Xizmat narxlari (nom => final_price)
                $priceMap = [];
                foreach ($project->services as $s) {
                    $priceMap[$s->service_name] = (float) $s->final_price;
                }
                // Har xizmat uchun to'langan ulush — HAR ISHNING NARXIGA PROPORSIONAL
                $svcPaidMap = [];
                foreach ($project->payments as $pay) {
                    $svcs = $pay->services ?? [];
                    if (empty($svcs)) continue;
                    $sumSel = 0;
                    foreach ($svcs as $sn) { $sumSel += ($priceMap[$sn] ?? 0); }
                    foreach ($svcs as $sn) {
                        $svcPrice = $priceMap[$sn] ?? 0;
                        $share = $sumSel > 0
                            ? (float)$pay->amount * ($svcPrice / $sumSel)
                            : (float)$pay->amount / count($svcs);
                        $svcPaidMap[$sn] = ($svcPaidMap[$sn] ?? 0) + $share;
                    }
                }
                // Agar xizmat belgilanmagan bo'lsa — proportional taqsimlash
                $hasTagged = !empty($svcPaidMap);
            @endphp
            @foreach($project->services as $svc)
            @php
                $svcPrice = (float) $svc->final_price;
                if ($hasTagged) {
                    $svcPaid = $svcPaidMap[$svc->service_name] ?? 0;
                } else {
                    $svcPaid = $totalPrice > 0 ? round($paidAmount * $svcPrice / $totalPrice) : 0;
                }
                $svcLabel = \App\Models\Project::serviceOptions()[$svc->service_name] ?? $svc->service_name;
            @endphp
            <tr>
                <td class="label" style="padding-left:14px;font-weight:600;color:#374151;">{{ $svcLabel }}</td>
                <td class="value" colspan="2" style="font-weight:700;">{{ number_format($svcPrice, 0, ',', ' ') }} UZS</td>
            </tr>
            <tr>
                <td class="label" style="padding-left:14px;font-weight:400;color:#6b7280;font-size:12px;border-top:none;">{{ $svcLabel }} To'langan:</td>
                <td class="value" colspan="2" style="color:{{ $svcPaid > 0 ? '#166534' : '#991b1b' }};font-weight:700;font-size:13px;border-top:none;">{{ number_format($svcPaid, 0, ',', ' ') }} UZS</td>
            </tr>
            @endforeach
        @endif
        <tr>
            <td class="label" id="lbl-total" style="font-weight:800;">Umumiy narx</td>
            <td class="value" colspan="2" style="font-size:15px;font-weight:700;">{{ number_format($project->total_price, 0, ',', ' ') }} UZS</td>
        </tr>
        @if($project->services && $project->services->count() > 0)
        <tr>
            <td class="label" style="font-weight:600;color:#374151;">Umumiy To'langan:</td>
            <td class="value" colspan="2" style="font-size:14px;font-weight:700;color:{{ $paidAmount > 0 ? '#166534' : '#991b1b' }};">{{ number_format($paidAmount, 0, ',', ' ') }} UZS</td>
        </tr>
        @endif
        <tr>
            <td class="label" id="lbl-paid">Oldindan to'lov</td>
            <td class="value" colspan="2" style="font-size:15px;font-weight:700;color:#166534;">{{ number_format($project->paid_amount, 0, ',', ' ') }} UZS</td>
        </tr>
        <tr>
            <td class="label" id="lbl-remaining">Qoldiq to'lov</td>
            <td class="value" colspan="2" style="font-size:15px;font-weight:700;color:#991b1b;">{{ number_format(max(0, $project->total_price - $project->paid_amount), 0, ',', ' ') }} UZS</td>
        </tr>
        <tr>
            <td class="label" id="lbl-note">Izohlar</td>
            <td class="value" colspan="2" style="min-height:44px;font-style:{{ $project->description ? 'normal' : 'italic' }};color:{{ $project->description ? '#111' : '#999' }};">
                {{ $project->description ?: '—' }}
            </td>
        </tr>
    </table>

    <!-- CONDITIONS: Uzbek -->
    <div class="conditions lang-uz active">
        <h3>{{ $project->number }} sonli shartnomaga ilova</h3>
        <p style="font-weight:700;margin-bottom:8px;font-size:13px;">Bildirishnoma</p>
        <p style="line-height:1.75;text-align:justify;">
            Men {{ $project->created_at->format('d.m.Y') }} yildagi
            <strong>№{{ $project->seq_no }}</strong> sonli obyektning loyiha hujjatlarini ishlab chiqish haqidagi
            shartnomaga ko'ra buyurtmachi (mulkdor)
            <strong>{{ $project->owner_name }}</strong>
            ushbu bildirishnoma bilan shuni ma'lum qilamanki, Vazirlar Mahkamasining 2026 yil 13 apreldagi
            167-son qarori bilan tasdiqlangan "Yakka tartibdagi uy-joylar hamda kichik hajmdagi noturar bino va
            inshootlarni qurish hamda rekonstruksiya qilish ishlari yuzasidan xususiy qurilish nazoratini amalga
            oshirish tartibi to'g'risidagi nizom"ning 1-bob 5-bandiga (Xususiy qurilish nazorati buyurtmachining
            (mulkdor) ixtiyori bilan amalga oshiriladi. Mazkur holatlarda buyurtmachi (mulkdor) xususiy qurilish
            nazoratini amalga oshirish zarurati mavjud emasligi to'g'risida obyektning loyiha hujjatlarini ishlab
            chiqqan loyiha tashkilotiga u bilan obyektning loyiha hujjatlarini ishlab chiqish bo'yicha
            shartnomani tuzish jarayonida yozma ravishda bildirishnomani taqdim etadi) muvofiq mazkur
            obyektning loyiha hujjatlarini ishlab chiqilishi bilan cheklanaman va buyurtmachi (mulkdor) sifatida
            loyiha tashkiloti tomonidan xususiy qurilish nazoratini amalga oshirish zarurati mavjud emasligini
            ma'lum qilaman.
        </p>
        <p style="margin-top:10px;line-height:1.7;color:#444;">
            Yuqoridagi bildirishnomani o'qib chiqdim, unga nisbatan e'tiroz va qo'shimchalarim yo'q.
            Kelib chiqadigan salbiy oqibatlar uchun javobgarlikni o'z zimmamda bo'lishidan xabardorman.
        </p>
    </div>

    <!-- CONDITIONS: Russian -->
    <div class="conditions lang-ru">
        <h3>Приложение к договору № {{ $project->number }}</h3>
        <p style="font-weight:700;margin-bottom:8px;font-size:12px;">Уведомление</p>
        <p style="line-height:1.75;text-align:justify;">
            Я, являясь Заказчиком (собственником) по договору № <strong>{{ $project->seq_no }}</strong>
            от {{ $project->created_at->format('d.m.Y') }} года на разработку проектной документации объекта
            <strong>{{ $project->owner_name }}</strong>,
            настоящим уведомлением сообщаю, что в соответствии с «Положением о порядке осуществления частного
            строительного контроля за работами по строительству и реконструкции индивидуального жилья, а также
            малогабаритных нежилых зданий и сооружений», утвержденным Постановлением Кабинета Министров от
            13 апреля 2026 года № 167, я ограничиваюсь лишь разработкой проектной документации данного объекта.
        </p>
        <p style="margin-top:10px;line-height:1.7;color:#444;">
            В качестве Заказчика (собственника) заявляю об отсутствии необходимости в осуществлении частного
            строительного контроля со стороны проектной организации.
        </p>
        <p style="margin-top:8px;line-height:1.7;color:#444;">
            С вышеуказанным уведомлением ознакомлен(а), возражений и дополнений не имею.
            Осведомлен(а) о принятии на себя ответственности за любые возможные негативные последствия.
        </p>
    </div>

    <!-- SIGNATURES -->
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-title" id="sig-company">Kompaniya vakili:</div>
            <div class="sig-line"></div>
            <div class="sig-label" id="sig-label-sign">Imzo / muhr</div>
            <div class="sig-name">{{ $project->assignedUsers->first()?->name ?: '________________' }}</div>
            <img src="/images/imzo.png" class="stamp-img" alt="">
        </div>
        <div class="sig-block sig-right">
            <div class="sig-title" id="sig-client">Buyurtmachi:</div>
            <div class="sig-line"></div>
            <div class="sig-label">Imzo</div>
            <div class="sig-name">{{ $project->owner_name }}</div>
            <div style="font-size:11px;color:#888;margin-top:3px;" id="sig-agree">shartlar bilan tanishib, rozilik bildirdi</div>
        </div>
    </div>

    <div class="footer-date">
        <span id="footer-created">Ariza tuzilgan sana:</span>
        {{ $project->created_at->format('d.m.Y H:i') }}
        &nbsp;|&nbsp;
        <span id="footer-printed">Chop etilgan:</span>
        {{ now()->format('d.m.Y H:i') }}
    </div>

</div><!-- /copy1 -->

<!-- ===== MIJOZ UCHUN 2-NUSXA (summalar yo'q) ===== -->
<div class="copy2">
    <div class="conditions conditions-plain lang-uz active">
        <div class="bild-title">BILDIRISHNOMA</div>
        <hr class="bild-title-line">
        <p style="line-height:1.75;text-align:justify;">
            Men {{ $project->created_at->format('d.m.Y') }} yildagi
            <strong>№{{ $project->seq_no }}</strong> sonli obyektning loyiha hujjatlarini ishlab chiqish haqidagi
            shartnomaga ko'ra buyurtmachi (mulkdor) <strong>{{ $project->owner_name }}</strong>
            ushbu bildirishnoma bilan shuni ma'lum qilamanki, Vazirlar Mahkamasining 2026 yil 13 apreldagi
            167-son qarori bilan tasdiqlangan "Yakka tartibdagi uy-joylar hamda kichik hajmdagi noturar bino va
            inshootlarni qurish hamda rekonstruksiya qilish ishlari yuzasidan xususiy qurilish nazoratini amalga
            oshirish tartibi to'g'risidagi nizom"ning 1-bob 5-bandiga (Xususiy qurilish nazorati buyurtmachining
            (mulkdor) ixtiyori bilan amalga oshiriladi. Mazkur holatlarda buyurtmachi (mulkdor) xususiy qurilish
            nazoratini amalga oshirish zarurati mavjud emasligi to'g'risida obyektning loyiha hujjatlarini ishlab
            chiqqan loyiha tashkilotiga u bilan obyektning loyiha hujjatlarini ishlab chiqish bo'yicha
            shartnomani tuzish jarayonida yozma ravishda bildirishnomani taqdim etadi) muvofiq mazkur
            obyektning loyiha hujjatlarini ishlab chiqilishi bilan cheklanaman va buyurtmachi (mulkdor) sifatida
            loyiha tashkiloti tomonidan xususiy qurilish nazoratini amalga oshirish zarurati mavjud emasligini
            ma'lum qilaman.
        </p>
        <p style="margin-top:10px;line-height:1.7;color:#444;">
            Yuqoridagi bildirishnomani o'qib chiqdim, unga nisbatan e'tiroz va qo'shimchalarim yo'q.
            Kelib chiqadigan salbiy oqibatlar uchun javobgarlikni o'z zimmamda bo'lishidan xabardorman.
        </p>
    </div>
    <div class="conditions conditions-plain lang-ru">
        <div class="bild-title">УВЕДОМЛЕНИЕ</div>
        <hr class="bild-title-line">
        <p style="line-height:1.75;text-align:justify;">
            Я, являясь Заказчиком (собственником) по договору № <strong>{{ $project->seq_no }}</strong>
            от {{ $project->created_at->format('d.m.Y') }} года на разработку проектной документации объекта
            <strong>{{ $project->owner_name }}</strong>, настоящим уведомлением сообщаю, что в соответствии с
            «Положением о порядке осуществления частного строительного контроля», утвержденным Постановлением
            Кабинета Министров от 13 апреля 2026 года № 167, я ограничиваюсь лишь разработкой проектной
            документации данного объекта.
        </p>
        <p style="margin-top:8px;line-height:1.7;color:#444;">
            В качестве Заказчика (собственника) заявляю об отсутствии необходимости в осуществлении частного
            строительного контроля со стороны проектной организации.
        </p>
        <p style="margin-top:8px;line-height:1.7;color:#444;">
            С вышеуказанным уведомлением ознакомлен(а), возражений и дополнений не имею.
            Осведомлен(а) о принятии на себя ответственности за любые возможные негативные последствия.
        </p>
    </div>

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-title">Direktor:</div>
            <div class="sig-line"></div>
            <div class="sig-label">Imzo / muhr</div>
            <div class="sig-name">Akimdjanov N.U</div>
            <img src="/images/imzo.png" class="stamp-img" alt="">
        </div>
        <div class="sig-block sig-right">
            <div class="sig-title">Mulkdor:</div>
            @if($project->signature_path)
            <div class="client-sig-wrap" id="clientSigWrap">
                <img src="{{ route('signature.view', $project) }}?t={{ time() }}" class="client-sig-img" id="clientSigImg" alt="">
                <div class="sig-rsz no-print" id="clientSigRsz" title="O'lcham"></div>
            </div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-label">Imzo</div>
            <div class="sig-name">{{ $project->owner_name }}</div>
            <div style="font-size:11px;color:#888;margin-top:3px;">shartlar bilan tanishib, rozilik bildirdi</div>
            <button type="button" class="no-print sig-btn" onclick="openSignPad()">✍️ Mijoz imzosi</button>
            @if($project->signature_path)
            <button type="button" class="no-print sig-btn" onclick="resetSigPos()" style="background:#f3f4f6;border-color:#d1d5db;color:#374151">↺ Joylashuvni tiklash</button>
            @endif
        </div>
    </div>

    <div class="footer-date">
        {{ $project->created_at->format('d.m.Y H:i') }}
        &nbsp;|&nbsp;
        {{ now()->format('d.m.Y H:i') }}
    </div>
</div><!-- /copy2 -->

</div><!-- /page -->

{{-- ✍️ Mijoz imzo chizish oynasi (to'liq ekran) --}}
<div class="sign-ov no-print" id="signOv">
    <div class="sign-box">
        <div class="sign-hd">✍️ Mijoz qo'l qo'ysin <span>— sichqoncha yoki barmoq bilan chizing</span></div>
        <canvas id="signCanvas"></canvas>
        <div class="sign-actions">
            <button class="s-clear" type="button" onclick="clearSign()">🧹 Tozalash</button>
            <span style="flex:1"></span>
            <button class="s-cancel" type="button" onclick="closeSignPad()">Bekor</button>
            <button class="s-done" type="button" onclick="finishSign()">✓ Saqlash</button>
        </div>
    </div>
</div>

<script>
const SIG_SAVE_URL = @json(route('signature.save', $project));
const CSRF_TOKEN    = document.querySelector('meta[name=csrf-token]').content;
let signCtx = null, signDrawn = false, signInit = false;

function openSignPad(){
    const c = document.getElementById('signCanvas');
    document.getElementById('signOv').style.display = 'flex';
    requestAnimationFrame(() => {
        const r = c.getBoundingClientRect();
        c.width  = Math.round(r.width);
        c.height = Math.round(r.height);
        signCtx = c.getContext('2d');
        signCtx.lineWidth = 4; signCtx.lineCap = 'round'; signCtx.lineJoin = 'round'; signCtx.strokeStyle = '#152a6e';
        clearSign();
        if (!signInit) { setupSignDraw(c); signInit = true; }
    });
}
function closeSignPad(){ document.getElementById('signOv').style.display = 'none'; }
function clearSign(){
    if (!signCtx) return;
    const c = document.getElementById('signCanvas');
    signCtx.clearRect(0, 0, c.width, c.height);
    signDrawn = false;
}
function setupSignDraw(c){
    let drawing = false, lx = 0, ly = 0;
    const pos = e => { const r = c.getBoundingClientRect(); const sx = c.width / r.width, sy = c.height / r.height;
        const p = e.touches ? e.touches[0] : e; return [(p.clientX - r.left) * sx, (p.clientY - r.top) * sy]; };
    const start = e => { drawing = true; [lx, ly] = pos(e); e.preventDefault(); };
    const move  = e => { if (!drawing) return; const [x, y] = pos(e); signCtx.beginPath(); signCtx.moveTo(lx, ly); signCtx.lineTo(x, y); signCtx.stroke(); lx = x; ly = y; signDrawn = true; e.preventDefault(); };
    const end   = () => { drawing = false; };
    c.addEventListener('mousedown', start); c.addEventListener('mousemove', move); window.addEventListener('mouseup', end);
    c.addEventListener('touchstart', start, {passive:false}); c.addEventListener('touchmove', move, {passive:false}); window.addEventListener('touchend', end);
}
// Imzo atrofidagi bo'sh (shaffof) joyni kesib tashlaydi — shunda imzo
// hujjatda chiziqqa yaqin, "bitta chiziqda" turadi (katta bo'sh maydonda
// suzib qolmaydi).
function cropSignature(c){
    const ctx = c.getContext('2d');
    const {width: w, height: h} = c;
    const data = ctx.getImageData(0, 0, w, h).data;
    let minX = w, minY = h, maxX = 0, maxY = 0, found = false;
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            if (data[(y * w + x) * 4 + 3] > 10) {
                found = true;
                if (x < minX) minX = x; if (x > maxX) maxX = x;
                if (y < minY) minY = y; if (y > maxY) maxY = y;
            }
        }
    }
    if (!found) return c;
    const pad = 8;
    minX = Math.max(0, minX - pad); minY = Math.max(0, minY - pad);
    maxX = Math.min(w - 1, maxX + pad); maxY = Math.min(h - 1, maxY + pad);
    const cw = maxX - minX + 1, ch = maxY - minY + 1;
    const out = document.createElement('canvas');
    out.width = cw; out.height = ch;
    out.getContext('2d').drawImage(c, minX, minY, cw, ch, 0, 0, cw, ch);
    return out;
}
async function finishSign(){
    if (!signDrawn) { alert("Avval mijoz qo'l qo'ysin"); return; }
    const c = document.getElementById('signCanvas');
    const dataUrl = cropSignature(c).toDataURL('image/png');
    try {
        const resp = await fetch(SIG_SAVE_URL, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN}, body: JSON.stringify({img: dataUrl})});
        const data = await resp.json();
        if (!data.ok) { alert('Xato: ' + (data.message || 'saqlanmadi')); return; }
        location.reload();
    } catch (e) { alert('Xatolik: ' + e.message); }
}

// ── Mijoz imzosini sudrab joylashtirish / o'lchamini o'zgartirish ──
// Joylashuv brauzerda (shu kompyuterda, shu loyiha uchun) eslab qolinadi.
const SIG_POS_KEY     = 'clientSigPos_' + {{ (int) $project->id }};
const SIG_POS_DEFAULT = {top: 10, left: 60, width: 240};

function applySigPos(pos){
    const wrap = document.getElementById('clientSigWrap');
    if (!wrap) return;
    wrap.style.top   = pos.top + 'px';
    wrap.style.left  = pos.left + 'px';
    wrap.style.width = pos.width + 'px';
}
function loadSigPos(){
    try { const raw = localStorage.getItem(SIG_POS_KEY); if (raw) return JSON.parse(raw); } catch (e) {}
    return null;
}
function saveSigPos(pos){
    try { localStorage.setItem(SIG_POS_KEY, JSON.stringify(pos)); } catch (e) {}
}
function resetSigPos(){
    try { localStorage.removeItem(SIG_POS_KEY); } catch (e) {}
    applySigPos(SIG_POS_DEFAULT);
}
function setupSigDrag(){
    const wrap = document.getElementById('clientSigWrap');
    if (!wrap) return;
    const rsz = document.getElementById('clientSigRsz');
    let mode = null, sx = 0, sy = 0, ol = 0, ot = 0, ow = 0;

    applySigPos(loadSigPos() || SIG_POS_DEFAULT);

    const posNow = () => ({
        top:   parseFloat(wrap.style.top)   || 0,
        left:  parseFloat(wrap.style.left)  || 0,
        width: parseFloat(wrap.style.width) || SIG_POS_DEFAULT.width,
    });
    const start = (x, y, isResize) => {
        mode = isResize ? 'resize' : 'move';
        sx = x; sy = y;
        const p = posNow(); ol = p.left; ot = p.top; ow = p.width;
    };
    const move = (x, y) => {
        if (!mode) return;
        if (mode === 'move') {
            wrap.style.left = (ol + (x - sx)) + 'px';
            wrap.style.top  = (ot + (y - sy)) + 'px';
        } else {
            wrap.style.width = Math.max(40, ow + (x - sx)) + 'px';
        }
    };
    const end = () => { if (mode) saveSigPos(posNow()); mode = null; };

    wrap.addEventListener('mousedown', e => { if (e.target === rsz) return; start(e.clientX, e.clientY, false); e.preventDefault(); });
    rsz.addEventListener('mousedown', e => { start(e.clientX, e.clientY, true); e.stopPropagation(); e.preventDefault(); });
    window.addEventListener('mousemove', e => move(e.clientX, e.clientY));
    window.addEventListener('mouseup', end);

    wrap.addEventListener('touchstart', e => { if (e.target === rsz) return; const t = e.touches[0]; start(t.clientX, t.clientY, false); }, {passive:true});
    rsz.addEventListener('touchstart', e => { const t = e.touches[0]; start(t.clientX, t.clientY, true); e.stopPropagation(); }, {passive:true});
    window.addEventListener('touchmove', e => { if (!mode) return; const t = e.touches[0]; move(t.clientX, t.clientY); }, {passive:true});
    window.addEventListener('touchend', end);
}
setupSigDrag();
</script>

<script>
const translations = {
    uz: {
        title: 'Qabul arizasi',
        ariza: 'Ariza',
        qrText: 'Buyurtma raqamini<br>skanerlang',
        lblClient: "Mijoz (F.I.Sh)",
        lblType: 'Loyiha turi',
        lblName: 'Loyiha nomi',
        lblAddress: "Ob'ekt manzili",
        lblWorker: "Mas'ul xodim",
        lblDeadline: 'Taxminiy muddat',
        lblTotal: 'Umumiy narx',
        lblPaid: "Oldindan to'lov",
        lblRemaining: "Qoldiq to'lov",
        lblNote: 'Izohlar',
        sigCompany: 'Kompaniya vakili:',
        sigClient: 'Buyurtmachi:',
        sigLabelSign: 'Imzo / muhr',
        sigAgree: "shartlar bilan tanishib, rozilik bildirdi",
        footerCreated: 'Ariza tuzilgan sana:',
        footerPrinted: 'Chop etilgan:',
    },
    ru: {
        title: 'Приёмная квитанция',
        ariza: 'Квитанция',
        qrText: 'Отсканируйте<br>номер заказа',
        lblClient: 'Клиент (Ф.И.О.)',
        lblType: 'Тип проекта',
        lblName: 'Название проекта',
        lblAddress: 'Адрес объекта',
        lblWorker: 'Ответственный',
        lblDeadline: 'Ориентировочный срок',
        lblTotal: 'Общая стоимость',
        lblPaid: 'Предоплата',
        lblRemaining: 'Остаток к оплате',
        lblNote: 'Примечания',
        sigCompany: 'Представитель компании:',
        sigClient: 'Заказчик:',
        sigLabelSign: 'Подпись / печать',
        sigAgree: 'ознакомлен с условиями и согласен',
        footerCreated: 'Дата составления:',
        footerPrinted: 'Дата печати:',
    }
};

function setLang(lang) {
    const t = translations[lang];

    document.getElementById('title-text').textContent = t.title;
    document.getElementById('label-ariza').textContent = t.ariza;
    document.getElementById('qr-text').innerHTML = t.qrText;
    document.getElementById('lbl-client').textContent = t.lblClient;
    document.getElementById('lbl-type').textContent = t.lblType;
    document.getElementById('lbl-name').textContent = t.lblName;
    document.getElementById('lbl-address').textContent = t.lblAddress;
    document.getElementById('lbl-worker').textContent = t.lblWorker;
    document.getElementById('lbl-deadline').textContent = t.lblDeadline;
    document.getElementById('lbl-total').textContent = t.lblTotal;
    document.getElementById('lbl-paid').textContent = t.lblPaid;
    document.getElementById('lbl-remaining').textContent = t.lblRemaining;
    document.getElementById('lbl-note').textContent = t.lblNote;
    document.getElementById('sig-company').textContent = t.sigCompany;
    document.getElementById('sig-client').textContent = t.sigClient;
    document.getElementById('sig-label-sign').textContent = t.sigLabelSign;
    document.getElementById('sig-agree').textContent = t.sigAgree;
    document.getElementById('footer-created').textContent = t.footerCreated;
    document.getElementById('footer-printed').textContent = t.footerPrinted;

    // Show/hide conditions and category spans
    document.querySelectorAll('.lang-uz').forEach(el => el.classList.toggle('active', lang === 'uz'));
    document.querySelectorAll('.lang-ru').forEach(el => el.classList.toggle('active', lang === 'ru'));

    // Button styles
    const btnUz = document.getElementById('btn-uz');
    const btnRu = document.getElementById('btn-ru');
    if (lang === 'uz') {
        btnUz.style.cssText = 'background:#fff;color:#1d4ed8;border:none;padding:6px 18px;border-radius:5px;font-size:13px;font-weight:700;cursor:pointer;';
        btnRu.style.cssText = 'background:transparent;color:#fff;border:none;padding:6px 18px;border-radius:5px;font-size:13px;font-weight:600;cursor:pointer;';
    } else {
        btnRu.style.cssText = 'background:#fff;color:#1d4ed8;border:none;padding:6px 18px;border-radius:5px;font-size:13px;font-weight:700;cursor:pointer;';
        btnUz.style.cssText = 'background:transparent;color:#fff;border:none;padding:6px 18px;border-radius:5px;font-size:13px;font-weight:600;cursor:pointer;';
    }
}

function viewMode(mode) {
    document.body.classList.remove('view-firma', 'view-mijoz');
    document.body.classList.add('view-' + mode);
    document.getElementById('btn-view-firma').classList.toggle('active', mode === 'firma');
    document.getElementById('btn-view-mijoz').classList.toggle('active', mode === 'mijoz');
    // URL'ga yozib qo'yamiz — imzo saqlangach sahifa qayta yuklanganda ham
    // shu ko'rinish (Mijoz nusxasi) saqlanib qolishi uchun.
    const url = new URL(location.href);
    url.searchParams.set('view', mode);
    history.replaceState(null, '', url);
}
// Sahifa ochilganda — URL'dagi ?view= parametriga qarab boshlang'ich ko'rinish
viewMode(new URLSearchParams(location.search).get('view') === 'mijoz' ? 'mijoz' : 'firma');
</script>
</body>
</html>
