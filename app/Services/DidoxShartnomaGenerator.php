<?php

namespace App\Services;

use App\Models\Project;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * DIDOX shartnomasini Word (.docx) sifatida yaratadi — PhpWord orqali,
 * dompdf/PDF versiyasi bilan bir xil matn va tuzilish (2 sahifa:
 * shartnoma + narx kelishuv bayonnomasi).
 */
class DidoxShartnomaGenerator
{
    private const NARX       = '400 000,00';
    private const NARX_SOZDA = "To'rt yuz ming so'm";

    public static function generate(Project $p): string
    {
        $oylar = [1 => 'yanvar', 2 => 'fevral', 3 => 'mart', 4 => 'aprel', 5 => 'may', 6 => 'iyun',
                  7 => 'iyul', 8 => 'avgust', 9 => 'sentabr', 10 => 'oktabr', 11 => 'noyabr', 12 => 'dekabr'];

        $sana     = $p->created_at ?? now();
        $sanaText = $sana->format('d') . '.' . $sana->format('m') . '.' . $sana->format('Y') . ' yil';
        $kun      = $sana->format('d');
        $oyNomi   = $oylar[(int) $sana->format('n')];
        $yil      = $sana->format('Y');
        $raqam    = $p->seq_no ?: $p->id;
        $ism      = $p->owner_name ?? '';
        $manzil   = $p->address ?? '';
        $narx     = self::NARX;
        $narxSozda = self::NARX_SOZDA;

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['marginLeft' => 1000, 'marginRight' => 1000]);

        $justify = ['alignment' => Jc::BOTH, 'spaceAfter' => 120];
        $center  = ['alignment' => Jc::CENTER];
        $bold    = ['bold' => true];

        $section->addText('Shartnoma №' . $raqam, ['bold' => true, 'size' => 16], $center);
        $section->addText(
            "Loyihaviy, qidiruv va ilmiy-texnik mahsulot tushunchasiga kiruvchi boshqa dizaynerlik xizmatlarini bajarish to'g'risida",
            [], array_merge($center, ['spaceAfter' => 200])
        );
        $section->addText('Toshkent sh. ' . $sanaText, [], ['alignment' => Jc::END, 'spaceAfter' => 200]);

        $section->addText(
            'MChJ «MY PERFEKT HOME» (keyingi o\'rinlarda «IJROCHI» deb yuritiladi), Ustav va 26-iyul 2021-yildagi AL-001868-sonli Litsenziya asosida harakat qiluvchi direktor J.Sh.Sarimsakov shaxsida bir tomondan va ' . $ism . ' (keyingi o\'rinlarda «BUYURTMACHI» deb yuritiladi), pasport asosida harakat qiluvchi jismoniy shaxs, ikkinchi tomondan, quyidagilar to\'g\'risida ushbu shartnomani tuzdilar:',
            [], $justify
        );

        self::sectionTitle($section, '1. SHARTNOMA PREDMETI');
        $section->addText('«BUYURTMACHI» quyidagi xizmatlarni ishlab chiqishni «IJROCHI» zimmasiga yuklaydi va «IJROCHI» ushbu majburiyatlarni qabul qiladi: ' . $manzil . ' manzili bo\'yicha loyihalash va hujjatlarni hamrohlik qilish xizmatlari.', [], $justify);
        $section->addText('Ishning mazmuni, qiymati va bajarilish muddatlari Shartnomaga ajralmas qism hisoblanadigan loyihalash topshirig\'i hamda shartnomaviy narx bayonnomasi orqali belgilanadi.', [], $justify);
        $section->addText('Ilmiy-texnik dizaynerlik mahsulotidan «BUYURTMACHI» tomonidan faqat bevosita maqsadda foydalaniladi. Ushbu mahsulotni «IJROCHI»ning yozma roziligisiz uchinchi shaxslarga berish yoki boshqa obyekt uchun foydalanish taqiqlanadi.', [], $justify);

