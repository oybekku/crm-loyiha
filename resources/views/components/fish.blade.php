{{--
    Mijoz FISH'i — barcha ro'yxatlarda bir xil: KATTA HARFDA, 15 belgidan keyin "…",
    to'liq ismi sichqoncha ustiga borganda (title). O'lchami Mijozlar qarzlari
    jadvalidagidek (14.85px, qalin). Ishlatish: <x-fish :name="$project->owner_name" />
--}}
@props(['name' => null])
@php $full = \App\Models\Project::fishFull($name); @endphp
<span {{ $attributes->merge(['style' => 'font-size:14.85px;font-weight:600;white-space:nowrap']) }} title="{{ $full }}">{{ \App\Models\Project::fishShort($name) }}</span>
