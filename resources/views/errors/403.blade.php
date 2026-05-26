@extends('errors.layout')
@section('content')
<div class="icon">🔒</div>
<div class="title">Kirish taqiqlangan</div>
<div class="desc">
    Bu sahifaga kirish huquqingiz yo'q.<br>
    Agar bu xato deb hisoblasangiz, administratorga murojaat qiling.
</div>
<div class="code-badge">Xato kodi: 403 — Ruxsat yo'q</div>
<br>
<a href="/admin" class="btn">Bosh sahifaga qaytish</a>
@endsection
