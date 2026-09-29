{{-- Hisob tanlash: kompaniya hisoblari (qoldig'i bilan) va shaxsiy (ulush) hisoblar.
     Xodim kartalari pul hisobi emas — bu ro'yxatda yo'q.
     $model — wire:model nomi, $accountsList — FinancialAccount'lar, $selectedId — joriy qiymat.
     $monthMode — true: umumiy qoldiq o'rniga ochiq oyning puli ko'rsatiladi;
     $noBalance — true: summa ko'rsatilmaydi (masalan, doimiy to'lov shablonida). --}}
@php
    $fmtA = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $noBalance = $noBalance ?? false;
    $monthMode = $monthMode ?? false;
    $balMap    = $monthMode ? $monthAccountBalances : $accountBalances;
    $balPrefix = $monthMode ? \Illuminate\Support\Str::before($monthLabel, ' ') . ': ' : '';
    $types = \App\Models\FinancialAccount::typeOptions();
    $company  = $accountsList->filter(fn ($a) => !$a->is_personal && !$a->user_id);
    $personal = $accountsList->filter(fn ($a) => $a->is_personal && !$a->user_id);
@endphp
<select class="yb-in" wire:model.live="{{ $model }}">
    <option value="">— tanlang —</option>
    @if($company->isNotEmpty())
        <optgroup label="Kompaniya hisoblari">
            @foreach($company as $a)<option value="{{ $a->id }}">{{ $a->name ?: ($types[$a->type] ?? $a->type) }}@unless($noBalance) · {{ $balPrefix }}{{ $fmtA($balMap[$a->id] ?? 0) }}@endunless</option>@endforeach
        </optgroup>
    @endif
    @if($personal->isNotEmpty())
        <optgroup label="Shaxsiy hisoblar (ulush)">
            @foreach($personal as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
        </optgroup>
    @endif
</select>
@if($selectedId && !$noBalance && $monthMode)
    @php $mb = (float) ($balMap[$selectedId] ?? 0); @endphp
    <div class="yb-sub" style="margin-top:4px">{{ $monthLabel }} puli: <b class="{{ $mb < 0 ? 'yb-r' : 'yb-g' }}">{{ $fmtA($mb) }} so'm</b> · umumiy: {{ $fmtA($accountBalances[$selectedId] ?? 0) }}</div>
@elseif($selectedId && !$noBalance && isset($accountBalances[$selectedId]))
    <div class="yb-sub" style="margin-top:4px">Hisobda: <b class="{{ $accountBalances[$selectedId] < 0 ? 'yb-r' : 'yb-g' }}">{{ $fmtA($accountBalances[$selectedId]) }} so'm</b></div>
@endif
