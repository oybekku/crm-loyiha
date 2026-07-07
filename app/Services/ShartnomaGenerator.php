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
    public static function generate(Project $p): string
    {
        $tpl = resource_path('templates/shartnoma.docx');
        if (!is_file($tpl)) {
            abort(500, 'Shartnoma shabloni topilmadi');
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
            '{narx_sozda}' => self::sumWords($total),
            '{raqam}'      => (string) ($p->seq_no ?? ''),
            '{sana}'       => $p->created_at ? $p->created_at->format('d.m.Y') : '',
            '{pasport}'    => $p->passport_series ?? '',
            '{berilgan}'   => $p->passport_issued_by ?? '',
            '{pinfl}'      => $p->pinfl ?? '',
            '{telefon}'    => $phone,
        ];

        foreach ($map as $tag => $val) {
            $xml = str_replace($tag, htmlspecialchars((string) $val, ENT_XML1, 'UTF-8'), $xml);
        }

        // Shablonда teglanmay qolgan to'liq ism (butun matn — ishonchli almashinadi)
        if (!empty($p->owner_name)) {
            $xml = str_replace('Кушманов Элёр Равшанбекович', htmlspecialchars($p->owner_name, ENT_XML1, 'UTF-8'), $xml);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        $data = file_get_contents($tmp);
        @unlink($tmp);
        return $data;
    }

    /** Summаni rus tilидa so'z bilan: 2400000 → "Два миллиона четыреста тысяч сум" */
    public static function sumWords(float $amount): string
    {
        // Shablonда {narx_sozda} dan keyin "сум" bor — shuning uchun bu yerда "сум" qo'shilmaydi
        $n = (int) round($amount);
        if ($n === 0) return 'Ноль';
        $w = self::ruWords($n);
        return mb_convert_case(mb_substr($w, 0, 1), MB_CASE_UPPER, 'UTF-8') . mb_substr($w, 1);
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
