<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>GENPLAN yig'ish — {{ $project->number }}</title>
@php
    $manzil = trim($project->oblozhka_address ?: $project->address ?: '');
    $tuman  = 'Toshkent viloyati Quyichirchiq tumani';
    $shahar = 'Toshkent';
    $yil    = now()->year;
@endphp
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;background:#0f172a;color:#e5e7eb;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{background:#1e293b;border-radius:18px;max-width:480px;width:100%;padding:28px;box-shadow:0 25px 70px rgba(0,0,0,.5)}
    h1{font-size:18px;margin-bottom:4px}
    .sub{font-size:13px;color:#94a3b8;margin-bottom:18px}
    .list{background:#0f172a;border-radius:10px;padding:12px 14px;font-size:13px;line-height:1.9;margin-bottom:18px}
    .list b{color:#fbbf24}
    .status{display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;margin-bottom:16px}
    .spin{width:20px;height:20px;border:3px solid #334155;border-top-color:#22c55e;border-radius:50%;animation:sp 1s linear infinite;flex-shrink:0}
    @keyframes sp{to{transform:rotate(360deg)}}
    .ok{color:#4ade80}.err{color:#f87171}
    button{border:none;border-radius:9px;padding:11px 20px;font-size:14px;font-weight:700;cursor:pointer;width:100%}
    .btn-close{background:#334155;color:#fff;margin-top:8px}
    .btn-retry{background:#2563eb;color:#fff}
</style>
</head>
<body>
<div class="box">
    <h1>🔗 GENPLAN yig'ish</h1>
    <div class="sub">{{ $project->owner_name }} — {{ $project->number }}</div>

    <div class="list">
        @if($withCover)
        <div>1. 📄 Muqova (abloshka)</div>
        <div>2. 📄 My perfect home (sertifikat)</div>
        @endif
        @foreach($files as $i => $f)
        <div>{{ $withCover ? $i + 3 : $i + 1 }}. 📎 {{ $f->file_name }}</div>
        @endforeach
        @if($files->isEmpty())
        <div style="color:#f87171">⚠️ Hech qanday PDF belgilanmagan</div>
        @endif
    </div>

    <div class="status" id="status"><span class="spin"></span> <span id="stxt">Tayyorlanmoqda...</span></div>

    <button class="btn-close" onclick="window.close()">Yopish</button>
</div>

<script src="{{ route('pechat.asset', 'pdf-lib.js') }}"></script>
<script>
const CSRF     = document.querySelector('meta[name=csrf-token]').content;
const SAVE_URL = @json($saveUrl);
const CERT_URL = @json(route('pechat.asset', 'certificate.pdf'));
const OBL_URL  = @json(route('pechat.asset', 'obloshka1.png'));
const SEL_URLS = @json($files->map(fn($f) => route('pechat.pdf', $f->id))->values());
const WITH_COVER = @json($withCover);
// Muqova matni
const MANZIL = @json($manzil);
const TUMAN  = @json($tuman);
const SHAHAR = @json($shahar);
const YIL    = @json((string)$yil);

const mm = v => v * 2.834645669;   // mm -> pt
function setStatus(txt, cls){ document.getElementById('stxt').textContent = txt; document.getElementById('stxt').className = cls||''; }
function done(ok){ document.querySelector('.spin').style.display = ok ? 'none' : 'none'; }

function clean(s){ return (s||'').replace(/[‘’ʻʼ′]/g, "'").replace(/[“”]/g,'"'); }

// Matnni belgilangan enga sig'dirib qatorlarga bo'lish
function wrapLines(text, font, size, maxW){
    const words = clean(text).split(/\s+/);
    const lines = []; let line = '';
    for(const w of words){
        const t = line ? line+' '+w : w;
        if(font.widthOfTextAtSize(t, size) > maxW && line){ lines.push(line); line = w; }
        else line = t;
    }
    if(line) lines.push(line);
    return lines;
}

async function run(){
    try{
        if(!SEL_URLS.length){ setStatus("Avval GENPLAN'da PDF belgilang", 'err'); done(false); return; }
        const {PDFDocument, StandardFonts, rgb} = PDFLib;
        const merged = await PDFDocument.create();
        const font   = await merged.embedFont(StandardFonts.TimesRoman);

        if(WITH_COVER){
            // ── 1) MUQOVA (A3 landscape) ──
            setStatus('Muqova tayyorlanmoqda...');
            const A3W = mm(420), A3H = mm(297);
            const page = merged.addPage([A3W, A3H]);
            const bgBytes = await fetch(OBL_URL).then(r=>r.arrayBuffer());
            const bg = await merged.embedPng(bgBytes);
            page.drawImage(bg, {x:0, y:0, width:A3W, height:A3H});

            const leftX = mm(420*0.06);
            const blockTop = A3H - mm(297*0.493);   // manzil bloki tepasi
            // tuman
            page.drawText(clean(TUMAN), {x:leftX, y: blockTop - mm(5.5), size: mm(5), font, color: rgb(0.1,0.1,0.1)});
            // manzil (o'ralgan)
            const maxW = mm(420*0.44);
            const lines = wrapLines(MANZIL, font, mm(7.9), maxW);
            let ly = blockTop - mm(14);
            for(const ln of lines){ page.drawText(ln, {x:leftX, y:ly, size: mm(7.9), font, color: rgb(0.07,0.07,0.07)}); ly -= mm(9); }
            // yil (markaz, oq)
            const yt = SHAHAR + ' - ' + YIL;
            const yw = font.widthOfTextAtSize(yt, mm(9.8));
            page.drawText(yt, {x:(A3W-yw)/2, y: A3H - mm(297*0.945), size: mm(9.8), font, color: rgb(1,1,1)});

            // ── 2) SERTIFIKAT ──
            setStatus('Sertifikat qo\'shilmoqda...');
            const certBytes = await fetch(CERT_URL).then(r=>r.arrayBuffer());
            const cert = await PDFDocument.load(certBytes, {ignoreEncryption:true});
            (await merged.copyPages(cert, cert.getPageIndices())).forEach(p=>merged.addPage(p));
        }

        // ── 3) TANLANGAN PDFlar ──
        let n = 0;
        for(const url of SEL_URLS){
            n++; setStatus('PDF qo\'shilmoqda ('+n+'/'+SEL_URLS.length+')...');
            const b = await fetch(url, {headers:{'X-CSRF-TOKEN':CSRF}}).then(r=>r.arrayBuffer());
            const d = await PDFDocument.load(b, {ignoreEncryption:true});
            (await merged.copyPages(d, d.getPageIndices())).forEach(p=>merged.addPage(p));
        }

        // ── Saqlash ──
        setStatus('Saqlanmoqda...');
        const out = await merged.save();
        let bin=''; const arr=new Uint8Array(out);
        for(let i=0;i<arr.length;i++) bin+=String.fromCharCode(arr[i]);
        const resp = await fetch(SAVE_URL, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({pdf: btoa(bin)})
        });
        const txt = await resp.text();
        let data; try{ data = JSON.parse(txt); }catch(e){ throw new Error('Server ('+resp.status+'): '+txt.slice(0,150)); }
        if(!data.ok) throw new Error(data.message||'saqlanmadi');

        done(true);
        setStatus('✅ Yig\'ildi va GENPLAN\'ga saqlandi: '+data.name, 'ok');
        setTimeout(()=>{ try{ window.opener && window.opener.location.reload(); }catch(e){} }, 800);
    }catch(err){
        done(false);
        setStatus('❌ Xato: '+err.message, 'err');
    }
}
run();
</script>
</body>
</html>
