<?php

namespace App\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * my.gov.uz "Arizani kuzatish" — qo'lda tekshirish.
 *
 * my.gov.uz/uz/task-view sahifasi aslida oldmy.gov.uz:4433/oz/site/task-view
 * formasini iframe'da ko'rsatadi. Oqim:
 *   1) start()  — formani ochib, CSRF + cookie + himoya kodi rasmini olamiz (rasm CRM'da ko'rsatiladi);
 *   2) submit() — xodim yozgan javob bilan formani yuboramiz → 302 natija manziliga
 *      (…/<xizmat>/task-view?id=…&pin_code=…) → shu sahifani parse qilamiz;
 *   3) refresh() — saqlangan natija manzilini qayta ochamiz (himoya kodisiz).
 * Faqat xodim tugma bosganda ishlaydi — avtomatik/ommaviy so'rov YO'Q.
 */
class MygovTaskChecker
{
    public const BASE = 'https://oldmy.gov.uz:4433';
    private const FORM = self::BASE . '/oz/site/task-view';
    private const UA   = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    /** @return array{token:string, image:string} image = data: URI */
    public function start(): array
    {
        $jar  = new CookieJar();
        $form = $this->http($jar)->get(self::FORM);
        if (!$form->successful()) {
            throw new \RuntimeException("my.gov.uz javob bermadi (HTTP {$form->status()})");
        }
        $html = $form->body();
        if (!preg_match('/name="_csrf-myap" value="([^"]+)"/', $html, $m)) {
            throw new \RuntimeException("my.gov.uz formasi o'zgargan (CSRF topilmadi)");
        }
        $csrf = html_entity_decode($m[1]);
        if (!preg_match('/id="captcha-image" src="([^"]+)"/', $html, $c)) {
            throw new \RuntimeException("my.gov.uz formasi o'zgargan (himoya kodi topilmadi)");
        }
        $img = $this->http($jar)->get(self::BASE . html_entity_decode($c[1]));
        if (!$img->successful()) {
            throw new \RuntimeException("Himoya kodi rasmini olib bo'lmadi");
        }
        $type  = $img->header('Content-Type') ?: 'image/png';
        $token = Str::random(32);
        Cache::put($this->key($token), ['csrf' => $csrf, 'cookies' => $jar->toArray()], now()->addMinutes(10));

        return ['token' => $token, 'image' => 'data:' . $type . ';base64,' . base64_encode($img->body())];
    }

    /** @return array{url:string, info:array} */
    public function submit(string $token, string $id, string $pin, string $answer): array
    {
        $state = Cache::pull($this->key($token));
        if (!$state) {
            throw new \RuntimeException("Himoya kodi eskirdi — qaytadan \"Tekshirish\"ni bosing");
        }
        $jar = new CookieJar(false, $state['cookies']);
        $res = $this->http($jar)
            ->withHeaders(['Referer' => self::FORM])
            ->asForm()
            ->post(self::FORM, [
                '_csrf-myap'                 => $state['csrf'],
                'TaskSearchForm[id]'         => $id,
                'TaskSearchForm[pin_code]'   => $pin,
                'TaskSearchForm[verifyCode]' => $answer,
            ]);

        $loc = $res->header('Location');
        if (!in_array($res->status(), [301, 302, 303], true) || !$loc) {
            throw new \RuntimeException($this->formError($res->body()));
        }
        $url = Str::startsWith($loc, 'http') ? $loc : self::BASE . $loc;
        if (!$this->isSafeUrl($url)) {
            throw new \RuntimeException("my.gov.uz kutilmagan manzilga yo'naltirdi");
        }
        return ['url' => $url, 'info' => $this->fetch($url, $jar)];
    }

    /** Saqlangan natija manzilini qayta ochish (himoya kodisiz). */
    public function refresh(string $url): array
    {
        if (!$this->isSafeUrl($url)) {
            throw new \RuntimeException("Saqlangan manzil noto'g'ri");
        }
        return $this->fetch($url, new CookieJar());
    }

