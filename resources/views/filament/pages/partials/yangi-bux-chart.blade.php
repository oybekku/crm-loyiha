@php
    $fmt = fn ($v) => number_format((float) $v, 0, '.', ' ');
    $split ??= false;   // true — chiqim "Xarajat" va "Oylik / avans" bo'lib ko'rsatiladi
@endphp
{{-- ── Tushum va xarajatlar grafigi ── --}}
<div class="yb-card">
    <div class="yb-h">
        <span>{{ $chartTitle }} ({{ $curYear }})</span>
        <div class="yb-legend">
            <span><i style="background:#22c55e"></i>{{ $showProfit ? 'Tushum' : 'Kirim' }}</span>
            @if($split)
                <span><i style="background:#ef4444"></i>Xarajat</span>
                <span><i style="background:#f59e0b"></i>Oylik / avans</span>
            @else
                <span><i style="background:#ef4444"></i>{{ $showProfit ? 'Xarajat' : 'Chiqim' }}</span>
            @endif
            @if($showProfit)<span><i style="background:#3b82f6"></i>Sof foyda</span>@endif
        </div>
    </div>
    <div class="yb-chart">
        <div class="yb-grid">
            @foreach([1, .75, .5, .25, 0] as $g)
                <div><span>{{ $chartMax * $g >= 1e6 ? rtrim(rtrim(number_format($chartMax * $g / 1e6, 1, '.', ''), '0'), '.') . 'M' : ($chartMax * $g >= 1e3 ? round($chartMax * $g / 1e3) . 'K' : 0) }}</span></div>
            @endforeach
        </div>
        @foreach($chart as $c)
            <div wire:click="ybSetMonth({{ $c['m'] }})" style="cursor:pointer" class="yb-col {{ $c['m'] === $curMonth ? 'cur' : '' }}"
                 title="{{ $c['label'] }}: tushum {{ $fmt($c['income']) }}, xarajat {{ $fmt($c['other']) }}, oylik/avans {{ $fmt($c['salary']) }}, foyda {{ $fmt($c['profit']) }}">
                <div class="yb-bars">
                    <div class="yb-bar" style="background:#22c55e;height:{{ $c['income'] / $chartMax * 100 }}%"></div>
                    @if($split)
                        <div class="yb-bar" style="background:#ef4444;height:{{ max(0, $c['other']) / $chartMax * 100 }}%"></div>
                        <div class="yb-bar" style="background:#f59e0b;height:{{ $c['salary'] / $chartMax * 100 }}%"></div>
                    @else
                        <div class="yb-bar" style="background:#ef4444;height:{{ $c['expense'] / $chartMax * 100 }}%"></div>
                    @endif
                    @if($showProfit)<div class="yb-bar" style="background:#3b82f6;height:{{ max(0, $c['profit']) / $chartMax * 100 }}%"></div>@endif
                </div>
                <div class="yb-col-l">{{ $c['label'] }}</div>
            </div>
        @endforeach
    </div>
</div>
