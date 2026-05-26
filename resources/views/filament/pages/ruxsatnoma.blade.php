<x-filament-panels::page>
<style>
.rx-wrap{display:flex;gap:20px;align-items:flex-start}
.rx-users{width:260px;flex-shrink:0;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.dark .rx-users{background:#18181b;border-color:#27272a}
.rx-users-head{padding:14px 16px;font-weight:700;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;background:#f9fafb}
.dark .rx-users-head{background:#1c1c1f;color:#e5e7eb;border-color:#27272a}
.rx-user-item{display:flex;align-items:center;gap:10px;padding:11px 16px;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background .12s}
.dark .rx-user-item{border-color:#27272a}
.rx-user-item:last-child{border-bottom:none}
.rx-user-item:hover{background:#f0f9ff}
.dark .rx-user-item:hover{background:#1e3a5f}
.rx-user-item.active{background:#eff6ff;border-left:3px solid #2563eb}
.dark .rx-user-item.active{background:#1e3a5f;border-left-color:#3b82f6}
.rx-avatar{width:34px;height:34px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0}
.rx-user-name{font-size:13px;font-weight:600;color:#111827}
.dark .rx-user-name{color:#f3f4f6}
.rx-user-role{font-size:11px;color:#6b7280}
.rx-panel{flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.dark .rx-panel{background:#18181b;border-color:#27272a}
.rx-panel-head{padding:14px 20px;font-weight:700;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;background:#f9fafb;display:flex;align-items:center;justify-content:space-between}
.dark .rx-panel-head{background:#1c1c1f;color:#e5e7eb;border-color:#27272a}
.rx-panel-body{padding:20px;display:flex;flex-direction:column;gap:10px}
.rx-perm-item{display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid #e5e7eb;border-radius:10px;cursor:pointer;transition:all .12s}
.dark .rx-perm-item{border-color:#3f3f46}
.rx-perm-item:hover{background:#f8fafc;border-color:#93c5fd}
.dark .rx-perm-item:hover{background:#1e3a5f}
.rx-perm-item.checked{background:#eff6ff;border-color:#2563eb}
.dark .rx-perm-item.checked{background:#1e3a5f;border-color:#3b82f6}
.rx-checkbox{width:18px;height:18px;border:2px solid #d1d5db;border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .12s}
.rx-perm-item.checked .rx-checkbox{background:#2563eb;border-color:#2563eb}
.rx-perm-label{font-size:13px;color:#374151;font-weight:500}
.dark .rx-perm-label{color:#e5e7eb}
.rx-save-btn{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:600;cursor:pointer}
.rx-save-btn:hover{background:#1d4ed8}
.rx-empty{padding:48px 20px;text-align:center;color:#9ca3af;font-size:13px}
.rx-admin-badge{font-size:11px;background:#fef3c7;color:#92400e;border-radius:6px;padding:2px 8px;font-weight:600}
</style>

<div class="rx-wrap">
    {{-- Hodimlar ro'yxati --}}
    <div class="rx-users">
        <div class="rx-users-head">Hodimlar ro'yxati</div>
        @foreach($users as $user)
        <div class="rx-user-item {{ $selectedUserId === $user->id ? 'active' : '' }}"
             wire:click="selectUser({{ $user->id }})">
            <div class="rx-avatar">{{ mb_substr($user->name, 0, 1) }}</div>
            <div>
                <div class="rx-user-name">{{ $user->name }}</div>
                <div class="rx-user-role">{{ $user->role_name }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Ruxsatnomalar paneli --}}
    <div class="rx-panel" style="flex:1">
        @if($selectedUser)
        <div class="rx-panel-head">
            <span>
                {{ $selectedUser->name }} — ruxsatnomalar
                @if($selectedUser->isAdmin())
                <span class="rx-admin-badge">Admin — barcha ruxsatlar mavjud</span>
                @endif
            </span>
            @if(!$selectedUser->isAdmin())
            <button class="rx-save-btn" wire:click="savePermissions">
                Saqlash
            </button>
            @endif
        </div>
        <div class="rx-panel-body">
            @if($selectedUser->isAdmin())
            <div style="padding:16px;background:#fef3c7;border-radius:10px;color:#92400e;font-size:13px">
                Admin foydalanuvchiga barcha ruxsatlar avtomatik berilgan. O'zgartirib bo'lmaydi.
            </div>
            @else
            @foreach($permGroups as $groupName => $groupPerms)
            @if(count($groupPerms))
            @php
                $groupKeys      = array_keys($groupPerms);
                $checkedInGroup = count(array_intersect($groupKeys, $userPermissions));
                $totalInGroup   = count($groupKeys);
                $allGroupChecked = $checkedInGroup === $totalInGroup;
            @endphp
            <div style="margin-bottom:8px">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 2px 8px">
                    <span style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em">
                        {{ $groupName }}
                        <span style="font-weight:400;font-size:11px;color:#9ca3af">({{ $checkedInGroup }}/{{ $totalInGroup }})</span>
                    </span>
                    <button wire:click="toggleGroup('{{ $groupName }}')"
                            style="font-size:11px;padding:3px 10px;border-radius:6px;border:1px solid {{ $allGroupChecked ? '#dc2626' : '#2563eb' }};background:{{ $allGroupChecked ? '#fee2e2' : '#eff6ff' }};color:{{ $allGroupChecked ? '#dc2626' : '#2563eb' }};cursor:pointer;font-weight:600">
                        {{ $allGroupChecked ? 'Barchasini olib tashlash' : 'Barchasini belgilash' }}
                    </button>
                </div>
                @foreach($groupPerms as $key => $label)
                @php
                    $checked   = in_array($key, $userPermissions);
                    $dispLabel = preg_replace('/^(Menyu|Amal|Kanban ustun):\s*/u', '', $label);
                @endphp
                <div class="rx-perm-item {{ $checked ? 'checked' : '' }}"
                     wire:click="togglePermission('{{ $key }}')"
                     style="margin-bottom:6px">
                    <div class="rx-checkbox">
                        @if($checked)
                        <svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                    <span class="rx-perm-label">{{ $dispLabel }}</span>
                </div>
                @endforeach
            </div>
            @endif
            @endforeach
            @endif
        </div>
        @else
        <div class="rx-empty">
            <div style="font-size:32px;margin-bottom:8px">👈</div>
            Hodimni tanlang
        </div>
        @endif
    </div>
</div>
</x-filament-panels::page>
