<?php

namespace App\Filament\Widgets;

use App\Models\UserZipcodeSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalClientsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Count unique users with active subscriptions
        $totalClients = UserZipcodeSubscription::active()
            ->distinct('user_id')
            ->count('user_id');

        return [
            Stat::make('Total Clients', $totalClients)
                ->description('Active subscriptions')
                ->icon('heroicon-o-users')
                ->color('info'),
        ];
    }
}
