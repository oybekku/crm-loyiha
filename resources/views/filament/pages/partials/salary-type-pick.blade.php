{{-- Oylik turi tanlagichi: Ishbay / Ma'muriy. Parametrlar: $model (wire:model), $value (joriy qiymat) --}}
<div style="margin-bottom:12px">
    <div style="font-size:12px;font-weight:600;margin-bottom:5px">Oylik turi <span style="color:#dc2626">*</span></div>
    <div style="display:flex;gap:8px">
        @foreach([
            'ishbay'  => ['🛠 Ishbay oylik', "toposyomka, ariza, eskiz — ishdan", '#2563eb', '#eff6ff'],
            'mamuriy' => ["🏢 Ma'muriy oylik", 'direktor, admin, buxgalter — firma daromadidan', '#7c3aed', '#f5f3ff'],
        ] as $tk => [$tl, $td, $tc, $tbg])
            <label style="flex:1;cursor:pointer;border:2px solid {{ $value === $tk ? $tc : '#e5e7eb' }};background:{{ $value === $tk ? $tbg : 'transparent' }};border-radius:10px;padding:8px 10px;font-size:13px;font-weight:700;color:{{ $value === $tk ? $tc : 'inherit' }}">
                <input type="radio" wire:model.live="{{ $model }}" value="{{ $tk }}" style="display:none">
                {{ $tl }}
                <span style="display:block;font-size:10.5px;font-weight:400;color:#6b7280;margin-top:2px">{{ $td }}</span>
            </label>
        @endforeach
    </div>
    @if($value === 'mamuriy')
        <div style="font-size:11px;color:#6b7280;margin-top:5px">Ish komissiyasiga bog'liq emas — "ortiqcha to'langan" hisoblanmaydi, hisobotda alohida ustunda turadi.</div>
    @endif
</div>
