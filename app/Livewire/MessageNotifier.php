<?php

namespace App\Livewire;

use App\Models\Message;
use Illuminate\Support\Str;
use Livewire\Component;

class MessageNotifier extends Component
{
    public int   $unreadCount = 0;
    public array $latestMsgs  = [];
    public bool  $showPanel   = false;

    public function mount(): void { $this->refresh(); }

    public function refresh(): void
    {
        $uid = auth()->id();
        $this->unreadCount = Message::where('to_user_id', $uid)->whereNull('read_at')->count();
        $this->latestMsgs  = Message::with('sender')
            ->where('to_user_id', $uid)
            ->whereNull('read_at')
            ->latest()->take(5)->get()
            ->map(fn($m) => [
                'id'   => $m->id,
                'from' => $m->sender->name,
                'body' => Str::limit($m->body, 55),
                'time' => $m->created_at->format('H:i'),
                'uid'  => $m->from_user_id,
            ])->toArray();
    }

    public function markAllRead(): void
    {
        Message::where('to_user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        $this->refresh();
        $this->showPanel = false;
    }

    public function render()
    {
        return view('livewire.message-notifier');
    }
}
