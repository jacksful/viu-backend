@php
    $filters = [
        'all' => 'All',
        'communication' => 'Communication',
        'billing' => 'System & billing',
    ];
@endphp

<div
    x-data="{
        filter: 'all',
        matches(category) {
            if (this.filter === 'all') {
                return true;
            }

            if (this.filter === 'communication') {
                return category === 'communication';
            }

            return ['billing', 'system'].includes(category);
        },
    }"
    class="fi-client-activity"
>
    <div class="mb-4 flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-700">
        @foreach($filters as $filterKey => $filterLabel)
            <button
                type="button"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                :class="filter === '{{ $filterKey }}'
                    ? 'bg-gray-100 text-gray-950 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-white dark:ring-gray-700'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                @click="filter = '{{ $filterKey }}'"
            >
                {{ $filterLabel }}
            </button>
        @endforeach
    </div>

    <div class="fi-client-activity-timeline max-h-[720px] overflow-y-auto pe-1">
        @forelse($activities as $activity)
            <div
                class="fi-client-activity-item"
                x-show="matches('{{ $activity['category'] }}')"
                x-cloak
            >
                <span @class([
                    'fi-client-activity-dot',
                    'fi-client-activity-dot-latest' => $loop->first,
                    'fi-client-activity-dot-default' => ! $loop->first,
                ])></span>

                <div class="space-y-1">
                    <p class="text-sm leading-snug text-gray-950 dark:text-white">
                        <span class="font-semibold">{{ $activity['title'] }}</span>

                        @if(filled($activity['summary'] ?? null))
                            <span class="font-normal text-gray-600 dark:text-gray-300">
                                — {{ $activity['summary'] }}
                            </span>
                        @endif

                        @if(filled($activity['badge'] ?? null))
                            <x-filament::badge
                                color="{{ $activity['badge_color'] ?? 'info' }}"
                                size="sm"
                                class="ms-1 align-middle"
                            >
                                {{ $activity['badge'] }}
                            </x-filament::badge>
                        @endif
                    </p>

                    @if(filled($activity['error'] ?? null))
                        <p class="text-sm text-danger-600 dark:text-danger-400">
                            {{ $activity['error'] }}
                        </p>
                    @endif

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $activity['timestamp']->format('M j, Y') }}
                        ·
                        {{ $activity['timestamp']->format('g:i A') }}

                        @if(filled($activity['meta_suffix'] ?? null))
                            · {{ $activity['meta_suffix'] }}
                        @endif

                        @if(filled($activity['action_label'] ?? null))
                            ·
                            <a
                                href="#client-intake-review"
                                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                            >
                                {{ $activity['action_label'] }}
                            </a>
                        @endif
                    </p>
                </div>
            </div>
        @empty
            <x-filament::empty-state
                heading="No activity recorded yet"
                description="Client events will appear here as they happen."
                icon="heroicon-o-bell"
            />
        @endforelse
    </div>
</div>
