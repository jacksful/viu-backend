<x-filament-widgets::widget class="fi-account-widget fi-recent-activity-widget">
    <style>
        .flex{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .flex-shrink-0{
            flex-shrink: 0;
        }
        .flex-1{
            flex: 1;
        }
        .min-w-0{
            min-width: 0;
        }
        .font-semibold{
            font-weight: 600;
        }
        .text-sm{
            font-size: 14px;
        }
        .text-gray-900{
            color: #333;
        }
        .text-gray-600{
            color: #666;
        }
        .text-gray-500{
            color: #999;
        }
        .text-xs{
            font-size: 12px;
        }
        .mt-0.5{
            margin-top: 5px;
        }
        .mt-1.5{
            margin-top: 15px;
        }
        .text-center{
            text-align: center;
        }
        .py-8{
            padding-top: 80px;
        }
        .fi-recent-activity-widget .fi-section-content{
            display: block;
        }
    </style>
    <x-filament::section>
        <x-slot name="heading">
            Recent Activity
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getActivities() as $activity)
                <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700" style="border-bottom: 1px solid #ebebeb;">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($activity->type === 'new_lead')
                            <x-filament::icon 
                                icon="heroicon-o-user-plus" 
                                class="w-5 h-5 text-success-600 dark:text-success-400" 
                            />
                        @elseif($activity->type === 'dataset_published')
                            <x-filament::icon 
                                icon="heroicon-o-circle-stack" 
                                class="w-5 h-5 text-info-600 dark:text-info-400" 
                            />
                        @elseif($activity->type === 'new_client')
                            <x-filament::icon 
                                icon="heroicon-o-user-group" 
                                class="w-5 h-5 text-purple-600 dark:text-purple-400" 
                            />
                        @else
                            <x-filament::icon 
                                icon="heroicon-o-bell" 
                                class="w-5 h-5 text-gray-500" 
                            />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                            {{ $activity->title }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                            {{ $activity->description }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1.5">
                            {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p class="text-sm">No recent activity</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