        self::sectionTitle($section, '2. SHARTNOMA QIYMATI VA HISOB-KITOB QILISH TARTIBI');
        $section->addText("2.1. Shartnoma narxi kelishuv bayonnomasiga muvofiq tomonlar tomonidan {$narx} ({$narxSozda}, QQSiz) miqdorida belgilangan.", [], $justify);
        $section->addText("2.2. Ushbu Shartnomaga muvofiq ilmiy-texnik mahsulot tushunchasiga kiruvchi ish va xizmatlarni bajargani uchun «BUYURTMACHI» shartnomaviy narx kelishuv bayonnomasiga muvofiq {$narx} ({$narxSozda}, QQSiz) miqdordagi mablag'ni 10 (o'n) bank kuni mobaynida «IJROCHI»ga o'tkazib beradi. Shartnomaviy narx kelishuv bayonnomasi ushbu shartnomaning ajralmas qismi hisoblanadi.", [], $justify);
        $section->addText("2.3. «BUYURTMACHI» shartnoma imzolangandan keyin 10 kunlik muddat ichida Shartnoma summasining 100% miqdorida, ya'ni {$narx} ({$narxSozda}, QQSiz) miqdoridagi avans oldindan to'lovini «IJROCHI»ning hisob raqamiga o'tkazib berish majburiyatini oladi.", [], $justify);
        $section->addText('2.4. «IJROCHI» loyiha ishlarini ishlab chiqishga faqatgina avans mablag\'i uning hisob raqamiga tushganidan keyingina kirishadi.', [], $justify);

        self::sectionTitle($section, '3. ISHLARNI TOPSHIRISH VA QABUL QILISH TARTIBI');
        $section->addText('3.1. Ishlar tugallangandan so\'ng «IJROCHI» «BUYURTMACHI»ga Shartnoma shartlarida nazarda tutilgan ilmiy-texnik va boshqa hujjatlar to\'plamini ilova qilgan holda, ilmiy-texnik hujjatlarni topshirish-qabul qilish dalolatnomasini taqdim etadi.', [], $justify);
        $section->addText('3.2. «BUYURTMACHI» topshirish-qabul qilish dalolatnomasi va ushbu Shartnomaning 3.1-bandida ko\'rsatilgan unga ilova qilingan hujjatlarni olgan kundan boshlab 5 kun ichida «IJROCHI»ga imzolangan ishni topshirish-qabul qilish dalolatnomasini yoki ishni qabul qilishdan asoslantirilgan rad javobini yuborishi shart.', [], $justify);
        $section->addText('3.3. «BUYURTMACHI» tomonidan asoslantirilgan rad javobi berilgan taqdirda, tomonlar 10 kunlik muddat ichida tomonlarning mas\'ul vakillarining imzolari bilan rasmiylashtirilgan, zarur kamchiliklarni bartaraf etish ro\'yxati va ularni bajarish muddatlari ko\'rsatilgan ikki tomonlama dalolatnoma tuzadilar.', [], $justify);
        $section->addText('3.4. Agar ishlar va xizmatlarni bajarish jarayonida uni keyinchalik davom ettirish maqsadga muvofiq emasligi aniqlansa, «IJROCHI» bu haqda «BUYURTMACHI»ni xabardor qilgan holda ularni to\'xtatib turishga majburdir. «BUYURTMACHI» 5 kunlik muddat ichida ishni to\'xtatib turish masalasini ko\'rib chiqishi shart. Bunday holda tomonlar 10 kunlik muddat ichida xizmatlarni davom ettirish maqsadga muvofiqligi yoki ishni to\'xtatish va o\'zaro hisob-kitob qilish dalolatnomalarini tuzish masalasini ko\'rib chiqishga majburdirlar.', [], $justify);

        self::sectionTitle($section, '4. TOMONLARNING JAVOBGARLIGI');
        $section->addText('4.1. «IJROCHI» va «BUYURTMACHI» ushbu Shartnomaning majburiyatlarini bajarmaganlik yoki lozim darajada bajarmaganlik uchun amaldagi qonunchilikka muvofiq va shartnoma summasidan oshmaydigan miqdorda mulkiy javobgarlikni o\'z zimmalariga oladilar.', [], $justify);
        $section->addText('4.2. Shartnoma bo\'yicha o\'z majburiyatlarini vicdonan bajarmaganlik uchun aybdor tomonga O\'zbekiston Respublikasining 1998-yil 29-avgustdagi «Xo\'jalik yurituvchi subyektlar faoliyatining shartnomaviy-huquqiy bazasi to\'g\'risida»gi Qonunida nazarda tutilgan normalar va sanksiyalar qo\'llaniladi.', [], $justify);
        $section->addText('4.3. «BUYURTMACHI» taqdim etilayotgan dastlabki ma\'lumotlarning to\'liqligi va haqqoniyligi uchun javobgardir.', [], $justify);
        $section->addText('4.4. «IJROCHI» loyihalash topshirig\'ida ko\'rsatilgan shartlarning bajarilishi uchun javobgardir.', [], $justify);

