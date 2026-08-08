<?php
    $sana      = $project->created_at ? $project->created_at : now();
    $oylar     = [1=>'yanvar',2=>'fevral',3=>'mart',4=>'aprel',5=>'may',6=>'iyun',7=>'iyul',8=>'avgust',9=>'sentabr',10=>'oktabr',11=>'noyabr',12=>'dekabr'];
    $sanaText  = $sana->format('d') . '.' . $sana->format('m') . '.' . $sana->format('Y') . ' yil';
    $kun       = $sana->format('d');
    $oyNomi    = $oylar[(int) $sana->format('n')];
    $yil       = $sana->format('Y');
    $raqam     = $project->seq_no ?: $project->id;
    $narx      = '250 000,00';
    $narxSozda = "Ikki yuz ellik ming so'm";
    $ism       = $project->owner_name ?? '';
    $manzil    = $project->address ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: "DejaVu Sans", sans-serif; font-size: 12px; line-height: 1.4; color: #000; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    h1 { font-size: 16px; text-align: center; margin: 0 0 4px 0; }
    .subtitle { text-align: center; font-size: 12px; margin: 0 0 10px 0; }
    .dateline { text-align: right; margin-bottom: 10px; }
    p { text-align: justify; margin: 0 0 8px 0; }
    .section-title { font-weight: bold; text-align: center; margin: 14px 0 8px 0; }
    table.req { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.req td { width: 50%; vertical-align: top; padding: 6px; border: 1px solid #000; font-size: 11px; }
    table.req .label { font-weight: bold; text-align: center; }
    .page-break { page-break-before: always; }
    .blank { border-bottom: 1px solid #000; display: inline-block; min-width: 160px; }
    table.sign { width: 100%; border-collapse: collapse; margin-top: 16px; }
    table.sign td { width: 50%; text-align: center; vertical-align: top; padding: 6px; font-size: 11px; }
</style>
</head>
<body>

<h1>Shartnoma &#8470;{{ $raqam }}</h1>
<p class="subtitle">Loyihaviy, qidiruv va ilmiy-texnik mahsulot tushunchasiga kiruvchi boshqa dizaynerlik xizmatlarini bajarish to'g'risida</p>
<p class="dateline">Toshkent sh. {{ $sanaText }}</p>

<p>MChJ «MY PERFEKT HOME» (keyingi o'rinlarda «IJROCHI» deb yuritiladi), Ustav va 26-iyul 2021-yildagi AL-001868-sonli Litsenziya asosida harakat qiluvchi direktor J.Sh.Sarimsakov shaxsida bir tomondan va {{ $ism }} (keyingi o'rinlarda «BUYURTMACHI» deb yuritiladi), pasport asosida harakat qiluvchi jismoniy shaxs, ikkinchi tomondan, quyidagilar to'g'risida ushbu shartnomani tuzdilar:</p>

<div class="section-title">1. SHARTNOMA PREDMETI</div>
<p>«BUYURTMACHI» quyidagi xizmatlarni ishlab chiqishni «IJROCHI» zimmasiga yuklaydi va «IJROCHI» ushbu majburiyatlarni qabul qiladi: {{ $manzil }} manzili bo'yicha loyihalash va hujjatlarni hamrohlik qilish xizmatlari.</p>
<p>Ishning mazmuni, qiymati va bajarilish muddatlari Shartnomaga ajralmas qism hisoblanadigan loyihalash topshirig'i hamda shartnomaviy narx bayonnomasi orqali belgilanadi.</p>
<p>Ilmiy-texnik dizaynerlik mahsulotidan «BUYURTMACHI» tomonidan faqat bevosita maqsadda foydalaniladi. Ushbu mahsulotni «IJROCHI»ning yozma roziligisiz uchinchi shaxslarga berish yoki boshqa obyekt uchun foydalanish taqiqlanadi.</p>

<div class="section-title">2. SHARTNOMA QIYMATI VA HISOB-KITOB QILISH TARTIBI</div>
<p>2.1. Shartnoma narxi kelishuv bayonnomasiga muvofiq tomonlar tomonidan {{ $narx }} ({{ $narxSozda }}, QQSiz) miqdorida belgilangan.</p>
<p>2.2. Ushbu Shartnomaga muvofiq ilmiy-texnik mahsulot tushunchasiga kiruvchi ish va xizmatlarni bajargani uchun «BUYURTMACHI» shartnomaviy narx kelishuv bayonnomasiga muvofiq {{ $narx }} ({{ $narxSozda }}, QQSiz) miqdordagi mablag'ni 10 (o'n) bank kuni mobaynida «IJROCHI»ga o'tkazib beradi. Shartnomaviy narx kelishuv bayonnomasi ushbu shartnomaning ajralmas qismi hisoblanadi.</p>
<p>2.3. «BUYURTMACHI» shartnoma imzolangandan keyin 10 kunlik muddat ichida Shartnoma summasining 100% miqdorida, ya'ni {{ $narx }} ({{ $narxSozda }}, QQSiz) miqdoridagi avans oldindan to'lovini «IJROCHI»ning hisob raqamiga o'tkazib berish majburiyatini oladi.</p>
<p>2.4. «IJROCHI» loyiha ishlarini ishlab chiqishga faqatgina avans mablag'i uning hisob raqamiga tushganidan keyingina kirishadi.</p>

<div class="section-title">3. ISHLARNI TOPSHIRISH VA QABUL QILISH TARTIBI</div>
<p>3.1. Ishlar tugallangandan so'ng «IJROCHI» «BUYURTMACHI»ga Shartnoma shartlarida nazarda tutilgan ilmiy-texnik va boshqa hujjatlar to'plamini ilova qilgan holda, ilmiy-texnik hujjatlarni topshirish-qabul qilish dalolatnomasini taqdim etadi.</p>
<p>3.2. «BUYURTMACHI» topshirish-qabul qilish dalolatnomasi va ushbu Shartnomaning 3.1-bandida ko'rsatilgan unga ilova qilingan hujjatlarni olgan kundan boshlab 5 kun ichida «IJROCHI»ga imzolangan ishni topshirish-qabul qilish dalolatnomasini yoki ishni qabul qilishdan asoslantirilgan rad javobini yuborishi shart.</p>
<p>3.3. «BUYURTMACHI» tomonidan asoslantirilgan rad javobi berilgan taqdirda, tomonlar 10 kunlik muddat ichida tomonlarning mas'ul vakillarining imzolari bilan rasmiylashtirilgan, zarur kamchiliklarni bartaraf etish ro'yxati va ularni bajarish muddatlari ko'rsatilgan ikki tomonlama dalolatnoma tuzadilar.</p>
<p>3.4. Agar ishlar va xizmatlarni bajarish jarayonida uni keyinchalik davom ettirish maqsadga muvofiq emasligi aniqlansa, «IJROCHI» bu haqda «BUYURTMACHI»ni xabardor qilgan holda ularni to'xtatib turishga majburdir. «BUYURTMACHI» 5 kunlik muddat ichida ishni to'xtatib turish masalasini ko'rib chiqishi shart. Bunday holda tomonlar 10 kunlik muddat ichida xizmatlarni davom ettirish maqsadga muvofiqligi yoki ishni to'xtatish va o'zaro hisob-kitob qilish dalolatnomalarini tuzish masalasini ko'rib chiqishga majburdirlar.</p>

<div class="section-title">4. TOMONLARNING JAVOBGARLIGI</div>
<p>4.1. «IJROCHI» va «BUYURTMACHI» ushbu Shartnomaning majburiyatlarini bajarmaganlik yoki lozim darajada bajarmaganlik uchun amaldagi qonunchilikka muvofiq va shartnoma summasidan oshmaydigan miqdorda mulkiy javobgarlikni o'z zimmalariga oladilar.</p>
<p>4.2. Shartnoma bo'yicha o'z majburiyatlarini vicdonan bajarmaganlik uchun aybdor tomonga O'zbekiston Respublikasining 1998-yil 29-avgustdagi «Xo'jalik yurituvchi subyektlar faoliyatining shartnomaviy-huquqiy bazasi to'g'risida»gi Qonunida nazarda tutilgan normalar va sanksiyalar qo'llaniladi.</p>
<p>4.3. «BUYURTMACHI» taqdim etilayotgan dastlabki ma'lumotlarning to'liqligi va haqqoniyligi uchun javobgardir.</p>
<p>4.4. «IJROCHI» loyihalash topshirig'ida ko'rsatilgan shartlarning bajarilishi uchun javobgardir.</p>

<div class="section-title">5. BOSHQA SHARTLAR</div>
<p>5.1. Shartnoma «IJROCHI» tomonidan avans mablag'i o'tkazilganligi haqidagi xabarnoma olingandan va «BUYURTMACHI» tomonidan dastlabki ma'lumotlar taqdim etilgandan keyin kuchga kiradi.</p>
<p>5.2. Yuqorida ko'rsatilgan muddatda (2.2-band) avans mablag'i tushmagan taqdirda, «IJROCHI» Shartnomani bir tomonlama tartibda bekor qilishga haqlidir.</p>
<p>5.3. Boshqa hollarda Shartnoma tomonlarning kelishuviga ko'ra yoki sud qaroriga binoan o'zgartirilishi yoki bekor qilinishi mumkin.</p>
<p>5.4. Agar «BUYURTMACHI» Shartnomani to'xtatsa, u ishlar to'xtatilgan kunga qadar bajarilgan ishlar hajmini to'lab berishga majburdir.</p>
<p>5.5. Qo'shimcha xarajatlarni keltirib chiqaradigan dizayn xizmatlari hajmi oshgan taqdirda, «BUYURTMACHI» qo'shimcha to'lovni amalga oshiradi.</p>
<p>5.6. Shartnoma bo'yicha ishlar (xizmatlar)ni bajarish uchun zarur bo'lgan dastlabki ma'lumotlarni taqdim etish 20 kundan ortiq muddatga kechiktirilganda, «IJROCHI» «BUYURTMACHI»ga ishni to'xtatish dalolatnomasini taqdim etgan holda Shartnomani bir tomonlama tartibda bekor qilishga haqlidir.</p>
<p>5.7. Shartnoma narxi ochiq hisoblanadi va energiya resurslari, materiallar, mehnat haqi shartlari hamda bazaviy narxda hisobga olinmagan boshqa xarajatlar narxlari o'zgarganda aniqlashtirilishi mumkin.</p>
<p>5.8. Bajarilgan ishlar o'z vaqtida to'lanmaganda, «BUYURTMACHI» tomonidan ushbu Shartnoma bo'yicha kelishilgan narx hamda to'lov vaqtidagi indeksatsiya qilingan narx o'rtasidagi farqqa teng qo'shimcha to'lov amalga oshiriladi.</p>
<p>5.9. «BUYURTMACHI» tomonidan ishlab chiqilgan dizayn hujjatlarini «IJROCHI»ning roziligisiz boshqalarga berish taqiqlanadi.</p>
<p>5.10. Ushbu shartnoma bo'yicha barcha nizolar va kelishmovchiliklar tomonlar o'rtasida muzokaralar olib borish yo'li bilan hal etiladi. Kelishuvga erishilmagan taqdirda, har qanday tomon buzilgan huquqlarini himoya qilish uchun sud organlariga murojaat qilishga haqli.</p>
<p>5.11. Ushbu Shartnomaning amal qilish muddati u imzolangan paytdan boshlab tomonlar Shartnoma bo'yicha qabul qilingan majburiyatlarni to'liq bajarguniga qadar etib belgilanadi.</p>

<div class="section-title">6. FORS-MAJOR HOLATLARI</div>
<p>6.1. Tomonlar fors-major holatlari (tabiiy ofatlar, suv toshqinlari, yong'inlar, shuningdek, davlat organlarining hujjatlari, harbiy harakatlar) yuzaga kelganda javobgar bo'lmaydilar. Fors-major holatlari yuzaga kelganda tomonlar bunday holatlar boshlangan paytdan e'tiboran 3 kun ichida bir-birlarini xabardor qilishga majburdirlar.</p>
<p>6.2. Fors-major holatlari bo'yicha yuqorida ko'rsatilgan shartlar bajarilmagan taqdirda, tomonlar ushbu shartnoma bo'yicha javobgarlikdan ozod qilinmaydilar. Shartnomaning 6.1-bandida oldindan ko'rsatilgan fors-major holatlari muddati tugaganidan so'ng, Ijrochi ishlarni yakunlash va shartnoma bo'yicha qabul qilingan majburiyatlarni oxiriga yetkazishga majburdir. Shartnoma muddati fors-major holatlari amal qilgan davrga muvofiq uzaytiriladi.</p>
<p>6.3. Fors-major tufayli o'z majburiyatlarini uch oy mobaynida bajarish imkoniyati bo'lmagan taqdirda, ushbu shartnoma bajarilgan ishlar dalolatnomasini tuzgan holda tomonlardan har qanday birining tashabbusi bilan bekor qilinishi mumkin.</p>

<div class="section-title">7. YAKUNIY QOIDALAR</div>
<p>7.1. Ushbu shartnoma bir xil yuridik kuchga ega bo'lgan ikki nusxada, har bir tomon uchun bir nusxadan tuzildi. Ushbu shartnomaga shartnomaviy narx kelishuv bayonnomasi ilova qilinadi.</p>

<div class="section-title">8. TOMONLARNING MANZILLARI VA REKVIZITLARI</div>
<table class="req">
<tr>
<td>
<div class="label">I J R O C H I:</div>
<p style="text-align:center">“MY PERFEKT HOME” MCHJ</p>
<p>Manzil: Toshkent shahri, Yangihayot tumani Uzar ko'chasi, 60-uy, 46-xona<br>
X/r: 20208000105393795001<br>
“Xalq Bank” Do'stobod BXM.<br>
MFO 01125 INN 308515451<br>
OKED 41100<br>
Tel: +99877-091-91-01</p>
<p style="margin-top:20px">Direktor:<br>J.Sh.Sarimsakov _________________<br>(imzo/muhr)</p>
</td>
<td>
<div class="label">B U Y U R T M A C H I:</div>
<p style="text-align:center">{{ $ism }}</p>
<p>(F.I.SH.)</p>
<p>Manzil: {{ $manzil }}</p>
<p>Passport seriya: <span class="blank">{{ $project->passport_series }}</span></p>
<p>Berilgan vaqt: <span class="blank">{{ $project->passport_issued_by }}</span></p>
<p>JSHIR/PINFL: <span class="blank">{{ $project->pinfl }}</span></p>
<p style="margin-top:20px">___________________ ______________<br>F.I.SH&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;imzo</p>
</td>
</tr>
</table>

<div class="page-break"></div>

<div class="section-title" style="margin-top:0">SHARTNOMAVIY NARX KELISHUV BAYONNOMASI</div>
<p class="center">(Shartnomaga ilova)</p>
<p class="center">«{{ $kun }}» {{ $oyNomi }} {{ $yil }} yil</p>

<p>Bir tomondan «BUYURTMACHI» va ikkinchi tomondan «IJROCHI» ushbu Bayonnomani quyidagilar haqida tuzdilar:</p>
<p>Ijrochi tomonidan {{ $manzil }} manzili bo'yicha bajariladigan loyihalash va hujjatlarni hamrohlik qilish xizmatlarining umumiy qiymati {{ $narx }} ({{ $narxSozda }}, QQSiz) miqdorida belgilandi.</p>
<p>Ushbu kelishuv bayonnomasi tomonlar o'rtasida tuzilgan asosiy shartnomaning ajralmas qismi hisoblanadi va har ikkala tomon tomonidan imzolangandan so'ng kuchga kiradi.</p>

<div class="section-title">TOMONLARNING IMZOLARI:</div>
<table class="sign">
<tr>
<td>
<div class="bold">“IJROCHI”</div>
<p>“MY PERFEKT HOME” MCHJ</p>
<p style="margin-top:20px">_______________ J.SH.Sarimsakov</p>
</td>
<td>
<div class="bold">“BUYURTMACHI”</div>
<p style="margin-top:8px">___________________________________</p>
<p>(F.I.SH.)</p>
</td>
</tr>
</table>

</body>
</html>
