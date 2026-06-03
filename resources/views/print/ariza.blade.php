<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Qabul arizasi — {{ $project->number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #000; background: #fff; }

    .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 10mm 12mm 8mm; display: flex; flex-direction: column; }

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
        margin-top: auto;
        padding-top: 10px;
    }
    .sig-block { flex: 1; position: relative; }
    .stamp-img {
        position: absolute;
        bottom: -10px;
        left: -10px;
        width: 280px;
        opacity: 0.92;
        pointer-events: none;
    }
    .sig-title { font-size: 13px; font-weight: 700; margin-bottom: 20px; }
    .sig-line { border-bottom: 1.5px solid #000; margin-bottom: 6px; }
    .sig-label { font-size: 11px; color: #666; }
    .sig-name { font-size: 14px; font-weight: 700; margin-top: 4px; }
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

    /* Print modes */
    @media print {
        body.print-copy1 .copy2 { display: none !important; }
        body.print-copy1 .copy-divider-wrap { display: none !important; }
        body.print-copy2 .copy1 { display: none !important; }
        body.print-copy2 .copy-divider-wrap { display: none !important; }
    }

    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { padding: 10mm 12mm; }
        .no-print { display: none !important; }
    }

    @page { size: A4; margin: 0; }
</style>
</head>
<body>

<!-- Toolbar -->
<div class="no-print" style="text-align:center;padding:10px 16px;background:#1e40af;border-bottom:1px solid #1d4ed8;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:center;gap:12px;">
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
    <button onclick="printCopy(1)" style="background:#fff;color:#1d4ed8;border:none;padding:8px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        🖨 Firma nusxasi
    </button>
    <button onclick="printCopy(2)" style="background:#22c55e;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        🖨 Mijoz nusxasi
    </button>
    <button onclick="printCopy(0)" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;">
        🖨 Ikkalasi
    </button>
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
        <tr>
            <td class="label" id="lbl-total">Umumiy narx</td>
            <td class="value" style="font-size:15px;font-weight:700;">{{ number_format($project->total_price, 0, ',', ' ') }} UZS</td>
        </tr>
        <tr>
            <td class="label" id="lbl-paid">Oldindan to'lov</td>
            <td class="value" style="font-size:15px;font-weight:700;color:#166534;">{{ number_format($project->paid_amount, 0, ',', ' ') }} UZS</td>
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
            <strong>{{ $project->number }}</strong> sonli obyektning loyiha hujjatlarini ishlab chiqish haqidagi
            shartnomaga ko'ra buyurtmachi (mulkdor)
            <strong>{{ $project->owner_name }}</strong>
            ushbu bildirishnoma bilan shuni ma'lum qilamanki, Vazirlar Mahkamasining 2026 yil 13 apreldagi
            167-son qarori bilan tasdiqlangan "Yakka tartibdagi uy-joylar hamda kichik hajmdagi noturar bino va
            inshootlarni qurish hamda rekonstruksiya qilish ishlari yuzasidan xususiy qurilish nazoratini amalga
            oshirish tartibi to'g'risidagi nizom"ga muvofiq mazkur obyektning loyiha hujjatlarini ishlab
            chiqilishi bilan cheklanaman va buyurtmachi (mulkdor) sifatida loyiha tashkiloti tomonidan xususiy
            qurilish nazoratini amalga oshirish zarurati mavjud emasligini ma'lum qilaman.
        </p>
        <p style="margin-top:10px;line-height:1.7;color:#444;">
            Yuqoridagi bildirishnomani o'qib chiqdim, unga nisbatan e'tiroz va qo'shimchalarim yo'q.
            Kelib chifadigan salbiy oqibatlar uchun javobgarlikni o'z zimmamda bo'lishidan xabardorman.
        </p>
    </div>

    <!-- CONDITIONS: Russian -->
    <div class="conditions lang-ru">
        <h3>Условия оказания услуг</h3>
        <ol>
            <li>Клиент принимает на себя риск возможной полной или частичной утраты работоспособности устройства в процессе ремонта, в случае грубых нарушений условий эксплуатации, наличии следов попадания токопроводящей жидкости (коррозии) либо механических повреждений.</li>
            <li>Сервисный центр не несёт ответственности за возможную потерю данных в памяти устройства, а также за оставленные Sim и Flash-карты.</li>
            <li>Аппарат принимается на ответственное хранение на весь срок обслуживания. Срок хранения — <strong>30 дней</strong> с ориентировочной даты готовности. После данного срока аппарат утилизируется и претензии не принимаются.</li>
            <li>Исполнитель обязуется произвести диагностику и ремонт исключительно заявленной неисправности. Время на диагностику и ремонт зависит от загруженности Исполнителя и определяется после диагностики.</li>
            <li>В случае утери квитанции устройство выдаётся по предъявлению паспорта заказчика.</li>
            <li>В случае отсутствия запчастей, материалов или тех. документации, Исполнитель вправе в одностороннем порядке отказаться от проведения ремонта.</li>
            <li>Клиент должен ознакомиться с прейскурантом Исполнителя до заключения договора и при необходимости уточнить стоимость услуг.</li>
            <li>При приёмке оговариваются только приблизительная стоимость и срок ремонта. Они могут быть пересмотрены, если потребуются дополнительные работы/материалы или будут выявлены новые дефекты. Исполнитель обязан уведомить Клиента. В случае отказа — Клиент оплачивает уже выполненные работы и использованные запчасти.</li>
            <li>Гарантия не распространяется на аппараты с попаданием жидкости и механическими повреждениями.</li>
            <li>Гарантия распространяется только на замененную или отремонтированную деталь.</li>
        </ol>
    </div>

    <!-- SIGNATURES -->
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-title" id="sig-company">Kompaniya vakili:</div>
            <div class="sig-line" style="margin-top:30px;"></div>
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

    <!-- ===== 2-NUSXA ===== -->
</div><!-- /copy1 -->

<div class="copy-divider-wrap" style="position:relative;margin:12px 0 10px;">
    <hr class="copy-divider">
    <span class="copy-label">✂ &nbsp; 2-nusxa (mijoz uchun) &nbsp; ✂</span>
</div>

<div class="copy2">
    <!-- 2-nusxa header -->
    <div class="header">
        <div class="header-left">
            <h1 id="title-text2">Qabul arizasi</h1>
            <div class="order-num">
                <span id="label-ariza2">Ariza</span>
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

    <!-- 2-nusxa jadval — summalar YO'Q -->
    <table class="main-table">
        <tr>
            <td class="label" id="lbl-client2">Mijoz (F.I.Sh)</td>
            <td class="value" colspan="2">
                <strong>{{ $project->owner_name }}</strong>
                @if($project->phones)
                    @foreach($project->phones as $phone)
                        &nbsp;&nbsp;{{ is_array($phone) ? ($phone['phone'] ?? '') : $phone }}@if(!$loop->last),@endif
                    @endforeach
                @endif
            </td>
        </tr>
        <tr>
            <td class="label" id="lbl-type2">Loyiha turi</td>
            <td class="value" colspan="2">
                @php
                    $cats2 = ['turar'=>'Turar-joy','tijorat'=>'Tijorat binosi','qishloq'=>'Qishloq qurilishi','sanoat'=>'Sanoat binosi','boshqa'=>'Boshqa'];
                    $catsRu2 = ['turar'=>'Жилое здание','tijorat'=>'Коммерческое здание','qishloq'=>'Сельское строительство','sanoat'=>'Промышленное здание','boshqa'=>'Другое'];
                @endphp
                <span class="lang-uz active">{{ $cats2[$project->category] ?? $project->category }}</span>
                <span class="lang-ru">{{ $catsRu2[$project->category] ?? $project->category }}</span>
            </td>
        </tr>
        <tr>
            <td class="label" id="lbl-name2">Loyiha nomi</td>
            <td class="value" colspan="2">{{ $project->title ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-address2">Ob'ekt manzili</td>
            <td class="value" colspan="2">{{ $project->address }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-worker2">Mas'ul xodim</td>
            <td class="value" colspan="2">{{ $project->assignedUsers->pluck('name')->join(', ') ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-deadline2">Taxminiy muddat</td>
            <td class="value" colspan="2">{{ $project->deadline_date ? $project->deadline_date->format('d.m.Y') : '—' }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-note2">Izohlar</td>
            <td class="value" colspan="2" style="min-height:44px;font-style:{{ $project->description ? 'normal' : 'italic' }};color:{{ $project->description ? '#111' : '#999' }};">
                {{ $project->description ?: '—' }}
            </td>
        </tr>
    </table>

    <!-- 2-nusxa shartlar -->
    <div class="conditions lang-uz active" id="cond2-uz">
        <h3>{{ $project->number }} sonli shartnomaga ilova</h3>
        <p style="font-weight:700;margin-bottom:8px;font-size:14px;">Bildirishnoma</p>
        <p style="line-height:1.75;text-align:justify;">
            Men {{ $project->created_at->format('d.m.Y') }} yildagi
            <strong>{{ $project->number }}</strong> sonli obyektning loyiha hujjatlarini ishlab chiqish haqidagi
            shartnomaga ko'ra buyurtmachi (mulkdor)
            <strong>{{ $project->owner_name }}</strong>
            ushbu bildirishnoma bilan shuni ma'lum qilamanki, Vazirlar Mahkamasining 2026 yil 13 apreldagi
            167-son qarori bilan tasdiqlangan "Yakka tartibdagi uy-joylar hamda kichik hajmdagi noturar bino va
            inshootlarni qurish hamda rekonstruksiya qilish ishlari yuzasidan xususiy qurilish nazoratini amalga
            oshirish tartibi to'g'risidagi nizom"ga muvofiq mazkur obyektning loyiha hujjatlarini ishlab
            chiqilishi bilan cheklanaman va buyurtmachi (mulkdor) sifatida loyiha tashkiloti tomonidan xususiy
            qurilish nazoratini amalga oshirish zarurati mavjud emasligini ma'lum qilaman.
        </p>
        <p style="margin-top:10px;line-height:1.7;color:#444;">
            Yuqoridagi bildirishnomani o'qib chiqdim, unga nisbatan e'tiroz va qo'shimchalarim yo'q.
            Kelib chifadigan salbiy oqibatlar uchun javobgarlikni o'z zimmamda bo'lishidan xabardorman.
        </p>
    </div>
    <div class="conditions lang-ru" id="cond2-ru">
        <h3>Условия оказания услуг</h3>
        <ol>
            <li>Клиент принимает на себя риск возможной полной или частичной утраты работоспособности устройства в процессе ремонта, в случае грубых нарушений условий эксплуатации, наличии следов попадания токопроводящей жидкости (коррозии) либо механических повреждений.</li>
            <li>Сервисный центр не несёт ответственности за возможную потерю данных в памяти устройства, а также за оставленные Sim и Flash-карты.</li>
            <li>Аппарат принимается на ответственное хранение на весь срок обслуживания. Срок хранения — <strong>30 дней</strong> с ориентировочной даты готовности. После данного срока аппарат утилизируется и претензии не принимаются.</li>
            <li>Исполнитель обязуется произвести диагностику и ремонт исключительно заявленной неисправности.</li>
            <li>В случае утери квитанции устройство выдаётся по предъявлению паспорта заказчика.</li>
            <li>Гарантия не распространяется на аппараты с попаданием жидкости и механическими повреждениями.</li>
        </ol>
    </div>

    <!-- 2-nusxa imzolar -->
    <div class="signatures">
        <div class="sig-block">
            <div class="sig-title" id="sig-company2">Kompaniya vakili:</div>
            <div class="sig-line" style="margin-top:30px;"></div>
            <div class="sig-label" id="sig-label-sign2">Imzo / muhr</div>
            <div class="sig-name">{{ $project->assignedUsers->first()?->name ?: '________________' }}</div>
            <img src="/images/imzo.png" class="stamp-img" alt="">
        </div>
        <div class="sig-block sig-right">
            <div class="sig-title" id="sig-client2">Buyurtmachi:</div>
            <div class="sig-line"></div>
            <div class="sig-label">Imzo</div>
            <div class="sig-name">{{ $project->owner_name }}</div>
            <div style="font-size:11px;color:#888;margin-top:3px;" id="sig-agree2">shartlar bilan tanishib, rozilik bildirdi</div>
        </div>
    </div>

    <div class="footer-date">
        {{ $project->created_at->format('d.m.Y H:i') }}
        &nbsp;|&nbsp;
        {{ now()->format('d.m.Y H:i') }}
    </div>

</div><!-- /copy2 -->

</div><!-- /page -->

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

function printCopy(copy) {
    document.body.classList.remove('print-copy1', 'print-copy2');
    if (copy === 1) document.body.classList.add('print-copy1');
    if (copy === 2) document.body.classList.add('print-copy2');
    window.print();
    document.body.classList.remove('print-copy1', 'print-copy2');
}
</script>
</body>
</html>
