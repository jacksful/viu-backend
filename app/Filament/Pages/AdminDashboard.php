<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveZipDatasetsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\TotalClientsWidget;
use App\Filament\Widgets\TotalLeadsWidget;
use App\Filament\Widgets\TotalSalesWidget;
use App\Filament\Widgets\Widgets1;
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
            Widgets1::class,
            RecentActivityWidget::class,
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

    // public function getSubheading(): ?string
    // {
    //     return 'Welcome back! Here\'s what\'s happening today.';
    // }
    public function getColumns(): int | array
    {
        return 12;
    }
}
