<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class TodayCashPaymentsWidget extends Widget
{
    protected static string $view = 'filament.widgets.today-cash-payments';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -1;

    // Faqat menejer va admin ko'radi — boshqa rollarga (bajaruvchi, hisobchi)
    // kunlik naqd inkassa ro'yxati kerak emas.
    public static function canView(): bool
    {
        $user = auth()->user();
        return (bool) ($user?->isMenejer() || $user?->isAdmin());
    }

    public function getViewData(): array
    {
        $user    = auth()->user();
        $isAdmin = (bool) $user?->isAdmin();

        // Bugun tizimга KIRITILGAN naqd to'lovlar — payment_date emas, chunki
        // menejer eski oy uchun to'lovni orqaga sanani o'zgartirib kiritishi
        // mumkin (mijoz avvalgi oy qarzini yopganda). Shu sababli "bugungi"
        // degani — bugun QOG'OZ o'rniga tizimga yozib qo'yilgan to'lovlar.
        $query = Payment::query()
            ->with('project:id,owner_name')
            ->where('method', 'naqd')
            ->whereDate('created_at', today());

        // Menejer faqat o'zi qabul qilgan naqd pulni ko'radi (aynan shuni
        // kechqurun inkassaga topshiradi). Admin — barcha menejerlarnikini.
        if (!$isAdmin) {
            $query->where('created_by', $user->id);
        } else {
            $query->with('createdBy:id,name');
        }

        $payments = $query->orderByDesc('payment_date')->orderBy('created_at')->get();

        // Oylarga ajratish — payment_date qaysi oyga tegishli bo'lsa, o'sha
        // guruhga tushadi (mijoz qaysi oy qarzini yopgani shu orqali ko'rinadi).
        $groups = [];
        foreach ($payments as $p) {
            $key = $p->payment_date->format('Y-m');
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' => ucfirst($p->payment_date->translatedFormat('F Y')),
                    'sum'   => 0,
                    'items' => [],
                ];
            }
            $groups[$key]['sum'] += (float) $p->amount;
            $groups[$key]['items'][] = [
                'fish'   => $p->project?->owner_name ?? '—',
                'sana'   => $p->payment_date->format('d.m.Y'),
                'summa'  => (float) $p->amount,
                'note'   => $p->note,
                'manager'=> $isAdmin ? ($p->createdBy?->name ?? '—') : null,
            ];
        }
        // Eng yangi oy tepada tursin (payment_date bo'yicha kamayish tartibida allaqachon)
        krsort($groups);

        return [
            'isAdmin'    => $isAdmin,
            'groups'     => $groups,
            'totalSum'   => (float) $payments->sum('amount'),
            'totalCount' => $payments->count(),
        ];
    }
}
