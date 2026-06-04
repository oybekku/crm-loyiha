<div wire:poll.8s="refresh" x-data>
<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}</style>

    {{-- Bell tugma --}}
    <button wire:click="$toggle('showPanel')"
            style="position:fixed;bottom:88px;right:20px;z-index:600;background:#fff;border:1.5px solid #e5e7eb;border-radius:50%;width:46px;height:46px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 10px rgba(0,0,0,.12);transition:all .15s"
            title="Xabarlar">
        <svg width="20" height="20" fill="none" stroke="#374151" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($unreadCount > 0)
        <span style="position:absolute;top:4px;right:4px;background:#ef4444;color:#fff;border-radius:10px;min-width:18px;height:18px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 4px;animation:pulse 1.5s infinite">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Panel --}}
    @if($showPanel)
    <div style="position:fixed;bottom:142px;right:16px;z-index:601;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.15);width:320px;max-width:calc(100vw - 32px);overflow:hidden"
         wire:click.outside="$set('showPanel', false)">

        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6">
            <span style="font-size:14px;font-weight:700;color:#111827">
                O'qilmagan xabarlar
                @if($unreadCount > 0)
                <span style="background:#ef4444;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px">{{ $unreadCount }}</span>
                @endif
            </span>
            <div style="display:flex;gap:6px">
                @if($unreadCount > 0)
                <button wire:click="markAllRead" style="font-size:11px;color:#2563eb;background:none;border:none;cursor:pointer;font-weight:600">Barchasi o'qildi</button>
                @endif
                <button wire:click="$set('showPanel',false)" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:18px;line-height:1">×</button>
            </div>
        </div>

        @forelse($latestMsgs as $msg)
        <a href="/admin/messages?user_id={{ $msg['uid'] }}"
           wire:navigate
           style="display:flex;gap:10px;padding:10px 14px;border-bottom:1px solid #f9fafb;text-decoration:none;transition:background .12s"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''"
           wire:click="$set('showPanel',false)">
            <div style="width:36px;height:36px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">
                {{ strtoupper(substr($msg['from'], 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:700;color:#111827;display:flex;justify-content:space-between">
                    <span>{{ $msg['from'] }}</span>
                    <span style="font-weight:400;color:#9ca3af;font-size:11px">{{ $msg['time'] }}</span>
                </div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $msg['body'] }}</div>
            </div>
        </a>
        @empty
        <div style="padding:20px;text-align:center;font-size:13px;color:#9ca3af">O'qilmagan xabar yo'q</div>
        @endforelse

        <a href="/admin/messages" wire:navigate style="display:block;text-align:center;padding:10px;font-size:12px;font-weight:600;color:#2563eb;text-decoration:none;border-top:1px solid #f3f4f6"
           wire:click="$set('showPanel',false)">
            Barcha xabarlar →
        </a>
    </div>
    @endif

</div>
