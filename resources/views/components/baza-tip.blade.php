{{--
    "Bazaga" tugmasi atrofidagi izoh: sichqoncha olib borilganda — nima uchun
    bu tugma kerakligi chiqadi. Foydalanish: <x-baza-tip><button>…</button></x-baza-tip>
    $align: 'left' (sukut) | 'right' — izoh qaysi chetga tekislanadi.
--}}
@props(['align' => 'left'])
@once
<style>
    .baza-tip-wrap{position:relative;display:inline-flex}
    .baza-tip-box{display:none;position:absolute;top:calc(100% + 8px);z-index:3000;width:320px;max-width:80vw;
        background:#0f172a;color:#e2e8f0;border-radius:10px;padding:12px 14px;font-size:12px;font-weight:400;line-height:1.55;
        box-shadow:0 12px 30px rgba(0,0,0,.3);text-align:left;white-space:normal;pointer-events:none}
    .baza-tip-box b{color:#fff}
    .baza-tip-box::before{content:"";position:absolute;top:-6px;width:12px;height:12px;background:#0f172a;transform:rotate(45deg)}
    .baza-tip-box.al-left{left:0}.baza-tip-box.al-left::before{left:18px}
    .baza-tip-box.al-right{right:0}.baza-tip-box.al-right::before{right:18px}
    .baza-tip-wrap:hover .baza-tip-box,.baza-tip-wrap:focus-within .baza-tip-box{display:block}
</style>
@endonce
<span {{ $attributes->merge(['class' => 'baza-tip-wrap']) }}>
    {{ $slot }}
    <span class="baza-tip-box al-{{ $align === 'right' ? 'right' : 'left' }}" role="tooltip">
        <b>📦 Baza — eskirgan loyihalar uchun</b><br>
        Ishi tugagan, eski loyihalarni asosiy saytdan butunlay olib qo'yadi, ular boshqa loyihalar bilan aralashib adashtirmaydi.<br><br>
        • Kanban, ro'yxatlar, Arxiv, qarzlar va statistikada <b>ko'rinmaydi</b>.<br>
        • Kassa qoldig'i va xodimlar maoshi <b>o'zgarmaydi</b>.<br>
        • Mijoz qisman to'lagan bo'lsa — xodimga to'langan qismga mos ulush beriladi.<br>
        • Faqat admin ko'radi, faqat o'qish uchun. "Baza" bo'limidan istalgan vaqt <b>qaytarish</b> mumkin.
    </span>
</span>
