@extends('errors.layout')
@section('content')
<div class="icon">🔍</div>
<div class="title">Sahifa topilmadi</div>
<div class="desc">
    Siz qidirayotgan sahifa mavjud emas yoki<br>
    ko'chirilgan bo'lishi mumkin.
</div>
<div class="code-badge">Xato kodi: 404 — Topilmadi</div>
<br>
<a href="/admin" class="btn">Bosh sahifaga qaytish</a>
@endsection
