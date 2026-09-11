<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>"MY PERFECT HOME" — Arxitektura va loyihalash</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior: smooth; }
body { font-family:'Manrope', Arial, sans-serif; color:#e5e7eb; background:#0c0a08; }
a { color: inherit; text-decoration:none; }
.container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ── Header ── */
.site-header {
    position: sticky; top: 0; z-index: 30;
    background: rgba(10,8,6,.85); backdrop-filter: blur(6px);
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.site-header .bar { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 20px; }
.brand { display:flex; align-items:center; gap:10px; }
.brand-text { display:flex; flex-direction:column; line-height:1.15; }
.brand-text strong { font-weight:800; font-size:14px; letter-spacing:.5px; color:#fff; }
.brand-text span { font-size:8.5px; letter-spacing:2px; color:#c9b28a; }
.main-nav { display:none; align-items:center; gap:34px; }
.main-nav a { font-size:13px; font-weight:600; color:#e9e4da; }
.main-nav a:hover { color:#d4af6a; }
.header-right { display:flex; align-items:center; gap:14px; }
.login-btn {
    display:flex; align-items:center; gap:7px; color:#fff; font-size:12.5px; font-weight:700;
    border:1px solid rgba(255,255,255,.3); padding:8px 16px; border-radius:24px; white-space:nowrap;
}
.login-btn:hover { border-color:#d4af6a; color:#d4af6a; }
@media(min-width:900px){ .main-nav{ display:flex; } }

/* ── Hero ── */
.hero {
    position:relative; overflow:hidden;
    height:calc(100vh - 68px); height:calc(100dvh - 68px);
    display:flex; flex-direction:column; color:#fff;
    background:#0c0a08;
}
.hero-bg { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:center center; z-index:0; }
.hero-overlay {
    position:absolute; inset:0; z-index:1;
    background:linear-gradient(100deg, rgba(10,8,6,.92) 0%, rgba(10,8,6,.62) 42%, rgba(10,8,6,.2) 68%, rgba(10,8,6,.4) 100%);
}
.hero-inner { position:relative; z-index:2; flex:1; display:flex; flex-direction:column; justify-content:center; padding:24px 20px; gap:16px; max-width:620px; }

/* Yondan kirib keluvchi animatsiya */
@keyframes slideInLeft { from { opacity:0; transform:translateX(-36px); } to { opacity:1; transform:translateX(0); } }
.anim-in { opacity:0; animation: slideInLeft .75s cubic-bezier(.2,.7,.3,1) forwards; }
.anim-d1 { animation-delay: .1s; }
.anim-d2 { animation-delay: .25s; }
.anim-d3 { animation-delay: .42s; }
.anim-d4 { animation-delay: .6s; }

.hero-eyebrow { font-size:14px; font-weight:500; color:#e9e4da; }
.hero-inner h1 { font-size:38px; line-height:1.15; letter-spacing:-1px; }
.hero-inner h1 .light { font-weight:400; }
.hero-inner h1 .bold { font-weight:800; }
.hero-inner p { font-size:14.5px; line-height:1.7; color:#d9d3c8; max-width:460px; }
.cta-outline {
    width:fit-content; display:flex; align-items:center; gap:10px;
    border:1.5px solid #d4af6a; color:#fff; font-weight:700; font-size:14px;
    padding:14px 24px; border-radius:30px; margin-top:6px;
}
.cta-outline:hover { background:#d4af6a; color:#14110c; }

.hero-steps {
    display:none; align-items:center; gap:12px; font-size:12.5px; color:#e9e4da; font-weight:600;
}
.hero-steps svg { flex-shrink:0; }
.hero-phone { position:relative; z-index:2; display:flex; align-items:center; gap:9px; padding:0 20px 22px; font-size:13.5px; font-weight:700; }

@media(min-width:900px){
    .hero-inner { padding:0 0 0 64px; max-width:640px; }
    .hero-inner h1 { font-size:56px; }
    .hero-bottom-row { position:absolute; z-index:2; left:64px; right:64px; bottom:44px; display:flex; align-items:center; justify-content:space-between; }
    .hero-steps { display:flex; }
    .hero-phone { padding:0; }
}

/* ── Xizmatlar ── */
.section { padding: 56px 0; background:#0c0a08; border-top:1px solid rgba(255,255,255,.06); }
.section-title { font-size:24px; font-weight:800; color:#fff; text-align:center; }
.section-sub { text-align:center; color:#9ca3af; font-size:14px; margin-top:8px; max-width:520px; margin-left:auto; margin-right:auto; }
.services-grid { margin-top:32px; display:grid; grid-template-columns:1fr; gap:18px; }
@media(min-width:800px){ .services-grid{ grid-template-columns:repeat(3,1fr); } }
.service-card {
    background:#161310; border:1px solid #2b241c; border-radius:16px; padding:24px 22px;
}
.service-icon {
    width:44px; height:44px; border-radius:12px; background:rgba(212,175,106,.14); color:#d4af6a;
    display:flex; align-items:center; justify-content:center; margin-bottom:14px;
}
.service-card h3 { font-size:16px; font-weight:800; color:#fff; margin-bottom:6px; }
.service-card p { font-size:13.5px; color:#9ca3af; line-height:1.55; }

/* ── Ariza forma ── */
.form-container {
    width:100%; max-width:340px;
    background:#161310; border:1.5px solid #3a3225; border-radius:14px;
    padding:20px 18px; display:flex; flex-direction:column; gap:13px;
}
.form-container .form-title { font-weight:700; font-size:15px; }
.form-container .form { display:flex; flex-direction:column; gap:13px; }
.form-container .form-group { display:flex; flex-direction:column; gap:5px; }
.form-container label { color:#a99a7c; font-weight:600; font-size:11px; letter-spacing:.3px; text-transform:uppercase; }
.form-container input, .form-container textarea {
    width:100%; padding:12px 13px; border-radius:8px; color:#fff; font-family:inherit;
    font-size:15px; background:transparent; border:1px solid #40382a;
}
.form-container input::placeholder, .form-container textarea::placeholder { color:#6b6459; opacity:1; }
.form-container textarea { resize:none; height:64px; }
.form-container input:focus, .form-container textarea:focus { outline:none; border-color:#d4af6a; }
.form-submit-btn {
    display:flex; align-items:center; justify-content:center; font-family:inherit;
    color:#14110c; font-weight:800; font-size:13px; letter-spacing:.5px; text-transform:uppercase;
    background:#d4af6a; border:none; padding:13px 0; border-radius:8px; cursor:pointer;
}
.form-submit-btn:hover { background:#e5c384; }
.form-error { color:#f87171; font-size:11.5px; font-weight:600; margin-top:-6px; }
.form-success {
    background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.4); color:#86efac;
    font-size:12.5px; font-weight:700; padding:10px 14px; border-radius:8px;
}
.contact-section { padding:56px 0 64px; }
.contact-wrap { display:flex; flex-direction:column; gap:32px; align-items:flex-start; }
.contact-info { display:flex; flex-direction:column; gap:14px; max-width:460px; }
.contact-info h2 { font-size:24px; font-weight:800; color:#fff; }
.contact-info p { font-size:14px; line-height:1.7; color:#9ca3af; }
.contact-line { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:600; color:#e5e7eb; }
@media(min-width:800px){ .contact-wrap{ flex-direction:row; justify-content:space-between; } }

/* ── Footer ── */
.site-footer { background:#080705; color:#8a8378; padding:24px 0; text-align:center; font-size:12px; border-top:1px solid rgba(255,255,255,.06); }
.site-footer strong { color:#e2e8f0; }
</style>
</head>
<body>

<header class="site-header">
    <div class="bar">
        <div class="brand">
            <svg width="26" height="24" viewBox="0 0 27 28" fill="none" stroke="#d4af6a" stroke-width="1.6" stroke-linejoin="round">
                <path d="M2 26V14L9 8V26"/>
                <path d="M9 26V10L16.5 3V26"/>
                <path d="M16.5 26V12L24 6V26"/>
            </svg>
            <div class="brand-text">
                <strong>MY PERFECT HOME</strong>
                <span>IDEAS &middot; SPACE &middot; LIFE</span>
            </div>
        </div>
        <nav class="main-nav">
            <a href="#xizmatlar">Xizmatlar</a>
            <a href="#aloqa">Aloqa</a>
        </nav>
        <div class="header-right">
            <a class="login-btn" href="/admin">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Kirish
            </a>
        </div>
    </div>
</header>

<section class="hero">
    <img class="hero-bg" src="{{ asset('images/hero-villa.jpg') }}" alt="">
    <div class="hero-overlay"></div>

    <div class="hero-inner">
        <span class="hero-eyebrow anim-in anim-d1">Sizning orzularingiz — bizning loyihamiz</span>
        <h1 class="anim-in anim-d2"><span class="light">Zamonaviy</span><br><span class="bold">arxitektura</span></h1>
        <p class="anim-in anim-d3">Biz zamonaviy uslub, funksionallik va estetikani birlashtirib, hayotingiz uchun ideal makon yaratamiz.</p>
        <a class="cta-outline anim-in anim-d4" href="#aloqa">
            Bog'lanish uchun ariza qoldirish
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="hero-bottom-row">
        <div class="hero-phone">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.65 2.65a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.43-1.42a2 2 0 0 1 2.11-.45c.86.31 1.75.53 2.65.65A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:+998770919101">+998 77 091 91 01</a>
        </div>
        <div class="hero-steps">
            <span>Loyihalash</span>
            <svg width="14" height="10" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            <span>Dizayn</span>
            <svg width="14" height="10" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            <span>Qurilish</span>
            <svg width="14" height="10" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            <span style="color:#fff;">Natija</span>
        </div>
    </div>
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

<section class="contact-section" id="aloqa">
    <div class="container contact-wrap">
        <div class="contact-info">
            <h2>Biz bilan bog'laning</h2>
            <p>Loyihangiz haqida qisqacha ma'lumot qoldiring — mutaxassislarimiz tez orada siz bilan bog'lanadi.</p>
            <div class="contact-line">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.65 2.65a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.43-1.42a2 2 0 0 1 2.11-.45c.86.31 1.75.53 2.65.65A2 2 0 0 1 22 16.92z"/></svg>
                <a href="tel:+998770919101">+998 77 091 91 01</a>
            </div>
            <div class="contact-line">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.65 2.65a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.43-1.42a2 2 0 0 1 2.11-.45c.86.31 1.75.53 2.65.65A2 2 0 0 1 22 16.92z"/></svg>
                <a href="tel:+998994681991">+998 99 468 19 91</a>
            </div>
            <div class="contact-line" style="align-items:flex-start;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4af6a" stroke-width="2" style="margin-top:2px; flex-shrink:0;"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span style="font-weight:500; color:#9ca3af;">Toshkent sh., Yangihayot t., Uzar ko'chasi, 60-uy, 46-xona</span>
            </div>
        </div>

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
                    <label for="message">Koment</label>
                    <textarea id="message" name="message" placeholder="Loyihangiz haqida qisqacha...">{{ old('message') }}</textarea>
                    @error('message') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="form-submit-btn">Yuborish</button>
            </form>
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
    var phone = document.getElementById('phone');
    if (!phone) return;
    phone.addEventListener('input', function () {
        var digits = phone.value.replace(/\D/g, '').replace(/^998/, '').slice(0, 9);
        var out = '+998';
        if (digits.length > 0) out += ' ' + digits.slice(0, 2);
        if (digits.length > 2) out += ' ' + digits.slice(2, 5);
        if (digits.length > 5) out += ' ' + digits.slice(5, 7);
        if (digits.length > 7) out += ' ' + digits.slice(7, 9);
        phone.value = out;
    });
})();
</script>

</body>
</html>
