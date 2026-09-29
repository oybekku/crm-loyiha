@php $fmt = fn ($v) => number_format((float) $v, 0, '.', ' '); @endphp
{{-- ── Tushum manbalari (xizmat turlari) ── --}}
<div class="yb-card">
    <div class="yb-h"><span>{{ $donutTitle }}</span></div>
    @if($donutTotal > 0)
        <div class="yb-donut-wrap">
            <div class="yb-donut">
                <svg viewBox="0 0 42 42" width="150" height="150" style="transform:rotate(-90deg)">
                    <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--yb-soft)" stroke-width="6"/>
                    @php $off = 0; @endphp
                    @foreach($donut as $d)
                        <circle cx="21" cy="21" r="15.915" fill="none" stroke="{{ $d['color'] }}" stroke-width="6"
                                stroke-dasharray="{{ $d['pct'] }} {{ 100 - $d['pct'] }}" stroke-dashoffset="{{ -$off }}"/>
                        @php $off += $d['pct']; @endphp
                    @endforeach
                </svg>
                <div class="yb-donut-c">
                    <b>{{ $donutTotal >= 1e6 ? round($donutTotal / 1e6, 1) . 'M' : $fmt($donutTotal) }}</b>
                    <span>so'm</span>
                </div>
            </div>
            <div class="yb-dl">
                @foreach($donut as $d)
                    @if(!empty($showAmounts))
                        <div><i style="background:{{ $d['color'] }}"></i>{{ $d['label'] }}<b>{{ $fmt($d['value']) }} so'm <span style="color:var(--yb-mu);font-weight:500">· {{ round($d['pct']) }}%</span></b></div>
                    @else
                        <div><i style="background:{{ $d['color'] }}"></i>{{ $d['label'] }}<b>{{ round($d['pct']) }}%</b></div>
                    @endif
                @endforeach
                @if(!empty($showAmounts))
                    <div style="border-top:1px solid var(--yb-soft);padding-top:8px;margin-top:4px;font-weight:700">Jami tushum<b>{{ $fmt($donutTotal) }} so'm</b></div>
                @endif
            </div>
        </div>
    @else
        <div class="yb-empty">Bu oyda tushum yo'q</div>
    @endif
</div>
