<x-filament-panels::page>
<style>
.msg-wrap{display:flex;height:calc(100vh - 180px);min-height:400px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff}
.dark .msg-wrap{background:#1e2533;border-color:#374151}
/* Sidebar */
.msg-sidebar{width:280px;flex-shrink:0;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;overflow:hidden}
.dark .msg-sidebar{border-color:#374151}
.msg-sidebar-head{padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between}
.dark .msg-sidebar-head{border-color:#374151}
.msg-sidebar-head h3{font-size:15px;font-weight:700;color:#111827}
.dark .msg-sidebar-head h3{color:#f9fafb}
.msg-list{flex:1;overflow-y:auto}
.msg-item{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .12s;border-bottom:1px solid #f3f4f6}
.dark .msg-item{border-color:#27272a}
.msg-item:hover{background:#f9fafb}
.dark .msg-item:hover{background:#27272a}
.msg-item.active{background:#eff6ff}
.dark .msg-item.active{background:#1e3a5f}
.msg-avatar{width:38px;height:38px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0}
.msg-item-info{flex:1;min-width:0}
.msg-item-name{font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dark .msg-item-name{color:#f9fafb}
.msg-item-last{font-size:11px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.msg-badge{background:#ef4444;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700;flex-shrink:0}
/* Chat area */
.msg-chat{flex:1;display:flex;flex-direction:column;min-width:0}
.msg-chat-head{padding:12px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:10px;flex-shrink:0}
.dark .msg-chat-head{border-color:#374151}
.msg-chat-head-name{font-size:15px;font-weight:700;color:#111827}
.dark .msg-chat-head-name{color:#f9fafb}
.msg-body{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:8px}
.msg-bubble-wrap{display:flex;flex-direction:column}
.msg-bubble-wrap.mine{align-items:flex-end}
.msg-bubble-wrap.theirs{align-items:flex-start}
.msg-bubble{max-width:70%;padding:9px 14px;border-radius:14px;font-size:13px;line-height:1.55;word-break:break-word}
.msg-bubble.mine{background:#2563eb;color:#fff;border-radius:14px 14px 4px 14px}
.msg-bubble.theirs{background:#f3f4f6;color:#111827;border-radius:14px 14px 14px 4px}
.dark .msg-bubble.theirs{background:#374151;color:#f9fafb}
.msg-time{font-size:10px;color:#9ca3af;margin-top:3px;padding:0 4px}
.msg-input-wrap{padding:12px 16px;border-top:1px solid #e5e7eb;display:flex;gap:8px;flex-shrink:0}
.dark .msg-input-wrap{border-color:#374151}
.msg-input{flex:1;border:1px solid #e2e8f0;border-radius:24px;padding:10px 16px;font-size:13px;outline:none;resize:none;background:#fff;color:#111;max-height:100px;overflow-y:auto}
.dark .msg-input{background:#27272a;border-color:#3f3f46;color:#f4f4f5}
.msg-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.msg-send-btn{background:#2563eb;color:#fff;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .15s}
.msg-send-btn:hover{background:#1d4ed8}
/* Empty state */
.msg-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#9ca3af;gap:10px}
.msg-empty svg{width:48px;height:48px;opacity:.4}
/* New chat btn */
.msg-new-btn{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px}
/* User select modal */
.msg-user-select{position:absolute;top:54px;left:0;right:0;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:50;max-height:260px;overflow-y:auto}
.dark .msg-user-select{background:#1e2533;border-color:#374151}
.msg-user-opt{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .12s}
.msg-user-opt:hover{background:#f9fafb}
.dark .msg-user-opt:hover{background:#27272a}

@media(max-width:640px){
  .msg-sidebar{width:100%;border-right:none;display:{{ $activeUserId ? 'none' : 'flex' }}}
  .msg-chat{display:{{ $activeUserId ? 'flex' : 'none' }}}
  .msg-wrap{height:calc(100vh - 140px)}
}
</style>

<div class="msg-wrap" wire:poll.5s>

    {{-- SIDEBAR --}}
    <div class="msg-sidebar">
        <div class="msg-sidebar-head" style="position:relative" x-data="{open:false}">
            <h3>Suhbatlar</h3>
            <button class="msg-new-btn" @click="open=!open">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Yangi
            </button>
            <div class="msg-user-select" x-show="open" x-cloak @click.outside="open=false">
                @foreach($allUsers as $u)
                <div class="msg-user-opt" wire:click="openConversation({{ $u->id }})" @click="open=false">
                    <div class="msg-avatar" style="width:30px;height:30px;font-size:12px">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <span style="font-size:13px;font-weight:500;color:#374151">{{ $u->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="msg-list">
            @forelse($conversations as $conv)
            <div class="msg-item {{ $activeUserId === $conv->user->id ? 'active' : '' }}"
                 wire:click="openConversation({{ $conv->user->id }})">
                <div class="msg-avatar">{{ strtoupper(substr($conv->user->name,0,1)) }}</div>
                <div class="msg-item-info">
                    <div class="msg-item-name">{{ $conv->user->name }}</div>
                    <div class="msg-item-last">{{ Str::limit($conv->last?->body, 35) }}</div>
                </div>
                @if($conv->unread > 0)
                <span class="msg-badge">{{ $conv->unread }}</span>
                @endif
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:#9ca3af">Hali suhbat yo'q</div>
            @endforelse
        </div>
    </div>

    {{-- CHAT AREA --}}
    <div class="msg-chat">
        @if($activeUserId && $activeUser)
        <div class="msg-chat-head">
            @if(request()->is('*/messages*'))
            <button wire:click="$set('activeUserId', 0)"
                    style="display:none;background:none;border:none;cursor:pointer;color:#6b7280;padding:4px"
                    class="msg-back-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            </button>
            @endif
            <div class="msg-avatar" style="width:34px;height:34px;font-size:13px">{{ strtoupper(substr($activeUser->name,0,1)) }}</div>
            <div>
                <div class="msg-chat-head-name">{{ $activeUser->name }}</div>
                <div style="font-size:11px;color:#6b7280">{{ $activeUser->role }}</div>
            </div>
        </div>

        <div class="msg-body" id="msg-body">
            @foreach($messages as $msg)
            @php $isMine = $msg->from_user_id === auth()->id(); @endphp
            <div class="msg-bubble-wrap {{ $isMine ? 'mine' : 'theirs' }}">
                <div class="msg-bubble {{ $isMine ? 'mine' : 'theirs' }}">{{ $msg->body }}</div>
                <div class="msg-time">{{ $msg->created_at->format('H:i') }} · {{ $msg->created_at->format('d.m') }}</div>
            </div>
            @endforeach
        </div>

        <div class="msg-input-wrap">
            <textarea class="msg-input" wire:model="newMessage" placeholder="Xabar yozing..."
                      rows="1"
                      wire:keydown.enter.prevent="sendMessage"></textarea>
            <button class="msg-send-btn" wire:click="sendMessage">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            </button>
        </div>

        @else
        <div class="msg-empty">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span style="font-size:14px;font-weight:500">Suhbat tanlang</span>
        </div>
        @endif
    </div>

</div>

<script>
// Xabarlar pastga scroll
function scrollMsgBottom() {
    const el = document.getElementById('msg-body');
    if (el) el.scrollTop = el.scrollHeight;
}
scrollMsgBottom();
document.addEventListener('livewire:update', scrollMsgBottom);

// Mobilda orqaga tugma
if (window.innerWidth <= 640) {
    document.querySelector('.msg-back-btn')?.style.setProperty('display', 'flex', 'important');
}
</script>

</x-filament-panels::page>
