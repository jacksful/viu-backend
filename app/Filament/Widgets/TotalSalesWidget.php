<?php

namespace App\Filament\Widgets;

use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TotalSalesWidget extends BaseWidget
{
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        // Calculate total sales from active subscriptions
        $totalSales = UserZipcodeSubscription::active()
            ->with('user')
            ->get()
            ->sum(function ($subscription) {
                $zipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
                return $zipcodes->sum(function ($zipcode) use ($subscription) {
                    // Determine if subscription is monthly or yearly based on duration
                    $startDate = Carbon::parse($subscription->start_date);
                    $endDate = $subscription->end_date ? Carbon::parse($subscription->end_date) : now();
                    $months = $startDate->diffInMonths($endDate);

                    // If subscription is 12+ months, use yearly price, otherwise monthly
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
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $previousMonthStart);
            })
            ->with('user')
            ->get()
            ->sum(function ($subscription) {
                $zipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
                return $zipcodes->sum(function ($zipcode) use ($subscription) {
                    $startDate = Carbon::parse($subscription->start_date);
                    $endDate = $subscription->end_date ? Carbon::parse($subscription->end_date) : now();
                    $months = $startDate->diffInMonths($endDate);

                    if ($months >= 12) {
                        return $zipcode->yearly_price ?? 0;
                    } else {
                        return ($zipcode->monthly_price ?? 0) * max(1, $months);
                    }
                });
            });

        // Calculate percentage change
        $percentageChange = $previousMonthSales > 0
            ? (($totalSales - $previousMonthSales) / $previousMonthSales) * 100
            : 0;

        return [
            Stat::make('Total Sales', '$' . number_format($totalSales, 2))
                ->description($percentageChange >= 0 ? '+' . number_format($percentageChange, 1) . '% from last month' : number_format($percentageChange, 1) . '% from last month')
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar')
                ->chart([7, 3, 4, 5, 6, 3, 5]),
        ];
    }
}
