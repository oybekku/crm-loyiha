@php
    $dark ??= false;
    $rtCurrent = request()->route()?->getName();
    $rtBase    = $dark
        ? ['bg' => 'transparent', 'border' => 'rgba(255,255,255,.18)', 'text' => '#cbd5e1']
        : ['bg' => '#fff', 'border' => '#d1d5db', 'text' => '#374151'];
@endphp
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
    @php $rtActive = $rtCurrent === 'filament.admin.pages.dashboard'; @endphp
    <a href="{{ route('filament.admin.pages.dashboard') }}" wire:navigate
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;padding:8px 16px;border-radius:8px;text-decoration:none;border:1px solid {{ $rtActive ? '#2563eb' : $rtBase['border'] }};background:{{ $rtActive ? '#2563eb' : $rtBase['bg'] }};color:{{ $rtActive ? '#fff' : $rtBase['text'] }}">
        📊 Statistika
    </a>

    @if(auth()->user()?->isAdmin())
    @php $rtActive = $rtCurrent === 'filament.admin.pages.monthly-report'; @endphp
    <a href="{{ route('filament.admin.pages.monthly-report') }}" wire:navigate
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;padding:8px 16px;border-radius:8px;text-decoration:none;border:1px solid {{ $rtActive ? '#2563eb' : $rtBase['border'] }};background:{{ $rtActive ? '#2563eb' : $rtBase['bg'] }};color:{{ $rtActive ? '#fff' : $rtBase['text'] }}">
        📅 Oylik hisobot
    </a>
    @endif

    @if(\App\Filament\Pages\Buxgalteriya::canAccess())
    @php $rtActive = $rtCurrent === 'filament.admin.pages.buxgalteriya'; @endphp
    <a href="{{ route('filament.admin.pages.buxgalteriya') }}" wire:navigate
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;padding:8px 16px;border-radius:8px;text-decoration:none;border:1px solid {{ $rtActive ? '#2563eb' : $rtBase['border'] }};background:{{ $rtActive ? '#2563eb' : $rtBase['bg'] }};color:{{ $rtActive ? '#fff' : $rtBase['text'] }}">
        💳 Buxgalteriya
    </a>
    @endif
</div>
