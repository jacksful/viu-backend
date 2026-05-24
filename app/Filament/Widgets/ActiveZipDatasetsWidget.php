<?php

namespace App\Filament\Widgets;

use App\Models\UploadedZipcode;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveZipDatasetsWidget extends BaseWidget
{
    protected static ?int $sort = 4;


    protected function getStats(): array
    {
        // Count published uploaded zipcodes that have datasets
        $activeDatasets = UploadedZipcode::where('status', 'published')
            ->whereHas('datasets')
            ->distinct('zipcode_id')
            ->count('zipcode_id');

        return [
            Stat::make('Active ZIP Datasets', $activeDatasets)
                ->description('With published data')
                ->icon('heroicon-o-circle-stack')
                ->color('purple'),
        ];
    }
}
