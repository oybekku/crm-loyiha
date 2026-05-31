<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Qabul arizasi — {{ $project->number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 14px; color: #000; background: #fff; }

    .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 14mm 14mm 12mm; display: flex; flex-direction: column; }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 3px solid #1d4ed8;
    }
    .header-left h1 { font-size: 30px; font-weight: 900; color: #1d4ed8; margin-bottom: 4px; letter-spacing: -0.5px; }
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
    .main-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .main-table td {
        border: 1.5px solid #c8d0db;
        padding: 9px 12px;
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
        padding: 12px 14px;
        margin-bottom: 14px;
        font-size: 11.5px;
        line-height: 1.65;
        color: #222;
        background: #fafbfc;
    }
    .conditions h3 {
        font-size: 12.5px;
        font-weight: 800;
        margin-bottom: 7px;
        color: #1d4ed8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .conditions ol { padding-left: 18px; }
    .conditions li { margin-bottom: 3px; }

    /* Signatures */
    .signatures {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        margin-top: auto;
        padding-top: 10px;
    }
    .sig-block { flex: 1; }
    .sig-title { font-size: 13px; font-weight: 700; margin-bottom: 30px; }
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
    <button onclick="window.print()" style="background:#fff;color:#1d4ed8;border:none;padding:8px 28px;border-radius:6px;font-size:14px;cursor:pointer;font-weight:700;">
        🖨 Chop etish
    </button>
    <button onclick="window.close()" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:8px 18px;border-radius:6px;font-size:14px;cursor:pointer;">
        ✕ Yopish
    </button>
</div>

<div class="page">

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
        <h3>Xizmat ko'rsatish shartlari</h3>
        <ol>
            <li>Mijoz tavakkalchilikni o'z bo'yniga oladi: agar qurilmadan foydalanish shartlari qo'pol ravishda buzilgan bo'lsa, ichiga tok o'tkazuvchi suyuqlik tushgan bo'lsa (korroziya) yoki mexanik shikastlanishlar bo'lsa, ta'mirlash jarayonida qurilmaning to'liq yoki qisman ishlamay qolishi xavfi uchun mijoz javobgar bo'ladi.</li>
            <li>Ma'lumotlar va sim-kartalar: servis markazi qurilma xotirasidagi ma'lumotlarning ehtimoliy yo'qolishi, shuningdek, ichida qolib ketgan Sim va Flash-kartalar uchun javobgarlikni o'z zimmasiga olmaydi.</li>
            <li>Saqlash muddati: qurilmani saqlash muddati tayyor bo'lishi taxmin qilingan sanadan boshlab <strong>30 kun</strong>ni tashkil etadi. Ushbu muddatdan keyin apparat utilizatsiya qilinadi va u bo'yicha e'tirozlar qabul qilinmaydi.</li>
            <li>Diagnostika va ta'mirlash hajmi: ijrochi faqatgina mijoz tomonidan bildirilgan nosozlikni diagnostika qilish va ta'mirlash majburiyatini oladi. Vaqt ijrochining ish hajmiga bog'liq va diagnostikadan keyin aniqlanadi.</li>
            <li>Kvitansiya yo'qolsa: kvitansiya yo'qolgan taqdirda, qurilma mijozning pasporti taqdim etilganda topshiriladi.</li>
            <li>Ehtiyot qismlar yetishmovchiligi: ehtiyot qismlar, materiallar yoki texnik hujjatlar bo'lmagan taqdirda, ijrochi bir tomonlama tartibda ta'mirlashni rad etish huquqiga ega.</li>
            <li>Narxlar bilan tanishish: mijoz ushbu shartnoma tuzilgunga qadar ijrochining narxlar ro'yxati bilan tanishishi va zarurat bo'lganda xizmatlar narxini aniqlashtirib olishi kerak.</li>
            <li>Narx va muddatning o'zgarishi: qabul vaqtida faqat taxminiy narx va muddat kelishiladi. Agar qo'shimcha ishlar, materiallar talab etilsa yoki yangi nuqsonlar aniqlansa — ijrochi mijozni ogohlantirishga majbur. Mijoz rad etsa, bajarilgan ishlar va ehtiyot qismlar qiymatini to'laydi.</li>
            <li>Kafolat cheklovlari: suyuqlik tekkan va mexanik shikastlanishlardan keyin ta'mirlangan apparatlarga kafolat berilmaydi.</li>
            <li>Kafolat doirasi: kafolat faqat almashtirilgan yoki ta'mirlangan qismning o'zigagina amal qiladi.</li>
        </ol>
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
            <div class="sig-line"></div>
            <div class="sig-label" id="sig-label-sign">Imzo / muhr</div>
            <div class="sig-name">{{ $project->assignedUsers->first()?->name ?: '________________' }}</div>
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

</div>

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
</script>
</body>
</html>
