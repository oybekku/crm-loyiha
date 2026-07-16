<x-filament-panels::page>
<div class="bx-page-root">
<style>
/* Shu sahifada chap navigatsiya menyusini yashirish va asosiy maydonni
   to'liq kenglikka, qora fonga chiqarish (Click ilovasi uslubidagi
   to'liq ekranli ko'rinish uchun). :has() bilan faqat shu sahifa DOM'ida
   bo'lganda ishlaydi — boshqa sahifalarga ta'sir qilmaydi. */
body:has(.bx-page-root) .fi-main-sidebar{display:none !important}
body:has(.bx-page-root) .fi-main{max-width:100% !important;padding:0 !important;background:#191a1b;min-height:100vh}

.bx-wrap{max-width:1200px;margin:0 auto;color:#e2e8f0;padding:28px 20px}
.bx-back{display:inline-flex;align-items:center;gap:6px;color:#94a3b8;font-size:13px;text-decoration:none;margin-bottom:18px}
.bx-back:hover{color:#e2e8f0}
.bx-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px}
.bx-title{font-size:20px;font-weight:800;color:#f1f5f9 !important}
.bx-add{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:11px 20px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
.bx-add:hover{background:#1d4ed8}

.bx-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
@media (max-width:900px){.bx-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:600px){.bx-grid{grid-template-columns:1fr}}

.acc-card{
    background:linear-gradient(135deg,#0d0d0f,#1a1a1d 55%,#232326) !important;
    border:2px solid #2a2a2e;
    border-radius:16px;
    padding:20px;
    position:relative;
    overflow:hidden;
    min-height:170px;
    box-shadow:0 10px 26px rgba(0,0,0,.35);
    cursor:pointer;
    transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    color:#e2e8f0 !important;
}
/* Filament'ning dark-mode/light-mode standart matn ranglari bu kartalar
   ichidagi matnni "yutib" yuborayotgan edi (fon qora, matn ham qorong'i
   chiqib, deyarli ko'rinmas bo'lib qolgan edi) — shu sababli har bir
   matn elementiga aniq rang !important bilan mahkamlanadi. */
.acc-card *{color:inherit}
.acc-card::before{
    content:'';
    position:absolute; right:-30px; top:-30px; width:140px; height:140px;
    background:rgba(255,255,255,.05); border-radius:50%;
}
.acc-card:hover{transform:translateY(-3px);box-shadow:0 0 16px rgba(56,189,248,.35),0 14px 30px rgba(0,0,0,.4)}
.acc-card.is-fav{border-color:#3b82f6;box-shadow:0 0 0 1px #3b82f6,0 0 18px rgba(59,130,246,.45)}
.acc-card.is-total{background:linear-gradient(135deg,#052e1a,#0a3d24 55%,#0f4a2c) !important;border-color:#166534;cursor:default}
.acc-card.is-total .acc-name{color:#bbf7d0 !important}

.acc-star{position:absolute;top:14px;right:14px;font-size:15px;color:#475569 !important;transition:color .2s,transform .2s;z-index:2}
.acc-star.on{color:#3b82f6 !important;transform:scale(1.15)}
.acc-actions{position:absolute;top:14px;right:38px;display:flex;gap:4px;opacity:0;transition:opacity .2s;z-index:2}
.acc-card:hover .acc-actions{opacity:1}
.acc-act-btn{width:24px;height:24px;border-radius:6px;border:none;background:rgba(255,255,255,.1);color:#e2e8f0 !important;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px}
.acc-act-btn:hover{background:rgba(255,255,255,.22)}

.acc-icon{font-size:20px;opacity:.9}
.acc-badge{font-size:9px;font-weight:800;color:#0f172a !important;background:#38bdf8;border-radius:5px;padding:3px 8px;white-space:nowrap;align-self:flex-start}
.acc-name{font-size:13px;font-weight:700;color:#f1f5f9 !important;margin-top:10px}
.acc-num{font-size:15px;letter-spacing:.15em;font-weight:700;color:#e2e8f0 !important;font-family:'Courier New',monospace;margin:12px 0 0;word-break:break-all}
.acc-bottom{display:flex;justify-content:space-between;align-items:flex-end;margin-top:14px}
.acc-balance-lbl{font-size:9px;color:#94a3b8 !important;text-transform:uppercase;letter-spacing:.5px}
.acc-balance{font-size:19px;font-weight:900;color:#4ade80 !important}
.acc-sub-right{font-size:9px;color:#94a3af !important;text-align:right}

.bx-empty{grid-column:1/-1;text-align:center;color:#64748b !important;padding:30px;font-size:13px;background:#1a1a1d;border-radius:14px;border:1px dashed #2a2a2e}

/* Modal */
.bx-modal-ov{position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:200;display:flex;align-items:center;justify-content:center;padding:16px}
.bx-modal{background:#161b22;color:#e2e8f0;border-radius:16px;width:100%;max-width:440px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.bx-field{margin-bottom:14px}
.bx-field label{font-size:12px;font-weight:600;color:#94a3b8;display:block;margin-bottom:5px}
.bx-field input,.bx-field select{width:100%;padding:9px 12px;border:1.5px solid #2a2a2e;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#0d1117;color:#eee}
.bx-type-tabs{display:flex;gap:6px;margin-bottom:16px}
.bx-type-tab{flex:1;padding:9px;border-radius:9px;border:1.5px solid #2a2a2e;background:#0d1117;color:#94a3b8;font-size:12px;font-weight:700;cursor:pointer;text-align:center}
.bx-type-tab.active{border-color:#2563eb;background:#12224a;color:#93c5fd}

.bx-month{display:flex;align-items:center;gap:4px;background:#161b22;border:1.5px solid #2a2a2e;border-radius:9px;padding:3px 5px}
.bx-month-btn{background:#1a1a1d;border:none;border-radius:6px;width:28px;height:28px;cursor:pointer;font-size:15px;color:#94a3b8;line-height:1}
.bx-month-btn:hover{background:#232326;color:#e2e8f0}
.bx-month-lbl{font-size:13px;font-weight:700;color:#60a5fa;min-width:112px;text-align:center;white-space:nowrap}

/* Xarajatlar (rasxodlar) bo'limi */
.exp-panel{margin-top:22px;background:#161b22;border:1.5px solid #2a2a2e;border-radius:14px;overflow:hidden}
.exp-head{display:flex;align-items:center;gap:10px;padding:16px 18px;cursor:pointer;user-select:none}
.exp-head-title{font-size:14px;font-weight:800;color:#f1f5f9;display:flex;align-items:center;gap:8px}
.exp-head-total{font-size:15px;font-weight:800;color:#f87171;margin-left:auto}
.exp-head-chev{color:#64748b;transition:transform .2s;flex-shrink:0}
.exp-head-add{background:#7f1d1d;color:#fca5a5;border:none;border-radius:8px;padding:7px 13px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;flex-shrink:0}
.exp-head-add:hover{background:#991b1b;color:#fecaca}
.exp-list{border-top:1px solid #2a2a2e}
.exp-row{display:flex;align-items:center;gap:12px;padding:11px 18px;border-bottom:1px solid #1f242c}
.exp-row:last-child{border-bottom:none}
.exp-row:hover{background:#1a1f28}
.exp-date{font-size:11px;color:#64748b;font-family:monospace;flex-shrink:0;width:66px}
.exp-acc-badge{font-size:10px;font-weight:700;color:#93c5fd;background:#12224a;border-radius:5px;padding:3px 8px;white-space:nowrap;flex-shrink:0}
.exp-comment{font-size:12.5px;color:#cbd5e1;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.exp-amount{font-size:13.5px;font-weight:800;color:#f87171;flex-shrink:0;white-space:nowrap}
.exp-row-actions{display:flex;gap:4px;flex-shrink:0;opacity:0;transition:opacity .15s}
.exp-row:hover .exp-row-actions{opacity:1}
.exp-empty{padding:26px;text-align:center;color:#64748b;font-size:12.5px}
</style>

<div class="bx-wrap">
    <a href="{{ route('filament.admin.pages.kanban-board') }}" class="bx-back">← Ortga</a>

    <div class="bx-top">
        <div class="bx-title">💳 Buxgalteriya</div>
        <div style="display:flex;align-items:center;gap:10px">
            <div class="bx-month">
                <button class="bx-month-btn" wire:click="bxChangeMonth(-1)" title="Oldingi oy">‹</button>
                <span class="bx-month-lbl">📅 {{ $bxMonthLabel }}</span>
                <button class="bx-month-btn" wire:click="bxChangeMonth(1)" title="Keyingi oy">›</button>
            </div>
            <button class="bx-add" wire:click="openAccountModal">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Yangi hisob qo'shish
            </button>
        </div>
    </div>

    <div class="bx-grid">
        {{-- Jami balans — boshqa kartalar bilan bir xil o'lchamda --}}
        <div class="acc-card is-total">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <span class="acc-icon">💰</span>
            </div>
            <div>
                <div class="acc-name">Jami balans ({{ $bxMonthLabel }})</div>
                <div class="acc-balance" style="font-size:24px;margin-top:10px">{{ number_format($totalBalance, 0, '.', ' ') }} <span style="font-size:13px;opacity:.7">so'm</span></div>
            </div>
        </div>

        @php $allAccs = collect($byType)->flatten(1); @endphp
        @forelse($allAccs as $acc)
        <div class="acc-card {{ $acc->is_favorite ? 'is-fav' : '' }}" wire:click="toggleFavorite({{ $acc->id }})">
            <span class="acc-star {{ $acc->is_favorite ? 'on' : '' }}" title="Belgilash">★</span>
            <div class="acc-actions">
                <button class="acc-act-btn" wire:click.stop="openAccountModal({{ $acc->id }})" title="Tahrirlash">✎</button>
                <button class="acc-act-btn" wire:click.stop="deleteAccount({{ $acc->id }})" wire:confirm="Ushbu hisobni o'chirasizmi?" title="O'chirish">✕</button>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:flex-start">
                <span class="acc-icon">
                    @if($acc->type === 'karta') 💳
                    @elseif($acc->type === 'naqd') 💵
                    @else 🏦
                    @endif
                </span>
                @if($acc->bank_name)
                <span class="acc-badge">{{ $acc->bank_name }}</span>
                @endif
            </div>

            <div>
                <div class="acc-name">{{ $acc->name ?: $typeOptions[$acc->type] }}</div>
                @if($acc->type === 'karta' && $acc->card_number)
                <div class="acc-num">{{ $acc->card_number }}</div>
                @elseif($acc->type === 'bank' && $acc->account_number)
                <div class="acc-num" style="font-size:12px">{{ $acc->account_number }}</div>
                @endif
            </div>

            <div class="acc-bottom">
                <div>
                    <div class="acc-balance-lbl">Balans</div>
                    <div class="acc-balance">{{ number_format($acc->balance, 0, '.', ' ') }} <span style="font-size:11px;opacity:.7">so'm</span></div>
                </div>
                @if($acc->type === 'karta' && $acc->expiry_date)
                <div class="acc-sub-right">Amal qilish<br>{{ $acc->expiry_date }}</div>
                @endif
            </div>
        </div>
        @empty
        <div class="bx-empty">Hali hech qanday hisob qo'shilmagan — "Yangi hisob qo'shish" tugmasini bosing</div>
        @endforelse
    </div>

    {{-- ── Xarajatlar (rasxodlar) — svernut qilinadigan ro'yxat ── --}}
    <div class="exp-panel" x-data="{ open: false }">
        <div class="exp-head" @click="open = !open">
            <span class="exp-head-title">🧾 Xarajatlar <span style="font-size:11px;color:#64748b;font-weight:600">({{ $bxMonthLabel }})</span></span>
            <span class="exp-head-total">− {{ number_format($totalSpent, 0, '.', ' ') }} so'm</span>
            <button class="exp-head-add" @click.stop="open = true" wire:click.stop="openExpenseModal">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Xarajat qo'shish
            </button>
            <svg class="exp-head-chev" :style="open ? 'transform:rotate(180deg)' : ''" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="exp-list" x-show="open" x-collapse>
            @forelse($expenses as $exp)
            <div class="exp-row">
                <span class="exp-date">{{ $exp->expense_date->format('d.m.Y') }}</span>
                <span class="exp-acc-badge">{{ $exp->account ? ($exp->account->name ?: $typeOptions[$exp->account->type]) : '—' }}</span>
                <span class="exp-comment">{{ $exp->comment ?: '—' }}</span>
                <span class="exp-amount">− {{ number_format($exp->amount, 0, '.', ' ') }} so'm</span>
                <div class="exp-row-actions">
                    <button class="acc-act-btn" wire:click="openExpenseModal({{ $exp->id }})" title="Tahrirlash">✎</button>
                    <button class="acc-act-btn" wire:click="deleteExpense({{ $exp->id }})" wire:confirm="Ushbu xarajatni o'chirasizmi?" title="O'chirish">✕</button>
                </div>
            </div>
            @empty
            <div class="exp-empty">Bu oyda hali xarajat kiritilmagan</div>
            @endforelse
        </div>
    </div>

    {{-- ── Hisob qo'shish/tahrirlash oynasi ── --}}
    @if($showAccountModal)
    <div class="bx-modal-ov" wire:click.self="closeAccountModal">
        <div class="bx-modal">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
                <div style="font-size:16px;font-weight:800">{{ $editAccountId ? 'Hisobni tahrirlash' : 'Yangi hisob qo\'shish' }}</div>
                <button wire:click="closeAccountModal" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;line-height:1">×</button>
            </div>

            <div class="bx-type-tabs">
                @foreach($typeOptions as $tk => $tl)
                <div class="bx-type-tab {{ $formType === $tk ? 'active' : '' }}" wire:click="$set('formType', '{{ $tk }}')">{{ $tl }}</div>
                @endforeach
            </div>

            <div class="bx-field">
                <label>Nomi</label>
                <input type="text" wire:model="formName" placeholder="Masalan: Asosiy karta">
            </div>

            @if($formType === 'karta')
            <div class="bx-field">
                <label>Karta raqami</label>
                <input type="text" wire:model="formCardNumber" placeholder="4073 **** **** 1805">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="bx-field">
                    <label>Bank</label>
                    <input type="text" wire:model="formBankName" placeholder="Humo, Uzcard...">
                </div>
                <div class="bx-field">
                    <label>Amal qilish muddati</label>
                    <input type="text" wire:model="formExpiryDate" placeholder="01/30">
                </div>
            </div>
            @elseif($formType === 'bank')
            <div class="bx-field">
                <label>Hisob raqami</label>
                <input type="text" wire:model="formAccountNumber" placeholder="2020 8000 ...">
            </div>
            <div class="bx-field">
                <label>Bank nomi</label>
                <input type="text" wire:model="formBankName" placeholder="Masalan: Ipoteka bank">
            </div>
            @endif

            <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px">
                <input type="checkbox" id="bx-fav" wire:model="formIsFavorite" style="width:16px;height:16px">
                <label for="bx-fav" style="font-size:12px;color:#94a3b8;cursor:pointer;margin-bottom:0">Belgilangan (ko'k ramka) sifatida boshlash</label>
            </div>

            <div style="display:flex;gap:10px">
                <button wire:click="closeAccountModal" style="flex:1;padding:11px;border-radius:9px;border:1px solid #2a2a2e;background:#0d1117;color:#94a3b8;font-weight:700;font-size:13px;cursor:pointer">Bekor</button>
                <button wire:click="saveAccount" style="flex:1;padding:11px;border-radius:9px;border:none;background:#16a34a;color:#fff;font-weight:700;font-size:13px;cursor:pointer">Saqlash</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Xarajat qo'shish/tahrirlash oynasi ── --}}
    @if($showExpenseModal)
    <div class="bx-modal-ov" wire:click.self="closeExpenseModal">
        <div class="bx-modal">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
                <div style="font-size:16px;font-weight:800">{{ $editExpenseId ? 'Xarajatni tahrirlash' : 'Xarajat qo\'shish' }}</div>
                <button wire:click="closeExpenseModal" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;line-height:1">×</button>
            </div>

            <div class="bx-field">
                <label>Qaysi hisobdan?</label>
                <select wire:model="expAccountId">
                    <option value="">— tanlang —</option>
                    @foreach($allAccounts as $a)
                    <option value="{{ $a->id }}">{{ $a->name ?: $typeOptions[$a->type] }}</option>
                    @endforeach
                </select>
                @error('expAccountId')<span style="font-size:11px;color:#f87171">{{ $message }}</span>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="bx-field">
                    <label>Summa (so'm)</label>
                    <input type="number" wire:model="expAmount" placeholder="200000">
                    @error('expAmount')<span style="font-size:11px;color:#f87171">{{ $message }}</span>@enderror
                </div>
                <div class="bx-field">
                    <label>Sana</label>
                    <input type="date" wire:model="expDate">
                    @error('expDate')<span style="font-size:11px;color:#f87171">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="bx-field">
                <label>Izoh</label>
                <input type="text" wire:model="expComment" placeholder="Masalan: Ofis ijarasi, xodim ulushi...">
            </div>

            <div style="display:flex;gap:10px;margin-top:4px">
                <button wire:click="closeExpenseModal" style="flex:1;padding:11px;border-radius:9px;border:1px solid #2a2a2e;background:#0d1117;color:#94a3b8;font-weight:700;font-size:13px;cursor:pointer">Bekor</button>
                <button wire:click="saveExpense" style="flex:1;padding:11px;border-radius:9px;border:none;background:#dc2626;color:#fff;font-weight:700;font-size:13px;cursor:pointer">Saqlash</button>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
</x-filament-panels::page>
