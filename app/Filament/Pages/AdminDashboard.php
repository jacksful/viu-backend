<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardMainColumnWidget;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\LaunchReadinessWidget;
use App\Support\AdminDashboardData;
use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    protected static string $routePath = '/';

    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            DashboardMainColumnWidget::class,
            LaunchReadinessWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Dashboard';
    }

    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        return app(AdminDashboardData::class)->formattedDate().' — All numbers link to the list they come from.';
    }

    public function getColumns(): int | array
    {
        return 12;
    }
}
