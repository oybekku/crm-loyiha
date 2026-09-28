{{-- Hisob tanlash: kompaniya hisoblari va xodimlar kartalari alohida guruhda, qoldig'i bilan.
     $model — wire:model nomi, $accountsList — FinancialAccount'lar, $selectedId — joriy qiymat. --}}
@php
    $fmtA = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $types = \App\Models\FinancialAccount::typeOptions();
    $company = $accountsList->filter(fn ($a) => !$a->is_personal && !$a->user_id);
    $staff   = $accountsList->filter(fn ($a) => $a->is_personal || $a->user_id);
@endphp
<select class="yb-in" wire:model.live="{{ $model }}">
    <option value="">— tanlang —</option>
    @if($company->isNotEmpty())
        <optgroup label="Kompaniya hisoblari">
            @foreach($company as $a)<option value="{{ $a->id }}">{{ $a->name ?: ($types[$a->type] ?? $a->type) }} · {{ $fmtA($accountBalances[$a->id] ?? 0) }}</option>@endforeach
        </optgroup>
    @endif
    @if(($showStaff ?? true) && $staff->isNotEmpty())
        <optgroup label="Xodimlar kartalari">
            @foreach($staff as $a)<option value="{{ $a->id }}">{{ $a->owner?->name ? $a->owner->name . ' — ' : '' }}{{ $a->name }} · {{ $fmtA($accountBalances[$a->id] ?? 0) }}</option>@endforeach
        </optgroup>
    @endif
</select>
@if($selectedId && isset($accountBalances[$selectedId]))
    <div class="yb-sub" style="margin-top:4px">Hisobda: <b class="{{ $accountBalances[$selectedId] < 0 ? 'yb-r' : 'yb-g' }}">{{ $fmtA($accountBalances[$selectedId]) }} so'm</b></div>
@endif
