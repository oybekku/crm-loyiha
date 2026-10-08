<?php

namespace App\Support;

/** "Qilinadigan ishlar ro'yxati" — formadan kelgan ishlarni tozalash (nomi, izoh, mas'ul). */
class WorkItemInput
{
    /**
     * @param  array  $rows    [['key'|'id', 'title', 'note', 'resp', 'done'], ...]
     * @param  string $prefix  yangi kalit prefiksi ('g' umumiy, 'x' qo'shimcha)
     * @return array  [['key','title','note','resp','done'], ...] — nomi bo'shlari tashlab yuboriladi
     */
    public static function clean(array $rows, string $prefix): array
    {
        $out  = [];
        $seen = [];
        foreach ($rows as $r) {
            if (! is_array($r)) continue;
            $title = self::text($r['title'] ?? ($r['label'] ?? ''), 250);
            if ($title === '') continue;
            $key = preg_replace('/[^a-z0-9_]/i', '', (string) ($r['key'] ?? ($r['id'] ?? '')));
            if ($key === '' || isset($seen[$key])) {
                $key = $prefix . bin2hex(random_bytes(4));
            }
            $seen[$key] = true;
            $out[] = [
                'key'   => $key,
                'title' => $title,
                'note'  => self::text($r['note'] ?? '', 500),
                'resp'  => self::text($r['resp'] ?? '', 60),
                'done'  => (bool) ($r['done'] ?? false),
            ];
        }
        return $out;
    }

    private static function text($v, int $max): string
    {
        $v = str_replace("\r", '', trim(strip_tags((string) $v)));
        return mb_substr($v, 0, $max);
    }
}
