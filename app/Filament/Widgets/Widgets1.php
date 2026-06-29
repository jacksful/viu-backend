<?php

namespace App\Filament\Widgets;

use App\Models\UploadedZipcode;
use App\Models\User;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class Widgets1 extends StatsOverviewWidget
{

    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '60s';
    protected function getStats(): array
    {
        // Total Sales Calculation
        $totalSales = UserZipcodeSubscription::active()
            ->with('user')
            ->get()
            ->sum(function ($subscription) {
                $zipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
                return $zipcodes->sum(function ($zipcode) use ($subscription) {
                    $startDate = Carbon::parse($subscription->start_date);
                    $endDate = $subscription->revenueEndAt();
                    $months = $startDate->diffInMonths($endDate);

                    if ($months >= 12) {
                        return $zipcode->yearly_price ?? 0;
                    } else {
                        return ($zipcode->monthly_price ?? 0) * max(1, $months);
                    }
                });
            });

        // Calculate previous month sales for comparison
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $previousMonthSales = UserZipcodeSubscription::active()
            ->where('start_date', '<=', $previousMonthEnd)
            ->where(function ($query) use ($previousMonthStart) {
                $query->where('status', 'active')
                    ->orWhere(function ($query) use ($previousMonthStart) {
                        $query->whereNotNull('end_date')
                            ->where('end_date', '>=', $previousMonthStart);
                    });
            })
            ->with('user')
            ->get()
            ->sum(function ($subscription) {
                $zipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
                return $zipcodes->sum(function ($zipcode) use ($subscription) {
                    $startDate = Carbon::parse($subscription->start_date);
                    $endDate = $subscription->revenueEndAt();
                    $months = $startDate->diffInMonths($endDate);

                    if ($months >= 12) {
                        return $zipcode->yearly_price ?? 0;
                    } else {
                        return ($zipcode->monthly_price ?? 0) * max(1, $months);
                    }
                });
            });

        $percentageChange = $previousMonthSales > 0
            ? (($totalSales - $previousMonthSales) / $previousMonthSales) * 100
            : 0;

        // Total Clients Calculation
        $totalClients = UserZipcodeSubscription::active()
            ->distinct('user_id')
            ->count('user_id');

        // Total Leads Calculation
        $customersWithSubscriptions = UserZipcodeSubscription::active()
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        $totalLeads = User::where('role', 'customer')
            ->whereNotIn('id', $customersWithSubscriptions)
            ->count();

        // Active ZIP Datasets Calculation
        $activeDatasets = UploadedZipcode::where('status', 'published')
            ->whereHas('datasets')
            ->distinct('zipcode_id')
            ->count('zipcode_id');

        return [
            Stat::make('Total Sales', '$' . number_format($totalSales, 2))
                ->description($percentageChange >= 0 ? '+' . number_format($percentageChange, 1) . '% from last month' : number_format($percentageChange, 1) . '% from last month')
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Total Clients', $totalClients)
                ->description('Active subscriptions')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Total Leads', $totalLeads)
                ->description('Awaiting conversion')
                ->icon('heroicon-o-user-plus')
                ->color('warning'),

            Stat::make('Active ZIP Datasets', $activeDatasets)
                ->description('With published data')
                ->icon('heroicon-o-circle-stack')
                ->color('purple'),
        ];
    }
}
