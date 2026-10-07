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

    {{-- Jadval ko'rinishi: Kategoriya yorliqlari, ixcham qatorlar, FISH o'lchami, hover rangi --}}
    <style>
        @keyframes pg-spin { to { transform: rotate(360deg); } }
        /* Kategoriya yorlig'i — rang oqishi va yorug'lik faqat sichqoncha ustiga
           borganda; "Jarayonda" halqasi doim aylanadi */
        .pg-badge {
            position: relative; overflow: hidden;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            box-sizing: border-box; width: 112px; height: 24px; padding: 0 8px;
            border-radius: 20px; white-space: nowrap;
            font-size: 12px; font-weight: 700; color: #fff;
            text-shadow: 0 1px 1px rgba(0,0,0,.18);
            background: linear-gradient(110deg, var(--c1) 0%, var(--c2) 50%, var(--c1) 100%);
            background-size: 220% 100%;
            box-shadow: 0 1px 3px rgba(0,0,0,.15);
            animation: pg-flow 4s ease-in-out infinite;
            animation-play-state: paused; /* faqat sichqoncha ustiga borganda "jonlanadi" */
        }
        /* "Jarayonda" halqasi esa doim aylanadi (o'z inline animatsiyasi) */
        .pg-badge:hover { animation-play-state: running; box-shadow: 0 0 10px var(--c2), 0 1px 3px rgba(0,0,0,.15); }
        /* Matn rangi yorliqning o'zidan (--tx: oq, Jarayonda — qora); panelning umumiy
           jadval matni rangi bosib ketmasin */
        .fi-main .pg-badge, .fi-main .pg-badge * { color: var(--tx, #fff) !important; }
        .pg-badge[style*="--tx:#111827"] { text-shadow: none; }
        @keyframes pg-flow  { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @media (prefers-reduced-motion: reduce) { .pg-badge { animation: none; } }
        /* Qatorlar ixchamroq — Filament standarti py-4 (16px) o'rniga 6px */
        .fi-ta-table .fi-ta-text,
        .fi-ta-table .fi-ta-selection-cell > div { padding-top: 6px !important; padding-bottom: 6px !important; }
        /* Egasi (FISH) — barcha ro'yxatlardagi fish komponenti bilan bir xil o'lcham */
        .pl-owner-name, .pl-owner-name .fi-ta-text-item-label { font-size: 14.85px !important; font-weight: 600; line-height: 1.3; white-space: nowrap; }
        /* Qator ustiga sichqoncha borganda — och yashil (Filament standarti och kulrang) */
        .fi-ta-table .fi-ta-row:hover { background-color: #ecfdf5 !important; }
        .dark .fi-ta-table .fi-ta-row:hover { background-color: rgba(16, 185, 129, .10) !important; }
    </style>

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
