<?php

namespace App\Filament\Livewire;

use App\Models\Notification;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class AdminNotificationsDropdown extends Component
{
    public function getUnreadCountProperty(): int
    {
        return $this->unreadNotificationsQuery()->count();
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getUnreadNotificationsProperty(): Collection
    {
        return $this->unreadNotificationsQuery()
            ->latest()
            ->limit(50)
            ->get();
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = $this->findNotificationForUser($notificationId);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        if ($url = data_get($notification->data, 'url')) {
            $this->redirect($url);
        }
    }

    public function markAllAsRead(): void
    {
        $this->unreadNotificationsQuery()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    protected function unreadNotificationsQuery()
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return Notification::query()->whereRaw('0 = 1');
        }

        return Notification::query()
            ->where('user_id', $user->getKey())
            ->where('is_read', false);
    }

    protected function findNotificationForUser(int $notificationId): ?Notification
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return null;
        }

        return Notification::query()
            ->where('user_id', $user->getKey())
            ->whereKey($notificationId)
            ->first();
    }

    public function render(): View
    {
        return view('filament.livewire.admin-notifications-dropdown');
    }
}
