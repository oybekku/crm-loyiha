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

    // ── Qoidalar (yozish ham, kod so'rash ham shularga qaraydi) ──────────────

    /** Narxi bor xizmat narxi/chegirmasi o'zgaradimi (0 → X — birinchi narx, o'zgarish emas) */
    public static function updateAffectsPrice(float $oldFinal, float $newFinal): bool
    {
        return $oldFinal > 0 && abs($newFinal - $oldFinal) >= 0.01;
    }

    /** Narxi bor loyihaga narxli xizmat qo'shiladimi */
    public static function createAffectsPrice(?int $projectId, float $newFinal, ?int $exceptServiceId = null): bool
    {
        if ($newFinal <= 0 || !$projectId || isset(self::$freshProjects[$projectId])) return false;
        $others = (float) ProjectService::where('project_id', $projectId)
            ->when($exceptServiceId, fn ($q) => $q->whereKeyNot($exceptServiceId))->sum('final_price');
        return $others > 0;
    }

    /** Narxi bor xizmat olib tashlanadimi */
    public static function deleteAffectsPrice(ProjectService $svc): bool
    {
        return (float) $svc->final_price > 0 && $svc->project_id && !isset(self::$freshProjects[$svc->project_id]);
    }

    /**
     * Saqlashdan OLDIN (ProjectService creating/updating/deleting): loyiha narxiga
     * ta'sir qiladigan o'zgarish shu so'rovda Google Authenticator kodi bilan
     * tasdiqlanmagan bo'lsa — to'xtatadi. Har bir oyna kodni o'zi so'raydi; bu
     * qo'riqchi esa biror yo'l esdan chiqib qolsa ham kodsiz narx o'zgarmasligi uchun.
     */
    public static function guard(ProjectService $svc, string $event): void
    {
        if (!auth()->check() || ActionConfirmService::confirmed()) return;
        if ($svc->project_id && isset(self::$freshProjects[$svc->project_id])) return;

        $affects = match ($event) {
            'updating' => $svc->isDirty('final_price') && self::updateAffectsPrice((float) $svc->getOriginal('final_price'), (float) $svc->final_price),
            'creating' => self::createAffectsPrice($svc->project_id, (float) $svc->final_price),
            'deleting' => self::deleteAffectsPrice($svc),
            default    => false,
        };
        if (!$affects) return;

        $msg = ActionConfirmService::canConfirm(auth()->user())
            ? "Loyiha narxini o'zgartirish uchun Google Authenticator kodi kerak"
            : "Loyiha narxini o'zgartirish uchun avval Google Authenticator'ni ulang";
        Notification::make()->title($msg)->danger()->send();
        throw \Illuminate\Validation\ValidationException::withMessages(['confirm_code' => $msg]);
    }

    // ── Yozish (saqlangandan KEYIN) ───────────────────────────────────────────

    public static function serviceUpdated(ProjectService $svc): void
    {
        if (!$svc->wasChanged('final_price')) return;
        $old = (float) $svc->getOriginal('final_price');
        $new = (float) $svc->final_price;
        if (!self::updateAffectsPrice($old, $new)) return;
        self::record($svc, 'changed', $old, $new);
    }

    public static function serviceCreated(ProjectService $svc): void
    {
        $new = (float) $svc->final_price;
        if (!self::createAffectsPrice($svc->project_id, $new, $svc->id)) return;
        self::record($svc, 'added', 0, $new);
    }

    public static function serviceDeleted(ProjectService $svc): void
    {
        if (!self::deleteAffectsPrice($svc)) return;
        self::record($svc, 'removed', (float) $svc->final_price, 0);
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
