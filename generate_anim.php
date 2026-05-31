<?php
$svgRaw = file_get_contents(__DIR__ . '/11.svg');

// XML va sodipodi metadata olib tashlash
$svgRaw = preg_replace('/<\?xml[^>]+\?>\s*/i', '', $svgRaw);
$svgRaw = preg_replace('/<sodipodi:namedview[\s\S]*?<\/sodipodi:namedview>\s*/i', '', $svgRaw);

// SVG ga id qo'shish (newline yoki bo'shliq bo'lsa ham ishlaydi)
$svgRaw = preg_replace('/<svg(\s)/i', '<svg id="bh-svg"$1', $svgRaw, 1);

$js = <<<'JSEND'
var TOTAL = 15;
var DUR   = 0.35;
var COLORS = ['#4af0c8','#00d4ff','#7b9fff','#55ccff'];
var paths = [];
var runId = 0;

function init() {
  var svg = document.getElementById('bh-svg') || document.querySelector('svg');
  if (!svg) { document.getElementById('info').textContent = 'SVG topilmadi!'; return; }
  svg.id = 'bh-svg';

  var all = svg.querySelectorAll('path');
  paths = [];
  all.forEach(function(p) {
    var s = p.getAttribute('style') || '';
    var m = s.match(/fill:\s*([^;]+)/);
    var f = m ? m[1].trim() : '';
    if (f === '#ffffff' || f === 'white' || f === 'rgb(255,255,255)') return;
    paths.push(p);
  });

  // Barcha pathlarni tayyorlaymiz
  paths.forEach(function(p, i) {
    p.removeAttribute('style');
    p.setAttribute('fill', 'none');
    p.setAttribute('stroke', COLORS[i % COLORS.length]);
    p.setAttribute('stroke-width', '0.7');
    p.setAttribute('opacity', '0');
    var len;
    try { len = Math.ceil(p.getTotalLength()); } catch(e) { len = 400; }
    len = Math.max(len, 1);
    p.setAttribute('stroke-dasharray', len);
    p.setAttribute('stroke-dashoffset', len);
    p._len = len;
  });

  document.getElementById('info').textContent =
    paths.length + ' ta chiziq topildi';
  setTimeout(rerun, 600);
}

function rerun() {
  runId++;
  var myId = runId;
  var N = paths.length;
  document.getElementById('btn').style.display = 'none';

  // Reset
  paths.forEach(function(p) {
    p.style.cssText = '';
    p.setAttribute('opacity', '0');
    p.setAttribute('stroke-dashoffset', p._len);
  });

  // Reflow majburlash
  document.querySelector('#bh-svg') && document.querySelector('#bh-svg').getBoundingClientRect();

  // Animatsiya
  paths.forEach(function(p, i) {
    var delay = (i / N) * TOTAL * 0.78;
    var dur   = TOTAL * DUR;
    p.style.animation = 'bh-draw ' + dur + 's ease-out ' + delay + 's both';
  });

  var start = Date.now();
  var endMs = (TOTAL * 1.8 + 2) * 1000;
  var iv = setInterval(function() {
    if (runId !== myId) { clearInterval(iv); return; }
    var pct = Math.min(100, Math.round((Date.now() - start) / endMs * 100));
    document.getElementById('info').textContent =
      pct < 100 ? 'CHIZILMOQDA... ' + pct + '%' : 'TAYYOR ✓';
    if (pct >= 100) {
      clearInterval(iv);
      document.getElementById('btn').style.display = 'inline-block';
    }
  }, 250);
}

window.onload = init;
JSEND;

$html = '<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BestHome — Animated Building</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0d1b2e;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh}
#wrap{width:min(96vw,900px)}
#wrap svg{width:100%;height:auto;display:block}
#info{color:#4af0c8;font-size:13px;font-weight:700;letter-spacing:2px;margin-bottom:14px;text-align:center;min-height:22px}
#btn{margin-top:18px;background:#4af0c8;color:#0d1b2e;border:none;border-radius:10px;padding:10px 28px;font-size:13px;font-weight:800;cursor:pointer;display:none}
#btn:hover{opacity:.85}
@keyframes bh-draw {
  to { stroke-dashoffset: 0; opacity: 1; }
}
</style>
</head>
<body>
<div id="info">YUKLANMOQDA...</div>
<div id="wrap">' . $svgRaw . '</div>
<button id="btn" onclick="rerun()">&#8635; Qayta ijro</button>
<script>' . $js . '</script>
</body>
</html>';

$out = __DIR__ . '/public/house-anim.html';
file_put_contents($out, $html);

// D:\2 ga ham nusxa
$out2 = 'D:/2/index.html';
file_put_contents($out2, $html);

echo 'OK: ' . number_format(strlen($html)) . " bytes\n";
echo "public/house-anim.html va D:/2/index.html yaratildi\n";
