<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ProjectStatus;
use App\Models\User;
use Carbon\Carbon;

/**
 * Chap panel ("Loyiha holatlari" va "Hodimlar") uchun ma'lumotlar.
 * Sonlar doskaning ko'rinish qoidalari bilan bir xil: tanlangan oy
 * (MyGOV — barcha oylar), hodim faqat o'z loyihalari.
 */
class StatusRailData
{
    /** @return array<string,string> key => label */
    public static function statuses(?User $user): array
    {
        $statuses = [];
        try {
            $statuses = ProjectStatus::allOrdered()
                ->where('is_hidden', false)
                ->pluck('label', 'key')
                ->toArray();
        } catch (\Throwable $e) {
            // Baza tayyor bo'lmagan holat (migratsiya/o'rnatishdan oldin)
        }

        // Hodim (bajaruvchi) — faqat o'z ish bo'limlari ko'rinadi
        if ($user && $user->isBajaruvchi()) {
            $allowedCols = $user->kanbanServiceCols();
            $statuses = array_filter($statuses, fn ($label, $key) => in_array($key, $allowedCols), ARRAY_FILTER_USE_BOTH);
        }

        return $statuses;
    }

    /** @return array<string,int> status key => loyihalar soni */
    public static function counts(?User $user, array $statuses, int $year, int $month): array
    {
        if (empty($statuses) || ! $user) {
            return [];
        }

        try {
            $visible = function ($q, bool $isMygov) use ($user, $statuses) {
                if ($user->canSeeAllProjects()) {
                    return $q;
                }
                if ($user->isHisobchi()) {
                    return $isMygov ? $q : $q->where('status', '!=', 'yangi');
                }
                if ($user->hasPermission('barcha_loyihalar')) {
                    return $q;
                }
                if ($isMygov) {
                    return $user->hasPermission('kanban_all_mygov')
                        ? $q
                        : $q->whereHas('services', fn ($s) => $s->where('assigned_user_id', $user->id));
                }
                $fullCols = [];
                foreach (array_keys($statuses) as $k) {
                    if ($user->hasPermission('kanban_all_' . $k)) {
                        $fullCols[] = $k;
                    }
                }

                return $q->where(function ($w) use ($user, $fullCols) {
                    $w->whereHas('services', fn ($s) => $s->where('assigned_user_id', $user->id));
                    if ($fullCols) {
                        $w->orWhereIn('status', $fullCols);
                    }
                });
            };

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $counts = $visible(Project::query(), false)
                ->whereBetween('created_at', [$start, $start->copy()->endOfMonth()])
                ->selectRaw('status, count(*) as c')->groupBy('status')
                ->pluck('c', 'status')->map(fn ($c) => (int) $c)->toArray();
            $counts['mygov'] = $visible(Project::query(), true)->where('status', 'mygov')->count();

            return $counts;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Faol hodimlar (bajaruvchi) va ularga biriktirilgan loyihalar soni.
     * Ishi yo'q hodim ro'yxatga kirmaydi. Faqat admin/menejer uchun.
     *
     * @return list<array{id:int,name:string,count:int}>
     */
    public static function staff(?User $user, int $year, int $month): array
    {
        if (! $user || ! $user->canSeeAllProjects()) {
            return [];
        }

        try {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $perUser = ProjectService::query()
                ->whereNotNull('assigned_user_id')
                ->whereHas('project', fn ($q) => $q->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]))
                ->selectRaw('assigned_user_id, count(distinct project_id) as c')
                ->groupBy('assigned_user_id')
                ->pluck('c', 'assigned_user_id');

            return User::where('role', 'bajaruvchi')
                ->where('is_active', true)
                ->whereIn('id', $perUser->keys())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($u) => ['id' => (int) $u->id, 'name' => $u->name, 'count' => (int) $perUser[$u->id]])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
