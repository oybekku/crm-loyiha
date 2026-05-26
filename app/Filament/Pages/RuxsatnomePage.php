<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Traits\HasMenuPermission;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class RuxsatnomePage extends Page
{
    use HasMenuPermission;

    protected static string  $view            = 'filament.pages.ruxsatnoma';
    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Ruxsatnomalar';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $title           = 'Ruxsatnomalar';

    public ?int   $selectedUserId  = null;
    public array  $userPermissions = [];

    public function selectUser(int $userId): void
    {
        $this->selectedUserId  = $userId;
        $user = User::find($userId);
        $this->userPermissions = $user?->permissions ?? [];
    }

    public function savePermissions(): void
    {
        if (!$this->selectedUserId) return;

        User::where('id', $this->selectedUserId)
            ->update(['permissions' => array_values($this->userPermissions)]);

        Notification::make()
            ->title('Ruxsatnomalar saqlandi')
            ->success()
            ->send();
    }

    public function togglePermission(string $key): void
    {
        if (in_array($key, $this->userPermissions)) {
            $this->userPermissions = array_values(
                array_filter($this->userPermissions, fn($p) => $p !== $key)
            );
        } else {
            $this->userPermissions[] = $key;
        }
    }

    public function toggleGroup(string $group): void
    {
        $all    = User::allPermissions();
        $groups = $this->buildGroups($all);
        $keys   = array_keys($groups[$group] ?? []);

        $allChecked = count(array_intersect($keys, $this->userPermissions)) === count($keys);

        if ($allChecked) {
            $this->userPermissions = array_values(
                array_filter($this->userPermissions, fn($p) => !in_array($p, $keys))
            );
        } else {
            $this->userPermissions = array_values(
                array_unique(array_merge($this->userPermissions, $keys))
            );
        }
    }

    private function buildGroups(array $all): array
    {
        $groups = ['Menyular' => [], 'Amallar' => [], 'Kanban ustunlari' => []];
        foreach ($all as $key => $label) {
            if (str_starts_with($label, 'Menyu:'))         $groups['Menyular'][$key] = $label;
            elseif (str_starts_with($label, 'Kanban ustun:')) $groups['Kanban ustunlari'][$key] = $label;
            else                                            $groups['Amallar'][$key] = $label;
        }
        return $groups;
    }

    public function getViewData(): array
    {
        $all = User::allPermissions();

        $groups = $this->buildGroups($all);

        return [
            'users'        => User::orderBy('name')->get(),
            'allPerms'     => $all,
            'permGroups'   => $groups,
            'selectedUser' => $this->selectedUserId ? User::find($this->selectedUserId) : null,
        ];
    }
}
