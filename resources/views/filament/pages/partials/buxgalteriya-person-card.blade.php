{{-- Xodim kartasi — PUL HISOBI EMAS: shu oyda shu xodimga qilingan xarajatlar
     yig'indisi (xarajatdagi "kim uchun" belgisi bo'yicha) va qaysi hisobdan
     qanchasi chiqqani. Balansga ta'sir qilmaydi. --}}
@php $ps = $personSpend[$acc->user_id] ?? ['total' => 0, 'count' => 0, 'by' => []]; @endphp
<div class="acc-card is-expense-total {{ $acc->background_image ? 'has-bg' : '' }}"
     @if($acc->background_image) style="--acc-bg:url('{{ \Illuminate\Support\Facades\Storage::url($acc->background_image) }}')" @endif
     draggable="true"
     @dragstart="$event.target.classList.add('dragging'); $event.dataTransfer.setData('text/plain', '{{ $acc->id }}')"
     @dragend="$event.target.classList.remove('dragging')">
    <div class="acc-actions">
        <button class="acc-act-btn" @click.stop="$wire.call('setBgTarget', {{ $acc->id }}).then(() => $refs.bgFileInput.click())" title="Fon rasm qo'yish">🖼️</button>
        @if($acc->background_image)
        <button class="acc-act-btn" wire:click.stop="removeAccountBackground({{ $acc->id }})" wire:confirm="Fon rasmni olib tashlaysizmi?" title="Fonni olib tashlash">🚫</button>
        @endif
        <button class="acc-act-btn" wire:click.stop="openAccountModal({{ $acc->id }})" title="Tahrirlash">✎</button>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
        <span class="acc-icon">👤</span>
    </div>
    <div>
        <div class="acc-name">{{ $acc->name }}</div>
        <div class="exp-tot-l" style="font-size:10px;letter-spacing:.05em;margin-top:6px;text-transform:uppercase">Xarajatlar · {{ $bxMonthLabel }}</div>
        <div class="acc-balance exp-tot-sum" style="font-size:22px;margin-top:2px">− {{ number_format($ps['total'], 0, '.', ' ') }} <span class="exp-tot-l" style="font-size:13px">so'm</span></div>
        <div style="margin-top:8px;display:flex;flex-direction:column;gap:3px">
            @forelse(array_slice($ps['by'], 0, 3, true) as $label => $sum)
                <div style="display:flex;justify-content:space-between;gap:8px;font-size:11.5px">
                    <span class="exp-tot-l" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $label }}dan</span>
                    <span class="exp-tot-v" style="font-weight:800;white-space:nowrap">{{ number_format($sum, 0, '.', ' ') }}</span>
                </div>
            @empty
                <div class="exp-tot-l" style="font-size:11.5px">Bu oyda xarajat yo'q</div>
            @endforelse
            @if($ps['count'])<div class="exp-tot-l" style="font-size:11px">{{ $ps['count'] }} ta xarajat</div>@endif
        </div>
    </div>
</div>
