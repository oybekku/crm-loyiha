{{--
    Mijoz FISH'i — barcha ro'yxatlarda bir xil: KATTA HARFDA, familiya + ism to'liq
    (otasining ismisiz), to'liq FISH sichqoncha ustiga borganda (title). O'lchami Mijozlar qarzlari
    jadvalidagidek (14.85px, qalin). Ishlatish: <x-fish :name="$project->owner_name" />
--}}
@props(['name' => null])
@php $full = \App\Models\Project::fishFull($name); @endphp
<span {{ $attributes->merge(['style' => 'font-size:14.85px;font-weight:600;white-space:nowrap']) }} title="{{ $full }}">{{ \App\Models\Project::fishShort($name) }}</span>
