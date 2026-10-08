<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $stamp   = \App\Services\DesignSettingsService::get();
    $canEdit = auth()->user()?->isAdmin() || auth()->user()?->isMenejer();
    $items   = $project->workChecklist();
    $formatPhone = function ($raw) {
        $digits = preg_replace('/\D/', '', (string) $raw);
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) {
            return '+'.substr($digits, 0, 3).' '.substr($digits, 3, 2).' '.substr($digits, 5, 3).' '.substr($digits, 8, 2).' '.substr($digits, 10, 2);
        }
        return $raw;
    };
    $phones = collect($project->phones ?: [])->map(fn ($p) => $formatPhone(is_array($p) ? ($p['phone'] ?? '') : $p))->filter()->implode(', ');
@endphp
<title>Ishlar ro'yxati — №{{ $project->seq_no ?: $project->id }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #111; background: #e5e7eb; }

    .sheet {
        width: 210mm; min-height: 297mm; margin: 18px auto; padding: 12mm 14mm 10mm;
        background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,.15);
        display: flex; flex-direction: column;
    }

    /* Sarlavha (Ariza bilan bir xil) */
    .header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-bottom: 8px; border-bottom: 3px solid #1d4ed8; }
    .qr-wrap { text-align: center; }
    .qr-wrap img { display: block; margin: 0 auto 4px; }
    .qr-wrap p { font-size: 10px; color: #666; line-height: 1.4; }

    .doc-title { text-align: center; margin: 22px 0 4px; font-size: 24px; font-weight: 800; color: #111; letter-spacing: -.3px; }
    .doc-sub   { text-align: center; font-size: 12px; color: #555; margin-bottom: 18px; }
    .doc-sub b { color: #1d4ed8; }

    /* Mijoz ma'lumotlari */
    .info { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .info td { padding: 7px 10px; vertical-align: top; font-size: 13px; }
    .info tr:nth-child(odd) td { background: #f3f4f6; }
    .info .lbl { width: 30%; font-weight: 600; color: #374151; }

    /* Ishlar ramkasi */
    .box { border: 1.5px solid #111; border-radius: 6px; padding: 18px 20px 14px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; grid-auto-flow: column; column-gap: 26px; row-gap: 14px; }
    .item { display: flex; align-items: flex-start; gap: 11px; font-size: 15px; line-height: 1.3; position: relative; }
    .item .cb {
        flex-shrink: 0; width: 20px; height: 20px; margin-top: -1px;
        border: 1.8px solid #111; border-radius: 3px; background: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .item .cb svg { width: 15px; height: 15px; display: none; }
    .item.done .cb svg { display: block; }
    .item.done .txt { color: #111; }
    .item:not(.done) .txt { font-weight: 700; }
    .item .num { flex-shrink: 0; min-width: 22px; text-align: right; font-weight: 700; color: #1d4ed8; }
    .item .acts { display: none; }

    .legend { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 14px; padding-top: 10px; border-top: 1px dashed #9ca3af; font-size: 11.5px; color: #4b5563; }
    .legend .mini { display: inline-flex; width: 13px; height: 13px; border: 1.4px solid #111; border-radius: 2px; vertical-align: -2px; align-items: center; justify-content: center; margin-right: 4px; }
    .legend .mini svg { width: 10px; height: 10px; }
    .legend .count { font-weight: 700; color: #111; font-size: 12.5px; }

    .note { margin-top: 16px; font-size: 12.5px; line-height: 1.6; color: #333; }

    .footer-date { font-size: 11px; color: #666; margin-top: auto; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }

    /* ── Tahrirlash (faqat ekranda) ── */
    .editable .item { cursor: pointer; border-radius: 6px; padding: 4px 6px; margin: -4px -6px; transition: background .12s; }
    .editable .item:hover { background: #eff6ff; }
    .editable .item:hover .cb { border-color: #2563eb; }
    .editable .item .acts { display: inline-flex; gap: 4px; margin-left: auto; flex-shrink: 0; }
    .act {
        width: 26px; height: 26px; border-radius: 6px; border: 1px solid #e5e7eb; background: #f9fafb;
        font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; padding: 0;
    }
    .act:hover { background: #fff; border-color: #9ca3af; }
    .act-del { background: #fef2f2; border-color: #fecaca; }
    .act-del:hover { background: #fee2e2; border-color: #f87171; }

    .hidden-box { margin-top: 12px; padding: 10px 12px; border: 1px dashed #d1d5db; border-radius: 8px; background: #f9fafb; font-size: 12.5px; color: #4b5563; }
    .hidden-box b { color: #111; }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: #fff; border: 1px solid #d1d5db; color: #6b7280; text-decoration: line-through; font-size: 12.5px; }
    .chip button { text-decoration: none; border: none; background: #dbeafe; color: #1d4ed8; border-radius: 999px; padding: 2px 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; }
    .chip .chip-del { background: #fee2e2; color: #b91c1c; }

    .panel { width: 210mm; margin: 0 auto; background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 4px 18px rgba(0,0,0,.08); display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .panel .hint { font-size: 12.5px; color: #374151; flex: 1 1 260px; }
    .panel input { flex: 1 1 220px; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; }
    .panel input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
    .btn { border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-add { background: #2563eb; color: #fff; }
    .btn-soft { background: #f3f4f6; color: #374151; }

    .toast { display: none; position: fixed; bottom: 20px; right: 20px; z-index: 60; background: #16a34a; color: #fff; padding: 10px 18px; border-radius: 9px; font-size: 13px; font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,.3); }
    .toast.err { background: #dc2626; }

    @media screen and (max-width: 820px) {
        .sheet, .panel { width: auto; margin-left: 16px; margin-right: 16px; }
        .sheet { padding: 20px 16px; min-height: 0; }
        .grid { grid-template-columns: 1fr; grid-auto-flow: row; }
    }
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .sheet { margin: 0; box-shadow: none; width: 210mm; height: 297mm; min-height: 0; }
        .editable .item:hover { background: none; }
        .item .acts { display: none !important; }
    }
    @page { size: A4; margin: 0; }
</style>
</head>
<body>

<!-- Toolbar -->
<div class="no-print" style="padding:10px 16px;background:#1e40af;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
    <span style="color:#fff;font-weight:700;font-size:14px;">✅ Qilinadigan ishlar ro'yxati</span>
    <button onclick="window.print()" style="background:#fbbf24;color:#78350f;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">🖨 Chop etish</button>
    <button onclick="window.close()" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:8px 18px;border-radius:6px;font-size:14px;cursor:pointer;">✕ Yopish</button>
</div>

@if($canEdit)
<div class="panel no-print" style="margin-top:16px;">
    <div class="hint">☝️ Bajarilgan ishni <b>bosing</b> — galochka qo'yiladi (yana bossangiz olinadi).
        <b>🙈</b> — shu mijozga kerak bo'lmagan ishni yashirish, <b>🗑</b> — qo'shilgan ishni o'chirish. Avtomatik saqlanadi.</div>
    <input id="newItem" type="text" maxlength="120" placeholder="Qo'shimcha ish nomi..." onkeydown="if(event.key==='Enter'){addItem()}">
    <button class="btn btn-add" onclick="addItem()">＋ Qo'shish</button>
    <button class="btn btn-soft" onclick="setAll(true)">Hammasi ✓</button>
    <button class="btn btn-soft" onclick="setAll(false)">Tozalash</button>
</div>
@endif

<div class="sheet">
    <div class="header">
        <img src="/images/logo-mph.png" alt="MY PERFECT HOME" style="width:{{ (int) $stamp['ariza_logo_width'] }}px;height:{{ (int) $stamp['ariza_logo_width'] }}px;object-fit:contain;flex-shrink:0;display:block;">
        <div style="text-align:center;font-size:{{ (int) $stamp['ariza_header_font'] }}px;line-height:1.6;">{!! $stamp['ariza_header_html'] ?: '<strong style="font-size:1.15em;display:block;margin-bottom:2px;">&quot;MY PERFECT HOME&quot; MCHJ</strong>
            Toshkent shahri, Yangihayot tumani<br>
            Uzar ko\'chasi, 60-uy, 46-xona<br>
            MFO 01125&nbsp;&nbsp;INN 308515451<br>
            OKED 41100<br>
            Tel: +998 77 091 91 01, +998 99 468 19 91' !!}</div>
        <div class="qr-wrap" style="flex-shrink:0;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ urlencode(route('track.project', ltrim($project->number, '#'))) }}" alt="QR" style="width:{{ (int) $stamp['ariza_qr_width'] }}px;height:{{ (int) $stamp['ariza_qr_width'] }}px;">
            <p>Buyurtma holatini<br>skanerlang</p>
        </div>
    </div>

    <div class="doc-title">Qilinadigan ishlar ro'yxati</div>
    <div class="doc-sub">Ma'lumotnoma · Loyiha <b>№{{ $project->seq_no ?: $project->id }}</b> · {{ $project->created_at->format('d.m.Y') }}</div>

    <table class="info">
        <tr><td class="lbl">Mijoz (F.I.Sh)</td><td><strong>{{ $project->owner_name }}</strong></td></tr>
        <tr><td class="lbl">Telefon</td><td>{{ $phones ?: '—' }}</td></tr>
        <tr><td class="lbl">Ob'ekt manzili</td><td>{{ $project->address ?: '—' }}</td></tr>
        @if($project->cadastre_number)
        <tr><td class="lbl">Kadastr raqami</td><td>{{ $project->cadastre_number }}</td></tr>
        @endif
    </table>

    <div class="box {{ $canEdit ? 'editable' : '' }}">
        <div class="grid" id="grid"></div>
        <div class="legend">
            <span>
                <span class="mini"><svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6"/></svg></span>— bajarilgan
                &nbsp;&nbsp;&nbsp;
                <span class="mini"></span>— bajarilishi kerak
            </span>
            <span class="count" id="count"></span>
        </div>
    </div>
    @if($canEdit)
    <div class="hidden-box no-print" id="hiddenBox" style="display:none"></div>
    @endif

    <div class="note">
        Hurmatli mijoz! Bo'sh katakchali (<b>belgilanmagan</b>) ishlar hali bajarilishi kerak bo'lgan ishlardir.
        Savollar bo'yicha yuqoridagi telefon raqamlariga murojaat qiling.
    </div>

    <div class="footer-date">Chop etilgan: {{ now()->format('d.m.Y H:i') }}</div>
</div>

<div id="toast" class="toast no-print">✅ Saqlandi</div>

<script>
    const CAN_EDIT = @json($canEdit);
    const SAVE_URL = @json(route('print.project.ishlar.save', $project));
    let items = @json($items);

    const CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6"/></svg>';

    function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    function render() {
        const grid = document.getElementById('grid');
        const visible = items.map(function (it, i) { return { it: it, i: i }; }).filter(function (v) { return !v.it.hidden; });
        // Ikki ustun: chapda birinchi yarmi, o'ngda qolgani (rasmdagidek)
        grid.style.gridTemplateRows = 'repeat(' + Math.max(1, Math.ceil(visible.length / 2)) + ', auto)';
        grid.innerHTML = visible.map(function (v, n) {
            const it = v.it, i = v.i;
            return '<div class="item' + (it.done ? ' done' : '') + '" data-i="' + i + '">'
                + '<span class="num">' + (n + 1) + '.</span>'
                + '<span class="cb">' + CHECK + '</span>'
                + '<span class="txt">' + esc(it.label) + '</span>'
                + '<span class="acts no-print">'
                +   '<button type="button" class="act" title="Yashirish (shu mijozga kerak emas)" data-hide="' + i + '">🙈</button>'
                +   (it.extra ? '<button type="button" class="act act-del" title="O\'chirish" data-del="' + i + '">🗑</button>' : '')
                + '</span>'
                + '</div>';
        }).join('') || '<div style="color:#6b7280;font-size:13px;">Ro\'yxat bo\'sh</div>';
        const done = visible.filter(function (v) { return v.it.done; }).length;
        document.getElementById('count').textContent = 'Bajarildi: ' + done + ' / ' + visible.length;

        const box = document.getElementById('hiddenBox');
        if (box) {
            const hid = items.map(function (it, i) { return { it: it, i: i }; }).filter(function (v) { return v.it.hidden; });
            box.style.display = hid.length ? '' : 'none';
            box.innerHTML = '🙈 <b>Yashirilgan ishlar (' + hid.length + ')</b> — chop etilmaydi. Kerak bo\'lsa "Qaytarish"ni bosing:'
                + '<div class="chips">' + hid.map(function (v) {
                    return '<span class="chip">' + esc(v.it.label)
                        + '<button type="button" data-show="' + v.i + '">↺ Qaytarish</button>'
                        + (v.it.extra ? '<button type="button" class="chip-del" data-del="' + v.i + '">🗑</button>' : '')
                        + '</span>';
                }).join('') + '</div>';
        }
    }

    let saveTimer = null;
    function save() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    done:  items.filter(function (it) { return !it.extra && it.done; }).map(function (it) { return it.key; }),
                    extra: items.filter(function (it) { return it.extra; }).map(function (it) { return { id: it.key, label: it.label, done: it.done }; }),
                    hidden: items.filter(function (it) { return it.hidden; }).map(function (it) { return it.key; }),
                }),
            }).then(function (r) {
                if (!r.ok) throw new Error(r.status);
                toast('✅ Saqlandi', false);
            }).catch(function () {
                toast('⚠️ Saqlanmadi — sahifani yangilang', true);
            });
        }, 300);
    }

    function toast(msg, err) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.toggle('err', err);
        t.style.display = 'block';
        clearTimeout(t._h);
        t._h = setTimeout(function () { t.style.display = 'none'; }, 1600);
    }

    function addItem() {
        const inp = document.getElementById('newItem');
        const label = inp.value.trim();
        if (!label) { inp.focus(); return; }
        items.push({ key: 'x' + Date.now().toString(36), label: label, done: false, extra: true, hidden: false });
        inp.value = '';
        render(); save();
    }

    function setAll(v) {
        items.forEach(function (it) { if (!it.hidden) it.done = v; });
        render(); save();
    }

    // Yashirish / qaytarish / o'chirish tugmalari (ro'yxatda ham, yashirilganlar qutisida ham)
    function handleAction(e) {
        const del = e.target.closest('[data-del]');
        if (del) {
            const i = +del.dataset.del;
            if (!confirm('"' + items[i].label + '" butunlay o\'chirilsinmi?')) return true;
            items.splice(i, 1);
            render(); save();
            return true;
        }
        const hide = e.target.closest('[data-hide]');
        if (hide) { items[+hide.dataset.hide].hidden = true; render(); save(); return true; }
        const show = e.target.closest('[data-show]');
        if (show) { items[+show.dataset.show].hidden = false; render(); save(); return true; }
        return false;
    }

    if (CAN_EDIT) {
        document.getElementById('hiddenBox').addEventListener('click', handleAction);
        document.getElementById('grid').addEventListener('click', function (e) {
            if (handleAction(e)) return;
            const row = e.target.closest('.item');
            if (!row) return;
            const it = items[+row.dataset.i];
            it.done = !it.done;
            render(); save();
        });
    }

    render();
</script>
</body>
</html>
