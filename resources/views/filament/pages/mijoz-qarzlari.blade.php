<x-filament-panels::page>
    @include('filament.pages.partials.client-debts')

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
    </script>
    @endscript
</x-filament-panels::page>
