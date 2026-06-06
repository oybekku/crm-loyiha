<x-filament-panels::page>
    {{-- Oy/yil navigatori (loyiha ochilgan oyiga qarab) --}}
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <button wire:click="goMonth(-1)" title="Oldingi oy"
                style="background:#fff;border:1.5px solid #e5e7eb;border-radius:8px;width:34px;height:34px;cursor:pointer;font-size:18px;color:#374151;line-height:1">‹</button>
        <span style="font-size:14px;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:7px 16px;min-width:140px;text-align:center">📅 {{ $this->getMonthLabel() }}</span>
        <button wire:click="goMonth(1)" title="Keyingi oy"
                style="background:#fff;border:1.5px solid #e5e7eb;border-radius:8px;width:34px;height:34px;cursor:pointer;font-size:18px;color:#374151;line-height:1">›</button>
        <span style="font-size:12px;color:#9ca3af">— shu oyda ochilgan loyihalar</span>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
