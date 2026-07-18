<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use App\Models\User;

class AdminNotificationService
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function notifyAll(
        string $type,
        string $title,
        string $description,
        ?array $data = null,
    ): void {
        User::query()
            ->where('role', 'admin')
            ->each(function (User $admin) use ($type, $title, $description, $data): void {
                AppNotification::create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $title,
                    'description' => $description,
                    'is_read' => false,
                    'data' => $data,
                ]);
            });
    }
}
