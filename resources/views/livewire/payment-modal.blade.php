<div>
@if($showPaymentModal)
@php $payProj = \App\Models\Project::with('payments')->find($paymentProjectId); @endphp
<div style="position:fixed;inset:0;z-index:1450;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5)">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:920px;max-height:92vh;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);display:flex;flex-direction:column" wire:click.stop>
    <div style="overflow-y:auto;padding:28px;flex:1;min-height:0">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700;color:#111827 !important;margin:0">To'lov qo'shish</h3>
            <button wire:click="closePaymentModal" style="border:none;background:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">×</button>
        </div>

        @if($payProj)
        @php $payAccOpts = $paymentAccounts->where('type', $paymentMethod); @endphp
        {{-- Project summary (vertikal bloklar, urg'u berilgan) --}}
        <div style="background:#f9fafb;border-radius:10px;padding:18px;margin-bottom:20px">
            <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:14px">{{ $payProj->owner_name }}</div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
                <div style="background:#fff;border:1px solid #eef2f7;border-radius:10px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.03em;margin-bottom:5px">Umumiy summa</div>
                    <div style="font-size:18px;font-weight:600;color:#111827;line-height:1.2">{{ number_format($payProj->total_price, 0, '.', ' ') }}<span style="font-size:11px;font-weight:500;color:#9ca3af !important"> so'm</span></div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:600;color:#15803d;text-transform:uppercase;letter-spacing:.03em;margin-bottom:5px">To'langan</div>
                    <div style="font-size:18px;font-weight:600;color:#16a34a;line-height:1.2">{{ number_format($payProj->paid_amount, 0, '.', ' ') }}<span style="font-size:11px;font-weight:500;color:#4ade80 !important"> so'm ({{ $payProj->payment_percent }}%)</span></div>
                </div>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px">
                    <div style="font-size:11px;font-weight:600;color:#b91c1c;text-transform:uppercase;letter-spacing:.03em;margin-bottom:5px">Qoldiq</div>
                    <div style="font-size:18px;font-weight:600;color:#dc2626;line-height:1.2">{{ number_format($payProj->remaining_amount, 0, '.', ' ') }}<span style="font-size:11px;font-weight:500;color:#f87171 !important"> so'm</span></div>
                </div>
            </div>

            <div style="background:#e5e7eb;border-radius:4px;height:6px;overflow:hidden">
                <div style="background:#16a34a;height:100%;width:{{ $payProj->payment_percent }}%;border-radius:4px"></div>
            </div>
        </div>

        {{-- Form --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Xizmat tanlash --}}
            @if($payProj->services->count())
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151 !important;display:block;margin-bottom:8px">Qaysi xizmat uchun to'lov?</label>
                <div style="display:flex;flex-direction:column;gap:6px">
                    @foreach($payProj->services as $svc)
                    @php
                        $svcPaid = \App\Services\EmployeePayableService::paidAmountForService($svc, $payProj);
                        $svcPct  = $svc->final_price > 0 ? min(100, (int) round($svcPaid / $svc->final_price * 100)) : 0;
                    @endphp
                    <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;transition:border-color .15s"
                         :style="$wire.paymentSelectedServices.includes('{{ $svc->service_name }}') ? 'border-color:#2563eb;background:#eff6ff' : ''">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#111827 !important">
                            <input type="checkbox"
                                   wire:model.live="paymentSelectedServices"
                                   value="{{ $svc->service_name }}"
                                   style="width:15px;height:15px;cursor:pointer;accent-color:#2563eb">
                            <span style="font-weight:600;color:#111827 !important">{{ \App\Models\Project::serviceOptions()[$svc->service_name] ?? $svc->service_name }}</span>
                            <span style="margin-left:auto;font-size:12px;color:#6b7280 !important">{{ number_format($svc->final_price, 0, '.', ' ') }} so'm</span>
                            <span style="font-size:12px;font-weight:700;color:{{ $svcPct >= 100 ? '#16a34a' : ($svcPct > 0 ? '#2563eb' : '#9ca3af') }} !important">
                                {{ number_format($svcPaid, 0, '.', ' ') }} so'm ({{ $svcPct }}%)
                            </span>
                        </label>
                        @if(auth()->user()?->isAdmin() || auth()->user()?->isMenejer())
                        <div style="margin-top:6px">
                            <button type="button"
                                    wire:click.stop="openServicePrice({{ $svc->id }})"
                                    onclick="event.stopPropagation()"
                                    style="font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #c7d2fe;background:#eef2ff;color:#4338ca;cursor:pointer;font-weight:600">
                                🔧 Joriy narxni o'rnatish (PIN)
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @error('paymentSelectedServices')<div style="font-size:11px;color:#dc2626 !important;margin-top:6px">{{ $message }}</div>@enderror
            </div>
            @endif

            {{-- Chegirma (tanlangan xizmat(lar)ga, admin/menejer uchun) — tugma bosilsa ochiladi --}}
            @if(auth()->user()?->isAdmin() || auth()->user()?->isMenejer())
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:1.5px solid #fde68a;background:#fefce8;color:#92400e;font-size:12px;font-weight:700;cursor:pointer">
                    🏷️ Chegirma
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" :style="open ? 'transform:rotate(180deg)' : ''" style="transition:transform .2s"><path d="M6 9l6 6 6-6"/></svg>
                </button>
            <div x-show="open" x-collapse x-cloak style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;padding:14px 16px;margin-top:8px">
                <label style="font-size:12px;font-weight:600;color:#92400e !important;display:block;margin-bottom:8px">Tanlangan xizmat(lar)ga qo'llanadi</label>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:8px">
                    <label style="display:flex;align-items:center;gap:5px;background:#fff;border:1.5px solid {{ $payDiscountCategory==='nogiron' ? '#f59e0b' : '#e5e7eb' }};border-radius:7px;padding:6px 8px;cursor:pointer;font-size:11px;color:#111827 !important">
                        <input type="radio" wire:model.live="payDiscountCategory" value="nogiron" style="accent-color:#f59e0b">
                        Guruh nogironlar 15%
                    </label>
                    <label style="display:flex;align-items:center;gap:5px;background:#fff;border:1.5px solid {{ $payDiscountCategory==='pensioner' ? '#f59e0b' : '#e5e7eb' }};border-radius:7px;padding:6px 8px;cursor:pointer;font-size:11px;color:#111827 !important">
                        <input type="radio" wire:model.live="payDiscountCategory" value="pensioner" style="accent-color:#f59e0b">
                        Pensionerlar 10%
                    </label>
                    <label style="display:flex;align-items:center;gap:5px;background:#fff;border:1.5px solid {{ $payDiscountCategory==='ijtimoiy' ? '#f59e0b' : '#e5e7eb' }};border-radius:7px;padding:6px 8px;cursor:pointer;font-size:11px;color:#111827 !important">
                        <input type="radio" wire:model.live="payDiscountCategory" value="ijtimoiy" style="accent-color:#f59e0b">
                        Ijtimoiy ximoya 10%
                    </label>
                    <label style="display:flex;align-items:center;gap:5px;background:#fff;border:1.5px solid {{ $payDiscountCategory==='boshqa' ? '#f59e0b' : '#e5e7eb' }};border-radius:7px;padding:6px 8px;cursor:pointer;font-size:11px;color:#111827 !important">
                        <input type="radio" wire:model.live="payDiscountCategory" value="boshqa" style="accent-color:#f59e0b">
                        Boshqalar
                    </label>
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    @if($payDiscountCategory === 'boshqa')
                    <input wire:model="payDiscountCustomPct" type="number" min="0" max="100" step="0.1"
                           placeholder="Foiz, masalan: 5"
                           style="flex:1;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;outline:none;box-sizing:border-box;background:#fff">
                    <span style="font-size:12px;color:#92400e !important">%</span>
                    @endif
                    <button type="button" wire:click="applyPaymentDiscount" @disabled(!$payDiscountCategory)
                            style="margin-left:auto;padding:8px 18px;border-radius:7px;border:none;background:{{ $payDiscountCategory ? '#f59e0b' : '#fde68a' }};color:#fff;font-size:12px;font-weight:700;cursor:{{ $payDiscountCategory ? 'pointer' : 'not-allowed' }};white-space:nowrap">
                        ✓ Bajarish
                    </button>
                </div>
                <div style="font-size:10px;color:#92400e;margin-top:6px;opacity:.8">Hech qaysi xizmat belgilanmagan bo'lsa — barcha xizmatlarga qo'llanadi</div>
            </div>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151 !important;display:block;margin-bottom:6px">Summa (so'm) *</label>
                    <input wire:model.live="paymentAmount" type="number" min="1"
                           style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                           placeholder="Masalan: 350000"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
                    @error('paymentAmount')<span style="font-size:11px;color:#dc2626 !important">{{ $message }}</span>@enderror
                    @if($paymentAmount && $payProj->total_price > 0)
                    @php $pct = min(100, round((float)$paymentAmount / (float)$payProj->total_price * 100)); @endphp
                    <div style="font-size:11px;color:#6b7280;margin-top:4px">
                        ≈ {{ $pct }}% (jami: {{ number_format($payProj->paid_amount + (float)$paymentAmount, 0, '.', ' ') }} so'm)
                    </div>
                    @endif
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151 !important;display:block;margin-bottom:6px">To'lov usuli</label>
                    <select wire:model.live="paymentMethod"
                            style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                        <option value="naqd">Naqd pul</option>
                        <option value="bank">Bank o'tkazma</option>
                        <option value="karta">Karta</option>
                    </select>
                </div>
            </div>

            <button type="button" wire:click="savePayment(true)"
                    style="width:100%;padding:12px;border-radius:8px;border:none;background:#2563eb;color:#fff;font-size:14px;font-weight:700;cursor:pointer">
                💾 To'lash
            </button>
            <div style="font-size:10px;color:#9ca3af;margin-top:-8px">Bu tugma barcha kiritilgan o'zgarishlarni saqlaydi, oyna yopilmaydi</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151 !important;display:block;margin-bottom:6px">Sana *</label>
                    <input wire:model="paymentDate" type="date"
                           style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                    @error('paymentDate')<span style="font-size:11px;color:#dc2626 !important">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151 !important;display:block;margin-bottom:6px">Qaysi hisobga tushdi? *</label>
                    @if($payAccOpts->count() > 0)
                    <select wire:model="paymentAccountId"
                            style="width:100%;padding:10px 12px;border:2px solid {{ $errors->has('paymentAccountId') ? '#dc2626' : '#e5e7eb' }};border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                        <option value="">— tanlanmagan —</option>
                        @foreach($payAccOpts as $acc)
                        <option value="{{ $acc->id }}">
                            {{ $acc->name ?: ucfirst($acc->type) }}
                            @if($acc->type === 'karta' && $acc->card_number) — {{ $acc->card_number }}@endif
                            @if($acc->type === 'bank' && $acc->account_number) — {{ $acc->account_number }}@endif
                        </option>
                        @endforeach
                    </select>
                    @error('paymentAccountId')<span style="font-size:11px;color:#dc2626 !important">{{ $message }}</span>@enderror
                    @else
                    <div style="font-size:12px;color:#9ca3af;padding:10px 12px;background:#f9fafb;border:2px solid #e5e7eb;border-radius:8px">Bu usul uchun hisob yo'q — avval Buxgalteriyada qo'shing</div>
                    @endif
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151 !important;display:block;margin-bottom:6px">Izoh</label>
                <textarea wire:model="paymentNote" rows="2"
                          style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;resize:none;box-sizing:border-box"
                          placeholder="Ixtiyoriy..."></textarea>
            </div>
            {{-- Xizmat mas'ullari --}}
            <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px">
                <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <svg width="13" height="13" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Xizmat mas'ullari
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <div>
                        <label style="font-size:11px;color:#6b7280 !important;display:block;margin-bottom:4px;font-weight:500">Toposyomka</label>
                        <select wire:model="paymentToposyomkaUserId"
                                style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;background:#fff;box-sizing:border-box;color:#111827">
                            <option value="">— Tanlang —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;color:#6b7280 !important;display:block;margin-bottom:4px;font-weight:500">Eskiz loyiha</label>
                        <select wire:model="paymentEskizUserId"
                                style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;background:#fff;box-sizing:border-box;color:#111827">
                            <option value="">— Tanlang —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @if($paymentFromQueue)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px">
                <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                <span style="font-size:13px;font-weight:500;color:#166534 !important">To'lov saqlanadi va loyiha <strong>To'langan</strong> bo'limiga o'tkaziladi</span>
            </div>
            @elseif($payProj && $payProj->status === 'tolov_jarayonida')
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f5f3ff;border-radius:8px;cursor:pointer;border:1px solid #ddd6fe">
                <input type="checkbox" wire:model="paymentMoveToEskiz" style="width:16px;height:16px;accent-color:#7c3aed">
                <span style="font-size:13px;font-weight:500;color:#5b21b6 !important">To'lovdan keyin → <strong>Toposyomka</strong> bo'limiga o'tkazish</span>
            </label>
            @endif
        </div>
        @endif

        {{-- Existing payments list --}}
        @if($payProj && $payProj->payments->count() > 0)
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px 14px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px">
                    Kiritilgan to'lovlar
                </div>
                <button onclick="event.stopPropagation()"
                        wire:click.stop="openPaymentLogs({{ $payProj->id }})"
                        style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;white-space:nowrap">
                    🕐 Tarix
                </button>
            </div>
            @foreach($payProj->payments->sortByDesc('payment_date') as $pmt)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                <div style="font-size:12px;color:#374151">
                    <span style="font-weight:600;color:#111827 !important">{{ number_format((float)$pmt->amount, 0, '.', ' ') }} so'm</span>
                    <span style="color:#9ca3af !important;margin-left:6px">{{ $pmt->payment_date?->format('d/m/Y') }}</span>
                    @if($pmt->createdBy)
                    <span style="color:#9ca3af !important;margin-left:4px">· {{ $pmt->createdBy->name }}</span>
                    @endif
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    <button onclick="event.stopPropagation();bhOpenChek({{ $pmt->id }})"
                            style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#16a34a;cursor:pointer;white-space:nowrap">
                        🧾 Chek
                    </button>
                    <button onclick="event.stopPropagation()"
                            wire:click.stop="openEditPayment({{ $pmt->id }})"
                            style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#2563eb;cursor:pointer;white-space:nowrap">
                        ✏️ Tahrirlash
                    </button>
                    <button onclick="event.stopPropagation()"
                            wire:click.stop="openDeletePayment({{ $pmt->id }})"
                            style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #fecaca;background:#fff;color:#dc2626;cursor:pointer;white-space:nowrap">
                        🗑 O'chirish
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Amount confirm warning --}}
        @if($paymentAmountConfirm)
        <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:8px;padding:14px 16px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                <span style="font-size:18px">⚠️</span>
                <span style="font-size:13px;font-weight:600;color:#92400e !important">Summa kiritilmadi!</span>
            </div>
            <p style="font-size:13px;color:#78350f !important;margin:0 0 12px">Summasisiz faqat hodim biriktirma ma'lumotlari saqlanadi. Davom etasizmi?</p>
            <div style="display:flex;gap:8px">
                <button wire:click="savePayment"
                        style="flex:1;padding:9px;border-radius:7px;border:none;background:#16a34a;color:#fff;font-size:13px;font-weight:600;cursor:pointer">
                    Ha, saqlash
                </button>
                <button wire:click="cancelPaymentAmountConfirm"
                        style="flex:1;padding:9px;border-radius:7px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:13px;cursor:pointer">
                    Yo'q, qaytish
                </button>
            </div>
        </div>
        @endif

        <div style="display:flex;gap:10px;margin:20px -28px -28px;position:sticky;bottom:-28px;background:#fff;padding:14px 28px 20px;border-top:1px solid #eef2f7;border-radius:0 0 16px 16px;z-index:2">
            <button wire:click="closePaymentModal"
                    style="flex:1;padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            @if(!$paymentAmountConfirm)
            <button wire:click="savePayment"
                    style="flex:2;padding:11px;border-radius:8px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Saqlash
            </button>
            @endif
        </div>
    </div>
    </div>
</div>
@endif

{{-- TAHRIRLASH MODAL (to'lov summasi) — To'lov oynasi (z-index:1450) ustidan
     ochilishi mumkin bo'lgani uchun undan balandroq bo'lishi shart, aks holda
     orqada qolib, tugmalari bosilmay qoladi. --}}
@if($showEditPaymentModal)
@php $editPmt = \App\Models\Payment::with('project.services')->find($editPaymentId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1500;display:flex;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:380px;max-height:90vh;overflow-y:auto;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span style="font-size:15px;font-weight:700;color:#111827">Summani tahrirlash</span>
            </div>
            <button wire:click="closeEditPayment" style="background:none;border:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">✕</button>
        </div>
        @if($editPmt)
        <div style="background:#f9fafb;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#374151">
            <strong>{{ $editPmt->project?->owner_name }}</strong>
            <span style="color:#9ca3af;margin-left:8px">{{ $editPmt->payment_date?->format('d/m/Y') }}</span>
        </div>
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:12px;color:#c2410c">Hozirgi summa:</span>
            <span style="font-size:13px;font-weight:700;color:#c2410c">{{ number_format((float)$editPmt->amount, 0, '.', ' ') }} so'm</span>
        </div>
        @endif
        <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Yangi summa (so'm)</label>
            <input wire:model.live="editPaymentAmount" type="number" min="1"
                   style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                   placeholder="Yangi summa kiriting"
                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
            @error('editPaymentAmount')<span style="font-size:11px;color:#dc2626 !important">{{ $message }}</span>@enderror
        </div>
        <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">To'lov sanasi</label>
            <input wire:model.live="editPaymentDate" type="date"
                   style="width:100%;padding:10px 12px;border:2px solid {{ $errors->has('editPaymentDate') ? '#dc2626' : '#e5e7eb' }};border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
            @error('editPaymentDate')<span style="font-size:11px;color:#dc2626 !important">{{ $message }}</span>@enderror
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
            <div>
                <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">To'lov usuli</label>
                <select wire:model.live="editPaymentMethod"
                        style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                    <option value="naqd">Naqd pul</option>
                    <option value="bank">Bank o'tkazma</option>
                    <option value="karta">Karta</option>
                </select>
            </div>
            <div>
                @php $editAccOpts = $paymentAccounts->where('type', $editPaymentMethod); @endphp
                <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Qaysi hisobga tushdi? *</label>
                @if($editAccOpts->count() > 0)
                <select wire:model="editPaymentAccountId"
                        style="width:100%;padding:10px 12px;border:2px solid {{ $errors->has('editPaymentAccountId') ? '#dc2626' : '#e5e7eb' }};border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                    <option value="">— tanlanmagan —</option>
                    @foreach($editAccOpts as $acc)
                    <option value="{{ $acc->id }}">
                        {{ $acc->name ?: ucfirst($acc->type) }}
                        @if($acc->type === 'karta' && $acc->card_number) — {{ $acc->card_number }}@endif
                        @if($acc->type === 'bank' && $acc->account_number) — {{ $acc->account_number }}@endif
                    </option>
                    @endforeach
                </select>
                @error('editPaymentAccountId')<span style="font-size:11px;color:#dc2626 !important">{{ $message }}</span>@enderror
                @else
                <div style="font-size:11px;color:#9ca3af;padding:10px 12px;background:#f9fafb;border:2px solid #e5e7eb;border-radius:8px">Bu usul uchun hisob yo'q</div>
                @endif
            </div>
        </div>
        @if($editPmt && is_null($editPmt->account_id))
        <div style="display:flex;align-items:center;gap:8px;padding:9px 12px;background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;margin-bottom:14px">
            <span style="font-size:14px">⚠️</span>
            <span style="font-size:11.5px;color:#92400e !important">Bu to'lov hozircha hech qaysi hisobga bog'lanmagan — Buxgalteriyada ko'rinmayapti. Yuqoridan hisob tanlab, saqlang.</span>
        </div>
        @endif
        @if($editPmt && $editPmt->project && $editPmt->project->services->count())
        <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Qaysi xizmat uchun to'lov?</label>
            @if(empty($editPaymentServices))
            <div style="display:flex;align-items:center;gap:6px;padding:8px 12px;margin-bottom:8px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px">
                <span style="font-size:12px">⚠️</span>
                <span style="font-size:11.5px;color:#b91c1c !important">Bu to'lov hech qaysi xizmatga bog'lanmagan — komissiya hisobida ko'rinmaydi.</span>
            </div>
            @endif
            <div style="display:flex;flex-direction:column;gap:6px">
                @foreach($editPmt->project->services as $esvc)
                @php
                    $esvcPaid = \App\Services\EmployeePayableService::paidAmountForService($esvc, $editPmt->project);
                    $esvcPct  = $esvc->final_price > 0 ? min(100, (int) round($esvcPaid / $esvc->final_price * 100)) : 0;
                @endphp
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#111827 !important;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px">
                    <input type="checkbox"
                           wire:model.live="editPaymentServices"
                           value="{{ $esvc->service_name }}"
                           style="width:15px;height:15px;cursor:pointer;accent-color:#2563eb">
                    <span style="font-weight:600;color:#111827 !important">{{ \App\Models\Project::serviceOptions()[$esvc->service_name] ?? $esvc->service_name }}</span>
                    <span style="margin-left:auto;font-size:12px;color:#6b7280 !important">{{ number_format($esvc->final_price, 0, '.', ' ') }} so'm</span>
                    <span style="font-size:12px;font-weight:700;color:{{ $esvcPct >= 100 ? '#16a34a' : ($esvcPct > 0 ? '#2563eb' : '#9ca3af') }} !important">
                        {{ number_format($esvcPaid, 0, '.', ' ') }} so'm ({{ $esvcPct }}%)
                    </span>
                </label>
                @endforeach
            </div>
        </div>
        @endif
        <div style="margin-bottom:14px">
            @if(\App\Services\TelegramOtpService::otpRequired())
            <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Telegram kodi</label>
            @if(!\App\Services\TelegramOtpService::isLinked(auth()->user()))
            <div style="font-size:13px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-bottom:8px">
                Avval <a href="{{ route('filament.admin.pages.telegram-settings') }}" style="color:#4338ca;font-weight:600">Telegramingizni bog'lang</a> — shundan keyin tasdiqlash kodi shu yerga keladi.
            </div>
            @endif
            <input type="password" wire:model="editPaymentPin"
                   wire:keydown.enter="saveEditPayment"
                   style="width:100%;border:1.5px solid {{ $editPaymentPinError ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:10px 14px;font-size:18px;letter-spacing:6px;text-align:center;outline:none;margin-bottom:8px;box-sizing:border-box"
                   placeholder="······" maxlength="6">
            @if($editPaymentPinError)
            <div style="font-size:12px;color:#ef4444;margin-bottom:10px">❌ Noto'g'ri yoki eskirgan kod</div>
            @endif
            @else
            <div style="font-size:12px;color:#9ca3af">Tasdiqlash kodi vaqtincha o'chirilgan (Telegram ishlamayapti)</div>
            @endif
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <button wire:click="closeEditPayment"
                    style="padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            <button wire:click="saveEditPayment"
                    style="padding:11px;border-radius:8px;border:none;background:#2563eb;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Saqlash
            </button>
        </div>
    </div>
</div>
@endif

{{-- TO'LOVNI O'CHIRISH — PIN MODAL --}}
@if($showDeletePaymentModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1500;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="closeDeletePayment">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:320px;box-shadow:0 25px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:6px">🔐 Telegram kodi</div>
        @if(\App\Services\TelegramOtpService::otpRequired())
        @if(!\App\Services\TelegramOtpService::isLinked(auth()->user()))
        <div style="font-size:13px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-bottom:12px">
            Avval <a href="{{ route('filament.admin.pages.telegram-settings') }}" style="color:#4338ca;font-weight:600">Telegramingizni bog'lang</a> — shundan keyin tasdiqlash kodi shu yerga keladi.
        </div>
        @else
        <div style="font-size:13px;color:#6b7280;margin-bottom:16px">To'lovni butunlay o'chirish uchun Telegramingizga yuborilgan kodni kiriting</div>
        @endif
        <input type="password" wire:model="deletePaymentPin"
               wire:keydown.enter="confirmDeletePayment"
               style="width:100%;border:1.5px solid {{ $deletePaymentPinError ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:10px 14px;font-size:18px;letter-spacing:6px;text-align:center;outline:none;margin-bottom:8px"
               placeholder="······" autofocus maxlength="6">
        @if($deletePaymentPinError)
        <div style="font-size:12px;color:#ef4444;margin-bottom:10px">❌ Noto'g'ri yoki eskirgan kod</div>
        @endif
        @else
        <div style="font-size:13px;color:#9ca3af;margin-bottom:12px">Tasdiqlash kodi vaqtincha o'chirilgan (Telegram ishlamayapti)</div>
        @endif
        <div style="display:flex;gap:8px;margin-top:12px">
            <button wire:click="closeDeletePayment"
                    style="flex:1;padding:10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:13px;cursor:pointer">
                Bekor
            </button>
            <button wire:click="confirmDeletePayment"
                    style="flex:1;padding:10px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-size:13px;font-weight:600;cursor:pointer">
                O'chirish
            </button>
        </div>
    </div>
</div>
@endif

{{-- XIZMAT NARXINI O'RNATISH — PIN MODAL --}}
@if($showServicePriceModal)
@php $spSvc = \App\Models\ProjectService::find($servicePriceId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1500;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="closeServicePrice">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:340px;box-shadow:0 25px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:6px">🔧 Joriy narxni o'rnatish</div>
        @if($spSvc)
        <div style="font-size:13px;color:#6b7280;margin-bottom:14px">
            {{ \App\Models\Project::serviceOptions()[$spSvc->service_name] ?? $spSvc->service_name }} —
            hozirgi: <strong>{{ number_format((float)$spSvc->final_price, 0, '.', ' ') }} so'm</strong>
        </div>
        @endif
        <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Yangi narx (so'm)</label>
        <input wire:model="servicePriceValue" type="number" min="0"
               style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 14px;font-size:15px;outline:none;margin-bottom:12px;box-sizing:border-box"
               placeholder="Masalan: 4078800">
        @if(\App\Services\TelegramOtpService::otpRequired())
        <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Telegram kodi</label>
        @if(!\App\Services\TelegramOtpService::isLinked(auth()->user()))
        <div style="font-size:13px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-bottom:8px">
            Avval <a href="{{ route('filament.admin.pages.telegram-settings') }}" style="color:#4338ca;font-weight:600">Telegramingizni bog'lang</a>.
        </div>
        @endif
        <input type="password" wire:model="servicePricePin"
               wire:keydown.enter="saveServicePrice"
               style="width:100%;border:1.5px solid {{ $servicePricePinError ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:10px 14px;font-size:18px;letter-spacing:6px;text-align:center;outline:none;margin-bottom:8px;box-sizing:border-box"
               placeholder="······" maxlength="6">
        @if($servicePricePinError)
        <div style="font-size:12px;color:#ef4444;margin-bottom:10px">❌ Noto'g'ri yoki eskirgan kod</div>
        @endif
        @else
        <div style="font-size:12px;color:#9ca3af;margin-bottom:8px">Tasdiqlash kodi vaqtincha o'chirilgan (Telegram ishlamayapti)</div>
        @endif
        <div style="display:flex;gap:8px;margin-top:12px">
            <button wire:click="closeServicePrice"
                    style="flex:1;padding:10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:13px;cursor:pointer">
                Bekor
            </button>
            <button wire:click="saveServicePrice"
                    style="flex:1;padding:10px;border-radius:8px;border:none;background:#4338ca;color:#fff;font-size:13px;font-weight:600;cursor:pointer">
                Saqlash
            </button>
        </div>
    </div>
</div>
@endif

{{-- TO'LOVLAR TARIXI MODAL --}}
@if($showPaymentLogsModal)
@php $plProject = \App\Models\Project::with('paymentLogs.user')->find($paymentLogsProjectId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1500;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="closePaymentLogsModal">
    <div style="background:#fff;border-radius:14px;padding:20px 24px;max-width:520px;width:100%;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:15px;font-weight:700;color:#111827">To'lovlar tarixi — {{ $plProject?->owner_name }}</span>
            <button wire:click="closePaymentLogsModal" style="background:none;border:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">✕</button>
        </div>
        @if($plProject)
        @include('filament.modals.payment-logs', ['project' => $plProject])
        @endif
    </div>
</div>
@endif
</div>
