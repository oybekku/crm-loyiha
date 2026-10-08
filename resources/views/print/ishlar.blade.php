<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $isAdmin  = (bool) auth()->user()?->isAdmin();
    $canEdit  = $isAdmin || auth()->user()?->isMenejer();
    $items    = $project->workChecklist();
    $template = \App\Models\Project::workItems();
    $formatPhone = function ($raw) {
        $digits = preg_replace('/\D/', '', (string) $raw);
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) {
            return '+'.substr($digits, 0, 3).' '.substr($digits, 3, 2).' '.substr($digits, 5, 3).' '.substr($digits, 8, 2).' '.substr($digits, 10, 2);
        }
        return $raw;
    };
    $phones = collect($project->phones ?: [])->map(fn ($p) => $formatPhone(is_array($p) ? ($p['phone'] ?? '') : $p))->filter()->implode(', ');

    // Firma telefoni — shahar (tenant) saytida o'sha shaharning raqami
    $tenantPhone = config('tenants')[request()->getHost()]['phone'] ?? null;
    $firmPhones  = $tenantPhone ? [$formatPhone($tenantPhone)] : ['+998 77 091 91 01', '+998 99 468 19 91'];

    $svg = fn ($d) => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'.$d.'</svg>';
    $ICON_USER  = $svg('<path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.2-8 5v1.5c0 .3.2.5.5.5h15c.3 0 .5-.2.5-.5V19c0-2.8-3.6-5-8-5Z"/>');
    $ICON_PHONE = $svg('<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2Z"/>');
    $ICON_PIN   = $svg('<path d="M12 2a7 7 0 0 0-7 7c0 5.3 7 13 7 13s7-7.7 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/>');
    $ICON_DOC   = $svg('<path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm1.5 15h-7v-1.5h7V17Zm0-3.5h-7V12h7v1.5ZM13 9V3.5L18.5 9H13Z"/>');
