<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * "Baza" — eskirgan loyihalarni asosiy saytdan butunlay olib qo'yish.
 *
 * Qoidalar (rahbariyat bilan kelishilgan):
 *  - Faqat admin ko'chiradi/qaytaradi. Hech narsa o'chirilmaydi — loyihaga
 *    baza_at belgisi qo'yiladi, HideBazaScope uni hamma joydan yashiradi.
 *  - Kassa qoldig'i, xodimlar maoshi o'zgarmaydi: to'lov/xizmat → loyiha
 *    bog'lanishlari scope'siz, ism-manzil esa "📦 BAZA LOYIHASI" bo'lib ko'rinadi.
 *  - Mijoz qisman to'lagan bo'lsa (masalan 60%) — xodim ulushi shu foizda
 *    "muzlaydi" (EmployeePayableService::commissionForService). Ish tugallanmagan
 *    bo'lsa ham admin belgilagan xodimga ulush ochiladi: xizmat "bajarildi"
 *    deb belgilanadi va KO'CHIRILGAN OY maoshiga tushadi (o'tgan oylar o'zgarmaydi).
 *  - Bazadagi loyiha faqat o'qish uchun (model guard'lari).
 *  - Qaytarishda hammasi baza_snapshot bo'yicha avvalgi holiga qaytadi.
 */
class BazaService
{
    /** true — model guard'lari (faqat o'qish) vaqtincha o'chadi (faqat shu servis ichida) */
    public static bool $bypass = false;

    /** Qaysi statuslar "tugallangan" hisoblanadi — boshqasi bo'lsa ko'chirishda 'tugallangan' qo'yiladi */
    public const CLOSED_STATUSES = ['tugallangan', 'taqdim_etilgan', 'bekor_qilingan'];

    public static function isBazaProjectId(int $projectId): bool
    {
        return isset(Project::bazaIds()[$projectId]);
    }

