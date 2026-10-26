<?php

namespace App\Services;

use App\Filament\Pages\NarxOzgarishlari;
use App\Models\PriceChange;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Loyiha narxi o'zgarishini yozadi va adminlarga (o'zgartirgan odamdan
 * boshqa) panelda 🔔 xabar qoldiradi. ProjectService hook'laridan chaqiriladi,
 * shuning uchun narx qayerda o'zgarmasin (Kanban, tahrirlash oynasi,
 * "Joriy narx" va h.k.) — ushlanadi.
 *
 * Yoziladi:  narxi bor xizmat narxi/chegirmasi o'zgardi (1 000 → 1 200);
 *            narxi bor loyihaga yangi narxli xizmat qo'shildi;
 *            narxi bor xizmat olib tashlandi.
 * Yozilmaydi: to'lov kiritish; yangi loyiha ochish (shu so'rovda yaratilgan
 *            loyiha xizmatlari); 0 narxli xizmatga birinchi marta narx yozish;
 *            tizim (login qilmagan) jarayonlari.
 */
class PriceChangeRecorder
{
    /** Shu so'rovda yaratilgan loyihalar — ularning xizmatlari "o'zgarish" emas */
    private static array $freshProjects = [];

    public static function markFresh(int $projectId): void
    {
        self::$freshProjects[$projectId] = true;
    }

    public static function serviceUpdated(ProjectService $svc): void
    {
        if (!$svc->wasChanged('final_price')) return;
        $old = (float) $svc->getOriginal('final_price');
        $new = (float) $svc->final_price;
        if ($old <= 0 || abs($new - $old) < 0.01) return;
        self::record($svc, 'changed', $old, $new);
    }

    public static function serviceCreated(ProjectService $svc): void
    {
        $new = (float) $svc->final_price;
        if ($new <= 0) return;
        // Faqat narxi bor loyihaga qo'shilganda (boshqa xizmatlar jami > 0)
        $others = (float) ProjectService::where('project_id', $svc->project_id)->whereKeyNot($svc->id)->sum('final_price');
        if ($others <= 0) return;
        self::record($svc, 'added', 0, $new);
    }

    public static function serviceDeleted(ProjectService $svc): void
    {
        $old = (float) $svc->final_price;
        if ($old <= 0) return;
        self::record($svc, 'removed', $old, 0);
    }

    private static function record(ProjectService $svc, string $kind, float $old, float $new): void
    {
        $actor = auth()->user();
        if (!$actor || !$svc->project_id || isset(self::$freshProjects[$svc->project_id])) return;

        $project = Project::find($svc->project_id); // soft-delete qilingan loyiha — o'tkazib yuboriladi
        if (!$project) return;

        try {
            // Jami — bazadagi hozirgi xizmatlar bo'yicha (hook updateTotals'dan oldin ishlashi mumkin)
            $newTotal = (float) ProjectService::where('project_id', $project->id)->sum('final_price');
            $oldTotal = $newTotal - ($new - $old);

            $pc = PriceChange::create([
                'user_id'        => $actor->id,
                'project_id'     => $project->id,
                'project_number' => $project->number,
                'owner_name'     => $project->owner_name,
                'service_name'   => $svc->service_name,
                'kind'           => $kind,
                'old_price'      => $old,
                'new_price'      => $new,
                'old_total'      => $oldTotal,
                'new_total'      => $newTotal,
            ]);

            $admins = User::where('role', 'admin')->where('is_active', true)->whereKeyNot($actor->id)->get();
            if ($admins->isEmpty()) return;

            $fmt   = fn ($v) => number_format($v, 0, '.', ' ');
            $delta = $new - $old;
            Notification::make()
                ->title(PriceChange::KINDS[$kind] . ' — ' . ($project->number ?: '#' . $project->id))
                ->body("{$actor->name}: {$pc->serviceLabel()} {$fmt($old)} → {$fmt($new)} so'm (" . ($delta > 0 ? '+' : '−') . $fmt(abs($delta)) . ")\n"
                    . "{$project->owner_name} · jami {$fmt($oldTotal)} → {$fmt($newTotal)}")
                ->icon($delta > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->iconColor($delta > 0 ? 'warning' : 'danger')
                ->actions([Action::make('open')->label("Ko'rish")->url(NarxOzgarishlari::getUrl())->markAsRead()])
                ->sendToDatabase($admins);
        } catch (\Throwable $e) {
            // Jurnal/xabar xatosi narxni saqlashni to'xtatmasin
            report($e);
        }
    }
}
