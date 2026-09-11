<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>"MY PERFECT HOME" — Arxitektura va loyihalash</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior: smooth; }
body { font-family:'IBM Plex Sans', Arial, sans-serif; color:#e5e7eb; background:#0e0c0a; }
a { color: inherit; text-decoration:none; }
.container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ── Header ── */
.site-header {
    position: sticky; top: 0; z-index: 30;
    background: rgba(10,15,26,.92); backdrop-filter: blur(6px);
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.site-header .bar { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 20px; }
.brand { font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:15px; color:#fff; letter-spacing:.2px; }
.main-nav { display:none; align-items:center; gap:40px; }
.main-nav a { font-size:13px; font-weight:600; letter-spacing:1.5px; text-transform:uppercase; color:#e5e7eb; }
.main-nav a:hover { color:#facc15; }
.header-right { display:flex; align-items:center; gap:18px; }
.login-btn {
    display:flex; align-items:center; gap:7px; color:#fff; font-size:13px; font-weight:700;
    letter-spacing:.5px; text-transform:uppercase; border:1px solid rgba(255,255,255,.3);
    padding:9px 16px; border-radius:8px; white-space:nowrap;
}
.login-btn:hover { border-color:#facc15; color:#facc15; }
@media(min-width:900px){ .main-nav{ display:flex; } }

/* ── Hero ── */
.hero {
    position:relative; overflow:hidden; min-height:640px;
    display:flex; flex-direction:column; color:#fff;
    background:#0e0c0a;
}
.hero-bg { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:78% center; z-index:0; }
.hero-overlay { position:absolute; inset:0; background:linear-gradient(180deg, rgba(8,8,8,.45), rgba(8,8,8,.68)); z-index:1; }
.hero-frame { position:absolute; z-index:2; top:0; left:24px; right:24px; bottom:0; border-left:1px dashed rgba(250,204,21,.28); border-right:1px dashed rgba(250,204,21,.28); pointer-events:none; display:none; }
.hero-inner { position:relative; z-index:2; flex:1; display:flex; flex-direction:column; padding:28px 20px 32px; gap:18px; }

.hero-headline { display:flex; flex-direction:column; gap:10px; flex:1; justify-content:center; max-width:420px; }
.hero-eyebrow { font-size:11px; font-weight:700; letter-spacing:1.4px; text-transform:uppercase; color:#facc15; }
.hero-headline h1 { font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:28px; line-height:1.25; letter-spacing:-.3px; }
.hero-headline p { font-size:13.5px; line-height:1.6; color:#d8dde3; }

.hero-tagline { display:flex; flex-direction:column; gap:4px; }
.hero-tagline .studio { font-family:'Space Grotesk',sans-serif; font-weight:800; font-size:20px; color:#facc15; }
.hero-tagline .tag { font-size:11.5px; letter-spacing:.8px; text-transform:uppercase; color:#d8dde3; }

/* Ariza formasi — animatsion gradient chegara (Uiverse.io by omriluz uslubidan moslashtirilgan) */
.form-container {
    width:100%; max-width:340px;
    background: linear-gradient(#1c1c1c, #1c1c1c) padding-box,
                linear-gradient(145deg, transparent 35%, #e81cff, #40c9ff) border-box;
    border:2px solid transparent; border-radius:14px;
    padding:20px 18px; display:flex; flex-direction:column; gap:13px;
}
.form-container .form-title { font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:15px; }
.form-container .form { display:flex; flex-direction:column; gap:13px; }
.form-container .form-group { display:flex; flex-direction:column; gap:5px; }
.form-container label { color:#9a9a9a; font-weight:600; font-size:11px; letter-spacing:.3px; text-transform:uppercase; }
.form-container input, .form-container textarea {
    width:100%; padding:12px 13px; border-radius:8px; color:#fff; font-family:inherit;
    font-size:15px; background:transparent; border:1px solid #414141;
}
.form-container input::placeholder, .form-container textarea::placeholder { color:#6b6b6b; opacity:1; }
.form-container textarea { resize:none; height:64px; }
.form-container input:focus, .form-container textarea:focus { outline:none; border-color:#e81cff; }
.form-submit-btn {
    display:flex; align-items:center; justify-content:center; font-family:inherit;
    color:#d0d0d0; font-weight:700; font-size:13px; letter-spacing:.5px; text-transform:uppercase;
    background:#2b2b2b; border:1px solid #414141; padding:13px 0; border-radius:8px; cursor:pointer;
}
.form-submit-btn:hover { background:#fff; border-color:#fff; color:#111; }
.form-error { color:#f87171; font-size:11.5px; font-weight:600; margin-top:-6px; }
.form-success {
    background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.4); color:#86efac;
    font-size:12.5px; font-weight:700; padding:10px 14px; border-radius:8px;
}

/* Sichqoncha bilan harakatlanuvchi CAD-krest — faqat haqiqiy sichqonchali (desktop) qurilmada */
.cad-cursor { display:none; }
@media (hover:hover) and (pointer:fine) {
    body.cad-active { cursor:none; }
    body.cad-active * { cursor:none !important; }
    .cad-cursor { display:block; position:fixed; z-index:50; pointer-events:none; }
    .cad-h { left:0; right:0; height:0; border-top:1px solid rgba(250,204,21,.55); }
    .cad-v { top:0; bottom:0; width:0; border-left:1px solid rgba(250,204,21,.55); }
    .cad-square { width:14px; height:14px; border:1.5px solid #facc15; }
    .cad-coord { font-family:'IBM Plex Mono',monospace; font-size:13px; letter-spacing:.5px; color:#e0c88a; }
}

/* ── Desktop hero tartibi ── */
@media(min-width:900px){
    .hero { min-height:760px; }
    .hero-frame { display:block; }
    .hero-inner { flex-direction:row; padding:44px 60px; align-items:flex-start; }
    .hero-headline { display:none; }
    .hero-form-col { display:flex; flex-direction:column; }
    .form-container { max-width:300px; }
    .hero-tagline { position:absolute; right:60px; bottom:56px; text-align:right; align-items:flex-end; z-index:2; }
    .hero-tagline .tag { max-width:280px; }
}

/* ── Xizmatlar ── */
.section { padding: 56px 0; background:#0e0c0a; border-top:1px solid rgba(255,255,255,.06); }
.section-title { font-size:24px; font-weight:900; color:#fff; text-align:center; font-family:'Space Grotesk',sans-serif; }
.section-sub { text-align:center; color:#9ca3af; font-size:14px; margin-top:8px; max-width:520px; margin-left:auto; margin-right:auto; }
.services-grid { margin-top:32px; display:grid; grid-template-columns:1fr; gap:18px; }
@media(min-width:800px){ .services-grid{ grid-template-columns:repeat(3,1fr); } }
.service-card {
    background:#1a1a1a; border:1px solid #2b2b2b; border-radius:16px; padding:24px 22px;
}
.service-icon {
    width:44px; height:44px; border-radius:12px; background:rgba(250,204,21,.12); color:#facc15;
    display:flex; align-items:center; justify-content:center; margin-bottom:14px;
}
.service-card h3 { font-size:16px; font-weight:800; color:#fff; margin-bottom:6px; font-family:'Space Grotesk',sans-serif; }
.service-card p { font-size:13.5px; color:#9ca3af; line-height:1.55; }

/* ── Footer ── */
.site-footer { background:#080d18; color:#94a3b8; padding:24px 0; text-align:center; font-size:12px; }
.site-footer strong { color:#e2e8f0; }
</style>
</head>
<body>

<header class="site-header">
    <div class="bar">
        <span class="brand">MY PERFECT HOME</span>
        <nav class="main-nav">
            <a href="#xizmatlar">Xizmatlar</a>
            <a href="#aloqa">Bog'lanish</a>
        </nav>
        <div class="header-right">
            <a class="login-btn" href="/admin">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Kirish
            </a>
        </div>
    </div>
</header>

<section class="hero" id="aloqa">
    <img class="hero-bg" src="{{ asset('images/hero-bg.jpg') }}" alt="">
    <div class="hero-overlay"></div>
    <div class="hero-frame"></div>

    <div class="hero-inner">

        <div class="hero-headline">
            <span class="hero-eyebrow">Arxitektura studiyasi</span>
            <h1>Orzuyingizdagi uyni ishonchli loyihaga aylantiramiz</h1>
            <p>Turar-joy va tijorat obyektlari uchun to'liq loyihalash xizmati.</p>
        </div>

        <div class="hero-form-col">
            <div class="form-container">
                <p class="form-title">Bog'lanish uchun ariza</p>

                @if(session('contact_sent'))
                <div class="form-success">✓ Arizangiz qabul qilindi. Tez orada bog'lanamiz!</div>
                @endif

                <form class="form" method="POST" action="{{ route('contact.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="full_name">F.I.Sh</label>
                        <input type="text" id="full_name" name="full_name" placeholder="Familiya Ism Sharif" value="{{ old('full_name') }}" required>
                        @error('full_name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone">Nomer</label>
                        <input type="tel" id="phone" name="phone" placeholder="+998 __ ___ __ __" value="{{ old('phone') }}" required>
                        @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="message">Izoh</label>
                        <textarea id="message" name="message" placeholder="Loyihangiz haqida qisqacha...">{{ old('message') }}</textarea>
                        @error('message') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="form-submit-btn">Yuborish</button>
                </form>
            </div>
        </div>

        <div class="hero-tagline">
            <span class="studio">studio</span>
            <span class="tag">Arxitektura — odamlar yashashi va ishlashi uchun</span>
        </div>

    </div>

    <div class="cad-cursor cad-h" id="cadH"></div>
    <div class="cad-cursor cad-v" id="cadV"></div>
    <div class="cad-cursor cad-square" id="cadSquare"></div>
    <div class="cad-cursor cad-coord" id="cadCoord"><span id="coordText"></span></div>
</section>

<section class="section" id="xizmatlar">
    <div class="container">
        <h2 class="section-title">Xizmatlarimiz</h2>
        <p class="section-sub">Loyihaning har bir bosqichida yoningizdamiz — eskizdan tayyor uygacha.</p>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V9l7-6 7 6v12M9 21v-6h6v6"/></svg>
                </div>
                <h3>Arxitektura loyihasi</h3>
                <p>Turar-joy va tijorat binolari uchun bosh reja, fasad va konstruktiv loyihalar.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                </div>
                <h3>Interyer dizayn</h3>
                <p>Zamonaviy va shinam interyer yechimlari, 3D vizualizatsiya bilan.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3>Qurilish nazorati</h3>
                <p>Loyiha asosida qurilish jarayonini kuzatish va muvofiqlikni ta'minlash.</p>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <strong>"MY PERFECT HOME" MCHJ</strong> &middot; Toshkent shahri, Yangihayot tumani &middot; &copy; {{ date('Y') }}
    </div>
</footer>

<script>
(function () {
    // Telefon raqamini avtomatik formatlash
    var phone = document.getElementById('phone');
    if (phone) {
        phone.addEventListener('input', function () {
            var digits = phone.value.replace(/\D/g, '').replace(/^998/, '').slice(0, 9);
            var out = '+998';
            if (digits.length > 0) out += ' ' + digits.slice(0, 2);
            if (digits.length > 2) out += ' ' + digits.slice(2, 5);
            if (digits.length > 5) out += ' ' + digits.slice(5, 7);
            if (digits.length > 7) out += ' ' + digits.slice(7, 9);
            phone.value = out;
        });
    }

    // CAD-krest effekti — faqat haqiqiy sichqonchali qurilmalarda
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    document.body.classList.add('cad-active');
    var h = document.getElementById('cadH');
    var v = document.getElementById('cadV');
    var sq = document.getElementById('cadSquare');
    var coordWrap = document.getElementById('cadCoord');
    var coordText = document.getElementById('coordText');

    document.addEventListener('mousemove', function (e) {
        var x = e.clientX, y = e.clientY;
        h.style.top = y + 'px';
        v.style.left = x + 'px';
        sq.style.left = (x - 7) + 'px';
        sq.style.top = (y - 7) + 'px';
        coordWrap.style.left = (x + 18) + 'px';
        coordWrap.style.top = (y + 12) + 'px';
        coordText.textContent = 'X ' + (x / 100).toFixed(3) + '   Y ' + (y / 100).toFixed(3);
    });
})();
</script>

</body>
</html>
