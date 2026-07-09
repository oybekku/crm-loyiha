<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Eskiz.uz SMS shlyuzi.
 *
 * Ishlashi uchun .env da (serverda ham) quyidagilar bo'lishi kerak:
 *   ESKIZ_EMAIL=...        (eskiz.uz kabinet email)
 *   ESKIZ_PASSWORD=...     (kabinetdagi API parol)
 *   ESKIZ_FROM=4546        (tasdiqlangan nik; test uchun 4546)
 *
 * Token 30 kun amal qiladi — biz uni cache'da 25 kun saqlaymiz,
 * 401 kelsa avtomat qayta login qilamiz.
 */
class EskizSms
{
    private const BASE       = 'https://notify.eskiz.uz/api';
    private const CACHE_KEY  = 'eskiz_token';

    /**
     * Bitta raqamga SMS yuboradi.
     *
     * @return array{ok:bool, message:string}
     */
    public static function send(string $phone, string $text): array
    {
        $phone = self::normalizePhone($phone);
        if (strlen($phone) !== 12) {
            return ['ok' => false, 'message' => "Noto'g'ri telefon raqam"];
        }

        $email = config('services.eskiz.email');
        $pass  = config('services.eskiz.password');
        if (empty($email) || empty($pass)) {
            return ['ok' => false, 'message' => 'Eskiz login/parol sozlanmagan (.env)'];
        }

        try {
            $token = self::token();
            if (!$token) {
                return ['ok' => false, 'message' => 'Eskiz: token olinmadi (login/parol xato?)'];
            }

            $resp = self::sendRequest($token, $phone, $text);

            // Token eskirgan bo'lsa (401) — tozalab, bir marta qayta urinamiz
            if ($resp->status() === 401) {
                Cache::forget(self::CACHE_KEY);
                $token = self::token();
                $resp  = $token ? self::sendRequest($token, $phone, $text) : $resp;
            }

            $json   = $resp->json() ?? [];
            $status = $json['status'] ?? null;

            if ($resp->successful() && in_array($status, ['waiting', 'success'], true)) {
                return ['ok' => true, 'message' => 'SMS yuborildi'];
            }

            $msg = $json['message'] ?? ('HTTP ' . $resp->status());
            Log::warning('Eskiz send failed', ['phone' => $phone, 'resp' => $json]);
            return ['ok' => false, 'message' => 'Eskiz: ' . (is_string($msg) ? $msg : json_encode($msg))];
        } catch (\Throwable $e) {
            Log::error('Eskiz send exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'Eskiz xatosi: ' . $e->getMessage()];
        }
    }

    private static function sendRequest(string $token, string $phone, string $text)
    {
        $payload = [
            'mobile_phone' => $phone,
            'message'      => $text,
            'from'         => config('services.eskiz.from', '4546'),
        ];

        return Http::withToken($token)
            ->asForm()
            ->timeout(20)
            ->post(self::BASE . '/message/sms/send', $payload);
    }

    /** Token — cache'dan yoki yangisini login orqali oladi. */
    private static function token(): ?string
    {
        return Cache::remember(self::CACHE_KEY, now()->addDays(25), function () {
            $resp = Http::asForm()->timeout(20)->post(self::BASE . '/auth/login', [
                'email'    => config('services.eskiz.email'),
                'password' => config('services.eskiz.password'),
            ]);

            $token = $resp->json('data.token');
            if (!$resp->successful() || empty($token)) {
                Log::warning('Eskiz login failed', ['resp' => $resp->json()]);
                return null; // remember null'ni cache qilmaydi — keyingi safar qayta urinadi
            }
            return $token;
        });
    }

    /** +998 90 123-45-67 / probel / tire — hammasini 998901234567 ga keltiradi. */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        // 901234567 (9 xona) → 998901234567
        if (strlen($digits) === 9) {
            $digits = '998' . $digits;
        }
        // 8901234567 kabi holatlar — 998 old qo'shamiz
        if (strlen($digits) === 10 && str_starts_with($digits, '8')) {
            $digits = '998' . substr($digits, 1);
        }

        return $digits;
    }
}
