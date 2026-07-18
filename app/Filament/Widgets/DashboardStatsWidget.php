<?php

namespace App\Filament\Widgets;

use App\Support\AdminDashboardData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 'full';

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $dashboard = app(AdminDashboardData::class);

        $holdSummary = $dashboard->activeHoldSummary();
        $holdCount = $dashboard->activeHoldCount();
        $holdDescription = '$'.number_format($holdSummary['amount'], 0).'/yr at stake';

        if ($holdSummary['nearest_expires_label']) {
            $holdDescription .= ' · nearest expires in '.$holdSummary['nearest_expires_label'];
        }

        $subscriptionChange = $dashboard->subscriptionMonthOverMonthChange();
        $subscriptionDescription = ($subscriptionChange >= 0 ? '+' : '')
            .number_format($subscriptionChange, 1).'% from last month';

        $revenue = $dashboard->revenueSummary();
        $datasetSummary = $dashboard->subscribedZipDatasetSummary();

        $revenueValue = '$'.number_format($revenue['live'], 2);

        if ($revenue['test_mode']) {
            $revenueValue = new HtmlString(
                e($revenueValue).' '
                .Blade::render('<x-filament::badge color="warning">TEST MODE</x-filament::badge>'),
            );
        }

        $revenueDescription = $revenue['test_mode']
            ? '$'.number_format($revenue['test_excluded'], 2).' test-mode excluded · ledger in Stripe'
            : 'Paid revenue · ledger in Stripe';

        $datasetDescription = $datasetSummary['total'] > 0
            ? "of {$datasetSummary['total']} subscribed ZIPs · {$datasetSummary['missing']} missing upload"
            : 'No subscribed ZIPs yet';

        return [
            Stat::make('Active holds', $holdCount)
                ->description($holdDescription)
                ->icon('heroicon-o-lock-closed')
                ->color('primary')
                ->url($dashboard->holdsUrl()),

            Stat::make('Active subscriptions', $dashboard->activeSubscriptionCount())
                ->description($subscriptionDescription)
                ->descriptionIcon($subscriptionChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($subscriptionChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-user-group')
                ->color('primary')
                ->url($dashboard->subscriptionsUrl()),

            Stat::make('Revenue (live)', $revenueValue)
                ->description($revenueDescription)
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->url($revenue['stripe_dashboard_url'])
                ->openUrlInNewTab(),

            Stat::make("ZIPs with {$datasetSummary['month_label']} data", $datasetSummary['with_data'])
                ->description($datasetDescription)
                ->icon('heroicon-o-circle-stack')
                ->color('primary')
                ->url($dashboard->datasetsUrl()),
        ];
    }
}