    /**
     * Ko'chirishdan oldin ko'rsatiladigan ma'lumot: har loyiha uchun qarz va
     * har xizmat bo'yicha xodim ulushi.
     *
     * @return array<int, array>
     */
    public static function preview(array $projectIds): array
    {
        $month = now()->format('Y-m');

        return Project::with(['services.assignedUser', 'payments'])
            ->whereIn('id', $projectIds)
            ->get()
            ->map(function (Project $p) use ($month) {
                $total     = (float) $p->total_price;
                $paid      = (float) $p->paid_amount;
                $cancelled = $p->status === 'bekor_qilingan';

                $services = $p->services->map(function (ProjectService $s) use ($p, $month, $cancelled) {
                    $price     = (float) $s->final_price;
                    $paidSvc   = EmployeePayableService::paidAmountForService($s, $p);
                    $ratio     = $price > 0 ? min(1, $paidSvc / $price) : 0;
                    $completed = (bool) $s->completed_at;
                    // Tugallanmagan ishga ulush berilsa — ko'chirilgan oy foizi bilan hisoblanadi
                    $rate  = EmployeePayableService::rateForService($s, $completed ? null : $month);
                    $share = $cancelled ? 0.0 : round($price * $rate / 100 * $ratio, 0);

                    return [
                        'id'          => $s->id,
                        'label'       => $s->service_label,
                        'employee'    => $s->assignedUser?->name,
                        'has_user'    => (bool) $s->assigned_user_id,
                        'completed'   => $completed,
                        'price'       => $price,
                        'paid'        => round($paidSvc, 0),
                        'pct'         => (int) round($ratio * 100),
                        'rate'        => $rate,
                        'share'       => $share,
                        // Admin tanlaydigan: tugallanmagan, xodimi bor, mijoz biror narsa to'lagan
                        'selectable'  => !$cancelled && !$completed && $s->assigned_user_id && $share > 0,
                    ];
                })->values()->all();

                return [
                    'id'        => $p->id,
                    'seq_no'    => $p->seq_no,
                    'number'    => $p->number,
                    'owner'     => $p->owner_name,
                    'address'   => $p->address,
                    'status'    => $p->status,
                    'cancelled' => $cancelled,
                    'total'     => $total,
                    'paid'      => $paid,
                    'debt'      => max(0, $total - $paid),
                    'pct'       => $total > 0 ? (int) min(100, round($paid / $total * 100)) : 100,
                    'payments'  => $p->payments->count(),
                    'services'  => $services,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Loyihalarni Bazaga ko'chiradi.
     *
     * @param  int[]  $projectIds
     * @param  int[]  $grantServiceIds  tugallanmagan, lekin ulush beriladigan xizmatlar
     * @return int    ko'chirilgan loyihalar soni
     */
    public static function move(array $projectIds, array $grantServiceIds, User $by): int
    {
        if (!$by->isAdmin()) {
            throw new \RuntimeException('Faqat admin Bazaga ko\'chira oladi.');
        }

        $grant = array_map('intval', $grantServiceIds);
        $now   = now();
        $moved = 0;

        self::$bypass = true;
        try {
            foreach (array_unique(array_map('intval', $projectIds)) as $id) {
                DB::transaction(function () use ($id, $grant, $now, $by, &$moved) {
                    $p = Project::lockForUpdate()->find($id); // scope: Bazada bo'lsa topilmaydi
                    if (!$p) return;

                    $snapshot = [
                        'prev_status' => $p->status,
                        'paid'        => (float) $p->paid_amount,
                        'total'       => (float) $p->total_price,
                        'services'    => [],
                    ];

                    // Tugallanmagan ishga ulush — admin belgilaganlariga (bekor qilinganda emas)
                    $granted = 0;
                    if ($p->status !== 'bekor_qilingan') {
                        $svcs = ProjectService::where('project_id', $p->id)
                            ->whereIn('id', $grant)
                            ->whereNull('completed_at')
                            ->whereNotNull('assigned_user_id')
                            ->get(['id', 'completed_at', 'work_month']);
                        foreach ($svcs as $s) {
                            $snapshot['services'][$s->id] = [
                                'completed_at' => $s->completed_at?->toDateTimeString(),
                                'work_month'   => $s->work_month,
                            ];
                        }
                        if ($svcs->isNotEmpty()) {
                            // Query update — model hodisalari (narx guard, jurnal) yonmaydi
                            ProjectService::whereIn('id', $svcs->pluck('id'))->update([
                                'completed_at' => $now,
                                'work_month'   => $now->format('Y-m'),
                            ]);
                            $granted = $svcs->count();
                        }
                    }

                    // "Tugallash" — SMS yuborilmaydi (saveQuietly), eski mijozlarga xabar ketmasin
                    if (!in_array($p->status, self::CLOSED_STATUSES, true)) {
                        Project::logStatusChange($p, 'tugallangan');
                        $p->status = 'tugallangan';
                    }

                    $p->forceFill([
                        'baza_at'       => $now,
                        'baza_by'       => $by->id,
                        'baza_snapshot' => $snapshot,
                    ])->saveQuietly();

                    ActivityLog::create([
                        'user_id'        => $by->id,
                        'project_id'     => $p->id,
                        'project_number' => $p->number,
                        'action'         => 'project_baza_moved',
                        'description'    => '№' . $p->seq_no
                            . " — to'langan " . number_format($snapshot['paid'], 0, '.', ' ')
                            . ' / ' . number_format($snapshot['total'], 0, '.', ' ') . " so'm"
                            . ($granted ? "; tugallanmagan {$granted} ta ishga ulush ochildi" : ''),
                    ]);

                    $moved++;
                });
            }
        } finally {
            self::$bypass = false;
            Project::flushBazaCache();
        }

        return $moved;
    }

    /** Bazadan asosiy saytga qaytaradi — hammasi ko'chirishdan oldingi holatiga */
    public static function restore(int $projectId, User $by): bool
    {
        if (!$by->isAdmin()) {
            throw new \RuntimeException('Faqat admin Bazadan qaytara oladi.');
        }

        self::$bypass = true;
        try {
            return DB::transaction(function () use ($projectId, $by) {
                $p = Project::onlyBaza()->lockForUpdate()->find($projectId);
                if (!$p) return false;

                $snap = $p->baza_snapshot ?? [];

                foreach ($snap['services'] ?? [] as $sid => $old) {
                    ProjectService::whereKey((int) $sid)->update([
                        'completed_at' => $old['completed_at'] ?? null,
                        'work_month'   => $old['work_month'] ?? null,
                    ]);
                }

                $prev = $snap['prev_status'] ?? null;
                if ($prev && $prev !== $p->status) {
                    Project::logStatusChange($p, $prev);
                    $p->status = $prev;
                }

                $p->forceFill([
                    'baza_at'       => null,
                    'baza_by'       => null,
                    'baza_snapshot' => null,
                ])->saveQuietly();

                ActivityLog::create([
                    'user_id'        => $by->id,
                    'project_id'     => $p->id,
                    'project_number' => $p->number,
                    'action'         => 'project_baza_restored',
                    'description'    => '№' . $p->seq_no . " — asosiy saytga qaytarildi",
                ]);

                return true;
            });
        } finally {
            self::$bypass = false;
            Project::flushBazaCache();
        }
    }
}
