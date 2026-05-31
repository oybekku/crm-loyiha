<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DesignSettingsService
{
    private static string $file      = 'design-settings.json';
    private static string $cacheKey  = 'design_settings';
    private static int    $cacheTtl  = 600; // 10 daqiqa

    public static function get(): array
    {
        return Cache::remember(self::$cacheKey, self::$cacheTtl, function () {
            $path = storage_path('app/' . self::$file);
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                if (is_array($data)) {
                    return array_merge(self::defaults(), $data);
                }
            }
            return self::defaults();
        });
    }

    public static function save(array $data): void
    {
        $path    = storage_path('app/' . self::$file);
        $tmpPath = $path . '.tmp.' . getmypid();
        $json    = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Atomik yozish: avval temp faylga, keyin rename (race condition yo'q)
        file_put_contents($tmpPath, $json, LOCK_EX);
        rename($tmpPath, $path);

        Cache::forget(self::$cacheKey);
    }

    public static function hexToRgba(string $hex, float $opacity = 1.0): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba({$r},{$g},{$b},{$opacity})";
    }

    public static function defaults(): array
    {
        return [
            // Login sahifasi
            'login_bg_image'   => '',
            'login_bg_opacity' => 80,
            'login_card_blur'  => 'glass',

            // Sidebar — Light
            'sidebar_color'        => '#3B82F6',
            'sidebar_opacity'      => 100,
            'sidebar_text_color'   => '#FFFFFF',
            'sidebar_active_color' => '#FFFFFF',

            // Sidebar — Dark
            'sidebar_dark_color'        => '#1E3A5F',
            'sidebar_dark_opacity'      => 100,
            'sidebar_dark_text_color'   => '#E5E7EB',
            'sidebar_dark_active_color' => '#FFFFFF',

            // Header
            'header_color'      => '#000000',
            'header_opacity'    => 50,
            'header_text_color' => '#D1D5DB',

            // Body — Light
            'light_mode_bg'         => '#F3F4F6',
            'light_mode_text_color' => '#111827',

            // Body — Dark
            'dark_mode_bg'         => '#1F2937',
            'dark_mode_text_color' => '#F3F4F6',

            // Hero (dashboard) animatsiya
            'hero_anim_type'       => 'video',
            'hero_anim_video_url'  => '/videos/hero-bg.webm',
            'hero_anim_lottie_url' => '',
            'hero_anim_css_code'   => '',
            'hero_anim_opacity'    => 65,
            'hero_anim_position'   => 'right-half',
            'hero_anim_speed'      => 1,

            // Sidebar animatsiya
            'sidebar_lottie_url'    => '',
            'sidebar_anim_opacity'  => 30,
            'sidebar_anim_scale'    => 100,
            'sidebar_anim_speed'    => 1,
            'sidebar_anim_loop'     => 0,
        ];
    }
}
