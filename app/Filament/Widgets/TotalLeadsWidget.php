<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\UserZipcodeSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalLeadsWidget extends BaseWidget
{
    protected static ?int $sort = 3;


    protected function getStats(): array
    {
        // Count customers who don't have any active subscriptions (leads awaiting conversion)
        $customersWithSubscriptions = UserZipcodeSubscription::active()
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        $totalLeads = User::where('role', 'customer')
            ->whereNotIn('id', $customersWithSubscriptions)
            ->count();

        return [
            Stat::make('Total Leads', $totalLeads)
                ->description('Awaiting conversion')
                ->icon('heroicon-o-user-plus')
                ->color('warning'),
        ];
    }
}
