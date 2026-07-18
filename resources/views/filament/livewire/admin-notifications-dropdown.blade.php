@php
    use Filament\Support\Enums\Width;
    use Filament\Support\Icons\Heroicon;

    $unreadNotifications = $this->unreadNotifications;
    $unreadCount = $this->unreadCount;

    $iconMap = [
        'waitlist_entry' => Heroicon::OutlinedClipboardDocumentList,
        'new_contact' => Heroicon::OutlinedUserPlus,
        'dataset_published' => Heroicon::OutlinedCircleStack,
        'data_update' => Heroicon::OutlinedArrowPath,
        'subscription_renewal' => Heroicon::OutlinedCalendarDays,
        'platform_update' => Heroicon::OutlinedMegaphone,
        'zipcode_assigned' => Heroicon::OutlinedMapPin,
        'subscription_activated' => Heroicon::OutlinedCheckCircle,
    ];
@endphp

<div wire:poll.30s class="fi-admin-notifications">
    <x-filament::dropdown
        placement="bottom-end"
        teleport
        width="{{ Width::Large }}"
    >
        <x-slot name="trigger">
            <x-filament::icon-button
                :badge="$unreadCount > 0 ? $unreadCount : null"
                color="gray"
                :icon="Heroicon::OutlinedBell"
                icon-size="lg"
                label="Notifications"
                class="fi-admin-notifications-trigger"
            />
        </x-slot>

        <div class="fi-admin-notifications-panel">
            <div class="fi-admin-notifications-header">
                <div class="fi-admin-notifications-heading">
                    {{ \Filament\Support\generate_icon_html(Heroicon::OutlinedBell, size: \Filament\Support\Enums\IconSize::Small) }}
                    <span>Notifications</span>
                    @if ($unreadCount > 0)
                        <x-filament::badge size="sm" color="primary">
                            {{ $unreadCount }}
                        </x-filament::badge>
                    @endif
                </div>

                @if ($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="fi-admin-notifications-mark-all"
                    >
                        Mark all as read
                    </button>
                @endif
            </div>

            <div class="fi-admin-notifications-list">
                @forelse ($unreadNotifications as $notification)
                    @php
                        $icon = $iconMap[$notification->type] ?? Heroicon::OutlinedBell;
                    @endphp

                    <button
                        type="button"
                        wire:click="markAsRead({{ $notification->id }})"
                        wire:key="admin-notification-{{ $notification->id }}"
                        class="fi-admin-notifications-item"
                    >
                        <span class="fi-admin-notifications-item-icon">
                            {{ \Filament\Support\generate_icon_html($icon, size: \Filament\Support\Enums\IconSize::Small) }}
                        </span>

                        <span class="fi-admin-notifications-item-content">
                            <span class="fi-admin-notifications-item-title">
                                {{ $notification->title }}
                            </span>
                            <span class="fi-admin-notifications-item-description">
                                {{ $notification->description }}
                            </span>
                            <span class="fi-admin-notifications-item-time">
                                {{ $notification->created_at?->diffForHumans() }}
                            </span>
                        </span>

                        <span class="fi-admin-notifications-item-dot" aria-hidden="true"></span>
                    </button>
                @empty
                    <div class="fi-admin-notifications-empty">
                        {{ \Filament\Support\generate_icon_html(Heroicon::OutlinedBellSlash, size: \Filament\Support\Enums\IconSize::Large) }}
                        <p>No unread notifications</p>
                    </div>
                @endforelse
            </div>
        </div>
    </x-filament::dropdown>
</div>
