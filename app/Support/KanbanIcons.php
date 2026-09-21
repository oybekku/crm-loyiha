<?php

namespace App\Support;

/**
 * Kanban kartasidagi ikonkalar (stroke uslubidagi inline SVG).
 */
class KanbanIcons
{
    private const PATHS = [
        'pin'     => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'clip'    => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>',
        'case'    => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'file'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'book'    => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'flag'    => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
        'img'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        'user'    => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'cal'     => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/>',
        'chk'     => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'xcircle' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
        'card'    => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'thumb'   => '<path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>',
        'alert'   => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'db'      => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'dia'     => '<path d="M12 2 22 12 12 22 2 12z"/>',
        'chevron' => '<polyline points="6 9 12 15 18 9"/>',
    ];

    /** Ish holati (work_status) => [katta blok ikonkasi, status belgisi ikonkasi] */
    private const WORK_STATUS = [
        'yangi'           => ['book', 'user'],
        'jarayonda'       => ['flag', 'cal'],
        'rad_qilindi'     => ['xcircle', 'xcircle'],
        'tayyor'          => ['img', 'chk'],
        'tolov_jarayonda' => ['card', 'card'],
        'kelishildi'      => ['thumb', 'chk'],
        'kelishilmadi'    => ['alert', 'alert'],
    ];

    /** Xizmat (service_name) => ikonka */
    private const SERVICE = [
        'toposyomka'   => 'pin',
        'eskiz_loyiha' => 'clip',
        'ariza'        => 'file',
    ];

    public static function svg(string $name, string $class = ''): string
    {
        $paths = self::PATHS[$name] ?? self::PATHS['case'];
        $cls   = $class !== '' ? ' class="' . e($class) . '"' : '';

        return '<svg' . $cls . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
    }

    /** @return array{0:string,1:string} */
    public static function workStatus(?string $key): array
    {
        return self::WORK_STATUS[$key ?? 'yangi'] ?? self::WORK_STATUS['yangi'];
    }

    public static function service(?string $key): string
    {
        return self::SERVICE[$key ?? ''] ?? 'case';
    }
}
