<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Pechat urish — {{ $file->file_name }}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;background:#52555a;color:#e5e7eb}

    .bar{position:sticky;top:0;z-index:50;background:#1f2937;display:flex;gap:10px;align-items:center;padding:10px 16px;flex-wrap:wrap;box-shadow:0 2px 10px rgba(0,0,0,.3)}
    .bar .title{font-size:13px;color:#9ca3af;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .bar button{border:none;border-radius:8px;padding:9px 16px;font-size:13px;font-weight:700;cursor:pointer}
    .bar .spacer{flex:1}
    .btn-stamp{background:#2563eb;color:#fff}
    .btn-save{background:#22c55e;color:#fff}
    .btn-save:disabled{background:#6b7280;cursor:not-allowed}
    .btn-close{background:#374151;color:#fff}
    .btn-sign{background:#0ea5e9;color:#fff}
    .btn-sign2{background:#8b5cf6;color:#fff}
    .sign-ov{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;display:none;align-items:center;justify-content:center;padding:16px}
    .sign-box{background:#fff;border-radius:14px;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.4);width:96vw;max-width:1400px;display:flex;flex-direction:column}
    .sign-hd{font-size:17px;font-weight:700;color:#111;margin-bottom:12px}
    .sign-hd span{font-weight:400;color:#9ca3af;font-size:13px}
    #signCanvas{border:2px dashed #cbd5e1;border-radius:10px;background:#fff;touch-action:none;cursor:crosshair;display:block;width:100%;height:auto}
    .sign-actions{display:flex;gap:10px;align-items:center;margin-top:14px}
    .sign-actions button{border:none;border-radius:8px;padding:13px 22px;font-size:15px;font-weight:700;cursor:pointer}
    .s-clear{background:#f1f5f9;color:#475569}
    .s-cancel{background:#e5e7eb;color:#374151}
    .s-done{background:#16a34a;color:#fff}
    .hint{font-size:12px;color:#fbbf24}

    #pages{padding:20px;display:flex;flex-direction:column;align-items:center;gap:18px}
    .page-wrap{position:relative;background:#fff;box-shadow:0 6px 24px rgba(0,0,0,.4);line-height:0}
    .page-wrap canvas{display:block}
    .stamp-layer{position:absolute;inset:0;overflow:visible}

    .stamp{position:absolute;cursor:move;touch-action:none;user-select:none}
    .stamp img{width:100%;height:100%;display:block;pointer-events:none}
    .stamp .ring{position:absolute;inset:-2px;border:2px dashed #2563eb;border-radius:4px;opacity:0;transition:opacity .12s}
    .stamp:hover .ring,.stamp.sel .ring{opacity:1}
    .stamp .del{position:absolute;top:-12px;right:-12px;width:24px;height:24px;border-radius:50%;background:#ef4444;color:#fff;border:2px solid #fff;font-size:13px;font-weight:800;cursor:pointer;display:none;align-items:center;justify-content:center;line-height:1;z-index:3}
    .stamp:hover .del,.stamp.sel .del{display:flex}
    .stamp .rsz{position:absolute;bottom:-9px;right:-9px;width:18px;height:18px;border-radius:50%;background:#2563eb;border:2px solid #fff;cursor:nwse-resize;display:none;z-index:3}
    .stamp:hover .rsz,.stamp.sel .rsz{display:block}
    .stamp .rot{position:absolute;top:-34px;left:50%;margin-left:-9px;width:18px;height:18px;border-radius:50%;background:#16a34a;border:2px solid #fff;cursor:grab;display:none;z-index:3}
    .stamp .rot::after{content:'';position:absolute;top:16px;left:50%;margin-left:-1px;width:2px;height:18px;background:#16a34a}
    .stamp:hover .rot,.stamp.sel .rot{display:block}

    .loading{text-align:center;padding:60px;color:#cbd5e1;font-size:15px}
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:100}
    .overlay .box{background:#fff;color:#111;border-radius:14px;padding:26px 34px;font-size:15px;font-weight:600;display:flex;align-items:center;gap:12px}
    .spin{width:20px;height:20px;border:3px solid #e5e7eb;border-top-color:#2563eb;border-radius:50%;animation:sp 1s linear infinite}
    @keyframes sp{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<div class="bar">
    <span class="title">📄 {{ $file->file_name }}</span>
    <button class="btn-stamp" onclick="addStamp()">🖋 Pechat</button>
    <button class="btn-sign" onclick="openSignPad()">✍️ Imzo chizish</button>
    <button class="btn-sign2" id="savedSigBtn" onclick="addSavedSignature()" style="display:none">♻️ Saqlangan imzo</button>
    <span class="hint">Suring · kattalashtiring · aylantiring · ✕ o'chirish</span>
    <span class="spacer"></span>
    <button class="btn-save" id="saveBtn" onclick="savePdf()" disabled>💾 Saqlash</button>
    <button class="btn-close" onclick="window.close()">✕ Yopish</button>
</div>

{{-- ✍️ Imzo paneli --}}
<div class="sign-ov" id="signOv">
    <div class="sign-box">
        <div class="sign-hd">✍️ Qo'l qo'ying <span>— sichqoncha yoki barmoq bilan chizing</span></div>
        <canvas id="signCanvas" width="1200" height="560"></canvas>
        <div class="sign-actions">
            <button class="s-clear" onclick="clearSign()">🧹 Tozalash</button>
            <span style="flex:1"></span>
            <button class="s-cancel" onclick="closeSignPad()">Bekor</button>
            <button class="s-done" onclick="finishSign()">✓ Tayyor</button>
        </div>
    </div>
</div>

<div id="pages"><div class="loading">PDF yuklanmoqda...</div></div>

<div class="overlay" id="ov"><div class="box"><span class="spin"></span> <span id="ovText">Saqlanmoqda...</span></div></div>

<script src="{{ route('pechat.asset', 'pdf.js') }}"></script>
<script>
const PDF_URL    = @json($pdfUrl);
const SAVE_URL   = @json($saveUrl);
const STAMP_SRC   = @json(route('pechat.asset', 'stamp.png').'?v=2');
const STAMP_RATIO = 0.706;   // pechat.png nisbati (553/783)
const PDFLIB_URL = @json(route('pechat.asset', 'pdf-lib.js'));
const SIG_SAVE_URL = @json($sigSaveUrl);
const SIG_URL      = @json($sigUrl);
const CSRF       = document.querySelector('meta[name=csrf-token]').content;

pdfjsLib.GlobalWorkerOptions.workerSrc = @json(route('pechat.asset', 'pdf.worker.js'));

// pdf-lib faqat SAQLASHda kerak — sahifa tez ochilishi uchun keyin yuklaymiz
function ensurePdfLib(){
    if(window.PDFLib) return Promise.resolve();
    return new Promise((res, rej)=>{
        const s=document.createElement('script');
        s.src=PDFLIB_URL; s.onload=()=>res(); s.onerror=()=>rej(new Error('pdf-lib yuklanmadi'));
        document.head.appendChild(s);
    });
}

let pdfBytes = null;          // original PDF (ArrayBuffer)
let pageEls  = [];            // {wrap, layer, w, h} per page (CSS px)
const RENDER_SCALE = 1.4;

async function init(){
    const resp = await fetch(PDF_URL, {headers:{'X-CSRF-TOKEN':CSRF}});
    if(!resp.ok){ document.getElementById('pages').innerHTML = '<div class="loading">PDF yuklanmadi ('+resp.status+')</div>'; return; }
    pdfBytes = await resp.arrayBuffer();

    const pdf = await pdfjsLib.getDocument({data: pdfBytes.slice(0)}).promise;
    const cont = document.getElementById('pages');
    cont.innerHTML = '';

    for(let n=1; n<=pdf.numPages; n++){
        const page = await pdf.getPage(n);
        const vp = page.getViewport({scale: RENDER_SCALE});
        const wrap = document.createElement('div');
        wrap.className = 'page-wrap';
        wrap.style.width = vp.width+'px';
        wrap.style.height = vp.height+'px';
        const canvas = document.createElement('canvas');
        canvas.width = vp.width; canvas.height = vp.height;
        wrap.appendChild(canvas);
        const layer = document.createElement('div');
        layer.className = 'stamp-layer';
        wrap.appendChild(layer);
        cont.appendChild(wrap);
        await page.render({canvasContext: canvas.getContext('2d'), viewport: vp}).promise;
        pageEls.push({wrap, layer, w: vp.width, h: vp.height, pageNum: n, viewport: vp});
    }
    document.getElementById('saveBtn').disabled = false;
}

// Hozir ko'rinib turgan (markazdagi) sahifani topamiz
function activePageIndex(){
    const mid = window.scrollY + window.innerHeight/2;
    let best = 0, bestDist = Infinity;
    pageEls.forEach((p,i)=>{
        const r = p.wrap.getBoundingClientRect();
        const c = window.scrollY + r.top + r.height/2;
        const d = Math.abs(c - mid);
        if(d < bestDist){ bestDist = d; best = i; }
    });
    return best;
}

function addStamp(src, ratio){
    if(!pageEls.length) return;
    src   = src || STAMP_SRC;
    ratio = ratio || STAMP_RATIO;
    const pi = activePageIndex();
    const p = pageEls[pi];
    const w = Math.min(238, p.w*0.45);   // 40% kattaroq default
    const h = w * ratio;
    const x = (p.w - w)/2, y = (p.h - h)/2;

    const el = document.createElement('div');
    el.className = 'stamp sel';
    el.style.left = x+'px'; el.style.top = y+'px';
    el.style.width = w+'px'; el.style.height = h+'px';
    el.dataset.rot = '0';
    el.dataset.ratio = ratio;
    el.innerHTML = '<div class="ring"></div><img src="'+src+'" alt=""><div class="del" title="O\'chirish">✕</div><div class="rsz" title="O\'lcham"></div><div class="rot" title="Aylantirish"></div>';
    p.layer.appendChild(el);
    selectStamp(el);

    el.querySelector('.del').addEventListener('mousedown', e=>{ e.stopPropagation(); el.remove(); });
    makeDraggable(el, p);
}

function selectStamp(el){
    document.querySelectorAll('.stamp').forEach(s=>s.classList.remove('sel'));
    el.classList.add('sel');
}

function makeDraggable(el, p){
    const rsz = el.querySelector('.rsz');
    const rot = el.querySelector('.rot');
    let mode=null, sx,sy, ox,oy, ow, cx, cy;

    function applyRot(){ el.style.transform = 'rotate('+(el.dataset.rot||0)+'deg)'; }
    applyRot();

    el.addEventListener('mousedown', e=>{
        if(e.target===rsz || e.target===rot) return;
        mode='move'; sx=e.clientX; sy=e.clientY; ox=parseFloat(el.style.left); oy=parseFloat(el.style.top);
        selectStamp(el); e.preventDefault();
    });
    rsz.addEventListener('mousedown', e=>{
        mode='resize'; sx=e.clientX; ow=parseFloat(el.style.width);
        selectStamp(el); e.stopPropagation(); e.preventDefault();
    });
    rot.addEventListener('mousedown', e=>{
        mode='rotate';
        const r=el.getBoundingClientRect(); cx=r.left+r.width/2; cy=r.top+r.height/2;
        selectStamp(el); e.stopPropagation(); e.preventDefault();
    });
    window.addEventListener('mousemove', e=>{
        if(!mode) return;
        if(mode==='move'){
            let nx=ox+(e.clientX-sx), ny=oy+(e.clientY-sy);
            nx=Math.max(-20,Math.min(p.w-20,nx)); ny=Math.max(-20,Math.min(p.h-20,ny));
            el.style.left=nx+'px'; el.style.top=ny+'px';
        } else if(mode==='resize'){
            let nw=Math.max(40, ow+(e.clientX-sx));
            const rt=parseFloat(el.dataset.ratio)||STAMP_RATIO;
            el.style.width=nw+'px'; el.style.height=(nw*rt)+'px';
        } else { // rotate
            let ang = Math.atan2(e.clientY-cy, e.clientX-cx)*180/Math.PI + 90;
            if(e.shiftKey) ang = Math.round(ang/15)*15;   // Shift bilan 15° qadam
            el.dataset.rot = Math.round(ang);
            applyRot();
        }
    });
    window.addEventListener('mouseup', ()=>{ mode=null; });
}

async function savePdf(){
    const stamps = [];
    pageEls.forEach((p)=>{
        p.layer.querySelectorAll('.stamp').forEach(el=>{
            stamps.push({
                page: p.pageNum,
                cxPx: parseFloat(el.style.left) + parseFloat(el.style.width)/2,   // markaz (kanvas px)
                cyPx: parseFloat(el.style.top)  + parseFloat(el.style.height)/2,
                wPx:  parseFloat(el.style.width),
                hPx:  parseFloat(el.style.height),
                rot:  parseFloat(el.dataset.rot||0),
                vp:   p.viewport,
                src:  el.querySelector('img').src,
            });
        });
    });
    if(!stamps.length){ alert("Avval kamida bitta pechat qo'shing."); return; }

    showOv('Pechat muhrlanmoqda...');
    try{
        await ensurePdfLib();
        const {PDFDocument, degrees} = PDFLib;
        const doc = await PDFDocument.load(pdfBytes.slice(0));
        const pages = doc.getPages();

        // Har xil rasmni (pechat, imzo) bir marta embed qilamiz
        const imgCache = {};
        for(const s of stamps){
            if(!imgCache[s.src]){
                const bytes = await fetch(s.src).then(r=>r.arrayBuffer());
                imgCache[s.src] = await doc.embedPng(bytes);
            }
        }

        stamps.forEach(s=>{
            const pg = pages[s.page-1];
            if(!pg || !s.vp) return;
            const png = imgCache[s.src];
            // Kanvas markazini PDF koordinatasiga o'giramiz — CropBox, rotatsiya, masshtabni
            // to'g'ri hisoblaydi (shu sababli har xil PDFlarda joyi siljimaydi).
            const c = s.vp.convertToPdfPoint(s.cxPx, s.cyPx);
            const mb = pg.getMediaBox();
            const cx = c[0] - mb.x;
            const cy = c[1] - mb.y;
            const w = s.wPx / RENDER_SCALE;
            const h = s.hPx / RENDER_SCALE;
            const pageRot = pg.getRotation().angle;   // 0/90/180/270
            // CSS soat yo'nalishida (-s.rot); sahifa /Rotate'ini qoplaymiz (+pageRot) —
            // shunda rotatsiyali sahifada ham pechat tik (to'g'ri) chiqadi.
            const drawRot = pageRot - s.rot;
            const rad = drawRot * Math.PI/180;
            const dx = w/2, dy = h/2;
            const x = cx - (dx*Math.cos(rad) - dy*Math.sin(rad));
            const y = cy - (dx*Math.sin(rad) + dy*Math.cos(rad));
            pg.drawImage(png, {x, y, width:w, height:h, rotate: degrees(drawRot)});
        });

        const out = await doc.save();
        let bin=''; const bytes=new Uint8Array(out);
        for(let i=0;i<bytes.length;i++) bin+=String.fromCharCode(bytes[i]);
        const b64 = btoa(bin);

        const resp = await fetch(SAVE_URL, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({pdf: b64})
        });
        const txt = await resp.text();
        hideOv();
        let data;
        try { data = JSON.parse(txt); }
        catch(e){ alert('Server xatosi ('+resp.status+'):\n'+txt.slice(0,250)); return; }
        if(data.ok){
            alert("✅ Pechatli PDF loyihaga saqlandi: "+data.name);
            window.close();
        } else {
            alert("Xato: "+(data.message||'saqlanmadi'));
        }
    }catch(err){ hideOv(); alert('Xatolik: '+err.message); }
}

function showOv(t){ document.getElementById('ovText').textContent=t; document.getElementById('ov').style.display='flex'; }
function hideOv(){ document.getElementById('ov').style.display='none'; }

// ── ✍️ IMZO PANELI ──
let signCtx=null, signDrawn=false, signInit=false, savedSig=null;
function openSignPad(){
    const c=document.getElementById('signCanvas');
    // Chizish maydonini planshet/telefon ekraniga moslab kattalashtiramiz
    const w = Math.round(Math.max(320, Math.min(window.innerWidth*0.90, 1600)));
    const h = Math.round(Math.max(220, Math.min(window.innerHeight*0.62, 800)));
    c.width = w; c.height = h;
    signCtx=c.getContext('2d');
    signCtx.lineWidth=3; signCtx.lineCap='round'; signCtx.lineJoin='round'; signCtx.strokeStyle='#0a2a6b';
    clearSign();
    document.getElementById('signOv').style.display='flex';
    if(!signInit){ setupSignDraw(c); signInit=true; }
}
function closeSignPad(){ document.getElementById('signOv').style.display='none'; }
function clearSign(){ if(!signCtx) return; const c=document.getElementById('signCanvas'); signCtx.clearRect(0,0,c.width,c.height); signDrawn=false; }
function setupSignDraw(c){
    let drawing=false, lx=0, ly=0;
    const pos=e=>{ const r=c.getBoundingClientRect(); const sx=c.width/r.width, sy=c.height/r.height;
        const p=e.touches?e.touches[0]:e; return [(p.clientX-r.left)*sx,(p.clientY-r.top)*sy]; };
    const start=e=>{ drawing=true; [lx,ly]=pos(e); e.preventDefault(); };
    const move=e=>{ if(!drawing) return; const [x,y]=pos(e); signCtx.beginPath(); signCtx.moveTo(lx,ly); signCtx.lineTo(x,y); signCtx.stroke(); lx=x; ly=y; signDrawn=true; e.preventDefault(); };
    const end=()=>{ drawing=false; };
    c.addEventListener('mousedown',start); c.addEventListener('mousemove',move); window.addEventListener('mouseup',end);
    c.addEventListener('touchstart',start,{passive:false}); c.addEventListener('touchmove',move,{passive:false}); window.addEventListener('touchend',end);
}
async function finishSign(){
    if(!signDrawn){ alert("Avval qo'l qo'ying"); return; }
    const c=document.getElementById('signCanvas');
    const dataUrl=c.toDataURL('image/png');
    closeSignPad();
    addStamp(dataUrl, c.height/c.width);       // shaffof imzoni PDFga joylaymiz
    savedSig=dataUrl;
    document.getElementById('savedSigBtn').style.display='';
    try{ await fetch(SIG_SAVE_URL, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify({img:dataUrl})}); }catch(e){}
}
function addSavedSignature(){
    const src = savedSig || (SIG_URL ? SIG_URL + '?t=' + Date.now() : '');
    if(!src) return;
    const img=new Image();
    img.onload=()=>{ addStamp(img.src, img.height/img.width); };
    img.onerror=()=>{ alert('Saqlangan imzo topilmadi'); };
    img.src=src;
}
if(SIG_URL){ document.getElementById('savedSigBtn').style.display=''; }

init();
</script>
</body>
</html>
