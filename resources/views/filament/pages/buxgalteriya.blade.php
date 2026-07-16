<x-filament-panels::page>
<style>
.bx-wrap{max-width:1100px}
.bx-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px}
.bx-total{background:linear-gradient(135deg,#0f172a,#1e293b);border:1px solid #334155;border-radius:14px;padding:16px 22px;color:#e2e8f0}
.bx-total .lbl{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;font-weight:700}
.bx-total .val{font-size:26px;font-weight:900;color:#4ade80;margin-top:2px}
.bx-add{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:11px 20px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
.bx-add:hover{background:#1d4ed8}

.bx-section{margin-bottom:28px}
.bx-section-title{font-size:14px;font-weight:800;color:#374151;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.dark .bx-section-title{color:#c9d1d9}

.bx-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}

.acc-card{
    background:linear-gradient(135deg,#0b1220,#131c33 60%,#0f1729);
    border:1.5px solid #1e293b;
    border-radius:16px;
    padding:20px;
    position:relative;
    color:#e2e8f0;
    transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    overflow:hidden;
}
.acc-card::before{
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(circle at 85% 15%, rgba(56,189,248,.10), transparent 55%);
    pointer-events:none;
}
.acc-card:hover{
    border-color:#38bdf8;
    box-shadow:0 0 18px rgba(56,189,248,.55), 0 0 42px rgba(59,130,246,.28), inset 0 0 20px rgba(56,189,248,.06);
    transform:translateY(-4px);
}
.acc-card.is-fav{border-color:#3b82f6;box-shadow:0 0 12px rgba(59,130,246,.35)}
.acc-card .acc-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:18px}
.acc-card .acc-name{font-size:13px;font-weight:700;color:#f1f5f9}
.acc-card .acc-sub{font-size:10px;color:#64748b;margin-top:2px}
.acc-card .acc-num{font-size:15px;letter-spacing:1.5px;font-weight:700;color:#e2e8f0;font-family:'Courier New',monospace;margin-bottom:14px;word-break:break-all}
.acc-card .acc-bottom{display:flex;align-items:flex-end;justify-content:space-between;gap:8px}
.acc-card .acc-balance-lbl{font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.acc-card .acc-balance{font-size:17px;font-weight:900;color:#4ade80}
.acc-card .acc-badge{font-size:9px;font-weight:800;color:#0f172a;background:#38bdf8;border-radius:5px;padding:3px 8px;white-space:nowrap}
.acc-card .acc-actions{position:absolute;top:14px;right:14px;display:flex;gap:4px;opacity:0;transition:opacity .2s}
.acc-card:hover .acc-actions{opacity:1}
.acc-card .acc-act-btn{width:24px;height:24px;border-radius:6px;border:none;background:rgba(255,255,255,.1);color:#e2e8f0;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px}
.acc-card .acc-act-btn:hover{background:rgba(255,255,255,.2)}
.acc-card .acc-star{position:absolute;top:14px;left:14px;cursor:pointer;font-size:15px;opacity:.35;transition:opacity .2s,transform .2s}
.acc-card .acc-star.on{opacity:1;color:#fbbf24}
.acc-card .acc-star:hover{transform:scale(1.2)}

.bx-empty{text-align:center;color:#9ca3af;padding:24px;font-size:13px;background:#f8fafc;border-radius:12px;border:1px dashed #e2e8f0}
.dark .bx-empty{background:#161b22;border-color:#21262d}

/* Modal */
.bx-modal-ov{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;display:flex;align-items:center;justify-content:center;padding:16px}
.bx-modal{background:#fff;border-radius:16px;width:100%;max-width:440px;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.dark .bx-modal{background:#161b22;color:#c9d1d9}
.bx-field{margin-bottom:14px}
.bx-field label{font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px}
.dark .bx-field label{color:#8b949e}
.bx-field input,.bx-field select{width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box}
.dark .bx-field input,.dark .bx-field select{background:#0d1117;border-color:#21262d;color:#eee}
.bx-type-tabs{display:flex;gap:6px;margin-bottom:16px}
.bx-type-tab{flex:1;padding:9px;border-radius:9px;border:1.5px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:12px;font-weight:700;cursor:pointer;text-align:center}
.bx-type-tab.active{border-color:#2563eb;background:#eff6ff;color:#1d4ed8}
</style>

<div class="bx-wrap" x-data="{ }">

    <div class="bx-top">
        <div class="bx-total">
            <div class="lbl">Jami balans</div>
            <div class="val">{{ number_format($totalBalance, 0, '.', ' ') }} so'm</div>
        </div>
        <button class="bx-add" wire:click="openAccountModal">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Yangi hisob qo'shish
        </button>
    </div>

    @foreach($byType as $type => $accs)
    <div class="bx-section">
        <div class="bx-section-title">
            @if($type === 'karta') 💳 Kartalar
            @elseif($type === 'naqd') 💵 Naqd pul
            @else 🏦 Bank hisoblari
            @endif
            <span style="font-weight:400;color:#9ca3af;font-size:12px">({{ $accs->count() }} ta)</span>
        </div>

        @if($accs->isEmpty())
        <div class="bx-empty">Hali {{ strtolower($typeOptions[$type]) }} qo'shilmagan</div>
        @else
        <div class="bx-grid">
            @foreach($accs as $acc)
            <div class="acc-card {{ $acc->is_favorite ? 'is-fav' : '' }}">
                <span class="acc-star {{ $acc->is_favorite ? 'on' : '' }}" wire:click="toggleFavorite({{ $acc->id }})" title="Sevimli">★</span>
                <div class="acc-actions">
                    <button class="acc-act-btn" wire:click="openAccountModal({{ $acc->id }})" title="Tahrirlash">✎</button>
                    <button class="acc-act-btn" wire:click="deleteAccount({{ $acc->id }})" wire:confirm="Ushbu hisobni o'chirasizmi?" title="O'chirish">✕</button>
                </div>

                <div class="acc-top" style="margin-top:8px">
                    <div>
                        <div class="acc-name">{{ $acc->name ?: $typeOptions[$acc->type] }}</div>
                        @if($acc->type === 'karta' && $acc->expiry_date)
                        <div class="acc-sub">Amal qilish muddati: {{ $acc->expiry_date }}</div>
                        @endif
                    </div>
                    @if($acc->bank_name)
                    <span class="acc-badge">{{ $acc->bank_name }}</span>
                    @endif
                </div>

                @if($acc->type === 'karta' && $acc->card_number)
                <div class="acc-num">{{ $acc->card_number }}</div>
                @elseif($acc->type === 'bank' && $acc->account_number)
                <div class="acc-num" style="font-size:12px">{{ $acc->account_number }}</div>
                @endif

                <div class="acc-bottom">
                    <div>
                        <div class="acc-balance-lbl">Balans</div>
                        <div class="acc-balance">{{ number_format($acc->balance, 0, '.', ' ') }} so'm</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach

    {{-- ── Hisob qo'shish/tahrirlash oynasi ── --}}
    @if($showAccountModal)
    <div class="bx-modal-ov" wire:click.self="closeAccountModal">
        <div class="bx-modal">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
                <div style="font-size:16px;font-weight:800">{{ $editAccountId ? 'Hisobni tahrirlash' : 'Yangi hisob qo\'shish' }}</div>
                <button wire:click="closeAccountModal" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;line-height:1">×</button>
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
                <label for="bx-fav" style="font-size:12px;color:#374151;cursor:pointer">Sevimli sifatida belgilash</label>
            </div>

            <div style="display:flex;gap:10px">
                <button wire:click="closeAccountModal" style="flex:1;padding:11px;border-radius:9px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-weight:700;font-size:13px;cursor:pointer">Bekor</button>
                <button wire:click="saveAccount" style="flex:1;padding:11px;border-radius:9px;border:none;background:#16a34a;color:#fff;font-weight:700;font-size:13px;cursor:pointer">Saqlash</button>
            </div>
        </div>
    </div>
    @endif

</div>
</x-filament-panels::page>