    public function isSafeUrl(string $url): bool
    {
        return (bool) preg_match('#^https://oldmy\.gov\.uz:4433/[a-z]{2,3}/[a-z0-9\-]+/task-view\?id=\d+&pin_code=[^&\s]+$#i', $url);
    }

    private function fetch(string $url, CookieJar $jar): array
    {
        $res = $this->http($jar)->get($url);
        if (!$res->successful()) {
            throw new \RuntimeException("Natija sahifasi ochilmadi (HTTP {$res->status()})");
        }
        $info = $this->parse($res->body());
        if (($info['holat'] ?? '') === '') {
            throw new \RuntimeException("Ariza holati hali ko'rinmayapti — ariza endi yuborilgan/qoralama bo'lishi mumkin yoki raqam/parol noto'g'ri. Birozdan keyin qayta tekshiring.");
        }
        return $info;
    }

    public function parse(string $html): array
    {
        $clean = fn(string $s) => trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($s), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        $info = ['holat' => '', 'holat_rang' => 'grey'];
        if (preg_match('/class="\s*taskStatus task_status_(\w+)">\s*([^<]+)/u', $html, $m)) {
            $info['holat']      = $clean($m[2]);
            $info['holat_rang'] = $m[1];
        }

        // "Ariza to'g'risida ma'lumot" jadvali — birinchi uchragan qiymatlar
        $fields = [
            'berilgan'     => 'Berilgan sana',
            'oxirgi'       => "Oxirgi o'zgartirish kiritilgan sana",
            'xizmat'       => 'Xizmat nomi',
            'tashkilot'    => 'Tashkilot',
            'hozirgi_amal' => 'Hozirgi amal',
            'kim'          => 'Hozirgi vaqtda amal bajarmoqda',
        ];
        foreach ($fields as $key => $label) {
            $re = '/<th[^>]*>\s*' . preg_quote($label, '/') . '(?:<i[^>]*><\/i>)?\s*<\/th>\s*<td[^>]*>(.*?)<\/td>/su';
            if (preg_match($re, $html, $m)) {
                $info[$key] = $clean($m[1]);
            }
        }

        // Amallar xronologiyasi — bosqichlar: nomi, vaqti, kim
        $info['bosqichlar'] = [];
        if (preg_match_all('/<div class="timeLineViewBoxTitleBox">\s*<p>((?:(?!<\/p>).)*)<\/p>\s*<span>((?:(?!<\/span>).)*)<\/span>/su', $html, $all, PREG_SET_ORDER)) {
            foreach ($all as $s) {
                $parts = preg_split('/\x{00A0}|&nbsp;/u', html_entity_decode($s[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $parts = array_values(array_filter(array_map(fn($x) => $clean($x), $parts), fn($x) => $x !== ''));
                $info['bosqichlar'][] = [
                    'nomi' => $clean($s[1]),
                    'vaqt' => $parts[0] ?? '',
                    'kim'  => $parts[1] ?? '',
                ];
            }
        }
        return $info;
    }

    private function formError(string $html): string
    {
        if (preg_match_all('/<div class="help-block[^"]*">([^<]+)<\/div>/u', $html, $m) && $m[1]) {
            $msg = trim(implode(' ', array_map('trim', $m[1])));
            if ($msg !== '') return 'my.gov.uz: ' . html_entity_decode($msg, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        if (preg_match('/class="[^"]*alert[^"]*"[^>]*>(.*?)<\/div>/su', $html, $a)) {
            $msg = trim(preg_replace('/\s+/u', ' ', strip_tags($a[1])));
            if ($msg !== '') return 'my.gov.uz: ' . html_entity_decode($msg, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return "Himoya kodi javobi, ariza raqami yoki parol noto'g'ri";
    }

    private function http(CookieJar $jar)
    {
        return Http::withOptions(['cookies' => $jar, 'allow_redirects' => false])
            ->withUserAgent(self::UA)
            ->timeout(20)
            ->connectTimeout(8);
    }

    private function key(string $token): string
    {
        return 'mygov-task-check:' . $token;
    }
}
