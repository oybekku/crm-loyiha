<div class="yb-tbl-wrap">
<table class="yb-tbl">
    <thead><tr><th>#</th><th>Sana</th><th>Tur</th><th>Mijoz / Loyiha</th><th>Tavsif</th><th class="num">Kirim (so'm)</th><th class="num">Chiqim (so'm)</th><th>To'lov turi</th><th>Mas'ul</th><th>Holat</th></tr></thead>
    <tbody>
    @forelse($rows as $i => $r)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td style="white-space:nowrap">{{ $r['date']?->format('d.m.Y') }}</td>
            <td><span class="yb-pill {{ $r['type'] === 'kirim' ? 'k' : 'c' }}">{{ $r['type'] === 'kirim' ? 'Kirim' : 'Chiqim' }}</span></td>
            <td>{{ $r['who'] }}@if($r['who_sub'])<span class="yb-sub">{{ \Illuminate\Support\Str::limit($r['who_sub'], 30) }}</span>@endif</td>
            <td>{{ \Illuminate\Support\Str::limit($r['desc'], 40) }}</td>
            <td class="num yb-g">{{ $r['type'] === 'kirim' ? number_format($r['amount'], 0, '.', ' ') : '—' }}</td>
            <td class="num yb-r">{{ $r['type'] === 'chiqim' ? number_format($r['amount'], 0, '.', ' ') : '—' }}</td>
            <td>{{ $r['method'] }}</td>
            <td>{{ $r['user'] ?? '—' }}</td>
            <td><span class="yb-pill ok">Bajarildi</span></td>
        </tr>
    @empty
        <tr><td colspan="10" class="yb-empty">Bu oyda operatsiya yo'q</td></tr>
    @endforelse
    </tbody>
</table>
</div>
