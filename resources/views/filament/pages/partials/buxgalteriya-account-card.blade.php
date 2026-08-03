<div class="acc-card {{ $acc->is_favorite ? 'is-fav' : '' }} {{ $acc->background_image ? 'has-bg' : '' }}"
     @if($acc->background_image) style="--acc-bg:url('{{ \Illuminate\Support\Facades\Storage::url($acc->background_image) }}')" @endif
     draggable="true"
     @dragstart="$event.target.classList.add('dragging'); $event.dataTransfer.setData('text/plain', '{{ $acc->id }}')"
     @dragend="$event.target.classList.remove('dragging')"
     wire:click="toggleFavorite({{ $acc->id }})">
    <span class="acc-star {{ $acc->is_favorite ? 'on' : '' }}" title="Belgilash">★</span>
    <div class="acc-actions">
        <button class="acc-act-btn" @click.stop="$wire.call('setBgTarget', {{ $acc->id }}).then(() => $refs.bgFileInput.click())" title="Fon rasm qo'yish">🖼️</button>
        @if($acc->background_image)
        <button class="acc-act-btn" wire:click.stop="removeAccountBackground({{ $acc->id }})" wire:confirm="Fon rasmni olib tashlaysizmi?" title="Fonni olib tashlash">🚫</button>
        @endif
        <button class="acc-act-btn" wire:click.stop="openAccountModal({{ $acc->id }})" title="Tahrirlash">✎</button>
        <button class="acc-act-btn" wire:click.stop="deleteAccount({{ $acc->id }})" wire:confirm="Ushbu hisobni o'chirasizmi?" title="O'chirish">✕</button>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:flex-start">
        @if($acc->type === 'karta')
        <div class="acc-chip"></div>
        @else
        <span class="acc-icon-wrap">{{ $acc->type === 'naqd' ? '💵' : '🏦' }}</span>
        @endif
        @if($acc->bank_name)
        @php
            $netKey  = strtolower(trim($acc->bank_name));
            $netCls  = str_contains($netKey, 'uzcard') ? 'net-uzcard'
                     : (str_contains($netKey, 'visa') ? 'net-visa'
                     : (str_contains($netKey, 'master') ? 'net-mastercard' : ''));
        @endphp
        <span class="acc-badge {{ $netCls }}">{{ $acc->bank_name }}</span>
        @endif
    </div>

    <div>
        <div class="acc-name">{{ $acc->name ?: $typeOptions[$acc->type] }} @if($acc->is_personal)<span class="acc-personal-badge">SHAXSIY</span>@endif @if($acc->is_expense_account)<span class="acc-personal-badge" style="background:#7c2d12;color:#fdba74">XARAJATLAR</span>@endif</div>
        @if($acc->type === 'karta' && $acc->card_number)
        <div class="acc-num">{{ $acc->card_number }}</div>
        @elseif($acc->type === 'bank' && $acc->account_number)
        <div class="acc-num" style="font-size:12px">{{ $acc->account_number }}</div>
        @endif
    </div>

    <div class="acc-bottom">
        <div>
            <div class="acc-balance-lbl">Balans</div>
            <div class="acc-balance">{{ number_format($acc->balance, 0, '.', ' ') }} <span style="font-size:11px;opacity:.7;color:#4ade80">so'm</span></div>
        </div>
        @if($acc->type === 'karta' && $acc->expiry_date)
        <div class="acc-sub-right">Amal qilish<br>{{ $acc->expiry_date }}</div>
        @endif
    </div>
</div>
