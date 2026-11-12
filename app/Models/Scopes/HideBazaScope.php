<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Bazaga ko'chirilgan (eskirgan) loyihalar asosiy saytning HAMMA joyidan
 * yashiriladi: Kanban, ro'yxatlar, Arxiv, qidiruv, qarzlar, statistika.
 *
 * MUHIM: To'lov / xizmat / xarajat → loyiha bog'lanishlari (Payment::project,
 * ProjectService::project ...) bu scope'ni ATAYLAB olib tashlaydi — kassa
 * qoldig'i va xodimlar maoshi hisobi bir so'mga ham o'zgarmasligi uchun.
 * Bazadagi loyihani ko'rish: Project::withBaza() / Project::onlyBaza().
 */
class HideBazaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull($model->qualifyColumn('baza_at'));
    }
}