@endphp
<title>Ishlar ro'yxati — №{{ $project->seq_no ?: $project->id }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #111; background: #e5e7eb; }

    .sheet {
        width: 210mm; min-height: 297mm; margin: 18px auto; padding: 13mm 13mm 9mm;
        background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,.15);
        display: flex; flex-direction: column;
        font-family: 'Segoe UI', Roboto, Arial, sans-serif; color: #0f172a;
    }
    .sheet, .sheet * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* Sarlavha + firma telefoni */
    .top { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 16px; }
    .doc-title { font-size: 30px; font-weight: 800; letter-spacing: -.5px; line-height: 1.1; color: #0f172a; }
    .doc-sub   { margin-top: 6px; font-size: 14px; color: #64748b; }
    .doc-sub b { color: #2563eb; }
    .firm { text-align: right; flex-shrink: 0; }
    .firm .fn { font-size: 11px; font-weight: 800; letter-spacing: .08em; color: #64748b; text-transform: uppercase; }
    .firm .fp { display: inline-flex; align-items: center; gap: 7px; margin-top: 5px; padding: 7px 12px; border-radius: 10px; background: #eff6ff; color: #1e3a8a; font-weight: 700; font-size: 13px; line-height: 1.35; text-align: left; }
    .firm .fp svg { width: 16px; height: 16px; flex-shrink: 0; color: #2563eb; }

    /* Mijoz ma'lumotlari — kartochka */
    .info { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px 0; border: 1px solid #dbe4f0; border-radius: 12px; background: #f8fafc; padding: 14px 6px; margin-bottom: 18px; }
    .info .f { display: flex; gap: 11px; align-items: flex-start; padding: 0 14px; min-width: 0; }
    .info .f + .f:not(.nl) { border-left: 1px solid #e2e8f0; }
    .info .ic { flex-shrink: 0; width: 32px; height: 32px; border-radius: 8px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; }
    .info .ic svg { width: 17px; height: 17px; }
    .info .l { font-size: 11.5px; color: #64748b; margin-bottom: 3px; }
    .info .v { font-size: 13.5px; font-weight: 700; color: #0f172a; line-height: 1.35; word-break: break-word; }

    /* Ishlar jadvali */
    .works-wrap { border: 1px solid #dbe4f0; border-radius: 12px; overflow: hidden; }
    .works { width: 100%; border-collapse: collapse; font-size: 14px; }
    .works thead th { background: linear-gradient(180deg, #3b6fc4, #2b5aa8); color: #fff; font-weight: 700; font-size: 14px; text-align: left; padding: 12px 14px; border-right: 1px solid rgba(255,255,255,.18); }
    .works thead th:last-child { border-right: none; }
    .works td { padding: 11px 14px; vertical-align: middle; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
    .works td:last-child { border-right: none; }
    .works .c-num  { width: 54px; text-align: center !important; font-weight: 700; }
    .works .c-st   { width: 82px; text-align: center !important; }
    .works .c-resp { width: 170px; }
    .works .w-title { font-weight: 700; font-size: 15.4px; line-height: 1.3; color: #0f172a; }
    .works .w-note  { font-style: italic; line-height: 1.35; white-space: pre-line; color: #64748b; font-size: 13px; margin-top: 3px; }
    .cb {
        width: 22px; height: 22px; border: 1.6px solid #64748b; border-radius: 5px; background: #fff;
        display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;
    }
    .cb svg { width: 16px; height: 16px; display: none; }
    tr.done .cb { background: #2563eb; border-color: #2563eb; }
    tr.done .cb svg { display: block; }
    .pill { display: inline-flex; align-items: center; gap: 8px; padding: 7px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 600; line-height: 1.25; background: #e0edff; color: #1e3a8a; }
    .pill svg { width: 15px; height: 15px; flex-shrink: 0; color: #1d4ed8; }
    .pill.org { background: #dcf5e7; color: #14532d; }
    .pill.org svg { color: #15803d; }
    .pill.other { background: #f1f5f9; color: #334155; }
    .pill.other svg { color: #64748b; }
    .empty-row td { text-align: center; color: #6b7280; padding: 16px; }

    .legend { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 10px; font-size: 12px; color: #64748b; }
    .legend .mini { display: inline-flex; width: 14px; height: 14px; border: 1.4px solid #64748b; border-radius: 3px; vertical-align: -3px; align-items: center; justify-content: center; margin-right: 5px; background: #fff; }
    .legend .mini.on { background: #2563eb; border-color: #2563eb; }
    .legend .mini svg { width: 10px; height: 10px; }
    .legend .count { font-weight: 700; color: #0f172a; font-size: 13px; }

    .note { margin-top: 14px; font-size: 12.5px; line-height: 1.6; color: #475569; }
    .footer-date { font-size: 11px; color: #94a3b8; margin-top: auto; padding-top: 6px; text-align: center; border-top: 1px solid #e2e8f0; }

    /* ── Tahrirlash (faqat ekranda) ── */
    .editable .c-st { cursor: pointer; }
    .editable .c-st:hover { background: #eff6ff; }
    .editable .c-st:hover .cb { border-color: #2563eb; }
    .acts { display: flex; gap: 2px; margin-top: 6px; }
    .act {
        width: 26px; height: 26px; border-radius: 6px; border: 1px solid #e5e7eb; background: #f9fafb;
        font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; padding: 0; margin: 1px;
    }
    .act:hover { background: #fff; border-color: #9ca3af; }
    .act-del { background: #fef2f2; border-color: #fecaca; }
    .act-del:hover { background: #fee2e2; border-color: #f87171; }

    .hidden-box { margin-top: 12px; padding: 10px 12px; border: 1px dashed #d1d5db; border-radius: 8px; background: #f9fafb; font-size: 12.5px; color: #4b5563; }
    .hidden-box b { color: #111; }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: #fff; border: 1px solid #d1d5db; color: #6b7280; font-size: 12.5px; }
    .chip > span { text-decoration: line-through; }
    .chip button { border: none; background: #dbeafe; color: #1d4ed8; border-radius: 999px; padding: 2px 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; }
    .chip .chip-del { background: #fee2e2; color: #b91c1c; }

    .panel { width: 210mm; margin: 16px auto 0; background: #fff; border-radius: 10px; padding: 12px 16px; box-shadow: 0 4px 18px rgba(0,0,0,.08); display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .panel .hint { font-size: 12.5px; color: #374151; flex: 1 1 320px; line-height: 1.5; }
    .btn { border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-add { background: #2563eb; color: #fff; }
    .btn-soft { background: #f3f4f6; color: #374151; }
    .btn-ok { background: #16a34a; color: #fff; }

    /* Modal oynalar */
    .ov { display: none; position: fixed; inset: 0; z-index: 80; background: rgba(15,23,42,.55); align-items: flex-start; justify-content: center; padding: 40px 16px; overflow-y: auto; }
    .ov.open { display: flex; }
    .modal { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; box-shadow: 0 25px 60px rgba(0,0,0,.35); }
    .modal.wide { max-width: 860px; }
    .modal-h { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; font-size: 16px; font-weight: 800; display: flex; justify-content: space-between; align-items: center; }
    .modal-h .x { border: none; background: #f3f4f6; width: 30px; height: 30px; border-radius: 8px; font-size: 16px; cursor: pointer; }
    .modal-b { padding: 16px 20px; }
    .modal-f { padding: 12px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px; position: sticky; bottom: 0; background: #fff; border-radius: 0 0 14px 14px; }
    .fld { margin-bottom: 12px; }
    .fld label { display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 4px; }
    .fld textarea, .fld input, .trow textarea, .trow input {
        width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13.5px; font-family: inherit; outline: none; resize: vertical;
    }
    .fld textarea:focus, .fld input:focus, .trow textarea:focus, .trow input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
    .resp-pick { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
    .resp-pick button { border: 1px solid #d1d5db; background: #f9fafb; border-radius: 999px; padding: 4px 10px; font-size: 12px; cursor: pointer; }
    .resp-pick button:hover { background: #eff6ff; border-color: #93c5fd; }
    .warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 9px 12px; font-size: 12.5px; margin-bottom: 12px; }
    .trow { display: grid; grid-template-columns: 30px 1fr 150px auto; gap: 8px; align-items: start; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .trow .n { font-weight: 800; color: #1d4ed8; padding-top: 8px; text-align: right; }
    .trow .col { display: flex; flex-direction: column; gap: 6px; }
    .trow .note-in { font-style: italic; }
    .trow .btns { display: flex; flex-direction: column; gap: 2px; }

    .toast { display: none; position: fixed; bottom: 20px; right: 20px; z-index: 90; background: #16a34a; color: #fff; padding: 10px 18px; border-radius: 9px; font-size: 13px; font-weight: 700; box-shadow: 0 10px 30px rgba(0,0,0,.3); }
    .toast.err { background: #dc2626; }

    @media screen and (max-width: 820px) {
        .sheet, .panel { width: auto; margin-left: 16px; margin-right: 16px; }
        .sheet { padding: 20px 12px; min-height: 0; }
        .works { font-size: 13px; }
        .works .c-resp { width: 90px; }
        .top { flex-direction: column; align-items: flex-start; }
        .firm { text-align: left; }
        .doc-title { font-size: 24px; }
        .info { grid-template-columns: 1fr; }
        .info .f + .f:not(.nl) { border-left: none; }
        .works .c-resp { width: auto; }
        .works td, .works thead th { padding: 9px 8px; }
        .trow { grid-template-columns: 22px 1fr; }
        .trow .resp-col, .trow .btns { grid-column: 2; }
        .trow .btns { flex-direction: row; }
    }
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .sheet { margin: 0; box-shadow: none; width: 210mm; min-height: 296mm; }
        .editable .c-st:hover { background: none; }
    }
    @page { size: A4; margin: 0; }
</style>
</head>
<body>

<!-- Toolbar -->
<div class="no-print" style="padding:10px 16px;background:#1e40af;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
    <span style="color:#fff;font-weight:700;font-size:14px;">✅ Qilinadigan ishlar ro'yxati</span>
    <button onclick="window.print()" style="background:#fbbf24;color:#78350f;border:none;padding:8px 22px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">🖨 Chop etish</button>
    @if($isAdmin)
    <button onclick="openTemplate()" style="background:#fff;color:#1d4ed8;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;font-weight:700;">⚙️ Umumiy ro'yxatni tahrirlash</button>
    @endif
    <button onclick="window.close()" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.4);padding:8px 18px;border-radius:6px;font-size:14px;cursor:pointer;">✕ Yopish</button>
</div>

@if($canEdit)
<div class="panel no-print">
    <div class="hint">
        ☝️ <b>Ҳолати</b> katakchasini bosing — galochka qo'yiladi (yana bossangiz olinadi).
        <b>🙈</b> — shu mijozga kerak bo'lmagan ishni yashirish, <b>✏️</b> / <b>🗑</b> — qo'shilgan ishni tahrirlash / o'chirish.
        @if($isAdmin)<br><b>⚙️ Umumiy ro'yxat</b> — hamma mijozlar uchun ishlarni tahrirlash.@endif
        Avtomatik saqlanadi.
    </div>
    <button class="btn btn-add" onclick="openItem(-1)">＋ Qo'shimcha ish</button>
    <button class="btn btn-soft" onclick="setAll(true)">Hammasi ✓</button>
    <button class="btn btn-soft" onclick="setAll(false)">Tozalash</button>
</div>
@endif

<div class="sheet">
    <div class="top">
        <div>
            <div class="doc-title">Қилинадиган ишлар рўйхати</div>
            <div class="doc-sub">Маълумотнома &nbsp;·&nbsp; Лойиҳа <b>№{{ $project->seq_no ?: $project->id }}</b> &nbsp;·&nbsp; {{ $project->created_at->format('d.m.Y') }}</div>
        </div>
        <div class="firm">
            <div class="fn">MY PERFECT HOME</div>
            <div class="fp">{!! $ICON_PHONE !!}<span>{!! implode('<br>', array_map('e', $firmPhones)) !!}</span></div>
        </div>
    </div>

    <div class="info">
        <div class="f">
            <div class="ic">{!! $ICON_USER !!}</div>
            <div><div class="l">Мижоз (Ф.И.Ш)</div><div class="v">{{ $project->owner_name }}</div></div>
        </div>
        <div class="f">
            <div class="ic">{!! $ICON_PHONE !!}</div>
            <div><div class="l">Телефон</div><div class="v">{!! $phones ? implode(',<br>', array_map('e', explode(', ', $phones))) : '—' !!}</div></div>
        </div>
        <div class="f">
            <div class="ic">{!! $ICON_PIN !!}</div>
            <div><div class="l">Объект манзили</div><div class="v">{{ $project->address ?: '—' }}</div></div>
        </div>
        @if($project->cadastre_number)
        <div class="f nl">
            <div class="ic">{!! $ICON_DOC !!}</div>
            <div><div class="l">Кадастр рақами</div><div class="v">{{ $project->cadastre_number }}</div></div>
        </div>
        @endif
    </div>

    <div class="works-wrap">
    <table class="works {{ $canEdit ? 'editable' : '' }}">
        <thead>
            <tr>
                <th class="c-num">Т/р</th>
                <th class="c-st">Ҳолати</th>
                <th>Иш турлари</th>
                <th class="c-resp">Масъул</th>
            </tr>
        </thead>
        <tbody id="rows"></tbody>
    </table>
    </div>
    <div class="legend">
        <span>
            <span class="mini on"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6"/></svg></span>— бажарилган
            &nbsp;&nbsp;&nbsp;
            <span class="mini"></span>— бажарилиши керак
        </span>
        <span class="count" id="count"></span>
    </div>

    @if($canEdit)
    <div class="hidden-box no-print" id="hiddenBox" style="display:none"></div>
    @endif

    <div class="note">
        Ҳурматли мижоз! Бўш катакчали (<b>белгиланмаган</b>) ишлар ҳали бажарилиши керак бўлган ишлардир.
    </div>

    <div class="footer-date">Чоп этилган: {{ now()->format('d.m.Y H:i') }}</div>
</div>

@if($canEdit)
{{-- Bitta ishni qo'shish / tahrirlash (shu loyiha uchun) --}}
<div class="ov no-print" id="itemOv">
    <div class="modal">
        <div class="modal-h"><span id="itemTitle">Qo'shimcha ish</span><button class="x" onclick="closeOv('itemOv')">✕</button></div>
        <div class="modal-b">
            <div class="fld">
                <label>Иш тури (nomi) *</label>
                <textarea id="fTitle" rows="2" maxlength="250" placeholder="Масалан: Лойиҳа ҳужжатларини тайёрлаш"></textarea>
            </div>
            <div class="fld">
                <label>Izoh (kursiv bilan, ixtiyoriy)</label>
                <textarea id="fNote" rows="2" maxlength="500" style="font-style:italic" placeholder="(Ягона интерактив давлат хизматлари портали орқали)"></textarea>
            </div>
            <div class="fld">
                <label>Масъул</label>
                <input id="fResp" type="text" maxlength="60">
                <div class="resp-pick">
                    <button type="button" onclick="document.getElementById('fResp').value='Фуқаро'">Фуқаро</button>
                    <button type="button" onclick="document.getElementById('fResp').value='Лойиҳа ташкилоти'">Лойиҳа ташкилоти</button>
                </div>
            </div>
            <div style="font-size:12px;color:#6b7280">Bu ish faqat <b>shu mijozga</b> qo'shiladi.</div>
        </div>
        <div class="modal-f">
            <button class="btn btn-soft" onclick="closeOv('itemOv')">Bekor</button>
            <button class="btn btn-ok" onclick="saveItem()">✓ Saqlash</button>
        </div>
    </div>
</div>
@endif

@if($isAdmin)
{{-- Umumiy ro'yxat (hamma loyihalar uchun) --}}
<div class="ov no-print" id="tplOv">
    <div class="modal wide">
        <div class="modal-h"><span>⚙️ Umumiy ishlar ro'yxati</span><button class="x" onclick="closeOv('tplOv')">✕</button></div>
        <div class="modal-b">
            <div class="warn">⚠️ Bu ro'yxat <b>HAMMA mijozlarga</b> chiqadi. O'chirilgan ish barcha loyihalardan (galochkasi bilan) yo'qoladi.</div>
            <div id="tplRows"></div>
            <button class="btn btn-soft" style="margin-top:10px" onclick="tplAdd()">＋ Ish qo'shish</button>
        </div>
        <div class="modal-f">
            <button class="btn btn-soft" onclick="closeOv('tplOv')">Bekor</button>
            <button class="btn btn-ok" onclick="tplSave()">✓ Saqlash (hamma uchun)</button>
        </div>
    </div>
</div>
@endif

<div id="toast" class="toast no-print">✅ Saqlandi</div>

<script>
    const CAN_EDIT = @json($canEdit);
    const SAVE_URL = @json(route('print.project.ishlar.save', $project));
    const TPL_URL  = @json(route('print.ishlar-template.save'));
    const CSRF     = document.querySelector('meta[name=csrf-token]').content;
    let items    = @json($items);
    let template = @json($template);

    const CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6"/></svg>';

    function esc(s) { const d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

    // Масъул — rangli belgi: Фуқаро (ko'k, hujjat), Лойиҳа ташкилоти (yashil, odam), boshqasi (kulrang)
    const ICON_DOC  = @json($ICON_DOC);
    const ICON_USER = @json($ICON_USER);
    function pill(resp) {
        if (!resp) return '';
        const r = resp.toLowerCase();
        const org = r.indexOf('ташкилот') >= 0 || r.indexOf('tashkilot') >= 0;
        const citizen = r.indexOf('фуқаро') >= 0 || r.indexOf('fuqaro') >= 0;
        const cls = org ? 'org' : (citizen ? '' : 'other');
        return '<span class="pill ' + cls + '">' + (org ? ICON_USER : ICON_DOC) + '<span>' + esc(resp) + '</span></span>';
    }

    function render() {
        const visible = items.map(function (it, i) { return { it: it, i: i }; }).filter(function (v) { return !v.it.hidden; });
        document.getElementById('rows').innerHTML = visible.map(function (v, n) {
            const it = v.it, i = v.i;
            return '<tr class="' + (it.done ? 'done' : '') + '">'
                + '<td class="c-num">' + (n + 1) + '</td>'
                + '<td class="c-st" data-toggle="' + i + '"><span class="cb">' + CHECK + '</span></td>'
                + '<td><div class="w-title">' + esc(it.title) + '</div>'
                +   (it.note ? '<div class="w-note">' + esc(it.note) + '</div>' : '') + '</td>'
                + '<td class="c-resp">' + pill(it.resp)
                + (CAN_EDIT ? '<div class="acts no-print">'
                    + '<button type="button" class="act" title="Yashirish (shu mijozga kerak emas)" data-hide="' + i + '">🙈</button>'
                    + (it.extra ? '<button type="button" class="act" title="Tahrirlash" data-edit="' + i + '">✏️</button>'
                                + '<button type="button" class="act act-del" title="O\'chirish" data-del="' + i + '">🗑</button>' : '')
                    + '</div>' : '')
                + '</td></tr>';
        }).join('') || '<tr class="empty-row"><td colspan="4">Рўйхат бўш</td></tr>';

        const done = visible.filter(function (v) { return v.it.done; }).length;
        document.getElementById('count').textContent = 'Бажарилди: ' + done + ' / ' + visible.length;

        const box = document.getElementById('hiddenBox');
        if (box) {
            const hid = items.map(function (it, i) { return { it: it, i: i }; }).filter(function (v) { return v.it.hidden; });
            box.style.display = hid.length ? '' : 'none';
            box.innerHTML = '🙈 <b>Yashirilgan ishlar (' + hid.length + ')</b> — chop etilmaydi. Kerak bo\'lsa "Qaytarish"ni bosing:'
                + '<div class="chips">' + hid.map(function (v) {
                    return '<span class="chip"><span>' + esc(v.it.title) + '</span>'
                        + '<button type="button" data-show="' + v.i + '">↺ Qaytarish</button>'
                        + (v.it.extra ? '<button type="button" class="chip-del" data-del="' + v.i + '">🗑</button>' : '')
                        + '</span>';
                }).join('') + '</div>';
        }
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        }).then(function (r) { if (!r.ok) throw new Error(r.status); return r; });
    }

    let saveTimer = null;
    function save() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            post(SAVE_URL, {
                done:   items.filter(function (it) { return !it.extra && it.done; }).map(function (it) { return it.key; }),
                extra:  items.filter(function (it) { return it.extra; }).map(function (it) { return { id: it.key, title: it.title, note: it.note, resp: it.resp, done: it.done }; }),
                hidden: items.filter(function (it) { return it.hidden; }).map(function (it) { return it.key; }),
            }).then(function () { toast('✅ Saqlandi', false); })
              .catch(function () { toast('⚠️ Saqlanmadi — sahifani yangilang', true); });
        }, 300);
    }

    function toast(msg, err) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.toggle('err', err);
        t.style.display = 'block';
        clearTimeout(t._h);
        t._h = setTimeout(function () { t.style.display = 'none'; }, 1800);
    }

    function closeOv(id) { document.getElementById(id).classList.remove('open'); }

    // ── Shu loyihaga qo'shimcha ish (qo'shish / tahrirlash) ──
    let editIdx = -1;
    function openItem(i) {
        editIdx = i;
        const it = i >= 0 ? items[i] : { title: '', note: '', resp: '' };
        document.getElementById('itemTitle').textContent = i >= 0 ? 'Ishni tahrirlash' : 'Qo\'shimcha ish';
        document.getElementById('fTitle').value = it.title || '';
        document.getElementById('fNote').value  = it.note || '';
        document.getElementById('fResp').value  = it.resp || '';
        document.getElementById('itemOv').classList.add('open');
        setTimeout(function () { document.getElementById('fTitle').focus(); }, 50);
    }
    function saveItem() {
        const title = document.getElementById('fTitle').value.trim();
        if (!title) { document.getElementById('fTitle').focus(); return; }
        const data = { title: title, note: document.getElementById('fNote').value.trim(), resp: document.getElementById('fResp').value.trim() };
        if (editIdx >= 0) Object.assign(items[editIdx], data);
        else items.push(Object.assign({ key: 'x' + Date.now().toString(36), done: false, extra: true, hidden: false }, data));
        closeOv('itemOv');
        render(); save();
    }

    function setAll(v) {
        items.forEach(function (it) { if (!it.hidden) it.done = v; });
        render(); save();
    }

    function handleAction(e) {
        let b;
        if ((b = e.target.closest('[data-del]'))) {
            const i = +b.dataset.del;
            if (confirm('"' + items[i].title + '" butunlay o\'chirilsinmi?')) { items.splice(i, 1); render(); save(); }
        } else if ((b = e.target.closest('[data-hide]'))) {
            items[+b.dataset.hide].hidden = true; render(); save();
        } else if ((b = e.target.closest('[data-show]'))) {
            items[+b.dataset.show].hidden = false; render(); save();
        } else if ((b = e.target.closest('[data-edit]'))) {
            openItem(+b.dataset.edit);
        } else if ((b = e.target.closest('[data-toggle]'))) {
            const it = items[+b.dataset.toggle]; it.done = !it.done; render(); save();
        }
    }

    if (CAN_EDIT) {
        document.getElementById('rows').addEventListener('click', handleAction);
        document.getElementById('hiddenBox').addEventListener('click', handleAction);
    }

    // ── Umumiy ro'yxat (admin) ──
    let tpl = [];
    function openTemplate() {
        tpl = template.map(function (w) { return Object.assign({}, w); });
        tplRender();
        document.getElementById('tplOv').classList.add('open');
    }
    function tplRender() {
        document.getElementById('tplRows').innerHTML = tpl.map(function (w, i) {
            return '<div class="trow">'
                + '<div class="n">' + (i + 1) + '.</div>'
                + '<div class="col">'
                +   '<textarea rows="2" maxlength="250" placeholder="Иш тури (nomi)" oninput="tpl[' + i + '].title=this.value">' + esc(w.title) + '</textarea>'
                +   '<textarea rows="2" maxlength="500" class="note-in" placeholder="Izoh (kursiv, ixtiyoriy)" oninput="tpl[' + i + '].note=this.value">' + esc(w.note) + '</textarea>'
                + '</div>'
                + '<div class="col resp-col">'
                +   '<input type="text" maxlength="60" placeholder="Масъул" value="' + esc(w.resp).replace(/"/g, '&quot;') + '" oninput="tpl[' + i + '].resp=this.value">'
                +   '<div class="resp-pick" style="margin-top:0">'
                +     '<button type="button" onclick="tplResp(' + i + ',\'Фуқаро\')">Фуқаро</button>'
                +     '<button type="button" onclick="tplResp(' + i + ',\'Лойиҳа ташкилоти\')">Лойиҳа ташк.</button>'
                +   '</div>'
                + '</div>'
                + '<div class="btns">'
                +   '<button type="button" class="act" title="Yuqoriga" onclick="tplMove(' + i + ',-1)">↑</button>'
                +   '<button type="button" class="act" title="Pastga" onclick="tplMove(' + i + ',1)">↓</button>'
                +   '<button type="button" class="act act-del" title="O\'chirish" onclick="tplDel(' + i + ')">🗑</button>'
                + '</div>'
                + '</div>';
        }).join('') || '<div style="color:#6b7280;padding:10px 0">Ro\'yxat bo\'sh</div>';
    }
    function tplResp(i, v) { tpl[i].resp = v; tplRender(); }
    function tplMove(i, d) {
        const j = i + d;
        if (j < 0 || j >= tpl.length) return;
        const t = tpl[i]; tpl[i] = tpl[j]; tpl[j] = t;
        tplRender();
    }
    function tplDel(i) {
        if (!confirm('"' + (tpl[i].title || 'Ish') + '" HAMMA mijozlardan o\'chirilsinmi?')) return;
        tpl.splice(i, 1); tplRender();
    }
    function tplAdd() {
        tpl.push({ key: '', title: '', note: '', resp: '' });
        tplRender();
        const rows = document.querySelectorAll('#tplRows .trow textarea');
        if (rows.length) rows[rows.length - 2].focus();
    }
    function tplSave() {
        const clean = tpl.filter(function (w) { return (w.title || '').trim(); });
        post(TPL_URL, { items: clean })
            .then(function () { toast('✅ Umumiy ro\'yxat saqlandi', false); setTimeout(function () { location.reload(); }, 600); })
            .catch(function () { toast('⚠️ Saqlanmadi', true); });
    }

    render();
</script>
</body>
</html>