        self::sectionTitle($section, '5. BOSHQA SHARTLAR');
        $section->addText('5.1. Shartnoma «IJROCHI» tomonidan avans mablag\'i o\'tkazilganligi haqidagi xabarnoma olingandan va «BUYURTMACHI» tomonidan dastlabki ma\'lumotlar taqdim etilgandan keyin kuchga kiradi.', [], $justify);
        $section->addText('5.2. Yuqorida ko\'rsatilgan muddatda (2.2-band) avans mablag\'i tushmagan taqdirda, «IJROCHI» Shartnomani bir tomonlama tartibda bekor qilishga haqlidir.', [], $justify);
        $section->addText('5.3. Boshqa hollarda Shartnoma tomonlarning kelishuviga ko\'ra yoki sud qaroriga binoan o\'zgartirilishi yoki bekor qilinishi mumkin.', [], $justify);
        $section->addText('5.4. Agar «BUYURTMACHI» Shartnomani to\'xtatsa, u ishlar to\'xtatilgan kunga qadar bajarilgan ishlar hajmini to\'lab berishga majburdir.', [], $justify);
        $section->addText('5.5. Qo\'shimcha xarajatlarni keltirib chiqaradigan dizayn xizmatlari hajmi oshgan taqdirda, «BUYURTMACHI» qo\'shimcha to\'lovni amalga oshiradi.', [], $justify);
        $section->addText('5.6. Shartnoma bo\'yicha ishlar (xizmatlar)ni bajarish uchun zarur bo\'lgan dastlabki ma\'lumotlarni taqdim etish 20 kundan ortiq muddatga kechiktirilganda, «IJROCHI» «BUYURTMACHI»ga ishni to\'xtatish dalolatnomasini taqdim etgan holda Shartnomani bir tomonlama tartibda bekor qilishga haqlidir.', [], $justify);
        $section->addText('5.7. Shartnoma narxi ochiq hisoblanadi va energiya resurslari, materiallar, mehnat haqi shartlari hamda bazaviy narxda hisobga olinmagan boshqa xarajatlar narxlari o\'zgarganda aniqlashtirilishi mumkin.', [], $justify);
        $section->addText('5.8. Bajarilgan ishlar o\'z vaqtida to\'lanmaganda, «BUYURTMACHI» tomonidan ushbu Shartnoma bo\'yicha kelishilgan narx hamda to\'lov vaqtidagi indeksatsiya qilingan narx o\'rtasidagi farqqa teng qo\'shimcha to\'lov amalga oshiriladi.', [], $justify);
        $section->addText('5.9. «BUYURTMACHI» tomonidan ishlab chiqilgan dizayn hujjatlarini «IJROCHI»ning roziligisiz boshqalarga berish taqiqlanadi.', [], $justify);
        $section->addText('5.10. Ushbu shartnoma bo\'yicha barcha nizolar va kelishmovchiliklar tomonlar o\'rtasida muzokaralar olib borish yo\'li bilan hal etiladi. Kelishuvga erishilmagan taqdirda, har qanday tomon buzilgan huquqlarini himoya qilish uchun sud organlariga murojaat qilishga haqli.', [], $justify);
        $section->addText('5.11. Ushbu Shartnomaning amal qilish muddati u imzolangan paytdan boshlab tomonlar Shartnoma bo\'yicha qabul qilingan majburiyatlarni to\'liq bajarguniga qadar etib belgilanadi.', [], $justify);

        self::sectionTitle($section, '6. FORS-MAJOR HOLATLARI');
        $section->addText('6.1. Tomonlar fors-major holatlari (tabiiy ofatlar, suv toshqinlari, yong\'inlar, shuningdek, davlat organlarining hujjatlari, harbiy harakatlar) yuzaga kelganda javobgar bo\'lmaydilar. Fors-major holatlari yuzaga kelganda tomonlar bunday holatlar boshlangan paytdan e\'tiboran 3 kun ichida bir-birlarini xabardor qilishga majburdirlar.', [], $justify);
        $section->addText('6.2. Fors-major holatlari bo\'yicha yuqorida ko\'rsatilgan shartlar bajarilmagan taqdirda, tomonlar ushbu shartnoma bo\'yicha javobgarlikdan ozod qilinmaydilar. Shartnomaning 6.1-bandida oldindan ko\'rsatilgan fors-major holatlari muddati tugaganidan so\'ng, Ijrochi ishlarni yakunlash va shartnoma bo\'yicha qabul qilingan majburiyatlarni oxiriga yetkazishga majburdir. Shartnoma muddati fors-major holatlari amal qilgan davrga muvofiq uzaytiriladi.', [], $justify);
        $section->addText('6.3. Fors-major tufayli o\'z majburiyatlarini uch oy mobaynida bajarish imkoniyati bo\'lmagan taqdirda, ushbu shartnoma bajarilgan ishlar dalolatnomasini tuzgan holda tomonlardan har qanday birining tashabbusi bilan bekor qilinishi mumkin.', [], $justify);

