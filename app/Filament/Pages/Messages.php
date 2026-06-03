<?php

namespace App\Filament\Pages;

use App\Models\Message;
use App\Models\User;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class Messages extends Page
{
    protected static string  $view           = 'filament.pages.messages';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Xabarlar';
    protected static ?int $navigationSort  = 5;
    protected static ?string $title           = 'Xabarlar';

    public int    $activeUserId = 0;
    public string $newMessage   = '';

    public static function getNavigationBadge(): ?string
    {
        $count = Message::where('to_user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function mount(): void
    {
        // URL dan user_id ni olish
        $uid = request()->get('user_id');
        if ($uid) {
            $this->openConversation((int) $uid);
        }
    }

    public function openConversation(int $userId): void
    {
        $this->activeUserId = $userId;
        // O'qilmagan xabarlarni o'qilgan deb belgilash
        Message::where('from_user_id', $userId)
            ->where('to_user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendMessage(): void
    {
        $body = trim($this->newMessage);
        if (!$body || !$this->activeUserId) return;

        Message::create([
            'from_user_id' => auth()->id(),
            'to_user_id'   => $this->activeUserId,
            'body'         => $body,
        ]);

        $this->newMessage = '';
    }

    public function getViewData(): array
    {
        $uid = auth()->id();

        // Suhbat qilgan foydalanuvchilar
        $userIds = Message::where('from_user_id', $uid)
            ->orWhere('to_user_id', $uid)
            ->get()
            ->flatMap(fn($m) => [$m->from_user_id, $m->to_user_id])
            ->unique()
            ->filter(fn($id) => $id !== $uid)
            ->values();

        $conversations = User::whereIn('id', $userIds)
            ->get()
            ->map(function ($user) use ($uid) {
                $last = Message::where(fn($q) => $q->where('from_user_id', $uid)->where('to_user_id', $user->id))
                    ->orWhere(fn($q) => $q->where('from_user_id', $user->id)->where('to_user_id', $uid))
                    ->latest()
                    ->first();
                $unread = Message::where('from_user_id', $user->id)
                    ->where('to_user_id', $uid)
                    ->whereNull('read_at')
                    ->count();
                return (object) [
                    'user'    => $user,
                    'last'    => $last,
                    'unread'  => $unread,
                ];
            })
            ->sortByDesc(fn($c) => $c->last?->created_at);

        $messages = [];
        $activeUser = null;
        if ($this->activeUserId) {
            $activeUser = User::find($this->activeUserId);
            $messages = Message::where(fn($q) => $q->where('from_user_id', $uid)->where('to_user_id', $this->activeUserId))
                ->orWhere(fn($q) => $q->where('from_user_id', $this->activeUserId)->where('to_user_id', $uid))
                ->orderBy('created_at')
                ->get();
        }

        // Yangi suhbat boshlash uchun — barcha foydalanuvchilar
        $allUsers = User::where('id', '!=', $uid)->orderBy('name')->get();

        return compact('conversations', 'messages', 'activeUser', 'allUsers');
    }
}
