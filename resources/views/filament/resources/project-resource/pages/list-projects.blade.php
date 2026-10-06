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

    {{-- Kategoriya ustunidagi "Jarayonda" yorlig'ining aylanuvchi halqasi --}}
    <style>@keyframes pg-spin { to { transform: rotate(360deg); } }</style>

    {{-- Qatorga bosilganda ochiladigan tahrirlash oynasi (Kanbandagi bilan bir xil) --}}
    @livewire('project-edit-modal')
    @livewire('payment-modal')
    <div id="kb-notify-box" style="display:none;position:fixed;top:20px;right:20px;z-index:100000;color:#fff;padding:12px 18px;border-radius:10px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2)"></div>
    @script
    <script>
        Livewire.on('notify', (data) => {
            const d = Array.isArray(data) ? data[0] : data;
            const box = document.getElementById('kb-notify-box');
            if (!box) return;
            box.textContent = d.message || '';
            box.style.background = d.type === 'success' ? '#16a34a' : '#dc2626';
            box.style.display = 'block';
            setTimeout(() => box.style.display = 'none', 3500);
        });
        Livewire.on('print-receipt', (data) => {
            const d = Array.isArray(data) ? data[0] : data;
            if (d && d.paymentId && window.bhOpenChek) window.bhOpenChek(d.paymentId);
        });
    </script>
    @endscript
</x-filament-panels::page>
