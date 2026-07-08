<?php

namespace App\Services;

use App\Models\Project;
use ZipArchive;

/**
 * Shartnoma Word (.docx) shablonини loyiha ma'lumotlari bilan to'ldiradi.
 * Kutubxonasiz — PHP ichki ZipArchive orqali {teg}larni almashtiradi.
 */
class ShartnomaGenerator
{
    public static function generate(Project $p, string $lang = 'ru', string $doc = 'shartnoma'): string
    {
        $lang = $lang === 'uz' ? 'uz' : 'ru';
        if ($doc === 'rozilik') {
            $tpl = resource_path('templates/rozilik.docx');
        } else {
            $tpl = resource_path('templates/' . ($lang === 'uz' ? 'shartnoma-uz.docx' : 'shartnoma.docx'));
        }
        if (!is_file($tpl)) {
            abort(500, 'Hujjat shabloni topilmadi');
        }

        // Vaqtinchalik nusxа ustidа ishlaymiz
        $tmp = tempnam(sys_get_temp_dir(), 'shn');
        copy($tpl, $tmp);

        $zip = new ZipArchive();
        if ($zip->open($tmp) !== true) {
            @unlink($tmp);
            abort(500, 'Shablon ochilmadi');
        }
        $xml = $zip->getFromName('word/document.xml');

        // Teglarга qo'yilgan bezakni tozalash: qora fon, kulrang matn, Courier shrift
        $xml = preg_replace('/<w:shd\b[^>]*w:fill="262626"[^>]*\/>/', '', $xml);
        $xml = str_replace('<w:color w:val="8C8C8C"/>', '<w:color w:val="000000"/>', $xml);
        $xml = str_replace('Courier New', 'Times New Roman', $xml);

        // Telefon (birinchi raqam)
        $phone  = '';
        $phones = $p->phones;
        if (is_array($phones) && !empty($phones)) {
            $first = $phones[0] ?? null;
            $phone = is_array($first) ? ($first['phone'] ?? '') : (string) ($first ?? '');
        }

        $total = (float) $p->total_price;

        $map = [
            '{ism}'        => $p->owner_name ?? '',
            '{manzil}'     => $p->address ?? '',
            '{narx}'       => number_format($total, 2, ',', ' '),
            '{narx_sozda}' => self::sumWords($total, $lang),
            '{raqam}'      => '№' . ($p->seq_no ?? ''),
            '{sana}'       => $p->created_at ? $p->created_at->format('d.m.Y') : '',
            '{pasport}'    => $p->passport_series ?? '',
            '{berilgan}'   => $p->passport_issued_by ?? '',
            '{pinfl}'      => $p->pinfl ?? '',
            '{telefon}'    => $phone,
        ];

        // Teglarni almashtirish — bo'linishga chidamli (Word teglarni run/proofErr bilan
        // bo'lib tashlashi mumkin). {teg} ichida XML teglar bo'lsa ham topadi.
        $xml = preg_replace_callback('/\{((?:<[^>]*>|[A-Za-z_])+)\}/u', function ($mm) use ($map) {
            $name = '{' . preg_replace('/<[^>]*>/', '', $mm[1]) . '}';
            return array_key_exists($name, $map)
                ? htmlspecialchars((string) $map[$name], ENT_XML1, 'UTF-8')
                : $mm[0];
        }, $xml);

        // Ruscha shartnoma shablonида teglanmay qolgan namuna qiymatlарини to'g'rilash
        if ($doc === 'shartnoma' && $lang === 'ru') {
            if (!empty($p->owner_name)) {
                $xml = str_replace('Кушманов Элёр Равшанбекович', htmlspecialchars($p->owner_name, ENT_XML1, 'UTF-8'), $xml);
            }
            $xml = str_replace('№456', '№' . htmlspecialchars((string) ($p->seq_no ?? ''), ENT_XML1, 'UTF-8'), $xml);
            $xml = str_replace('/09-24', '', $xml);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        $data = file_get_contents($tmp);
        @unlink($tmp);
        return $data;
    }

    /** Summаni so'z bilan (ru yoki uz). Shablonда keyin "сум/so'm" bor — bu yerда qo'shilmaydi. */
    public static function sumWords(float $amount, string $lang = 'ru'): string
    {
        // RU shablonда {narx_sozda} dan keyin "сум" YO'Q — shu yerда qo'shamiz.
        // UZ shablonда keyin "so'm" BOR — qo'shmaymiz.
        $n = (int) round($amount);
        $suffix = $lang === 'uz' ? '' : ' сум';
        if ($n === 0) return ($lang === 'uz' ? 'Nol' : 'Ноль') . $suffix;
        $w = $lang === 'uz' ? self::uzWords($n) : self::ruWords($n);
        return mb_convert_case(mb_substr($w, 0, 1), MB_CASE_UPPER, 'UTF-8') . mb_substr($w, 1) . $suffix;
    }

    /** O'zbekcha: 2400000 → "ikki million to'rt yuz ming" (kelishik yo'q — sodda) */
    private static function uzWords(int $n): string
    {
        if ($n === 0) return 'nol';
        $ones = ['', 'bir', 'ikki', 'uch', "to'rt", 'besh', 'olti', 'yetti', 'sakkiz', "to'qqiz"];
        $tens = ['', "o'n", 'yigirma', "o'ttiz", 'qirq', 'ellik', 'oltmish', 'yetmish', 'sakson', "to'qson"];
        $scales = ['', 'ming', 'million', 'milliard', 'trillion'];

        $groups = [];
        $x = $n;
        while ($x > 0) { $groups[] = $x % 1000; $x = intdiv($x, 1000); }

        $parts = [];
        for ($g = count($groups) - 1; $g >= 0; $g--) {
            $num = $groups[$g];
            if ($num === 0) continue;
            $w = [];
            $h = intdiv($num, 100);
            $t = intdiv($num % 100, 10);
            $u = $num % 10;
            if ($h) { $w[] = $ones[$h]; $w[] = 'yuz'; }
            if ($t) $w[] = $tens[$t];
            if ($u) $w[] = $ones[$u];
            if ($g > 0) $w[] = $scales[$g];
            $parts[] = implode(' ', $w);
        }
        return implode(' ', $parts);
    }

    private static function ruWords(int $n): string
    {
        if ($n === 0) return 'ноль';

        // 0-mas., 1-jen. (тысяча uchun)
        $units = [
            ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ];
        $teens    = ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'];
        $tens     = ['', '', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundreds = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];

        // guruh so'zlari: [1, 2-4, 5-0, jinsi(0/1)]
        $scales = [
            ['', '', '', 0],
            ['тысяча', 'тысячи', 'тысяч', 1],
            ['миллион', 'миллиона', 'миллионов', 0],
            ['миллиард', 'миллиарда', 'миллиардов', 0],
            ['триллион', 'триллиона', 'триллионов', 0],
        ];

        // 3 xonalik guruhlarga ajratamiz (0 = birlik, 1 = mingliklar ...)
        $groups = [];
        $x = $n;
        while ($x > 0) {
            $groups[] = $x % 1000;
            $x = intdiv($x, 1000);
        }

        $parts = [];
        for ($g = count($groups) - 1; $g >= 0; $g--) {
            $num = $groups[$g];
            if ($num === 0) continue;

            $gender = $scales[$g][3];
            $words  = [];

            $h = intdiv($num, 100);
            $t = intdiv($num % 100, 10);
            $u = $num % 10;

            if ($h) $words[] = $hundreds[$h];
            if ($t === 1) {
                $words[] = $teens[$u];
            } else {
                if ($t) $words[] = $tens[$t];
                if ($u) $words[] = $units[$gender][$u];
            }

            // guruh so'zi (тысяча/миллион...) — kelishikда
            if ($g > 0) {
                $lastTwo = $num % 100;
                $last    = $num % 10;
                if ($lastTwo >= 11 && $lastTwo <= 14)      $sw = $scales[$g][2];
                elseif ($last === 1)                        $sw = $scales[$g][0];
                elseif ($last >= 2 && $last <= 4)           $sw = $scales[$g][1];
                else                                        $sw = $scales[$g][2];
                $words[] = $sw;
            }

            $parts[] = implode(' ', $words);
        }

        return implode(' ', $parts);
    }
}
