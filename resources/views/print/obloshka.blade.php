@php
    // Obloshka manzili — alohida kiritilgan bo'lsa o'sha, bo'lmasa oddiy manzil
    $manzil = trim($project->oblozhka_address ?: $project->address ?: '');
    $yil    = now()->year;            // avtomatik joriy yil
    $shahar = 'Toshkent';             // yil yonidagi shahar nomi
    $tuman  = 'Toshkent viloyati Quyichirchiq tumani';  // har bir loyiha manzili tepasidagi doimiy qator

    // Qavat turi: 1 yoki 2 — faqat fon shabloni o'zgaradi
    $qavat   = (int) request('qavat') === 2 ? 2 : 1;
    $bgImage = $qavat === 2 ? 'images/obloshka-2qavat.png' : 'images/obloshka-1qavat.png';

    // 2-qavat shablonida "TOSHKENT - 2026" rasmga yozilgan → bizniki kerak emas
    $showYil   = $qavat === 1;
    // Sarlavha 2-qavatda 2 qatorli — manzilni pastroqdan boshlaymiz
    $manzilTop = $qavat === 2 ? '51%' : '49.3%';
@endphp
<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Obloshka — {{ $project->number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* ── A3 landscape ── */
    @page { size: A3 landscape; margin: 0; }

    html, body { background: #525659; }

    .sheet {
        position: relative;
        width: 420mm;
        height: 297mm;
        margin: 0 auto;
        overflow: hidden;
        font-family: 'Times New Roman', Times, serif;
        color: #000;
    }
    /* Fon rasmi — <img> bo'lgani uchun har doim chop etiladi (background-image emas) */
    .sheet-bg {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: fill;
        z-index: 0;
    }
    .manzil, .yil { z-index: 2; }

    /* ── MANZIL (chapda, sarlavha ostida) ──
       Joyni sozlash: top / left / width qiymatlarini o'zgartiring */
    .manzil {
        position: absolute;
        top: 49.3%;
        left: 2.2%;
        width: 46%;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 7.9mm;        /* -20% */
        line-height: 1.12;       /* qatorlar yaqin */
        color: #111;
        font-weight: 500;
    }
    .manzil-text {
        outline: none;
        border-radius: 4px;
        transition: background .15s;
    }
    /* Tahrirlanadigan matn — faqat ekranda ko'rinadigan belgi (chop etishda yo'q) */
    @media screen {
        .manzil-text:hover { background: rgba(22,163,74,0.10); }
        .manzil-text:focus { background: rgba(22,163,74,0.16); box-shadow: 0 0 0 2px rgba(22,163,74,0.5); }
    }

    /* ── YIL: "Toshkent - 2026" (pastki panel, markazroq) ── */
    .yil {
        position: absolute;
        top: 92.5%;              /* to'q panel markazi */
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 9.8mm;        /* +40% */
        font-weight: 600;
        color: #ffffff;          /* oq rang */
        letter-spacing: 0.04em;
        text-shadow: 0 1px 3px rgba(0,0,0,0.45);
    }

    /* ── Boshqaruv paneli (chop etishda ko'rinmaydi) ── */
    .toolbar {
        position: fixed; top: 0; left: 0; right: 0;
        background: #1e293b; color: #fff;
        padding: 10px 20px; display: flex; gap: 12px; align-items: center;
        z-index: 9999; font-family: Arial, sans-serif;
    }
    .toolbar button {
        background: #16a34a; color: #fff; border: none;
        padding: 9px 18px; border-radius: 8px; font-size: 14px;
        font-weight: 700; cursor: pointer;
    }
    .toolbar .hint { font-size: 12px; opacity: 0.8; }
    body { padding-top: 0; }
    .toolbar + .sheet { margin-top: 56px; }

    @media print {
        .toolbar { display: none !important; }
        .toolbar + .sheet { margin-top: 0; }
        html, body { background: #fff; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button id="saveBtn" style="background:#2563eb" onclick="saveManzil()">💾 Manzilni saqlash</button>
    <button onclick="window.print()">📄 PDF saqlash / Chop etish</button>
    <span class="hint">Manzil matni ustiga bosib tahrirlang → «Manzilni saqlash». Chop etishda: A3, yotiq, chekkalar yo'q.</span>
</div>

<div class="sheet">
    <img class="sheet-bg" src="{{ asset($bgImage) }}" alt="">
    <div class="manzil" style="top:{{ $manzilTop }}">
        <div class="manzil-tuman">{{ $tuman }}</div>
        <div class="manzil-text" id="manzilEdit" contenteditable="true" spellcheck="false">{{ $manzil }}</div>
    </div>
    @if($showYil)
    <div class="yil">{{ $shahar }} - {{ $yil }}</div>
    @endif
</div>

<script>
function saveManzil() {
    var btn  = document.getElementById('saveBtn');
    var text = document.getElementById('manzilEdit').innerText.trim();
    var old  = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = 'Saqlanmoqda...';
    fetch('{{ route('print.project.obloshka.save', $project) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ manzil: text })
    })
    .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
    .then(function () { btn.innerHTML = '✓ Saqlandi'; setTimeout(function(){ btn.innerHTML = old; btn.disabled = false; }, 1500); })
    .catch(function () { btn.innerHTML = '✗ Xatolik'; setTimeout(function(){ btn.innerHTML = old; btn.disabled = false; }, 2000); });
}
</script>
</body>
</html>
