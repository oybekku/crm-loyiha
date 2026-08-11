<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
@php $stamp = \App\Services\DesignSettingsService::get(); @endphp
<title>Qabul arizasi — {{ $project->number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #000; background: #fff; }

    .page { width: 210mm; margin: 0 auto; padding: 0; }
    .copy1, .copy2 { width: 100%; min-height: 297mm; padding: 10mm 12mm 8mm; display: flex; flex-direction: column; box-sizing: border-box; }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 3px solid #1d4ed8;
    }
    .order-num h1 { font-size: 18px; font-weight: 900; color: #1d4ed8; letter-spacing: -0.5px; }
    .order-num .value { display: flex; align-items: center; gap: 12px; }
    .order-date { color: #444; font-size: 13px; }

    .num-badge {
        display: inline-block;
        background: #1d4ed8;
        color: #fff;
        padding: 3px 12px;
        border-radius: 5px;
        font-weight: 800;
        font-size: 15px;
        letter-spacing: 0.05em;
    }

    /* Main table — qatorlar orasida chiziq yo'q, sof matn ko'rinishi */
    .main-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .main-table td {
        padding: 8px 8px;
        vertical-align: top;
    }
    .main-table .label {
        width: 40%;
        font-weight: 600;
        font-size: 13px;
        color: #111;
    }
    .main-table .value { font-size: 14px; color: #333; }
    .main-table tr:nth-child(even) td { background: #f3f4f6; }
    .section-title {
        font-size: 12px;
        font-weight: 800;
        color: #1d4ed8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin: 4px 0 6px;
        padding-top: 12px;
        border-top: 2px solid #1d4ed8;
    }
    .main-table .qr-cell {
        width: 110px;
        text-align: center;
        vertical-align: middle;
        background: #fff;
        border-left: 1.5px solid #c8d0db;
    }

    .qr-wrap { text-align: center; padding: 6px; }
    .qr-wrap img { width: 96px; height: 96px; display: block; margin: 0 auto 5px; }
    .qr-wrap p { font-size: 10px; color: #666; line-height: 1.4; }

    /* Ariza sarlavhasini tahrirlash (logo/matn/QR o'lchami — admin) */
    .hdr-logo-wrap, .hdr-qr-wrap, .hdr-text-wrap { position: relative; flex-shrink: 0; }
    .hdr-text-wrap { flex-shrink: 1; }
    .hdr-rsz {
        display: none;
        position: absolute; right: -10px; bottom: -10px; width: 20px; height: 20px;
        background: #2563eb; border: 2px solid #fff; border-radius: 50%;
        cursor: nwse-resize; z-index: 10; box-shadow: 0 1px 4px rgba(0,0,0,.4);
    }
    .header-editing-active .hdr-rsz { display: block; }
    .header-editing-active .hdr-logo-wrap,
    .header-editing-active .hdr-qr-wrap,
    .header-editing-active #arizaHeaderText {
        outline: 2px dashed #2563eb; outline-offset: 4px;
    }
    .header-editing-active #arizaHeaderText { cursor: text; }
    @media print { .hdr-rsz { display: none !important; } }

    /* Conditions */
    .conditions {
        border: 1.5px solid #c8d0db;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 8px;
        font-size: 12px;
        line-height: 1.6;
        color: #222;
        background: #fafbfc;
    }
    .conditions h3 {
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 5px;
        color: #1d4ed8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .conditions ol { padding-left: 18px; }
    .conditions li { margin-bottom: 3px; }

    /* Page break */
    .page-break { page-break-before: always; break-before: page; }
    .copy-divider {
        border: none; border-top: 2px dashed #9ca3af;
        margin: 12px 0 10px;
    }
    .copy-label {
        text-align: center; font-size: 11px; color: #9ca3af;
        margin: -32px 0 20px; background: #fff;
        display: inline-block; padding: 0 12px;
        position: relative; left: 50%; transform: translateX(-50%);
    }

    /* Signatures */
    .signatures {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        margin-top: 42px;
        padding-top: 6px;
    }
    .sig-block { flex: 1; position: relative; min-height: 110px; }
    .stamp-img {
        position: absolute;
        top: {{ (int) $stamp['stamp_top'] }}px;
        left: 19px;
        width: {{ (int) $stamp['stamp_width'] }}px;
        opacity: 0.92;
        pointer-events: none;
        z-index: 1;
    }
    .sig-title { font-size: 13px; font-weight: 700; margin-bottom: 35px; }
    .sig-line { border-bottom: 1.5px solid #000; margin-bottom: 6px; position: relative; z-index: 2; }
    .sig-label { font-size: 11px; color: #666; position: relative; z-index: 2; }
    .sig-name { font-size: 14px; font-weight: 700; margin-top: 4px; position: relative; z-index: 2; }
    .sig-right { text-align: right; }

    .footer-date {
        font-size: 11px;
        color: #666;
        margin-top: 12px;
        text-align: center;
        border-top: 1px solid #e5e7eb;
        padding-top: 8px;
    }

    .footer-date-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 12px;
        border-top: 1px solid #e5e7eb;
        padding-top: 8px;
    }
    .footer-date-row .footer-date { margin-top: 0; border-top: none; padding-top: 0; flex: 1; text-align: right; }
    .footer-loc-qr { display: flex; align-items: center; gap: 5px; flex-shrink: 0; }
    .footer-loc-qr img { width: 34px; height: 34px; display: block; }
    .footer-loc-qr span { font-size: 9px; color: #888; line-height: 1.3; max-width: 60px; }

    /* Mijoz nusxasi — ramkasiz, sof matn ko'rinishi */
    .conditions-plain {
        border: none;
        border-radius: 0;
        background: transparent;
        padding: 0;
    }

    /* Mijoz nusxasidagi katta BILDIRISHNOMA sarlavhasi */
    .bild-title {
        font-size: 24px;
        font-weight: 800;
        color: #1e3a5f;
        text-align: center;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .bild-title-line {
        border: none;
        border-top: 2px solid #cbd5e1;
        margin-bottom: 16px;
    }

    /* Mijoz imzosi — sudrab (drag) joylashtiriladigan, o'lchami o'zgartiriladigan rasm */
    .client-sig-wrap {
        position: absolute;
        top: 10px;
        left: 60px;
        width: 240px;
        cursor: move;
        z-index: 2;
    }
    .client-sig-img { width: 100%; display: block; filter: contrast(1.3); pointer-events: none; }
    .sig-rsz {
        position: absolute; right: -8px; bottom: -8px; width: 16px; height: 16px;
        border-radius: 50%; background: #2563eb; border: 2px solid #fff;
        cursor: nwse-resize; z-index: 3;
    }
    .sig-btn {
        margin-top: 10px; padding: 6px 14px; border-radius: 6px;
        border: 1px solid #93c5fd; background: #eff6ff; color: #1d4ed8;
        font-size: 12px; font-weight: 700; cursor: pointer;
    }

    /* Mijoz imzo chizish oynasi (to'liq ekran) */
    .sign-ov { position: fixed; inset: 0; background: #fff; z-index: 300; display: none; flex-direction: column; }
    .sign-box { background: #fff; width: 100%; height: 100%; display: flex; flex-direction: column; }
    .sign-hd { font-size: 16px; font-weight: 700; color: #111; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; flex-shrink: 0; }
    .sign-hd span { font-weight: 400; color: #9ca3af; font-size: 12px; }
    #signCanvas { border: none; background: #fff; touch-action: none; cursor: crosshair; display: block; flex: 1; width: 100%; min-height: 0; }
    .sign-actions { display: flex; gap: 10px; align-items: center; padding: 12px 16px; border-top: 1px solid #e5e7eb; flex-shrink: 0; }
    .sign-actions button { border: none; border-radius: 8px; padding: 13px 22px; font-size: 15px; font-weight: 700; cursor: pointer; }
    .s-clear { background: #f1f5f9; color: #475569; }
    .s-stu { background: #0891b2; color: #fff; }
    .s-stu:disabled { background: #cbd5e1; color: #64748b; cursor: not-allowed; }
    .s-cancel { background: #e5e7eb; color: #374151; }
    .s-done { background: #16a34a; color: #fff; }

    /* Ko'rinish rejimi — ekranda ham, chop etishda ham bir xil (WYSIWYG):
       "Mijoz nusxasi" tanlansa, mijoz shu yerda o'z imzosini qo'ya oladi,
       keyin xohlagan payt "Chop etish" bosiladi. */
    .copy2 { display: none; }
    body.view-mijoz .copy1 { display: none; }
    body.view-mijoz .copy2 { display: flex; flex-direction: column; }
    .view-btn.active { outline: 2px solid #fff; outline-offset: -2px; }

    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { padding: 10mm 12mm; }
        .no-print { display: none !important; }
        .sign-ov { display: none !important; }
    }

    @page { size: A4; margin: 0; }
</style>
</head>
<body>

<!-- Toolbar -->
<div class="no-print" style="text-align:center;padding:10px 16px;background:#1e40af;border-bottom:1px solid #1d4ed8;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
    <button id="btn-view-firma" class="view-btn" onclick="viewMode('firma')" style="background:#fff;color:#1d4ed8;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        👁 Firma nusxasi
    </button>
    <button id="btn-view-mijoz" class="view-btn" onclick="viewMode('mijoz')" style="background:#22c55e;color:#fff;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        👁 Mijoz nusxasi
    </button>
    <button onclick="window.print()" style="background:#fbbf24;color:#78350f;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        🖨 Chop etish
    </button>
    @if(auth()->user()?->isAdmin())
    <button id="stampEditBtn" onclick="toggleStampEdit()" style="background:#fff;color:#374151;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        ✏️ Pechatni joylashtirish
    </button>
    <button id="headerEditBtn" onclick="toggleHeaderEdit()" style="background:#fff;color:#374151;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        ✏️ Sarlavhani tahrirlash
    </button>
    @endif
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <button onclick="window.open('{{ route('print.project.obloshka', $project) }}?qavat=1','_blank')" style="background:#fff;color:#1d4ed8;border:none;padding:8px 18px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        ▦ Bir qavat
    </button>
    <button onclick="window.open('{{ route('print.project.obloshka', $project) }}?qavat=2','_blank')" style="background:#fff;color:#1d4ed8;border:none;padding:8px 18px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">
        ▦ Ikki qavat
    </button>
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <a href="{{ route('print.project.shartnoma', ['project' => $project, 'lang' => 'ru']) }}" style="background:#fff;color:#1d4ed8;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">📄 Shartnoma RU</a>
    <a href="{{ route('print.project.shartnoma', ['project' => $project, 'lang' => 'uz']) }}" style="background:#fff;color:#15803d;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">📄 Shartnoma UZ</a>
    <a href="{{ route('print.project.rozilik', $project) }}" style="background:#fff;color:#7c3aed;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">📝 Rozilik xati</a>
    <a href="{{ route('print.project.didox', $project) }}" target="_blank" style="background:#fff;color:#0891b2;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">🔷 DIDOX</a>
    <button onclick="window.open('{{ route('print.project.chegirma', $project) }}','_blank')" style="background:#fff;color:#b45309;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">🎟 Chegirma</button>
    <div style="width:1px;height:28px;background:rgba(255,255,255,0.3);"></div>
    <button onclick="window.close()" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:8px 18px;border-radius:6px;font-size:14px;cursor:pointer;">
        ✕ Yopish
    </button>
</div>

@if(auth()->user()?->isAdmin())
{{-- Pechat/imzo rasmini to'g'ridan-to'g'ri sudrab (tepaga/pastga) joylashtirish
     va burchagidan tortib o'lchamini o'zgartirish — mijoz imzosidagi kabi. --}}
<div id="stampToast" class="no-print" style="display:none;position:fixed;bottom:20px;right:20px;z-index:60;background:#16a34a;color:#fff;padding:10px 18px;border-radius:9px;font-size:13px;font-weight:700;box-shadow:0 10px 30px rgba(0,0,0,.3);font-family:Arial,sans-serif;">
    ✅ Saqlandi
</div>
<style>
    .stamp-rsz {
        position: absolute; width: 18px; height: 18px; right: -9px; bottom: -9px;
        background: #2563eb; border: 2px solid #fff; border-radius: 50%;
        cursor: ns-resize; z-index: 10; box-shadow: 0 1px 4px rgba(0,0,0,.4);
        display: none;
    }
    .stamp-editing-active { outline: 2px dashed #2563eb; outline-offset: 6px; cursor: ns-resize; }
    .stamp-editing-active .stamp-rsz { display: block; }
    @media print {
        .stamp-editing-active { outline: none !important; }
        .stamp-rsz { display: none !important; }
    }
</style>
<script>
    const STAMP_SAVE_URL = '{{ route('print.stamp-settings.save') }}';
    let stampEditOn = false;

    function toggleStampEdit() {
        stampEditOn = !stampEditOn;
        const btn = document.getElementById('stampEditBtn');
        document.querySelectorAll('.stamp-wrap').forEach(function (wrap) {
            wrap.classList.toggle('stamp-editing-active', stampEditOn);
        });
        if (btn) {
            btn.style.background = stampEditOn ? '#2563eb' : '#fff';
            btn.style.color = stampEditOn ? '#fff' : '#374151';
            btn.textContent = stampEditOn ? '✓ Joylashtirish tugadi' : "✏️ Pechatni joylashtirish";
        }
    }

    function stampToast() {
        const t = document.getElementById('stampToast');
        t.style.display = 'block';
        setTimeout(function () { t.style.display = 'none'; }, 1600);
    }

    function stampSave(width, top) {
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        fetch(STAMP_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ width: Math.round(width), top: Math.round(top) })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.ok) stampToast();
        });
    }

    // Har bir pechat rasmini "wrapper" ichiga o'raymiz — shu wrapperga top/width
    // qo'yiladi, rasm ichida 100% kenglikda turadi (mijoz imzosidagi #clientSigWrap
    // bilan bir xil naqsh). Faqat vertikal harakat + o'lcham — chapga-o'ngga surilmaydi.
    function setupStampDrag() {
        let mode = null, sy = 0, ot = 0, ow = 0;

        document.querySelectorAll('.stamp-img').forEach(function (img) {
            // .stamp-img'ning top/left/width'i CSS klassdan keladi (inline emas) —
            // shuning uchun hisoblangan (computed) qiymatni o'qib olamiz.
            const cs = getComputedStyle(img);
            const wrap = document.createElement('div');
            wrap.className = 'stamp-wrap';
            wrap.style.position = 'absolute';
            wrap.style.top    = cs.top;
            wrap.style.left   = cs.left;
            wrap.style.width  = cs.width;
            wrap.style.zIndex = cs.zIndex !== 'auto' ? cs.zIndex : '1';

            img.parentNode.insertBefore(wrap, img);
            wrap.appendChild(img);
            img.style.position = 'static';
            img.style.top   = '';
            img.style.left  = '';
            img.style.width = '100%';
            img.style.display = 'block';

            const rsz = document.createElement('div');
            rsz.className = 'stamp-rsz no-print';
            rsz.title = "O'lcham";
            wrap.appendChild(rsz);

            const start = (y, isResize) => {
                if (!stampEditOn) return;
                mode = isResize ? 'resize' : 'move';
                sy = y;
                ot = parseFloat(wrap.style.top) || 0;
                ow = parseFloat(wrap.style.width) || wrap.offsetWidth;
            };
            wrap.addEventListener('mousedown', e => { if (e.target === rsz) return; start(e.clientY, false); e.preventDefault(); });
            rsz.addEventListener('mousedown', e => { start(e.clientY, true); e.stopPropagation(); e.preventDefault(); });
            wrap.addEventListener('touchstart', e => { if (e.target === rsz) return; const t = e.touches[0]; start(t.clientY, false); }, {passive:true});
            rsz.addEventListener('touchstart', e => { const t = e.touches[0]; start(t.clientY, true); e.stopPropagation(); }, {passive:true});
        });

        const applyMove = (y) => {
            if (!mode) return;
            if (mode === 'move') {
                const newTop = ot + (y - sy);
                document.querySelectorAll('.stamp-wrap').forEach(w => w.style.top = newTop + 'px');
            } else {
                const newWidth = Math.max(80, ow + (y - sy) * 1.4);
                document.querySelectorAll('.stamp-wrap').forEach(w => w.style.width = newWidth + 'px');
            }
        };
        const endMove = () => {
            if (!mode) return;
            const wrap = document.querySelector('.stamp-wrap');
            if (wrap) stampSave(parseFloat(wrap.style.width) || ow, parseFloat(wrap.style.top) || ot);
            mode = null;
        };

        window.addEventListener('mousemove', e => applyMove(e.clientY));
        window.addEventListener('mouseup', endMove);
        window.addEventListener('touchmove', e => { if (!mode) return; const t = e.touches[0]; applyMove(t.clientY); }, {passive:true});
        window.addEventListener('touchend', endMove);
    }
    // Bu skript <head> ichida, .stamp-img rasmlari esa sahifaning pastida —
    // shuning uchun DOM to'liq tayyor bo'lguncha kutamiz (aks holda rasmlar
    // hali mavjud bo'lmaydi va hech narsa o'ralmaydi).
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupStampDrag);
    } else {
        setupStampDrag();
    }

    // ── Ariza sarlavhasi: logo/QR o'lchami (burchakdan sudrab) + firma matni
    //    (to'g'ridan-to'g'ri sahifada, contenteditable) — hammasi saqlanadi va
    //    barcha arizalarga umumiy qo'llaniladi (DesignSettingsService orqali). ──
    const HEADER_SAVE_URL = '{{ route('print.ariza-header-settings.save') }}';
    let headerEditOn = false;

    function toggleHeaderEdit() {
        headerEditOn = !headerEditOn;
        const btn = document.getElementById('headerEditBtn');
        document.querySelector('.header')?.classList.toggle('header-editing-active', headerEditOn);
        const textEl = document.getElementById('arizaHeaderText');
        if (textEl) textEl.contentEditable = headerEditOn ? 'true' : 'false';
        if (btn) {
            btn.style.background = headerEditOn ? '#2563eb' : '#fff';
            btn.style.color = headerEditOn ? '#fff' : '#374151';
            btn.textContent = headerEditOn ? '✓ Sarlavha tahrirlash tugadi' : '✏️ Sarlavhani tahrirlash';
        }
        if (!headerEditOn) saveHeaderText();
    }

    function saveHeaderSettings(payload) {
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        fetch(HEADER_SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(d => { if (d.ok) stampToast(); });
    }

    function saveHeaderText() {
        const txt = document.getElementById('arizaHeaderText');
        if (txt) saveHeaderSettings({ header_html: txt.innerHTML });
    }

    // Logo/QR — burchagidagi doiradan sudrab o'lchamini o'zgartirish.
    // Matn bloki — burchagidan sudrab shrift o'lchamini o'zgartirish.
    function setupHeaderResize() {
        let mode = null, sx = 0, startVal = 0, targetEl = null, fontMode = false;

        document.querySelectorAll('.hdr-rsz').forEach(handle => {
            const start = (x) => {
                if (!headerEditOn) return;
                const name = handle.dataset.target;
                targetEl = name === 'logo' ? document.getElementById('arizaLogo')
                         : name === 'qr'   ? document.getElementById('arizaQr')
                         : document.getElementById('arizaHeaderText');
                if (!targetEl) return;
                fontMode = name === 'text';
                mode = 'resize';
                sx = x;
                startVal = fontMode
                    ? parseFloat(getComputedStyle(targetEl).fontSize)
                    : targetEl.getBoundingClientRect().width;
            };
            handle.addEventListener('mousedown', e => { start(e.clientX); e.stopPropagation(); e.preventDefault(); });
            handle.addEventListener('touchstart', e => { const t = e.touches[0]; start(t.clientX); e.stopPropagation(); }, { passive: true });
        });

        const applyResize = (x) => {
            if (!mode || !targetEl) return;
            const delta = x - sx;
            if (fontMode) {
                const newSize = Math.max(9, Math.min(24, startVal + delta * 0.08));
                targetEl.style.fontSize = newSize + 'px';
            } else {
                const newW = Math.max(24, Math.min(200, startVal + delta));
                targetEl.style.width  = newW + 'px';
                targetEl.style.height = newW + 'px';
            }
        };
        const endResize = () => {
            if (!mode) return;
            mode = null;
            if (fontMode) {
                saveHeaderSettings({ header_font: Math.round(parseFloat(document.getElementById('arizaHeaderText').style.fontSize)) });
            } else if (targetEl.id === 'arizaLogo') {
                saveHeaderSettings({ logo_width: Math.round(targetEl.getBoundingClientRect().width) });
            } else if (targetEl.id === 'arizaQr') {
                saveHeaderSettings({ qr_width: Math.round(targetEl.getBoundingClientRect().width) });
            }
        };

        window.addEventListener('mousemove', e => applyResize(e.clientX));
        window.addEventListener('mouseup', endResize);
        window.addEventListener('touchmove', e => { if (!mode) return; const t = e.touches[0]; applyResize(t.clientX); }, { passive: true });
        window.addEventListener('touchend', endResize);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupHeaderResize);
    } else {
        setupHeaderResize();
    }
</script>
@endif

<div class="page">

<div class="copy1">
    <!-- HEADER -->
    <div class="header" style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
        <div class="hdr-logo-wrap">
            <img id="arizaLogo" src="/images/logo-mph.png" alt="MY PERFECT HOME" style="width:{{ (int) $stamp['ariza_logo_width'] }}px;height:{{ (int) $stamp['ariza_logo_width'] }}px;object-fit:contain;flex-shrink:0;display:block;">
            <div class="hdr-rsz no-print" data-target="logo" title="O'lcham"></div>
        </div>
        <div class="hdr-text-wrap">
            <div id="arizaHeaderText" style="text-align:center;font-size:{{ (int) $stamp['ariza_header_font'] }}px;line-height:1.6;outline:none;">{!! $stamp['ariza_header_html'] ?: '<strong style="font-size:1.15em;display:block;margin-bottom:2px;">&quot;MY PERFECT HOME&quot; MCHJ</strong>
                Toshkent shahri, Yangihayot tumani<br>
                Uzar ko\'chasi, 60-uy, 46-xona<br>
                MFO 01125&nbsp;&nbsp;INN 308515451<br>
                OKED 41100<br>
                Tel: +998 77 091 91 01, +998 99 468 19 91' !!}</div>
            <div class="hdr-rsz no-print" data-target="text" title="Shrift o'lchami"></div>
        </div>
        <div class="hdr-qr-wrap">
            <div class="qr-wrap" style="flex-shrink:0;">
                <img id="arizaQr" src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ urlencode(route('track.project', ltrim($project->number, '#'))) }}" alt="QR" style="width:{{ (int) $stamp['ariza_qr_width'] }}px;height:{{ (int) $stamp['ariza_qr_width'] }}px;">
                <p>Buyurtma raqamini<br>skanerlang</p>
            </div>
            <div class="hdr-rsz no-print" data-target="qr" title="O'lcham"></div>
        </div>
    </div>

    <table class="main-table order-num">
        <tr>
            <td class="label"><h1>Qabul arizasi</h1></td>
            <td class="value">
                <span class="num-badge">{{ $project->number }}</span>
                <span class="order-date">{{ $project->created_at->format('d.m.Y') }}</span>
            </td>
        </tr>
    </table>

    <!-- MAIN DATA TABLE -->
    <table class="main-table">
        <tr>
            <td class="label" id="lbl-client">Mijoz (F.I.Sh)</td>
            <td class="value">
                <strong>{{ $project->owner_name }}</strong>
                @if($project->phones)
                    @foreach($project->phones as $phone)
                        &nbsp;&nbsp;{{ is_array($phone) ? ($phone['phone'] ?? '') : $phone }}@if(!$loop->last),@endif
                    @endforeach
                @endif
            </td>
        </tr>
        <tr>
            <td class="label" id="lbl-type">Loyiha turi</td>
            <td class="value">
                @php
                    $cats = ['turar'=>'Turar-joy','tijorat'=>'Tijorat binosi','qishloq'=>'Qishloq qurilishi','sanoat'=>'Sanoat binosi','boshqa'=>'Boshqa'];
                @endphp
                {{ $cats[$project->category] ?? $project->category }}
            </td>
        </tr>
        <tr>
            <td class="label" id="lbl-name">Loyiha nomi</td>
            <td class="value">{{ $project->title ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label" id="lbl-address">Ob'ekt manzili</td>
            <td class="value">{{ $project->address }}</td>
        </tr>
    </table>

    <!-- JISMONIY SHAXS MA'LUMOTLARI -->
    <div class="section-title">Jismoniy shaxs ma'lumotlari</div>
    <table class="main-table">
        <tr>
            <td class="label">JSHSHIR (PINFL)</td>
            <td class="value">{{ $project->pinfl ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">F.I.Sh</td>
            <td class="value">{{ $project->owner_name }}</td>
        </tr>
        <tr>
            <td class="label">Pasport seriya va raqami</td>
            <td class="value">{{ $project->passport_series ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">Telefon raqam</td>
            <td class="value">
                @if($project->phones)
                    @foreach($project->phones as $phone)
                        {{ is_array($phone) ? ($phone['phone'] ?? '') : $phone }}@if(!$loop->last),@endif
                    @endforeach
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Arizachi turi</td>
            <td class="value">{{ \App\Models\Project::applicantTypeOptions()[$project->applicant_type] ?? '—' }}</td>
        </tr>
    </table>

    <!-- OBYEKT MA'LUMOTLARI -->
    <div class="section-title">Obyekt ma'lumotlari</div>
    <table class="main-table">
        <tr>
            <td class="label">Ko'chmas mulk (kadastr raqami)</td>
            <td class="value">{{ $project->cadastre_number ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">Obyektning joylashgan hudud</td>
            <td class="value">{{ \App\Models\Project::regionOptions()[$project->region] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Obyektning joylashgan tuman</td>
            <td class="value">{{ $project->district ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">Ro'yxatga olishga asos bo'luvchi hujjat turi</td>
            <td class="value">{{ $project->registration_basis ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">Obyekt manzili</td>
            <td class="value">{{ $project->address }}</td>
        </tr>
    </table>

    <div class="footer-date-row">
        @php
            $mapQuery = ($project->latitude && $project->longitude)
                ? $project->latitude.','.$project->longitude
                : $project->address;
        @endphp
        @if($mapQuery)
        <div class="footer-loc-qr">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=68x68&margin=0&data={{ urlencode('https://maps.google.com/?q='.$mapQuery) }}" alt="Lokatsiya QR">
            <span>Obyekt<br>lokatsiyasi</span>
        </div>
        @endif
        <div class="footer-date">
            <span id="footer-created">Ariza tuzilgan sana:</span>
            {{ $project->created_at->format('d.m.Y H:i') }}
            &nbsp;|&nbsp;
            <span id="footer-printed">Chop etilgan:</span>
            {{ now()->format('d.m.Y H:i') }}
        </div>
    </div>

</div><!-- /copy1 -->

<!-- ===== MIJOZ UCHUN 2-NUSXA (summalar yo'q) ===== -->
<div class="copy2">
    <div class="conditions conditions-plain">
        <div class="bild-title">BILDIRISHNOMA</div>
        <hr class="bild-title-line">
        <p style="line-height:1.75;text-align:justify;">
            Men {{ $project->created_at->format('d.m.Y') }} yildagi
            <strong>№{{ $project->seq_no }}</strong> sonli obyektning loyiha hujjatlarini ishlab chiqish haqidagi
            shartnomaga ko'ra buyurtmachi (mulkdor) <strong>{{ $project->owner_name }}</strong>
            ushbu bildirishnoma bilan shuni ma'lum qilamanki, Vazirlar Mahkamasining 2026 yil 13 apreldagi
            167-son qarori bilan tasdiqlangan "Yakka tartibdagi uy-joylar hamda kichik hajmdagi noturar bino va
            inshootlarni qurish hamda rekonstruksiya qilish ishlari yuzasidan xususiy qurilish nazoratini amalga
            oshirish tartibi to'g'risidagi nizom"ning 1-bob 5-bandiga (Xususiy qurilish nazorati buyurtmachining
            (mulkdor) ixtiyori bilan amalga oshiriladi. Mazkur holatlarda buyurtmachi (mulkdor) xususiy qurilish
            nazoratini amalga oshirish zarurati mavjud emasligi to'g'risida obyektning loyiha hujjatlarini ishlab
            chiqqan loyiha tashkilotiga u bilan obyektning loyiha hujjatlarini ishlab chiqish bo'yicha
            shartnomani tuzish jarayonida yozma ravishda bildirishnomani taqdim etadi) muvofiq mazkur
            obyektning loyiha hujjatlarini ishlab chiqilishi bilan cheklanaman va buyurtmachi (mulkdor) sifatida
            loyiha tashkiloti tomonidan xususiy qurilish nazoratini amalga oshirish zarurati mavjud emasligini
            ma'lum qilaman.
        </p>
        <p style="margin-top:10px;line-height:1.7;color:#444;">
            Yuqoridagi bildirishnomani o'qib chiqdim, unga nisbatan e'tiroz va qo'shimchalarim yo'q.
            Kelib chiqadigan salbiy oqibatlar uchun javobgarlikni o'z zimmamda bo'lishidan xabardorman.
        </p>
    </div>

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-title">Direktor:</div>
            <div class="sig-line"></div>
            <div class="sig-label">Imzo / muhr</div>
            <div class="sig-name">Sarimsakov J.</div>
            <img src="/images/imzo.png?v=2" class="stamp-img" alt="">
        </div>
        <div class="sig-block sig-right">
            <div class="sig-title">Mulkdor:</div>
            @if($project->signature_path)
            <div class="client-sig-wrap" id="clientSigWrap">
                <img src="{{ route('signature.view', $project) }}?t={{ time() }}" class="client-sig-img" id="clientSigImg" alt="">
                <div class="sig-rsz no-print" id="clientSigRsz" title="O'lcham"></div>
            </div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-label">Imzo</div>
            <div class="sig-name">{{ $project->owner_name }}</div>
            <div style="font-size:11px;color:#888;margin-top:3px;">shartlar bilan tanishib, rozilik bildirdi</div>
            <button type="button" class="no-print sig-btn" onclick="openSignPad()">✍️ Mijoz imzosi</button>
            @if($project->signature_path)
            <button type="button" class="no-print sig-btn" onclick="resetSigPos()" style="background:#f3f4f6;border-color:#d1d5db;color:#374151">↺ Joylashuvni tiklash</button>
            @endif
        </div>
    </div>

    <div class="footer-date">
        {{ $project->created_at->format('d.m.Y H:i') }}
        &nbsp;|&nbsp;
        {{ now()->format('d.m.Y H:i') }}
    </div>
</div><!-- /copy2 -->

</div><!-- /page -->

{{-- ✍️ Mijoz imzo chizish oynasi (to'liq ekran) --}}
<div class="sign-ov no-print" id="signOv">
    <div class="sign-box">
        <div class="sign-hd">✍️ Mijoz qo'l qo'ysin <span>— sichqoncha yoki barmoq bilan chizing</span></div>
        <canvas id="signCanvas"></canvas>
        <div class="sign-actions">
            <button class="s-clear" type="button" onclick="clearSign()">🧹 Tozalash</button>
            <button class="s-stu" type="button" id="stuCaptureBtn" onclick="captureFromSTU()" disabled title="STU planshetni ulang">🖊️ STU planshet bilan</button>
            <span style="flex:1"></span>
            <button class="s-cancel" type="button" onclick="closeSignPad()">Bekor</button>
            <button class="s-done" type="button" onclick="finishSign()">✓ Saqlash</button>
        </div>
    </div>
</div>

<script>
const SIG_SAVE_URL = @json(route('signature.save', $project));
const CSRF_TOKEN    = document.querySelector('meta[name=csrf-token]').content;
let signCtx = null, signDrawn = false, signInit = false;

function openSignPad(){
    const c = document.getElementById('signCanvas');
    document.getElementById('signOv').style.display = 'flex';
    requestAnimationFrame(() => {
        const r = c.getBoundingClientRect();
        c.width  = Math.round(r.width);
        c.height = Math.round(r.height);
        signCtx = c.getContext('2d');
        signCtx.lineWidth = 4; signCtx.lineCap = 'round'; signCtx.lineJoin = 'round'; signCtx.strokeStyle = '#152a6e';
        clearSign();
        if (!signInit) { setupSignDraw(c); signInit = true; }
    });
}
function closeSignPad(){ document.getElementById('signOv').style.display = 'none'; }
function clearSign(){
    if (!signCtx) return;
    const c = document.getElementById('signCanvas');
    signCtx.clearRect(0, 0, c.width, c.height);
    signDrawn = false;
}
function setupSignDraw(c){
    let drawing = false, lx = 0, ly = 0;
    const pos = e => { const r = c.getBoundingClientRect(); const sx = c.width / r.width, sy = c.height / r.height;
        const p = e.touches ? e.touches[0] : e; return [(p.clientX - r.left) * sx, (p.clientY - r.top) * sy]; };
    const start = e => { drawing = true; [lx, ly] = pos(e); e.preventDefault(); };
    const move  = e => { if (!drawing) return; const [x, y] = pos(e); signCtx.beginPath(); signCtx.moveTo(lx, ly); signCtx.lineTo(x, y); signCtx.stroke(); lx = x; ly = y; signDrawn = true; e.preventDefault(); };
    const end   = () => { drawing = false; };
    c.addEventListener('mousedown', start); c.addEventListener('mousemove', move); window.addEventListener('mouseup', end);
    c.addEventListener('touchstart', start, {passive:false}); c.addEventListener('touchmove', move, {passive:false}); window.addEventListener('touchend', end);
}
// Imzo atrofidagi bo'sh (shaffof) joyni kesib tashlaydi — shunda imzo
// hujjatda chiziqqa yaqin, "bitta chiziqda" turadi (katta bo'sh maydonda
// suzib qolmaydi).
function cropSignature(c){
    const ctx = c.getContext('2d');
    const {width: w, height: h} = c;
    const data = ctx.getImageData(0, 0, w, h).data;
    let minX = w, minY = h, maxX = 0, maxY = 0, found = false;
    for (let y = 0; y < h; y++) {
        for (let x = 0; x < w; x++) {
            if (data[(y * w + x) * 4 + 3] > 10) {
                found = true;
                if (x < minX) minX = x; if (x > maxX) maxX = x;
                if (y < minY) minY = y; if (y > maxY) maxY = y;
            }
        }
    }
    if (!found) return c;
    const pad = 8;
    minX = Math.max(0, minX - pad); minY = Math.max(0, minY - pad);
    maxX = Math.min(w - 1, maxX + pad); maxY = Math.min(h - 1, maxY + pad);
    const cw = maxX - minX + 1, ch = maxY - minY + 1;
    const out = document.createElement('canvas');
    out.width = cw; out.height = ch;
    out.getContext('2d').drawImage(c, minX, minY, cw, ch, 0, 0, cw, ch);
    return out;
}
async function finishSign(){
    if (!signDrawn) { alert("Avval mijoz qo'l qo'ysin"); return; }
    const c = document.getElementById('signCanvas');
    const dataUrl = cropSignature(c).toDataURL('image/png');
    try {
        const resp = await fetch(SIG_SAVE_URL, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN}, body: JSON.stringify({img: dataUrl})});
        const data = await resp.json();
        if (!data.ok) { alert('Xato: ' + (data.message || 'saqlanmadi')); return; }
        location.reload();
    } catch (e) { alert('Xatolik: ' + e.message); }
}

// Wacom STU planshetidan olingan imzo rasmini shu canvasga chizib qo'yadi —
// shundan keyin oddiy "✓ Saqlash" tugmasi (finishSign) o'zgarishsiz ishlaydi.
function setSignatureImage(dataUrl){
    const c = document.getElementById('signCanvas');
    const img = new Image();
    img.onload = () => {
        signCtx.clearRect(0, 0, c.width, c.height);
        const scale = Math.min(c.width / img.width, c.height / img.height);
        const w = img.width * scale, h = img.height * scale;
        signCtx.drawImage(img, (c.width - w) / 2, (c.height - h) / 2, w, h);
        signDrawn = true;
    };
    img.src = dataUrl;
}
window.setSignatureImage = setSignatureImage;

// ── Mijoz imzosini sudrab joylashtirish / o'lchamini o'zgartirish ──
// Joylashuv brauzerda (shu kompyuterda, shu loyiha uchun) eslab qolinadi.
const SIG_POS_KEY     = 'clientSigPos_' + {{ (int) $project->id }};
const SIG_POS_DEFAULT = {top: 10, left: 60, width: 240};

function applySigPos(pos){
    const wrap = document.getElementById('clientSigWrap');
    if (!wrap) return;
    wrap.style.top   = pos.top + 'px';
    wrap.style.left  = pos.left + 'px';
    wrap.style.width = pos.width + 'px';
}
function loadSigPos(){
    try { const raw = localStorage.getItem(SIG_POS_KEY); if (raw) return JSON.parse(raw); } catch (e) {}
    return null;
}
function saveSigPos(pos){
    try { localStorage.setItem(SIG_POS_KEY, JSON.stringify(pos)); } catch (e) {}
}
function resetSigPos(){
    try { localStorage.removeItem(SIG_POS_KEY); } catch (e) {}
    applySigPos(SIG_POS_DEFAULT);
}
function setupSigDrag(){
    const wrap = document.getElementById('clientSigWrap');
    if (!wrap) return;
    const rsz = document.getElementById('clientSigRsz');
    let mode = null, sx = 0, sy = 0, ol = 0, ot = 0, ow = 0;

    applySigPos(loadSigPos() || SIG_POS_DEFAULT);

    const posNow = () => ({
        top:   parseFloat(wrap.style.top)   || 0,
        left:  parseFloat(wrap.style.left)  || 0,
        width: parseFloat(wrap.style.width) || SIG_POS_DEFAULT.width,
    });
    const start = (x, y, isResize) => {
        mode = isResize ? 'resize' : 'move';
        sx = x; sy = y;
        const p = posNow(); ol = p.left; ot = p.top; ow = p.width;
    };
    const move = (x, y) => {
        if (!mode) return;
        if (mode === 'move') {
            wrap.style.left = (ol + (x - sx)) + 'px';
            wrap.style.top  = (ot + (y - sy)) + 'px';
        } else {
            wrap.style.width = Math.max(40, ow + (x - sx)) + 'px';
        }
    };
    const end = () => { if (mode) saveSigPos(posNow()); mode = null; };

    wrap.addEventListener('mousedown', e => { if (e.target === rsz) return; start(e.clientX, e.clientY, false); e.preventDefault(); });
    rsz.addEventListener('mousedown', e => { start(e.clientX, e.clientY, true); e.stopPropagation(); e.preventDefault(); });
    window.addEventListener('mousemove', e => move(e.clientX, e.clientY));
    window.addEventListener('mouseup', end);

    wrap.addEventListener('touchstart', e => { if (e.target === rsz) return; const t = e.touches[0]; start(t.clientX, t.clientY, false); }, {passive:true});
    rsz.addEventListener('touchstart', e => { const t = e.touches[0]; start(t.clientX, t.clientY, true); e.stopPropagation(); }, {passive:true});
    window.addEventListener('touchmove', e => { if (!mode) return; const t = e.touches[0]; move(t.clientX, t.clientY); }, {passive:true});
    window.addEventListener('touchend', end);
}
setupSigDrag();
</script>

<script>
function viewMode(mode) {
    document.body.classList.remove('view-firma', 'view-mijoz');
    document.body.classList.add('view-' + mode);
    document.getElementById('btn-view-firma').classList.toggle('active', mode === 'firma');
    document.getElementById('btn-view-mijoz').classList.toggle('active', mode === 'mijoz');
    // URL'ga yozib qo'yamiz — imzo saqlangach sahifa qayta yuklanganda ham
    // shu ko'rinish (Mijoz nusxasi) saqlanib qolishi uchun.
    const url = new URL(location.href);
    url.searchParams.set('view', mode);
    history.replaceState(null, '', url);
}
// Sahifa ochilganda — URL'dagi ?view= parametriga qarab boshlang'ich ko'rinish
viewMode(new URLSearchParams(location.search).get('view') === 'mijoz' ? 'mijoz' : 'firma');
</script>

{{-- Wacom STU-300 imzo planshetidan (WebHID orqali, mahalliy dastursiz)
     mijoz imzosini olish. Faqat https (yoki localhost) sahifada ishlaydi. --}}
<script type="module">
import SigSDK from "/js/wacom/signature-sdk.js";

let wacomSDK = null, wacomSigObj = null;

async function initWacom() {
    try {
        wacomSDK = await new SigSDK();
        wacomSigObj = new wacomSDK.SigObj();
        await wacomSigObj.setLicence(
            @json(config('services.wacom.sig_key')),
            @json(config('services.wacom.sig_secret'))
        );
        const btn = document.getElementById('stuCaptureBtn');
        if (wacomSDK.STUDevice.isHIDSupported()) {
            btn.disabled = false;
            btn.title = "STU planshetdan imzo olish";
        } else {
            btn.title = "Bu brauzer WebHID'ni qo'llab-quvvatlamaydi (Chrome/Edge ishlating)";
        }
    } catch (e) {
        console.error('Wacom Signature SDK ishga tushmadi', e);
    }
}
initWacom();

window.captureFromSTU = async function () {
    if (!wacomSDK || !wacomSigObj) { alert("Imzo qurilmasi tizimi hali tayyor emas, biroz kuting"); return; }
    let stuDevice = null;
    try {
        const devices = await wacomSDK.STUDevice.requestDevices();
        if (devices.length === 0) return;
        stuDevice = new wacomSDK.STUDevice(devices[0]);
        const config = new wacomSDK.Config();
        const captDialog = new wacomSDK.StuCaptDialog(stuDevice, config);

        captDialog.addEventListener(wacomSDK.EventType.OK, async function () {
            try {
                // pikselga aylantirish (dpi asosida) — imzoning haqiqiy nisbatini saqlaydi
                let w = Math.trunc((96 * wacomSigObj.getWidth(false) * 0.01) / 25.4);
                let h = Math.trunc((96 * wacomSigObj.getHeight(false) * 0.01) / 25.4);
                const scale = Math.min(300 / w, 200 / h);
                let rw = Math.trunc(w * scale);
                const rh = Math.trunc(h * scale);
                if (rw % 4 !== 0) rw += rw % 4;

                const image = await wacomSigObj.renderBitmap(
                    rw, rh, "image/png", 4, "#152a6e", "white", 0, 0,
                    wacomSDK.RenderFlags.RenderEncodeData.value
                );
                window.setSignatureImage(image);
            } catch (e) {
                alert("Imzoni o'qishda xato: " + e);
            }
            stuDevice.delete();
        });
        captDialog.addEventListener(wacomSDK.EventType.CANCEL, function () {
            stuDevice.delete();
        });

        await captDialog.open(wacomSigObj, "", "", null, wacomSDK.KeyType.SHA512, null);
    } catch (e) {
        alert("STU qurilmasi bilan bog'lanishda xato: " + e);
        if (stuDevice) stuDevice.delete();
    }
};
</script>
</body>
</html>
