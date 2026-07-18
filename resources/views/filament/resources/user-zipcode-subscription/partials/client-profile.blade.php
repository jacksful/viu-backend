@php
    /** @var \App\Models\User $user */
@endphp

<x-filament::section
    :contained="false"
    class="fi-client-profile-section"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
        <x-filament-panels::avatar.user
            :user="$user"
            size="lg"
            :circular="true"
            class="ring-2 ring-gray-200 light:ring-gray-200 dark:ring-gray-700"
        />

        <div class="min-w-0 flex-1 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-lg font-semibold text-gray-950 dark:text-white">
                    {{ $user->name }}
                </span>

                @if($user->email_verified_at)
                    <x-filament::badge color="success">
                        Verified
                    </x-filament::badge>
                @endif
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                @if($companyName)
                    {{ $companyName }} ·
                @endif
                {{ $locationLabel }} ·
                {{ $clientSince }}
            </p>

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                <x-filament::link
                    :href="'mailto:'.$user->email"
                    icon="heroicon-m-envelope"
                >
                    {{ $user->email }}
                </x-filament::link>

                @if($user->phone)
                    <x-filament::link
                        :href="'tel:'.$user->phone"
                        icon="heroicon-m-phone"
                    >
                        {{ $user->phone }}
                    </x-filament::link>
                @endif
            </div>
        </div>
    </div>
</x-filament::section>
