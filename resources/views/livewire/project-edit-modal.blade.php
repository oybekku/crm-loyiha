<div>
@if($showEditInfoModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1400;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="closeEditInfoModal">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:760px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>
        {{-- STICKY SARLAVHA --}}
        <div style="flex-shrink:0;padding:18px 26px 14px;border-bottom:1px solid #eef2f7">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <span style="font-size:17px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px">✏️ Loyiha ma'lumotini tahrirlash</span>
                <button wire:click="closeEditInfoModal" style="background:#f3f4f6;border:none;border-radius:8px;width:30px;height:30px;cursor:pointer;color:#6b7280;font-size:15px;line-height:1;transition:background .15s" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">✕</button>
            </div>

            {{-- Amal tugmalari --}}
            <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center">
            @if(auth()->user()?->canSeeAllProjects())
            <div x-data="{open:false}" style="position:relative" @click.outside="open=false">
                <button type="button" @click="open=!open" style="padding:6px 11px;border-radius:7px;border:1px solid #93c5fd;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px">→ O'tkazish ▾</button>
                <div x-show="open" x-cloak style="position:absolute;top:100%;left:0;margin-top:4px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 12px 30px rgba(0,0,0,.15);z-index:20;min-width:170px;max-height:240px;overflow:auto">
                    @foreach($statuses as $sk => $st)
                    @if($sk !== $ei_status)
                    <button type="button" wire:click="eiMove('{{ $sk }}')" @click="open=false" style="display:block;width:100%;text-align:left;padding:8px 12px;border:none;background:#fff;cursor:pointer;font-size:12px;color:#374151">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $st['color'] }};margin-right:6px;vertical-align:middle"></span>{{ $st['label'] }}
                    </button>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if(auth()->user()?->isHisobchi() || auth()->user()?->canSeeAllProjects())
            <button type="button" wire:click="eiGoPayment" style="padding:6px 11px;border-radius:7px;border:1px solid #86efac;background:#f0fdf4;color:#16a34a;font-size:12px;font-weight:600;cursor:pointer">💳 To'lov</button>
            @endif

            @if(auth()->user()?->canSeeAllProjects())
                @if($ei_paymentRequested)
                <button type="button" wire:click="eiCancelRequest" style="padding:6px 11px;border-radius:7px;border:1px solid #fcd34d;background:#fef3c7;color:#b45309;font-size:12px;font-weight:600;cursor:pointer">🕐 Navbatda</button>
                @else
                <button type="button" wire:click="eiRequestPayment" style="padding:6px 11px;border-radius:7px;border:1px solid #fcd34d;background:#fef3c7;color:#b45309;font-size:12px;font-weight:600;cursor:pointer">📨 To'lovga</button>
                @endif
            @endif

            @if(!auth()->user()?->isHisobchi())
            <button type="button" wire:click="eiGoRoute" style="padding:6px 11px;border-radius:7px;border:1px solid #93c5fd;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:600;cursor:pointer">✈ Yuborish</button>
            @endif

            <a href="{{ route('print.project.ariza', $editInfoId) }}" target="_blank" style="padding:6px 11px;border-radius:7px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px">🖨 Ariza</a>

            <a href="{{ route('print.project.chegirma', $editInfoId) }}" target="_blank" style="padding:6px 11px;border-radius:7px;border:1px solid #fcd34d;background:#fffbeb;color:#b45309;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px">🎟 Chegirma</a>

            @if(auth()->user()?->isAdmin() || auth()->user()?->isMenejer())
                @if($ei_status === 'tugallangan')
                <button type="button" wire:click="eiMarkUncomplete" wire:confirm="Loyihani jarayonga qaytarmoqchimisiz?" style="padding:6px 11px;border-radius:7px;border:1px solid #86efac;background:#dcfce7;color:#16a34a;font-size:12px;font-weight:700;cursor:pointer">✓ Tugallandi</button>
                @else
                <button type="button" wire:click="eiMarkComplete" wire:confirm="Loyihani tugallangan deb belgilaysizmi?" style="padding:6px 11px;border-radius:7px;border:1px solid #d1d5db;background:#f3f4f6;color:#6b7280;font-size:12px;font-weight:600;cursor:pointer">🕐 Tekshirildi</button>
                @endif
            @endif

            @if(!auth()->user()?->isHisobchi())
            <button type="button" wire:click="eiGoAssign" style="padding:6px 11px;border-radius:7px;border:1px solid #e5e7eb;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">👤 Hodim</button>
            @endif
            </div>
        </div>{{-- /sticky sarlavha --}}

        {{-- SCROLL MARKAZ --}}
        <div style="flex:1;overflow-y:auto;padding:20px 26px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Egasining ismi *</label>
                <input wire:model="ei_owner" type="text" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                @error('ei_owner')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Loyiha nomi</label>
                <input wire:model="ei_title" type="text" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
            </div>
        </div>

        <div style="margin-bottom:12px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Manzil *</label>
            <input wire:model="ei_address" type="text" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
            @error('ei_address')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
        </div>

        <div style="margin-bottom:12px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Obloshka manzili
                <span style="font-weight:400;color:#9ca3af">— muqova chop etish uchun (bo'sh bo'lsa yuqoridagi manzil olinadi)</span>
            </label>
            <textarea wire:model="ei_oblozhka" rows="2" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;resize:vertical"></textarea>
        </div>

        {{-- Xizmatlar va to'lovlar (Koordinatalar yuqorisida) --}}
        @if(count($ei_services) > 0)
        <div style="margin-bottom:16px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden">
            <div style="background:#f8fafc;padding:9px 14px;font-size:13px;font-weight:700;color:#111827;border-bottom:1px solid #e5e7eb">💰 Xizmatlar va to'lovlar</div>
            <div style="padding:6px 14px">
                @foreach($ei_services as $svc)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#374151;{{ $svc['completed'] ? 'text-decoration:line-through;opacity:.6' : '' }}">{{ $svc['label'] }}</div>
                        @if(!empty($svc['employee']))
                        <div style="font-size:11px;color:#6366f1;margin-top:2px">👷 {{ $svc['employee'] }}</div>
                        @else
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px">👤 mas'ul biriktirilmagan</div>
                        @endif
                        @if(auth()->user()?->isAdmin())
                        <button type="button" wire:click="eiToggleService({{ $svc['id'] }})"
                                title="{{ $svc['completed'] ? 'Tugallanmagan deb belgilash' : 'Tugallangan deb belgilash' }}"
                                style="margin-top:6px;display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;border:1px solid {{ $svc['completed'] ? '#86efac' : '#d1d5db' }};background:{{ $svc['completed'] ? '#f0fdf4' : '#f9fafb' }};color:{{ $svc['completed'] ? '#16a34a' : '#9ca3af' }};font-size:11px;font-weight:600;cursor:pointer">
                            @if($svc['completed'])
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Tugallandi
                            @else
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> Tugalmagan
                            @endif
                        </button>
                        @endif
                    </div>
                    <div style="text-align:right">
                        <span style="font-size:12px;color:#6b7280">{{ number_format($svc['price'], 0, '.', ' ') }} so'm</span>
                        <span style="font-size:12px;font-weight:700;color:{{ $svc['pct'] >= 100 ? '#16a34a' : ($svc['pct'] > 0 ? '#2563eb' : '#9ca3af') }};margin-left:8px">
                            {{ number_format($svc['paid'], 0, '.', ' ') }} so'm ({{ $svc['pct'] }}%)
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            <div style="background:#fffbeb;padding:6px 14px;font-size:10px;color:#92400e;border-top:1px solid #fef3c7">Narx tahrirlash uchun "To'liq sahifa" → Xizmatlar panelidan foydalaning.</div>
        </div>
        @endif

        <div style="margin-bottom:12px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">📍 Koordinatalar <span style="font-weight:400;color:#9ca3af">(kenglik, uzunlik)</span></label>
            <div style="display:flex;gap:8px">
                <input wire:model="ei_coords" id="ei-coords-input" type="text" placeholder="41.299800, 69.240100" style="flex:1;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                <button type="button"
                        onclick="(function(btn){var inp=document.getElementById('ei-coords-input');var v=(inp&&inp.value||'').trim();if(!v){return;}var done=function(){var old=btn.getAttribute('data-lbl')||btn.innerHTML;btn.setAttribute('data-lbl',old);btn.innerHTML='✓ Nusxalandi';btn.style.color='#16a34a';setTimeout(function(){btn.innerHTML=old;btn.style.color='#374151';},1200);};var ta=document.createElement('textarea');ta.value=v;ta.style.position='fixed';ta.style.top='-1000px';ta.style.opacity='0';document.body.appendChild(ta);ta.focus();ta.select();var ok=false;try{ok=document.execCommand('copy');}catch(e){}document.body.removeChild(ta);if(ok){done();return;}if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(v).then(done).catch(function(){});}})(this)"
                        style="padding:9px 14px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;color:#374151;cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap">⧉ Nusxalash</button>
            </div>
        </div>

        <div style="margin-bottom:12px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Telefon raqamlar</label>
            @foreach($ei_phones as $i => $ph)
            <div style="display:flex;gap:6px;margin-bottom:6px">
                <input wire:model="ei_phones.{{ $i }}" type="text" style="flex:1;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none">
                @if(count($ei_phones) > 1)
                <button wire:click="eiRemovePhone({{ $i }})" style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;cursor:pointer;width:38px;font-size:16px">−</button>
                @endif
            </div>
            @endforeach
            <button wire:click="eiAddPhone" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;color:#2563eb;cursor:pointer;padding:6px 12px;font-size:12px;font-weight:600">+ Raqam qo'shish</button>
        </div>

        <div style="margin-bottom:12px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Qo'shimcha ma'lumot</label>
            <textarea wire:model="ei_description" rows="2" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;resize:vertical"></textarea>
        </div>

        <div style="margin-bottom:18px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Kategoriya</label>
            <select wire:model="ei_category" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                <option value="turar">Turar joy</option>
                <option value="noturar">Noturar joy</option>
            </select>
        </div>

        {{-- Hujjatlar --}}
        <div style="margin-bottom:16px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden">
            <div style="background:#f8fafc;padding:9px 14px;font-size:13px;font-weight:700;color:#111827;border-bottom:1px solid #e5e7eb">📎 Hujjatlar</div>
            <div style="padding:10px 14px">
                @if(count($ei_files) > 0)
                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:10px">
                    @foreach($ei_files as $f)
                    <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px">
                        <span style="font-size:16px">{{ $f['icon'] }}</span>
                        <a href="{{ $f['url'] }}" target="_blank" style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#374151;font-size:13px;text-decoration:none">{{ $f['name'] }}</a>
                        <span style="color:#9ca3af;font-size:11px">{{ $f['size'] }}</span>
                        @if(auth()->user()?->isAdmin() && \Illuminate\Support\Str::endsWith(strtolower($f['name']), '.pdf'))
                        <a href="{{ route('pechat.editor', $f['id']) }}" target="_blank" title="Pechat urish" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:5px;color:#2563eb;text-decoration:none;padding:2px 8px;font-size:12px">🖋</a>
                        @endif
                        <button wire:click="eiDeleteFile({{ $f['id'] }})" onclick="return confirm('Faylni o\'chirasizmi?')" style="background:#fef2f2;border:1px solid #fecaca;border-radius:5px;color:#dc2626;cursor:pointer;padding:2px 7px;font-size:11px">🗑</button>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="font-size:12px;color:#9ca3af;margin-bottom:10px">Hali fayl yuklanmagan</div>
                @endif
                <div x-data="{ up:false, prog:0 }"
                     x-on:livewire-upload-start="up=true; prog=0"
                     x-on:livewire-upload-finish="up=false; prog=100"
                     x-on:livewire-upload-error="up=false"
                     x-on:livewire-upload-progress="prog=$event.detail.progress">
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">Yangi fayl yuklash <span style="font-weight:400;color:#9ca3af">— tanlash bilan avtomatik saqlanadi</span></label>
                    <input wire:model="ei_newFiles" type="file" multiple style="width:100%;font-size:12px;padding:8px;border:1.5px dashed #c7d2fe;border-radius:8px;background:#fafbff;box-sizing:border-box;cursor:pointer">
                    <div x-show="up" x-cloak style="margin-top:10px">
                        <div style="height:9px;background:#e5e7eb;border-radius:6px;overflow:hidden">
                            <div style="height:100%;background:linear-gradient(90deg,#3b82f6,#2563eb);border-radius:6px;transition:width .2s" x-bind:style="'width:'+prog+'%'"></div>
                        </div>
                        <div style="font-size:12px;color:#2563eb;margin-top:5px;font-weight:700;display:flex;align-items:center;gap:6px">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Yuklanmoqda... <span x-text="prog+'%'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ➕ Yangi xizmat (ish) qo'shish --}}
        @if(!auth()->user()?->isHisobchi())
        @php
            $existingKeys = collect($ei_services)->pluck('key')->toArray();
            $availSvc = collect(\App\Models\Project::serviceOptions())->reject(fn($l, $k) => in_array($k, $existingKeys));
        @endphp
        <div style="margin-bottom:16px;border:1px dashed #c7d2fe;border-radius:10px;background:#f5f7ff;padding:13px 14px">
            <div style="font-size:13px;font-weight:700;color:#4338ca;margin-bottom:10px">➕ Yangi xizmat (ish) qo'shish</div>
            @if($availSvc->isEmpty())
            <div style="font-size:12px;color:#9ca3af">Barcha xizmat turlari allaqachon qo'shilgan</div>
            @else
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
                <div>
                    <select wire:model="ei_newSvcType" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                        <option value="">— Xizmat turi —</option>
                        @foreach($availSvc as $k => $lbl)<option value="{{ $k }}">{{ $lbl }}</option>@endforeach
                    </select>
                    @error('ei_newSvcType')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
                </div>
                <div>
                    <input wire:model="ei_newSvcPrice" type="number" min="1" placeholder="Narx (so'm)" style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                    @error('ei_newSvcPrice')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
                </div>
            </div>
            <div style="display:flex;gap:8px">
                <select wire:model="ei_newSvcUser" style="flex:1;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                    <option value="">— Mas'ul hodim (ixtiyoriy) —</option>
                    @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                </select>
                <button type="button" wire:click="eiAddService" style="padding:9px 18px;border-radius:8px;border:none;background:#4338ca;color:#fff;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">➕ Qo'shish</button>
            </div>
            <div style="font-size:10px;color:#6366f1;margin-top:7px">⚠️ Yangi ish qo'shilsa loyiha umumiy summasi oshadi.</div>
            @endif
        </div>
        @endif

        <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:8px 12px;margin-bottom:14px;font-size:11px;color:#0369a1">
            ℹ️ Saqlash bosilganda ma'lumot va yangi fayllar saqlanadi. Xizmat narxini o'zgartirish "To'liq sahifa"da.
        </div>
        </div>{{-- /scroll markaz --}}

        {{-- STICKY PASTKI PANEL --}}
        <div style="flex-shrink:0;padding:14px 26px;border-top:1px solid #eef2f7;display:flex;gap:10px;background:#fff">
            <button wire:click="closeEditInfoModal" style="flex:1;padding:11px;border-radius:9px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:600">Bekor</button>
            <a href="/admin/projects/{{ $editInfoId }}/edit" style="padding:11px 16px;border-radius:9px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;text-decoration:none;font-size:13px;font-weight:600;display:flex;align-items:center;white-space:nowrap">To'liq sahifa</a>
            <button wire:click="saveEditInfo" style="flex:2;padding:11px;border-radius:9px;border:none;background:#2563eb;color:#fff;cursor:pointer;font-size:13px;font-weight:700;box-shadow:0 2px 8px rgba(37,99,235,.3)">💾 Saqlash</button>
        </div>
    </div>
</div>
@endif
</div>
