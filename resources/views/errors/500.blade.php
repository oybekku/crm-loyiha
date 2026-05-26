@extends('errors.layout')
@section('content')
<div class="icon">🔧</div>
<div class="title">Sayt yangilik kiritilyabdi</div>
<div class="desc">
    Hozir texnik ishlar olib borilmoqda.<br>
    Bir oz kutib, sahifani yangilang.
</div>
<div class="code-badge">Xato kodi: 500 — Server xatosi</div>
<br>
<a href="javascript:location.reload()" class="btn">Qayta urinish</a>
<div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
@endsection