        self::sectionTitle($section, '7. YAKUNIY QOIDALAR');
        $section->addText('7.1. Ushbu shartnoma bir xil yuridik kuchga ega bo\'lgan ikki nusxada, har bir tomon uchun bir nusxadan tuzildi. Ushbu shartnomaga shartnomaviy narx kelishuv bayonnomasi ilova qilinadi.', [], $justify);

        self::sectionTitle($section, '8. TOMONLARNING MANZILLARI VA REKVIZITLARI');

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 100, 'width' => 100 * 50, 'unit' => 'pct']);
        $table->addRow();

        $cell1 = $table->addCell(5000);
        $cell1->addText('I J R O C H I:', $bold, $center);
        $cell1->addText('"MY PERFEKT HOME" MCHJ', [], $center);
        $cell1->addText("Manzil: Toshkent shahri, Yangihayot tumani Uzar ko'chasi, 60-uy, 46-xona", [], ['spaceBefore' => 100]);
        $cell1->addText('X/r: 20208000105393795001');
        $cell1->addText('"Xalq Bank" Do\'stobod BXM.');
        $cell1->addText('MFO 01125 INN 308515451');
        $cell1->addText('OKED 41100');
        $cell1->addText('Tel: +99877-091-91-01');
        $cell1->addText('Direktor:', ['spaceBefore' => 300]);
        $cell1->addText('J.Sh.Sarimsakov _________________');
        $cell1->addText('(imzo/muhr)');

        $cell2 = $table->addCell(5000);
        $cell2->addText('B U Y U R T M A C H I:', $bold, $center);
        $cell2->addText($ism, [], $center);
        $cell2->addText('(F.I.SH.)', [], $center);
        $cell2->addText('Manzil: ' . $manzil, [], ['spaceBefore' => 100]);
        $cell2->addText('Passport seriya: ' . ($p->passport_series ?? ''));
        $cell2->addText('Berilgan vaqt: ' . ($p->passport_issued_by ?? ''));
        $cell2->addText('JSHIR/PINFL: ' . ($p->pinfl ?? ''));
        $cell2->addText('___________________ ______________', ['spaceBefore' => 300]);
        $cell2->addText('F.I.SH.                                   imzo');

        $section->addPageBreak();

        $section->addText('SHARTNOMAVIY NARX KELISHUV BAYONNOMASI', $bold, $center);
        $section->addText('(Shartnomaga ilova)', [], $center);
        $section->addText("«{$kun}» {$oyNomi} {$yil} yil", [], $center);

        $section->addText('Bir tomondan «BUYURTMACHI» va ikkinchi tomondan «IJROCHI» ushbu Bayonnomani quyidagilar haqida tuzdilar:', [], array_merge($justify, ['spaceBefore' => 200]));
        $section->addText("Ijrochi tomonidan {$manzil} manzili bo'yicha bajariladigan loyihalash va hujjatlarni hamrohlik qilish xizmatlarining umumiy qiymati {$narx} ({$narxSozda}, QQSiz) miqdorida belgilandi.", [], $justify);
        $section->addText('Ushbu kelishuv bayonnomasi tomonlar o\'rtasida tuzilgan asosiy shartnomaning ajralmas qismi hisoblanadi va har ikkala tomon tomonidan imzolangandan so\'ng kuchga kiradi.', [], $justify);

        self::sectionTitle($section, 'TOMONLARNING IMZOLARI:');

        $signTable = $section->addTable(['cellMargin' => 100, 'width' => 100 * 50, 'unit' => 'pct']);
        $signTable->addRow();
        $sc1 = $signTable->addCell(5000);
        $sc1->addText('"IJROCHI"', $bold, $center);
        $sc1->addText('"MY PERFEKT HOME" MCHJ', [], $center);
        $sc1->addText('_______________ J.SH.Sarimsakov', [], array_merge($center, ['spaceBefore' => 300]));

        $sc2 = $signTable->addCell(5000);
        $sc2->addText('"BUYURTMACHI"', $bold, $center);
        $sc2->addText('___________________________________', [], array_merge($center, ['spaceBefore' => 200]));
        $sc2->addText('(F.I.SH.)', [], $center);

        $tmp = tempnam(sys_get_temp_dir(), 'didox');
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmp);
        $data = file_get_contents($tmp);
        @unlink($tmp);

        return $data;
    }

    private static function sectionTitle(\PhpOffice\PhpWord\Element\Section $section, string $text): void
    {
        $section->addText($text, ['bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 150]);
    }
}
