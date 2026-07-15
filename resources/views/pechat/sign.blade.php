<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Imzo chekish — {{ $project->owner_name ?? ('Loyiha #'.$project->id) }}</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,'Segoe UI',Roboto,sans-serif;background:#52555a;color:#e5e7eb;min-height:100vh;display:flex;flex-direction:column}

    .bar{position:sticky;top:0;z-index:50;background:#1f2937;display:flex;gap:10px;align-items:center;padding:12px 16px;flex-wrap:wrap;box-shadow:0 2px 10px rgba(0,0,0,.3)}
    .bar .title{font-size:14px;color:#e5e7eb;font-weight:700;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .bar .sub{font-size:12px;color:#9ca3af}
    .bar .spacer{flex:1}
    .bar button, .bar a{border:none;border-radius:8px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
    .btn-close{background:#374151;color:#fff}

    .wrap{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;gap:18px}

    .saved-box{background:#fff;border-radius:14px;padding:22px;box-shadow:0 20px 60px rgba(0,0,0,.4);width:96vw;max-width:640px;text-align:center}
    .saved-box img{max-width:100%;max-height:260px;background:repeating-conic-gradient(#f3f4f6 0% 25%, #fff 0% 50%) 50% / 20px 20px;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:16px}
    .saved-box .lbl{font-size:15px;font-weight:700;color:#111;margin-bottom:14px}
    .saved-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
    .saved-actions button{border:none;border-radius:8px;padding:12px 20px;font-size:14px;font-weight:700;cursor:pointer}
    .s-redraw{background:#0ea5e9;color:#fff}
    .s-del{background:#fef2f2;color:#dc2626;border:1px solid #fecaca!important}

    .sign-box{background:#fff;border-radius:14px;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.4);width:96vw;max-width:1400px;display:flex;flex-direction:column}
    .sign-hd{font-size:17px;font-weight:700;color:#111;margin-bottom:12px}
    .sign-hd span{font-weight:400;color:#9ca3af;font-size:13px}
    #signCanvas{border:2px dashed #cbd5e1;border-radius:10px;background:#fff;touch-action:none;cursor:crosshair;display:block;width:100%;height:auto}
    .sign-actions{display:flex;gap:10px;align-items:center;margin-top:14px;flex-wrap:wrap}
    .sign-actions button{border:none;border-radius:8px;padding:13px 22px;font-size:15px;font-weight:700;cursor:pointer}
    .s-clear{background:#f1f5f9;color:#475569}
    .s-cancel{background:#e5e7eb;color:#374151}
    .s-done{background:#16a34a;color:#fff}

    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:100}
    .overlay .box{background:#fff;color:#111;border-radius:14px;padding:26px 34px;font-size:15px;font-weight:600;display:flex;align-items:center;gap:12px}
    .spin{width:20px;height:20px;border:3px solid #e5e7eb;border-top-color:#2563eb;border-radius:50%;animation:sp 1s linear infinite}
    @keyframes sp{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<div class="bar">
    <span class="title">✍️ Imzo chekish</span>
    <span class="sub">{{ $project->owner_name ?? ('Loyiha #'.$project->id) }}</span>
    <span class="spacer"></span>
    <button class="btn-close" onclick="window.close()">✕ Yopish</button>
</div>

<div class="wrap" id="wrap"></div>

<div class="overlay" id="ov"><div class="box"><span class="spin"></span> <span id="ovText">Saqlanmoqda...</span></div></div>

<script>
const SIG_SAVE_URL = @json($sigSaveUrl);
const SIG_DEL_URL  = @json($sigDelUrl);
let SIG_URL         = @json($sigUrl);
const CSRF = document.querySelector('meta[name=csrf-token]').content;

const wrapEl = document.getElementById('wrap');

function showOv(t){ document.getElementById('ovText').textContent=t; document.getElementById('ov').style.display='flex'; }
function hideOv(){ document.getElementById('ov').style.display='none'; }

function renderSaved(){
    wrapEl.innerHTML = `
        <div class="saved-box">
            <div class="lbl">Saqlangan imzo</div>
            <img src="${SIG_URL}&t=${Date.now()}" alt="imzo">
            <div class="saved-actions">
                <button class="s-redraw" onclick="renderPad()">✍️ Qayta chizish</button>
                <button class="s-del" onclick="deleteSig()">🗑 O'chirish</button>
            </div>
        </div>`;
}

function renderPad(){
    wrapEl.innerHTML = `
        <div class="sign-box">
            <div class="sign-hd">✍️ Qo'l qo'ying <span>— sichqoncha yoki barmoq bilan chizing</span></div>
            <canvas id="signCanvas" width="1200" height="560"></canvas>
            <div class="sign-actions">
                <button class="s-clear" onclick="clearSign()">🧹 Tozalash</button>
                <span style="flex:1"></span>
                ${SIG_URL ? '<button class="s-cancel" onclick="renderSaved()">Bekor</button>' : ''}
                <button class="s-done" onclick="finishSign()">✓ Saqlash</button>
            </div>
        </div>`;

    const c = document.getElementById('signCanvas');
    const w = Math.round(Math.max(320, Math.min(window.innerWidth*0.90, 1600)));
    const h = Math.round(Math.max(220, Math.min(window.innerHeight*0.62, 800)));
    c.width = w; c.height = h;
    signCtx = c.getContext('2d');
    signCtx.lineWidth=3; signCtx.lineCap='round'; signCtx.lineJoin='round'; signCtx.strokeStyle='#0a2a6b';
    signDrawn = false;
    setupSignDraw(c);
}

let signCtx=null, signDrawn=false;
function clearSign(){
    if(!signCtx) return;
    const c=document.getElementById('signCanvas');
    signCtx.clearRect(0,0,c.width,c.height);
    signDrawn=false;
}
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
    showOv('Saqlanmoqda...');
    try{
        const resp = await fetch(SIG_SAVE_URL, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify({img:dataUrl})});
        const data = await resp.json();
        hideOv();
        if(!data.ok){ alert('Xato: '+(data.message||'saqlanmadi')); return; }
        SIG_URL = @json(route('signature.view', $project));
        renderSaved();
    }catch(e){ hideOv(); alert('Xatolik: '+e.message); }
}
async function deleteSig(){
    if(!confirm("Imzoni o'chirasizmi?")) return;
    showOv("O'chirilmoqda...");
    try{
        await fetch(SIG_DEL_URL, {method:'DELETE', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}});
    }catch(e){}
    hideOv();
    SIG_URL = null;
    renderPad();
}

if(SIG_URL){ renderSaved(); } else { renderPad(); }
</script>
</body>
</html>
